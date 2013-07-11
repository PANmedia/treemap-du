<?php
//die(file_get_contents(__DIR__ . '/flare.json'));
$du = file_get_contents(__DIR__ . '/du.txt');
$tree = [
    'name' => '.',
    'size' => null,
    'children' => [],
];
foreach (explode("\n", $du) as $line) {
    if (!$line) {
        continue;
    }
    if (preg_match('/([0-9]+)\s+(.*)/', trim($line), $matches)) {
        $size = $matches[1];
        if ($size < 100) {
            continue;
        }
        $path = trim($matches[2], '/.');
        if (!$path) {
            continue;
        }
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

echo json_encode($tree, JSON_PRETTY_PRINT);
