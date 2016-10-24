<?php

/* Format date to be checked */

define ('TSTP_F', "d-m-Y H:i:s");
define ('DATE_F', "d-m-Y");

require_once("Logger.php");
require_once("TypeConstant.php");
require_once("ErrorConstant.php");
require_once("Type.php");
require_once("Handler.php");

class Model {
	// property
 
	public $id;
	public $name ;
	public $attrPredefList = array('id','vnum','ctstp','utstp');
	public $attr_lst = array('id','vnum','ctstp','utstp');
	public $attr_typ = array("id"=>M_ID,"vnum"=>M_INT,"ctstp"=>M_TMSTP,"utstp"=>M_TMSTP);
	public $attr_val = array('vnum'=>0);
	public $attr_path = [];
	public $errLog;
	public $stateHdlr=0;

	
	// constructors
	function __construct()
    {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    } 
	
	function __construct1($name) {
		$this->init($name,0);
		$this->setValNoCheck ("ctstp",date(TSTP_F));
		$this->setValNoCheck ("vnum",1);
		$x = $this->stateHdlr;
		if ($x) {
			$x->restoreMod($this);
		}
	}

	function __construct2($name,$id) {
		$this->init($name,$id);
		if ( ! $id) {throw new Exception(E_ERC012.':'.$id);}
		$idr=0;
		$x = $this->stateHdlr;
		if ($x) {
			$x->restoreMod($this);
			$idr =$x->restoreObj($this);
			if ($id !== $idr){throw new exception(E_ERC007.':'.$id);};
		}
	}

	public function init($name,$id) {
		if (! checkType($name,M_ALPHA)) {throw new Exception(E_ERC010.':'.$name.':'.M_ALPHA);}
		if (! checkType($id,M_INTP))    {throw new Exception(E_ERC011.':'.$id.':'.M_INTP);}
		$this->id=$id; 
		$this->name=$name;
		$this->setValNoCheck ('utstp',date(TSTP_F));
		$logname = $name.'_ErrLog';
		$this->errLog= new Logger($logname);
		$this->stateHdlr=getStateHandler ($name);
	}
	
	public function getErrLog () {
		return $this->errLog;
	}
	
	public function getModName () {
		return $this->name;
	}
	
	public function getId () {
		return $this->id;
	}
	
	public function getAllAttr () {
        return $this->attr_lst;}

	public function getAllAttrTyp () {
		return $this->attr_typ;        	
   	}

	public function getAllVal () {
		return $this->attr_val;        	
   	}
	
	public function getAllPath () { // no id value 
		return $this->attr_path;        	
   	}
	
	public function getPreDefAttr () {
		return $this->attrPredefList;
	}

	public function addAttr ($attr,$typ=M_STRING,$path=0) {
		$x= $this->existsAttr ($attr);
		if ($x) 								{$this->errLog->logLine(E_ERC003.':'.$attr);return 0;}
		if ($typ == M_REF){ 
			if (!$path) 						{$this->errLog->logLine(E_ERC008.':'.$attr);return 0;}
		}
		$this->attr_lst[]=$attr;
		$r=$this->setTyp ($attr,$typ);
		$r2=true;
		if ($typ == M_REF) {
			$r2=$this->setPath($attr,$path); // should check path ?
		}
		if ($r and $r2){return true;}
		return 0;
	}

	public function delAttr ($attr) {
		$x= $this->existsAttr ($attr);
		if (!$x) 								{$this->errLog->logLine( E_ERC002.':'.$attr);return 0;}
		if (in_array($attr,$this->attrPredefList)) 
												{$this->errLog->logLine(E_ERC001.':'.$attr);return 0;}
		unset($this->attr_lst[$attr]);
		unset($this->attr_typ[$attr]);
		return true;
	}

	public function getVal ($attr) {
		if ($attr == 'id') {return $this->getId();}
		$x= $this->existsAttr ($attr);
		if (!$x) 								{$this->errLog->logLine( E_ERC002.':'.$attr);return 0;}
		foreach($this->attr_val as $x => $val) {
			if ($x==$attr) {return $val;}
		}    	
		return NULL;        	
   	}

	public function getTyp ($attr) {
		if (! $this->existsAttr ($attr)) 		{$this->errLog->logLine(E_ERC002.':'.$attr);return 0;}
		foreach($this->attr_typ as $x => $typ) {
			if ($x==$attr) {return $typ;}
		}           			
		return NULL;
    }
	
	public function getPath ($attr) {
		if (! $this->existsAttr ($attr)) 		{$this->errLog->logLine(E_ERC002.':'.$attr);return 0;}
		foreach($this->attr_path as $x => $path) {
			if ($x==$attr) {return $path;}
		}           			
		return NULL;
    }
	
	public function existsAttr ($attr) {
		if (in_array($attr, $this->attr_lst)) {return true;} ;
        return 0;
    }

	private function setValNoCheck ($attr,$val) {
		if (! $this->existsAttr ($attr)) 		{$this->errLog->logLine(E_ERC002.':'.$attr);return 0;}
		$this->attr_val[$attr]=$val;
		return true;
	}
	
	public function setTyp ($attr,$typ) {
		if (! $x= $this->existsAttr ($attr)) 	{$this->errLog->logLine(E_ERC002.':'.$attr);return 0;};
		if (!isMtype($typ)) 					{$this->errLog->logLine(E_ERC004.':'.$typ) ;return 0;}
		$this->attr_typ[$attr]=$typ;
	    return true;
	}
	
	public function setPath ($attr,$path) {
		if (! $x= $this->existsAttr ($attr)) 	{$this->errLog->logLine(E_ERC002.':'.$attr);return 0;};
		$this->attr_path[$attr]=$path;
	    return true;
	}
	
	public function setVal ($Attr,$Val,$check=true) {
		if (in_array($Attr,$this->attrPredefList) 
			and $check){						$this->errLog->logLine(E_ERC001.':'.$Attr);return 0;};
		// type checking
		$type=$this->getTyp($Attr);
		$btype=$type;
		if ($type==M_ID or $type == M_REF or $type == M_CREF) {$btype = M_INTP;}
		$res =checkType($Val,$btype);
		if (! $res)								{$this->errLog->logLine(E_ERC005.':'.$Val.':'.$btype) ;return 0;}
		// ref checking
		if ($type == M_REF) {
			$res = $this-> checkRef($Attr,$Val);
			if (! $res) {return 0;}
		}
		return ($this->setValNoCheck ($Attr,$Val));
    }

	public function checkRef($Attr,$Val) {
		$mod = $this->getPath($Attr) ;
		if (!$mod)								 {$this->errLog->logLine(E_ERC008.':'.$Attr);return 0;}
		try {$res = new Model($mod,$Val);} catch (Exception $e) {$this->errLog->logLine($e->getMessage());return 0;}
		return true;
	}
	
	public function save (){
		if (! $this->stateHdlr) 				{$this->errLog->logLine(E_ERC006);return 0;}
		$res=$this->stateHdlr->saveObj($this);
		$this->id=$res;
		return $res;
	}

	public function saveMod (){
		if (! $this->stateHdlr) 				{$this->errLog->logLine(E_ERC006);return 0;}
		$res=$this->stateHdlr->saveMod($this);
		return $res;
	}
}

?>