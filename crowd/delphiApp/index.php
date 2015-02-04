<?php
header('Content-Type: text/html; charset=UTF-8');
include '../../../../config.php';
global $CFG,$USER;

$cmid = required_param('cmid',PARAM_INT);
$ids = required_param('ids',PARAM_INT);
$cm = get_coursemodule_from_id('emarking',$cmid);
if(!$cm){
    throw new Exception("Invalid cm");
}

include $CFG->dirroot.'/mod/emarking/crowd/crowdlib.php';
$context = context_module::instance($cm->id);
$crowdmod = new emarking_crowd($cm,$context);
$icanmarkhere=true;
$delphiactive=false;
if($crowdmod->is_active()){
    $delphiactive=true;
    if(!$crowdmod->is_mine()){
        $icanmarkhere=false;
    }
    $mysub = $crowdmod->get_my_correct_submissionid($ids);
}
include_once $CFG->dirroot.'/mod/emarking/crowd/vendor/FirebaseToken.php';
//Esto es temporal para el experimento de la Prueba 1
$secret = isset($CFG->emarking_crowdexperiment_rtm_secret)?$CFG->emarking_crowdexperiment_rtm_secret:"";
$appid = isset($CFG->emarking_crowdexperiment_rtm_appid)?$CFG->emarking_crowdexperiment_rtm_appid:"";
$rtmtoken=null;
$loggedIn = false;
if(isloggedin()){
    $loggedIn=true;

    if(strlen($secret)>5&&strlen($appid)>5){
        $expire = 0;
        if(isset($_SESSION[$USER->sesskey . "rtmtoken".$crowdmod->get_parent_id()])){
            if(isset($_SESSION[$USER->sesskey . "rtmexpire".$crowdmod->get_parent_id()])){
                $expire= $_SESSION[$USER->sesskey . "rtmexpire".$crowdmod->get_parent_id()];
            }
            if($expire>(time()+1*3600)){ //If we have 1 hours left or more
                $rtmtoken=$_SESSION[$USER->sesskey . "rtmtoken".$crowdmod->get_parent_id()];
            }
        }
        if(!$rtmtoken){
            $tokenGen = new Services_FirebaseTokenGenerator($secret);
            $rtmtoken = $tokenGen->createToken(array(
                "app_id"=>$appid,
                "user_id" => $USER->id,
                "processid"=>$crowdmod->get_parent_id(),"user_name"=>$USER->firstname." ".$USER->lastname
            ));
            $expire=time()+24*3600;
            $_SESSION[$USER->sesskey . "rtmtoken".$crowdmod->get_parent_id()]=$rtmtoken;
            $_SESSION[$USER->sesskey . "rtmexpire".$crowdmod->get_parent_id()]=$expire;
        }
    }
}else{
    echo '<html><body><div>Por favor <a href="'.$CFG->wwwroot.'/login/index.php">Ingresar</a> antes de usar esta aplicación</div></body></html>';
    die();
}

//.. if not we create our front end.
?>
<!DOCTYPE html>
<html ng-app="delphiApp" ng-init="initConfig(<?php echo $cmid;?>,<?php echo $ids;?>,'../../../..')">
<head>
    <script>window.wwwroot='../../../..';</script>
    <script src="https://cdn.firebase.com/js/client/1.0.11/firebase.js"></script>
    <script type="text/javascript" language="javascript" src="../assets/angular.min.js"></script>
    <script type="text/javascript" language="javascript" src="../assets/angular-route.min.js"></script>
    <script src="https://cdn.firebase.com/libs/angularfire/0.7.1/angularfire.min.js"></script>
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" language="javascript" src="../assets/ui-bootstrap-tpls-0.10.0.min.js"></script>
    <script type="text/javascript" language="javascript" src="../assets/angular-animate.min.js"></script>
    <script type="text/javascript" language="javascript" src="../assets/ng-grid.min.js"></script>
    <script type="text/javascript" language="javascript" src="js/app.js"></script>
    <script type="text/javascript" language="javascript" src="js/controllers.js"></script>
    <script type="text/javascript" language="javascript" src="js/directives.js"></script>
    <script type="text/javascript" language="javascript" src="js/filters.js"></script>
    <script type="text/javascript" language="javascript" src="js/services.js"></script>
    <link rel="stylesheet" type="text/css" href="../assets/bootstrap.min.css">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/ng-grid.min.css">
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/app.css">
</head>
<body ng-controller="MainController" >

    <script>
        window.rtmtoken='<?php echo $rtmtoken;?>';
    </script>


    <div class="navbar navbar-inverse">
    <div class="navbar-brand">
        Delphi
    </div>
    </div>
    <div ng-view style="position:relative;height:80%;width:100%;">
    </div>
</body>
</html>