<?php
	

require_once 'Request.php';
require_once 'CstMode.php';

class Request_Test extends PHPUnit_Framework_TestCase {

	public function testR()
	{
		$_SERVER['REQUEST_METHOD']='GET';
		$r=new Request();
		$this->assertnotNull($r);
		$this->assertEquals($r->getRootPath(),$r->getPath());
	}
	

    /**
     * @dataProvider Provider1
     */
 
	public function testRequest($a, $b, $c, $e1,$e2,$e3,$e4)
    {
		$_SERVER['PATH_INFO']=$a;
		$_SERVER['REQUEST_METHOD']=$b;
		if (is_null($c)) {		
			unset($_GET['Action']);
			unset($_POST['Action']);
		} else {
			$_GET['Action']=$c;
			$_POST['Action']=$c;
		}
		
		$r=new Request();
        $this->assertnotNull($r);
		$this->assertEquals($e1,$r->getAction());
		$this->assertEquals($e2,$r->isRootPath());
		$this->assertEquals($e3,$r->isClassPath());		
		$this->assertEquals($e4,$r->isObjPath());
		
	}
 
    public function Provider1() {
        return [
			['/', 	    'GET',null,		V_S_READ,true,false,false],
			['/X', 	    'GET',null,		V_S_SLCT,false,true,false],
			['/X/1', 	'GET',null,		V_S_READ,false,false,true],
            ['/X/1/X', 	'GET',null,		V_S_SLCT,false,true,false],
            ['/X/1/X/1','GET',null,		V_S_READ,false,false,true],
			
			['/', 	    'GET',V_S_UPDT,	V_S_UPDT,true,false,false],
			['/X', 	    'GET',V_S_CREA,	V_S_CREA,false,true,false],
			['/X/1', 	'GET',V_S_UPDT,	V_S_UPDT,false,false,true],
            ['/X/1/X', 	'GET',V_S_SLCT,	V_S_SLCT,false,true,false],
            ['/X/1/X/1','GET',V_S_DELT,	V_S_DELT,false,false,true],
			
			['/X', 	    'POST',null,	V_S_CREA,false,true,false],
			
			['/', 	    'POST',V_S_UPDT,V_S_UPDT,true,false,false],
			['/X', 	    'POST',V_S_CREA,V_S_CREA,false,true,false],
			['/X/1', 	'POST',V_S_UPDT,V_S_UPDT,false,false,true],
            ['/X/1/X', 	'POST',V_S_SLCT,V_S_SLCT,false,true,false],
            ['/X/1/X/1','POST',V_S_DELT,V_S_DELT,false,false,true],
	
 			];
    }

    /**
     * @dataProvider Provider2
     */
 	public function testGetAction($a, $b, $c, $d,$e1)
    {
		$_SERVER['PATH_INFO']=$a;
		$_SERVER['REQUEST_METHOD']=$b;
		if (is_null($c)) {		
			unset($_GET['Action']);
			unset($_POST['Action']);
		} else {
			$_GET['Action']=$c;
			$_POST['Action']=$c;
		}
		
		$r=new Request();
        $this->assertnotNull($r);
		if (!is_null($e1)) {
			if ($d == V_S_READ) {
				$e= $r->prfxPath($e1);
			} else {
				$e= $r->prfxPath($e1);
			}
		} else {
			$e=null;
		}
		$res = $r->getActionPath($d);
		if (! is_null($res)) {
			$res = $r->joinPathAction($res,$d);
		}
		$this->assertEquals($e,$res);
	}
	
