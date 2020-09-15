<?php

namespace ialopezg\Libraries;

/**
 * Abstract class to provide accessing and iterators to an array of objects and treat them as a collection.
 *
 * @package ialopezg\Libraries
 */
abstract class Collection implements \ArrayAccess, CollectionInterface, \Iterator {
    /** @var array collection list  */
    protected $data = null;
    /** @var array data cache store. */
    protected $cache = [];

    /**
     * Sets default options, if any.
     *
     * @param array $data Data to be passed to this instance, if any.
     */
    public function __construct(array $data) {
        $this->data = array_merge($this->getDefaults(), $data);
    }

    /**
     * Returns an array of default options and values.
     *
     * @return array an array of default options and values.
     */
    protected function getDefaults() {
        return [];
    }

    /**
     * Merge config from another instance.
     *
     * @param CollectionInterface $config Configuration to merged.
     *
     * @return $this
     */
    public function merge(CollectionInterface $config) {
        $this->data = array_replace_recursive($this->data, $config->all());

        return $this;
    }

    /**
     * Remove a value by given key name.
     *
     * @param string $key Key name to remove.
     */
    public function remove($key) {
        $this->offsetUnset($key);
    }

    /*
     * ----------------------------------------------------------------------
     * Collection Interface Methods
     * ----------------------------------------------------------------------
     */

    /**
     * @inheritDoc
     */
    public function all() {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null) {
        return $this->has($key) ? $this->cache[$key] : $default;
    }

    /**
     * @inheritDoc
     */
    public function has($key) {
        if (isset($this->cache[$key])) {
            return true;
        }

        $segments = explode('.', $key);
        $root = $this->data;

        // Nested lookup.
        foreach ($segments as $segment) {
            if (array_key_exists($segment, $root)) {
                $root = $root[$segment];

                continue;
            } else {
                return false;
            }
        }

        $this->cache[$key] = $root;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value) {
        $segments = explode('.', $key);
        $root = &$this->data;
        $cacheKey = '';

        // Look for the key, creating nested keys if needed
        while ($part = array_shift($segments)) {
            if ($cacheKey != '') {
                $cacheKey .= '.';
            }
            $cacheKey .= $part;
            if (!isset($root[$part]) && count($segments)) {
                $root[$part] = [];
            }
            $root = &$root[$part];

            //Unset all old nested cache
            if (isset($this->cache[$cacheKey])) {
                unset($this->cache[$cacheKey]);
            }

            //Unset all old nested cache in case of array
            if (count($segments) === 0) {
                foreach ($this->cache as $cache_key => $cache_value) {
                    if (substr($cache_key, 0, strlen($cacheKey)) === $cacheKey) {
                        unset($this->cache[$cache_key]);
                    }
                }
            }
        }

        // Assign value at target node
        $this->cache[$key] = $root = $value;
    }

    /*
     * ----------------------------------------------------------------------
     * ArrayAccess Interface Methods
     * ----------------------------------------------------------------------
     */

    /**
     * @inheritDoc
     */
    public function offsetExists($offset) {
        return $this->has($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset) {
        return $this->get($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value) {
        $this->set($offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset) {
        $this->set($offset, null);
    }

    /*
     * ----------------------------------------------------------------------
     * Iterator Interface Methods
     * ----------------------------------------------------------------------
     */

    /**
     * @inheritDoc
     */
    public function current() {
        return is_array($this->data) ? current($this->data) : null;
    }

    /**
     * @inheritDoc
     */
    public function key() {
        return is_array($this->data) ? key($this->data) : null;
    }

    /**
     * @inheritDoc
     */
    public function next() {
        return is_array($this->data) ? next($this->data) : null;
    }

    /**
     * @inheritDoc
     */
    public function rewind() {
        return is_array($this->data) ? reset($this->data) : null;
    }

    /**
     * @inheritDoc
     */
    public function valid() {
        return is_array($this->data) ? key($this->data) !== null : false;
    }
}
