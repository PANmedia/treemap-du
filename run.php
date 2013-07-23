<?php
$path = __DIR__ . '/logs';
if (!is_dir($path)) {
    mkdir($path);
}
if (!is_dir($path)) {
    die('Could not create logs directory: ' . $path);
}
$cwd = '.';
if (isset($_SERVER['argv'][1])) {
    $cwd = $_SERVER['argv'][1];
}
$cmd = 'du ' . $cwd;
echo 'Running ' . $cmd . PHP_EOL;
exec($cmd, $output);
$output = implode(PHP_EOL, $output);
$file = $path . '/du_' . date('Y-m-d_H-i-s') . '.txt';
file_put_contents($file, $output);
echo 'Wrote ' . $file . PHP_EOL . PHP_EOL;

$cmd = 'df -h';
echo 'Running ' . $cmd . PHP_EOL;
exec($cmd, $output);
$path = __DIR__ . '/public';
$output = implode(PHP_EOL, $output);
$file = $path . '/df.txt';
file_put_contents($file, $output);
echo 'Wrote ' . $file . PHP_EOL . PHP_EOL;
