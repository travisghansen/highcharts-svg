var page = require('webpage').create();
var fs = require('fs');

page.content = fs.read (phantom.args[0]);

console.log(page.evaluate(function () {
    return chart.getSVG();
}));

phantom.exit();
