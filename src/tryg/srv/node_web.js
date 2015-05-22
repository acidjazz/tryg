
/**
 * local jade delivery via localhost web port 3000
 */

var fs = require('fs');
var jade = require('jade');
var stylus = require('stylus');
var http = require('http');
var path = require('path');

http.createServer(function(req, res) {

  if (req.method != 'POST') {
    res.writeHead(500, {'Content-Type': 'text/json'});
    res.end(JSON.stringify({'error': 'post required'}));
    return true;
  }

  var body = '';

  req.on('data', function(data) {
    body += data;
  });

  req.on('end', function() {

    var post = JSON.parse(body);

    if (post.module == 'jade') {

      jade.renderFile(post.options.file, post.data, function(error, output) {
        if (error) {
          res.writeHead(500, {'Content-Type': 'text/html'});
          res.end(error.message); 
        } else {
          res.writeHead(200, {'Content-Type': 'text/html'});
          res.end(output);
        }
      });

    }

    if (post.module == 'stylus') {

      stylus(post.data, post.options).define('cfg', post.options.cfg).render(function(error, output) {

        if (error) {
          res.writeHead(500, {'Content-Type': 'text/html'});
          res.end(error.message); 
        } else {
          res.writeHead(200, {'Content-Type': 'text/html'});
          res.end(output);
        }

      });

    }

  });

}).listen(3000);
