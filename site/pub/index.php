<?php

$loader = require '../../vendor/autoload.php';
$loader->setPsr4('ctl\\', '../ctl/');

set_error_handler(['\tryg\Debug', 'handler'], E_ALL);
register_shutdown_function(['\tryg\Debug', 'shutdown']);

tryg\Debug::def(tryg\Debug::slurp(tryg\Debug::rootPath().'/dat/'));

date_default_timezone_set($data['tryg']['timezone']);

function hpr() { return call_user_func_array(['\tryg\Debug', 'hpr'], func_get_args()); }

(new tryg\Controller($_SERVER['REQUEST_URI']))->start();



