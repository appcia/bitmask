<?php

use Appcia\BitMask\Mask;

class MaskTest extends PHPUnit_Framework_TestCase
{

    public function testInitial()
    {
        $mask = new Mask();

        $this->assertSame(0, $mask->getValue());
        $this->assertSame("0", $mask->getBin());
    }

    public function testBits()
    {
        $mask = new Mask();

        $this->assertFalse($mask->is(Mask::N10));
        $mask->set(Mask::N10, true);
        $this->assertEquals(Mask::N10, $mask->getValue());
        $mask->set(Mask::N0, true);
        $this->assertEquals(Mask::N10 + Mask::N0, $mask->getValue());
        $this->assertTrue($mask->is(Mask::N10));
        $mask->set(Mask::N10, false);
        $this->assertFalse($mask->is(Mask::N10));
        $this->assertEquals(Mask::N0, $mask->getValue());
    }

    public function testCountable()
    {
        {
            $mask = new Mask(0);
            $this->assertEquals(0, count($mask));
        }

        {
            $mask = new Mask(19);
            $this->assertEquals(3, count($mask));
        }
    }

    public function testSerializable()
    {
        $mask = new Mask();

        $serialized = serialize($mask);
        $origin = unserialize($serialized);

        $this->assertEquals($mask->getValue(), $origin->getValue());

    }

    public function testListener()
    {
        $mask = new Mask();

        $listened = false;
        $mask->listen(function (Mask $mask, $origin) use (&$listened) {
            $listened = true;

            $this->assertSame($origin, 0);
            $this->assertSame(1, $mask->getValue());
        });
        $mask->set(Mask::N0, true);

        $this->assertTrue($listened);
    }
}
 