<?php
    
use ABridge\ABridge\Mod\Model;
use ABridge\ABridge\Mod\Mod;
use ABridge\ABridge\CstError;
use ABridge\ABridge\Mod\Mtype;
use ABridge\ABridge\UtilsC;

class Model_Abst_Test extends PHPUnit_Framework_TestCase
{
    protected static $dbs;
    protected static $prm;
    

    protected $Application;
    protected $Component;
    protected $ABB;
    protected $Exchange;
    protected $db;
    protected $napp=2;
    protected $ncomp=5;
    
    
    public static function setUpBeforeClass()
    {
    
        $classes = ['ABB','Application','Component','Exchange'];
        $baseTypes=['dataBase','fileBase','memBase'];
        $baseName='test';
        
        $prm=UtilsC::genPrm($classes, get_called_class(), $baseTypes);
        
        self::$prm=$prm;
        self::$dbs=[];
        
        Mod::reset();
        Mod::get()->init($prm['application'], $prm['handlers']);
        
        foreach ($baseTypes as $baseType) {
            self::$dbs[$baseType]=Mod::get()->getBase($baseType, $baseName);
        }
    }
    
    public function setTyp($typ)
    {
            $this->db=self::$dbs[$typ];
            $this->ABB=self::$prm[$typ]['ABB'];
            $this->Application=self::$prm[$typ]['Application'];
            $this->Component=self::$prm[$typ]['Component'];
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

        $ABB = new Model($this->ABB);
        $this->assertNotNull($ABB);
        
        $res= $ABB->deleteMod();
        $this->assertTrue($res);
        
        $res = $ABB->addAttr('Bkey', Mtype::M_STRING);
        $this->assertTrue($res);

        $res=$ABB->setProp('Bkey', Model::P_BKY);
        $this->assertTrue($res);

        $res = $ABB->addAttr('Name', Mtype::M_STRING);
        $this->assertTrue($res);

        $res=$ABB->setProp('Name', Model::P_MDT);
        $this->assertTrue($res);
        
        $res = $ABB->addAttr('SurName', Mtype::M_STRING);
        $this->assertTrue($res);
        
        $res=$ABB->setCkey(['Name','SurName'], true);
        $this->assertTrue($res);

        $res = $ABB->addAttr('CDate', Mtype::M_DATE);
        $this->assertTrue($res);

        $res = $ABB->setDflt('CDate', '1959-05-26'); //default
        $this->assertTrue($res);
        
        $res = $ABB->addAttr('Inn', Mtype::M_CREF, '/'.$this->Exchange.'/Inn');
        $this->assertTrue($res);
        
        $res = $ABB->addAttr('Outt', Mtype::M_CREF, '/'.$this->Exchange.'/Outt');
        $this->assertTrue($res);
        
        $res = $ABB->setAbstr();
        $this->assertTrue($res);

        $res = $ABB->isDel();
        $this->assertFalse($res);
        
        $res = $ABB->saveMod();
        $this->assertTrue($res);

        
        $Application = new Model($this->Application);
        $this->assertNotNull($Application);
    
        $res= $Application->deleteMod();
        $this->assertTrue($res);

        $res = $Application->addAttr('Owner', Mtype::M_STRING);
        $this->assertTrue($res);

        $res = $Application->addAttr('BuiltFrom', Mtype::M_CREF, '/'.$this->Component.'/Of');
        $this->assertTrue($res);
        
        $res = $Application->setInhNme($this->ABB);
        $this->assertTrue($res);

        $res = $Application->saveMod();
        $this->assertTrue($res);

        
        $Component = new Model($this->Component);
        $this->assertNotNull($Component);
        
        $res= $Component->deleteMod();
        $this->assertTrue($res);
        
        $res = $Component->addAttr('Type', Mtype::M_STRING);
        $this->assertTrue($res);
        
        $res = $Component->addAttr('Of', Mtype::M_REF, '/'.$this->Application);
        $this->assertTrue($res);

        $res = $Component->setInhNme($this->ABB);
        $this->assertTrue($res);
        
        $res = $Component->saveMod();
        $this->assertTrue($res);

        $Exchange = new Model($this->Exchange);
        $this->assertNotNull($Exchange);
        
        $res= $Exchange->deleteMod();
        $this->assertTrue($res);

        $res = $Exchange->addAttr('Inn', Mtype::M_REF, '/'.$this->ABB);
        $this->assertTrue($res);

        $res = $Exchange->addAttr('Outt', Mtype::M_REF, '/'.$this->ABB);
        $this->assertTrue($res);

        $res=$Exchange->setCkey(['Inn','Outt'], true);
        $this->assertTrue($res);
        
        $res = $Exchange->addAttr('Object', Mtype::M_STRING);
        $this->assertTrue($res);

        $res = $Exchange->saveMod();
        $this->assertTrue($res);
        
        $db->commit();
    }

