<?php

require_once("UnitTest.php");
$logName = basename(__FILE__, ".php");

$log=new unitTest($logName);

/**************************************/

require_once("Model.php"); 
require_once("View.php"); 

function viewCases()
{
	$x=new Model('test');
	$x->deleteMod();

	$x->addAttr('A1',M_INT);

	$x->addAttr('A2',M_STRING);

	$x->addAttr('A3',M_DATE);

	$x->addAttr('A4',M_TXT);

	$x->setVal('A1',5);

	$x->setVal('A2','aa');

	$x->setVal('A3','2016-12-08');

	$x->setVal('A4','ceci est un texte');

	$v = new View($x);

	$v->setAttrList(['A1','A2'],V_S_REF);
	
	$test = [];
	$n=0;
	$test[$n]=[$v,V_S_READ,$n];	
	$n++;
	$test[$n]=[$v,V_S_UPDT,$n];	
	$n++;
	$test[$n]=[$v,V_S_CREA,$n];	
	$n++;
	$test[$n]=[$v,V_S_DELT,$n];	
	$n++;
	$test[$n]=[$v,V_S_REF,$n];
	$n++;
	$test[$n]=[$v,V_S_CREF,$n];
	
	return $test;
}

$show = false;
$test = viewCases();

for ($i=0;$i<count($test);$i++) {
if ($show) {echo "<br>" ; };
$v = $test[$i][0];
$s = $test[$i][1];
$res = $v->show($s,$show); 
$log->logLine ($res);
}

$log->saveTest();

//$log->showTest();


?>