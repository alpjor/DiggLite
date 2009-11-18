<?php
/**
 * DiggLite_Cache 
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
 * DiggLite_Cache 
 * 
 * Base cache interface.  Also provides a factory method.  See 
 * {@link http://pear.php.net/Cache_Lite Cache_Lite} and 
 * {@link http://us.php.net/manual/en/book.memcached.php Memcached} documentation.
 * 
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Digg, Inc.
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://digglite.com
 */
abstract class DiggLite_Cache
{
    protected $cache = null;

    /**
     * Factory method for instantiating your Cache driver.
     * 
     * @param string $driver  The name of the driver you want to load (CacheLite or 
     *                        Memcached)
     * @param array  $options An array of options to be used.  For CacheLite, it's 
     *                        the same format as the Cache_Lite constructor.  For 
     *                        Memcached, it's a "servers" array, where each entry 
     *                        can have the 3 arguments to Memcached::addServer - 
     *                        host, post, and timeout.
     * 
     * @throws DiggLite_Exception on failure
     * @return DiggLite_Cache instance
     */
    static public function factory($driver, array$options = array())
    {
        $filename = 'DiggLite/Cache/' . str_replace('_', '/', $driver . '.php');
        $class    = 'DiggLite_Cache_' . $driver;

        include_once $filename;
        if (!class_exists($class)) {
            throw new DiggLite_Exception("Cache driver $class does not exist");
        }

        return new $class($options);
    }

    /**
     * Gets the instance of the cache driver in case you need it.
     * 
     * @return Object (Cache_Lite or Memcached instances)
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Definition of getting an object from the cache
     * 
     * @param string $key The cache id
     * 
     * @return mixed Cached result on success, false on failure
     */
    abstract public function get($key);

    /**
     * Definition of setting an object in cache.
     * 
     * @param string $key    Id (key) of the cached item
     * @param mixed  $value  Value of the cached item
     * @param int    $expire Expiration in seconds
     * 
     * @return bool
     */
    abstract public function set($key, $value, $expire = 0);

    /**
     * Definition of deleting an item from the cache
     * 
     * @param string $key Id of the cached item
     * 
     * @return bool
     */
    abstract public function delete($key);
}
?>
