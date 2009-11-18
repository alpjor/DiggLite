<?php
/**
 * Services_Digg2 
 * 
 * PHP Version 5.2.0+
 * 
 * @category  Services
 * @package   Services_Digg2
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Digg, Inc.
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/digg/services_digg2
 */

/**
 * Required files 
 */
require_once 'Validate.php';
require_once 'HTTP/Request2.php';
require_once 'Services/Digg2/Exception.php';
require_once 'HTTP/OAuth.php';
require_once 'HTTP/OAuth/Consumer.php';
require_once 'HTTP/OAuth/Consumer/Request.php';

/**
 * Services_Digg2 
 * 
 * A simple pass-through layer for accessing Digg's second generation API.
 * 
 * Anonymous request for popular stories:
 * <code>
 * require_once 'Services/Digg2.php';
 * 
 * $digg = new Services_Digg2;
 * try {
 *     $results = $digg->story->getPopular();
 * } catch (Services_Digg2_Exception $e) {
 *     echo $e->getMessage();
 * }
 * 
 * var_dump($results);
 * </code>
 * 
 * 
 * Authenticated request for digging a story
 * <code>
 * require_once 'Services/Digg2.php';
 * require_once 'HTTP/OAuth/Consumer.php';
 * 
 * $digg  = new Services_Digg2;
 * $oauth = new HTTP_OAuth_Consumer('key', 'secret', 'token', 'token_secret');
 * $digg->accept($oauth);
 * 
 * try {
 *     $result = $digg->story->digg(array('story_id' => 12345));
 * } catch (Services_Digg2_Exception $e) {
 *     echo $e->getMessage();
 * }
 * 
 * var_dump($result);
 * </code>
 * 
 * See {@link http://digg.com/api} for API documentation.
 * 
 * @category  Services
 * @package   Services_Digg2
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Digg, Inc.
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://github.com/digg/services_digg2
 */
class Services_Digg2
{
    /**
     * URI of API.  You shouldn't need to change this unless you work at Digg.
     * 
     * @see getURI()
     * @see setURI()
     * @var string
     */
    protected $uri = 'http://services.digg.com';

    /**
     * Version to use in API calls
     * 
     * @var mixed
     */
    protected $version = '1.0';

    /**
     * Supported version numbers
     * 
     * @var $versions
     */
    protected $versions = array('1.0');

    /**
     * Current group requested.  i.e., $digg->story->getAll(), the group is "story".
     * 
     * @see __get(), __call()
     * @var string
     */
    protected $currentGroup = null;

    /**
     * Stores an optional custom instance of HTTP_Request2.  Use this if you want
     * to set HTTP_Request2 options, like timeouts, etc.
     * 
     * @see accept(), getHTTPRequest2()
     * @var HTTP_Request2
     */
    protected $HTTPRequest2 = null;

    /**
     * Stores an instance of HTTP_OAuth_Consumer
     * 
     * @see accept(), getHTTPOAuthConsumer()
     * @var HTTP_OAuth_Consumer|null
     */
    protected $HTTPOAuthConsumer = null;

    /**
     * Methods that require POST and OAuth
     * 
     * @var array
     */
    protected $writeMethods = array(
        'story.digg',
        'story.bury',
        'comment.digg',
        'comment.bury',
        'oauth.verify'
    );

    /**
     * Last response object
     * 
     * @see getLastResponse()
     * @var HTTP_Request2_Response
     */
    protected $lastResponse = null;

    /**
     * Sets the URI.  You'll probably never use this unless you work at Digg.
     * 
     * @param string $uri The Digg API URI
     * 
     * @see getURI()
     * @see $uri
     * @throws Services_Digg2_Exception on invalid URI
     * @return void
     */
    public function setURI($uri)
    {
        if (!Validate::uri($uri)) {
            throw new Services_Digg2_Exception('Invalid URI: ' . $uri);
        }
        $this->uri = $uri;
    }

    /**
     * Gets the Digg API URI
     * 
     * @return string
     */
    public function getURI()
    {
        return $this->uri;
    }

