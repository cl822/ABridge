<?php
/**
 * Model class file
 *
 * PHP Version 5.6
 *
 * @category PHP
 * @package  ABridge
 * @author   Pierre Lavency <pierrelavency@hotmail.com>
 * @link     No
 */
 
require_once "Logger.php";
require_once "TypeConstant.php";
require_once "ErrorConstant.php";
require_once "Type.php";
require_once "Handler.php";


define('M_P_EVAL', "M_P_EVAL");
define('M_P_EVALP', "M_P_EVALP");


/**
 * Model class
 *
 * Models are the business entities of the applications
 * 
 * @category PHP
 * @package  ABridge
 * @author   Pierre Lavency <pierrelavency@hotmail.com>
 * @link     No
 */
class Model
{
    /** 
     * @var integer The object's id. 
     */
    protected $_id;
    /**
     * @var string  The Model name. 
     */
    protected $_name ;
    /**
     * @var indicates if model has changed or not. 
     */
    protected $_modChgd;
    /**
    /**
     * @var array The list of predefined attributes. 
     */
    protected $_attrPredef;
    /**
     * @var array The list of attributes. 
     */
    protected $_attrLst;
    /**
     * @var array The list of types associated to attributes. 
     */   
    protected $_attrTyp;
    /**
     * @var array The list of values associated to attributes. 
     */ 
    protected $_attrVal;
    /**
     * @var array The list of path associated to reference attributes.  
     */
    protected $_refParm ;
    /**
     * @var array The list of types business key attributes. 
     */
    protected $_attrBkey;
    /**
     * @var array The list of mandatory attributes. 
     */
    protected $_attrMdtr;
    /**
     * @var array The list of attribute default values. 
     */
    protected $_attrDflt;
    /**
     * @var array The error logger. 
     */
    protected $_errLog;
    /**
     * @var array The state handler.
     */
    protected $_stateHdlr=0;
    protected $_trusted=false;
    protected $_checkTrusted=false; // could be usefull !!
    protected $_custom=false;
    protected $_attrCkey;
    protected $_attrProtected;
    protected $_asCriteria;
    protected $_attrEval;
    protected $_attrEvalP;  
    protected $_obj; // custom class object
    protected $_abstract;
    protected $_inhObj;
    protected $_inhNme; 
    
