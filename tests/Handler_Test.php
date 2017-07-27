<?php
    
use ABridge\ABridge\Handler;

class Handler_Test extends PHPUnit_Framework_TestCase
{

 
    public function testBaseHandler1()
    {
    	$prm=[
    			'path'=>'C:/Users/pierr/ABridge/Datastore/',
    			'host'=>'localhost',
    			'user'=>'cl822',
    			'pass'=>'cl822'
    	];
        $this->assertTrue(Handler::get()->resetHandlers());
        $this->assertNotNull($db1 = Handler::get()->setBase('dataBase', 'test',$prm));
        $this->assertNotNull($db2 = Handler::get()->getBase('dataBase', 'test'));
        $this->assertEquals($db1, $db2);
    }
    
    public function testBaseHandler2()
    {
    	$prm=[
    			'path'=>'C:/Users/pierr/ABridge/Datastore/',
    			'host'=>'localhost',
    			'user'=>'cl822',
    			'pass'=>'cl822'
    	];
        $this->assertTrue(Handler::get()->resetHandlers());
        $this->assertNotNull($db1 = Handler::get()->setBase('dataBase', 'test',$prm));
        $this->assertNotNull($db2 = Handler::get()->setBase('fileBase', 'test',$prm));
        $this->assertNotEquals($db1, $db2);
    }
    
    public function testBaseHandler3()
    {
    	$prm=[
    			'path'=>'C:/Users/pierr/ABridge/Datastore/',
    			'host'=>'localhost',
    			'user'=>'cl822',
    			'pass'=>'cl822'
    	];
        $this->assertTrue(Handler::get()->resetHandlers());
        $this->assertNotNull($db1 = Handler::get()->setBase('fileBase', 'test',$prm));
        $this->assertNotNull($db2 = Handler::get()->setBase('fileBase', 'test1',$prm));
        $this->assertFalse($db2 = Handler::get()->getBase('NOTEXISTS', 'NOTEXISTS'));
        $this->assertNotEquals($db1, $db2);
    }
    
    public function testStateHandler()
    {
    	$prm=[
    			'path'=>'C:/Users/pierr/ABridge/Datastore/',
    			'host'=>'localhost',
    			'user'=>'cl822',
    			'pass'=>'cl822'
    	];
        $this->assertTrue(Handler::get()->resetHandlers());
        $this->assertNotNull($db1 = Handler::get()->setBase('fileBase', 'test',$prm));
        $this->assertNotNull($db = Handler::get()->setStateHandler('CLassA', 'fileBase', 'test'));
        $this->assertNotNull($db = Handler::get()->setStateHandler('CLassB', 'fileBase', 'test'));
        $this->assertNotNull($c1 = Handler::get()->getStateHandler('CLassA'));
        $this->assertNotNull($c2 = Handler::get()->getStateHandler('CLassB'));
        $this->assertEquals(['CLassA','CLassB'], Handler::get()->getMods());
//        $this->assertEquals(2,count(Handler::get()->getBaseClasses()));
        $this->assertEquals($c1, $c2);
    }
 
    public function testViewHandler()
    {
        $this->assertTrue(Handler::get()->resetHandlers());
        $x = 'x';
        Handler::get()->setViewHandler($x, $x);
        $y = Handler::get()->getViewHandler($x);
        $this->assertEquals($x, $y);
        $y = Handler::get()->getViewHandler('yy');
        $this->assertNull($y);
    }
}
