<?php
    
use ABridge\ABridge\Log\Logger;

require_once 'View_case.php';

class View_Test extends PHPUnit_Framework_TestCase
{

    protected static $log;

    public static function setUpBeforeClass()
    {
        self::$log=new Logger();
        self::$log->load('C:/Users/pierr/ABridge/Datastore/', 'View_init');
    }
    
    
    public function testViewOut()
    {
        $test= viewCases();
        $v = $test[0][0];
        $p = $test[0][1];
        $s = $test[0][2];
        $e = self::$log->getLine(0);
        $this->expectOutputString(self::$log->getLine(0));
        $this->assertNotNull($v->show($s, true));
    }
    
    
    /**
     * @dataProvider Provider1
     */
 
    public function testView($v, $p, $s, $expected)
    {
        
        $this->assertEquals(self::$log->getLine($expected), $v->show($s, false));
    }
 
    public function Provider1()
    {
        return viewCases();
    }
}
