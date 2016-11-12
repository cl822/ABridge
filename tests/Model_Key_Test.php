<?php
	
require_once("Model.php"); 
require_once("Handler.php"); 

class Model_Key_Test extends PHPUnit_Framework_TestCase  
{
	protected static $db1;
	protected static $db2;


	protected $Code='Code';		
	protected $CodeVal='CodeVal';		
	protected $Student='Student';
	protected $db;
	
	
	public static function setUpBeforeClass()
	{	
	
		resetHandlers();
		$typ='dataBase';
		$name='atest';	
		$Code='Code';		
		$CodeVal='CodeVal';		
		$Student='Student';
		self::$db1=getBaseHandler ($typ, $name);
		initStateHandler ($Code		,$typ, $name);
		initStateHandler ($CodeVal	,$typ, $name);
		initStateHandler ($Student	,$typ, $name);
		
		$typ='fileBase';
		$name=$name.'_f';
		$Code='Codef';		
		$CodeVal='CodeValf';		
		$Student='Studentf';
		self::$db2=getBaseHandler ($typ, $name);
		initStateHandler ($Code		,$typ, $name);
		initStateHandler ($CodeVal	,$typ, $name);
		initStateHandler ($Student	,$typ, $name);
		
	}
	
	public function setTyp ($typ) 
	{
		if ($typ== 'SQL') {
			$this->db=self::$db1;
			$this->Student='Student';
			$this->Code='Code';
			$this->CodeVal='CodeVal';
			} 
		else {
			$this->db=self::$db2;
			$this->Student='Studentf';
			$this->Code='Codef';
			$this->CodeVal='CodeValf';
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
	
		// Ref -CodeVal
		$codeval = new Model($this->CodeVal);
		$this->assertNotNull($codeval);	
		
		$res= $codeval->deleteMod();
		$this->assertTrue($res);	
		
		$path='/'.$this->Code;
		$res = $codeval->addAttr('ValueOf',M_REF,$path);
		$this->assertTrue($res);	

		$res=$codeval->setMdtr('ValueOf',true); // Mdtr
		$this->assertTrue($res);
		
		$res = $codeval->saveMod();	
		$this->assertTrue($res);	

		$r = $codeval-> getErrLog ();
		$this->assertEquals($r->logSize(),0);	
		
		// CRef - Code
		$code = new Model($this->Code);
		$this->assertNotNull($code);	
	
		$res= $code->deleteMod();
		$this->assertTrue($res);	
		
		$res = $code->addAttr('CodeName'); 
		$this->assertTrue($res);	
		
		$res=$code->setBkey('CodeName',true);// unique
		$this->assertTrue($res);
		
		$path='/'.$this->CodeVal.'/ValueOf';
		$res = $code->addAttr('Values',M_CREF,$path);
		$this->assertTrue($res);	
		
		$res = $code->saveMod();	
		$this->assertTrue($res);	

		$r = $code-> getErrLog ();
		$this->assertEquals($r->logSize(),0);			
		
		$db->commit();

	}

	/**
     * @dataProvider Provider1
     *	
	/**
    * @depends testSaveMod
    */
	public function testNewCode($typ) 
	{
		
		$this->setTyp($typ);
		$db=$this->db;
		$db->beginTrans();

		// Sexe
		
		$code = new Model($this->Code);
		$this->assertNotNull($code);	
		
		$res = $code->setVal('CodeName','Sexe');
		$this->assertTrue($res);
		
		$id = $code->save();
		$this->assertEquals($id,1);	

		$r = $code-> getErrLog ();
		$this->assertEquals($r->logSize(),0);	

		$db->commit();

		//  Male
		$codeval = new Model($this->CodeVal);
		$this->assertNotNull($codeval);	
		
		$res = $codeval->setVal('ValueOf',$id);
		$this->assertTrue($res);
		
		$id1= $codeval->save();
		$this->assertEquals($id1,1);	

		$r = $codeval-> getErrLog ();
		$this->assertEquals($r->logSize(),0);	

		$db->commit();
	}
	/**
     * @dataProvider Provider1
     *	
	/**
    * @depends testNewCode
    */
		public function testErrors($typ) 
	{
		
		$this->setTyp($typ);
		$db=$this->db;
		$db->beginTrans();

		$code = new Model($this->Code);
		$this->assertNotNull($code);	
		$log = $code->getErrLog ();
		
		$res = $code->setVal('CodeName','Sexe');
		$this->assertFalse($res);
		$this->assertEquals($log->getLine(0),E_ERC018.':CodeName:Sexe');
						
		$codeval = new Model($this->CodeVal);
		$this->assertNotNull($codeval);	
		$log = $codeval->getErrLog ();
		
		$id1= $codeval->save();
		$this->assertEquals($id1,0);	

		$r = $codeval-> getErrLog ();
		$this->assertEquals($log->getLine(0),E_ERC019.':ValueOf');	
		
		$db->commit();
	}
	
	
}

?>	