    /**
    * Constructor
    */
    function __construct() 
    {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this, $f = 'construct'.$i)) {
            call_user_func_array(array($this, $f), $a);
        }
    } 
    /**
     * Constructor of a new object that does not exists (id is equal to 0).
     *
     * @param string $name The model name. 
     * 
     * @return void
     */       
    function construct1($name) 
    {
        $this->init($name, 0);
        $this->setValNoCheck("ctstp", date(M_FORMAT_T));
        $this->setValNoCheck("vnum", 1);
        $x = $this->_stateHdlr;
        if ($x) {
            $x->restoreMod($this);
        }
        $this->_modChgd=false;
        $this->initObj($name);
    }  
    /**
     * Constructor of an existing object (id must be different from 0).
     *
     * @param string $name The model name. 
     * @param int    $id   The object id. 
     *
     * @return void
     */   
    function construct2($name,$id) 
    {
        if (! checkType($id, M_INTP)) {
            throw new Exception(E_ERC011.':'.$id.':'.M_INTP);
        }
        $this->init($name, $id);
        $idr=0;
        $x = $this->_stateHdlr;
        if ($x) {
            $x->restoreMod($this);
            $this->_trusted = true;
            $idr =$x->restoreObj($this);
            $this->_trusted = false;
            if ($id != $idr) {
                throw new exception(E_ERC007.':'.$name.':'.$id);
            };
        }
        $this->_modChgd=false;
        $this->initObj($name);
    }
    
    protected function initObj($name) 
    {
        if (! class_exists($name)) {
            $this->_obj= null;
            return null;
        }
        $this->_custom=true;
        $this->_obj = new $name($this);
        $this->_custom=false;
        return $this->_obj;
    }
     /**
     * Initialise the attributes and the errologger
     *
     * @param string $name The model name. 
     * @param int    $id   The object id. 
     *
     * @return void
     */     
    protected function init($name,$id) 
    {
        if (! checkType($name, M_ALPHA)) {
            throw new Exception(E_ERC010.':'.$name.':'.M_ALPHA);
        }
        $this->initattr();
        $this->_id=$id; 
        $this->_name=$name;
        $logname = $name.'_ErrLog';
        $this->_errLog= new Logger($logname);
        $this->_obj=null;
        $this->_stateHdlr=getStateHandler($name);
    }
    /**
     * Re/Initialise the attributes and their properties
     *
     * @return void
     */ 
    protected function initattr() 
    {
        $this->_modChgd=false;
        $this->_attrPredef = array('id','vnum','ctstp','utstp');
        $this->_attrLst = array('id','vnum','ctstp','utstp');
        $this->_attrTyp = array(
            "id"=>M_ID,
            "vnum"=>M_INT,
            "ctstp"=>M_TMSTP,
            "utstp"=>M_TMSTP,
        );
        $this->_attrVal = array('vnum' => 0);
        $this->_attrDflt = [];
        $this->_refParm = [];
        $this->_attrBkey = [];
        $this->_attrMdtr = [];
        $this->_attrCkey = [];
        $this->_attrProtected = [];
        $this->_asCriteria = [];
        $this->_attrEval=[];
        $this->_attrEvalP=[];
        $this->_abstract= false;
        $this->_inhNme=false;
    }
    /**
     * Returns the errorlogger.
     *
     * @return Logger
     */ 
    public function getErrLog() 
    {
        return $this->_errLog;
    }    
    
    public function getErrLine() 
    {
        $c = $this->_errLog->logSize();
        if ($c) {
            $l = $this->_errLog->getLine($c-1);
            return $l;
        }
        return false;
    }
    /**
     * Returns the Model Name.
     *
     * @return string
     */
    public function getModName() 
    {
        return $this->_name;
    }

    public function getAbstrNme()
    {
        $abstr = $this->getInhObj();
        if (!is_null($abstr)) {
            return $abstr->getModName();
        }
        return null;
    }
    
    
    /**
     * Returns the Object Id.
     *
     * @return integer
     */   
    public function getId()
    {
        return $this->_id;
    }

    public function isAbstr()
    {
        return $this->_abstract;
    }
    
    public function setAbstr()
    {
        $this->_abstract=true;
        return true;
    }

    public function getInhNme()
    {
        return $this->_inhNme;
    }

    public function getInhObj()
    {       
        return $this->_inhObj;
    }
    
    public function setInhNme($name)
    {
        $this->_inhNme=$name;
        $obj = new Model($name);
        if ($obj->isAbstr()) {
            $this->_inhObj=$obj;
            return true;
        }
        return false;
    }
    /**
     * Returns the list of attributes of a Model.
     *
     * @return array
     */
    public function getAllAttr() 
    {
        return $this->_attrLst;
    }

    public function getAttrList()
    {
        $list = $this->getAllAttr();
        $abstr = $this->getInhObj();
        if (is_null($abstr)) {
            return $list;
        }   
        $ilist = $abstr->getAllAttr();
        $ilist = array_diff($ilist, $this->getAllPredef());
        $res = ['id'];
        $res = array_merge($res, $ilist);
        $key = array_search('id', $list);
        if ($key!==false) {
            unset($list[$key]);
        } 
        $res = array_merge($res, $list);
        return $res ;
    }
    
    /**
     * Returns the list of attribute types of a Model. 
     *
     * @return array
     */    
    public function getAllTyp()
    {
        return $this->_attrTyp;         
    }
   /**
     * Returns the list of default values of a Model.
     *
     * @return array
     */
    public function getAllDflt() 
    {
        return $this->_attrDflt;         
    }
    /**
     * Returns the list of values BUT the id value of a Model.
     *
     * @return array
     */
    public function getAllVal() 
    {
        return $this->_attrVal;         
    }
    /**
     * Returns the list of all path of a Model.
     *
     * @return array
     */ 
    public function getAllRefParm() 
    { 
        return $this->_refParm;            
    }   
    /**
     * Returns the list of Business Key attributes of a Model.
     *
     * @return array
     */  
    public function getAllCkey() 
    { 
        return $this->_attrCkey;            
    } 
    public function getAllBkey() 
    { 
        return $this->_attrBkey;            
    } 
    /**
     * Returns the list of mandatory attributes of a Model.
     *
     * @return array
     */ 
    public function getAllMdtr()
    { 
        return $this->_attrMdtr;            
    }  
    /**
     * Returns the list of Predefined attributes of a Model.
     *
     * @return array
     */
    public function getAllPredef() 
    {
        return $this->_attrPredef;
    }

    /**
     * Returns the type of an attribute.
     *
     * @param string $attr the attribute. 
     *
     * @return string its type.
     */
    public function getTyp($attr) 
    {
        if (! $this->existsAttr($attr)) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);
            return false;
        }
        if (isset($this->_attrTyp[$attr])) {
            return $this->_attrTyp[$attr];
        }
        $abstr = $this->getInhObj();
        return $abstr->getTyp($attr);           
    }


    /**
     * Returns the 'path' of an attribute.
     *
     * @param string $attr the attribute. 
     *
     * @return string its 'path'.
     */ 

    protected function getParm($attr)
    {
        if (isset($this->_refParm[$attr])) {
            return $this->_refParm[$attr] ;
        }
        $abstr = $this->getInhObj();
        return $abstr->getParm($attr);
    }
    
    public function getRef($attr) 
    {     
        $mod = $this->getRefMod($attr);
        if (! $mod) {
            throw new Exception($this->getErrLine());
        }
        $id = $this->getVal($attr);
        if (is_null($id)) {
            return null;
        }
        $res=new Model($mod, $id);
        return ($res);
    }
   
    protected function setRef($attr,$mod) 
    {
        $modA = $this->getRefMod($attr);
        $modN = $mod->getModName();
        if ($modA != $modN) {
            $abstr = $mod->getInhObj();
            if (is_null($abstr) or $modA != $abstr->getModName()) {
                throw new Exception(E_ERC033.':'.$attr.':'.$modA.':'.$modN);
            }
        }
        $id = $mod->getId();
        if ($id) {
            $this->setValNoCheck($attr, $id);
        }
    }

    public function getModCref($attr) 
    {
        $res = $this->getCrefMod($attr);
        if (!$res) {
            return false;
        }
        return $res[1];
    }
    
    private function getCrefMod($attr) 
    {
        if (! $this->existsAttr($attr)) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);
            return false;
        }
        if ($this->getTyp($attr)!= M_CREF) {
            $this->_errLog->logLine(E_ERC027.':'.$attr);
            return false;
        }
        $path=$this->getParm($attr);
        $patha=explode('/', $path);
        return ($patha);
    }
    
    public function newCref($attr) 
    {
        $patha=$this->getCrefMod($attr);
        if (!$patha) {
            throw new Exception($this->getErrLine());
        }
        $m=new Model($patha[1]);
        $m->setRef($patha[2], $this);
        $m->protect($patha[2]);
        return $m;
    }
   
    public function getCref($attr,$id) 
    {
        $patha=$this->getCrefMod($attr);
        if (!$patha) {
            throw new Exception($this->getErrLine()); 
        }
        $res=new Model($patha[1], $id);
        $rid=$res->getVal($patha[2]);
        if ($rid != $this->getId()) {
            throw new Exception(E_ERC032.':'.$attr.':'.$id);
        }
        $res->protect($patha[2]);
        return ($res);
    }
    
    public function getCode($attr,$id) 
    {
        if (! $this->existsAttr($attr)) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);
            return false;
        }
        $typ = $this->getTyp($attr);
        if ($typ != M_CODE and $typ != M_REF) {
            $this->_errLog->logLine(E_ERC028.':'.$attr);
            return false;
        }
        $path=$this->getParm($attr);
        $patha=explode('/', $path);
        if ($typ == M_CODE) {       
            $m = new Model($patha[1], (int) $patha[2]);
            $res=$m->getCref($patha[3], $id);
            return ($res);
        }
        return new Model($this->getRefMod($attr), $id);
    }

    /**
     * Returns the Logger.
     *
     * @return Logger
     */    
    public function isErr()
    {
        $c=$this->_errLog->logSize();
        if ($c) {
            return true;
        }
        return false;
    }
    /**
     * Returns true if the attribute is Protected.
     *
     * @param string $attr the attribute. 
     *
     * @return boolean
     */        
    public function isProtected($attr) 
    {
        if (! $this->existsAttr($attr)) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);return false;
        }
        $res = in_array($attr, $this->_attrProtected);      
        return ($res);
    }    
    /**
     * Returns true if the attribute is a Business key.
     *
     * @param string $attr the attribute. 
     *
     * @return boolean
     */        
    public function isBkey($attr) 
    {
        if (! $this->existsAttr($attr)) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);return false;
        }
        $res = in_array($attr, $this->_attrBkey);
        if ($res) {
            return $res;
        }
        $abstr = $this->getInhObj();
        if (!is_null($abstr)) {
            return $abstr->isBkey($attr);
        }       
        return ($res);
    }
    /**
     * Returns true if the attribute is a Mandatory.
     *
     * @param string $attr the attribute. 
     *
     * @return boolean
     */         
    public function isMdtr($attr) 
    {
        if (! $this->existsAttr($attr)) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);
            return false;
        }
        $res =in_array($attr, $this->_attrMdtr);
        if ($res) {
            return $res;
        }
        $abstr = $this->getInhObj();
        if (!is_null($abstr)) {
            return $abstr->isMdtr($attr);
        }       
        return ($res);
    }   
    /**
     * Returns true if the attribute is a Pedefined attribute.
     *
     * @param string $attr the attribute. 
     *
     * @return boolean
     */         
    public function isPredef($attr) 
    {
        if (! $this->existsAttr($attr)) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);
            return false;
        }
        return (in_array($attr, $this->_attrPredef));
    } 
    /**
     * Returns true if the attribute is an optional attribute.
     *
     * @param string $attr the attribute. 
     *
     * @return boolean
     */   
    public function isOptl($attr) 
    {
        if (! $this->existsAttr($attr)) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);
            return false;
        }
        if ($this->isPredef($attr) ) {
            return false;
        }
        if ($this->isEval($attr) ) {
            return false;
        }
        if ($this->isEvalP($attr) ) {
            return false;
        }
        if ($this->isMdtr($attr) ) {
            return false;
        }
        $typ = $this->getTyp($attr);
        if ($typ == M_CREF) {
            return false;
        }
        return true;
    }
    
    public function isModif($attr)
    {
        if (! $this->existsAttr($attr)) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);
            return false;
        }
        $res = false;
        if ( $this->isMdtr($attr) or $this->isOptl($attr) ) {
            $res= true;
        }
        if ($this->isProtected($attr)) {
            $res = false;
        }
        return $res;
    }
    
    public function isSelect($attr)
    {
        if (! $this->existsAttr($attr)) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);
            return false;
        }
        if ($attr == 'id') {
            return true;
        }
        if ($this->isEvalP($attr)) {
            return true;
        }
        return $this->isModif($attr);
    }
    
    
    public function isEvalP($attr)
    {
        $res = in_array($attr, $this->_attrEvalP);
        if ($res) {
            return $res;
        }
        $abstr = $this->getInhObj();
        if (!is_null($abstr)) {
            return $abstr->isEvalP($attr);
        }       
        return ($res);
    }
    
    public function isEval($attr)
    {
        $res=in_array($attr, $this->_attrEval);
        if ($res) {
            return $res;
        }
        $abstr = $this->getInhObj();
        if (!is_null($abstr)) {
            return $abstr->isEval($attr);
        }
        return ($res);
    }
    
    /**
     * Returns true if the attribute exists.
     *
     * @param string $attr the attribute. 
     *
     * @return boolean
     */   
    public function existsAttr($attr)
    {
        if (in_array($attr, $this->_attrLst)) {
            return true;
        } ;
        $abstr = $this->getInhObj();
        if (!is_null($abstr)) {
            return $abstr->existsAttr($attr);
        }
        return false;
    }
    
    protected function existsAttrLocal($attr) 
    {
        if (in_array($attr, $this->_attrLst)) {
            return true;
        } ;
        return false;
    }

 
    /**
     * Set the default value of an attribute.
     *
     * @param string $attr the attribute. 
     * @param string $val  the default value.
     *
     * @return boolean
     */      
    public function setDflt($attr,$val) 
    {
        if (! $this->existsAttr($attr)) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);
            return false;
        };
        $this->_attrDflt[$attr]=$val;
        $this->_modChgd=true;
        return true;
    }
    /**
     * Set an attribute as protected.
     *
     * @param string $attr the attribute. 
     *
     * @return boolean
     */
    public function protect($attr)
    {
        if (! $x= $this->existsAttr($attr)) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);
            return false;
        };
        if (!in_array($attr, $this->_attrProtected)) {
            $this->_attrProtected[]=$attr;
        }      
        return true;
    }
    /**
     * Set an attribute value of a business key .
     *
     * @param string $attr the attribute. 
     * @param string $val  the value.
     *
     * @return boolean
     */     
    public function setBkey($attr,$val) 
    {
        if (! $x= $this->existsAttr($attr)) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);
            return false;
        };
        if (!$this->_stateHdlr) {
            $this->_errLog->logLine(E_ERC017.':'.$attr);
            return false;
        }     
        if ($val) {
            if (!in_array($attr, $this->_attrBkey)) {
                $this->_attrBkey[]=$attr;
            }      
        }
        if (! $val) {
            $key = array_search($attr, $this->_attrBkey);
            if ($key!==false) {
                unset($this->_attrBkey[$key]);
            }             
        }
        $this->_modChgd=true;
        return true;
    }
    public function setCkey($attrLst,$val) 
    {
        if (! is_array($attrLst)) {
            $this->_errLog->logLine(E_ERC029);
            return false;
        }
        foreach ($attrLst as $attr) {
            if (! $x= $this->existsAttr($attr)) {
                $this->_errLog->logLine(E_ERC002.':'.$attr);
                return false;
            };
        }
        if (!$this->_stateHdlr) {
            $this->_errLog->logLine(E_ERC017);
            return false;
        }     
        if ($val) {
            if (!in_array($attrLst, $this->_attrCkey)) {
                $this->_attrCkey[]=$attrLst;
            }      
        }
        if (! $val) {
            $key = array_search($attrLst, $this->_attrCkey);
            if ($key!==false) {
                unset($this->_attrCkey[$key]);
            }             
        }
        $this->_modChgd=true;
        return true;
    }
    /**
     * Set an attribute value of a mandatory attribute .
     *
     * @param string $attr the attribute. 
     * @param string $val  the value.
     *
     * @return boolean
     */         
    public function setMdtr($attr,$val) 
    {
        if (! $x= $this->existsAttr($attr)) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);
            return false;
        };
        if ($val) {
            if (!in_array($attr, $this->_attrMdtr)) {
                $this->_attrMdtr[]=$attr;
            }      
        }
        if (! $val) { 
            $key = array_search($attr, $this->_attrMdtr);
            if ($key!==false) {
                unset($this->_attrMdtr[$key]);
            }            
        }
        $this->_modChgd=true;
        return true;
    }
    
    /**
     * Set the critera to select objects .
     *
     * @param string $attr attribute list. 
     * @param string $val  value lits .
     *
     * @return boolean
     */         
    public function setCriteria($attrL,$opL,$valL) 
    {
        foreach ($attrL as $attr) {
            if (! $x= $this->existsAttr($attr)) {
                $this->_errLog->logLine(E_ERC002.':'.$attr);
                return false;
            };
        }
        $this->_asCriteria=[$attrL,$opL,$valL];
        return true;
    }
    /**
     * Add an attribute.
     *
     * @param string $attr the attribute. 
     * @param string $typ  the type.
     * @param string $path the path.
     *
     * @return boolean
     */      
    public function addAttr($attr,$typ,$path=0) 
    {
        $x= $this->existsAttr($attr);
        if ($x) {
            $this->_errLog->logLine(E_ERC003.':'.$attr);
            return false;
        }
        if ($typ == M_REF or $typ == M_CREF or $typ == M_CODE) { 
            if (!$path) {
                $this->_errLog->logLine(E_ERC008.':'.$attr.':'.$typ);
                return false;
            }
            if (!$this->_stateHdlr) {
                $this->_errLog->logLine(E_ERC014.':'.$attr.':'.$typ);
                return false;
            }
        }
        if (!isMtype($typ)) {
            $this->_errLog->logLine(E_ERC004.':'.$typ);
            return false;
        }
        if (! $this->checkParm($attr, $typ, $path)) {
            return false;
        }
        if ($typ == M_REF or $typ == M_CREF or $typ == M_CODE) {
            $this->_refParm[$attr]=$path;
        }
        if ($path === M_P_EVAL) {
            $this->_refParm[$attr]=$path;
            $this->_attrEval[]=$attr;
        }
        if ($path === M_P_EVALP) {
            $this->_refParm[$attr]=$path;
            $this->_attrEvalP[]=$attr;
        }
        $this->_attrLst[]=$attr;
        $this->_attrTyp[$attr]=$typ;

        $this->_modChgd=true;
        return true;
    }
    /**
     * Delete an attribute.
     *
     * @param string $attr the attribute. 
     *
     * @return boolean
     */        
    public function delAttr($attr)  
    {
        $x= $this->existsAttrLocal($attr);
        if (!$x) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);
            return false;
        }
        
        if (in_array($attr, $this->_attrPredef)) {
            $this->_errLog->logLine(E_ERC001.':'.$attr);
            return false;
        }
        foreach ($this->_attrCkey as $attrLst) {
            if (in_array($attr, $attrLst)) {
                $this->_errLog->logLine(E_ERC030.':'.$attr);
                return false;
            }
        }
        $key = array_search($attr, $this->_attrLst);
        if ($key!==false) {
            unset($this->_attrLst[$key]);
        }                             
        if (isset($this->_attrTyp[$attr])) {
            unset($this->_attrTyp[$attr]);
        }
        if (isset($this->_refParm[$attr])) {
            unset($this->_refParm[$attr]);
        }
        if (isset($this->_attrDflt[$attr])) {
            unset($this->_attrDflt[$attr]);
        }
        $key = array_search($attr, $this->_attrBkey);
        if ($key!==false) {
            unset($this->_attrBkey[$key]);
        }    
        $key = array_search($attr, $this->_attrMdtr);
        if ($key!==false) {
            unset($this->_attrMdtr[$key]);
        } 
        $key = array_search($attr, $this->_attrProtected);
        if ($key!==false) {
            unset($this->_attrProtected[$key]);
        }
        $key = array_search($attr, $this->_attrEval);
        if ($key!==false) {
            unset($this->_attrEval[$key]);
        }
        $key = array_search($attr, $this->_attrEvalP);
        if ($key!==false) {
            unset($this->_attrEvalP[$key]);
        }           
        $this->_modChgd=true;        
        return true;
    }
     /**
     * Get the default value of an attribute.
     *
     * @param string $attr the attribute. 
     *
     * @return string  the value
     */      
    public function getDflt($attr) 
    {
        $x= $this->existsAttr($attr);
        if (!$x) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);
            return false;
        }
        foreach ($this->_attrDflt as $x => $val) {
            if ($x==$attr) {
                return $val;
            }
        }
        $abstr = $this->getInhObj();
        if (!is_null($abstr)) {
            return $abstr->getDflt($attr);
        }
        return null;            
    }
    
    public function select() 
    {
        $result = [];
        $res = $this->_asCriteria;
        if ($res == []) {
            return $result;
        }
        
        foreach ($this->getAllAttr() as $attr) {
            if ($this->isProtected($attr)) {
                $val = $this->getVal($attr);                
                $key = array_search($attr, $res[0]);
                if ($key!==false) {
                    $res[2][$key]=$val;
                } else {
                    $res[2][]=$val;
                    $res[0][]=$attr;
                }
                $res[1][$attr]='=';
            }
        }   
        
        $mod=$this->getModName();
        $result=$this->_stateHdlr->findObjWheOp(
            $mod, 
            $res[0], 
            $res[1], 
            $res[2]
        );
        return $result;
    }
    
    /**
     * Get the value of an attribute.
     *
     * @param string $attr the attribute. 
     *
     * @return void  the value
     */      
    public function getVal($attr) 
    {
        if ($attr == 'id') {
            return $this->getId();
        }
        $x= $this->existsAttr($attr);
        if (!$x) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);
            return false;
        }
        if ($this->isEval($attr)) {
            return $this->_obj->getVal($attr);
        }
        $type=$this->getTyp($attr);
        if ($type == M_CREF) { //will not work if on different Base !!
            $path = $this->getParm($attr);
            $patha=explode('/', $path);
            $hdlr=$this->_stateHdlr;
            $res=$hdlr->findObj($patha[1], $patha[2], $this->getId());
            return $res;
        }
        foreach ($this->_attrVal as $x => $val) {
            if ($x==$attr) {
                return $val;
            }
        }       
        return null;            
    }
    /**
     * Set the value of an attribute without any check.
     *
     * @param string $attr the attribute. 
     * @param void   $val  the value.
     *
     * @return boolean
     */      
    protected function setValNoCheck($attr,$val)
    {
        $this->_attrVal[$attr]=$val;
        return true;
    }  
    /**
     * Set the value of an attribute.
     *
     * @param string  $Attr  the attribute. 
     * @param void    $Val   the value.
     * @param boolean $check check if predef or not.
     *
     * @return boolean
     */    
    public function setVal($attr,$val)
    {
        if (! $this->existsAttr($attr)) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);
            return false;
        }
        if ($this->isAbstr()) {
            $this->_errLog->logLine(E_ERC045);
            return false;
        }
        $check=!($this->_trusted);
        if (in_array($attr, $this->_attrPredef) and $check) {
            $this->_errLog->logLine(E_ERC001.':'.$attr);
            return false;
        };
        $check= ($check or $this->_checkTrusted);
        // Eval
        if ($this->isEval($attr)) {
            $this->_errLog->logLine(E_ERC039.':'.$attr);
            return false;
        }
        // EvalP
        if ($this->isEvalP($attr) 
            and (! $this->_custom) 
            and !($this->_trusted)) {
            $this->_errLog->logLine(E_ERC042.':'.$attr);
            return false;
        }   
        // protected
        if ($this->isProtected($attr)) {
            $this->_errLog->logLine(E_ERC034.':'.$attr);
            return false;
        }
        // type checking
        $type=$this->getTyp($attr);
        if ($type ==M_CREF ) {
            $this->_errLog->logLine(E_ERC013.':'.$attr);
            return false;
        }
        $btype=baseType($type);
        $res =checkType($val, $btype);
        if (! $res) {
            $this->_errLog->logLine(E_ERC005.':'.$attr.':'.$val.':'.$btype);
            return false;
        }
        // ref checking
        if ($type == M_REF and $check) {
            $res = $this-> checkRef($attr, $val);
            if (! $res) {
                return false;
            }
        }
        // code values
        if ($type == M_CODE and $check) {
            $res = $this-> checkCode($attr, $val);
            if (! $res) {
                $this->_errLog->logLine(E_ERC016.':'.$attr.':'.$val);
                return false;
            }
        }
        // BKey
        if ($this->isBkey($attr) and $check) {
            $res = $this->checkBkey($attr, $val);
            if (! $res) {
                $this->_errLog->logLine(E_ERC018.':'.$attr.':'.$val);
                return false;
            } 
        }
        return ($this->setValNoCheck($attr, $val));
    }
    /**
     * Check the value of an business key attribute.
     *
     * @param string $Attr the attribute. 
     * @param void   $Val  the value.
     *
     * @return boolean
     */    
    protected function checkBkey($attr,$val)
    {       
        if (is_null($val)) {
            return true;
        }
        $res=$this->_stateHdlr->findObj($this->getModName(), $attr, $val);
        if ($res == []) {
            return true;
        }
        $id=array_pop($res);
        if ($id == $this->getId()) {
            return true;
        }
        return false;       
    }
    
    protected function checkCkey($attrLst)
    {   
        $valLst=[];
        $res=[];
        foreach ($attrLst as $attr) {
            $val = $this->getVal($attr);
            if (is_null($val)) {
                return true;
            }
            $valLst[]=$val;
        }       
        $res=$this->_stateHdlr->findObjWheOp(
            $this->getModName(), 
            $attrLst,
            [],         
            $valLst
        );
        if ($res == []) {
            return true;
        }
        $id=array_pop($res);
        if ($id == $this->getId()) {
            return true;
        }
        return false;       
    }
    
    /**
     * Check the value of a code attribute.
     *
     * @param string $Attr the attribute. 
     * @param void   $Val  the value.
     *
     * @return boolean
     */
    protected function checkCode($attr,$val)
    {
        if (is_null($val)) {
            return true;
        }
        $vals = $this->getValues($attr);
        if (!$vals) {
            return false;
        }
        $res = in_array($val, $vals);
        return $res;        
    }

    protected function checkParm($attr,$typ,$parm) 
    {
        if ($parm === M_P_EVAL or $parm === M_P_EVALP) {
            if (! class_exists($this->getModName())) {
                $this->_errLog->logLine(E_ERC040.':'.$attr.':'.$typ);
                return false;
            }
            return true;
        }
        if ($typ != M_REF and $typ != M_CREF and $typ != M_CODE) {
            if ($parm) {
                $this->_errLog->logLine(E_ERC041.':'.$attr.':'.$typ.':'.$parm);
                return false;
            }
            return true;
        }
        $path=explode('/', $parm);
        $root = $path[0];
        if (($root != "" )) {
            $this->_errLog->logLine(E_ERC020.':'.$attr.':'.$parm);
            return false;
        }
        $c = count($path)-1;

        switch ($typ) {
            case M_REF:
                if ($c==1) {
                    return true;
                }
            case M_CREF:
                if ($c==2) {
                    return true;
                }
            case M_CODE:
                if ($c==3) {
                    return true;
                }
        }
        $this->_errLog->logLine(E_ERC020.':'.$attr.':'.$parm);
        return false;
    }
  
    public function getModRef($attr) 
    {
        return $this->getRefMod($attr);
    }
    // should be private
    public function getRefMod($attr) 
    {     
        if (! $this->existsAttr($attr)) {
            $this->_errLog->logLine(E_ERC002.':'.$attr);
            return false;
        }
        if ($this->getTyp($attr)!= M_REF) {
             $this->_errLog->logLine(E_ERC026.':'.$attr);
            return false;
        }
        $path=$this->getParm($attr);
        $patha=explode('/', $path);
        return ($patha[1]);
    }
       /**
     * Get the possible values of a code attribute.
     *
     * @param string $Attr the attribute. 
     *
     * @return array
     */
    public function getValues($attr) 
    {
        $r=$this->getTyp($attr);
        if (!$r) {
            return false;
        }
        if ($r != M_CODE and $r != M_REF) {
            $this->_errLog->logLine(E_ERC015.':'.$attr.':'.$r); 
            return false;
        }
        if ($r == M_CODE) {
            $r=$this->getParm($attr);
            $apath = explode('/', $r);
            $mod = new Model($apath[1], (int) $apath[2]);
            $val = $mod->getVal($apath[3]);
        }
        if ($r == M_REF) {
            $mod = $this->getRefMod($attr);
            $obj= new Model($mod);
            $val=$obj->_stateHdlr->findObjWheOp($mod, [], [], []);
        }
        return $val;
    }

     /**
     * Check the value of a ref attribute.
     *
     * @param string $Attr the attribute. 
     * @param void   $id   the value.
     *
     * @return boolean
     */   
    protected  function checkRef($attr,$id) 
    {
        if (is_null($id)) {
            return true;
        }
        
        $mod = $this->getRefMod($attr);
        try {
            $res = new Model($mod, $id);
        } 
        catch (Exception $e) {
            $this->_errLog->logLine($e->getMessage());
            return false;
        }
        return true;
    }
    /**
     * Check if a mandatory attribute is set.
     *
     * @param string $Attr the attribute. 
     *
     * @return boolean
     */    
    protected function checkMdtr($attrList)
    {
        foreach ($attrList as $attr) {
            if (array_key_exists($attr, $this->_attrVal)) {
                $val = $this->getVal($attr);
                if (is_null($val)) {
                    $this->_errLog->logLine(E_ERC019.':'.$attr);
                    return false;
                }
            } else {
                $this->_errLog->logLine(E_ERC019.':'.$attr);
                return false;
            }
        }
        return true;
    }   
    
    protected function checkCkeyList($ckeyList) 
    {
        foreach ($ckeyList as $attrLst) {
            $res=$this->checkCkey($attrLst);
            if (!$res) {
                $l=implode(':', $attrLst);
                $this->_errLog->logLine(E_ERC031.':'.$l);
                return false;
            }
        }
        return true;
    }
    
    
    /**
     * Save an object.
     *
     * @return int the id.
     */      
    public function save()
    {
        if ($this->isAbstr()) {
            $this->_errLog->logLine(E_ERC044);
            return false;
        }
        if (! $this->_stateHdlr) {
            $this->_errLog->logLine(E_ERC006);
            return false;
        }
        if ($this->_modChgd) {
            $this->_errLog->logLine(E_ERC024);
            return false;
        }

        $attrL= $this->getAllMdtr();
        if (!$this->checkMdtr($attrL)) {
            return false;
        }
        $abstr = $this->getInhObj();
        if (!is_null($abstr)) {
            $attrL= $abstr->getAllMdtr();
            if (!$this->checkMdtr($attrL)) {
                return false;
            }
        }

        $ckeyList=$this->getAllCkey();
        if (!$this->checkCkeyList($ckeyList)) {
            return false;
        }
        if (!is_null($abstr)) {
            $ckeyList= $abstr->getAllCkey();
            if (!$this->checkCkeyList($ckeyList)) {
                return false;
            }
        }
        
        $n=$this->getVal('vnum');
        $n++;
        $this->setValNoCheck('vnum', $n);
        $this->setValNoCheck('utstp', date(M_FORMAT_T));
        if (! is_null($this->_obj)) {
            $this->_custom=true;
            $res = $this->_obj->save();
            $this->_custom=false;
            if (!$res) {
                return false;
            }
        }   
        $res=$this->_stateHdlr->saveObj($this);
        $this->_id=$res;
        if (! is_null($this->_obj)) {
            $this->_custom=true;
            $res=$this->_obj->afterSave();
            $this->_custom=false;
            if (!$res) {
                return false;
            }
        }
        return $this->_id;
    }
    
    public function isDel() 
    {
        if ($this->isAbstr()) {
            return false;
        }
        foreach ($this->getAllAttr() as $attr) {
            $typ = $this->getTyp($attr);
            if ($typ == M_CREF) {
                $res=$this->getVal($attr);
                if (count($res)) {
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * Delete an object.
     *
     * @return boolean
     */         
    public function delet() 
    {
        if ($this->isAbstr()) {
            $this->_errLog->logLine(E_ERC044);
            return false;
        }
        if (! $this->_stateHdlr) {
            $this->_errLog->logLine(E_ERC006);
            return false;
        }
        if (!$this->isDel()) {
            $this->_errLog->logLine(E_ERC052);
            return false;
        }
        if (! is_null($this->_obj)) {
            $this->_custom=true;
            $res = $this->_obj->delet();
            $this->_custom=false;
            if (!$res) {
                return false;
            }
        }   
        $res=$this->_stateHdlr->eraseObj($this);
        if ($res) {
            $this->_id=0;
        }

        if (! is_null($this->_obj)) {
            $this->_custom=true;
            $res = $this->_obj->afterDelet();
            $this->_custom=false;
            if (!$res) {
                return false;
            }
        }   
        return $res;
    }
    /**
     * Delete the Model of the object.
     *
     * @return boolean
     */        
    public function deleteMod()
    {
        if (! $this->_stateHdlr) {
            $this->_errLog->logLine(E_ERC006);
            return false;
        }
        $res=$this->_stateHdlr->eraseMod($this);
        $this->initattr();
        return $res;
    }
    /**
     * Save the Model of the object.
     *
     * @return boolean
     */    
    public function saveMod()
    {
        if (! $this->_stateHdlr) {
            $this->_errLog->logLine(E_ERC006);
            return false;
        }
        $res=$this->_stateHdlr->saveMod($this);
        if ($res) {
            $this->_modChgd=false;
        }
        return $res;
    }
}

