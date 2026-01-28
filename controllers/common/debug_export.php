<?php

use Tygh\Debugger;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Debug Export controller.
 *
 * Usage
 *  1. Enable DEBUG_MODE.
 *  2. Obtain debug hash.
 *  3. Visit the URL: index.php?dispatch=debug_export.debug_export&debugger_hash=XXXX
 *
 * Get the hash from the debugger toolbar (the same value used in SQL/logging tabs).
 */

if ($mode === 'debug_export') {
    // Require debugger to be active (DEBUG_MODE/cookie/token) to avoid leaking data unintentionally.
    if (!Debugger::isActive()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'Debugger is not active. Enable DEBUG_MODE and reload the page with the debugger toolbar.',
        ]);
        exit;
    }

    $hash = isset($_REQUEST['debugger_hash']) ? (string) $_REQUEST['debugger_hash'] : '';

    if ($hash === '') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'Missing debugger_hash. Load the slow page with debugger on, then use the hash shown in the toolbar.',
        ]);
        exit;
    }

    $data = Debugger::getData($hash);

    if (empty($data)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'No debugger data found for this hash. The data may have expired; reload the page with debugger on and try again.',
        ]);
        exit;
    }

    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="debug-report-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $hash) . '.json"');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

