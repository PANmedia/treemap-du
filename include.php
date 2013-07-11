<?php

function getLogPath() {
    return __DIR__ . '/logs';
}

function getAllLogs() {
    return glob(getLogPath() . '/*.txt');
}

function eachLine($file, $limit, $callback) {
    $du = file_get_contents($file);
    foreach (explode("\n", $du) as $line) {
        if (!$line) {
            continue;
        }
        if (preg_match('/([0-9]+)\s+(.*)/', trim($line), $matches)) {
            $size = $matches[1];
            if ($size < $limit) {
                continue;
            }
            $path = trim($matches[2], '/.');
            if (!$path) {
                continue;
            }
            $callback((int) $size * 1000, $path);
        }
    }
}

function getTimeStamp($file) {
    $timestamp = basename($file);
    $timestamp = str_replace('du_', '', $timestamp);
    $timestamp = str_replace('.txt', '', $timestamp);
    return DateTime::createFromFormat('Y-m-d_H-i-s', $timestamp)->format('U');
}

/**
 * Find position of Nth $occurrence of $needle in $haystack
 * Starts from the beginning of the string
**/
function strpos_offset($needle, $haystack, $occurrence) {
  // explode the haystack
  $arr = explode($needle, $haystack);
  // check the needle is not out of bounds
  switch( $occurrence ) {
    case $occurrence == 0:
      return false;
    case $occurrence > max(array_keys($arr)):
      return false;
    default:
      return strlen(implode($needle, array_slice($arr, 0, $occurrence)));
  }
}
