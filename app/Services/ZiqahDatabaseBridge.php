<?php

namespace App\Services;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ZiqahDatabaseBridge
{
    public function __construct(
        protected int $maxSchemaTables = 40,
        protected int $maxColumnsPerTable = 12,
    ) {}

    /**
     * @return list<string>
     */
    public function connectionNames(): array
    {
        return array_keys(Config::get('database.connections', []));
    }

    public function resolveConnection(?string $name): string
    {
        $name = $name !== null && $name !== '' ? $name : (string) Config::get('database.default', 'mysql');

        if (! in_array($name, $this->connectionNames(), true)) {
            return '';
        }

        return $name;
    }

    /**
     * Short text block listing configured connections that respond, with table/column hints.
     */
    public function buildSchemaContext(): string
    {
        $blocks = [];

        foreach ($this->connectionNames() as $connName) {
            try {
                $connection = DB::connection($connName);
                $connection->getPdo();
                $driver = $connection->getDriverName();
                $database = $this->safeDatabaseLabel($connection->getDatabaseName());

                $tables = $this->listTables($connection, $driver);
                if ($tables === []) {
                    $blocks[] = "- Connection `{$connName}` ({$driver}".($database !== '' ? ", db `{$database}`" : '').'): no tables found or not introspectable.';

                    continue;
                }

                $lines = ["- Connection `{$connName}` ({$driver}".($database !== '' ? ", db `{$database}`" : '').'):'];
                foreach (array_slice($tables, 0, $this->maxSchemaTables) as $table) {
                    $cols = $this->listColumns($connection, $driver, $table);
                    $colStr = $cols === []
                        ? '(columns unknown)'
                        : implode(', ', array_slice($cols, 0, $this->maxColumnsPerTable)).(count($cols) > $this->maxColumnsPerTable ? ', …' : '');
                    $lines[] = "  - `{$table}`: {$colStr}";
                }
                if (count($tables) > $this->maxSchemaTables) {
                    $lines[] = '  - … more tables omitted';
                }
                $blocks[] = implode("\n", $lines);
            } catch (Throwable $e) {
                Log::debug('ZiqahDatabaseBridge: connection skipped', ['connection' => $connName, 'error' => $e->getMessage()]);
                $blocks[] = "- Connection `{$connName}`: unreachable ({$e->getMessage()}).";
            }
        }

        if ($blocks === []) {
            return '';
        }

        return "\n\nDATABASE CONTEXT (Laravel connections; use the database_query tool for live rows/changes):\n".implode("\n", $blocks);
    }

