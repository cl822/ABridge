<?php
namespace ABridge\ABridge\Adm;

use ABridge\ABridge\Mod\Model;

use ABridge\ABridge\Handler;

class Adm
{
    protected static $isNew=false;
    
    public static function init($app, $prm)
    {
        $mod = 'Admin';
        handler::get()->setCmod($mod, 'ABridge\ABridge\Adm\Admin');
    }
    
    public static function begin($app, $prm)
    {
        $mod = 'Admin';
        $obj = new Model($mod);
        $obj->setCriteria([], [], []);
        $res = $obj->select();
        if (count($res)==0) {
            $obj->setVal('Application', $app);
            $obj->setVal('Init', true);
            $obj->save();
        } else {
            $obj = new Model($mod, $res[0]);
        }
        return [$obj->isNew(),$obj];
    }
}