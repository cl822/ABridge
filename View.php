<?php

require_once("Model.php"); 
require_once("ViewConstant.php");
require_once("GenHTML.php");

define('V_TYPE', "v_type");
define('V_LT', "v_lt");
define('V_ELEM', "v_elem");
define('V_LIST', "v_list");
define('V_ALIST', "v_alist");
define('V_CLIST', "v_clist");
define('V_CREF', "v_cref");
define('V_CVAL', "v_cval");
define('V_REF', "v_ref");
define('V_ARG', "v_arg");
define('V_OBJ', "v_obj");
define('V_NAV', "v_nav");
define('V_NAVC', "new");
define('V_ERROR', "v_error"); 
define('V_FORM', "v_form");

define('V_ID', "v_id");
define('V_STRING', "v_string");

// V_ELEM
define('V_ATTR', "v_attr");
    
define('V_PROP', "v_prop");   
define('V_P_INP', "v_p_Attr");
define('V_P_LBL', "v_p_Lbl");
define('V_P_VAL', "v_p_Val");
define('V_P_NAME', "v_p_Name");
define('V_P_TYPE', "v_p_type");
define('V_P_REF', "v_p_ref");

define('V_S_CREA', "Create"); // object view states
define('V_S_UPDT', "Update"); 
define('V_S_READ', "Read"); 
define('V_S_DELT', "Delete");
define('V_S_REF', "reference"); // reference view
define('V_S_CREF', "creference"); // collection view
define('V_B_SUBM', "Submit"); // buttons
define('V_B_CANC', "Cancel");



class View
{
    // property

    protected $_model;
    protected $_cmodel;
    protected $_path;
    protected $_attrList;
    protected $_attrHtml=['Sexe'=>H_T_RADIO,'A'=>H_T_SELECT,'De'=>H_T_SELECT]; //bof
    protected $_attrRef;
    protected $_attrCref;
    protected $_listHtml;
    protected $_nav=[V_S_UPDT,V_S_DELT,V_S_CREA];
    protected $_attrLbl = [];
    protected $_attrProp;
    protected $_viewName = []; 

    // constructors

    function __construct($model) 
    {
        $this->_model=$model; 
        $this->_cmodel=null;
    }

    // methods
    
    public function setAttrList($dspec,$viewState="") 
    {
        if ($viewState == V_S_REF) {
            $this->_attrRef = $dspec;
            return true;
        }
        if ($viewState == V_S_CREF) {
            $this->_attrCref = $dspec;
            return true;
        }
        $this->_attrList= $dspec;
        return true;
    }

    public function getAttrList($viewState)
    {
        switch ($viewState) {
            case V_S_REF :
                $dspec = $this->_attrRef;
                if (is_null($dspec)) {
                    $dspec = ['id'];
                }
                return $dspec;
            case V_S_CREF :
                $dspec = $this->_attrCref;
                if (is_null($dspec)) {
                    $dspec = [];
                    $res = array_diff(
                        $this->_model->getAllAttr(),
                        ['vnum','ctstp','utstp']
                    );
                    $ref = $this->getAttrList(V_S_REF);
                    $key = array_search('id', $ref);
                    if ($key!==false) {
                        unset($ref[$key]);
                    } 
                    $res = array_diff($res, $ref);       
                    foreach ($res as $attr) {
                        $atyp=$this->_model->getTyp($attr);
                        if ($atyp != M_CREF and 
                            $atyp != M_TXT
                        ) {
                            $dspec[]=$attr;
                        }
                    }
                }
                return $dspec ;   
            default : 
                $dspec = $this->_attrList;
                if (is_null($dspec)) {
                    $dspec = array_diff(
                        $this->_model->getAllAttr(),
                        ['vnum','ctstp','utstp']
                    );
                }
                return $dspec;
        }
    }
    
    public function setListHtml($dspec) 
    {
        $this->_listHtml= $dspec;
        return true;
    }

    public function getListHtml($listType)
    {       
        switch ($listType) {
            case V_OBJ: 
                $result=H_T_LIST_BR; //BR
                break;
            case V_NAV:
                $result=H_T_LIST_BR;  // BR
                break;
            case V_ALIST:
                $result=H_T_TABLE; // TABLE
                break;
            case V_ATTR:
                $result=H_T_LIST;
                break;          
            case V_CLIST:
                $result=H_T_LIST_BR; //BR
                break;                                              
            case V_CREF:
                $result=H_T_LIST_BR; //BR
                break;
            case V_CVAL:
                $result=H_T_TABLE; // TABLE
                break;
            case V_S_REF: // no choice !
                $result=H_T_CONCAT;
                break;
            case V_S_CREF: // shoud get this from the calling object ? 
                $result=H_T_LIST;  // CONCAT
                break;
            default:
                $result=H_T_LIST;
        }                       
 
        return $result;
    }
     
