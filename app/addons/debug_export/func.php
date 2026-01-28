<?php

use Tygh\Debugger;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

/**
 * Run debug export before any controller output so the response is pure JSON.
 * Handles dispatch=debug_export.debug_export and exits; avoids .part / failed downloads.
 *
 * @param string $controller
 * @param string $mode
 * @param string $action
 * @param string $dispatch_extra
 * @param string $area
 */
function fn_debug_export_before_dispatch($controller, $mode, $action, $dispatch_extra, $area)
{
    if ($controller !== 'debug_export' || $mode !== 'debug_export') {
        return;
    }

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

    set_time_limit(0);
    if (function_exists('ini_set')) {
        @ini_set('memory_limit', '512M');
    }

    $data = Debugger::getData($hash);
    if (empty($data)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'No debugger data found for this hash. The data may have expired; reload the page with debugger on and try again.',
        ]);
        exit;
    }

    while (ob_get_level()) {
        ob_end_clean();
    }
    if (function_exists('ini_set')) {
        @ini_set('zlib.output_compression', 'Off');
    }
    if (!headers_sent()) {
        header_remove('Content-Encoding');
        header_remove('Vary');
    }

    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Failed to encode debug data.']);
        exit;
    }

    $filename = 'debug-report-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $hash) . '.json';
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($json));
    header('Cache-Control: no-store');
    echo $json;
    exit;
}
