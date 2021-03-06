<?php
use ABridge\ABridge\UtilsC;

use ABridge\ABridge\Mod\Model;
use ABridge\ABridge\Mod\Mod;

use ABridge\ABridge\Usr\User;
use ABridge\ABridge\Usr\Role;
use ABridge\ABridge\Usr\Distribution;
use ABridge\ABridge\CstError;
use ABridge\ABridge\Mod\ModUtils;

class User_Distribution_Test_dataBase_User extends User
{
}
class User_Distribution_Test_fileBase_User extends User
{
}

class User_Distribution_Test_dataBase_Role extends Role
{
}
class User_Distribution_Test_fileBase_Role extends Role
{
}

class User_Distribution_Test_dataBase_Distribution extends Distribution
{
}
class User_Distribution_Test_fileBase_Distribution extends Distribution
{
}


class User_Distribution_Test extends PHPUnit_Framework_TestCase
{

    
    public function testInit()
    {
        $classes = ['User','Role','Distribution'];
        
        $prm=UtilsC::genPrm($classes, get_called_class());
        
        Mod::reset();
        
        $mod= Mod::get();
        
        $mod->init($prm['application'], $prm['handlers']);
        
        $mod->begin();
        
        $res = ModUtils::initModBindings($prm['dataBase']);
        $res = ($res && ModUtils::initModBindings($prm['fileBase']));
        
        $mod->end();
        
        $this->assertTrue($res);
        
        return $prm;
    }
 
    /**
    * @depends testInit
    */
    public function testsave($prm)
    {
        $mod= Mod::get();
        
        foreach ($prm['bindL'] as $bd) {
            $mod->begin();
            
            $x = new Model($bd['User']);
            $res=$x->save();
            $this->assertEquals(1, $res);
     
            $x = new Model($bd['Role']);
            $res=$x->save();
            $this->assertEquals(1, $res);
     
            $x = new Model($bd['Role']);
            $res=$x->save();
            $this->assertEquals(2, $res);
            
            $x = new Model($bd['Role']);
            $res=$x->save();
            $this->assertEquals(3, $res);

            $x = new Model($bd['Distribution']);
            $x->setVal('Role', 1);
            $x->setVal('User', 1);
            $res=$x->save();
            $this->assertEquals(1, $res);

            $x = new Model($bd['Distribution']);
            $x->setVal('Role', 3);
            $x->setVal('User', 1);
            $res=$x->save();
            $this->assertEquals(2, $res);
            
            $obj = $x->getCobj();
            $res= $obj->initMod([]);
            $this->assertFalse($res);
            
            $mod->end();
        }
        return $prm;
    }
 
    /**
    * @depends  testsave
    */
    
    public function testset($prm)
    {
        
        $mod= Mod::get();
        
        foreach ($prm['bindL'] as $bd) {
            $mod->begin();
            
            $x = new Model($bd['User'], 1);
            $res= $x->getValues('Role');
            $this->assertEquals([1,3], $res);
            
            $obj=$x->getCobj();
            $res = $obj->checkAttr('Role', 2);
            $this->assertFalse($res);

            $res = $obj->checkAttr('Role', 1);
            $this->assertTrue($res);
            
            $x->setVal('Role', 1);
            $x->save();
            $this->assertFalse($x->isErr());
            
            $res= $x->setVal('Role', 2);
            $this->assertFalse($res);

            $d = new Model($bd['Distribution'], 1);
            $res= $d->delet();
            $this->assertFalse($res);
            $this->assertEquals(CstError::E_ERC052.':UserRole', $d->getErrLine());
            
            $d = new Model($bd['Distribution'], 2);
            $res= $d->delet();
            $this->assertTrue($res);

            $mod->end();
        }
        return $prm;
    }
}
