<?php
/**
 * Services_Digg2_Exception 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      PEAR_Exception
 * @category  Services
 * @package   Services_Digg2
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */

/**
 * Required file 
 */
require_once 'PEAR/Exception.php';

/**
 * Base exception handler for Services_Digg2
 * 
 * @uses      PEAR_Exception
 * @category  Services
 * @package   Services_Digg2
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://pearopenid.googlecode.com
 */
class Services_Digg2_Exception extends PEAR_Exception
{
    /**
     * HTTP Status Code
     * 
     * @var mixed
     */
    public $status = null;

    /**
     * Stores the HTTP Status code in addition to the exception message and code
     * 
     * @param string $message Exception message
     * @param int    $code    Exception code
     * @param int    $status  HTTP status code
     * 
     * @return void
     */
    public function __construct($message = '', $code = 0, $status = 0)
    {
        $this->status = $status;
        parent::__construct($message, $code);
    }
}
?>
