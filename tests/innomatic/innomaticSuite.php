<?php
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'tests/innomatic/core/coreSuite.php';
/**
 * Static test suite.
 */
class innomaticSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * Constructs the test suite handler.
     */
    public function __construct ()
    {
        $this->setName('innomaticSuite');
        $this->addTestSuite('coreSuite');
    }
    /**
     * Creates the suite.
     */
    public static function suite ()
    {
        return new self();
    }
}

