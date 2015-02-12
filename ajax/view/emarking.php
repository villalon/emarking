<?php
global $CFG;
$lang = $USER->lang;
$parts = explode("_",$lang);
if(count($parts)>1) {
	$lang = $parts[0] .'_'.strtoupper($parts[1]);
}
$langhtml = '<meta name="gwt:property" content="locale='.$lang.'">';

$emarkingdir = $CFG->wwwroot. '/mod/emarking/marking/emarkingweb';
$version = $module->version;

header('Content-Type: text/html; charset=utf-8');

?>
<!doctype html>
<!-- The DOCTYPE declaration above will set the     -->
<!-- browser's rendering engine into                -->
<!-- "Standards Mode". Replacing this declaration   -->
<!-- with a "Quirks Mode" doctype is not supported. -->

<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<?php echo $langhtml ?>
<!--                                                               -->
<!-- Consider inlining CSS to reduce the number of requested files -->
<!--                                                               -->

<!--                                           -->
<!-- Any title is fine                         -->
<!--                                           -->
<title>eMarking 2.0</title>

<!--                                           -->
<!-- This script loads your compiled module.   -->
<!-- If you add any GWT meta tags, they must   -->
<!-- be added before this line.                -->

<script type="text/javascript" language="javascript"
	src="<?php echo $emarkingdir?>/emarkingweb.nocache.js"></script>
</head>

<!--                                           -->
<!-- The body can have arbitrary html, or      -->
<!-- you can leave the body empty if you want  -->
<!-- to create a completely dynamic UI.        -->
<!--                                           -->
<body style="padding-top:0px;">
	<div id="emarking" 
		version="<?php echo $version ?>" 
		submissionId="<?php  echo $ids?>"
		moodleurl="<?php echo $CFG->wwwroot ?>/mod/emarking/ajax/a.php"></div>
</body>
</html>
