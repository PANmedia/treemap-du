<?php
$path = __DIR__ . '/logs';
if (!is_dir($path)) {
    mkdir($path);
}
if (!is_dir($path)) {
    die('Could not create logs directory: ' . $path);
}
exec('du', $du);
$du = implode(PHP_EOL, $du);
$file = $path . '/du_' . date('Y-m-d_H-i-s') . '.txt';
file_put_contents($file, $du);
echo 'Wrote ' . $file . PHP_EOL;
