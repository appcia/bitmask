<?php

namespace Appcia\BitMask;

/**
 * Bit mask operations utility
 *
 * Useful for optimized options (stored in database as one integer column)
 */
class Mask implements \Serializable, \Countable
{
    /**
     * Integer length
     */
    const BITS = 32;

    /**
     * Bit values
     */
    const N0 = 1;

    const N1 = 2;

    const N2 = 4;

    const N3 = 8;

    const N4 = 16;

    const N5 = 32;

    const N6 = 64;

    const N7 = 128;

    const N8 = 256;

    const N9 = 512;

    const N10 = 1024;

    const N11 = 2048;

    const N12 = 4096;

    const N13 = 8192;

    const N14 = 16384;

    const N15 = 32768;

    const N16 = 65536;

    const N17 = 131072;

    const N18 = 262144;

    const N19 = 524288;

    const N20 = 1048576;

    const N21 = 2097152;

    const N22 = 4194304;

    const N23 = 8388608;

    const N24 = 16777216;

    const N25 = 33554432;

    const N26 = 67108864;

    const N27 = 134217728;

    const N28 = 268435456;

    const N29 = 536870912;

    const N30 = 1073741824;

    const N31 = 2147483648;

    const N32 = 4294967296;

    /**
     * Plain integer value
     *
     * @var int
     */
    protected $value;

    /**
     * Value changed listeners
     *
     * @var callable[]
     */
    protected $listeners;

    /**
     * Constructor
     *
     * @param int $value
     */
    public function __construct($value = 0)
    {
        $this->value = 0;
        $this->listeners = array();

        $this->setValue($value);
    }

    /**
     * Factory method (useful when chained)
     *
     * @param mixed $value
     *
     * @return static
     */
    public static function factory($value)
    {
        return new static($value);
    }

    /**
     * Check whether value is power of 2
     *
     * @param int $option Integer number
     *
     * @return boolean
     */
    public static function checkOption($option)
    {
        return ($option === 0) || (($option & ($option - 1)) == 0);
    }

    /**
     * Check whether value could be a mask
     *
     * @param $value
     *
     * @return boolean
     */
    public static function checkValue($value)
    {
        return $value >= 0;
    }

    /**
     * Toggle mask option
     *
     * @param int $option Option value
     *
     * @return $this
     */
    public function toggle($option)
    {
        $flag = !$this->is($option);
        $this->set($option, $flag);

        return $this;
    }

    /**
     * Check some mask option
     *
     * @param int $option Option value
     *
     * @return boolean
     */
    public function is($option)
    {
        if (!$this->checkOption($option)) {
            return false;
        }

        $flag = ($this->value & $option) > 0;

        return $flag;
    }

    /**
     * Set mask option
     *
     * @param int     $option Option value
     * @param boolean $flag   True of false
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function set($option, $flag)
    {
        if (!$this->checkOption($option)) {
            throw new \InvalidArgumentException(sprintf(
                "Mask option should be a number which is power of 2.", $option
            ));
        }

        $value = $this->value;
        if ($flag) {
            $value |= $option;
        } else {
            $value &= ~$option;
        }

        $this->setValue($value);

        return $this;
    }

    /**
     * Get integer value
     *
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set integer value
     *
     * @param int $value Value
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setValue($value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException("Mask value should be greater than 0.");
        }

        $changed = ($this->value !== $value);
        $origin = $this->value;

        $this->value = $value;
        if ($changed) {
            foreach ($this->listeners as $listener) {
                $listener($this, $origin);
            }
        }

        return $this;
    }

    /**
     * Bind value by reference
     *
     * @param int $value Value
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function bindValue(&$value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException("Mask value should be greater than 0.");
        }

        $this->value = & $value;

        return $this;
    }

    /**
     * Push various type value
     *
     * @param mixed $value Value
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function push($value)
    {
        if ($value instanceof static) {
            $value = $value->getValue();
        }

        if (is_string($value)) {
            $this->setBin($value);
        } elseif (is_numeric($value)) {
            $this->setValue($value);
        } elseif (is_null($value)) {
            $this->setValue(0);
        } else {
            throw new \InvalidArgumentException(sprintf(
                "Mask value to be pushed has invalid type: '%s'.",
                gettype($value)
            ));
        }

        return $this;
    }

    /**
     * Register value changed callback
     *
     * @param $callback
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function listen($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException("Mask value changed callback is not callable.");
        }

        $this->listeners[] = $callback;

        return $this;
    }

    /**
     * Act as getter when value is not specified, otherwise as setter
     *
     * @param mixed $value Value
     *
     * @return $this
     */
    public function act($value = null)
    {
        if ($value === null) {
            return $this;
        }

        $this->push($value);

        return $this;
    }

    /**
     * Get binary string representation e.g "10011" when mask value is 19
     *
     * @return string
     */
    public function getBin()
    {
        return decbin($this->value);;
    }

    /**
     * Set value by binary string representation
     *
     * @param string $bin Binary string of zeros and ones
     *
     * @return $this
     */
    public function setBin($bin)
    {
        $this->setValue(bindec($bin));

        return $this;
    }

    /**
     * Get serialized form of mask
     */
    public function serialize()
    {
        return serialize($this->value);
    }

    /**
     * Set mask from serialized form
     */
    public function unserialize($serialized)
    {
        $this->value = unserialize($serialized);
    }

    /**
     * Count enabled bits
     *
     * @return int
     */
    public function count()
    {
        return substr_count($this->getBin(), '1');
    }

    /**
     * Get string representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getBin();
    }
}