    /**
     * Distinct location-related values from BruDMS tables (default connection) for the AI system prompt.
     */
    public function buildLocationCatalog(int $perSourceLimit = 60, int $maxTotalChars = 14000): string
    {
        $connName = (string) Config::get('database.default', 'mysql');
        $perSourceLimit = max(5, min(200, $perSourceLimit));
        $maxTotalChars = max(2000, min(50000, $maxTotalChars));

        try {
            $connection = DB::connection($connName);
            $connection->getPdo();
        } catch (Throwable $e) {
            Log::debug('ZiqahDatabaseBridge: location catalog skipped', ['error' => $e->getMessage()]);

            return '';
        }

        $schema = Schema::connection($connName);
        $districts = Config::get('brunei.districts', []);
        $mukims = Config::get('brunei.mukims', []);

        $lines = [
            "\n\nLOCATION DATA (from your database; samples below — use `database_query` to search, filter, or list coordinates):",
            '- `reports`: column `address` (text), plus `latitude` / `longitude` when set. For public/nearby context, consider only reports already linked to `operations_reports` (sent to operations).',
            '- `operations_reports`: `location_address`, `district`, `mukim` (slugs matching Brunei config), `latitude`, `longitude`.',
            '- `work_orders`: same geographic columns as operations reports.',
        ];

        try {
            if ($schema->hasTable('reports') && $schema->hasColumn('reports', 'address')) {
                $reportQuery = $connection->table('reports')
                    ->whereNotNull('address')
                    ->where('address', '!=', '')
                    ->when(
                        $schema->hasTable('operations_reports') && $schema->hasColumn('operations_reports', 'customer_report_id'),
                        function ($q): void {
                            $q->whereExists(function ($sub): void {
                                $sub->selectRaw('1')
                                    ->from('operations_reports')
                                    ->whereColumn('operations_reports.customer_report_id', 'reports.id');
                            });
                        }
                    );
                $addrs = $reportQuery->distinct()
                    ->orderBy('address')
                    ->limit($perSourceLimit)
                    ->pluck('address');
                $samples = $this->stringListFromIterable($addrs);
                if ($samples !== []) {
                    $lines[] = 'Distinct customer report addresses already sent to operations (`reports.address`, sample up to '.$perSourceLimit.'):';
                    foreach ($samples as $s) {
                        $lines[] = '  • '.$s;
                    }
                }
            }

            foreach (['operations_reports' => 'Operations reports', 'work_orders' => 'Work orders'] as $table => $label) {
                if (! $schema->hasTable($table) || ! $schema->hasColumn($table, 'location_address')) {
                    continue;
                }

                $addrs = $connection->table($table)
                    ->whereNotNull('location_address')
                    ->where('location_address', '!=', '')
                    ->distinct()
                    ->orderBy('location_address')
                    ->limit($perSourceLimit)
                    ->pluck('location_address');
                $samples = $this->stringListFromIterable($addrs);
                if ($samples !== []) {
                    $lines[] = "{$label} (`{$table}.location_address`, sample):";
                    foreach ($samples as $s) {
                        $lines[] = '  • '.$s;
                    }
                }

                if ($schema->hasColumn($table, 'district') && $schema->hasColumn($table, 'mukim')) {
                    $pairs = $connection->table($table)
                        ->select(['district', 'mukim'])
                        ->where(function ($q): void {
                            $q->whereNotNull('district')->orWhereNotNull('mukim');
                        })
                        ->distinct()
                        ->orderBy('district')
                        ->orderBy('mukim')
                        ->limit($perSourceLimit)
                        ->get();

                    $pairLines = [];
                    foreach ($pairs as $row) {
                        $d = $row->district ?? null;
                        $m = $row->mukim ?? null;
                        if (($d === null || $d === '') && ($m === null || $m === '')) {
                            continue;
                        }
                        $dKey = is_string($d) ? $d : '';
                        $mKey = is_string($m) ? $m : '';
                        $dLabel = $dKey !== '' ? ($districts[$dKey] ?? $dKey) : '—';
                        $mLabel = '—';
                        if ($mKey !== '') {
                            $mLabel = ($dKey !== '' && isset($mukims[$dKey]) && is_array($mukims[$dKey]))
                                ? ($mukims[$dKey][$mKey] ?? $mKey)
                                : $mKey;
                        }
                        $pairLines[] = '  • district key `'.$dKey.'` ('.$dLabel.') + mukim key `'.$mKey.'` ('.$mLabel.')';
                    }
                    if ($pairLines !== []) {
                        $lines[] = "{$label} (`{$table}` district/mukim pairs, sample):";
                        $lines = array_merge($lines, $pairLines);
                    }
                }
            }
        } catch (Throwable $e) {
            Log::warning('ZiqahDatabaseBridge: location catalog query failed', ['error' => $e->getMessage()]);
            $lines[] = '(Location catalog could not be loaded: '.$e->getMessage().')';
        }

        $body = implode("\n", $lines);
        if (strlen($body) <= $maxTotalChars) {
            return $body;
        }

        return substr($body, 0, $maxTotalChars - 40)."\n… (location catalog truncated for size)";
    }

    /**
     * @param  iterable<int, mixed>  $values
     * @return list<string>
     */
    private function stringListFromIterable(iterable $values): array
    {
        $out = [];
        foreach ($values as $v) {
            $s = is_string($v) ? trim($v) : '';
            if ($s === '') {
                continue;
            }
            if (strlen($s) > 500) {
                $s = substr($s, 0, 497).'…';
            }
            $out[] = $s;
        }

        return $out;
    }

