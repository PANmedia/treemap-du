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
    if ($line[1] === '/') {
        $tree['size'] = $line[0];
        continue;
    }
    $paths = explode('/', trim($line[1], '/'));
    if (count($paths) > $limit) {
        continue;
    }
    $root =& $tree['children'];
    for ($i = 0, $count = count($paths) - 1; $i <= $count; $i++) {
        if (!isset($root[$paths[$i]])) {
            $root[$paths[$i]] = [
                'path' => $paths[$i],
                'name' => null,
                'size' => null,
                'children' => [],
            ];
        }
        if ($i != $count) {
            $root =& $root[$paths[$i]]['children'];
        }
    }
    $root[$paths[$i - 1]]['size'] = $line[0];
    $root[$paths[$i - 1]]['name'] = $line[1];
}

function format($bytes, $decimal_places = 2) {
    $suffix = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    for ($i = 0; $bytes > 1024 && $i < 8; $i++) {
        $bytes /= 1024;
    }
    return round($bytes, $decimal_places).' '.$suffix[$i];
}

function renderTree($node, $total) {
    $children = '';
    usort($node['children'], function($a, $b) {
        return $b['size'] - $a['size'];
    });
    foreach ($node['children'] as $child) {
        $children .= renderTree($child, $total) . PHP_EOL;
    }
    $percent = number_format(100 / $total * $node['size']);
    $size = format($node['size'] * 1024);
    return "
        <li class='node'>
            <div class='name'>{$node['name']} ($size) $percent%</div>
            <ul class='children'>$children</ul>
        </li>
    ";
}
?>

<ul>
    <?= renderTree($tree, $tree['size']); ?>
</ul>

<script type="text/javascript" src="//code.jquery.com/jquery-2.1.1.min.js"></script>
<script>
    $('body').on('click', '.name', function() {
        $(this).next().toggle();
    });
</script>
