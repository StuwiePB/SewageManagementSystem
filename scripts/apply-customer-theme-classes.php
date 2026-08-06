<?php

$root = dirname(__DIR__) . '/resources/views/r_customer';
$files = array_merge(
    glob($root . '/*.blade.php') ?: [],
    glob($root . '/partials/*.blade.php') ?: []
);

$replacements = [
    'min-height: 220px; border-radius: 10px; background: rgba(66, 106, 120, 0.16); border: 0.7px solid rgba(255, 255, 255, 0.12); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px);' => 'class="cust-settings-card" style="min-height: 220px; border-radius: 10px; backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px);',
    'min-height: 100px; border-radius: 10px; background: rgba(66, 106, 120, 0.16); border: 0.7px solid rgba(255, 255, 255, 0.12); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px);' => 'class="cust-settings-card" style="min-height: 100px; border-radius: 10px; backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px);',
    'min-height: 200px; border-radius: 10px; background: rgba(66, 106, 120, 0.16); border: 0.7px solid rgba(255, 255, 255, 0.12); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px);' => 'class="cust-settings-card" style="min-height: 200px; border-radius: 10px; backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px);',
    'border-radius: 12px; background: rgba(66, 106, 120, 0.16); border: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px);' => 'class="cust-list-row cust-glass" style="border-radius: 12px; backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px);',
    'position: relative; border-radius: 12px; background: rgba(66, 106, 120, 0.16); border: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px);' => 'class="cust-glass cust-list-row" style="position: relative; border-radius: 12px; backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px);',
    'background: rgba(66, 106, 120, 0.16); outline: 1.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); text-decoration: none;' => 'class="tab-btn" style="backdrop-filter: blur(1.5px); text-decoration: none; outline: 1.7px solid var(--brudms-chip-border);',
    'background: var(--brudms-primary); backdrop-filter: blur(1.5px); text-decoration: none;">' => 'class="tab-btn tab-btn-active" style="backdrop-filter: blur(1.5px); text-decoration: none;">',
    'stroke="#22d3ee"' => 'stroke="var(--accent-blue)"',
    'background: #22d3ee !important' => 'background: var(--accent-blue) !important',
    'background: #22d3ee;' => 'background: var(--accent-blue);',
    'background: #2acbff' => 'background: var(--accent-blue)',
    'color: #22d3ee' => 'color: var(--accent-blue)',
    'outline-color: var(--brudms-primary) !important;' => 'outline-color: var(--accent-blue) !important;',
    '.tab-btn:hover { color: var(--brudms-primary); }' => '.tab-btn:hover { color: var(--accent-blue); }',
    'class="tab-btn-active" style="flex: 1; height: 43px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--brudms-primary);' => 'class="tab-btn tab-btn-active" style="flex: 1; height: 43px; display: flex; align-items: center; justify-content: center; border-radius: 12px;',
    'id="confirm-choice-btn" class="press-btn"' => 'id="confirm-choice-btn" class="press-btn cust-btn-confirm cust-btn-primary"',
    'id="confirm-photo-btn" class="press-btn"' => 'id="confirm-photo-btn" class="press-btn cust-btn-confirm cust-btn-primary"',
    'id="confirm-location-btn" class="press-btn"' => 'id="confirm-location-btn" class="press-btn cust-btn-confirm cust-btn-primary"',
    'id="confirm-details-btn" class="press-btn"' => 'id="confirm-details-btn" class="press-btn cust-btn-confirm cust-btn-primary"',
    'id="submit-report-btn"' => 'id="submit-report-btn" class="cust-btn-confirm cust-btn-primary"',
    'class="press-btn delayed-nav bottom-bar-btn"' => 'class="press-btn delayed-nav bottom-bar-btn cust-btn-secondary"',
    'background: rgba(66, 106, 120, 0.16); border: 1.2px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); color: white;' => 'class="cust-field" style="backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px);',
    'class="input-pill" style="flex: 1; height: 44px; border-radius: 9999px; border: none; outline: 1.7px solid rgba(255, 255, 255, 0.21); background: rgba(66, 106, 120, 0.16);' => 'class="input-pill cust-chat-input-wrap" style="flex: 1; height: 44px; border-radius: 9999px; border: none; outline: 1.7px solid var(--brudms-chip-border);',
    'id="chat-send" style="width: 44px; height: 44px; border-radius: 9999px; border: none; background: var(--brudms-primary);' => 'id="chat-send" class="cust-chat-send" style="width: 44px; height: 44px; border-radius: 9999px; border: none;',
    'id="chat-input" type="text" placeholder="Type a message..." style="flex: 1; height: 44px; border: none; outline: none; background: transparent; padding: 0 16px 0 0; color: white;' => 'id="chat-input" class="cust-chat-input" type="text" placeholder="Type a message..." style="flex: 1; height: 44px; border: none; outline: none; background: transparent; padding: 0 16px 0 0;',
    'background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px;' => 'class="faq-item cust-glass" style="border-radius: 10px;',
    'color: white; font-size: 16px; font-weight: 600; font-family: Poppins, sans-serif;' => 'class="cust-title" style="font-size: 16px; font-weight: 600; font-family: Poppins, sans-serif;',
    'color: white; font-size: 17px; font-family: Poppins, sans-serif; font-weight: 600;' => 'class="cust-title" style="font-size: 17px; font-family: Poppins, sans-serif; font-weight: 600;',
    'color: white; font-size: 13px; font-family: Poppins, sans-serif; font-weight: 600;' => 'class="cust-subtitle" style="font-size: 13px; font-family: Poppins, sans-serif; font-weight: 600;',
    'color: white; font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;' => 'class="cust-text" style="font-size: 11px; font-weight: 600; font-family: Poppins, sans-serif; flex: 1;',
    'style="width: 16px; height: 16px; color: white; flex-shrink: 0;' => 'class="faq-chevron" style="width: 16px; height: 16px; flex-shrink: 0; color: var(--text-primary);',
    'position: relative; width: 100%; margin-top: 6px; height: 15vh; min-height: 132px; border-radius: 9px; background: rgba(66, 106, 120, 0.16); outline: 0.7px solid rgba(255, 255, 255, 0.21);' => 'class="cust-stat-panel" style="position: relative; width: 100%; margin-top: 6px; height: 15vh; min-height: 132px; border-radius: 9px;',
    'position: relative; width: 100%; margin-top: 6px; height: min(28vh, 240px); min-height: 200px; border-radius: 9px; background: rgba(66, 106, 120, 0.16); outline: 0.7px solid rgba(255, 255, 255, 0.21);' => 'class="cust-stat-panel" style="position: relative; width: 100%; margin-top: 6px; height: min(28vh, 240px); min-height: 200px; border-radius: 9px;',
    'border-radius: 8px; border: 0.7px solid rgba(255,255,255,0.21); background: rgba(66, 106, 120, 0.22); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); color: rgba(255,255,255,0.9);' => 'class="cust-stat-select" style="border-radius: 8px; backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);',
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    foreach ($replacements as $from => $to) {
        $content = str_replace($from, $to, $content);
    }
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo str_replace(dirname(__DIR__) . '/', '', $file) . PHP_EOL;
    }
}