    /**
     * Rank issues with coordinates by straight-line distance from the user (default DB connection).
     */
    public function buildNearestIssuesContext(float $userLat, float $userLng, int $limit = 8, int $maxCandidatesPerTable = 200): string
    {
        $limit = max(1, min(20, $limit));
        $maxCandidatesPerTable = max(30, min(500, $maxCandidatesPerTable));

        $connName = (string) Config::get('database.default', 'mysql');
        try {
            $connection = DB::connection($connName);
            $connection->getPdo();
        } catch (Throwable $e) {
            Log::debug('ZiqahDatabaseBridge: nearest issues skipped', ['error' => $e->getMessage()]);

            return '';
        }

        $schema = Schema::connection($connName);
        $candidates = [];

        try {
            if ($schema->hasTable('reports') && $schema->hasColumn('reports', 'latitude') && $schema->hasColumn('reports', 'longitude')) {
                $cols = ['id', 'latitude', 'longitude', 'status'];
                if ($schema->hasColumn('reports', 'reference_code')) {
                    $cols[] = 'reference_code';
                }
                if ($schema->hasColumn('reports', 'address')) {
                    $cols[] = 'address';
                }
                if ($schema->hasColumn('reports', 'problem_type')) {
                    $cols[] = 'problem_type';
                }
                $rows = $connection->table('reports')->select($cols)
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    // Exclude private "under review"/unsent customer submissions from nearby context.
                    ->when(
                        $schema->hasTable('operations_reports') && $schema->hasColumn('operations_reports', 'customer_report_id'),
                        function ($q): void {
                            $q->whereExists(function ($sub): void {
                                $sub->selectRaw('1')
                                    ->from('operations_reports')
                                    ->whereColumn('operations_reports.customer_report_id', 'reports.id');
                            });
                        }
                    )
                    ->orderByDesc('id')
                    ->limit($maxCandidatesPerTable)
                    ->get();
                foreach ($rows as $r) {
                    $this->pushGeoCandidate($candidates, $userLat, $userLng, $r, 'Customer report', function ($row) {
                        $ref = isset($row->reference_code) && $row->reference_code !== '' ? (string) $row->reference_code : '#'.$row->id;
                        $type = isset($row->problem_type) ? (string) $row->problem_type : '';
                        $addr = isset($row->address) ? trim((string) $row->address) : '';
                        $st = isset($row->status) ? (string) $row->status : '';

                        return $ref.($type !== '' ? ' — '.$type : '').($st !== '' ? ' — status: '.$st : '').($addr !== '' ? ' — '.$addr : '');
                    });
                }
            }

            if ($schema->hasTable('operations_reports')
                && $schema->hasColumn('operations_reports', 'latitude')
                && $schema->hasColumn('operations_reports', 'longitude')) {
                $cols = ['id', 'report_number', 'issue_type', 'status', 'location_address', 'latitude', 'longitude'];
                $rows = $connection->table('operations_reports')->select($cols)
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->orderByDesc('id')
                    ->limit($maxCandidatesPerTable)
                    ->get();
                foreach ($rows as $r) {
                    $this->pushGeoCandidate($candidates, $userLat, $userLng, $r, 'Operations report', function ($row) {
                        $num = isset($row->report_number) ? (string) $row->report_number : '#'.$row->id;
                        $issue = isset($row->issue_type) ? (string) $row->issue_type : '';
                        $st = isset($row->status) ? (string) $row->status : '';
                        $addr = isset($row->location_address) ? trim((string) $row->location_address) : '';

                        return $num.($issue !== '' ? ' — '.$issue : '').($st !== '' ? ' — status: '.$st : '').($addr !== '' ? ' — '.$addr : '');
                    });
                }
            }

            if ($schema->hasTable('work_orders')
                && $schema->hasColumn('work_orders', 'latitude')
                && $schema->hasColumn('work_orders', 'longitude')) {
                $cols = ['id', 'work_order_number', 'type', 'status', 'location_address', 'latitude', 'longitude'];
                $rows = $connection->table('work_orders')->select($cols)
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->orderByDesc('id')
                    ->limit($maxCandidatesPerTable)
                    ->get();
                foreach ($rows as $r) {
                    $this->pushGeoCandidate($candidates, $userLat, $userLng, $r, 'Work order', function ($row) {
                        $num = isset($row->work_order_number) ? (string) $row->work_order_number : '#'.$row->id;
                        $type = isset($row->type) ? (string) $row->type : '';
                        $st = isset($row->status) ? (string) $row->status : '';
                        $addr = isset($row->location_address) ? trim((string) $row->location_address) : '';

                        return $num.($type !== '' ? ' — '.$type : '').($st !== '' ? ' — status: '.$st : '').($addr !== '' ? ' — '.$addr : '');
                    });
                }
            }
        } catch (Throwable $e) {
            Log::warning('ZiqahDatabaseBridge: nearest issues query failed', ['error' => $e->getMessage()]);

            return "\n\nUSER GEO CONTEXT: latitude {$userLat}, longitude {$userLng} (Brunei).\nNEAREST KNOWN ISSUES: (could not load — ".$e->getMessage().')';
        }

        usort($candidates, fn (array $a, array $b): int => $a['km'] <=> $b['km']);
        $top = array_slice($candidates, 0, $limit);

        $lines = [
            "\n\nUSER GEO CONTEXT (this chat request; user’s approximate position):",
            '- Latitude: '.round($userLat, 6).', Longitude: '.round($userLng, 6).' (within Brunei bounds).',
            'NEAREST KNOWN ISSUES (straight-line distance, not driving time; from `reports`, `operations_reports`, `work_orders` that have coordinates):',
        ];

        if ($top === []) {
            $lines[] = '- No geo-tagged issues were found in the sampled database rows. Suggest the user check Live Map or file a new report.';

            return implode("\n", $lines);
        }

        $n = 1;
        foreach ($top as $item) {
            $lines[] = $n.'. ~'.number_format($item['km'], 1).' km — '.$item['kind'].' — '.$item['summary'];
            $n++;
        }

        return implode("\n", $lines);
    }

