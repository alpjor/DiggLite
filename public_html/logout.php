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

session_module_name('files');
session_start();
session_destroy();
header('Location: /');

?>
