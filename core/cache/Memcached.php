<?php

namespace beaba\core\cache;

use \beaba\core;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Memcached extends core\Service implements core\ICacheDriver
{

    /**
     * Gets the value from the specified key
     * @param string $key
     * @return mixed
     */
    public function getValue($key)
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * Get values from the specified keys
     * @param array $keys
     * @return array
     */
    public function getValues(array $keys)
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * Sets a value attached to the specified key
     * @param string $key
     * @param mixed $value
     * @return ICache
     */
    public function setValue($key, $value)
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * Set values attaches to specified indexes (keys)
     * @param array $values
     * @return ICache
     */
    public function setValues($values)
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * Remove the specified key
     * @param string $key
     * @return ICache
     */
    public function unsetValue($key)
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * Remove the specified keys
     * @param array $key
     * @return ICache
     */
    function unsetValues($keys)
    {
        throw new \BadMethodCallException('Not implemented');
    }

}
