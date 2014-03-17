<?php

namespace Innomatic\Core\Tests;

/**
 * InnomaticSettings test case.
 */
class InnomaticSettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InnomaticSettings
     */
    private $InnomaticSettings;
    /**
     * Prepares the environment before running a test.
     */
    protected function setUp ()
    {
        parent::setUp();
        $this->InnomaticSettings = new \Innomatic\Core\InnomaticSettings(\Innomatic\Core\RootContainer::instance('\Innomatic\Core\RootContainer')->getHome().'/innomatic/core/conf/tests/innomatic.ini');
    }
    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown ()
    {
        $this->InnomaticSettings = null;
        parent::tearDown();
    }
    /**
     * Constructs the test case.
     */
    public function __construct ()
    {
    }
    /**
     * Tests InnomaticSettings->getKey()
     */
    public function testGetKey ()
    {
        $this->assertSame('test', $this->InnomaticSettings->getKey('PlatformName'));
    }
    /**
     * Tests InnomaticSettings->refresh()
     */
    public function testRefresh ()
    {
        $this->InnomaticSettings->setVolatileKey('PlatformName', 'test2');
        $this->InnomaticSettings->refresh();
        $this->assertSame('test', $this->InnomaticSettings->getKey('PlatformName'));
    }
    /**
     * Tests InnomaticSettings->__construct() exception throwing
     * @expectedException Exception
     */
    public function testConstructorException ()
    {
        $innomaticSettings = new \Innomatic\Core\InnomaticSettings(dirname(__FILE__).'/innomatic_wrong.ini');
    }
    /**
     * Tests InnomaticSettings->setVolatileKey()
     */
    public function testSetVolatileKey ()
    {
        $this->InnomaticSettings->setVolatileKey('PlatformName', 'test2');
        $this->assertSame('test2', $this->InnomaticSettings->getKey('PlatformName'));
    }
    /**
     * Tests InnomaticSettings->value()
     */
    public function testValue ()
    {
        $this->InnomaticSettings->refresh(/* parameters */);
        $this->assertSame('test', $this->InnomaticSettings->getKey('PlatformName'));
    }
}

