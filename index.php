<?php

$configFile = __DIR__ . DIRECTORY_SEPARATOR . 'config.json';
if (!is_readable($configFile)) {
    echo 'could not find or read ' . $configFile . PHP_EOL;
    exit();
}

$config = json_decode(file_get_contents($configFile), true);

$fileDir = $config['factorioBaseFolder'] . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, 'script-output/statistic/forces/');

if (!is_dir($fileDir)) {
    echo 'could not find folder: ' . $fileDir . ' is it possible, that you entered the wrong factorio base folder?' . PHP_EOL;
    exit();
}


if (PHP_SAPI !== 'cli') {
    $cacheFile = __DIR__ . DIRECTORY_SEPARATOR . 'statsLive.html';
    if (isset($_GET['update']) || !file_exists($cacheFile)) {
        exec('php ' . __FILE__ . ' > ' . $cacheFile);
    }
    return readfile($cacheFile);
}

require_once 'analyse.php';


function getTypeName($n)
{
    switch ($n) {
        default:
            return 'NOT DEFINED';
        case 'fluid_production_statistics':
            return 'Flüssige Rohstoff Statistik';
        case 'item_resource_statistics':
            return 'Rohstoff Förderung';
        case 'fluid_resource_statistics':
            return 'Flüssige Rohstoff Förderung';
        case 'kill_count_statistics':
            return 'Kill Statistik';
        case 'item_production_statistics':
            return 'Item Statistik';
        case 'entity_build_count_statistics':
            return 'Gebäude Bau Statistik';
    }
}

$forces = getForces();


$max = count($forces);

$hash = getCompareHashes($forces);

//echo "<pre>";
//print_r($hash);
//exit;

function getAllItemNames(array $hash)
{
    $h = array();
    foreach ($hash as $type => $items) {
        $h = array_merge($h, array_keys($items));
    }
    return array_unique($h);
}

$allItemNames = array();
foreach (getAllItemNames($hash) as $itemName) {
    $allItemNames[$itemName] = getItemName($itemName);
}
asort($allItemNames);


?>


<html>
<head>
    <title>Factorio Statistiken</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">

    <!-- Optional theme -->
    <!--    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">-->

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"
            integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS"
            crossorigin="anonymous"></script>
    <!--    <script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>-->
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.26.5/js/jquery.tablesorter.min.js"></script>
    <link rel="stylesheet" href="http://cdn.jsdelivr.net/jquery.sidr/2.2.1/stylesheets/jquery.sidr.dark.min.css">
    <!--    <script src="https://cdnjs.cloudflare.com/ajax/libs/tablesort/4.0.1/src/sorts/tablesort.number.js"></script>-->

    <style>
        td, td span {
            line-height: 32px;
        }

        img {
            vertical-align: middle;
        }

        span.type-input {
            color: green;
        }

        span.type-output {
            color: red;
        }

        tbody.data tr:hover > td {
            background-color: #cecece;
        }

        td.td-type-input {
            border-left: 1px solid #ddd;
        }

        div.panel-item,
        #info .panel {
            padding-bottom: -30px;
            margin-bottom: 20px !important;
            height: 400px;
        }

        body {
            /*margin-left: 20px;*/
        }

        /*#filter {*/
        /*position: fixed;*/
        /*top: 0;*/
        /*left: -220px;*/
        /*bottom: 0;*/
        /*width: 220px;*/
        /*border-right: 1px solid #eee;*/
        /*background-color: white;*/
        /*/!*padding: 5px;*!/*/
        /*z-index: 500;*/
        /*}*/
        #filter-content {
            /*width: 220px;*/
            /*overflow-y: auto;*/
            /*overflow-x: hidden;*/
            /*max-height: 100%;*/
            /*z-index: 510;*/
            padding: 5px;
        }

        /*#filter-c {*/
        /*position: fixed;*/
        /*left: 0;*/
        /*content: ' ';*/
        /*width: 20px;*/
        /*bottom: 0;*/
        /*top: 0px;*/
        /*}*/
        /*#filter-c button {*/
        /*position: fixed;*/
        /*left: 0;*/
        /*height: 20px;*/
        /*width: 20px;*/
        /*top: 50%;*/
        /*}*/
        .sidr select option {
            /*width: 100%;*/
            /*font-size: 13px;*/
            /*padding: 5px;*/
            /*-moz-box-sizing: border-box;*/
            /*-webkit-box-sizing: border-box;*/
            /*box-sizing: border-box;*/
            /*/!*margin: 0 0 10px;*!/*/
            /*border-radius: 2px;*/
            /*border: 0;*/
            background: #333;
            color: rgba(255, 255, 255, 0.6);
            /*display: block;*/
            /*clear: both*/
        }

        #info .panel-heading {
            border-bottom: none;
        }
    </style>
