<?php
namespace ABridge\ABridge\Mod;

use Exception;
use ABridge\ABridge\Mod\Base;
use ABridge\ABridge\CstError;

class FileBase extends Base
{

    public function __construct($path, $id)
    {
        parent::__construct($path, 'fileBase/'.$id);
    }
    
    public function checkFKey($flag)
    {

        return true;
    }
    
    public function remove()
    {
        parent::erase();
        return $this->close();
    }
    
    public static function exists($path, $id)
    {
        return parent::existsBase($path, 'fileBase/'.$id);
    }
    
    public function putMod($model, $meta, $addList, $delList)
    {
        if (! $this->existsMod($model)) {
            return false;
        }
        $attrLst=[];
        if (isset($delList['attr_lst'])) {
            $attrLst = $delList['attr_lst'];
        }
        foreach ($this->objects[$model] as $id => $list) {
            if ($id) {
                foreach ($attrLst as $attr) {
                    if (isset($list[$attr])) {
                        unset($list[$attr]);
                    }
                }
            }
        }
        $r = parent::putModel($model, $meta);
        return $r;
    }
 
    public function newObj($model, $values)
    {
        return $this->newObjId($model, $values, 0);
    }

    public function newObjId($model, $values, $id)
    {
        if (! $this->isConnected()) {
            throw new Exception(CstError::E_ERC025);
        }
        if (! $this->existsMod($model)) {
            return false;
        }
        $meta=$this->objects[$model][0];
        if (!$id) {
            $id = $meta["lastId"];
            if (!$id) {
                throw new Exception(CstError::E_ERC043.':'.$id);
            }
        }
        if (isset($this->objects[$model][$id])) {
            throw new Exception(CstError::E_ERC043.':'.$id);
        }
        $this->objects[$model][$id] = $values;
        if ($meta["lastId"]) {
            $meta["lastId"]=$id+1;
        }
        $this->objects[$model][0]=$meta;
        $this->logLine(1, "newObj $model $id \n");
        return $id;
    }

    public function getObj($model, $id)
    {
        if (! $this->isConnected()) {
            throw new Exception(CstError::E_ERC025);
        }
        if (! $this->existsMod($model)) {
            return false;
        }
        if ($id == 0) {
            return false;
        }
        if (! array_key_exists($id, $this->objects[$model])) {
            return false;
        }
        $this->logLine(1, "getObj $model $id \n");
        return $this->objects[$model][$id];
    }

    public function putObj($model, $id, $vnum, $values)
    {
        if (! $this->isConnected()) {
            throw new Exception(CstError::E_ERC025);
        }
        if (! $this->existsMod($model)) {
            return false;
        }
        if ($id == 0) {
            return false;
        }
        if (! array_key_exists($id, $this->objects[$model])) {
            return false;
        }
        $this->objects[$model][$id] = $values;
        $this->logLine(1, "putObj $model $id \n");
        return $id; // check -> true
    }

    public function delObj($model, $id)
    {
        if (! $this->isConnected()) {
            throw new Exception(CstError::E_ERC025);
        }
        if (! $this->existsMod($model)) {
            return false;
        }
        if ($id == 0) {
            return true;
        }
        if (! array_key_exists($id, $this->objects[$model])) {
            return true;
        }
        unset($this->objects[$model][$id]);
        $this->logLine(1, "delObj $model $id \n");
        return true;
    }


    public function findObj($model, $attr, $val)
    {
        if (! $this->isConnected()) {
            throw new Exception(CstError::E_ERC025);
        }
        if (! $this->existsMod($model)) {
            return false;
        };
        $result = [];
        foreach ($this->objects[$model] as $id => $list) {
            if ($id) {
                foreach ($list as $a => $v) {
                    if ($attr == $a and $val == $v) {
                        $result[]=$id;
                    }
                }
                if ($attr == 'id' and $id==$val) {
                    $result[]=$id;
                }
            }
        }
        $this->logLine(1, "findObj $model $attr $val  \n");
        return $result;
    }

    public function findObjWheOp($model, $attrList, $opList, $valList)
    {
        if (! $this->existsMod($model)) {
            return false;
        }
        $res= $this->evalWheOp($model, $attrList, $opList, $valList);
        return $res;
    }
    
    private function evalWheOp($model, $attrList, $opList, $valList)
    {
        if ($attrList== []) {
            $result = [];
            foreach ($this->objects[$model] as $id => $list) {
                if ($id) {
                    $result[]=$id;
                };
            }
            return $result;
        }
        $attr= array_pop($attrList);
        $val = array_pop($valList);
        $op = '=';
        if (isset($opList[$attr])) {
            $op = $opList[$attr];
        }
        $res = $this->findObjOp($model, $attr, $op, $val);
        $result = $this->evalWheOp($model, $attrList, $opList, $valList);
        $result = array_intersect($result, $res);
        return $result;
    }
    
    private function findObjOp($model, $attr, $op, $val)
    {
        $result = [];
        foreach ($this->objects[$model] as $id => $list) {
            if ($id) {
                foreach ($list as $a => $v) {
                    if ($attr == $a and $this->evalOp($v, $op, $val)) {
                        $result[]=$id;
                    }
                }
                if ($attr == 'id' and $this->evalOp($id, $op, $val)) {
                    $result[]=$id;
                }
            }
        }
        $this->logLine(1, "findObjOp $model $attr $op $val  \n");
        return $result;
    }
    
    private function evalOp($attrVal, $op, $val)
    {
        switch ($op) {
            case '=':
                if ($attrVal == $val) {
                    return true ;
                }
                break;
            case '>':
                if ($attrVal > $val) {
                    return true ;
                }
                break;
            case '<':
                if ($attrVal < $val) {
                    return true ;
                }
                break;
            case '::':
                if (strpos($attrVal, $val) !== false) {
                    return true;
                }
                break;
        }
        return false;
    }
}