<?php

use ABridge\ABridge\UtilsC;
use ABridge\ABridge\Mod\Model;
use ABridge\ABridge\CstError;

use ABridge\ABridge\Usr\User;
use ABridge\ABridge\Usr\Role;
use ABridge\ABridge\Usr\Distribution;
use ABridge\ABridge\Usr\Session;
use ABridge\ABridge\Usr\UserGroup;
use ABridge\ABridge\Usr\GroupUser;

class Session_All_Test_dataBase_2 extends User
{
}
class Session_All_Test_fileBase_2 extends User
{
}

class Session_All_Test_dataBase_3 extends Role
{
}
class Session_All_Test_fileBase_3 extends Role
{
}

class Session_All_Test_dataBase_1 extends Session
{
}
class Session_All_Test_fileBase_1 extends Session
{
}

class Session_All_Test_dataBase_4 extends Distribution
{
}
class Session_All_Test_fileBase_4 extends Distribution
{
}

class Session_All_Test_dataBase_5 extends UserGroup
{
}
class Session_All_Test_fileBase_5 extends UserGroup
{
}

class Session_All_Test_dataBase_6 extends GroupUser
{
}
class Session_All_Test_fileBase_6 extends GroupUser
{
}


class Session_All_Test extends PHPUnit_Framework_TestCase
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
        $classes = ['Session','User','Role','Distribution','UserGroup','GroupUser'];
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
            
            $x = new Model($bd['Role']);
            $x->setVal('Name', 'Default');
            $res=$x->save();
            $this->assertEquals(1, $res);

            $x = new Model($bd['Role']);
            $x->setVal('Name', 'test');
            $res=$x->save();
            $this->assertEquals(2, $res);
 
            $x = new Model($bd['UserGroup']);
            $x->setVal('Name', 'test1');
            $res=$x->save();
            $this->assertEquals(1, $res);

            $x = new Model($bd['UserGroup']);
            $x->setVal('Name', 'test2');
            $res=$x->save();
            $this->assertEquals(2, $res);
            
            $x = new Model($bd['User']);
            $x->setVal('UserId', 'test');
            $res=$x->save();
            $this->assertEquals(1, $res);

            $x = new Model($bd['GroupUser']);
            $x->setVal('UserGroup', 1);
            $x->setVal('User', 1);
            $res=$x->save();
            $this->assertEquals(1, $res);
            
            $x = new Model($bd['Distribution']);
            $x->setVal('Role', 1);
            $x->setVal('User', 1);
            $res=$x->save();
            $this->assertEquals(1, $res);
            
            $x = new Model($bd['Session']);
            $res=$x->save();
            $this->assertEquals(1, $res);

            $db->commit();
        }
        
        return [$bases,$idl];
    }


    /**
    * @depends  testsave
    */
    
    public function testsave1($basesId)
    {
        list($bases,$idl) = $basesId;
        $i=0;
        
        foreach ($bases as $base) {
            list($db,$bd) = $base;
            
            $db->beginTrans();
            
            $x = new Model($bd['Session'], 1);
            
            $x->setVal('UserId', 'test');
            $x->save();
            $this->assertEquals(CstError::E_ERC064.":".$bd['Role'], $x->getErrLine());
      
            $x->setVal('RoleName', 'test');
            $x->save();
            $this->assertEquals(CstError::E_ERC060.":test", $x->getErrLine());
                       
            $x->setVal('UserId', null);
            $x->setVal('RoleName', 'test');
            $x->save();
            $this->assertEquals(CstError::E_ERC060.":test", $x->getErrLine());
 
            $x->setVal('UserId', null);
            $x->setVal('RoleName', null);
            $x->setVal('GroupName', 'NotExists');
            $x->save();
            $this->assertEquals(CstError::E_ERC059.":".$bd['UserGroup'].":NotExists", $x->getErrLine());
            
            $x->setVal('UserId', 'test');
            $x->setVal('RoleName', 'Default');
            $x->setVal('GroupName', 'test2');
            $x->save();
            $this->assertEquals(CstError::E_ERC060.":test2", $x->getErrLine());
            $db->commit();
        }
        
        return $basesId;
    }
 
    /**
    * @depends  testsave1
    */
    
    public function testsave2($basesId)
    {
        list($bases,$idl) = $basesId;
        $i=0;
        
        foreach ($bases as $base) {
            list($db,$bd) = $base;
            
            $db->beginTrans();
            
            $x=new Model($bd['User'], 1);
            $x->setVal('Role', 1);
            $x->setVal('UserGroup', 1);
            $x->save();
            
            $x = new Model($bd['Session'], 1);
            
            $x->setVal('UserId', null);
            $x->setVal('RoleName', null);
            $x->setVal('GroupName', null);
            
            $x->save();
            $this->assertFalse($x->isErr());
            $this->assertEquals($x->getVal('ActiveRole'), 1);
            $this->assertNull($x->getVal('ActiveGroup'));

            
            $x->setVal('UserId', null);
            $x->setVal('RoleName', 'Default');
            $x->setVal('GroupName', null);
            
            $x->save();
            $this->assertFalse($x->isErr());
            $this->assertEquals($x->getVal('ActiveRole'), 1);
           
            
            $x->setVal('UserId', 'test');
            $x->setVal('RoleName', null);
            $x->setVal('GroupName', null);
            
            $x->save();
            $this->assertFalse($x->isErr());
            $this->assertEquals($x->getVal('ActiveRole'), 1);
            $this->assertEquals($x->getVal('ActiveGroup'), 1);
 
            $x->setVal('GroupName', 'test1');
            $x->save();
            $this->assertFalse($x->isErr());
            $this->assertEquals($x->getVal('ActiveGroup'), 1);
            
            $db->commit();
        }
        
        return $basesId;
    }
    
    /**
    * @depends  testsave2
    */
    
    public function testsave3($basesId)
    {
        list($bases,$idl) = $basesId;
        $i=0;
        
        foreach ($bases as $base) {
            list($db,$bd) = $base;
            
            $db->beginTrans();
                        
            $x = new Model($bd['Session'], 1);
                     
            
            $x->setVal('UserId', 'test');
            $x->setVal('RoleName', null);

            $x->save();
            $this->assertFalse($x->isErr());
            
            $y = new Model($bd['Session']);
            $obj = $y->getCobj();
            $obj->initPrev($x);
            
            $y->save();
            $this->assertFalse($y->isErr());
            
            $this->assertEquals('Default', $y->getVal('RoleName'));
            $this->assertEquals('test1', $y->getVal('GroupName'));
            
            $db->commit();
        }
        
        return $basesId;
    }
}