    /**
     * Accepts HTTP_OAuth_Consumer (for writeable endpoints), or a custom instance 
     * of HTTP_Request2.  The latter is useful if you want to use custom config 
     * options, adapters, etc, for HTTP_Request2.
     * 
     * @param mixed $object HTTP_OAuth_Consumer instance or custom instance of 
     *                      HTTP_Request2
     * 
     * @see getHTTPRequest2(), getHTTPOAuthConsumer()
     * @return void
     */
    public function accept($object)
    {
        switch (get_class($object)) {
        case 'HTTP_Request2':
            $this->HTTPRequest2 = $object;
            break;
        case 'HTTP_OAuth_Consumer':
            $this->HTTPOAuthConsumer = $object;
            break;
        default:
            throw new Services_Digg2_Exception(
                'Only HTTP_Request2 and HTTP_OAuth_Consumer may be accepted'
            );
        }
    }

    /**
     * Gets an instance of HTTP_Request2.  If $HTTPRequest2 is not aleady set,
     * an instance will be created, set, and returned.
     * 
     * @see accept()
     * @return HTTP_Request2
     */
    public function getHTTPRequest2()
    {
        if (!$this->HTTPRequest2 instanceof HTTP_Request2) {
            $this->HTTPRequest2 = new HTTP_Request2;
            $this->HTTPRequest2->setConfig(array('connect_timeout' => 3,
                                                 'timeout'         => 3));
        }

        return $this->HTTPRequest2;
    }

    /**
     * Gets $this->HTTPOAuthConsumer, regardless of whether it is set or not.
     * 
     * @return void
     */
    public function getHTTPOAuthConsumer()
    {
        return $this->HTTPOAuthConsumer;
    }

    /**
     * Sets the version of the API to use.
     * 
     * @param mixed $version Version to use, defaults to 1.0
     * 
     * @see getVersion()
     * @throws Services_Digg2_Exception on invalid version
     * @return void
     */
    public function setVersion($version)
    {
        if (!in_array($version, $this->versions)) {
            throw new Services_Digg2_Exception("Invalid version: $version");
        }
        $this->version = $version;
    }

    /**
     * Gets the current version setting (i.e. 1.0).
     * 
     * @see setVersion()
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Represents an API "group", and provides a pass-through to __call().
     * 
     * The example below calls story.getInfo().  The object spacing is needed 
     * because member variables in PHP cannot have '.' in them.
     * <code>
     * $digg  = new Services_Digg2()
     * $story = $digg->story->getInfo(array('story_id' => 12345));
     * </code>
     * 
     * See {@link http://digg.com/api} for API documentation.
     * 
     * @param string $group The name of the API "group"
     * 
     * @see __call()
     * @return Services_Digg2 (current instance)
     */
    public function __get($group)
    {
        $this->currentGroup = $group;
        return $this;
    }

    /**
     * Concatenates the current group and a method, then adds it as the 'method'
     * key of the arguments array, and finally passes to sendRequest().  No local
     * validation is done.  Any errors are passed back from the Digg API.
     * 
     * See {@link http://digg.com/api} for API documentation.
     * 
     * @param string $name Method name (i.e. getInfo())
     * @param array  $args Array of arguments.  'method' key will be overwritten.
     * 
     * @see __get()
     * @throws Services_Digg2_Exception (indirectly) on anything but a 2XX response
     * @return result of sendRequest()
     */
    public function __call($name, array $args = array())
    {
        if (count($args)) {
            $args = $args[0];
        }
        $args['method'] = $this->getCurrentGroup() . '.' . $name;
        return $this->sendRequest($args);
    }

