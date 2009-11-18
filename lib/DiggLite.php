<?php
/**
 * Digg Lite
 * 
 * @author    Jeff Hodsdon <jeff@digg.com>
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Digg, Inc.
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://digglite.com
 */

require_once 'HTTP/OAuth/Consumer.php';
require_once 'Services/Digg2.php';
require_once 'DiggLite/Exception.php';
require_once 'DiggLite/Cache.php';
require_once 'DiggLite/View.php';

/**
 * Digg Lite
 * 
 * @author    Jeff Hodsdon <jeff@digg.com>
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Digg, Inc.
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://digglite.com
 */
class DiggLite
{

    /**
     * Configuration options
     *
     * @var array $options Configuration options
     */
    static public $options = array();

    /**
     * cache 
     * 
     * @var Cache Instance of cache
     */
    protected $cache = null;

    /**
     * Digg
     * 
     * @var Services_Digg2 Instance of the Services_Digg2 library
     */
    protected $digg = null;

    /**
     * View object
     *
     * The view object $view Outputs the template
     *
     * @var DiggLite_View Instance of the view
     */
    protected $view = null;

    /**
     * Digg containers
     *
     * @var array $containers Containers from the Digg API
     * @see self::getContainers()
     */
    protected $containers = array();

    /**
     * Constructor
     *
     * Sets up instances of Services_Digg2, DiggLite_View, Cache, and
     * HTTP_OAuth_Consumer using data from the current session and config
     * options.
     *
     * @return void
     */
    public function __construct()
    {
        // Some error checking
        if (!isset(self::$options['apiKey'])) {
            throw new DiggLite_Exception('apiKey option is missing');
        }

        if (!isset(self::$options['apiSecret'])) {
            throw new DiggLite_Exception('apiKey option is missing');
        }

        // Instantiate Services_Digg2
        $this->digg = new Services_Digg2();
        $this->digg->setURI(self::$options['apiUrl']);

        // Instantiate a View object
        $this->view = new DiggLite_View();

        // Instantiate Cache
        if (!isset(self::$options['cache'])) {
            $this->cache = DiggLite_Cache::factory('CacheLite');
            return;
        }

        // Cache options must be an array
        if (!isset(self::$options['cacheOptions'])) {
            self::$options['cacheOptions'] = array();
        }

        // Create the cache instance
        $this->cache = DiggLite_Cache::factory(self::$options['cache'],
                                               self::$options['cacheOptions']);

        // Create the instance of HTTP_OAuth_Consumer
        $this->oauth = new HTTP_OAuth_Consumer(self::$options['apiKey'],
                                               self::$options['apiSecret']);

        // Add token and token secret to HTTP_OAuth_Consumer
        if (!empty($_SESSION['authorized'])) {
            $this->oauth->setToken($_SESSION['oauth_token']);
            $this->oauth->setTokenSecret($_SESSION['oauth_token_secret']);

            // Accept into Services_Digg2 for OAuth requests
            $this->digg->accept($this->oauth);
        }

    }

    /**
     * Main
     *
     * Generate everything needed to display DiggLite's list page.
     *
     * @return void
     */
    public function main()
    {
        if (empty($_SESSION['authorized'])) {
            $this->view->authURL = $this->getAuthURL();
        } else {
            $this->view->user = $this->digg->oauth->verify()->oauthverification->user;
        }
        if (isset($_POST['event']) && $_POST['event'] == 'setContainer') {
            $this->setContainer();
        }

        $this->view->containers = $this->getContainers();
        $this->getSelectedContainer();
        $this->getStories();

        return $this->view->render('list.tpl');
    }

    /**
     * Callback
     *
     * Upon a callback do this. Retreive an the access token from the API,
     * store it in the session, and redirect the browser to the list.
     *
     * @return void
     */
    public function callback()
    {
        $this->oauth->setToken($_SESSION['oauth_token']);
        $this->oauth->setTokenSecret($_SESSION['oauth_token_secret']);
        $this->oauth->getAccessToken(self::$options['apiEndpoint'], $_GET['oauth_verifier'],
            array('method' => 'oauth.getAccessToken'), 'POST');

        $_SESSION['oauth_token']        = $this->oauth->getToken();
        $_SESSION['oauth_token_secret'] = $this->oauth->getTokenSecret();
        $_SESSION['authorized']         = 1;

        session_write_close();

        return header('Location: /');
    }