    public function setPropList($dspec) 
    {
        $this->_attrProp= $dspec;
        return true;
    }

    public function getPropList($viewState) 
    {
        switch($viewState) {
            case V_S_REF :
                $res = [V_P_VAL];
                return $res;
            case V_S_CREF : 
                $res = [V_P_VAL];
                return $res;
            default :
                $res = $this->_attrProp;
                if (is_null($res)) {
                    $res = [V_P_LBL,V_P_VAL];
                }
                return $res;
        }
    }

    public function setNav($dspec) 
    {
        $this->_nav= $dspec;
        return true;
    }
 
    public function setLblList($dspec) 
    {
        $this->_attrLbl= $dspec;
        return true;
    }
    
    public function getLbl($attr) 
    {
        foreach ($this->_attrLbl as $x => $lbl) {
            if ($x==$attr) {
                return $lbl;
            }
        }           
        return $attr;
    }
        
    public function getProp($attr,$prop) 
    {
        switch ($prop) {
            case V_P_LBL:
                return $this->getLbl($attr);
                break;
            case V_P_VAL:
                $val = $this->_model->getVal($attr);
                return $val;
                break;
            case V_P_TYPE:  
                return $this->_model->getTyp($attr);
                break;
            case V_P_NAME:
                return $attr;
                break;
            default:
                return 0;
        }       
    }
    
    public function getAttrHtml($attr) 
    {
        if (isset($this->_attrHtml[$attr])) {
            return $this->_attrHtml[$attr];
        }
        $typ = $this->_model->getTyp($attr);
        $res=H_T_TEXT;
        if ($typ == M_TXT) {
            $res=H_T_TEXTAREA;
        }
        if ($typ == M_CODE) {
            $res=H_T_SELECT;
        }
        return $res;
    }
    
    public function evale($spec,$viewState) 
    {
        $attr = $spec[V_ATTR];
        $typ = $this->_model->getTyp($attr);
        $prop = $spec[V_PROP];
        $res = [];
        if (($viewState == V_S_CREA or $viewState == V_S_UPDT) 
            and $prop==V_P_VAL 
            and (! $this->_model->isProtected($attr)) 
            and
            ($this->_model->isMdtr($attr) or $this->_model->isOptl($attr))) {
            $res[H_NAME]=$attr;
            $default=null;
            if (isset($_POST[$attr])) {
                $default= $_POST[$attr];
            } else {
                if ($viewState == V_S_UPDT) {
                    $default=$this->_model->getVal($attr);
                }
                if ($viewState == V_S_CREA ) {
                    $default=$this->_model->getDflt($attr);
                }                  
            }
            if ($default) {
                $res[H_DEFAULT]=$default;
            }
            $htyp = $this->getAttrHtml($attr);
            $res[H_TYPE]=$htyp;
            if ($htyp == H_T_SELECT or $htyp == H_T_RADIO) {
                $vals=$this->_model->getValues($attr);
                $values=[];
                if ($htyp == H_T_SELECT 
                and (!$this->_model->isMdtr($attr))) {
                    $values[] = ["",""];
                }
                foreach ($vals as $v) {
                    if ($typ == M_CODE) {
                        $m = $this->_model->getCode($attr, (int) $v);
                    }
                    if ($typ == M_REF) {
                        $rmod = $this->_model->getRefMod($attr);
                        $m = new Model($rmod, (int) $v);
                    }
                    $vw = new View($m);
                    $l = $vw->show($this->_path, V_S_REF, false);
                    $r = [$v,$l];
                    $values[]=$r;
                }
                $res[H_VALUES]=$values;    
            }
            return $res ;
        }
        if ($viewState == V_S_CREF) {
            if ($typ == M_REF) {
                $rid = $this->_model->getVal($attr);
                $rmod = $this->_model->getRefMod($attr);
                if (($this->_cmodel->getId() == $rid) and 
                ($this->_cmodel->getModName() == $rmod)) {
                    return false;
                }
            }
        }
        if ($typ != M_CREF or $prop != V_P_VAL) {
            $x=$this->getProp($attr, $prop);
        }
        if ($prop==V_P_VAL) {
            if ($attr == 'id' and $viewState == V_S_CREF) {
                $res[H_TYPE]=H_T_LINK;
                $res[H_LABEL]=$this->show($this->_path, V_S_REF, false);
                $res[H_NAME]=$this->_path->getPath();
                return $res;
            }
            if ($typ==M_CREF) {     
                $id=$spec[V_ID];
                $m = $this->_model->getCref($attr, $id);
                $v = new View($m);
                $m = $this->_cmodel;
                if (is_null($m)) {
                    $m = $this->_model;
                }
                $this->_path->push($attr, $id);
                $v->_path=$this->_path;
                $res = $v->buildCView($m, V_S_CREF);
                $this->_path->pop();
                return $res; 
            }
            if ($typ==M_REF) {
                $res[H_TYPE]=H_T_LINK;
                $m=$this->_model->getRef($attr);
                if (is_null($m)) {
                    return false;
                }
                $v = new View($m);
                $res[H_LABEL]=$v->show($this->_path, V_S_REF, false);
                $res[H_NAME]=$this->_path->getRefPath($m); 
                return $res;        
            }
            if ($typ==M_CODE and (!is_null($x))) {
                $m=$this->_model->getCode($attr, (int) $x);
                $v = new View($m);
                $x = $v->show($this->_path, V_S_REF, false);
            }
        }
        $res =[H_TYPE =>H_T_PLAIN, H_DEFAULT=>$x];
        if ($typ ==  M_TXT and $prop==V_P_VAL) {
            $res = [H_TYPE =>H_T_TEXTAREA, H_DISABLED=>true, H_DEFAULT=>$x];
        }
        return $res;
    }
    
