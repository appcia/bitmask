<?php

use Appcia\BitMask\Mask\Options;

class OptionsTest extends PHPUnit_Framework_TestCase
{
    public function testNamedBits()
    {
        $options = new Options(0, array(
            Options::N0 => 'active',
            Options::N1 => 'visible',
            Options::N32 => 'fixed',
        ));

        $this->assertFalse($options->is('active'));
        $options->set('active', true);
        $this->assertTrue($options->is('active'));

        $this->assertFalse($options->is(Options::N1));
        $options->set(Options::N1, true);
        $this->assertTrue($options->is(Options::N1));

        $this->assertEquals(array(
            Options::N0 => true,
            Options::N1 => true,
            Options::N32 => false
        ), $options->getAll(true));

        $this->assertFalse($options->is('fixed'));
        $options->apply('fixed');
        $this->assertTrue($options->is('fixed'));

        $this->assertEquals(Options::N0 + Options::N1 + Options::N32, $options->getValue());
        $this->assertEquals('100000000000000000000000000000011', $options->getBin());

        $this->assertEquals(array(
            'active' => true,
            'visible' => true,
            'fixed' => true
        ), $options->getAll());

        $options->apply(array('fixed', Options::N1, 'active'), false);
        $this->assertFalse($options->is(Options::N0));
        $this->assertFalse($options->is('visible'));
        $this->assertFalse($options->is('fixed'));

        $this->assertEquals(0, $options->getValue());
    }
}
 