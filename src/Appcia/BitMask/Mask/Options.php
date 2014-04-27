<?php

namespace Appcia\BitMask\Mask;

use Appcia\BitMask\Mask;

/**
 * Bit mask with named options
 */
class Options extends Mask implements \ArrayAccess, \IteratorAggregate
{
    /**
     * Mapped mask options to names
     *
     * @var array
     */
    protected $map;

    /**
     * Constructor
     *
     * @param mixed $value
     * @param array $map
     */
    public function __construct($value = 0, array $map = array())
    {
        parent::__construct();

        $this->setMap($map);
        $this->push($value);
    }

    /**
     * Factory method (useful when chained)
     *
     * @param mixed   $value
     * @param array $map
     *
     * @return static
     */
    public static function factory($value = 0, array $map = array())
    {
        return new static($value, $map);
    }

    /**
     * Get all options with names
     *
     * @param bool $bits Gey keys as integers (instead of names)
     *
     * @return array
     */
    public function getAll($bits = false)
    {
        $values = array();
        foreach ($this->map as $option => $value) {
            $key = $bits ? $option: $value;
            $values[$key] = $this->is($option);
        }

        return $values;
    }

    /**
     * Check option by name or integer
     *
     * @param string|int $option
     *
     * @return bool
     */
    public function is($option)
    {
        $option = $this->map($option);

        return parent::is($option);
    }

    /**
     * Map option name to integer
     *
     * @param string|int $name Option
     *
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function map($name)
    {
        $option = NULL;
        if (is_string($name)) {
            $option = array_search($name, $this->map);
            if ($option === FALSE) {
                throw new \OutOfBoundsException(sprintf("Mask option by name '%s' not found.", $name));
            }
        } else {
            if (!array_key_exists($name, $this->map)) {
                throw new \OutOfBoundsException(sprintf("Mask option '%s' does not exist.", $name));
            }

            $option = $name;
        }

        return $option;
    }

    /**
     * Set mask options using names
     *
     * @param array $options Options
     *
     * @return $this
     */
    public function setAll(array $options)
    {
        foreach ($options as $option => $value) {
            $this->set($option, $value);
        }

        return $this;
    }

    /**
     * Enable or disable multiple options at once
     *
     * @param mixed   $options
     * @param boolean $flag
     *
     * @return $this
     */
    public function apply($options, $flag = TRUE)
    {
        if (!is_array($options)) {
            $options = array($options);
        }
        foreach ($options as $option) {
            $this->set($option, $flag);
        }

        return $this;
    }

    /**
     * Set option by name or integer
     *
     * @param string|int $option
     * @param boolean    $flag
     *
     * @return $this
     */
    public function set($option, $flag)
    {
        $option = $this->map($option);

        return parent::set($option, $flag);
    }

    /**
     * Added support of array with mapped options names/bits as keys and booleans as values
     *
     * {@inheritdoc}
     */
    public function push($value)
    {
        if (is_array($value)) {
            $this->setAll($value);
        } else {
            parent::push($value);
        }

        return $this;
    }

    /**
     * Get mask options
     *
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * Set mask options
     *
     * @param array $options Mapped mask options to names
     *
     * @return $this
     */
    public function setMap($options)
    {
        $this->map = $options;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($option)
    {
        return $this->has($option);
    }

    /**
     * Check whether option exist
     *
     * @param string $option Option name
     *
     * @return boolean
     */
    public function has($option)
    {
        return in_array($option, $this->map);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($option)
    {
        return $this->is($option);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($option, $value)
    {
        $this->set($option, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($option)
    {
        $this->set($option, FALSE);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getAll());
    }

    /**
     * Get serialized form of mask options
     */
    public function serialize()
    {
        $data = array(
            'map' => $this->map,
            'value' => $this->value,
        );
        
        return serialize($$data);
    }

    /**
     * Set mask options from serialized form
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        
        if (isset($data['map'])) {
            $this->map = $data['map'];
        }
        if (isset($data['value'])) {
            $this->value = $data['value'];
        }
    }
}