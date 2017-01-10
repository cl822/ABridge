<?php

require_once("Model.php"); 
require_once("ViewConstant.php");
require_once("GenHTML.php");


class View
{
    // property

    protected $_model;
    protected $_cmodel;
    protected $_path;

    protected $_attrHtml= [];
                
    protected $_attrList;
    
    protected $_listHtml = [
                V_OBJ   => H_T_LIST_BR,
                V_CNAV  => H_T_1TABLE,
                V_NAV   => H_T_1TABLE,
                V_ALIST => H_T_TABLE,
                V_ATTR  => H_T_LIST,
                V_CLIST => H_T_LIST_BR,
                V_CREF  => H_T_LIST_BR,
                V_CVAL  => H_T_TABLE,
                V_S_REF => H_T_CONCAT, // do not change
                V_S_CREF=> H_T_LIST, // caller or callee ?
                V_ERROR => H_T_LIST,
                ];

    protected $_nav=[
              V_S_READ =>[
                [V_TYPE=>V_NAV,V_P_VAL=>V_S_UPDT],
                [V_TYPE=>V_NAV,V_P_VAL=>V_S_DELT],
                [V_TYPE=>V_NAV,V_P_VAL=>V_S_CREA],
                [V_TYPE=>V_NAV,V_P_VAL=>V_S_SLCT],
                ],
              V_S_SLCT =>[
                [V_TYPE=>V_NAV,V_P_VAL=>V_B_SUBM],
                [V_TYPE=>V_NAV,V_P_VAL=>V_B_RFCH],
                [V_TYPE=>V_NAV,V_P_VAL=>V_B_CANC],
                [V_TYPE=>V_NAV,V_P_VAL=>V_S_CREA],
                ],
              V_S_CREA =>[
                [V_TYPE=>V_NAV,V_P_VAL=>V_B_SUBM],
                [V_TYPE=>V_NAV,V_P_VAL=>V_B_CANC],
                ],
              V_S_UPDT =>[
                [V_TYPE=>V_NAV,V_P_VAL=>V_B_SUBM],
                [V_TYPE=>V_NAV,V_P_VAL=>V_B_CANC],
                ],
              V_S_DELT =>[
                [V_TYPE=>V_NAV,V_P_VAL=>V_B_SUBM],
                [V_TYPE=>V_NAV,V_P_VAL=>V_B_CANC],
                ],              
              ];

    protected $_navClass=[];

    protected $_attrProp=[
              V_S_READ =>[V_P_LBL,V_P_VAL],
              V_S_SLCT =>[V_P_LBL,V_P_VAL],
              V_S_CREA =>[V_P_LBL,V_P_VAL],
              V_S_UPDT =>[V_P_LBL,V_P_VAL],
              V_S_DELT =>[V_P_LBL,V_P_VAL],
              V_S_REF  =>[V_P_VAL],
              V_S_CREF =>[V_P_VAL],
              ];
              
    protected $_attrLbl = [];

    // constructors

    function __construct($model) 
    {
        $this->_model=$model; 
        $this->_cmodel=null;
    }


    
    // methods
    
    public function setAttrList($dspec,$viewState) 
    {
        $this->_attrList[$viewState]= $dspec;
        return true;
    }

    public function getAttrList($viewState)
    {
        if (isset($this->_attrList[$viewState])) {
            return $this->_attrList[$viewState];
        }
        $dspec = [];
        switch ($viewState) {
            case V_S_REF :
                $dspec = ['id']; 
                return $dspec;
            case V_S_SLCT :
                $res = array_diff(
                    $this->_model->getAllAttr(),
                    ['vnum','ctstp','utstp']
                );
                foreach ($res as $attr) {
                    $atyp=$this->_model->getTyp($attr);
                    $eval = $this->_model->isEval($attr);
                    if ($atyp != M_CREF and $atyp != M_TXT and !$eval) {
                        $dspec[]=$attr;
                    }
                }
                return $dspec ;   
            case V_S_CREF :
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
                    if ($atyp != M_CREF and $atyp != M_TXT) {
                        $dspec[]=$attr;
                    }
                }
                return $dspec ;   
            default : 
                $dspec = array_diff(
                    $this->_model->getAllAttr(),
                    ['vnum','ctstp','utstp']
                );
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
        if (isset($this->_listHtml[$listType])) {
            return $this->_listHtml[$listType];
        }
        return H_T_LIST;
    }
     