    /**
     * @dataProvider Provider1
     *
    /**
    * @depends testSaveMod
    */
    public function testNewMod($typ)
    {
        
        $this->setTyp($typ);
        $db=$this->db;
        $db->beginTrans();

        $obj=new Model($this->Application);
        $this->assertNotNull($obj);
        
        $this->assertTrue($obj->existsAttr('Name'));
        $this->assertTrue($obj->existsAttr('Owner'));
        $this->assertTrue($obj->existsAttr('BuiltFrom'));
        $this->assertTrue($obj->existsAttr('Outt'));
        
        $this->assertEquals($obj->getTyp('Name'), Mtype::M_STRING);
        $this->assertTrue($obj->isProp('Name', Model::P_MDT));
        $this->assertTrue($obj->isProp('Bkey', Model::P_BKY));
        $this->assertEquals($obj->getTyp('Owner'), Mtype::M_STRING);
        $this->assertEquals($obj->getTyp('BuiltFrom'), Mtype::M_CREF);
        $this->assertEquals($obj->getTyp('Outt'), Mtype::M_CREF);
        $this->assertEquals($obj->getDflt('CDate'), '1959-05-26');
            
        $id = 1;
        $na = $this->napp;
        $nc = $this->ncomp;
        for ($j=0; $j<$na; $j++) {
            $obj=new Model($this->Application);
            $this->assertNotNull($obj);
            $name = 'App_'.$j;
            $obj->setVal('Name', $name);
            $obj->setVal('Bkey', $name);
            $obj->setVal('Owner', 'Me');
            $this->assertEquals($id, $obj->save());
            $this->assertFalse($obj->isErr());
            $appid = $id;
            $x = $obj;
            $id++;
            for ($i=0; $i<$nc; $i++) {
                $obj=new Model($this->Component);
                $name = 'Name_'.$j.'_'.$i;
                $obj->setVal('Name', $name);
                $obj->setVal('SurName', 'same');
                $obj->setVal('Type', 'Messages');
                $obj->setVal('Of', $appid);
                $this->assertEquals($id, $obj->save());
                $this->assertFalse($obj->isErr());
                if ($i>0) {
                    $obj=new Model($this->Exchange);
                    $pid = $id-1;
                    $obj->setVal('Inn', $id);
                    $obj->setVal('Outt', $pid);
                    $name = $pid . ' -> ' . $id;
                    $obj->setVal('Object', $name);
                    $obj->save();
                    $this->assertFalse($obj->isErr());
                }
                $id++;
            }
            $obj=new Model($this->Exchange);
            $this->assertNotNull($obj);
            $pid = $appid+1;
            $obj->setVal('Inn', $pid);
            $obj->setVal('Outt', $appid);
            $name = $appid. ' -> ' . $pid;
            $obj->setVal('Object', $name);
            $obj->save();
            $this->assertFalse($obj->isErr());
            if ($j >0) {
                $obj=new Model($this->Exchange);
                $pid = $appid-1;
                $obj->setVal('Inn', $appid);
                $obj->setVal('Outt', $pid);
                $name = $pid. ' -> ' . $appid;
                $obj->setVal('Object', $name);
                $obj->save();
                $this->assertFalse($obj->isErr());
            }
        }
        $this->assertEquals(5, count($x->getVal('BuiltFrom')));
        $db->commit();
    }

