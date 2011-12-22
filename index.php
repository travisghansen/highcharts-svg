<?php


$s = DIRECTORY_SEPARATOR;

$file_path = dirname(realpath(__FILE__));
$config_file_path = $file_path . $s . "config" . $s . "config.ini";
$javascript_path = $file_path . $s . "javascript";

$bundled_highcharts_version = "2.1.9";
$bundled_jquery_version = "1.7.1";

$config = @parse_ini_file($config_file_path, true);
if($config === false){
    $config = array();
}

$phantomjs_path = $config['phantomjs']['path'];
if( ! is_executable ($phantomjs_path)){
    die("phantomjs path: '" . $phantomjs_path . "' does not appear to be executable.  Please set it properly in '" . $config_file_path . "'" . PHP_EOL);
}

$phantomjs_cmd_extra_options = $config['phantomjs']['extra_options'];
$phantomjs_script = $javascript_path . $s . "chart.js";
$highcharts_version = (isset($config['highcharts']['version'])) ? $config['highcharts']['version'] : $bundled_highcharts_version;
$jquery_version = (isset($config['jquery']['version'])) ? $config['jquery']['version'] : $bundled_jquery_version;


function serverify_chart_data(&$chart_data){
    /*
    options.chart.renderTo = 'container';
    options.chart.renderer = 'SVG';
    options.chart.animation = false;
    options.series.forEach(function(series) {
    series.animation = false;
    */
    $chart_data['chart']['renderTo'] = "container";
    $chart_data['chart']['renderer'] = "SVG";
    $chart_data['chart']['animation'] = false;
    foreach($chart_data['series'] as $series){
        $series['animation'] = false;
    }
}


function clean_output(&$output){
    $start = stripos($output, "<svg");
    $end   = stripos($output, "</svg>");
    if(is_numeric($start) && is_numeric($end)){
        $output = substr($output, $start, ($end - $start + 6));
    } else {
        $output = "";
    }
}



$chart_data = $_REQUEST['chart'];
$output_format = (isset($_REQUEST['format'])) ? strtolower($_REQUEST['format']) : "svg";
$convert_args = $_REQUEST['convert_args'];

// sample chart for testing
//$chart_data = '{"chart":{"defaultSeriesType":"area","renderTo":"containerfoo","renderer":"SVG"},"title":{"text":"Sales By Partner"},"subtitle":{"text":"Source: SecurityTrax"},"xAxis":{"categories":["11\/01\/2011","11\/02\/2011","11\/03\/2011","11\/04\/2011","11\/05\/2011","11\/06\/2011","11\/07\/2011","11\/08\/2011","11\/09\/2011","11\/10\/2011","11\/11\/2011","11\/12\/2011","11\/13\/2011","11\/14\/2011","11\/15\/2011","11\/16\/2011","11\/17\/2011","11\/18\/2011","11\/19\/2011","11\/20\/2011","11\/21\/2011","11\/22\/2011","11\/23\/2011","11\/24\/2011","11\/25\/2011","11\/26\/2011","11\/27\/2011","11\/28\/2011","11\/29\/2011","11\/30\/2011"],"tickmarkPlacement":"on"},"yAxis":{"title":{"text":"Sales"}},"plotOptions":{"area":{"stacking":"normal","lineWidth":1,"lineColor":"#666666","marker":{"lineWidth":1,"lineColor":"#666666"}}},"series":[{"name":"HiValley","data":[0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],"animation":false}]}';

if(empty($chart_data)){
    die("chart data is invalid" . PHP_EOL);
}

if( ! isset($_REQUEST['ignore_servify'])){
    $chart_data = json_decode($chart_data, true);
    serverify_chart_data($chart_data);
    $chart_data = json_encode($chart_data);
}

if(isset($_REQUEST['theme'])){
    $theme = $_REQUEST['theme'];
} elseif(isset($config['highcharts']['theme'])){
    $theme = $config['highcharts']['theme'];
}

$included_javascript = array (
    $javascript_path . $s . "jQuery" . $s . "jquery-" . $jquery_version . ".min.js",
    $javascript_path . $s . "Highcharts" . $s . "Highcharts-" . $highcharts_version . $s . "js" . $s . "highcharts.js",
    $javascript_path . $s . "Highcharts" . $s . "Highcharts-" . $highcharts_version . $s . "js" . $s . "highcharts.js",
    $javascript_path . $s . "Highcharts" . $s . "Highcharts-" . $highcharts_version . $s . "js" . $s . "modules" . $s . "exporting.js", // needed for getSVG() method
);

if( ! empty($theme)){
    $theme_file = $javascript_path . $s . "Highcharts" . $s . "Highcharts-" . $highcharts_version . $s . "js" . $s . "themes" . $s . $theme . ".js";
    if(file_exists($theme_file)){
        $included_javascript[] = $theme_file;
    }
}

foreach($included_javascript as $path){
    $js_content .= "\n" . trim(file_get_contents($path)) . "\n";
}


$html = <<<HTML
<html>
<head>
<title>Chart Export</title>
<script language="javascript">

$js_content

var chart_data = $chart_data;
var chart;
jQuery(document).ready(function() {
	chart = new Highcharts.Chart(chart_data);
});
</script>
</head>
<body>
<div id="container"></div>
</body>
</html>
HTML;

if(isset($config['platform']['temp_dir']) && is_dir($config['platform']['temp_dir'])){
    $temp_dir = $config['platform']['temp_dir'];
} else {
    $temp_dir = sys_get_temp_dir();
}

$tmp_file_path = tempnam($temp_dir, "highchart");
file_put_contents($tmp_file_path, $html);

$command = $phantomjs_path . " $phantomjs_cmd_extra_options " . $phantomjs_script . " " . $tmp_file_path;
$svg = shell_exec(escapeshellcmd($command));

clean_output($svg);

// testing output format
//$output_format = "png";

if( ! empty($svg)){
    switch($output_format){
        case "jpg":
        case "jpeg":
        case "png":
            $img = shell_exec("echo " . escapeshellarg($svg) . " | convert -background transparent $convert_args svg:- $output_format:-");
            header("Content-Type: image/$output_format");
            echo $img;
            break;
        case "svg":
        default:
            header("Content-Type: image/svg+xml");
            echo $svg;
            break;
    }
}

unlink($tmp_file_path);

?>
