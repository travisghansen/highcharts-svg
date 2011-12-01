#!/usr/bin/env php
<?php

require_once "Zend/Http/Client.php";

$url = "http://highcharts.domain.com/";

$chart_data = '{"chart":{"defaultSeriesType":"area","renderTo":"containerfoo","renderer":"SVG"},"title":{"text":"Sales By Partner"},"subtitle":{"text":"Source: SecurityTrax"},"xAxis":{"categories":["11\/01\/2011","11\/02\/2011","11\/03\/2011","11\/04\/2011","11\/05\/2011","11\/06\/2011","11\/07\/2011","11\/08\/2011","11\/09\/2011","11\/10\/2011","11\/11\/2011","11\/12\/2011","11\/13\/2011","11\/14\/2011","11\/15\/2011","11\/16\/2011","11\/17\/2011","11\/18\/2011","11\/19\/2011","11\/20\/2011","11\/21\/2011","11\/22\/2011","11\/23\/2011","11\/24\/2011","11\/25\/2011","11\/26\/2011","11\/27\/2011","11\/28\/2011","11\/29\/2011","11\/30\/2011"],"tickmarkPlacement":"on"},"yAxis":{"title":{"text":"Sales"}},"plotOptions":{"area":{"stacking":"normal","lineWidth":1,"lineColor":"#666666","marker":{"lineWidth":1,"lineColor":"#666666"}}},"series":[{"name":"HiValley","data":[0,0,0,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],"animation":false}]}';

$client = new Zend_Http_Client($url);
$client->setParameterPost('chart', $chart_data);

$response = $client->request('POST');

// SVG Data
$svg = $response->getBody();

// pass the data along to 'convert' or use pecl-imagick (http://pecl.php.net/imagick)
// to create a jpg/png/whatever

?>
