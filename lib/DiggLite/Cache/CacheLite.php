<?php
/**
 * DiggLite_Cache_CacheLite 
 * 
 * PHP Version 5.2.0+
 * 
 * @uses      DiggLite_Cache
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://digglite.com
 */

require_once 'DiggLite/Cache.php';
require_once 'Cache/Lite.php';

/**
 * A Cache_Lite implementation
 * 
 * @uses      DiggLite_Cache
 * @author    Bill Shupp <hostmaster@shupp.org> 
 * @copyright 2009 Bill Shupp
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://digglite.com
 */
class DiggLite_Cache_CacheLite extends DiggLite_Cache
{
    protected $defaultOptions = array(
        'cacheDir'             => '/tmp/digglite/',
        'lifeTime'             => 60,
        'hashedDirectoryLevel' => 2
    );

    /**
     * Instantiates Cache_Lite with options passed in.  Creates the directory if it
     * doesn't already exist.
     * 
     * @param array $options Cache_Lite::Cache_Lite options
     * 
     * @return void
     */
    public function __construct(array $options)
    {
        $options = array_merge($this->defaultOptions, $options);

        if (!file_exists($options['cacheDir'])) {
            mkdir($options['cacheDir'], 0777, true);
        }
        
        $this->cache = new Cache_Lite($options);
    }

    /**
     * Gets a cache object by id
     * 
     * @param string $key The cache id
     * 
     * @return mixed
     */
    public function get($key)
    {
        return unserialize($this->cache->get($key));
    }

    /**
     * Sets an object in cache
     * 
     * @param string $key    The cache id
     * @param mixed  $value  The value to be cached
     * @param int    $expire The expiration in seconds
     * 
     * @return void
     */
    public function set($key, $value, $expire = 0)
    {
        return $this->cache->save(serialize($value), $key);
    }

    /**
     * Deletes an id from cache
     * 
     * @param string $key The cache object's id
     * 
     * @return bool
     */
    public function delete($key)
    {
        return $this->cache->remove($key);
    }
}
?>
