<?php
namespace Innomatic\Core\Tests;

use \Innomatic\Core;

/**
 * RootContainer test case.
 */
class RootContainerTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var RootContainer
     */
    private $RootContainer;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->RootContainer = RootContainer::instance('\Innomatic\Core\RootContainer');
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->RootContainer = null;
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {}

    /**
     * Tests RootContainer->getHome()
     */
    public function testGetHome()
    {
        $this->assertEquals(realpath(dirname(__FILE__) . '/../../../') . '/source/', $this->RootContainer->getHome());
    }

    /**
     * Tests RootContainer->isClean()
     */
    public function testIsClean()
    {
        $this->assertFalse($this->RootContainer->isClean());
    }

    /**
     * Tests RootContainer->stop()
     */
    public function testStop()
    {
        $this->RootContainer->stop();
        $this->assertTrue($this->RootContainer->isClean());
    }
}

