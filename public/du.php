<?php
require_once __DIR__ . '/../include.php';
$limit = isset($_GET['limit']) ? $_GET['limit'] : 100;
$tree = [
    'name' => '.',
    'size' => null,
    'children' => [],
];
$file = getLatestLog();

eachLine($file, $limit, function($size, $path) use(&$tree) {
    $paths = explode('/', $path);
    $root =& $tree['children'];
    foreach ($paths as $node) {
        if (!isset($root[$node])) {
            $root[$node] = [
                'name' => $node,
                'size' => $size,
                'children' => [],
            ];
        }
        $root =& $root[$node]['children'];
    }
});

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

echo json_encode($tree, JSON_PRETTY_PRINT);
