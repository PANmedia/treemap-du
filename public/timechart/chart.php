<?php
    require_once __DIR__ . '/../../include.php';
    set_time_limit(100);

    $cull = isset($_GET['cull']) ? (int) $_GET['cull'] : 1;
    $startPath = isset($_GET['path']) ? $_GET['path'] : '/';

    function makeRowsConsistant(&$data, $unique) {
        foreach ($data as $key => &$rows) {
            foreach ($unique as $u) {
                if (!isset($rows[$u])) {
                    $rows[$u] = 0;
                }
            }
        }
    }

    function cullSmallDirs(&$data, $end, $total, $cull, $unique) {
        $cullKeys = [];
        foreach ($data[$end] as $i => $final) {
            if ($final / $total * 100 < $cull) {
                $cullKeys[$i] = $i;
            }
        }
        foreach ($data as $key => &$rows) {
            foreach ($unique as $u) {
                if (isset($cullKeys[$u])) {
                    unset($rows[$u]);
                }
            }
        }
        return $cullKeys;
    }

    function sortBySize($data, $end) {
        $sorted = [];
        arsort($data[$end]);
        $order = array_keys($data[$end]);
        foreach ($data as $i => $rows) {
            $sorted[$i] = [];
            foreach ($order as $j) {
                $sorted[$i][$j] = $rows[$j];
            }
        }
        return $sorted;
    }

    function createChartData($data, &$categories = [], &$series = []) {
        foreach ($data as $time => $rows2) {
            $categories[] = date('Y-m-d H:i:s', $time);
            foreach ($rows2 as $path => $row2) {
                if (!isset($series[$path])) {
                    $series[$path] = [
                        'name' => $path,
                        'data' => [],
                    ];
                }
                $series[$path]['data'][] = (int) $row2;
            }
        }
        $series = array_values($series);
    }

    function getData($cull, $startPath, $logs) {
        if (!preg_match('@^[a-z0-9-_ ./\\\]+$@i', $startPath)) {
            die('Invalid path');
        }
        $startPath = '/' . trim($startPath, '/');
        $startDepth = substr_count($startPath, '/') + 1;

        $key = [$cull, $startPath];
        foreach ($logs as $log) {
            $key[] = filemtime($log);
        }
        $cacheFile = __DIR__ . '/../cache/' . md5(implode(':', $key)) . '.json';
        if (is_file($cacheFile)) {
            return json_decode(file_get_contents($cacheFile), true);
        }

        $data = [];
        $unique = [];
        foreach ($logs as $log) {
            $row = [];
            $fp = fopen($log, 'r');
            while ($line = fgetcsv($fp, 1024, "\t")) {
                if (strpos($line[1], $startPath) === 0 && substr_count($line[1], '/') == $startDepth) {
                    $row[$line[1]] = $line[0];
                    $unique[$line[1]] = $line[1];
                }
            }
            fclose($fp);
            if (!empty($row)) {
                $data[getTimeStamp($log)] = $row;
            }
        }
        if (!empty($data)) {
            $end = end($data);
            $end = key($data);
            $total = 0;
            foreach ($data[$end] as $final) {
                $total += $final;
            }

            // Make rows consistant
            makeRowsConsistant($data, $unique);

            // Cull small dirs
            $cullKeys = cullSmallDirs($data, $end, $total, $cull, $unique);

            // Sort by size
            $data = sortBySize($data, $end);

            createChartData($data, $categories, $series);
        }

        $result = [
            'categories' => $categories,
            'series' => $series,
            'unique' => $unique,
            'cullKeys' => $cullKeys,
        ];

        //file_put_contents($cacheFile, json_encode($result, JSON_PRETTY_PRINT));
        return $result;
    }
    $logs = isset($_GET['all']) ? getAllLogs() : get3Logs();
    $data = getData($cull, $startPath, $logs);
?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
<script src="//code.highcharts.com/highcharts.js"></script>
<script src="//code.highcharts.com/modules/exporting.js"></script>
<style type="text/css">
    #sidebar {
        width: 20%;
        height: 100%;
        float: left;
    }
    #container {
        width: 80%;
        height: 100%;
        float: left;
        margin: 0 auto;
    }
    pre {
        overflow: auto;
    }
</style>
<div id="sidebar">
    <form action="" method="get">
        <label>Cull: </label>
        <input type="number" name="cull" value="<?= $cull; ?>" /> %<br/>
        <label>Path: </label>
        <input type="text" name="path" value="<?= $startPath; ?>" /><br/>
        <input type="checkbox" name="all" value="true" />
        <label> Include all logs</label><br/>
        <button type="submit">Submit</button>
        <?php
            foreach ($data['unique'] as $u) {
                if (!isset($data['cullKeys'][$u])) {
                    $u2 = basename($u);
                    echo "<br/><a href='?path=$u'>$u2</a>";
                }
            }
        ?>
    </form>
</div>
<?php if (!empty($data['series'])): ?>
    <div id="container"></div>
    <script>
        function humanFileSize(bytes, si) {
            var thresh = si ? 1000 : 1024;
            if(bytes < thresh) return bytes + ' B';
            var units = si ? ['kB','MB','GB','TB','PB','EB','ZB','YB'] : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];
            var u = -1;
            do {
                bytes /= thresh;
                ++u;
            } while(bytes >= thresh);
            return bytes.toFixed(1)+' '+units[u];
        };
        $(function () {
            $('#container').highcharts({
                chart: {
                    type: 'area'
                },
                title: {
                    text: 'HDD Usage Over Time'
                },
                subtitle: {
                    text: ''
                },
                xAxis: {
                    categories: <?= json_encode($data['categories']); ?>,
                    tickmarkPlacement: 'on',
                    title: {
                        enabled: false
                    }
                },
                tooltip: {
                    shared: true
                },
                plotOptions: {
                    series: {
                        marker: {
                            enabled: false
                        }
                    },
                    area: {
                        stacking: 'normal',
                        lineColor: '#666666',
                        lineWidth: 1,
                        marker: {
                            lineWidth: 1,
                            lineColor: '#666666'
                        }
                    }
                },
                series: <?= json_encode($data['series']); ?>
            });
        });
    </script>
<?php endif; ?>
