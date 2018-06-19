<?php
/**
 * @auth  bxl@gmail.com
 * @date 2018-06-13
 * 入口文件
 */
ini_set('session.use_strict_mode', true);
ini_set('session.cookie_lifetime', 86400 / 2);
ini_set('session.name', 'aducode_sid');
isset($_GET['__path__']) && $_SERVER['PATH_INFO'] = $_GET['__path__'];
require "../../tuner/init.php";

Config::$mode = in_array($_SERVER['SERVER_ADDR'], array('47.104.93.177','172.31.177.101')) ? 'dev' : 'online';

App::run(isset($_GET['debug']) && $_GET['debug'] == 'dodebug');
