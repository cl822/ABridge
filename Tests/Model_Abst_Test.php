<?php
	
require_once("Model.php"); 
require_once("Handler.php"); 

class Model_Abst_Test extends PHPUnit_Framework_TestCase  
{
	protected static $db1;
	protected static $db2;


	protected $Application='Application';		
	protected $Component='Component';		
	protected $ABB='ABB';
	protected $Exchange='Exchange';
	protected $db;
	protected $napp=2;
	protected $ncomp=5;
	
	
	public static function setUpBeforeClass()
	{	
	
		resetHandlers();
		$typ='dataBase';
		$name='atest';	
		$Application='Application';		
		$Component='Component';		
		$ABB='ABB';
		$Exchange='Exchange';
		
		self::$db1=getBaseHandler ($typ, $name);
		initStateHandler ($Application		,$typ, $name);
		initStateHandler ($Component	,$typ, $name);
		initStateHandler ($ABB	,$typ, $name);
		initStateHandler ($Exchange	,$typ, $name);
		
		$typ='fileBase';
		$name=$name.'_f';
		$Application='Applicationf';		
		$Component='Componentf';		
		$ABB='ABBf';
		$Exchange='Exchangef';		
		self::$db2=getBaseHandler ($typ, $name);
		initStateHandler ($Application		,$typ, $name);
		initStateHandler ($Component	,$typ, $name);
		initStateHandler ($ABB	,$typ, $name);
		initStateHandler ($Exchange	,$typ, $name);
		
	}
	
	public function setTyp ($typ) 
	{
		if ($typ== 'SQL') {
			$this->db=self::$db1;
			$this->ABB='ABB';
			$this->Application='Application';
			$this->Component='Component';
			$this->Exchange='Exchange';			
			} 
		else {
			$this->db=self::$db2;
			$this->ABB='ABBf';
			$this->Application='Applicationf';
			$this->Component='Componentf';
			$this->Exchange='Exchangef';					
			}

	}
	
