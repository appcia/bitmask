<?php

namespace Appcia\BitMask\Mask;

use Appcia\BitMask\Mask;
use Appcia\Utils\Arrays;

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
     * Wrapped property
     *
     * @var string|null
     */
    protected $prop;

    /**
     * Constructor
     *
     * @param mixed $value
     * @param array $map
     */
    public function __construct($value = null, array $map = array())
    {
        parent::__construct();

        $this->setMap($map);
        $this->push($value);
    }

    /**
     * Factory method (useful when chained)
     *
     * @param mixed $value
     * @param array $map
     *
     * @return static
     */
    public static function factory($value = 0, array $map = array())
    {
        return new static($value, $map);
    }

    /**
     * Wrap mask options, attach value change listener and push value at once
     * Most commonly used combination
     *
     * @param object $model ORM model with magic getter / setters
     * @param string $prop  ORM property name
     * @param mixed  $value Mask value to be applied
     * @param array  $map   Possible mask options
     *
     * @return $this
     */
    public static function wrap($model, $prop, $value = null, array $map = array())
    {
        $options = static::factory($model->{$prop}, $map)
            ->listen(function (self $options) use ($model, $prop) {
                $model->{$prop} = $options->getValue();
            });
        $options->prop = $prop;

        return $options->act($value);
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
            $key = $bits ? $option : $value;
            $values[$key] = $this->is($option);
        }

        return $values;
    }

    /**
     * Filter options by state
     *
     * @param bool $flag  State
     * @param bool $names Option as name
     *
     * @return array
     */
    public function filter($flag = true, $names = true)
    {
        $values = array();
        foreach ($this->map as $key => $option) {
            if ($flag != $this->is($option)) {
                continue;
            }

            $values[] = !$names
                ? $key
                : $option;
        }

        return $values;
    }

    /**
     * Clear all options
     *
     * @return $this
     */
    public function clear()
    {
        $this->setValue(0);

        return $this;
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
        $option = null;
        if (is_string($name)) {
            $option = array_search($name, $this->map);
            if ($option === false) {
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
     * @param mixed   $options Array of options to be affected or true (for all)
     * @param boolean $flag    Enable / disable specified options
     *
     * @return $this
     */
    public function apply($options = null, $flag = true)
    {
        if ($options === true) {
            $options = array_keys($this->map);
        } elseif (!is_array($options)) {
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
     * @param string|int $option Option
     * @param boolean    $flag   Enable / disable
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
        if (Arrays::isArray($value)) {
            if (Arrays::isAssoc($value)) {
                $this->setAll($value);
            } else {
                $this->clear()->apply($value);
            }
        }
        else {
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
     * Get wrapped property
     *
     * @return null|string
     */
    public function getProp()
    {
        return $this->prop;
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
        $this->set($option, false);
    }

    /**
     * @see is()
     */
    public function __get($property)
    {
        return $this->is($property);
    }

    /**
     * @see set()
     */
    public function __set($property, $value)
    {
        $this->set($property, $value);
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

        return serialize($data);
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