</head>
<body>
<div id="filter" style="display: none">
    <div id="filter-content">
        <h4>Filter
            <small><input type="checkbox" id="changeAll" class="pull-right" checked="checked"/></small>
        </h4>
        <div class="form"></div>
        <select class="form-control" id="select">
            <option value=".view-type-table">Tabelle</option>
            <option value=".view-type-grid" selected="selected">Grid</option>
        </select>
        <?php
        foreach ($allItemNames as $itemName => $translation) {
            echo "<div class='checkbox'><label>";
            echo "<input checked='checked' class='item-checkbox' type='checkbox' value='{$itemName}'/>";
            echo $translation;
            echo "</label></div>";
        }
        ?>
    </div>
</div><nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="" id="showFilter">Filter</a>
        </div>

    </div>
</nav>
<div class="container-fluid">
    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
        <?php
        foreach ($hash as $type => $items) {
            ?>

            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="heading_<?= $type; ?>">
                    <h4 class="panel-title">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_<?= $type; ?>"
                           aria-expanded="true" aria-controls="collapse_<?= $type; ?>">
                            <?= getTypeName($type); ?>
                        </a>
                    </h4>
                </div>

                <div id="collapse_<?= $type; ?>" class="panel-collapse collapse" role="tabpanel"
                     aria-labelledby="heading_<?= $type; ?>">
                    <div class="panel-body">
                        <div class="view-type view-type-grid">
                            <div class="row">
                                <?php
                                $tmpItems = $items;
                                uasort($tmpItems, function (array $a, array $b) {
                                    $sumA = 0;
                                    $sumB = 0;
                                    foreach (array(
                                                 'sumA' => $a,
                                                 'sumB' => $b
                                             ) as $var => $e) {
                                        foreach ($e['forces'] as $fData) {
                                            $$var += isset($fData['input']) ? $fData['input'] : 0;
                                            $$var += isset($fData['output']) ? $fData['output'] : 0;
                                        }
                                    }
                                    return $sumB - $sumA;
                                });
                                foreach ($tmpItems as $itemName => $itemData) {
                                    $tmp = $itemData['forces'];
                                    uasort($tmp, function (array $a, array $b) {
                                        $ai = isset($a['input']) ? $a['input'] : 0;
                                        $ao = isset($a['output']) ? $a['output'] : 0;
                                        $bi = isset($b['input']) ? $b['input'] : 0;
                                        $bo = isset($b['output']) ? $b['output'] : 0;
                                        return (($ai + $ao) - ($bi + $bo)) * -1;
                                    });
                                    ?>
                                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 item-name-<?= $itemName; ?>">
                                        <div class="panel panel-default panel-item">
                                            <div class="panel-heading">
                                                <img src="<?= ltrim($itemData['image'], '/'); ?>"
                                                     alt="<?= $itemData['name']; ?>"/> <?= getItemName($itemData['name']); ?>
                                            </div>
                                            <div class="panel-body">
                                                <div class="info hide">
                                                    <div class="panel panel-default">
                                                        <div class="panel-heading">
                                                            <img src="<?= ltrim($itemData['image'], '/'); ?>"
                                                                 alt="<?= $itemData['name']; ?>"/> <?= getItemName($itemData['name']); ?>
                                                            Diff
                                                        </div>
                                                        <div class="panel-body">
                                                            <table class="table table-hover">
                                                                <thead>
                                                                <tr>
                                                                    <td class="text-right">+/-</td>
                                                                    <td class="text-right">%</td>
                                                                    <td class="text-right">+/-</td>
                                                                    <td class="text-right">%</td>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <?php
                                                                foreach ($tmp as $forceName => $amount) {

                                                                    echo "<tr>";
                                                                    foreach (array(
                                                                                 'input_diff',
                                                                                 'output_diff'
                                                                             ) as $t) {
                                                                        echo "<td class='text-right td-type-{$t}'>";
                                                                        if (isset($amount[$t])) {
                                                                            echo "<span class='original hide'>{$amount[$t]['diff']}</span>";
                                                                        } else {
                                                                            echo "<span class='original hide'>0</span>";
                                                                        }
                                                                        echo "<span class='type type-{$t}'>";
                                                                        if (isset($amount[$t])) {
                                                                            echo formatWithSuffix($amount[$t]['diff']);
                                                                        } else {
                                                                            echo 0;
                                                                        }
                                                                        echo "</span> ";
                                                                        echo "</td>";
                                                                        echo "<td class='text-right td-type-{$t}'>";
                                                                        if (isset($amount[$t])) {
                                                                            echo "<span class='original hide'>{$amount[$t]['percent']}</span>";
                                                                        } else {
                                                                            echo "<span class='original hide'>0</span>";
                                                                        }
                                                                        echo "<span class='type type-{$t}'>";
                                                                        if (isset($amount[$t])) {
                                                                            echo $amount[$t]['percent'] . '%';
                                                                        } else {
                                                                            echo '0.00%';
                                                                        }
                                                                        echo "</span> ";
                                                                        echo "</td>";
                                                                    }

                                                                    echo "</tr>";
                                                                }
                                                                ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <table class="table table-hover">
                                                    <thead>
                                                    <tr>
                                                        <td></td>
                                                        <td class="text-right">P</td>
                                                        <td class="text-right">V</td>
                                                    </tr>
                                                    </thead>
                                                    <tbody>

                                                    <?php
                                                    foreach ($tmp as $forceName => $amount) {

                                                        echo "<tr>";
                                                        echo "<td>{$forceName}</td>";
                                                        foreach (array(
                                                                     'input',
                                                                     'output'
                                                                 ) as $t) {
                                                            echo "<td class='text-right td-type-{$t}'>";
                                                            if (isset($amount[$t])) {
                                                                echo "<span class='original hide'>{$amount[$t]}</span>";
                                                            } else {
                                                                echo "<span class='original hide'>0</span>";
                                                            }
                                                            echo "<span class='type type-{$t}'>";
                                                            if (isset($amount[$t])) {
                                                                echo formatWithSuffix($amount[$t]);
                                                            } else {
                                                                echo 0;
                                                            }
                                                            echo "</span> ";
                                                            echo "</td>";
                                                        }

                                                        echo "</tr>";
                                                    }
                                                    ?>

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>

                        <table class="table table-striped table-condensed table-hover view-type view-type-table"
                               style="display: none;">
                            <tbody>
                            <tr>
                                <td class="sorter-false"></td>
                                <?php
                                foreach ($forces as $force) {
                                    echo "<td colspan='2' class='text-right sorter-false'>";
                                    echo "<span style=\"color: " . $force->getColorAsHex() . '">';
                                    echo $force->getName();
                                    echo "</span>";
//                                    echo "<br>";
//                                    echo "<small>(Produktion/Verbrauch)</small>";
                                    echo "</td>";
                                }
                                ?>
                            </tr>
                            </tbody>
                            <thead>
                            <tr>
                                <td class="sorter-false"></td>
                                <?php
                                foreach ($forces as $f) {
                                    echo '<td class="text-right td-type-input">Input</td><td class="text-right td-type-output">Output</td>';
                                }
                                ?>
                            </tr>
                            </thead>
                            <tbody class="data">
                            <?php
                            foreach ($items as $itemName => $itemData) {
                                ?>
                                <tr class="item-name-<?= $itemName; ?>">
                                    <td>
                                        <img src="<?= ltrim($itemData['image'], '/'); ?>"
                                             alt="<?= $itemData['name']; ?>"/> <?= getItemName($itemData['name']); ?>
                                    </td>
                                    <?php
                                    foreach ($itemData['forces'] as $amount) {

                                        foreach (array(
                                                     'input',
                                                     'output'
                                                 ) as $t) {
                                            echo "<td class='text-right td-type-{$t}'>";
                                            if (isset($amount[$t])) {
                                                echo "<span class='original hide'>{$amount[$t]}</span>";
                                            } else {
                                                echo "<span class='original hide'>0</span>";
                                            }
                                            echo "<span class='type type-{$t}'>";
                                            if (isset($amount[$t])) {
                                                echo formatWithSuffix($amount[$t]);
                                            } else {
                                                echo 0;
                                            }
                                            echo "</span> ";
                                            echo "<img src='" . ltrim($itemData['image'], '/') . "' alt='{$itemData['name']}' style='height: 20px'/>";
                                            echo "</td>";
                                        }
                                    }
                                    ?>
                                </tr>
                                <?php
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
<script src="http://cdn.jsdelivr.net/jquery.touchswipe/1.6.15/jquery.touchSwipe.min.js"></script>
<script src="http://cdn.jsdelivr.net/jquery.sidr/2.2.1/jquery.sidr.min.js"></script>
<script>
    $(function () {

        $('#showFilter').sidr({
            name: 'sidr-main',
            source: '#filter',
            renaming: false,

        });

        $('body').swipe({
            //Single swipe handler for left swipes
            swipeLeft: function () {
                $.sidr('close', 'sidr-main');
            },
            swipeRight: function () {
                $.sidr('open', 'sidr-main');
            },
            //Default is 75px, set to 0 for demo so any distance triggers swipe
            threshold: 45
        });
        var max = <?=$max;?>;
        max *= 2;
        var headers = {};

        for (var i = 1; i < max; i++) {
            headers[i] = {
                sorter: "digit"
            }
        }

        $('.panel-collapse').on('shown.bs.collapse', function () {
            $(this).find('table').not('.tablesorter').tablesorter({
                cssInfoBlock: 'info-no-sort',
                textExtraction: function (node) {
                    var me = $(node);
                    if (me.find('.original')) {
                        return me.find('.original').html();
                    }
                    return node.innerHtml;
                },
                headers: headers
            });
        }).on('hidden.bs.collapse', function () {
            $('#info').fadeOut(100, function () {
                $('#info').remove();
            })
        });
//        $('#filter-c').on('mouseenter',function(e) {
//
//            $('#filter-c, #filter-c span').stop().animate({left: "220px"}, function() {
//                $('#filter-c span').removeClass('glyphicon-menu-right').addClass('glyphicon-menu-left');
//            });
//            $('#filter').stop().animate({left: 0});
//
//        });
//        $('#filter').on('mouseleave',function(e) {
//            $(this).stop().animate({left: "-220px"});
//            $('#filter-c,#filter-c span').stop().animate({left: 0}, function() {
//                $('#filter-c span').removeClass('glyphicon-menu-left').addClass('glyphicon-menu-right');
//            });
//
//        });
        $(document).on('change', 'div.checkbox input', function () {
            var me = $(this);
            var itemName = me.val();
            if (me.is(':checked')) {
                $('.item-name-' + itemName).fadeIn('fast');
            } else {
                $('.item-name-' + itemName).fadeOut('fast');
            }
        });
        $(document).on('change', '#changeAll', function () {
            var me = $(this);
            if (me.is(':checked')) {
                $('input.item-checkbox').prop('checked', true).trigger('change');
            } else {
                $('input.item-checkbox').prop('checked', false).trigger('change');
            }
        });
        $(document).on('change', '#select', function () {
            console.log('HIER');
            var me = $(this);
            $('.view-type').fadeOut('fast', function () {
                $(me.val()).fadeIn();
            });
        });
<?php
if (isset($config['withDiff']) && $config['withDiff'] === true) {
?>
        $(document).on('mouseenter', '.panel-item', function (e) {
            e.preventDefault();

            var me = $(this);
            var info = $('#info');
            var load = function () {
                info.remove();
                var i = $('<div/>').attr('id', 'info');
                i.html(me.find('.info').html());
                i.css({
                    position: 'absolute',
                    top: me.offset().top,
                    display: 'none',
                });
                $('body').append(i);

                var left = me.offset().left + me.width();
                if (left + i.width() > $(window).width()) {
                    left = me.offset().left - i.width();
                }
                i.css({
                    left: left
                })

                i.fadeIn(100);
            };
            if (info.length == 1) {
                $('#info').fadeOut(100, load)
            } else {
                load();
            }
        });
<?php
}
?>
    });
</script>


</body>
</html>
