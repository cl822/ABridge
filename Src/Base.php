
<?php

require_once 'Logger.php';

abstract class Base
{
    protected static $_filePath ="";
    protected $_objects=[];
    protected $_fileN;
    protected $_fileName;
    protected $_logLevl;
    protected $_logger;
    protected $_connected;
     
    protected function  __construct($id) 
    {
        $this->_fileN = self::$_filePath . $id;
        $this->_fileName = $this->_fileN.'.txt';
        $this->_logLevl=0;
        $this->_logger=null;
        $this->_connected=true;
        $this->load();
    }

    protected function erase() 
    {
        if (file_exists($this->_fileName)) {
            unlink($this->_fileName);
        }
        $this->_objects = [];
        return true;
    }       
    
    public static function setPath($path)
    {
        self::$_filePath =$path;
        return true;
    }
    
    
    public static function getPath()
    {
        return self::$_filePath;
    }
    
    protected static function _exists($id) 
    {
        $f = self::$_filePath . $id.'.txt';
        return file_exists($f);
    }
    
    function connect() 
    {
        $this->_connected = true;
        return true;
    }
    
    function close() 
    {
        if (! $this->isConnected()) {
            throw new Exception(E_ERC025);
        }
        $this->_connected = false;
        return true;
    }
    
    function isConnected() 
    {
        return $this->_connected;
    }
    
    private function load() 
    {
        if (file_exists($this->_fileName)) {
            $file = file_get_contents($this->_fileName, FILE_USE_INCLUDE_PATH);
            $this->_objects = unserialize($file);
            return true;
        }
        $this->_objects = [];
        return true;
    }
    
    function beginTrans() 
    {
        if (! $this->isConnected()) {
            throw new Exception(E_ERC025);
        }
        $this->_transOpen=true;
        return true;
    }

    function commit()
    {
        if (! $this->isConnected()) {
            throw new Exception(E_ERC025);
        }
        $file = serialize($this->_objects);
        $r=file_put_contents($this->_fileName, $file, FILE_USE_INCLUDE_PATH);
        return $r;
    }

    function rollback() 
    {
        if (! $this->isConnected()) {
            throw new Exception(E_ERC025);
        }
        $this->load();
        return true;
    }
 
    function existsMod ($model) 
    {
        if (! $this->isConnected()) {
            throw new Exception(E_ERC025);
        }
        return(array_key_exists($model, $this->_objects));
    }
 
    function newMod($model,$meta) 
    {
        return $this->newModId($model, $meta, true);
    }
    
    function newModId($model,$meta,$idF) 
    {
        if ($this->existsMod($model)) {
            return false;
        }; 
        $meta['lastId']=1;
        if (!$idF) {
            $meta['lastId']=0;
        }
        $this->_objects[$model][0] = $meta;
        return true;
    }   

    function getAllMod () 
    {
        if (! $this->isConnected()) {
            throw new Exception(E_ERC025);
        }
        return array_keys($this->_objects);
    }
    
    
    function getMod($model) 
    {
        if (! $this->existsMod($model)) {
            return false;
        };
        $meta = $this->_objects[$model][0] ;
        unset($meta['lastId']);
        return $meta;
    }
    
    protected function putModel($model,$meta) 
    {
        $id = $this->_objects[$model][0]['lastId'] ;
        $meta['lastId']=$id;
        $this->_objects[$model][0] = $meta;
        return true;
    }
    
    function delMod($model) 
    {
        if (! $this->existsMod($model)) {
            return true;
        };
        unset($this->_objects[$model]);
        return true;    
    }
    
    function setLogLevl ($levl) 
    {
        if (($levl > 0) and is_null($this->_logger)) {
            $logname = $this->_fileN.'_ErrLog';
            $this->_logger = new Logger($logname);
            
        }
        $this->_logLevl=$levl;
        return true;
    }
    
    function logLine($levl,$line) 
    {
        if ($this->_logLevl <= 0) {
            return true;
        }
        if ($levl <= $this->_logLevl) {
            $this->_logger->logLine($line);
        }
        return true;
    }
    
    function getLog() 
    {
        if (is_null($this->_logger)) {
            return false;
        }
        return $this->_logger;
    }
    
    abstract protected function remove() ; 

    abstract protected static function exists($name) ;  

    abstract protected function putMod($model,$meta,$addList,$delList);
    
    abstract protected function newObj($model, $values) ;
    
    abstract protected function newObjId($model, $values, $id) ;

    abstract protected function getObj($model, $id) ;

    abstract protected function putObj($model, $id , $values) ;

    abstract protected function delObj($model, $id) ;
    
    abstract protected function findObj($model, $attr, $val) ;

    abstract protected function findObjWheOp($model,$attrList,$opList,$valList);

};