<?php
include 'locallib.php';
$action=$_POST['action'];
switch($action){
	case 'rating':
		echo rating($_POST['userid'],$_POST['id'],$_POST['rating']);
		break;
}

