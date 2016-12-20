
<?php

// must clean up interface with set up !!

require_once("Model.php"); 
require_once("View.php"); 
require_once("Path.php"); 

class Controler
{
    protected $_bases=[];
    protected $_obj = null;
    protected $_logLevel = 0;
    
    function __construct($config) 
    {
        $bases = [];
        $handlers = [];
        resetHandlers();
        foreach ($config as $classN => $handler) {
            if (! in_array($handler, $handlers)) {
                $handlers[] = $handler;
                $x = getBaseHandler($handler[0], $handler[1]);
                $bases[]=$x;
            }
            initStateHandler($classN, $handler[0], $handler[1]);
        }
        $this->_bases = $bases;
    }

    protected function beginTrans() 
    {
        foreach ($this->_bases as $base) {
            $base-> beginTrans();
        }
    }
    
    protected function setLogLevl($level) 
    {
        if (!$level) {
            return true;
        }
        foreach ($this->_bases as $base) {
            $base-> setLogLevl($level);
        }
        $this->_logLevel=$level;
    }
    
    protected function logStartView() 
    {
        if (!$this->_logLevel) {
            return true;
        }
        foreach ($this->_bases as $base) {
            $log = $base-> getLog();
            $log->logLine(' **************  ');
        }
    }
    
    protected function showLog() 
    {
        if (!$this->_logLevel) {
            return true;
        }
        foreach ($this->_bases as $base) {
            $log = $base-> getLog();
            $log->show();
        }
    }
    
    protected function commit()
    {
        $res = true;
        foreach ($this->_bases as $base) {
            $r =$base->commit();
            $res = ($res and $r);
        }
        return $res;
    }
    
    protected function close()
    {
        $res = true;
        foreach ($this->_bases as $base) {
            $r =$base->close();
            $res = ($res and $r);
        }
        return $res;
    }
    
    protected function rollback()
    {
        $res = true;
        foreach ($this->_bases as $base) {
            $r =$base->rollback();
            $res = ($res and $r);
        }
        return $res;
    }
    
    protected function setVal() 
    {
        $c= $this->_obj;
        foreach ($c->getAllAttr() as $attr) { 
            if ($c->isMdtr($attr) or $c->isOptl($attr)) {
                if (isset($_POST[$attr])) {
                    $val= $_POST[$attr];
                    $typ= $c->getTyp($attr);
                    $valC = convertString($val, $typ);
                    $c->setVal($attr, $valC);
                }
            }
        }
        return (!$c->isErr());
    }
    
    protected function getAction($method) 
    {
        $action = V_S_READ;
        if ($method == 'GET') {
            if (isset($_GET['View'])) {
                $action = $_GET['View'];
            }
            if (! $this->_obj->getid()) {
                $action = V_S_CREA;
            }
        }
        if ($method =='POST') {
            $action = $_POST['action'];
        }
        return $action; 
    }
    
    function run($show,$logLevel)
    {
        $method = $_SERVER['REQUEST_METHOD'];   
        $this->beginTrans();
        $this->setLogLevl($logLevel);   
        $path = new Path();
        $this->_obj = $path->getObj();
        $action = $this->getAction($method);
        $actionExec = false;
        if ($method =='POST') {
            if ($action == V_S_UPDT or $action == V_S_CREA) {
                $res = $this->setVal();
            }
            if (!$this->_obj->isErr()) {
                if ($action == V_S_DELT) {
                    $this->_obj->delet();
                }
                if ($action == V_S_UPDT or $action == V_S_CREA) {
                    $this->_obj->save();         
                }
            }
            if (!$this->_obj->isErr()) {
                $res=$this->commit();
                if ($res) {
                    $actionExec=true;
                }
            } else {
                $this->rollback(); 
            }
        }
        if ($actionExec) {
            if ($action == V_S_DELT) {
                $path->pop();
                $this->_obj= $path->getObj();
            }
            if ($action == V_S_CREA) {
                $path->pushId($this->_obj->getId());
            }
            $action= V_S_READ;
        }
        $this->logStartView();
        $v=new View($this->_obj);
        $v->show($path, $action, $show);
        $this->close();
        $this->showLog();
        return $path->getPath();
    }
}


