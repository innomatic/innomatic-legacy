<?php
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'tests/innomatic/innomaticSuite.php';
require_once 'tests/shared/sharedSuite.php';
/**
 * Static test suite.
 */
class allTests extends PHPUnit_Framework_TestSuite
{
    /**
     * Constructs the test suite handler.
     */
    public function __construct ()
    {
        $this->setName('allTests');
        $this->addTestSuite('innomaticSuite');
        $this->addTestSuite('sharedSuite');
    }
    /**
     * Creates the suite.
     */
    public static function suite ()
    {
        return new self();
    }
}

