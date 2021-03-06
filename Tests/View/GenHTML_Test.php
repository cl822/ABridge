<?php
    
use ABridge\ABridge\Log\Logger;
use ABridge\ABridge\View\GenHTML;

require_once 'GenHTML_case.php';

class GenHTML_Test extends PHPUnit_Framework_TestCase
{

    protected static $log;

    public static function setUpBeforeClass()
    {
        self::$log=new Logger();
        self::$log->load('C:/Users/pierr/ABridge/Datastore/', 'GenHTML_init');
    }
    
    public function testFormElmOut()
    {
        $test= GenHTLMCases();
        $this->expectOutputString(self::$log->getLine(0));
        $this->assertNotNull(GenHTML::genFormElem($test[0][0], true));
    }
    
    /**
     * @dataProvider Provider1
     */
 
    public function testFormElm($a, $expected)
    {
        $this->assertEquals(self::$log->getLine($expected), GenHTML::genFormElem($a, false));
    }
 
    public function Provider1()
    {
        return GenHTLMCases();
    }
}
