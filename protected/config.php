<?php

date_default_timezone_set('PRC');
require_once('model/CONFIG.php');

$config = array(
    'rewrite' => array(
        'mhs/<code>'                                         => 'main/viewMHS',
        'api/<a>'                                            => 'api/<a>',
        'ajax/<a>'                                           => 'ajax/<a>',
        'account/<a>'                                        => 'account/<a>',
        'account/'                                           => 'account/index',
        'item/<a>'                                           => 'item/<a>',
        'order/<a>'                                          => 'order/<a>',
        'usercenter/<a>'                                     => 'usercenter/<a>',
        'usercenter'                                         => 'usercenter/index',
        '<a>'                                                => 'main/<a>',
        '/'                                                  => 'main/index',

    ),
);

$domain = array(
    "127.0.0.1" => array( // 调试配置
        'debug' => 1,
        'maintain' => 0,
        'mysql' => array(
            'MYSQL_HOST' => CONFIG::GET('MHS_DEBUG_MYSQL_HOST'),
            'MYSQL_PORT' => CONFIG::GET('MHS_DEBUG_MYSQL_PORT'),
            'MYSQL_USER' => CONFIG::GET('MHS_DEBUG_MYSQL_USER'),
            'MYSQL_DB'   => CONFIG::GET('MHS_DEBUG_MYSQL_DATABASE'),
            'MYSQL_PASS' => CONFIG::GET('MHS_DEBUG_MYSQL_PASSWORD'),
            'MYSQL_CHARSET' => 'utf8',
        ),
    ),
    "mhs.atsast.com" => array( //本地域名映射配置
        'debug' => 1,
        'maintain' => 0,
        'mysql' => array(
            'MYSQL_HOST' => CONFIG::GET('MHS_DEBUG_MYSQL_HOST'),
            'MYSQL_PORT' => CONFIG::GET('MHS_DEBUG_MYSQL_PORT'),
            'MYSQL_USER' => CONFIG::GET('MHS_DEBUG_MYSQL_USER'),
            'MYSQL_DB'   => CONFIG::GET('MHS_DEBUG_MYSQL_DATABASE'),
            'MYSQL_PASS' => CONFIG::GET('MHS_DEBUG_MYSQL_PASSWORD'),
            'MYSQL_CHARSET' => 'utf8',
        ),
    ),
    "credit.winter.mundb.xyz" => array( //本地域名映射配置
        'debug' => 1,
        'maintain' => 0,
        'mysql' => array(
            'MYSQL_HOST' => CONFIG::GET('MHS_DEBUG_MYSQL_HOST'),
            'MYSQL_PORT' => CONFIG::GET('MHS_DEBUG_MYSQL_PORT'),
            'MYSQL_USER' => CONFIG::GET('MHS_DEBUG_MYSQL_USER'),
            'MYSQL_DB'   => CONFIG::GET('MHS_DEBUG_MYSQL_DATABASE'),
            'MYSQL_PASS' => CONFIG::GET('MHS_DEBUG_MYSQL_PASSWORD'),
            'MYSQL_CHARSET' => 'utf8',
        ),
    ),
);
// 为了避免开始使用时会不正确配置域名导致程序错误，加入判断
if(empty($domain[$_SERVER["HTTP_HOST"]])) die("配置域名不正确，请确认".$_SERVER["HTTP_HOST"]."的配置是否存在！");

return $domain[$_SERVER["HTTP_HOST"]] + $config;