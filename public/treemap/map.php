<?php
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 4;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
        <link type="text/css" rel="stylesheet" href="style.css"/>
        <script type="text/javascript" src="//code.jquery.com/jquery-2.1.1.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/d3/3.4.11/d3.js"></script>
        <style type="text/css">
            .chart {
                display: block;
                margin: auto;
                margin-top: 40px;
            }

            text {
                font-size: 11px;
            }

            rect {
                fill: none;
            }

            .center {
                text-align: center;
            }
        </style>
    </head>
  <body>
    <div id="body">
        <form class="center" action="" method="get">
            <label>Limit: </label>
            <input type="number" name="limit" value="<?= $limit; ?>" />
            <button type="submit">Submit</button>
            <div id="path"></div>
        </form>
        <div id="footer">
            d3.layout.treemap
            <div class="hint">click or option-click to descend or ascend</div>
            <div>
                <select>
                    <option value="size">Size</option>
                    <option value="count">Count</option>
                </select>
            </div>
        </div>
    </div>
    <script type="text/javascript">
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

        var w = $(window).width() - 100,
            h = 800 - 180,
            x = d3.scale.linear().range([0, w]),
            y = d3.scale.linear().range([0, h]),
            color = d3.scale.category20c(),
            root,
            node;

        var treemap = d3.layout.treemap()
            .round(false)
            .size([w, h])
            .sticky(true)
            .value(function(d) { return d.size; });

        var svg = d3.select("#body").append("div")
            .attr("class", "chart")
            .style("width", w + "px")
            .style("height", h + "px")
          .append("svg:svg")
            .attr("width", w)
            .attr("height", h)
          .append("svg:g")
            .attr("transform", "translate(.5,.5)");

        d3.json("du.php?limit=<?= $limit; ?>", function(data) {
            node = root = data;

            var nodes = treemap.nodes(root)
                .filter(function(d) { return !d.children; });

            var cell = svg.selectAll("g")
                    .data(nodes)
                .enter().append("svg:g")
                    .attr("class", "cell")
                    .attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; })
                    .on("mouseover", function(d) {
                        document.getElementById('path').innerText = humanFileSize(d.size) + ' ' + d.name;
                    })
                    .on("click", function(d) { return zoom(node == d.parent ? root : d.parent); });

            cell.append("svg:rect")
                .attr("width", function(d) { return d.dx - 1; })
                .attr("height", function(d) { return d.dy - 1; })
                .style("fill", function(d) { return color(d.parent.name); });

            cell.append("svg:text")
                .attr("x", function(d) { return d.dx / 2; })
                .attr("y", function(d) { return d.dy / 2; })
                .attr("dy", ".35em")
                .attr("text-anchor", "middle")
                .text(function(d) { return d.name; })
                .style("opacity", function(d) { d.w = this.getComputedTextLength(); return d.dx > d.w ? 1 : 0; });

            d3.select(window).on("click", function() { zoom(root); });

            d3.select("select").on("change", function() {
                treemap.value(this.value == "size" ? size : count).nodes(root);
                zoom(node);
            });
        });

        function size(d) {
            return d.size;
        }

        function count(d) {
            return 1;
        }

        function zoom(d) {
            var kx = w / d.dx, ky = h / d.dy;
            x.domain([d.x, d.x + d.dx]);
            y.domain([d.y, d.y + d.dy]);

            var t = svg.selectAll("g.cell").transition()
                .duration(d3.event.altKey ? 7500 : 750)
                .attr("transform", function(d) { return "translate(" + x(d.x) + "," + y(d.y) + ")"; });

            t.select("rect")
                .attr("width", function(d) { return kx * d.dx - 1; })
                .attr("height", function(d) { return ky * d.dy - 1; })

            t.select("text")
                .attr("x", function(d) { return kx * d.dx / 2; })
                .attr("y", function(d) { return ky * d.dy / 2; })
                .style("opacity", function(d) { return kx * d.dx > d.w ? 1 : 0; });

            node = d;
            d3.event.stopPropagation();
        }
    </script>
  </body>
</html>