    /**
     * Digg a story
     *
     * This is ran on the ajax call do digg a story.
     *
     * @return void
     */
    public function digg()
    {
        header('Content-Type: application/json');
        try {
            if (!isset($_POST['story_id'])) {
                throw new Exception('No story id in request');
            }

            $res = $this->digg->story->digg(array('story_id' => $_POST['story_id']));
        } catch (Exception $e) {
            return print(json_encode(array('error' => $e->getMessage())));
        }

        if (empty($res->success->status)) {
            return print(json_encode(array('error' => 'Ack! Digg on story was not successful')));
        }

        return print(json_encode($res));
    }

    /**
     * Bury a story
     *
     * This is ran on the ajax call do bury a story.
     *
     * @return void
     */
    public function bury()
    {
        header('Content-Type: application/json');
        try {
            if (!isset($_POST['story_id'])) {
                throw new Exception('No story id in request');
            }

            $res = $this->digg->story->bury(array('story_id' => $_POST['story_id']));
        } catch (Exception $e) {
            return print(json_encode(array('error' => $e->getMessage())));
        }

        if (empty($res->success->status)) {
            return print(json_encode(array('error' => 'Ack! Bury on story was not successful')));
        }

        return print(json_encode($res));
    }

    /**
     * Error handler
     *
     * @param Exception $e The Exception that occured
     *
     * @return void
     */
    public function error(Exception $e)
    {
        if (empty(self::$options['debug'])) {
            echo $e->getMessage();
        } else {
            echo $e->getTraceAsString(); 
        }
    }

    /**
     * Get an authorize url
     *
     * Generate the url the user goes to in order to authorize this application
     *
     * @return void
     */
    public function getAuthURL()
    {
        $this->oauth->getRequestToken(self::$options['apiEndpoint'], self::$options['callback'],
            array('method' => 'oauth.getRequestToken'), 'POST');

        $_SESSION['oauth_token']        = $this->oauth->getToken();
        $_SESSION['oauth_token_secret'] = $this->oauth->getTokenSecret();
        $_SESSION['authorized']         = 0;

        return $this->oauth->getAuthorizeURL(self::$options['authorizeUrl']);
    }

    /**
     * Get the stories to display
     *
     * Does logic to determine if a container has been selected or not.
     *
     * @return void
     */
    protected function getStories()
    {
        $this->digg->setURI(self::$options['apiUrl']);
        $storiesKey = md5('stories' . $this->view->selectedContainer);

        $stories = $this->cache->get($storiesKey);
        if (!$stories) {
            $params = array('count' => 50);
            if ($this->view->selectedContainer) {
                $params['container'] = $this->view->selectedContainer;
            }
            $stories = $this->digg->story->getPopular($params)->stories;
            $this->cache->set($storiesKey, $stories);
        }

        $this->view->stories = $stories;
    }

    /**
     * Determine selected container and set that container on the view 
     * 
     * @return void
     */
    protected function getSelectedContainer()
    {
        $this->view->selectedContainer = null;
        $this->view->containerTitle    = 'All Topics';
        if (isset($_SESSION['selectedContainer'])) {
            $this->view->selectedContainer = $_SESSION['selectedContainer'];
            foreach ($this->view->containers as $container) {
                if ($container->short_name == $_SESSION['selectedContainer']) {
                    $this->view->containerTitle = $container->name;
                }
            }
        }
    }

    /**
     * Set the users selected container
     *
     * @return void
     */
    public function setContainer()
    {
        if (!isset($_POST['container'])) {
            return;
        }

        if ($_POST['container'] == 'all') {
            unset($_SESSION['selectedContainer']);
            return;
        }

        foreach ($this->getContainers() as $container) {
            if ($container->short_name == $_POST['container']) {
                $_SESSION['selectedContainer'] = $_POST['container'];
                $_SESSION['containerTopic']    = $container->name;
            }
        }
    }

    /**
     * Get containers from the API for the drop down
     *
     * @return array Container information
     */
    protected function getContainers()
    {
        $containersKey = md5('containers');
        $containers    = $this->cache->get($containersKey);
        if (!$containers) {
            $this->digg->setURI(self::$options['apiUrl']);
            $containers = $this->digg->container->getAll()->containers;
            $this->cache->set($containersKey, $containers);
        }

        return $containers;
    }

}


?>
