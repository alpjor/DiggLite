<?php
/**
 * Digg Lite reference application
 * 
 * @author    Bill Shupp <bill@digg.com>
 * @author    Jeff Hodsdon <jeff@digg.com>
 * @copyright 2009 Digg, Inc.
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://digglite.com
 */

ini_set('include_path', '../PEAR:../lib:' . get_include_path());

require_once 'DiggLite.php';

DiggLite::$options = array(
    'debug'        => 0,
    'apiKey'       => 'key',
    'apiSecret'    => 'secret',
    'apiUrl'       => 'http://services.digg.com',
    'apiEndpoint'  => 'http://services.digg.com/1.0/endpoint',
    'callback'     => 'http://yourwebsite.com/callback.php',
    'authorizeUrl' => 'http://digg.com/oauth/authenticate',
    /*
    'cache'        => 'Memcache',
    */
    'cache'        => 'CacheLite',
    'cacheOptions' => array(
    /*
        'servers' => array(
            array(
                'host'    => 'localhost',
                'port'    => 11211,
                'timeout' => 1
            )
        )
        */
        'cacheDir'             => '/tmp/digglite/',
        'lifeTime'             => 60,
        'hashedDirectoryLevel' => 2
    )
);

session_module_name('files');
session_start();

?>
