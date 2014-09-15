<?php
require_once __DIR__ . '/../../include.php';
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 4;
$tree = [
    'name' => '{root}',
    'size' => null,
    'children' => [],
];
$file = getLatestLog();

foreach (eachLine($file) as $line) {
    $paths = explode('/', $line[1]);
    if (count($paths) > $limit) {
        continue;
    }
    $root =& $tree['children'];
    foreach ($paths as $node) {
        if (!isset($root[$node])) {
            $root[$node] = [
                'name' => $line[1],
                'size' => $line[0],
                'children' => [],
            ];
        }
        $root =& $root[$node]['children'];
    }
}

function trimTree(&$tree) {
    if (sizeof($tree['children']) == 0) {
        unset($tree['children']);
    } else {
        unset($tree['size']);
        foreach ($tree['children'] as &$child) {
            trimTree($child);
        }
        $tree['children'] = array_values($tree['children']);
    }
}
trimTree($tree);

echo json_encode($tree['children'][0], JSON_PRETTY_PRINT);
