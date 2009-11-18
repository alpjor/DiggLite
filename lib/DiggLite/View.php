<?php
/**
 * DiggLite_View 
 * 
 * PHP Version 5.2.0+
 * 
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Digg, Inc.
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://digglite.com
 */

require_once 'DiggLite/Exception.php';

/**
 * A very simple view layer.
 * 
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Digg, Inc.
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://digglite.com
 */
class DiggLite_View
{
    /**
     * Data for the template 
     * 
     * @var array $data Array containing the data
     */
    protected $data = array();

    /**
     * Template
     *
     * @var string $templateDir Directory that houses all the templates
     */
    protected $templateDir = '../templates/';

    /**
     * Optionally sets the template directory
     *
     * @param string $templateDir Path to the template directory
     *
     * @return void
     */
    public function __construct($templateDir = null)
    {
        if ($templateDir !== null) {
            $this->templateDir = $templateDir;
        }
    }

    /**
     * Sets variables for the templates to use
     * 
     * @param string $key The variable name
     * @param mixed  $val The variable value
     * 
     * @return void
     */
    public function __set($key, $val)
    {
        $this->data[$key] = $val;
    }

    /**
     * Gets a variable if set, returns null otherwise
     *
     * @param string $key Name for the value
     * 
     * @return mixed Variable value or null
     */
    public function __get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        return null;
    }

    /**
     * Renders a template
     * 
     * @param string $template The template file name
     * 
     * @return void
     */
    public function render($template)
    {
        foreach ($this->data as $key => $val) {
            $$key = $val;
        }

        include_once $this->templateDir . $template;
    }

    /**
     * Returns the template contents
     * 
     * @param string $template The template filename
     * 
     * @return string
     */
    public function fetch($template)
    {
        ob_start();
        $this->render($template);
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
}
?>
