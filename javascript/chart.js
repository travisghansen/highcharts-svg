var page = require('webpage').create();
var fs = require('fs');

page.viewportSize = { width: 1280, height: 1024 };
page.content = fs.read (phantom.args[0]);

console.log(page.evaluate(function () {
    return chart.getSVG();
}));

phantom.exit();
