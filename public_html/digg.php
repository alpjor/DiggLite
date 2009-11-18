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

require_once '../config.php';
require_once 'DiggLite.php';

$diggLite = new DiggLite;
try {
    $diggLite->digg();
} catch (Exception $e) {
    $diggLite->error($e);
}

?>
