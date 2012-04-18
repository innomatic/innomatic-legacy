<?php
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'tests/innomatic/core/RootContainerTest.php';
require_once 'tests/innomatic/core/InnomaticSettingsTest.php';
/**
 * Static test suite.
 */
class coreSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * Constructs the test suite handler.
     */
    public function __construct ()
    {
        $this->setName('coreSuite');
        $this->addTestSuite('RootContainerTest');
        $this->addTestSuite('InnomaticSettingsTest');
    }
    /**
     * Creates the suite.
     */
    public static function suite ()
    {
        return new self();
    }
}

