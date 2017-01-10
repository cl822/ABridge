<?php
	
require_once("Model.php"); 
require_once("Handler.php"); 

class testevalP 
{
	private $_mod;
	private $_fsave = true;
	protected $_fdel  = true;
	
	function __construct($mod) 
	{
		$this->_mod=$mod;
	}
	
	
	public function save()
	{
		$a = $this->_mod->getVal('a');
		$b = $this->_mod->getVal('b');
		$this->_mod->setVal('aplusb',$a+$b);
		if (!$a and $this->_mod->getId() and $this->_fsave) {
			$this->_mod->getErrLog()->logLine('wrong');
			$this->_fsave = false;
			return false;
		}
		return true;
	}
	
	public function afterDelet() 
	{
		if(!$this->_fdel) {
			$this->_mod->getErrLog()->logLine('DDwrong');
		}
		return 	$this->_fdel;
	}	

	public function delet()
	{
		$a = $this->_mod->getVal('a');
		if (!$a and $this->_mod->getId() and $this->_fdel) {
			$this->_mod->getErrLog()->logLine('Dwrong');
			$this->_fdel = false;
			return false;
		}
		return true;
	}
	public function afterSave()
	{
		if (!$this->_fsave) {
			$this->_mod->getErrLog()->logLine('Awrong');
		}
		return $this->_fsave;
	}
}
class testevalPF 
{
	private $_mod;
	private $_x;
	private $_fsave = true;
	private $_fdel  = true;
	
	function __construct($mod) 
	{
		$this->_mod=$mod;
	}
	
	
	public function save()
	{
		$a = $this->_mod->getVal('a');
		$b = $this->_mod->getVal('b');
		$this->_mod->setVal('aplusb',$a+$b);
		if (!$a and $this->_mod->getId()and $this->_fsave) {
			$this->_mod->getErrLog()->logLine('wrong');
			$this->_fsave = false;
			return false;
		}
		return true;
	}
	
	public function delet()
	{
		$a = $this->_mod->getVal('a');
		if (!$a and $this->_mod->getId()and $this->_fdel) {
			$this->_mod->getErrLog()->logLine('Dwrong');
			$this->_fdel = false;
			return false;
		}
		return true;
	}
	public function afterSave()
	{
		if (!$this->_fsave) {
			$this->_mod->getErrLog()->logLine('Awrong');
		}		
		return $this->_fsave;		
	}
	public function afterDelet() 
	{
		if(!$this->_fdel) {
			$this->_mod->getErrLog()->logLine('DDwrong');
		}
		return $this->_fdel;
	}
}
class Model_Evp_Test extends PHPUnit_Framework_TestCase  
{
	protected static $db1;
	protected static $db2;

	protected $Student='testevalP';
	protected $db;

	
	public static function setUpBeforeClass()
	{	
	
		resetHandlers();
		$typ='dataBase';
		$name='test';	
		$Student='testevalP';
		
		self::$db1=getBaseHandler ($typ, $name);

		initStateHandler ($Student	,$typ, $name);
		$Student='testevalPF';	
		$typ='fileBase';
		
		self::$db2=getBaseHandler ($typ, $name);

		initStateHandler ($Student	,$typ, $name);
		
	}
	
	public function setTyp ($typ) 
	{
		if ($typ== 'SQL') {
			$this->db=self::$db1;
			$this->Student='testevalP';

			} 
		else {
			$this->db=self::$db2;
			$this->Student='testevalPF';

			}

	}
	
	public function Provider1() 
	{
		return [['SQL'],['FLE']];
	}	
	/**
     * @dataProvider Provider1
     */

	public function testNew($typ) 
	{
		$this->setTyp($typ);
		$db=$this->db;
		$db->beginTrans();
		
		$this->assertNotNull($x = new Model($this->Student));
		$res=$x->deleteMod();
		$this->assertTrue($res);
		
		$this->assertTrue($x->addAttr('a',M_INT));
		$this->assertTrue($x->addAttr('b',M_INT));
		$this->assertTrue($x->addAttr('aplusb',M_INT,M_P_EVALP));
		$this->assertFalse($x->isErr());

		$res = $x->saveMod();	
		$this->assertTrue($res);			
		$db->commit();

	}
	/**
     * @dataProvider Provider1
     *	
	/**
    * @depends testNew
    */
	public function testsave($typ) 
	{
		
		$this->setTyp($typ);
		$db=$this->db;
		$db->beginTrans();
		
		$this->assertNotNull($x = new Model($this->Student));
		$res= $x->setVal('a',1);
		$this->assertTrue($res);
		$this->assertTrue($x->setVal('b',1));
		
		$res=$x->save();
		$this->assertEquals(1,$res);
		
		$this->assertNotNull($x = new Model($this->Student));
		$res= $x->setVal('a',1);
		$this->assertTrue($res);
		$this->assertTrue($x->setVal('b',1));
		
		$res=$x->save();
		$this->assertEquals(2,$res);	
		
		$db->commit();
	}
	/**
     * @dataProvider Provider1
     *	
	/**
    * @depends  testsave
    */
	
	public function testget($typ) 
	{
		
		$this->setTyp($typ);
		$db=$this->db;
		$db->beginTrans();
		
		$this->assertNotNull($x = new Model($this->Student,1));
		$res= $x->getVal('aplusb');
		$this->assertEquals(2,$res);
		
		$res=$x->isOptl('aplusb');
		$this->assertFalse($res);

		$res=$x->isSelect('aplusb');
		$this->assertTrue($res);
		
		$db->commit();
	}
	
	/**
     * @dataProvider Provider1
     *	
	/**
    * @depends  testget
    */
	public function testerr($typ) 
	{
		
		$this->setTyp($typ);
		$db=$this->db;
		$db->beginTrans();
		
		$this->assertNotNull($x = new Model($this->Student,1));
		$res= $x->setVal('aplusb',1);
		$res= $x->setVal('a',1);
		$this->assertTrue($res);
		
		$this->assertEquals($x->getErrLine(),E_ERC042.':'.'aplusb');
		
		$res=$x->isSelect('xx');
		$this->assertFalse($res);		
		$this->assertEquals($x->getErrLine(),E_ERC002.':'.'xx');

		$res=$x->isModif('xx');
		$this->assertFalse($res);		
		$this->assertEquals($x->getErrLine(),E_ERC002.':'.'xx');		
		
		$res= $x->setVal('a',0);
		$this->assertTrue($res);
		
		$res = $x->save();
		$this->assertFalse($res);
		$this->assertEquals($x->getErrLine(),'wrong');

		$res = $x->save();
		$this->assertFalse($res);
		$this->assertEquals($x->getErrLine(),'Awrong');


		$this->assertNotNull($x = new Model($this->Student,2));

		$res= $x->setVal('a',0);
		$this->assertTrue($res);
		
		$res = $x->delet();
		$this->assertFalse($res);
		$this->assertEquals($x->getErrLine(),'Dwrong');

				
		$res = $x->delet();
		$this->assertFalse($res);
		$this->assertEquals($x->getErrLine(),'DDwrong');
		
		$db->commit();
	}
	
	/**
     * @dataProvider Provider1
     *	
	/**
    * @depends  testerr
    */
	public function testdel($typ) 
	{
		
		$this->setTyp($typ);
		$db=$this->db;
		$db->beginTrans();
		$this->assertNotNull($x = new Model($this->Student,1));

		$res= $x->setVal('a',1);
		$this->assertTrue($res);
		
		$res= $x->delAttr('aplusb');
		$this->assertTrue($res);
		
		$res= $x->delet();

		$this->assertTrue($res);	
		$db->commit();
	}	
}