    public function evalo($dspec,$viewState) 
    {
        $name = $this->_model->getModName();
        $res =[H_TYPE =>H_T_PLAIN, H_DEFAULT=>$name];
        return $res;
    }
    
    public function subst($spec,$viewState)
    {
        $type = $spec[V_TYPE];
        $result=[];
        switch ($type) {
            case V_ELEM:
                    $result= $this->evale($spec, $viewState);
                break;
            case V_OBJ:
                    $result= $this->evalo($spec, $viewState);
                break;
            case V_LIST:
                    $lt = "";
                    if (isset($spec[V_LT])) {
                        $lt=$spec[V_LT];
                    }
                    $result[H_TYPE]=$this->getListHtml($lt);
                    $result[H_SEPARATOR]= ' ';
                    $arg=[];
                    foreach ($spec[V_ARG] as $elem) {
                        $r=$this->subst($elem, $viewState);
                        if ($r) {
                            $arg[]=$r;
                        }
                    }
                    $result[H_ARG]=$arg;
                break;
            case V_FORM:
                    $result[H_TYPE]=H_T_FORM;
                    $path = $this->_path->getPath();
                    $result[H_ACTION]="POST";
                    $result[H_HIDDEN]=$viewState;
                    $result[H_URL]=$path;                   
                    $arg=[];
                    foreach ($spec[V_ARG] as $elem) {
                        $r=$this->subst($elem, $viewState);
                        if ($r) {
                            $arg[]=$r;
                        }
                    }
                    $result[H_ARG]=$arg;
                break;
            case V_NAVC:
                    $result[H_TYPE]=H_T_LINK;
                    $result[H_LABEL]=$this->getLbl(V_NAVC);
                    $path = $this->_path->getPath();
                    $result[H_NAME]="'".$path.'/'.$spec[V_ATTR]."'";// here
                break;
            case V_NAV:
                    $result[H_TYPE]=$this->getListHtml(V_NAV);;
                    $result[H_SEPARATOR]= ' ';
                    $arg=[];
                    $res=[];
                    if ($viewState == V_S_CREA 
                    or  $viewState == V_S_DELT 
                    or  $viewState == V_S_UPDT) {
                        $res[H_TYPE]=H_T_SUBMIT;
                        $res[H_LABEL]=$this->getLbl(V_B_SUBM);
                        $arg[]=$res;
                        $res=[];
                        $res[H_TYPE]=H_T_LINK;
                        $lbl=$this->getLbl(V_B_CANC);  
                        $res[H_LABEL]=$lbl;                   
                        if ($viewState == V_S_CREA) {
                            $path = $this->_path->getObjPath();
                        } else {
                            $path = $this->_path->getPath();
                        }
                        $res[H_NAME]="'".$path."'";
                        $arg[]=$res;
                    }       
                    if ($viewState == V_S_READ) {
                        if (count($this->_nav)) {
                            $res[H_TYPE]=H_T_LINK;      
                            foreach ($this->_nav as $nav) {
                                $res[H_LABEL]=$this->getLbl($nav);
                                if ($nav == V_S_CREA) {
                                    $path = $this->_path->getCreaPath();
                                    $res[H_NAME]=$path; 
                                } else {
                                    $path = $this->_path->getPath(); 
                                    $res[H_NAME]="'".$path.'?View='.$nav."'";
                                }
                                $arg[]=$res;
                            }
                            
                        }
                    }
                    $result[H_ARG]=$arg;
                break;
            case V_ERROR:
                    $result[H_TYPE]=H_T_PLAIN;
                    $result[H_DEFAULT]=$spec[V_STRING];
                break;                              
        }
        return $result;
    }

