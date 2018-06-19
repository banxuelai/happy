<?php
/**
 * @author bxl@gmail.com
 * @date 2017-12-26
 * 数据库配置文件
 */
return array(
    'default' => array(
        'master' => array(
            'host' => '47.104.93.177',
            'user' => 'aducode',
            'password' => 'B5aRAFw2Bine96Eh',
            'dbname' => 'aducode',
            'charset' => 'utf8mb4', //支持emoji表情
            'port' => 3306,
        ),
        'slave' => array(
            'host' => '193.112.73.122',
            'user' => 'aducode',
            'password' => 'B5aRAFw2Bine96Eh',
            'dbname' => 'aducode',
            'charset' => 'utf8mb4', //支持emoji表情
            'port' => 3306,
        ),
    ),
);