    /**
     * Sends a request to the Digg API.
     * 
     * @param array $args Array of arguments, includeing the method.
     * 
     * @ignore
     * @throws Services_Digg2_Exception on error
     * @return stdObject response
     */
    protected function sendRequest(array $args)
    {
        $httpMethod = in_array($args['method'], $this->writeMethods) ?
                      HTTP_Request2::METHOD_POST : HTTP_Request2::METHOD_GET;

        $this->getHTTPRequest2()->setMethod($httpMethod);

        // Hard coding json for now
        $args['type'] = 'json';
        $this->getHTTPRequest2()->setHeader('Accept: application/json');

        $uri = $this->getURI() . '/' . $this->getVersion() . '/endpoint';

        if ($this->getHTTPOAuthConsumer() instanceof HTTP_OAuth_Consumer) {
            try {
                return $this->parseResponse($this->sendOAuthRequest($uri, $args, $httpMethod));
            } catch (HTTP_OAuth_Exception $e) {
                throw new Services_Digg2_Exception($e->getMessage(), $e->getCode());
            }
        }
        try {
            return $this->parseResponse($this->sendAnonymousRequest($uri, $args));
        } catch (HTTP_Request2_Exception $e) {
            throw new Services_Digg2_Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Sends an OAuth request to the Digg API
     * 
     * @param string $uri  The URI to talk to
     * @param array  $args An array of arguments
     * 
     * @ignore
     * @return HTTP_Request2_Response
     */
    protected function sendOAuthRequest($uri, array $args, $httpMethod)
    {
        $oauth = $this->getHTTPOAuthConsumer();

        // Use the same instance of HTTP_Request2
        $consumerRequest = new HTTP_OAuth_Consumer_Request;
        $consumerRequest->accept($this->getHTTPRequest2());
        $oauth->accept($consumerRequest);

        return $oauth->sendRequest($uri, $args, $httpMethod)->getResponse();
    }

    /**
     * Sends an anonymous GET request to the Digg API.
     * 
     * @param string $uri  The Digg API URI
     * @param array  $args An array of arguments
     * 
     * @ignore
     * @return void
     */
    protected function sendAnonymousRequest($uri, $args)
    {
        $req   = $this->getHTTPRequest2();
        $parts = array();
        foreach ($args as $param => $value) {
            $parts[] = urlencode($param) . '=' . urlencode($value);
        }

        $req->setUrl($uri .= '?' . implode('&', $parts));
        return $req->send();
    }

    /**
     * Parses the response, returning stdClass of the reponse body
     * 
     * @param HTTP_Request2_Response $response The HTTP_Request2 response
     * 
     * @ignore
     * @throws Services_Digg2_Exception on a non-2XX response
     * @return stdClass (decoded json)
     */
    protected function parseResponse(HTTP_Request2_Response $response)
    {
        $this->lastResponse = $response;

        $body = json_decode($response->getBody());
        if (!is_object($body)) {
            throw new Services_Digg2_Exception(
                'Unabled to decode result: ' . $response->getBody()
            );
        }
        $status = $response->getStatus();

        if (strncmp($status, '2', 1) !== 0) {
            throw new Services_Digg2_Exception($body->error->message,
                                               $body->error->code,
                                               $status);
        }
        return $body;
    }

    /**
     * Returns the current group being selected.
     * 
     * @ignore
     * @see $currentGroups
     * @see __get()
     * @return string
     */
    protected function getCurrentGroup()
    {
        return $this->currentGroup;
    }

    /**
     * Returns the last HTTP_Request2_Response object.  Defaults to null if no 
     * API request has been made yet.  Handy if you want to look at the X-RateLimit 
     * headers, like so:
     * 
     * <code>
     * require_once 'Services/Digg2.php';
     * 
     * $digg    = new Services_Digg2;
     * $popular = $digg->story->getPopular();
     * $current = $digg->getLastResponse()->getHeader('X-RateLimit-current');
     * $max     = $digg->getLastResponse()->getHeader('X-RateLimit-max');
     * $reset   = $digg->getLastResponse()->getHeader('X-RateLimit-reset');
     * 
     * echo "I've used $current of my $max allowed requests.  This rate limit period 
     * resets in $reset seconds";
     * </code>
     * 
     * @return HTTP_Request2_Response|null
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }
}
?>
