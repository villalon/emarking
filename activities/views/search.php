<!DOCTYPE html>
<?php
require_once (dirname(dirname(dirname ( dirname ( dirname ( __FILE__ ) ) ) )). '/config.php');
GLOBAL $USER, $CFG;
require_once ($CFG->dirroot. '/mod/emarking/activities/generos.php');
$teacherroleid = 3;
$logged = false;

if (isloggedin ()) {
	$logged = true;
	$courses = enrol_get_all_users_courses ( $USER->id );
	$countcourses = count ( $courses );
	foreach ( $courses as $course ) {
		$context = context_course::instance ( $course->id );
		$roles = get_user_roles ( $context, $USER->id, true );
		foreach ( $roles as $rol ) {
			if ($rol->roleid == $teacherroleid) {
				$asteachercourses [$course->id] = $course->fullname;
			}
		}
	}
}
 
include 'header.php'; 
include_once '../forms/search.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST'){

	switch ($_POST['type']){
	case 1:
		$search=$_POST['search'];
		$sql="SELECT *
			  FROM {emarking_activities}
			  WHERE parent IS NULL AND 
			  		(title like '%$search%' OR 
					description like '%$search%' OR
					audience like '%$search%' OR
					instructions like '%$search%' OR
					teaching like '%$search%' OR
					languageresources like '%$search%')";
		$results = $DB->get_records_sql($sql);
		break;
	case 2;
	
		break;
	case 3:
		$results=$DB->get_records('emarking_activities',array('comunicativepurpose'=>$_POST['pc'],'parent'=>null));
		break;
	case 4:
		$results=$DB->get_records('emarking_activities',array('genre'=>$_POST['genero'],'parent'=>null));
		
		break;
	}
	
include 'results.php';
}
include 'footer.php'; 
 
 ?>