	public function Provider2() {
        return [
			['/', 	    'GET',null,		V_S_READ,'/'],
			['/', 	    'GET',null,		V_S_UPDT,'/?Action='.V_S_UPDT],
			['/X', 	    'GET',null,		V_S_SLCT,'/X?Action='.V_S_SLCT],
			['/X', 	    'GET',null,		V_S_READ,'/'],			
			['/X/1', 	'GET',null,		V_S_READ,'/X/1'],
			['/X/1', 	'GET',null,		V_S_SLCT,'/X?Action='.V_S_SLCT],
            ['/X/1/X', 	'GET',null,		V_S_CREA,'/X/1/X?Action='.V_S_CREA],
	        ['/X/1/X', 	'GET',null,		V_S_READ,'/X/1'],
	        ['/X/1/X', 	'GET',null,		V_S_UPDT,null],
		    ['/', 		'GET',null,		V_S_DELT,null],		
			['/X/1', 	'GET',null,		'X',null],			
            ['/X/1/X/1','GET',null,		V_S_DELT,'/X/1/X/1?Action='.V_S_DELT],
 			];
    }


 
	public function testMethods() 
	{
			$r=new Request('/X/1/X',V_S_CREA);
			$this->assertnotNull($r);
			$this->assertEquals($r->prfxPath('/X/1/X'),$r->classPath());
			
			$this->assertEquals($r->objN(),2);
			$this->assertEquals(count($r->pathArr()),3);
			$res = $r->getClassPath('X',V_S_CREA);
			$this->assertEquals($res,$r->prfxPath('/X?Action='.V_S_CREA));

			
			$r=new Request('/X/1/X/1',V_S_READ);
			$this->assertnotNull($r);
			
			$res = $r->getCrefPath('X',V_S_CREA);
			$this->assertEquals($res,$r->prfxPath('/X/1/X/1/X?Action='.V_S_CREA));
			
			$p = $r->popObj();
			$this->assertEquals('/X/1',$p);
			
			$r=new Request('/X/1',V_S_READ);
			$this->assertnotNull($r);
			$p = $r->popObj();
			$this->assertEquals('/',$p);
			
			$this->assertEquals($r->prfxPath('/X/1'),$r->objPath());
			
			$_SERVER['PATH_INFO']='/X/1';
			$_SERVER['REQUEST_METHOD']='GET';
			$_SERVER['PHP_SELF']='/API.php';
			$_GET['Name']='X';
			
			$r=new Request();
			$this->assertnotNull($r);
			$this->assertEquals('/API.php',$r->getDocRoot());
			$this->assertEquals('/API.php/',$r->getRootPath());
			$this->assertEquals('X',$r->getPrm('Name'));
	}
	
	public function testPathErr() 
	{


		$_SERVER['PATH_INFO']='/X/1';
		$_SERVER['REQUEST_METHOD']='POST';
		$_SERVER['PHP_SELF']='/ABridge.php';
			
		try {$x=new Request();} catch (Exception $e) {$r= $e->getMessage();}
		$this->assertEquals($r, E_ERC048);
				
		$r=new Request('/',V_S_READ);
		$this->assertnotNull($r);

		try {$r->objPath();} catch (Exception $e) {$x= $e->getMessage();}
		$this->assertEquals($x, E_ERC038);

		try {$r->classPath();} catch (Exception $e) {$x= $e->getMessage();}
		$this->assertEquals($x, E_ERC038);
		
		$pathStrg=1;
		try {$x=new Request($pathStrg,V_S_READ);} catch (Exception $e) {$r= $e->getMessage();}
		$this->assertEquals($r, E_ERC036.':'.$pathStrg);
				
		$pathStrg='/$/1';
		try {$x=new Request($pathStrg,V_S_READ);} catch (Exception $e) {$r= $e->getMessage();}
		$this->assertEquals($r, E_ERC036.':'.$pathStrg.':0');

		$pathStrg='/a/$';
		try {$x=new Request($pathStrg,V_S_READ);} catch (Exception $e) {$r= $e->getMessage();}
		$this->assertEquals($r, E_ERC036.':'.$pathStrg.':1');

		$pathStrg='/a/1/$';
		try {$x=new Request($pathStrg,V_S_READ);} catch (Exception $e) {$r= $e->getMessage();}
		$this->assertEquals($r, E_ERC036.':'.$pathStrg.':2');
		
		$path='/X';
		
		try {$x=new Request($path,V_S_READ);} catch (Exception $e) {$r= $e->getMessage();}
		$this->assertEquals($r, E_ERC048.':'.V_S_READ.':/ABridge.php'.$path);
		
		
		$p1 = new Request($path,V_S_CREA);
		$this->assertNotNull($p1);
		
		try {$x=$p1->popObj();} catch (Exception $e) {$r= $e->getMessage();}
		$this->assertEquals($r, E_ERC035);

		$path='/X/1';
		try {$x=new Request($path,V_S_SLCT);} catch (Exception $e) {$r= $e->getMessage();}
		$this->assertEquals($r, E_ERC048.':'.V_S_SLCT.':/ABridge.php'.$path);	
		
		$p1 = new Request($path,V_S_READ);
		$this->assertNotNull($p1);	
		
		try {$x=$p1->pushId(1);} catch (Exception $e) {$r= $e->getMessage();}
		$this->assertEquals($r, E_ERC037);
		
		$path='/';
		try {$x=new Request($path,V_S_SLCT);} catch (Exception $e) {$r= $e->getMessage();}
		$this->assertEquals($r, E_ERC048.':'.V_S_SLCT.':/ABridge.php'.$path);	
	
	
	}
	
	
}

?>	