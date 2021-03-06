<?php
namespace ABridge\ABridge\Mod;

use Exception;
use ABridge\ABridge\Mod\Base;
use ABridge\ABridge\CstError;
use ABridge\ABridge\Log\Log;

// memBase = FileBase with null file 


class FileBase extends Base
{
    protected $logger;
    
    public function __construct($path, $id)
    {
        $fileName = null;
        $this->logger=Log::Get();
        if (! is_null($id)) {
            $fileName = 'fileBase/'.$id;
        }
        parent::__construct($path, $fileName);
    }
    
    public function checkFKey($flag)
    {
        return true;
    }
    
    public function remove()
    {
        parent::removeBase();
        return $this->close();
    }
    
    public static function existsBase($path, $id)
    {
        return parent::existBase($path, 'fileBase/'.$id);
    }
    
    public function getBaseType()
    {
        if ($this->memBase) {
            return 'memBase';
        }
        return 'fileBase';
    }
    
    public function putMod($model, $meta, $addList, $delList, $attrFrg)
    {
        if (! $this->existsMod($model)) {
            return false;
        }
        $attrLst=[];
        if (isset($delList['attr_typ'])) {
            $attrLst = $delList['attr_typ'];
        }
        foreach ($this->objects[$model] as $id => $list) {
            if ($id) {
                foreach ($attrLst as $attr => $typ) {
                    if (isset($list[$attr])) {
                        unset($list[$attr]);
                    }
                }
            }
        }
        $res = parent::putModel($model, $meta);
        parent::commit(); // to align
        return $res;
    }
    
    
    public function delMod($model)
    {
        $r=parent::delMod($model);
        parent::commit(); // to align
        return $r;
    }
    
    public function newModId($model, $meta, $idF, $newList, $attrFrg)
    {
        $res=parent::newModelId($model, $meta, $idF);
        parent::commit(); // to align
        return $res;
    }
    
    
    public function newObj($model, $values)
    {
        return $this->newObjId($model, $values, 0);
    }

    public function newObjId($model, $values, $id)
    {
        if (! $this->existsMod($model)) {
            throw new Exception(CstError::E_ERC022.':'.$model);
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
        $this->logger->logLine("newObj $model $id \n", ['class'=>__CLASS__,'line'=>__LINE__]);
        return $id;
    }

    public function getObj($model, $id)
    {
        if (! $this->existsMod($model)) {
            throw new Exception(CstError::E_ERC022.':'.$model);
        }
        if ($id == 0) {
            return false;
        }
        if (! array_key_exists($id, $this->objects[$model])) {
            return false;
        }
        $this->logger->logLine("getObj $model $id \n", ['class'=>__CLASS__,'line'=>__LINE__]);
        return $this->objects[$model][$id];
    }

    public function putObj($model, $id, $vnum, $values)
    {
        if (! $this->existsMod($model)) {
            throw new Exception(CstError::E_ERC022.':'.$model);
        }
        if ($id == 0) {
            return false;
        }
        if (! array_key_exists($id, $this->objects[$model])) {
            return false;
        }
        $this->objects[$model][$id] = $values;
        $this->logger->logLine("putObj $model $id \n", ['class'=>__CLASS__,'line'=>__LINE__]);
        return $id; // check -> true
    }

    public function delObj($model, $id)
    {
        if (! $this->existsMod($model)) {
            throw new Exception(CstError::E_ERC022.':'.$model);
        }
        if ($id == 0) {
            return true;
        }
        if (! array_key_exists($id, $this->objects[$model])) {
            return true;
        }
        unset($this->objects[$model][$id]);
        $this->logger->logLine("delObj $model $id \n", ['class'=>__CLASS__,'line'=>__LINE__]);
        return true;
    }


    public function findObj($model, $attr, $val)
    {
        if (! $this->existsMod($model)) {
            throw new Exception(CstError::E_ERC022.':'.$model);
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
        $this->logger->logLine("findObj $model $attr $val  \n", ['class'=>__CLASS__,'line'=>__LINE__]);
        return $result;
    }

    public function findObjWheOp($model, $attrList, $opList, $valList, $ordList)
    {
        $modList=[$model];
        if (is_array($model)) {
            $modList=$model;
        }
        $sortList=[];
        if (is_array($model) and $ordList==[]) {
            $ordList=[['id',false]];
        }
        foreach ($modList as $mod) {
            $res=$this->execFind($mod, $attrList, $opList, $valList, $ordList);
            $sortList= $sortList+$res;
        }
        if ($ordList == []) {
            return $sortList;
        }
        $desc = $ordList[0][1];
        if ($desc) {
            arsort($sortList);
            return array_keys($sortList);
        }
        asort($sortList);
        return array_keys($sortList);
    }
    
    private function execFind($model, $attrList, $opList, $valList, $ordList)
    {
        if (! $this->existsMod($model)) {
            throw new Exception(CstError::E_ERC022.':'.$model);
        }
        $res= $this->evalWheOp($model, $attrList, $opList, $valList);
        if ($ordList == []) {
            return $res;
        }
        $sortAttr=$ordList[0][0];
        return $this->buildOrderList($model, $res, $sortAttr);
    }
        
    private function buildOrderList($model, $list, $attr)
    {
        $sortList=[];
        foreach ($list as $id) {
            $sortVal=$id;
            if ($attr != 'id') {
                $sortVal=$this->objects[$model][$id][$attr];
            }
            $sortList[$id]=$sortVal;
        }
        return $sortList;
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
        $opr = '=';
        if (isset($opList[$attr])) {
            $opr = $opList[$attr];
        }
        $res = $this->findObjOp($model, $attr, $opr, $val);
        $result = $this->evalWheOp($model, $attrList, $opList, $valList);
        $result = array_intersect($result, $res);
        return $result;
    }
    
    private function findObjOp($model, $attr, $opr, $val)
    {
        $result = [];
        foreach ($this->objects[$model] as $id => $list) {
            if ($id) {
                foreach ($list as $a => $v) {
                    if ($attr == $a and $this->evalOp($v, $opr, $val)) {
                        $result[]=$id;
                    }
                }
                if ($attr == 'id' and $this->evalOp($id, $opr, $val)) {
                    $result[]=$id;
                }
            }
        }
        $this->logger->logLine("findObjOp $model $attr $opr $val  \n", ['class'=>__CLASS__,'line'=>__LINE__]);
        return $result;
    }
    
    private function evalOp($attrVal, $opr, $val)
    {
        switch ($opr) {
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
