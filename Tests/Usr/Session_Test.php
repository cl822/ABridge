<?php
use ABridge\ABridge\UtilsC;
use ABridge\ABridge\Mod\Find;


use ABridge\ABridge\Usr\Session;

class Session_Test_dataBase_1 extends Session
{
}
class Session_Test_fileBase_1 extends Session
{
}


class Session_Test extends PHPUnit_Framework_TestCase
{

    public function testInit()
    {
        $prm=[
                'path'=>'C:/Users/pierr/ABridge/Datastore/',
                'host'=>'localhost',
                'user'=>'cl822',
                'pass'=>'cl822'
        ];
        $name = 'test';
        $classes = ['Session'];
        $bsname = get_called_class();
        $bases= UtilsC::initHandlers($name, $classes, $bsname, $prm);
        $res = UtilsC::initClasses($bases);
        $this->assertTrue($res);
        return $bases;
    }
    /**
    * @depends testInit
    */
    public function testsave($bases)
    {
        $idl = [];
        foreach ($bases as $base) {
            list($db,$bd) = $base;
            
            $db->beginTrans();
            $obj=$bd['Session']::getSession(0);
            $x = $obj->getMod();

            $idl[] = $obj->getKey();
            
            $res=$x->save();
            $this->assertEquals(1, $res);
     
            $db->commit();
        }
        
        return [$bases,$idl];
    }

    /**
    * @depends  testsave
    */
    
    public function testKey($basesId)
    {
        list($bases,$idl) = $basesId;
        $i=0;
        
        foreach ($bases as $base) {
            list($db,$bd) = $base;
            
            $db->beginTrans();
            
            $obj = $bd['Session']::getSession($idl[$i]);

        
            $this->assertEquals($idl[$i], $obj->getKey());
            
            $i++;
            $db->commit();
        }
        
        return $basesId;
    }
 
    /**
    * @depends  testKey
    */

    public function testdel($basesId)
    {
        
        list($bases,$idl) = $basesId;
        $i=0;
        
        foreach ($bases as $base) {
            list($db,$bd) = $base;
            
            $db->beginTrans();
        
            $obj = $bd['Session']::getSession($idl[$i]);
            $mod= $obj->getMod();
        
            $mod->delet();
            
            $this->assertEquals(0, $mod->getValN('ValidFlag'));
            
            $i++;
            $db->commit();
        }
        
        return $basesId;
    }
    
    /**
    * @depends  testdel
    */
    public function testdel2($basesId)
    {
        
        list($bases,$idl) = $basesId;
        $i=0;
        
        foreach ($bases as $base) {
            list($db,$bd) = $base;
            
            $db->beginTrans();

            $obj = $bd['Session']::getSession($idl[$i]);
            $mod= $obj->getMod();
  
            $this->assertTrue($obj->isNew());
            $res=$mod->save();
            
            $this->assertEquals(2, $res);
            
            $mod->delet();

            $x = Find::byKey($bd['Session'], 'BKey', $idl[$i]);
            $this->assertNull($x);
            
            $i++;
            $db->commit();
        }
        
        return $basesId;
    }
}