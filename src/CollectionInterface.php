<?php

namespace ialopezg\Libraries;

/**
 * Interface to provide access to an associative array of objects as a collection of objects.
 *
 * @package ialopezg\Libraries
 */
interface CollectionInterface {
    /**
     * Get all items from data store.
     *
     * @return array An array containing all connection items.
     */
    public function all();

    /**
     * Gets an object from data store using its key name.
     *
     * @param string $key       key name requested.
     * @param mixed $default    default value to be return, if key name not exists.
     *
     * @return mixed the object associated to requested key name; otherwise `$default` value.
     */
    public function get($key, $default = null);

    /**
     * Checks if an object exists into the data store using its key name.
     *
     * @param string $key requested key.
     * @return bool <code>true</code> if key name exists, otherwise <code>false</code>.
     */
    public function has($key);

    /**
     * Sets or updates a key name and its value.
     *
     * @param string $key key name.
     * @param mixed $value value to set.
     */
    public function set($key, $value);
}