<?php
    require_once __DIR__ . '/../include.php';
    $depth = isset($_GET['depth']) ? $_GET['depth'] : 0;
    $data = [];
    foreach (getAllLogs() as $log) {
        $row = [];
        eachLine($log, 0, function($size, $path) use(&$row, $depth) {
            if (substr_count($path, '/') == $depth) {
                // substr($path, 0, strpos_offset('/', $path, $depth))
                $row[$path] = $size;
            }
        });
        $data[getTimeStamp($log)] = $row;
    }
    $categories = [];
    $series = [];
    foreach ($data as $time => $rows) {
        $categories[] = date('Y-m-d H:i:s', $time);
        foreach ($rows as $path => $row) {
            if (!isset($series[$path])) {
                $series[$path] = [
                    'name' => $path,
                    'data' => [],
                ];
            }
            $series[$path]['data'][] = $row;
        }
    }
    $series = array_values($series);
?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
<script src="http://code.highcharts.com/highcharts.js"></script>
<script src="http://code.highcharts.com/modules/exporting.js"></script>
<form action="" method="get">
    <label>Depth: </label>
    <input type="number" name="depth" value="<?= $depth; ?>" />
    <button type="submit">Submit</button>
</form>
<div id="container" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
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
                categories: <?= json_encode($categories); ?>,
                tickmarkPlacement: 'on',
                title: {
                    enabled: false
                }
            },
            yAxis: {
                title: {
                    text: 'MB'
                },
                labels: {
                    formatter: function() {
                        return this.value / 1000 / 1000;
                    }
                }
            },
            tooltip: {
                shared: true,
                formatter: function() {
                    var result = [];
                    for (var i = 0; i < this.points.length; i++) {
                        result.push(this.points[i].series.name + ' ' + humanFileSize(this.points[i].y));
                    }
                    return result.join('<br/>');
                }
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
            series: <?= json_encode($series); ?>
        });
    });
</script>
