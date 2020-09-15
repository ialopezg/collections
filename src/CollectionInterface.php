<?php

namespace ialopezg\Libraries;

/**
 * Interface to provide access to an associative array of objects as a collection of objects.
 *
 * @package ialopezg\Libraries
 */
interface CollectionInterface {
    /**
     * Get all items.
     *
     * @return array An array containing all connection items.
     */
    public function all();

    /**
     * Gets an item using a key.
     *
     * @param string $key Requested key.
     *
     * @return mixed Return the value of requested key or false value if not found.
     */
    public function get($key);

    /**
     * Checks if item exist using key name.
     *
     * @param string $key requested key.
     * @return bool <code>true</code> if key exist, otherwise <code>false</code>.
     */
    public function has($key);

    /**
     * Sets or updates an item, using its key name.
     *
     * @param string $key Key name to set.
     * @param mixed $value Value to set.
     */
    public function set($key, $value);
}