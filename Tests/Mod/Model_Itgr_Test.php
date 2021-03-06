<?php
    
use ABridge\ABridge\Mod\Model;
use ABridge\ABridge\Mod\Mod;
use ABridge\ABridge\UtilsC;
use ABridge\ABridge\Mod\Mtype;

class Model_Rig_Test extends PHPUnit_Framework_TestCase
{
    protected static $dbs;
    protected static $prm;
    
    protected $Application='AApplication';
    protected $AbstrApp='AbstrApp';
    protected $Code='ACode';
    protected $Exchange='Exchange';
    protected $db;
    protected $napp=2;
    protected $ncomp=5;
    
    
    public static function setUpBeforeClass()
    {
        $classes = ['AbstrApp','Application','Code','Exchange'];
        $baseTypes=['dataBase','fileBase','memBase'];
        $baseName='test';
        
        $prm=UtilsC::genPrm($classes, get_called_class(), $baseTypes);
        
        self::$prm=$prm;
        self::$dbs=[];
        
        Mod::reset();
        Mod::get()->init($prm['application'], $prm['handlers']);
        
        foreach ($baseTypes as $baseType) {
            self::$dbs[$baseType]=Mod::get()->getBase($baseType, $baseName);
        };
    }
    
    public function setTyp($typ)
    {
        $this->db=self::$dbs[$typ];
        $this->AbstrApp=self::$prm[$typ]['AbstrApp'];
        $this->Application=self::$prm[$typ]['Application'];
        $this->Code=self::$prm[$typ]['Code'];
        $this->Exchange=self::$prm[$typ]['Exchange'];
    }
    
    public function Provider1()
    {
        return [['dataBase'],['fileBase'],['memBase']];
    }
    /**
     * @dataProvider Provider1
     */

    public function testSaveMod($typ)
    {
        $this->setTyp($typ);
        $db=$this->db;
        $db->beginTrans();
        
        $Code = new Model($this->Code);
        $res= $Code->deleteMod();

        $AbstrApp = new Model($this->AbstrApp);
        $res= $AbstrApp->deleteMod();
        
        $this->assertTrue($db->checkFKey(true));

        $res = $AbstrApp->setAbstr();
        $this->assertTrue($res);
        $res = $AbstrApp->saveMod();
            
        $res = $Code->addAttr('CodeVal', Mtype::M_STRING);
        $res = $Code->saveMod();
        $res = $Code->setVal('CodeVal', 'V1');
        $v1 = $Code->save();
        $this->assertFalse($Code->isErr());

        $Code = new Model($this->Code);
        $res = $Code->setVal('CodeVal', 'V2');
        $v2 = $Code->save();
        $this->assertFalse($Code->isErr());

        $Application = new Model($this->Application);
        $res= $Application->deleteMod();

        $res = $Application->setInhNme($this->AbstrApp);
        $res = $Application->addAttr('Name', Mtype::M_STRING);
        $res = $Application->addAttr('Code', Mtype::M_REF, '/'.$this->Code);
        $res = $Application->saveMod();
        $res = $Application->setVal('Code', $v1);
        $res = $Application->setVal('Name', 'App');
        $res = $Application->save();
        $this->assertFalse($Application->isErr());

        $db->commit();
    }

    
    /**
     * @dataProvider Provider1
     *
    /**
    * @depends testSaveMod
    */
    public function testREfInt($typ)
    {
        $this->setTyp($typ);
        $db=$this->db;
        $db->beginTrans();

        $obj = new Model($this->Code, 2);
        $this->assertTrue($obj->delet());

        $this->assertFalse($obj->isErr());
        $db->commit();
    }
    /**
     * @dataProvider Provider1
     *
    /**
    * @depends testREfInt
    */
    public function testREfInt2($typ)
    {
        $this->setTyp($typ);
        $db=$this->db;
        $db->beginTrans();

        $obj = new Model($this->Code, 1);
        $res = true;
        $res= $obj->delet();

        if ($typ == 'dataBase') {
            $this->assertFalse($res);
            $this->assertTrue($obj->isErr());
            $this->assertTrue($db->checkFKey(false));
            $res= $obj->delet();
        }
        
        $this->assertTrue($res);
        $db->commit();
    }
    
        /**
     * @dataProvider Provider1
     *
    /**
    * @depends testREfInt2
    */
    public function testChangeMod($typ)
    {
        $this->setTyp($typ);
        $db=$this->db;
        $db->beginTrans();
        $this->assertTrue($db->checkFKey(true));
            
        $Application = new Model($this->Application);
        
        $res = $Application->addAttr('Code2', Mtype::M_REF, '/'.$this->Code);
        $res = $Application->addAttr('Code3', Mtype::M_REF, '/'.$this->Code);
        $res = $Application->saveMod();
        
        $this->assertFalse($Application->isErr());
        
        $db->commit();
    }
    
    /**
     * @dataProvider Provider1
     *
    /**
    * @depends testChangeMod
    */
    public function testdelMod($typ)
    {
        $this->setTyp($typ);
        $db=$this->db;
        $db->beginTrans();
        
        $Application = new Model($this->Application);
        
        $this->assertTrue($Application->existsAttr('Code2'));
        
        $res = $Application->delAttr('Code3');
        
        $res = $Application->saveMod();

        $this->assertFalse($Application->isErr());
        
        $db->commit();
    }
    /**
     * @dataProvider Provider1
     *
    /**
    * @depends testdelMod
    */
    public function testend($typ)
    {
        $this->setTyp($typ);
        $db=$this->db;
        $db->beginTrans();
        
        $Application = new Model($this->Application);
        
        $this->assertFalse($Application->existsAttr('Code3'));

        $this->assertTrue($Application->deleteMod());
        
        $db->commit();
    }
}