    /**
     * @dataProvider Provider1
     *
    /**
    * @depends testNewMod
    */
    public function testGetMod($typ)
    {
        $this->setTyp($typ);
        $db=$this->db;
        $db->beginTrans();

        $obj=new Model($this->Application, 1);
        $this->assertNotNull($obj);
        $x = $obj->getVal('Outt');
        $eid = $x[0];
        $exch = $obj->getCref('Outt', $eid);
        $pobj = $exch->getRef('Outt');
        $this->assertEquals($obj, $pobj);

        $this->assertEquals($obj->getAbstrNme(), $this->ABB);
        
        $abstr = $obj->getInhObj();

        $this->assertNull($abstr->getAbstrNme());
        $a = count($obj->getAllAttr());
        $b = count($obj->getAttrList());
        $c = count($abstr->getAllAttr());
        $d = count($obj->getAllPredef());
        
        $this->assertEquals($b, $a+$c-$d);
        
        $end = false;
        $na =1;
        $nc =0;
        
        while (! $end) {
            $x = $obj->getVal('Outt');
            if (count($x)) {
                $eid = $x[0];
                $exch = $obj->getCref('Outt', $eid);
                $obj = $exch->getRef('Inn');
                $modn = $obj -> getModName();
                if ($modn == $this->Component) {
                    $nc++;
                }
                if ($modn == $this->Application) {
                    $na++;
                }
            } else {
                $end =true;
            }
        }
        $this->assertEquals($this->napp, $na);
        $tnc = $this->ncomp * $this->napp;
        $this->assertEquals($tnc, $nc);
        $db->commit();
    }
    /**
     * @dataProvider Provider1
     *
     /**
    * @depends testGetMod
     */
    public function testSelect($typ)
    {
        $this->setTyp($typ);
        $db=$this->db;
        $db->beginTrans();
 
        $obj=new Model($this->ABB);
        $obj->setCriteria([], [], [], []);
        $res = $obj->select();
        $na=  $this->napp + $this->ncomp * $this->napp ;
        $this->assertEquals($na, count($res));
        
        $obj->setCriteria(['Name'], ['Name'=>'::'], ['App'], []);
        $res = $obj->select();
        $na=  $this->napp;
        $this->assertEquals($na, count($res));
        
        $db->commit();
    }
    /**
     * @dataProvider Provider1
     *
    /**
    * @depends testSelect
    */
    public function testDelMod($typ)
    {
        
        $this->setTyp($typ);
        $db=$this->db;
        $db->beginTrans();

        $id=($this->napp * $this->ncomp) + $this->napp - $this->ncomp;
        
        $obj=new Model($this->ABB, $id);
        $this->assertNotNull($obj);
        $modn = $obj -> getModName();
        $this->assertEquals($this->Application, $modn);
        $app = $obj;

        $id = $id+$this->ncomp;
        $obj=new Model($this->ABB, $id);
        $this->assertNotNull($obj);
        $modn = $obj -> getModName();
        $this->assertEquals($this->Component, $modn);

        $comp=$obj;
        $x=$comp->getVal('Outt');
        $this->assertEquals(0, count($x));
        $x=$comp->getVal('Inn');
        $this->assertEquals(1, count($x));

        $id=$x[0];
        $exch=$comp->getCref('Inn', $id);
        $this->assertNotNull($exch);
        
        $this->assertTrue($exch->delet());
        $x=$comp->getVal('Inn');
        $this->assertEquals(0, count($x));
        
        $this->assertTrue($comp->delet());
        
        $x=$app->getVal('BuiltFrom');
        $n = $this->ncomp -1;
        $this->assertEquals($n, count($x));
        
        $db->commit();
    }
    
    /**
     * @dataProvider Provider1
     *
    /**
    * @depends testDelMod
    */
    
    public function testChgAbstr($typ)
    {
        $this->setTyp($typ);
        $db=$this->db;
        $db->beginTrans();

        $ABB = new Model($this->ABB);
        $this->assertNotNull($ABB);
        
        $res = $ABB->addAttr('NewAttr', Mtype::M_STRING);
        $this->assertTrue($res);

        $res= $ABB->saveMod();
        $this->assertFalse($ABB->isErr());
        
        $Application = new Model($this->Application);
        $this->assertNotNull($Application);
        
        $this->assertTrue($Application->existsAttr('NewAttr'));

        $ABB = new Model($this->ABB);
        $this->assertNotNull($ABB);
        
        $res = $ABB->delAttr('NewAttr');
        $this->assertTrue($res);

        $res= $ABB->saveMod();
        $this->assertFalse($ABB->isErr());

        $Application = new Model($this->Application);
        $this->assertNotNull($Application);
        
        $this->assertFalse($Application->existsAttr('NewAttr'));
        
        $db->commit();
    }
    
    /**
     * @dataProvider Provider1
     *
    /**
    * @depends testChgAbstr
    */
    
    public function testErr($typ)
    {
        $this->setTyp($typ);
        $db=$this->db;
        $db->beginTrans();

        $comp = new Model($this->Application);

        $attr ='NotExists';
        $comp->getTyp($attr);
        $this->assertEquals(CstError::E_ERC002.':'.$attr, $comp->getErrLine());
        
        
        $comp->setVal('Name', 'newName');
        $comp->setVal('Surname', 'same');
        $comp->setVal('Bkey', 'App_0');
        $this->assertFalse($comp->save());
        $this->assertEquals($comp->getErrLine(), CstError::E_ERC018.':Bkey:App_0');
        
        
        $comp = new Model($this->Component);

        $this->assertFalse($comp->save());
        $this->assertEquals($comp->getErrLine(), CstError::E_ERC019.':Name');

        $comp->setVal('Name', 'Name_0_0');
        $comp->setVal('SurName', 'same');
        $this->assertFalse($comp->save());
        $this->assertEquals($comp->getErrLine(), CstError::E_ERC031.':Name:SurName');
  
        
        $ABB = new Model($this->ABB);
        $this->assertNotNull($ABB);

        $ABB->save();
        $this->assertEquals($ABB->getErrLine(), CstError::E_ERC044);

        $ABB->setVal('Name', 'XX');
        $this->assertEquals($ABB->getErrLine(), CstError::E_ERC045);
        
        $ABB->delet();
        $this->assertEquals($ABB->getErrLine(), CstError::E_ERC044);

        $db->commit();
    }
}
