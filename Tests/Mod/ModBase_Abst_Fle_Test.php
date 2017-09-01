<?php
    
use ABridge\ABridge\Mod\FileBase;

require_once("ModBase_Abst_Case.php");

class ModBase_Abst_Fle_Test extends ModBase_Abst_Case
{

    public static function setUpBeforeClass()
    {
        self::$CName=get_called_class().'_f_1';
        self::$HName=get_called_class().'_f_2';
        self::$DBName= 'atest';
        $prm=[
                'path'=>'C:/Users/pierr/ABridge/Datastore/',
                'host'=>'localhost',
                'user'=>'cl822',
                'pass'=>'cl822'
        ];
        self::$db = new FileBase($prm['path'], self::$DBName);
    }
}