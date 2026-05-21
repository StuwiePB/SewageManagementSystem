<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\DmsFormDateTime;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::query()->with('user');

        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        if ($request->filled('datetime_from')) {
            $from = DmsFormDateTime::parse($request->datetime_from);
            if ($from) {
                $query->where('created_at', '>=', $from);
            }
        }

        if ($request->filled('datetime_to')) {
            $to = DmsFormDateTime::parse($request->datetime_to);
            if ($to) {
                if (strlen($request->datetime_to) <= 10) {
                    $to = $to->endOfDay();
                }
                $query->where('created_at', '<=', $to);
            }
        }

        $logs = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(25)->withQueryString();

        $stats = [
            'today' => AuditLog::whereDate('created_at', today())->count(),
            'admin' => AuditLog::where('area', 'admin')->count(),
            'operations' => AuditLog::where('area', 'operations')->count(),
            'total' => AuditLog::count(),
        ];

        $staffUsers = User::query()
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN, User::ROLE_OPERATOR])
            ->orderBy('name')
            ->get(['id', 'name', 'staffname', 'role']);

        $actionOptions = config('audit.action_labels', []);

        return view('r_admin.audit-log.index', compact('logs', 'staffUsers', 'actionOptions', 'stats'));
    }
}
