<?php

function getLogPath() {
    return __DIR__ . '/logs';
}

function getAllLogs() {
    $logs = glob(getLogPath() . '/*.txt');
    sort($logs);
    return $logs;
}

function get3Logs() {
    $logs = getAllLogs();
    sort($logs);
    $result = [];
    $result[] = reset($logs);
    $result[] = $logs[floor(count($logs) / 2)];
    $result[] = end($logs);
    return $result;
}

function getLatestLog() {
    $logs = getAllLogs();
    rsort($logs);
    return $logs[0];
}

function eachLine($file, $limit) {
    $du = file_get_contents($file);
    $fp = fopen($file, 'r');
    while ($line = fgetcsv($fp, 1024, "\t")) {
        yield $line;
    }
}

function getTimeStamp($file) {
    $timestamp = basename($file);
    if (preg_match('/du_.*?([0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}-[0-9]{2}-[0-9]{2})/', $timestamp, $matches)) {
        return DateTime::createFromFormat('Y-m-d_H-i-s', $matches[1])->format('U');
    }
    return null;
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
