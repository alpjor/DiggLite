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

if (!isset($_GET['oauth_token']) || !isset($_GET['oauth_verifier'])) {
    throw new Exception('Missing token or verifier');
}

$diggLite = new DiggLite;
try {
    $diggLite->callback();
} catch (Exception $e) {
    $diggLite->error($e);
}


?>