    public function setPropList($dspec,$viewState) 
    {
        $this->_attrProp[$viewState]= $dspec;
        return true;
    }

    public function getPropList($viewState) 
    {
        if (isset($this->_attrProp[$viewState])) {
            return $this->_attrProp[$viewState];
        }
        $res = [V_P_LBL,V_P_VAL];
        return $res;
    }

    public function setNavClass($dspec)
    {
        $this->_navClass=[];
        foreach ($dspec as $classN) {
            $this->_navClass[]= 
            [V_TYPE=>V_CNAV,V_OBJ=>$classN,V_P_VAL=>V_S_SLCT];
        }
        return true;
    }

    public function getNavClass($viewState)
    {
        if (isset($this->_navClass)) {
            return $this->_navClass;
        }
 
        return [];
    }
    
    public function setNav($dspec,$viewState) 
    {
        $this->_nav[$viewState]= $dspec;
        return true;
    }
    
    public function getNav($viewState) 
    {
        if (isset($this->_nav[$viewState])) {
            return $this->_nav[$viewState];
        }
        return [];
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
            case V_P_OP:
                return ':';
                break;              
            default:
                return 0;
        }       
    }
    
    public function setAttrListHtml($dspec,$viewState) 
    {
        $this->_attrHtml[$viewState]= $dspec;
        return true;
    }
    
    public function getAttrHtml($attr,$viewState) 
    {
        if (isset($this->_attrHtml[$viewState])) {
            $list = $this->_attrHtml[$viewState];
            if (isset($list[$attr])) {
                return $list[$attr];
            }
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
        if ($attr == V_S_SLCT) { //bof
            $typ = M_CREF;
        } else {
            $typ = $this->_model->getTyp($attr);
        }
        $prop = $spec[V_PROP];
        $res = [];
        
        if ($prop == V_P_OP and $viewState == V_S_SLCT) {
            $res[H_TYPE]=H_T_SELECT;
            $name=$attr.'_OP';
            $res[H_NAME]=$name;
            $res[H_VALUES]=[['=','='],['>','>'],['<','<']];
            if ($typ == M_CODE) {
                $res[H_VALUES]=[['=','=']];
            }
            if ($typ == M_STRING) {
                $res[H_VALUES]=[['::','::'],['=','='],['>','>'],['<','<']];
            }
            $default='=';
            if (isset($_POST[$name])) {
                $default= $_POST[$name]; // only dep on method !!
            }
            $res[H_DEFAULT]=$default;
            return $res;
        }
        
        if ($prop==V_P_VAL and 
            ((($viewState == V_S_CREA or $viewState == V_S_UPDT)
             and $this->_model->isModif($attr))
            or 
            ($viewState == V_S_SLCT 
             and $this->_model->isSelect($attr)))) {
            $res[H_NAME]=$attr;
            $default=null;
            if (isset($_POST[$attr])) {
                $default= $_POST[$attr]; // only dep on method !!
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
            $htyp = $this->getAttrHtml($attr, $viewState);
            $res[H_TYPE]=$htyp;
            if ($htyp == H_T_SELECT or $htyp == H_T_RADIO) {
                $vals=$this->_model->getValues($attr);
                $values=[];
                if ($htyp == H_T_SELECT 
                and ((!$this->_model->isMdtr($attr)) 
                or $viewState == V_S_SLCT)) {
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
                if (
                (!is_null($this->_cmodel)) and 
                ($this->_cmodel->getId() == $rid) and 
                ($this->_cmodel->getModName() == $rmod) and 
                 $rid!= 0) {
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
            if ($typ==M_CREF and $attr != V_S_SLCT ) {     
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
            if ($typ==M_CREF and $attr == V_S_SLCT ) {     
                $id=$spec[V_ID];
                $m = new Model($this->_model->getModName(), $id);
                $v = new View($m);
                $m = $this->_cmodel;
                if (is_null($m)) {
                    $m = $this->_model;
                }
                $this->_path->pushid($id);
                $v->_path=$this->_path;
                $res = $v->buildCView($m, V_S_CREF);
                $this->_path->popid();
                return $res; 
            }
            if ($typ==M_REF) {
                $m=$this->_model->getRef($attr);
                if (is_null($m)) {
                    return [H_TYPE =>H_T_PLAIN, H_DEFAULT=>""];
                }
                $res[H_TYPE]=H_T_LINK;
                $v = new View($m);
                $res[H_LABEL]=$v->show($this->_path, V_S_REF, false);
                $mod= $m->getModName();
                $id = $m->getId();
                $res[H_NAME]=$this->_path->getRefPath($mod, $id); 
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
    
    public function evaln($nav,$viewState) 
    {
        $res=[];
        $nav=$nav[V_P_VAL];
        if ($nav == V_B_SUBM) {
            $res[H_TYPE]=H_T_SUBMIT;
            $res[H_LABEL]=$this->getLbl($viewState);
        } else {
            $res[H_TYPE]=H_T_LINK;
            $res[H_LABEL]=$this->getLbl($nav);
            $path = $this->_path->getActionPath($nav);
            $res[H_NAME]=$path; 
        }
        return $res;
    }
    
    public function evalc($spec, $viewState)
    {
        $res=[];
        $nav=$spec[V_P_VAL];
        $mod=$spec[V_OBJ];
        $res[H_TYPE]=H_T_LINK;
        $res[H_LABEL]=$this->getLbl($mod);
        if ($mod == 'Home') {
             $path = "'".$this->_path->getHomePath()."'";
        } else {
             $path = $this->_path->getClassPath($mod, $nav);
        } 
        $res[H_NAME]=$path; 
        return $res;
    }
    
    public function evalcn($spec,$viewState)
    {
        $result=[];
        $nav=$spec[V_P_VAL];
        $result[H_LABEL]=$this->getLbl($nav);
        $attr=$spec[V_ATTR];
        if ($nav==V_B_NEW) {
            $result[H_TYPE]=H_T_LINK;
            $path=$this->_path->getCrefPath($attr, V_S_CREA);
            $result[H_NAME]=$path;
        } else {
            $pos = $spec[V_ID];
            $path=$this->_path->getPath().'?'.$attr.'='.$pos;
            if ($viewState == V_S_SLCT) {
                $result[H_TYPE]=H_T_SUBMIT;
                $result[H_BACTION]=$path;   
            }
            if ($viewState == V_S_READ) {
                $result[H_TYPE]=H_T_LINK;;
                $result[H_NAME]=$path;  
            }
        }
        return $result;
    }
    
    public function subst($spec,$viewState)
    {
        $type = $spec[V_TYPE];
        $result=[];
        switch ($type) {
            case V_ELEM:
                    $result= $this->evale($spec, $viewState);
                break;
            case V_NAV:
                    $result= $this->evaln($spec, $viewState);
                break;
            case V_CNAV:
                    $result= $this->evalc($spec, $viewState);
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
                    $result= $this->evalcn($spec, $viewState);
                break;
            case V_PLAIN:
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
        if ($viewState != V_S_REF and $viewState != V_S_CREF) {
            $r=genHTML($r, $show);
        } else {
            $r=genFormElem($r, $show);
        }
        return $r;
    }
    
    public function buildCView($cmodel,$viewState) 
    {
        $this->_cmodel = $cmodel;
        return ($this->buildView($viewState));
    }
    
    public function initView($model,$viewState)
    {   
        if (is_null($model)) {
            return true;
        }
        $modName = $model->getModName();
        $spec = Handler::get()->getViewHandler($modName);
        if (is_null($spec)) {
                return true;
        }
        if (isset($spec['attrList'])) {
            $specma=$spec['attrList'];
            if (isset($specma[$viewState])) {
                $this->setAttrList($specma[$viewState], $viewState);
            }
        }
        if (isset($spec['attrHtml'])) {
            $specma=$spec['attrHtml'];
            if (isset($specma[$viewState])) {
                $this->setAttrListHtml($specma[$viewState], $viewState);
            }
        }
        if (isset($spec['attrProp'])) {
            $specma=$spec['attrProp'];
            if (isset($specma[$viewState])) {
                $this->setPropList($specma[$viewState], $viewState);
            }
        }
        if (isset($spec['lblList'])) {
            $specma=$spec['lblList'];
            $this->setLblList($specma);
        }
        
    }
    public function buildView($viewState) 
    {
        $spec=[];       
        $specL=[];  
        $specS=[];
        $arg = [];
        
        $this->initView($this->_model, $viewState);

        if (is_null($this->_model)) {
            $navClass= $this->getNavClass($viewState);
            $arg[]= [V_TYPE=>V_LIST,V_LT=>V_CNAV,V_ARG=>$navClass];
            $speci = [V_TYPE=>V_LIST,V_LT=>V_OBJ,V_ARG=>$arg];  
            $r=$this->subst($speci, $viewState);
            return $r;          
        }  
             
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
                $view[]=[V_TYPE=>V_NAVC,V_ATTR => $attr,V_P_VAL=>V_B_NEW];
                $list = $this->_model->getVal($attr);
                $view = $this->getSlice($attr, $list, $view);
                $specL[]=[V_TYPE=>V_LIST,V_LT=>V_CREF,V_ARG=>$view];
            }
        }
        if ($viewState == V_S_REF or $viewState == V_S_CREF) {
            $specf = [V_TYPE=>V_LIST,V_LT=>$viewState,V_ARG=>$spec];
            $r=$this->subst($specf, $viewState);
            return $r;
        }
        $arg = [];
        $navClass= $this->getNavClass($viewState);
        $arg[]= [V_TYPE=>V_LIST,V_LT=>V_CNAV,V_ARG=>$navClass]; 
        $arg[]= [V_TYPE=>V_OBJ];
        $navs = $this->getNav($viewState);
        $arg[]= [V_TYPE=>V_LIST,V_LT=>V_NAV,V_ARG=>$navs];
        $arg[]= [V_TYPE=>V_LIST,V_LT=>V_ALIST,V_ARG=>$spec];
        if ($viewState == V_S_SLCT ) {
            $view=[];
            $view[]=[V_TYPE=>V_ELEM,V_ATTR => V_S_SLCT, V_PROP => V_P_LBL];
            $list=$this->_model->select();
            $view = $this->getSlice(V_S_SLCT, $list, $view);
            $specS[]=[V_TYPE=>V_LIST,V_LT=>V_CREF,V_ARG=>$view];
            $arg[] = [V_TYPE=>V_LIST,V_LT=>V_CLIST,V_ARG=>$specS];
        }
        if ($this->_model->isErr()) {
            $e = $this->viewErr();
            $arg[]=$e;
        }
        if ($viewState == V_S_CREA 
        or  $viewState == V_S_DELT 
        or  $viewState == V_S_UPDT
        or  $viewState == V_S_SLCT) {
            $speci = [V_TYPE=>V_FORM,V_LT=>V_OBJ,V_ARG=>$arg];
            $r=$this->subst($speci, $viewState);
            return $r;
        }
        $arg[] = [V_TYPE=>V_LIST,V_LT=>V_CLIST,V_ARG=>$specL];
        $speci = [V_TYPE=>V_LIST,V_LT=>V_OBJ,V_ARG=>$arg];  
        $r=$this->subst($speci, $viewState);
        return $r;
    }

    
    protected function getSlice($attr,$list,$viewL) 
    {
        $view[]=$viewL[0];
        $c=count($list);
        $pos=0;
        $slice = 10;
        if (isset($_GET[$attr])) {
            $pos=(int) $_GET[$attr];
            if ($pos<0) {
                $pos=-$pos-$slice;
                if ($pos<0) {
                    $pos=0;
                }
            }
            if ($pos > $c) {
                $pos=$c-$slice;
            }               
        }
        if ($c > $slice) {
                $list= array_slice($list, $pos, $slice);
        }
        $viewe=[];
        foreach ($list as $id) {
            $viewe[]=[V_TYPE=>V_ELEM,V_ATTR => $attr,
                      V_PROP => V_P_VAL,V_ID=>$id];
        }
        $ind = $c;
        if ($c > $slice) {
            $nc=count($list)+$pos;
            $ind=$c.' : '.$pos.'-'.$nc;
        }
        $view[]=[V_TYPE=>V_PLAIN,V_STRING=>$ind];
        if ($c > $slice) {
            $npos= $pos+$slice;
            if ($npos>=$c) {
                $npos=$pos;
            }
            $view[]=
            [V_TYPE=>V_NAVC,V_ATTR => $attr,V_P_VAL=>V_B_NXT,V_ID=>$npos];
            $view[]=
            [V_TYPE=>V_NAVC,V_ATTR => $attr,V_P_VAL=>V_B_PRV,V_ID=>-$pos];
        }
        $first = true;
        foreach ($viewL as $elm) {
            if (! $first) {
                $view[] = $elm;
            }
            $first = false;
        }
        $view[]=[V_TYPE=>V_LIST,V_LT=>V_CVAL,V_ARG=>$viewe];
        return $view;
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

