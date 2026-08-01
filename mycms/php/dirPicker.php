<?php

require_once __DIR__ . "/functions.php";
confirmLoggedIn();
accessControl("superAdmin,admin");

header('Content-Type: application/json; charset=utf-8');

function is_windows() {
    return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
}

function list_drives() {
    $drives = [];
    foreach (range('C', 'Z') as $letter) {
        if (@is_dir($letter . ':\\')) {
            $drives[] = $letter . ':\\';
        }
    }
    return $drives;
}

function sanitize_path($path) {
    $path = realpath($path);
    return $path ?: null;
}

$req_path = isset($_GET['path']) ? (string)$_GET['path'] : '';
$dirs = [];
$current_path = '';

if (empty($req_path) || $req_path === 'ROOT') {
    $current_path = 'ROOT';
    $items = is_windows() ? list_drives() : ['/'];
    foreach ($items as $item) {
        $dirs[] = ['name' => $item, 'path' => $item];
    }
} else {
    $current_path = sanitize_path($req_path);
    if (!$current_path || !is_dir($current_path)) {
        echo json_encode(['ok' => false, 'error' => 'Invalid or inaccessible path.']);
        exit;
    }

    $items = @scandir($current_path) ?: [];
    foreach ($items as $name) {
        if ($name === '.' || $name === '..') continue;
        $full_path = $current_path . DIRECTORY_SEPARATOR . $name;
        if (is_dir($full_path) && is_readable($full_path)) {
            $dirs[] = ['name' => $name, 'path' => $full_path];
        }
    }
}

echo json_encode(['ok' => true, 'path' => $current_path, 'dirs' => $dirs]);