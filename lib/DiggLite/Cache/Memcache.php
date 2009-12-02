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

require_once 'DiggLite/Cache.php';

/**
 * Digg Lite reference application
 * 
 * @author    Bill Shupp <bill@digg.com>
 * @author    Jeff Hodsdon <jeff@digg.com>
 * @copyright 2009 Digg, Inc.
 * @license   http://www.opensource.org/licenses/bsd-license.php FreeBSD
 * @link      http://digglite.com
 */
class DiggLite_Cache_Memcache extends DiggLite_Cache
{

    /**
     * Instance of Memcache 
     * 
     * @var Memcache $cache Memcache instance that will be used
     */
    protected $cache = null;

    /**
     * Construct 
     * 
     * @param array $options Cache options
     *
     * @return void
     */
    public function __construct(array $options)
    {
        $this->cache = new Memcache;
        foreach ($options['servers'] as $server) {
            $this->cache->addServer($server['host'],
                                    $server['port'],
                                    $server['timeout']);
        }
    }

    /**
     * Get value
     *
     * @param string $key Memcache key
     *
     * @return mixed Result of the get call
     */
    public function get($key)
    {
        return $this->cache->get($key);
    }

    /**
     * Set value into memcache
     *
     * @param string $key    Cache key
     * @param string $value  Value to store
     * @param int    $expire Time to live
     *
     * @return bool Result of the set
     */
    public function set($key, $value, $expire = 60)
    {
        return $this->cache->set($key, $value, null, $expire);
    }

    /**
     * Delete value in cache
     *
     * @param string $key 
     *
     * @return bool Result of the delete
     */
    public function delete($key)
    {
        return $this->cache->delete($key);
    }
}
?>
