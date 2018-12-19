<?php

namespace Html5VideoConverter;

abstract class Parameters
{
    /**
     * The config items
     *
     * @var mixed
     */
    protected $storage;

    /**
     * Set Parameter
     *
     * @param string|int $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->storage[$key] = $value;
    }

    /**
     * Get Parameter
     *
     * @param string|int $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (isset($this->storage[$key])) {
            return $this->storage[$key];
        }
        return $default;
    }

    /**
     * Delete Parameter
     *
     * @param string|int $key
     */
    public function delete($key)
    {
        if (isset($this->storage[$key])) {
            unset($this->storage[$key]);
        }
    }

    public function __toString()
    {
        return implode(
            ' ',
            array_map(
                function ($key, $value) {
                    return $key . ' ' . $value;
                },
                array_keys($this->storage),
                $this->storage
            )
        );
    }
}