    public function show($path,$viewState,$show = true) 
    {
        $this->_path=$path;
        $r = $this->buildView($viewState);
        $r=genFormElem($r, $show);
        return $r;
    }
    
    public function buildCView($cmodel,$viewState) 
    {
        $this->_cmodel = $cmodel;
        return ($this->buildView($viewState));
    }
    
    
    public function buildView($viewState) 
    {
        $labels = 
        [ 'Person'  => ['SurName','Name'],
          'Student' => ['SurName','Name'],
          'Cours'   => ['Name'],
          'CodeValue'=>['Name']];

        if (isset($labels[$this->_model->getModName()])) {
            $x = $labels[$this->_model->getModName()];
            $this->setAttrList($x, V_S_REF);
        }
        
        $name = $this->_model->getModName();
        $spec=[];       
        $specL=[];                  
        foreach ($this->getAttrList($viewState) as $attr) {
            $view =[];
            $typ= $this->_model->getTyp($attr);
            if ($typ != M_CREF) {
                foreach ($this->getPropList($viewState) as $prop) {
                    $view[] = [V_TYPE=>V_ELEM,V_ATTR => $attr, V_PROP => $prop];
                }
                if ($viewState == V_S_REF or $viewState == V_S_CREF) {
                    $spec = array_merge($spec, $view);
                } else {
                    $spec[]=[V_TYPE=>V_LIST,V_LT=>V_ATTR,V_ARG=>$view];
                }
            }
            if ($typ == M_CREF) {
                $view[]=[V_TYPE=>V_ELEM,V_ATTR => $attr, V_PROP => V_P_LBL];
                $view[]=[V_TYPE=>V_NAVC,V_ATTR => $attr];
                $viewe=[];
                foreach ($this->_model->getVal($attr) as $id) {
                    $viewe[]=[V_TYPE=>V_ELEM,V_ATTR => $attr,
                             V_PROP => V_P_VAL,V_ID=>$id];
                }
                $view[]=[V_TYPE=>V_LIST,V_LT=>V_CVAL,V_ARG=>$viewe];
                $specL[]=[V_TYPE=>V_LIST,V_LT=>V_CREF,V_ARG=>$view];
            }
        }
        if ($viewState == V_S_REF or $viewState == V_S_CREF) {
            $specf = [V_TYPE=>V_LIST,V_LT=>$viewState,V_ARG=>$spec];
            $r=$this->subst($specf, $viewState);
            return $r;
        }
        $arg = [];
        $arg[]= [V_TYPE=>V_OBJ];
        $arg[]= [V_TYPE=>V_NAV];
        $arg[]= [V_TYPE=>V_LIST,V_LT=>V_ALIST,V_ARG=>$spec];
        if ($this->_model->isErr()) {
            $e = $this->viewErr();
            $arg[]=$e;
        }
        if ($viewState == V_S_CREA 
        or  $viewState == V_S_DELT 
        or  $viewState == V_S_UPDT) {
            $speci = [V_TYPE=>V_FORM,V_LT=>V_OBJ,V_ARG=>$arg];
            $r=$this->subst($speci, $viewState);
            return $r;
        }
        $arg[] = [V_TYPE=>V_LIST,V_LT=>V_CLIST,V_ARG=>$specL];
        $speci = [V_TYPE=>V_LIST,V_LT=>V_OBJ,V_ARG=>$arg];  
        $r=$this->subst($speci, $viewState);
        return $r;
    }

    public function viewErr() 
    {
            $log = $this->_model->getErrLog();
            $c=$log->logSize();
            if (!$c) {
                return 0;
            }
            $result=[];
            for ($i=0;$i<$c;$i++) {
                $r = $log->getLine($i);
                $result[$i]=[V_TYPE=>V_ERROR,V_STRING=>$r];
            }
            $result = [V_TYPE =>V_LIST,V_LT=>V_ERROR,V_ARG=>$result];
            return $result;
    }

};