    /**
     * @param  list<array{km: float, kind: string, summary: string}>  $candidates
     */
    private function pushGeoCandidate(array &$candidates, float $userLat, float $userLng, object $row, string $kind, callable $summarize): void
    {
        $lat = isset($row->latitude) ? (float) $row->latitude : 0.0;
        $lng = isset($row->longitude) ? (float) $row->longitude : 0.0;
        if ($lat === 0.0 && $lng === 0.0) {
            return;
        }
        $km = $this->haversineKm($userLat, $userLng, $lat, $lng);
        $summary = (string) $summarize($row);
        if (strlen($summary) > 280) {
            $summary = substr($summary, 0, 277).'…';
        }
        $candidates[] = ['km' => $km, 'kind' => $kind, 'summary' => $summary];
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earth * $c;
    }

    /**
     * @return array{
     *   ok: true,
     *   result_type: string,
     *   rows: list<object|string>,
     *   truncated: bool,
     *   row_count: int,
     *   affected_rows: int,
     *   success: bool
     * }|array{ok: false, error: string}
     */
    public function runSql(string $connectionName, string $sql, int $maxRows): array
    {
        $conn = $this->resolveConnection($connectionName);
        if ($conn === '') {
            return [
                'ok' => false,
                'error' => 'Unknown connection. Use one of: '.implode(', ', $this->connectionNames()).', or omit for default `'.Config::get('database.default').'`.',
            ];
        }

        $sql = trim($sql);
        if ($sql === '') {
            return ['ok' => false, 'error' => 'Empty SQL.'];
        }

        $core = rtrim($sql, " \t\n\r\0\x0B;");
        $isSelect = (bool) preg_match('/^SELECT\s/is', $core);

        try {
            if ($isSelect) {
                $limited = $this->ensureSelectLimit($core, $maxRows);
                $rows = DB::connection($conn)->select($limited);
                $truncated = count($rows) >= $maxRows;

                return [
                    'ok' => true,
                    'result_type' => 'select',
                    'rows' => array_values($rows),
                    'truncated' => $truncated,
                    'row_count' => count($rows),
                    'affected_rows' => 0,
                    'success' => true,
                ];
            }

            $affectedRows = DB::connection($conn)->affectingStatement($core);

            return [
                'ok' => true,
                'result_type' => 'mutation',
                'rows' => [],
                'truncated' => false,
                'row_count' => 0,
                'affected_rows' => max(0, (int) $affectedRows),
                'success' => true,
            ];
        } catch (Throwable $e) {
            Log::warning('ZiqahDatabaseBridge: query failed', ['connection' => $conn, 'error' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'Query failed: '.$e->getMessage()];
        }
    }

    private function ensureSelectLimit(string $sql, int $max): string
    {
        $t = rtrim(trim($sql), ';');
        if (preg_match('/\bLIMIT\s+[0-9]+\s*$/i', $t)) {
            return $t;
        }

        return $t.' LIMIT '.$max;
    }

    private function safeDatabaseLabel(?string $name): string
    {
        if ($name === null || $name === '') {
            return '';
        }

        return preg_match('/^[a-zA-Z0-9_.-]+$/', $name) ? $name : '';
    }

    /**
     * @return list<string>
     */
    private function listTables(Connection $connection, string $driver): array
    {
        if ($driver === 'sqlite') {
            $rows = $connection->select(
                "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
            );

            return array_map(fn ($r) => (string) $r->name, $rows);
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $db = $connection->getDatabaseName();
            $rows = $connection->select(
                'SELECT TABLE_NAME AS name FROM information_schema.tables WHERE table_schema = ? AND table_type = \'BASE TABLE\' ORDER BY TABLE_NAME',
                [$db]
            );

            return array_map(fn ($r) => (string) $r->name, $rows);
        }

        if ($driver === 'pgsql') {
            $rows = $connection->select(
                "SELECT tablename AS name FROM pg_catalog.pg_tables WHERE schemaname NOT IN ('pg_catalog', 'information_schema') ORDER BY tablename"
            );

            return array_map(fn ($r) => (string) $r->name, $rows);
        }

        if ($driver === 'sqlsrv') {
            $rows = $connection->select(
                'SELECT TABLE_NAME AS name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = \'BASE TABLE\' ORDER BY TABLE_NAME'
            );

            return array_map(fn ($r) => (string) $r->name, $rows);
        }

        return [];
    }

    /**
     * @return list<string>
     */
    private function listColumns(Connection $connection, string $driver, string $table): array
    {
        if (! preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            return [];
        }

        try {
            if ($driver === 'sqlite') {
                // $table is restricted to [a-zA-Z0-9_]
                $rows = $connection->select('PRAGMA table_info('.$table.')');

                return array_map(fn ($r) => (string) $r->name, $rows);
            }

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                $db = $connection->getDatabaseName();
                $rows = $connection->select(
                    'SELECT COLUMN_NAME AS name FROM information_schema.columns WHERE table_schema = ? AND table_name = ? ORDER BY ORDINAL_POSITION',
                    [$db, $table]
                );

                return array_map(fn ($r) => (string) $r->name, $rows);
            }

            if ($driver === 'pgsql') {
                $rows = $connection->select(
                    'SELECT column_name AS name FROM information_schema.columns WHERE table_name = ? ORDER BY ordinal_position',
                    [$table]
                );

                return array_map(fn ($r) => (string) $r->name, $rows);
            }

            if ($driver === 'sqlsrv') {
                $rows = $connection->select(
                    'SELECT COLUMN_NAME AS name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? ORDER BY ORDINAL_POSITION',
                    [$table]
                );

                return array_map(fn ($r) => (string) $r->name, $rows);
            }
        } catch (Throwable) {
            return [];
        }

        return [];
    }
}
