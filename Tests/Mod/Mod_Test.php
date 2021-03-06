<?php

use ABridge\ABridge\Mod\Mod;
use ABridge\ABridge\UtilsC;
use ABridge\ABridge\Mod\Model;
use ABridge\ABridge\Mod\Mtype;
use ABridge\ABridge\CstError;

class Mod_Test extends \PHPUnit_Framework_TestCase
{

    public function testInit()
    {
        $prm=UtilsC::genPrm(['test1','test2','test3','test4'], get_called_class());

        $config=[
                'test1'=>[],
                'test2'=>['dataBase'],
                'test3'=>['fileBase'],
                'test4'=>['dataBase','test'],
        ];
        
        Mod::reset();
        $mod= Mod::get();
        
        $mod->init($prm['application'], $config);
        $this->assertTrue($mod->isNew());
        $this->assertEquals(['test1','test2','test3','test4'], $mod->getMods());
        
        $res= [
            'dataBase' => [
                    [
                            'test',
                            [
                                'test1',
                                'test2',
                                'test4',
                            ]
                    ]
            ],
            'fileBase' => [
                    [
                            'test',
                            [
                                'test3',
                            ]
                    ]
            ],
        ];
        
 //       var_dump($mod->showState());
        $this->assertEquals($res, $mod->showState());
        
        Mod::reset();
        $mod= Mod::get();
                
        $mod->init($prm['application'], $prm['handlers']);

        $this->assertEquals(8, count($mod->getMods()));
        
        $mod->begin();
        
        foreach ($prm['names'] as $modName) {
            $obj=new Model($modName);
            $obj->deleteMod();
            $obj->addAttr('Name', Mtype::M_STRING);
            $res=$obj->saveMod();
            $this->assertTrue($res);
        }
        
        $res=$mod->end();
        $this->assertTrue($res);

        $mod->begin();
        foreach ($prm['names'] as $modName) {
            $obj=new Model($modName);
            $obj->setVal('Name', 'toto');
            $res=$obj->save();
            $this->assertEquals(1, $res);
        }
        
        $res=$mod->end();
        $this->assertTrue($res);
    }

    
    public function testBaseHandler1()
    {
        $prm=[
                'path'=>'C:/Users/pierr/ABridge/Datastore/',
                'host'=>'localhost',
                'user'=>'cl822',
                'pass'=>'cl822'
        ];
        $this->assertTrue(Mod::reset());
        $this->assertEquals([], Mod::get()->initMeta());
        $this->assertNotNull($db1 = Mod::get()->setBase('dataBase', 'test', $prm));
        $this->assertNotNull($db2 = Mod::get()->getBase('dataBase', 'test'));
        $this->assertNull(Mod::get()->getBase('dataBase', 'test2'));
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
        $this->assertTrue(Mod::reset());
        $this->assertNotNull($db1 = Mod::get()->setBase('dataBase', 'test', $prm));
        $this->assertNotNull($db2 = Mod::get()->setBase('fileBase', 'test', $prm));
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
        $this->assertTrue(Mod::reset());
        $this->assertNotNull($db1 = Mod::get()->setBase('fileBase', 'test', $prm));
        $this->assertNotNull($db2 = Mod::get()->setBase('fileBase', 'test1', $prm));
        $r = false;
        try {
            Mod::get()->setBase('nOTEXISTS', 'test1', $prm);
        } catch (Exception $e) {
            $r = true;
        }
        $this->assertTrue($r);
        $this->assertnull($db2 = Mod::get()->getBase('NOTEXISTS', 'NOTEXISTS'));
        $this->assertNotEquals($db1, $db2);
    }
       
    public function testModHandler()
    {
        $this->assertTrue(Mod::reset());
        $this->assertNull(Mod::get()->getClassMod('notexists'));
        $this->assertEquals(get_called_class(), Mod::get()->getClassMod(get_called_class()));
        $this->assertTrue(Mod::get()->assocClassMod(get_called_class(), 'x'));
        $this->assertEquals('x', Mod::get()->getClassMod(get_called_class()));
    }
}
