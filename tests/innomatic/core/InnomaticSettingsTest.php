<?php
require_once 'source/innomatic/WEB-INF/classes/innomatic/core/InnomaticSettings.php';
require_once 'PHPUnit/Framework/TestCase.php';
/**
 * InnomaticSettings test case.
 */
class InnomaticSettingsTest extends PHPUnit_Framework_TestCase
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
        $this->InnomaticSettings = new InnomaticSettings(dirname(__FILE__).'/innomatic.ini');
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
     */
    public function testConstructorException ()
    {
        try {
            $innomaticSettings = new InnomaticSettings(
                dirname(__FILE__).'/innomatic_wrong.ini'
            );
        } catch (Exception $expected) {
            return;
        }
        
        $this->fail('An expected Exception has not been raised.');
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