	public function Provider1() 
	{
		return [['SQL'],['FLE']];
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

		$res = $ABB->addAttr('Name',M_STRING); 
		$this->assertTrue($res);	

		$res=$ABB->setMdtr('Name',true); // Mdtr
		$this->assertTrue($res);
			
		$res = $ABB->addAttr('SurName',M_STRING); 
		$this->assertTrue($res);	
		
		$res=$ABB->setCkey(['Name','SurName'],true);	
		$this->assertTrue($res);

		$res = $ABB->addAttr('CDate',M_DATE); 
		$this->assertTrue($res);	

		$res = $ABB->setDflt('CDate','1959-05-26'); //default
		$this->assertTrue($res);
		
		$res = $ABB->addAttr('Inn',M_CREF,'/'.$this->Exchange.'/Inn'); 
		$this->assertTrue($res);
		
		$res = $ABB->addAttr('Outt',M_CREF,'/'.$this->Exchange.'/Outt'); 
		$this->assertTrue($res);		
		
		$res = $ABB->setAbstr(); 
		$this->assertTrue($res);	
		
		$res = $ABB->saveMod();	
		$this->assertTrue($res);	

		
		$Application = new Model($this->Application);
		$this->assertNotNull($Application);	
	
		$res= $Application->deleteMod();
		$this->assertTrue($res);	

		$res = $Application->addAttr('Owner',M_STRING);		
		$this->assertTrue($res);	

		$res = $Application->addAttr('BuiltFrom',M_CREF,'/'.$this->Component.'/Of'); 
		$this->assertTrue($res);
		
		$res = $Application->setInhNme($this->ABB);
		$this->assertTrue($res);	

		$res = $Application->saveMod();	
		$this->assertTrue($res);			

		
		$Component = new Model($this->Component);
		$this->assertNotNull($Component);	
		
		$res= $Component->deleteMod();
		$this->assertTrue($res);	
		
		$res = $Component->addAttr('Type',M_STRING); 
		$this->assertTrue($res);	
		
		$res = $Component->addAttr('Of',M_REF,'/'.$this->Application); 
		$this->assertTrue($res);

		$res = $Component->setInhNme($this->ABB);
		$this->assertTrue($res);
		
		$res = $Component->saveMod();	
		$this->assertTrue($res);	

		$Exchange = new Model($this->Exchange);
		$this->assertNotNull($Exchange);	
		
		$res= $Exchange->deleteMod();
		$this->assertTrue($res);	

		$res = $Exchange->addAttr('Inn',M_REF,'/'.$this->ABB); 
		$this->assertTrue($res);

		$res = $Exchange->addAttr('Outt',M_REF,'/'.$this->ABB); 
		$this->assertTrue($res);

		$res=$Exchange->setCkey(['Inn','Outt'],true);	
		$this->assertTrue($res);
		
		$res = $Exchange->addAttr('Object',M_STRING);		
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
		
		$this->assertEquals($obj->getTyp('Name'),M_STRING);
		$this->assertTrue($obj->isMdtr('Name'));
		$this->assertEquals($obj->getTyp('Owner'),M_STRING);
		$this->assertEquals($obj->getTyp('BuiltFrom'),M_CREF);
		$this->assertEquals($obj->getTyp('Outt'),M_CREF);
		$this->assertEquals($obj->getDflt('CDate'),'1959-05-26');
			
		$id = 1;
		$na = $this->napp;
		$nc = $this->ncomp;
		for ($j=0;$j<$na;$j++) {
			$obj=new Model($this->Application);
			$this->assertNotNull($obj);	
			$name = 'App_'.$j;
			$obj->setVal('Name',$name);
			$obj->setVal('Owner','Me');			
			$this->assertEquals($id,$obj->save());
			$this->assertFalse($obj->isErr());
			$appid = $id;
			$x = $obj;
			$id++;
			for ($i=0;$i<$nc;$i++) {
				$obj=new Model($this->Component);
				$name = 'Name_'.$j.'_'.$i;
				$obj->setVal('Name',$name);
				$obj->setVal('SurName','same');
				$obj->setVal('Type','Messages');
				$obj->setVal('Of',$appid);
				$this->assertEquals($id,$obj->save());
				$this->assertFalse($obj->isErr());				
				if($i>0){
					$obj=new Model($this->Exchange);
					$pid = $id-1;
					$obj->setVal('Inn',$id);
					$obj->setVal('Outt',$pid);
					$name = $pid . ' -> ' . $id;
					$obj->setVal('Object',$name);
					$obj->save();
					$this->assertFalse($obj->isErr());		
				}
				$id++;
			}
			$obj=new Model($this->Exchange);
			$this->assertNotNull($obj);
			$pid = $appid+1;
			$obj->setVal('Inn',$pid);
			$obj->setVal('Outt',$appid);
			$name = $appid. ' -> ' . $pid;
			$obj->setVal('Object',$name);
			$obj->save();
			$this->assertFalse($obj->isErr());
			if($j >0) {
				$obj=new Model($this->Exchange);
				$pid = $appid-1;
				$obj->setVal('Inn',$appid);
				$obj->setVal('Outt',$pid);
				$name = $pid. ' -> ' . $appid;
				$obj->setVal('Object',$name);
				$obj->save();
				$this->assertFalse($obj->isErr());
			}
		}			
		$this->assertEquals(5,count($x->getVal('BuiltFrom')));		
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

		$obj=new Model($this->Application,1);
		$this->assertNotNull($obj);	
		$x = $obj->getVal('Outt');
		$eid = $x[0];	
		$exch = $obj->getCref('Outt',$eid);
		$pobj = $exch->getRef('Outt');
		$this->assertEquals($obj,$pobj);

		$this->assertEquals($obj->getAbstrNme(),$this->ABB);
		
		$abstr = $obj->getInhObj();

		$this->assertNull($abstr->getAbstrNme());
		$a = count($obj->getAllAttr());
		$b = count($obj->getAttrList());
		$c = count($abstr->getAllAttr());
		$d = count($obj->getAllPredef());
		
		$this->assertEquals($b,$a+$c-$d);
		
		$end = false; 
		$na =1;
		$nc =0;
		
		while (! $end) {			
			$x = $obj->getVal('Outt');
			if (count($x)) {
				$eid = $x[0];	
				$exch = $obj->getCref('Outt',$eid);
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
		$this->assertEquals($this->napp,$na);
		$tnc = $this->ncomp * $this->napp;
		$this->assertEquals($tnc,$nc);			
		$db->commit();
		
	}
	
	/**
     * @dataProvider Provider1
     *	
	/**
    * @depends testGetMod
    */
	public function testDelMod($typ) 
	{
		
		$this->setTyp($typ);
		$db=$this->db;
		$db->beginTrans();

		$id=($this->napp * $this->ncomp) + $this->napp - $this->ncomp;
		
		$obj=new Model($this->ABB,$id);
		$this->assertNotNull($obj);	
		$modn = $obj -> getModName();
		$this->assertEquals($this->Application,$modn);
		$app = $obj;

		$id = $id+$this->ncomp;
		$obj=new Model($this->ABB,$id);
		$this->assertNotNull($obj);	
		$modn = $obj -> getModName();
		$this->assertEquals($this->Component,$modn);

		$comp=$obj;
		$x=$comp->getVal('Outt');
		$this->assertEquals(0,count($x));
		$x=$comp->getVal('Inn');
		$this->assertEquals(1,count($x));

		$id=$x[0];
		$exch=$comp->getCref('Inn',$id);
		$this->assertNotNull($exch);	
		
		$this->assertTrue($exch->delet());
		$x=$comp->getVal('Inn');
		$this->assertEquals(0,count($x));
		
		$this->assertTrue($comp->delet());
		
		$x=$app->getVal('BuiltFrom');
		$n = $this->ncomp -1;
		$this->assertEquals($n,count($x));
		
		$db->commit();
		

	}
	/**
     * @dataProvider Provider1
     *	
	/**
    * @depends testDelMod
    */
	public function testErr($typ) 
	{
		$this->setTyp($typ);
		$db=$this->db;
		$db->beginTrans();

		$comp = new Model($this->Component);

		$this->assertFalse($comp->save());
		$this->assertEquals($comp->getErrLine(), E_ERC019.':Name');			

		$comp->setVal('Name','Name_0_0');
		$comp->setVal('SurName','same');
		$this->assertFalse($comp->save());
		$this->assertEquals($comp->getErrLine(), E_ERC031.':Name:SurName');		
		
		$ABB = new Model($this->ABB);
		$this->assertNotNull($ABB);	

		$ABB->save();
		$this->assertEquals($ABB->getErrLine(), E_ERC044);	

		$ABB->setVal('Name','XX');
		$this->assertEquals($ABB->getErrLine(), E_ERC045);	
		
		$ABB->delet();
		$this->assertEquals($ABB->getErrLine(), E_ERC044);	

		$db->commit();
				
		
	}
	
}

?>	