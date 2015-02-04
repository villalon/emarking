<?php

class emarking_itemrepo {
    /**
     * @var $cm stdClass
     */
    private $cm = null;
    public function __construct(stdClass $cm){

        $this->cm =  $cm;
    }

    public function findAllWithoutTask()
    {

        global $DB;
        $sql = "
            SELECT s.*,t.studentid
            FROM {emarking_submission} as s
            LEFT JOIN {emarking_task} as t
              ON t.studentid = s.student
              AND t.masteractivity = s.emarking
            WHERE s.emarking = :emarkingid AND t.studentid IS NULL
        ";
        $records = $DB->get_records_sql($sql,array("emarkingid"=>$this->cm->instance));
        $items = array();
        foreach($records as $record){
            $item = new stdClass();
            $item->id = $record->student;
            $items[] = $item;
        }
        return $items;
    }
}
class emarking_markertaskrepo {
    /**
     * This is the cm where this markertask repo works.
     * It is usually initialized to the parent one.
     * @var stdClass
     */
    private $cm = null;
    public function __construct(stdClass $cm){

        $this->cm =  $cm;
        if(!$cm){
            throw new Exception("Incorrect cm provided in code");
        }
    }
    function findById($id)
    {
        global $DB;
        $record = $DB->get_record('local_emarking_marker_task',array("id"=>$id));
        $item = new stdClass();
        $item->id = $record->itemid;
        $marker = new stdClass();
        $marker->id = $record->id;
        $markerTask = new stdClass();
        $markerTask->marker = $marker;
        $markerTask->item = $item;
        $markerTask->stage = $record->stage;
        return $markerTask;
    }

    public function findPendingForMarker(stdClass $marker)
    {
        global $DB;
        $sql = ' SELECT t.*, m.activityid ,s.id as ids
            FROM mdl_emarking_task as t
            JOIN mdl_emarking_markers as m
            ON t.masteractivity = m.masteractivity AND m.markerid = t.markerid  AND m.activityid!=m.masteractivity
            JOIN mdl_emarking_submission as s
            ON s.emarking = m.activityid AND t.studentid = s.student
            WHERE t.masteractivity = :masteractivity AND t.markerid= :markerid AND t.stage = :stage
        ';
        $records = $DB->get_records_sql($sql,array("masteractivity"=>$this->cm->instance,"markerid"=>$marker->id,"stage"=>"0"));

        $markerTasks = array();
        foreach($records as $record){

            $item = new stdClass();
            $item->id = $record->studentid;
            $item->ids = $record->ids;
            $markerTask = new stdClass();
            $markerTask->marker = $marker;
            $markerTask->item = $item;
            $markerTask->stage =$record->stage;
            $markerTasks[] = $markerTask;
        }
        return $markerTasks;
    }

    public function persist(stdClass $task)
    {
        global $DB;
        $record = new stdClass();
        $record->masteractivity = $this->cm->instance;
        $record->markerid = $task->marker->id;
        $record->studentid = $task->item->id;
        $record->stage = $task->stage?$task->stage:0;
        if($id = $task->id){
            $record->id = $id;
            $DB->update_record('emarking_task',$record);
        }else{
            $id = $DB->insert_record('emarking_task',$record);

            $task->id = $id;
        }
    }

    public function findDebatesForMarkerid($id,$markermoduleid)
    {
        global $DB,$USER;
        /*
        $sql ="
            SELECT CONCAT(s.student,',',rc.description) as ikey,GROUP_CONCAT(us.username),GROUP_CONCAT(c.markerid) as mrk,GROUP_CONCAT(um.username) as mrk2,rc.description,COUNT(rc.id) as voters,AVG(c.bonus+rl.score) as groupscore,SUM(mycomment.bonus+rl.score) as myscore
            FROM {emarking_comment} c
            JOIN {emarking_page} p
            ON p.id = c.page
            JOIN {emarking_submission} s
            ON s.id = p.submission
            JOIN {emarking_markers} m
            ON s.emarking = m.activityid
            JOIN {gradingform_rubric_levels} rl
            ON rl.id = c.levelid
            JOIN {gradingform_rubric_criteria} rc
            ON rl.criterionid = rc.id
            JOIN {user} us
            ON us.id = s.student
            JOIN {user} um
            ON um.id = c.markerid
            left Join {emarking_comment} mycomment
            ON mycomment.markerid = :markerid AND c.id = mycomment.id
            WHERE c.textformat=2 AND m.masteractivity= :masterid
            group by rc.description,s.student
            HAVING COUNT(rc.id)>=2
            ORDER BY ikey
        ";
        */
        $sql ="
            SELECT CONCAT(s.student,',',rc.description) as ikey,CONCAT(s.student,',',rc.id) as numkey,GROUP_CONCAT(us.username) as usernames,GROUP_CONCAT(s.id) as subs,
                  MIN(mysub.id) as mysub,max(mysub.student) as student,GROUP_CONCAT(c.markerid) as mrk,
                  GROUP_CONCAT(um.username) as mrk2,rc.description,rc.id as critid,COUNT(rc.id) as voters,
                  AVG(theircomment.bonus+rl.score) as groupscore,SUM(mycomment.bonus+rl.score) as myscore,
                  GROUP_CONCAT(tm.id) as theirlogs,sum(tm.hasvotes) as theyhavevotes,max(tm.lastargumentchange) as theirlastargumentchange,
                  max(tm.lastvote) as theirlastvote,max(tm.timehidden) as theirtimehidden,
                  GROUP_CONCAT(mytm.id) as mylogs,max(mytm.hasvotes) as ihavevotes,max(mytm.lastargumentchange) as mylastargumentchange,
                  max(mytm.lastvote) as mylastvote,max(mytm.timehidden) as mytimehidden
            FROM {emarking_comment} c
            JOIN {emarking_page} p
            ON p.id = c.page
            JOIN {emarking_submission} s
            ON s.id = p.submission
            JOIN {emarking_markers} m
            ON s.emarking = m.activityid  AND m.activityid!=m.masteractivity
            JOIN {gradingform_rubric_levels} rl
            ON rl.id = c.levelid
            JOIN {gradingform_rubric_criteria} rc
            ON rl.criterionid = rc.id
            JOIN {user} us
            ON us.id = s.student
            JOIN {user} um
            ON um.id = c.markerid
            left Join {emarking_comment} mycomment
            ON mycomment.markerid = :markerid AND c.id = mycomment.id
            left Join {emarking_comment} theircomment
            ON theircomment.markerid != :markerid4 AND c.id = theircomment.id
            left JOIN {emarking_submission} mysub
            ON mysub.emarking=:markermoduleid AND s.id=mysub.id
            left join {emarking_debate_timings} tm
            ON tm.parentcm=m.masteractivity AND tm.studentid = s.student AND tm.criteriondesc = rc.description AND tm.markerid = c.markerid AND tm.markerid != :markerid2
            left join {emarking_debate_timings} mytm
            ON mytm.parentcm=m.masteractivity AND mytm.studentid = s.student AND mytm.criteriondesc = rc.description AND mytm.markerid = c.markerid AND mytm.markerid = :markerid3

            WHERE c.textformat=2 AND m.masteractivity= :masterid
            group by rc.description,s.student
            HAVING COUNT(rc.id)>=2
            ORDER BY numkey
        ";
        $records = $DB->get_records_sql($sql,array("markerid"=>$id,"markerid2"=>$id,"markerid3"=>$id,"markerid4"=>$id,"masterid"=>$this->cm->instance,"markermoduleid"=>$markermoduleid));





        $result = array();
        foreach($records as $key => $record){
           //we split the marker
            $markerarray = preg_split("/,/",$record->mrk);

            foreach($markerarray as $sp){//we filter our debates.
                //$record->groupscore /= ($record->voters-1);
                if($sp===$USER->id){
                    $result[$key] = $record;
                    break;
                }
            }

            $record->alerts = $this->computeAlerts($record);

        }

        return $result;
    }

    /**
     * This function computes alerts for a given record, which
     *  are based on the tm and mytm data (debate timings for "others" and "current marker"
     * The function expects a record with following attributes:
     *      theirlogs, (a concat of all the log ids of others)
     *      theyhavevotes (sum of vote flags of others, 3 means 3 other people have issued votes)
     *      theirlastargumentchange (last time other person changed an argument)
     *      theirlastvote (last time other person changed a vote)
     *      theirtimehidden  (last time other person hid this to receive no more alerts)
     *  mylogs (concatid,should be one id of the timing record for this context (emarkingid-marker-student-critid)),
     *      ihavevotes (sum of votes flag, should be either 1 or 0),
     *      mylastargumentchange (last time I changed my argument)
     *      mylastvote (last time y changed or deleted a vote)
     *      mytimehidden (the time when I hid this)
     *
     * @param $record
     * @return array
     */
    private function computeAlerts($record)
    {
        $alerts = array();
        $mylastchange = max(array($record->mylastvote,$record->mylastargumentchange,$record->mytimehidden));
        if($record->myscore!=$record->groupscore&&!$record->ihavevotes){
            $alerts[]="noarg";
            //we return instantly as this is a blocking alert.
            return $alerts;
        }

        if($record->mytimehidden>0){
            //The marker said he doesn't want more alerts (DISABLED)
            //return array();
        }

        if($record->theirlastvote>$mylastchange){
            $alerts[] = "newvotes";
        }

        if($record->theirlastargumentchange>$mylastchange){
            $alerts[] = "newargs";
        }
        if(count($alerts)==0&&$record->myscore!=$record->groupscore){
            $alerts[] = "noagreement";
        }
        return $alerts;

    }
}

class emarking_markerrepo {

    private $cm = null;
    public function __construct(stdClass $cm){

        $this->cm =  $cm;
    }

    function findById($id)
    {
        global $DB;
        //$record = $DB->get_record('user',array("id"=>$id));
        $marker = new stdClass();
        $marker->id = $id;
        return $marker;
    }



    function findAll()
    {
        $context = context_module::instance($this->cm->id);
        $users =  get_users_by_capability($context, 'mod/assign:grade');
        $markers = array();
        foreach($users as $user){
            $marker = new stdClass();
            $marker->id = $user->id;
            $marker->name = $user->firstname." ".$user->lastname;
            $markers[] = $marker;
        }
        return $markers;
    }
}

class emarking_crowdconfig{

    private $cm = null;
    public function __construct(stdClass $cm){

        $this->instanceid =  $cm;
    }

    public function getOverlap()
    {
        return 0;
    }

    public function getNewBatchSize()
    {
        return 5;
    }

    public function getMarkerQuota()
    {
        global $DB;
        $instanceid = $this->cm->instance;
        /* TODO : USE THIS FOR INSIPRATION
        $DB->get_record_sql("SELECT 1, SUM(bonus) as total,
			FROM {assignment_emarking_comment}
			WHERE assignment_submission = ?",
            array($submissionid))
        */
        return 30;
    }
}

class emarking_crowd {
    private $cm;
    private $context;
    private $activities=array();
    private $isparent=false;
    private $active=false;
    private $parent;
    private $parentcm;
    private $mycm;
    private $continueUrl; //URL to continue to in case of an error
    public function __construct(stdClass $cm, context_module $context){

        global $DB,$USER,$CFG;

        $this->cm =$cm;
        $this->context = $context;
        $this->parent = $this->get_parent();
        $this->canmanage = has_capability('mod/emarking:manageanonymousmarking',$this->context);
        $this->cangrade = has_capability('mod/emarking:grade',$this->context);
        if($this->parent){
            $this->active=true;
            if($this->parent->masteractivity == $this->cm->instance){
                $this->isparent=true;
            }
            $this->activities = $DB->get_records('emarking_markers',array('masteractivity'=>$this->parent->masteractivity));
            $myactivity = $DB->get_record('emarking_markers',array('masteractivity'=>$this->parent->masteractivity,"markerid"=>$USER->id));
            $this->parentcm = get_coursemodule_from_instance('emarking',$this->parent->masteractivity);
            if($myactivity){
                $this->mycm = get_coursemodule_from_instance('emarking',$myactivity->activityid);
            }

            //Inject moodle dependencies to task service
            $this->markerRepo = new emarking_markerrepo($this->parentcm);
            $this->taskRepo = new emarking_markertaskrepo($this->parentcm);
            $this->itemRepo = new emarking_itemrepo($this->parentcm);
            $this->config = new emarking_crowdconfig($this->parentcm);

            if($this->mycm){
            }elseif($this->canmanage){
                //$this->mycm = $this->parentcm;
            }
        }
        if(!$this->cangrade){
            $this->active=false;
        }
        $this->continueUrl = $CFG->wwwroot."/mod/emarking/crowd/marking.php?cmid=".$this->cm->id;

    }
    public function view($action){
        global $USER,$CFG,$OUTPUT;
        $o= "";
        if($action=="createfakepages"){
            if(!debugging()){
                return "You cant do this unless server is on debug mode!";
            }
            if($_SERVER['REQUEST_METHOD']!="POST"){//.. if he has not confirmed, we ask him to confirm or cancel
                $href = $CFG->wwwroot."/mod/emarking/crowd/marking.php?act=createfakepages&cmid=".$this->cm->id;
                $hrefcancel = $CFG->wwwroot."/mod/emarking/crowd/marking.php?cmid=".$this->cm->id;
                $button = '<form action="'.$href.'" method="POST"><input class="btn" type="submit" value="Confirm"/>  </form>';
                $cancelbutton = "<a class=\"btn\"href=\"".$hrefcancel."\">CANCEL</a>";
                $o.="You are about to generate or replace 2 first pages of each student with fake ones.".$button.$cancelbutton."";
                return $o;
            }else{

                $hrefcancel = $CFG->wwwroot."/mod/emarking/crowd/marking.php?cmid=".$this->cm->id;
                $this->fill_with_magic();
                $cancelbutton = "<a href=\"".$hrefcancel."\">CONTINUE</a>";
                $o.="Process finished.<ul><li>".$cancelbutton."</li></ul><br>";
                return $o;
            }
        }
        /*
         * First we check special states in which this module is disabled
         */
        if(!$this->active){         //If this is not an active crowd activity,
            if($this->cangrade){

                if($action!="promote"){ //...and we are not in the promotion process (promote = make it active)
                                        //, then we warn the user telling him to promote the crowd activity to continue.

                    $href = $CFG->wwwroot."/mod/emarking/crowd/marking.php?act=promote&cmid=".$this->cm->id;
                    ob_start();
                    ?>
                    <div class="row-fluid">
                        <div class="span7" style="float: none;margin: 0 auto;">
                            <form class="form form-inline" action="<?php echo $href;?>" method="POST">
                                <h3>Delphi is disabled</h3>
                                <p>Emarking Delphi is an experimental feature to help markers agree on students' grades</p>
                                 <?php if(has_capability('mod/emarking:activatedelphiprocess',$this->context)) {?>
                                    <input type="submit" value="Activate">
                                <?php }else{ ?>
                                    <p>Contact the administrator for information on how to activate it.</p>
                                <?php } ?>
                            </form>
                        </div>
                    </div>
                    <?php

                    $o.=ob_get_clean();
                    /*
                    $button = "<a href=\"".$href."\">Make this a new crowd master</a>(cant be undone).Will generate 1 module for each marker and redirect automagically.";
                    $o.="Not crowd, you have three options:<ul><li>".$button."</li><li>Link to a parent(Not implemented yet)</li>";
                    $o .= '<li><a href="'.$CFG->wwwroot."/mod/emarking/crowd/marking.php?act=createfakepages&cmid=".$this->cm->id.'">Click here</a> to create pages or replace 1 and 2 for all students with dummy test objects</li>';
                    $o .="</ul><br>";
                    */
                    return $o;

                }else{//...and the user is trying to deploy the crowd activity,
                    if(!has_capability('mod/emarking:activatedelphiprocess',$this->context)) {
                        return "Please request an administrator to activate the delphi process.";
                    }

                    if($_SERVER['REQUEST_METHOD']!="POST"){//.. if he has not confirmed, we ask him to confirm or cancel
                        $href = $CFG->wwwroot."/mod/emarking/crowd/marking.php?act=promote&cmid=".$this->cm->id;
                        $hrefcancel = $CFG->wwwroot."/mod/emarking/crowd/marking.php?cmid=".$this->cm->id;
                        $button = '<form action="'.$href.'" method="POST"><input class="btn" type="submit" value="Confirm"/>  </form>';
                        $cancelbutton = "<a class=\"btn\"href=\"".$hrefcancel."\">CANCEL</a>";
                        $o.="You are about to generate 1 module for each marker (cant be undone) .".$button.$cancelbutton."";
                        return $o;
                    }else{

                    $hrefcancel = $CFG->wwwroot."/mod/emarking/crowd/marking.php?cmid=".$this->cm->id;
                    $this->make_master();
                    $cancelbutton = "<a href=\"".$hrefcancel."\">CONTINUE</a>";
                    $o.="Process finished.<ul><li>".$cancelbutton."</li></ul><br>";
                    return $o;
                    }


                }
            }

            if(!$this->cangrade){
                return "_";
            }
        }else{ //Its active
            if(!$this->cangrade){
                return "-";
            }
        }

        if($action == "includemarker"){
            $markerid = required_param("markerid",PARAM_INT);
            if($_SERVER["REQUEST_METHOD"]!="POST"){
                $href = $CFG->wwwroot."/mod/emarking/crowd/marking.php?act=includemarker&cmid=".$this->cm->id;
                $hrefcancel = $CFG->wwwroot."/mod/emarking/crowd/marking.php?cmid=".$this->cm->id;
                $button = '<form action="'.$href.'" method="POST"><input type="hidden" name="markerid" value="'.$markerid.'"><input class="btn" type="submit" value="Confirm"/>  </form>';
                $cancelbutton = "<a class=\"btn\"href=\"".$hrefcancel."\">CANCEL</a>";
                $o.="You are about to generate 1 module for marker $markerid .".$button.$cancelbutton."";
                return $o;
            }
            $this->create_marker_module($markerid);
            $hrefcancel = $CFG->wwwroot."/mod/emarking/crowd/marking.php?cmid=".$this->cm->id;
            $cancelbutton = "<a href=\"".$hrefcancel."\">CONTINUE</a>";
            $o.="Process finished.<ul><li>".$cancelbutton."</li></ul><br>";
            return $o;
        }
        if($action == "includeallmarkers"){
            if($_SERVER['REQUEST_METHOD']!="POST"){//.. if he has not confirmed, we ask him to confirm or cancel
                $href = $CFG->wwwroot."/mod/emarking/crowd/marking.php?act=includeallmarkers&cmid=".$this->cm->id;
                $hrefcancel = $CFG->wwwroot."/mod/emarking/crowd/marking.php?cmid=".$this->cm->id;
                $button = '<form action="'.$href.'" method="POST"><input class="btn" type="submit" value="Confirm"/>  </form>';
                $cancelbutton = "<a class=\"btn\"href=\"".$hrefcancel."\">CANCEL</a>";
                $o.="You are about to generate 1 module for each marker .".$button.$cancelbutton."";
                return $o;
            }else{

                $hrefcancel = $CFG->wwwroot."/mod/emarking/crowd/marking.php?cmid=".$this->cm->id;
                $this->make_create_for_all();
                $cancelbutton = "<a href=\"".$hrefcancel."\">CONTINUE</a>";
                $o.="Process finished.<ul><li>".$cancelbutton."</li></ul><br>";
                return $o;
            }
        }

        //If the module is active and we can grade we act normally.


        global $PAGE;
        $PAGE->requires->js(new moodle_url('/mod/emarking/crowd/assets/crowd.js'));
        $PAGE->requires->css(new moodle_url('/mod/emarking/crowd/assets/crowd.css'));
        $PAGE->requires->css(new moodle_url('/mod/emarking/crowd/assets/font-awesome.min.css'));


        ini_set('xdebug.var_display_max_depth', 50);
        //var_dump($data);
        //$this->canmanage=true;
        ob_start();
        $PAGE->requires->jquery();
        $PAGE->requires->js(new moodle_url('/mod/emarking/crowd/assets/angular.min.js'));
        $PAGE->requires->js(new moodle_url('/mod/emarking/crowd/assets/angular-animate.min.js'));
        $PAGE->requires->js(new moodle_url('/mod/emarking/crowd/assets/angular-route.min.js'));
        $PAGE->requires->js(new moodle_url('/mod/emarking/crowd/assets/ng-grid.min.js'));
        $PAGE->requires->js(new moodle_url('/mod/emarking/crowd/assets/ui-bootstrap-tpls-0.10.0.min.js'));
        $PAGE->requires->js(new moodle_url('/mod/emarking/crowd/delphiApp/js/app.js'));
        $PAGE->requires->js(new moodle_url('/mod/emarking/crowd/delphiApp/js/controllers.js'));
        $PAGE->requires->js(new moodle_url('/mod/emarking/crowd/delphiApp/js/directives.js'));
        $PAGE->requires->js(new moodle_url('/mod/emarking/crowd/delphiApp/js/filters.js'));
        $PAGE->requires->js(new moodle_url('/mod/emarking/crowd/delphiApp/js/services.js'));
        $PAGE->requires->css(new moodle_url('/mod/emarking/crowd/assets/font-awesome.min.css'));
        $PAGE->requires->css(new moodle_url('/mod/emarking/crowd/assets/ng-grid.min.css'));
        $PAGE->requires->css(new moodle_url('/mod/emarking/crowd/delphiApp/css/app.css'));
        if($this->mycm){

        ?> <script>window.wwwroot='../../..';</script><div style="overflow-y:auto;">
        <div class="delphiembed" ng-app="embedApp" ng-init="initConfig(<?php echo $this->mycm->id;?>,<?php echo 0;?>,'../../..')">
            <div ng-view></div>
        </div>
        </div>
        <?php

        }
        if($this->canmanage){

            if(debugging()){
                echo $this->render_debug_tab();
            }
            if(has_capability('mod/emarking:configuredelphiprocess',$this->context)){
                echo $this->create_configuration_tab();
            }
            echo $this->create_dashboard_student();
        }

        $o.=ob_get_clean();

        return $o;

    }

    private function get_parent()
    {
        global $DB;
        $markermap = $DB->get_record('emarking_markers',array('activityid'=>$this->cm->instance));
        if($markermap){
            return $markermap;
        }
        return null;
    }
    public function get_parent_id(){
        $parentactivity= $this->parentcm->instance;
        return $parentactivity;
    }
    public function ajax($action)
    {
        global $DB,$USER;

        $ret = array("error"=>"Not an action");
        switch($action){
            case "newargument":
                if($_SERVER['REQUEST_METHOD']!="POST"){
                    $ret["error"]= "Method not allowed";
                    break;
                }
                $argument = required_param("argument",PARAM_TEXT);
                $levelid = required_param("levelid",PARAM_INT);
                $bonus = required_param("bonus",PARAM_FLOAT);
                $studentid = required_param("studentid",PARAM_INT);
                if(strlen($argument)<2){
                    $ret["error"]= "Argument invalid";
                    break;
                }
                $newarg = new stdClass();
                $newarg->markerid = $USER->id;
                $newarg->text = $argument;
                $newarg->levelid = $levelid;
                $newarg->bonus = $bonus;
                $newarg->studentid =$studentid;
                $transaction = $DB->start_delegated_transaction();
                try{
                    $id=$DB->insert_record('emarking_arguments',$newarg);
                    $newvote = new stdClass();
                    $newvote->markerid = $USER->id;
                    $newvote->argumentid=$id;
                    $DB->insert_record('emarking_argument_votes',$newvote);


                    $parentactivity= $this->parentcm->instance;
                    $criteriondesc = $this->get_criteria_description_for_levelid($levelid);
                    $markerid = $USER->id;
                    $timing = new stdClass();
                    $timing->parentcm=$parentactivity;
                    $timing->studentid=$studentid;
                    $timing->criteriondesc=$criteriondesc;
                    $timing->markerid = $markerid;

                    $timing->hasvotes=1;
                    $timing->lastargumentchange=time();
                    $timing->lastvote=time();
                    $this->create_or_update_timing($timing);




                    $transaction->allow_commit();
                }catch(Exception $e){
                    $transaction->rollback($e);
                    $ret = array("error"=>"Houston tenemos un problema");
                    break;
                }




                $ret = array("msg"=>"Exitooo");
            break;
            case "detail":
                $studentid = required_param("stdid",PARAM_INT);
                $critdesc  = required_param("critd",PARAM_TEXT);

                $ret=$this->wip_debate_screen($studentid,$critdesc,true);

            break;
            case "agree":
                $argumentid = required_param("argumentid",PARAM_INT);
                if(!$arg = $DB->get_record('emarking_arguments',array("id"=>$argumentid))){
                    $ret = array("error"=>"Invalid argument id");
                    break;
                }

                if($vote=$DB->get_record('emarking_argument_votes',array("argumentid"=>$argumentid,"markerid"=>$USER->id))){
                    $ret = array("msg"=>"Already agreeing but its ok");
                }else{
                    $insertable= new stdClass();
                    $insertable->argumentid=$argumentid;
                    $insertable->markerid = $USER->id;
                    $vote=$DB->insert_record('emarking_argument_votes',$insertable);
                    $ret = array("msg"=>"Inserted!");
                }


                $parentactivity= $this->parentcm->instance;
                $criteriondesc = $this->get_criteria_description_for_argument($argumentid);
                $markerid = $USER->id;
                $studentid = $arg->studentid;
                $timing = new stdClass();
                $timing->parentcm=$parentactivity;
                $timing->studentid=$studentid;
                $timing->criteriondesc=$criteriondesc;
                $timing->markerid = $markerid;

                $timing->hasvotes=1;
                $timing->lastvote=time();
                $this->create_or_update_timing($timing);

            break;
            case "disagree":
                $argumentid = required_param("argumentid",PARAM_INT);

                if(!$arg = $DB->get_record('emarking_arguments',array("id"=>$argumentid))){
                    $ret = array("error"=>"Invalid argument id");
                    break;
                }
                $parentactivity= $this->parentcm->instance;
                $criteriondesc = $this->get_criteria_description_for_argument($argumentid);
                $markerid = $USER->id;
                $studentid = $arg->studentid;
                $timing = new stdClass();
                $timing->parentcm=$parentactivity;
                $timing->studentid=$studentid;
                $timing->criteriondesc=$criteriondesc;
                $timing->markerid = $markerid;

                $timing->hasvotes=$this->has_votes($studentid,$criteriondesc)?1:0;
                $timing->lastvote=time();

                if($vote=$DB->get_record('emarking_argument_votes',array("argumentid"=>$argumentid,"markerid"=>$USER->id))){
                    $DB->delete_records('emarking_argument_votes',array("argumentid"=>$argumentid,"markerid"=>$USER->id));
                    $ret = array("msg"=>"Deleted!");

                }else{
                    $ret = array("msg"=>"Already disagreed but its ok");
                }


                $this->create_or_update_timing($timing);


            break;
            case "deleteArgument":
                $argumentid = required_param("argumentid",PARAM_INT);
                if($votes=$DB->get_records('emarking_argument_votes',array("argumentid"=>$argumentid))){
                    $ret = array("msg"=>"Cannot delete if it has votes!");
                }else{
                    if($arg=$DB->get_record('emarking_arguments',array("id"=>$argumentid))){
                        if($arg->markerid==$USER->id){



                            $parentactivity= $this->parentcm->instance;
                            $criteriondesc = $this->get_criteria_description_for_argument($argumentid);
                            $markerid = $USER->id;
                            $studentid = $arg->studentid;
                            $timing = new stdClass();
                            $timing->parentcm=$parentactivity;
                            $timing->studentid=$studentid;
                            $timing->criteriondesc=$criteriondesc;
                            $timing->markerid = $markerid;

                            $timing->hasvotes=$this->has_votes($studentid,$criteriondesc)?1:0;
                            $timing->lastvote=time();
                            $DB->delete_records('emarking_arguments',array("id"=>$argumentid));
                            $this->create_or_update_timing($timing);

                            $ret = array("msg"=>"Deleted!!");
                            break;
                        }else{
                            $ret = array("msg"=>"You cannot delete other peoples arguments!");
                            break;
                        }
                    }else{
                        $ret= array("msg"=>"Nothing to delete!!");
                        break;
                    }
                }

            break;
            case "dismiss":
                $studentid = required_param("stdid",PARAM_INT);
                $critdesc  = required_param("critd",PARAM_TEXT);
                $parentactivity= $this->parentcm->instance;
                $criteriondesc =$critdesc;
                $markerid = $USER->id;
                $timing = new stdClass();
                $timing->parentcm=$parentactivity;
                $timing->studentid=$studentid;
                $timing->criteriondesc=$criteriondesc;
                $timing->markerid = $markerid;

                $timing->timehidden = time();
                $this->create_or_update_timing($timing);
                $ret = array("msg"=>"exito");

                break;
            case "undismiss":
                $studentid = required_param("stdid",PARAM_INT);
                $critdesc  = required_param("critd",PARAM_TEXT);
                $parentactivity= $this->parentcm->instance;
                $criteriondesc =$critdesc;
                $markerid = $USER->id;
                $timing = new stdClass();
                $timing->parentcm=$parentactivity;
                $timing->studentid=$studentid;
                $timing->criteriondesc=$criteriondesc;
                $timing->markerid = $markerid;

                $timing->timehidden = 0;
                $this->create_or_update_timing($timing);
                $ret = array("msg"=>"exito");
            break;
            case "getMarkerModuleData":
                $instance = $this->parentcm->instance;
                $sql = "
                    SELECT e.id, e.name, m.masteractivity,m.markerid as markerid,CONCAT(u.firstname,' ',u.lastname) as markername
                    FROM {emarking_markers} as m
                    JOIN {emarking} as e
                      ON m.activityid=e.id
                    LEFT JOIN {user} as u
                     ON m.markerid = u.id
                     WHERE m.masteractivity = :masteractivity
                ";
                $modules = $DB->get_records_sql($sql,array('masteractivity'=>$instance));

                $markers = get_users_by_capability($this->context,'mod/emarking:grade',"u.id,CONCAT(u.firstname,' ',u.lastname) as name");

                $data = new stdClass();
                $data->modules = array_values($modules);
                $data->markers = array_values($markers);

                $ret = array("msg"=>"exito","data"=>$data);
            break;
            case "debates":
                $ret=$this->get_debates_array();
            break;
            case "gettasks":
                if(!$this->mycm){
                    $ret = array("error"=>"Marker is not part of this assignment");
                    break;
                }

                $sql = "
                    SELECT s.id,s.student, COUNT(c.id) as fillings,IFNULL(SUM(lv.score+c.bonus),0) as score,IFNULL(max(c.timemodified),0) as timemodified
                    FROM {emarking_submission} s
                    JOIN {emarking} e
                    ON s.emarking = e.id
                    JOIN {emarking_page} p
                    ON p.submission = s.id
                    LEFT JOIN {emarking_comment} c
                    ON p.id = c.page AND c.textformat=:rubrictype
                    LEFT JOIN {gradingform_rubric_levels} lv
                    ON lv.id = c.levelid
                    LEFT JOIN {gradingform_rubric_criteria} cr
                    ON lv.criterionid = cr.id
                    WHERE e.id = :myinstance
                    GROUP BY s.id
                ";
                $records = $DB->get_records_sql($sql,array(
                    "myinstance"=>$this->mycm->instance,
                    "rubrictype"=>2
                ));
                $debates = $this->get_debates_array();
                $criteria = $this->get_all_criteria();
                $maxscore=0;
                $ncriteria = count($criteria);
                foreach($criteria as $crit){
                    $maxscore+=$crit->maxscore;
                }
                foreach($records as $record){
                    $record->ncriteria =$ncriteria;
                    $record->maxscore =$maxscore;
                    $record->alerts = 0;
                    $record->debatedcriteria = 0;
                    foreach($debates as $debate){
                        if($debate->student==$record->student){
                            $nalerts = count($debate->alerts);
                            if($nalerts>0){
                                $record->alerts+=count($nalerts);

                                $record->debatedcriteria++;
                            }
                        }
                    }
                    $record->effectiveprogress=$record->fillings-$record->debatedcriteria;
                    $record->effectiveprogressstr=$record->fillings."-".($record->fillings-$record->debatedcriteria);
                }
                $ret = array("msg"=>"he","data"=>array_values($records));
                break;
        }
        return json_encode($ret);
    }

    public function is_mine()
    {
        return $this->mycm->id == $this->cm->id;
    }

    public function get_my_cm()
    {
        return $this->mycm;
    }

    /**
     * Using other markers' submission id, returns the submission id for current marker.
     * @param $submissionid
     * @return number|null
     */
    public function get_my_correct_submissionid($submissionid)
    {
        global $DB;
        $othersub = $DB->get_record('emarking_submission',array("id"=>$submissionid));
        $student = $othersub->student;
        $mysub = $DB->get_record('emarking_submission',array("student"=>$student,"emarking"=>$this->mycm->instance));
        if($mysub){
            return $mysub->id;
        }
        return null;
    }

    public function is_active()
    {
        return $this->active;
    }

    private function get_children($masteractivityid=null)
    {
        global $DB;
        if(!$masteractivityid){
            if(!$this->parentcm){
                return array();
            }
            $masteractivityid = $this->parentcm->instance;
        }
        return $markermap = $DB->get_records('emarking_markers',array('masteractivity'=>$masteractivityid));
    }
    private function get_activity_for_marker($masteractivityid,$markerid)
    {
        global $DB;
        if(!$masteractivityid){
            $masteractivityid = $this->cm->instance;
        }
        return $markermap = $DB->get_records('emarking_markers',array('masteractivity'=>$masteractivityid));
    }
    private function fill_with_magic(){
        global $DB,$CFG;
        $students = get_users_by_capability($this->context,'mod/emarking:submit');
        $emarking = $DB->get_record('emarking',array("id"=>$this->cm->instance));
        $context = $this->context;
        $courseid = $this->cm->course;
        $testfolder = $CFG->dirroot.'/mod/emarking/tests/img';
        $tempdir = emarking_get_temp_dir_path($emarking->id);
        emarking_initialize_directory($tempdir, true);
        foreach($students as $student){
            copy($testfolder."/test1.png",$tempdir.'/'.$student->id.'-'.$courseid.'-1.png');
            copy($testfolder."/test1_a.png",$tempdir.'/'.$student->id.'-'.$courseid.'-1_a.png');
            copy($testfolder."/test2.png",$tempdir.'/'.$student->id.'-'.$courseid.'-2.png');
            copy($testfolder."/test2_a.png",$tempdir.'/'.$student->id.'-'.$courseid.'-2_a.png');
            emarking_submit($emarking, $context, $tempdir,$student->id.'-'.$courseid.'-1.png', $student, 1);
            emarking_submit($emarking, $context, $tempdir,$student->id.'-'.$courseid.'-2.png', $student, 2);
        }
    }
    private function make_master()
    {
        global $CFG,$DB;
        $markermap = new stdClass();
        //First register this as a master
        $markermap->masteractivity = $this->cm->instance;
        $markermap->markerid = 0;
        $markermap->activityid = $this->cm->instance;
        $DB->insert_record('emarking_markers',$markermap);
    }

    private function create_marker_module($markerid){

        global $CFG,$DB;


        require_once($CFG->dirroot . '/lib/phpunit/classes/util.php');

        // Start transaction.
        $transaction = $DB->start_delegated_transaction();
        try{
            // Get generator.
            $generator = phpunit_util::get_data_generator();
            /**@var mod_emarking_generator $emarking_generator*/
            $emarking_generator= $generator->get_plugin_generator('mod_emarking');

            $blueprint = $DB->get_record('emarking',array("id"=>$this->cm->instance));
            $blueprint->timecreated = time();
            $originalname = $blueprint->name;
            $markers = get_users_by_capability($this->context,'mod/emarking:grade','u.id,u.username');
            $marker = $markers[$markerid];
            if(!$marker){
                throw new Exception ("Marker not found");
            }
            $previous = $DB->get_record('emarking_markers',array('masteractivity'=>$this->parentcm->instance,'markerid'=>$markerid));
            if($previous){
                throw new Exception("Delete previous marker record before assigning a new one");
            }
            $submissions = $DB->get_records('emarking_submission',array("emarking"=>$this->cm->instance));
            $pages = array();
            foreach($submissions as $sub){
                $pages[$sub->id] = $DB->get_records('emarking_page',array("submission"=>$sub->id));

            }
            unset($blueprint->id);
            $blueprint->name=$originalname." -- ".$marker->username;
            $inserted = $emarking_generator->create_instance($blueprint,array("visible"=>0));


            $markermap = new stdClass();

            $markermap->masteractivity = $this->parentcm->instance;
            $markermap->markerid = $marker->id;
            $markermap->activityid = $inserted->id;
            $DB->insert_record('emarking_markers',$markermap);

            // se agrega el grading method correcto.
            require_once($CFG->dirroot.'/grade/grading/lib.php');
            $gradingman = get_grading_manager(context_module::instance($inserted->cmid), 'mod_emarking');
            $gradingman->set_area("attempt");
            $gradingman->set_active_method("rubric");

            emarking_grade_item_update($inserted);


            //Now replicate submissions

            foreach($submissions as $sub){

                $insertable = clone($sub);
                unset($insertable->id);
                $insertable->emarking = $inserted->id;
                $id = $DB->insert_record('emarking_submission',$insertable);

                foreach($pages[$sub->id] as $subpage){
                    $insertablepage = clone($subpage);
                    unset($insertablepage->id);
                    $insertablepage->submission = $id;
                    $DB->insert_record('emarking_page',$insertablepage);
                }
                // Update the raw grade of the user
                $grade_item = grade_item::fetch(array('itemtype'=>'mod', 'itemmodule'=>'emarking','iteminstance'=>$inserted->id));
                $grade = $grade_item->get_grade($insertable->student, true);
                $grade_item->update_final_grade(
                    $insertable->student, // student
                    null, // rawgrade
                    'upload', // source
                    '', // feedback
                    FORMAT_HTML, // feedback format
                    $marker->id // usermodified
                ); // grade

            }
            $transaction->allow_commit();
        }catch(Exception $e){
            $transaction->rollback($e);
        }
    }

    private function get_students_grid_data()
    {

        global $DB;

        $sql ="
            SELECT CONCAT(s.student,',',rc.description) as ikey,us.id as userid,us.username,AVG(c.bonus+rl.score) as avgscore,GROUP_CONCAT(s.id) as subs,GROUP_CONCAT(c.markerid) as mrk,GROUP_CONCAT(um.username) as mrk2,rc.description,GROUP_CONCAT(c.bonus+rl.score) as groupscores
            FROM {emarking_comment} c
            JOIN {emarking_page} p
            ON p.id = c.page
            JOIN {emarking_submission} s
            ON s.id = p.submission
            JOIN {emarking_markers} m
            ON s.emarking = m.activityid AND m.activityid!=m.masteractivity
            JOIN {gradingform_rubric_levels} rl
            ON rl.id = c.levelid
            JOIN {gradingform_rubric_criteria} rc
            ON rl.criterionid = rc.id
            JOIN {user} us
            ON us.id = s.student
            JOIN {user} um
            ON um.id = c.markerid
            WHERE c.textformat=2 AND m.masteractivity= :masterid
            group by rc.description,s.student
            ORDER BY ikey
        ";
        $params = array("masterid"=>$this->parentcm->instance);
        $records = $DB->get_records_sql($sql,$params);



        $students = array();
        foreach($records as $record){
            if(!isset($students[$record->userid])){ // New student

                //Prepare the student data object
                $student = new stdClass();
                $student->criteria = array();
                $student->id = $record->userid;
                $student->avgscore = 0;


                //Keep track of it.
                $students[$record->userid] = $student;
            }else{
                $student = $students[$record->userid];
            }

            $criterion = new stdClass();
            $criterion->description = $record->description;
            $criterion->avgscore = $record->avgscore;
            $student->avgscore += $record->avgscore;
            $criterion->voters = array();

            $markerids = explode(",",$record->mrk);
            $subs = explode(",",$record->subs);
            $markers = explode(",",$record->mrk2);
            $scores = explode(",",$record->groupscores);

            for($i=0;$i<count($markerids);$i++){
                $vote = new stdClass();
                $vote->markerid = $markerids[$i];
                $vote->sub = $subs[$i];
                $vote->marker = $markers[$i];
                $vote->score = $scores[$i];
                $criterion->voters[] =$vote;
            }

            $student->criteria[$criterion->description] = $criterion;
        }

        return $students;
    }

    private function get_all_criteria(){
        global $DB;
        $sql = "select

				a.id as criterionid,
				a.description as description,
				max(l.score) as maxscore
				from {emarking} as s
				inner join {course_modules} as cm on (s.id = cm.instance)
				inner join {context} as c on (c.instanceid = cm.id)
				inner join {grading_areas} as ar on (ar.contextid = c.id)
				inner join {grading_definitions} AS d on (ar.id = d.areaid)
				inner join {gradingform_rubric_criteria} AS a on (d.id = a.definitionid)
				inner join {gradingform_rubric_levels} AS l on (a.id = l.criterionid)

				WHERE s.id = :emarkingparent
				group by (criterionid)
				";

        $records = $DB->get_records_sql($sql,array("emarkingparent"=>$this->parentcm->instance));
        return $records;
    }

    private function get_all_levels($criteriondesc,$master = true){
        global $DB;
        $sql = "select
                l.id as levelid,
				a.id as criterionid,
				a.description as description,
				l.score as score,
				l.definition as definition
				from {emarking} as s
				inner join {course_modules} as cm on (s.id = cm.instance)
				inner join {context} as c on (c.instanceid = cm.id)
				inner join {grading_areas} as ar on (ar.contextid = c.id)
				inner join {grading_definitions} AS d on (ar.id = d.areaid)
				inner join {gradingform_rubric_criteria} AS a on (d.id = a.definitionid)
				inner join {gradingform_rubric_levels} AS l on (a.id = l.criterionid)

				WHERE s.id = :emarkingparent AND a.description = :critdesc

				";

        $records = $DB->get_records_sql($sql,
            array(
            "emarkingparent"=>$master?$this->parentcm->instance:$this->mycm->instance,
            "critdesc"=>$criteriondesc,
        ));
        return $records;
    }

    /**
     * @return array
     */
    private function create_dashboard_student()
    {
        global $USER;
        $o="";
        $data = $this->get_students_grid_data();
        $students = get_users_by_capability($this->context,'mod/emarking:submit');
        $criteria = $this->get_all_criteria();
        ob_start();

        ?>
        <h2>Dashboard</h2><h3>>>Students </h3>
        <p>Legend <i class="icon-info-sign has-tooltip" data-tooltip="#legends"></i><div id="legends" class="popup" style="display:none;">
        <ul>
            <li>Each row is a student, and heach column a criteria.</li>
            <li>The number is the average score.</li>
        </ul>
        <table class="dashboard mini">
            <tr>
                <th colspan="2" style="text-align:center;">Legend</th>
            </tr>
            <tr>
                <td class="success"></td>
                <th>Agreement!</th>
            </tr>
            <tr>
                <td class="warn"></td>
                <th>2 different opinion groups</th>
            </tr>
            <tr>
                <td class="fail"></td>
                <th>More than 2 opinion group</th>
            </tr>
            <tr>
                <th><i class="icon-male pending"></i></th>
                <th>A marker that has this as a pending task</th>
            </tr>
            <tr>
                <th><i class="icon-male"></i></th>
                <th>A marker that already marked this</th>
            </tr>

        </table>
    </div></p>


        <p><label>Marker <input id="markerfilter"/></label> </p>
        <table class="dashboard">
            <thead>
            <tr>
                <th></th>
                <?php foreach ($criteria as $criterion) { ?>

                    <th><?php echo  $criterion->description ?></th>
                    <th><p></p></th>

                <?php } ?>
                <th></th>
                <th>Score <i class></i></th>

            </tr>
            </thead>
            <tbody>
            <?php foreach ($students as $student) { ?>
                <tr class="<?php if (!isset($data[$student->id])) {echo "notgraded";}else{echo "graded";}?>">

                    <th><?php echo  $student->firstname . " " . $student->lastname ?></th>
                    <?php if (isset($data[$student->id])) {
                        $markedstudent = $data[$student->id]; ?>
                        <?php foreach ($criteria as $criterion) { ?>
                            <?php if (isset($markedstudent->criteria[$criterion->description])) {
                                $markedcriterion = $markedstudent->criteria[$criterion->description];
                                $maxsep = 0;
                                $min = 1000;
                                $max = 0;
                                $dist = array();
                                foreach ($markedcriterion->voters as $vote) {
                                    $min = $vote->score < $min ? $vote->score : $min;
                                    $max = $vote->score > $max ? $vote->score : $max;
                                    if (isset($dist[$vote->score])) {

                                        $dist[$vote->score]->n++;
                                        $dist[$vote->score]->voters[] = $vote;
                                    } else {
                                        $votedist = new stdClass();
                                        $votedist->voters = array();
                                        $votedist->n = 1;
                                        $votedist->voters[] = $vote;
                                        $votedist->score = $vote->score;
                                        $dist[$vote->score] = $votedist;

                                    }
                                    ?>

                                <?php
                                }
                                $class = "";
                                if ($max - $min == 0) {
                                    $class = "success";
                                } elseif (count($dist) < 3) {
                                    $class = "warn";
                                } else {
                                    $class = "fail";
                                }
                                ?>

                                <td class="<?php echo $class ?>">
                                    <div class="handle"></div>
                                    <div class="voters">
                                        <?php $k = 0;
                                        foreach ($dist as $vote) {
                                            echo $k > 0 ? "-" : "";
                                            $k++; ?>
                                            <?php foreach ($vote->voters as $thevoter) {?>

                                                <i class="icon-male"

                                                   data-name="<?php echo  $thevoter->marker ?>"></i>

                                            <?php } ?>
                                        <?php } ?>
                                    </div>
                                    <p><?php echo  number_format($markedcriterion->avgscore, 2) ?></p>
                                </td>
                                <th><p></p></th>

                            <?php } else { ?>
                                <td>P</td>

                            <?php } ?>
                        <?php } ?>

                        <th> =</th>
                        <td><?php echo  number_format($markedstudent->avgscore, 2) ?></td>
                    <?php
                    } else {
                        for ($i = 0; $i < count($criteria); $i++) {
                            echo "<td><p></p></td><th><p></p></th>";
                        }
                        echo "<th><p></p></th>";
                        echo "<td><p></p></td>";
                    }?>

                </tr>
            <?php } ?>
            </tbody>
        </table>

        <?php $o .= ob_get_clean();
        return $o;
    }

    /**
     *
     * @return string
     */
    private function render_tasks_tab()
    {
        global $USER,$CFG,$OUTPUT;
        $markobjects = $this->taskRepo->findPendingForMarker($this->markerRepo->findById($USER->id));
        $o = "";

        $o .= "<strong>YOUR TASKS</strong><br>";
        if (count($markobjects) > 0) {
            foreach ($markobjects as $marktask) {
                $url = $CFG->wwwroot . '/mod/emarking/ajax/a.php?action=emarking&ids=' . $marktask->item->ids;

                $o .= '<a href="' . $url . '">You need to mark ' . $marktask->item->id . '<br></a>';
            }
        } else {
            $o .= "No tasks, system should generate more";
        }

       return $o;
    }

    private function get_debates_array(){
        global $USER;
        if($this->mycm){
            $debates = $this->taskRepo->findDebatesForMarkerid($USER->id, $this->mycm->instance);
            return array_values($debates);
        }else{
            return array();
        }

    }

    /**
     * @return string
     */
    private function render_debug_tab()
    {
        $o ="";
        $o .= "";
        ob_start();
        global $CFG,$USER;
        $o.= ob_get_clean();
        $o .= "Debugging enabled." . "<br>";
        $o .= '<a href="'.$CFG->wwwroot."/mod/emarking/crowd/marking.php?act=createfakepages&cmid=".$this->cm->id.'">Click here</a> to create pages or replace 1 and 2 for all students with dummy test objects';
        if ($this->isparent) {
            $o .= "You are on the parent: " . $this->cm->instance . "<br> this emarking has the following activities:" . "<br>";
        } else {
            $o .= "You are on a child: " . $this->cm->instance . "<br> this emarking has the following activities:" . "<br>";
        }
        foreach ($this->activities as $peer) {
            if ($peer->activityid == $this->parent->masteractivity) {

                $o .= "Activity " . $peer->activityid . " is master. Only this one should be visible to students.<br>";
            } else {
                $o .= "Activity " . $peer->activityid . " is marked by " . $peer->markerid . "<br>";
            }
        }
        return $o;
    }

    private function wip_debate_screen($studentid,$criteriondesc){
        global $DB;
        global $USER;

        $sql ="
            SELECT c.markerid as markerid,us.id as studentid,mycomment.rawtext as mycomment, mycomment.bonus as mybonus,mypage.page as mypage, mycomment.posx as myx, mycomment.posy as myy,mysub.id as mysub,rc.description,rl.id as levelid,c.bonus,rl.score
            FROM {emarking_comment} c
            JOIN {emarking_page} p
            ON p.id = c.page
            JOIN {emarking_submission} s
            ON s.id = p.submission
            JOIN {emarking_markers} m
            ON s.emarking = m.activityid  AND m.activityid!=m.masteractivity
            JOIN {gradingform_rubric_levels} rl
            ON rl.id = c.levelid
            JOIN {gradingform_rubric_criteria} rc
            ON rl.criterionid = rc.id
            JOIN {user} us
            ON us.id = s.student
            JOIN {user} um
            ON um.id = c.markerid
            left Join {emarking_comment} mycomment
            ON mycomment.markerid = :markerid AND c.id = mycomment.id
            left JOIN {emarking_page} mypage
            on mycomment.page = mypage.id
            left JOIN {emarking_submission} mysub
            ON mysub.emarking=:markermoduleid AND mysub.student=s.student

            WHERE c.textformat=2 AND m.masteractivity= :masterid AND rc.description = :description AND s.student = :studentid
            ORDER BY markerid
        ";
        $params = array(
            "masterid"=>$this->parentcm->instance,
            "description"=>$criteriondesc,
            "studentid"=>$studentid,
            "markerid"=>$USER->id,
            "markermoduleid"=>$this->mycm->instance
        );
        $records = $DB->get_records_sql($sql,$params);
        //foreach($records as $record)
        $levels = $this->get_all_levels($criteriondesc);
        $mylevels = $this->get_all_levels($criteriondesc,false);
        $result = array();

        $keys =  "(".implode(",",array_keys($levels)).")";
        $arguments = $DB->get_records_sql(
            "SELECT IFNULL(CONCAT(a.id,',',v.id),a.id) as fakeid,a.id as argid,a.markerid as creatorid,v.id as voteid,a.levelid,a.bonus,v.markerid as voterid,a.text FROM {emarking_arguments} a
            LEFT JOIN {emarking_argument_votes} as v
            ON a.id = v.argumentid
            WHERE a.levelid in $keys AND a.studentid = :studentid
            ",array("studentid"=>$studentid));
        //var_dump($arguments);
        foreach($levels as $dblevel){
            $level = new stdClass();
            $level->id = $dblevel->levelid;
            $level->definition = $dblevel->definition;
            $level->score = $dblevel->score*1;
            $level->bonuses = array();
            foreach($mylevels as $mylevel){
                if($mylevel->definition==$dblevel->definition){
                    $level->mylevelid=$mylevel->levelid;
                }
            }
            $result[$level->score*1] = $level;

        }
        //THERE ARE 2 FOREACH LOOPS DUPLICATED:
        //THE FIRST ONE ADDS THE ACTUAL MARKS
        foreach($records as $key=> $record){

            if(isset($result[$record->score*1])){
                $resultrow = $result[$record->score*1];

                if($record->mypage&&$record->mybonus&&$record->myx&&$record->myy){
                    $resultrow->mycomment = $record->mycomment;
                    $resultrow->mypage = $record->mypage*1;
                    $resultrow->mybonus = $record->mybonus*1;
                    $resultrow->myx = $record->myx*1;
                    $resultrow->myy = $record->myy*1;
                }

                if(isset($resultrow->bonuses[($record->bonus*1).""])){

                    $bonusrow = $resultrow->bonuses[($record->bonus*1).""];
                }else{
                    $bonusrow = new stdClass();
                    $bonusrow->bonus = $record->bonus*1;
                    $bonusrow->mymark=false;
                    $bonusrow->marks = array();
                    $bonusrow->allvotes = array();
                    $bonusrow->arguments = array();
                    $resultrow->bonuses[($record->bonus*1).""] = $bonusrow;
                }
                $bonusrow->marks[] = $record;
                if($record->markerid==$USER->id){
                    $bonusrow->mymark=true;
                }
            }else{
                throw new Exception("The rubric scores may have been changed since creating this module.");
            }
        }
        //THE SECOND ONE ADDS THE ARGUMENTS
        foreach($arguments as $arg){

            foreach($levels as $dblevel){
                if($arg->levelid==$dblevel->levelid){
                    $resultrow = $result[$dblevel->score*1];
                }
            }
            if(!isset($resultrow)){
                throw new Exception("RUBRIC INCONSISTENT");
            }

            if(isset($resultrow->bonuses[($arg->bonus*1).""])){
                $bonusrow = $resultrow->bonuses[($arg->bonus*1).""];
            }else{
                $bonusrow = new stdClass();
                $bonusrow->bonus = $arg->bonus*1;
                $bonusrow->mymark=false;
                $bonusrow->marks = array();
                $bonusrow->allvotes = array();
                $bonusrow->arguments = array();
                $resultrow->bonuses[($arg->bonus*1).""] = $bonusrow;
            }

            if(isset($bonusrow->arguments[$arg->argid])){
                $argumentrow = $bonusrow->arguments[$arg->argid];
            }else{
                $argumentrow = new stdClass();
                $argumentrow->id = $arg->argid;
                $argumentrow->isMine=$arg->creatorid==$USER->id;
                $argumentrow->iLike = false;
                $argumentrow->text = $arg->text;
                $argumentrow->voters = array();
                $bonusrow->arguments[$arg->argid] = $argumentrow;
            }

            if($arg->voteid){
                $bonusrow->allvotes[] =$arg;
                $argumentrow->voters[] =$arg;
                if($arg->voterid==$USER->id){
                    $argumentrow->iLike=true;
                }
            }
        }
        $table = new html_table();
        $table->head = array("Level", "Score", "Bonus","Marks", "AllVotes", "Arguments");
        $table->data = array();

        //remove keys
        foreach($result as $res){
            foreach($res->bonuses as $bonusrow){
                $bonusrow->arguments = array_values($bonusrow->arguments);
            }
        }
        return $result;


    }
    private function get_criteria_description_for_levelid($levelid)
    {
        global $DB;
        $resp=$DB->get_records_sql('
        SELECT cr.id, cr.description
        FROM {gradingform_rubric_levels} as le
        JOIN {gradingform_rubric_criteria} as cr
        ON le.criterionid = cr.id
        WHERE le.id = :levelid
        ',
            array("levelid"=>$levelid)
        );
        if(count($resp)>0){

            $vals = array_values($resp);
            $firstcrit=$vals[0];
            return $firstcrit->description;
        }else{
            return "";
        }
    }

    private function create_or_update_timing($timing)
    {
        global $DB;
        if($dbtiming=$DB->get_record_sql(
            'SELECT *
            FROM {emarking_debate_timings}
            WHERE parentcm = :parentcm AND studentid=:studentid
              AND criteriondesc = :criteriondesc
              AND markerid = :markerid
            ',
            array(
                "parentcm"=>$timing->parentcm,
                "studentid"=>$timing->studentid,
                "criteriondesc"=>$timing->criteriondesc,
                "markerid"=>    $timing->markerid
            ))){
            if(isset($timing->hasvotes)){
                $dbtiming->hasvotes=$timing->hasvotes;
            }
            if(isset($timing->lastargumentchange)){
                $dbtiming->lastargumentchange=$timing->lastargumentchange;
            }
            if(isset($timing->lastvote)){
                $dbtiming->lastvote=$timing->lastvote;
            }
            if(isset($timing->timehidden)){
                $dbtiming->timehidden=$timing->timehidden;
            }
            $DB->update_record('emarking_debate_timings',$dbtiming);
        }else{
            $DB->insert_record('emarking_debate_timings',$timing);
        }
    }

    private function get_criteria_description_for_argument($argid)
    {
        global $DB;
        $resp=$DB->get_records_sql('
        SELECT cr.id, cr.description
        FROM {emarking_arguments} as arg
        JOIN {gradingform_rubric_levels} as le
        ON arg.levelid = le.id
        JOIN {gradingform_rubric_criteria} as cr
        ON le.criterionid = cr.id
        WHERE arg.id = :argid
        ',
            array("argid"=>$argid)
        );
        if(count($resp)>0){

            $vals = array_values($resp);
            $firstcrit=$vals[0];
            return $firstcrit->description;
        }else{
            return "";
        }
    }

    private function has_votes($studentid, $criteriondesc)
    {
        global $DB,$USER;

        $levels = $this->get_all_levels($criteriondesc);

        $keys =  "(".implode(",",array_keys($levels)).")";
        $arguments = $DB->get_records_sql(
            "SELECT IFNULL(CONCAT(a.id,',',v.id),a.id) as fakeid,a.id as argid,a.markerid as creatorid,v.id as voteid,a.levelid,a.bonus,v.markerid as voterid,a.text
            FROM {emarking_arguments} a
            JOIN {emarking_argument_votes} as v
            ON a.id = v.argumentid
            WHERE a.levelid in $keys AND a.studentid = :studentid AND v.markerid = :markerid
            ",array("studentid"=>$studentid,"markerid"=>$USER->id));

        return count($arguments)>0;
    }

    private function create_configuration_tab()
    {
        global $CFG;
        $o = "";

        $o.=  "<h3>Module creation</h3>";
        $markers = get_users_by_capability($this->context,'mod/emarking:grade','u.id,u.firstname,u.lastname');
        $children = $this->get_children();
        $nomodules = 0;
        $o.= '<table class="table">';
        $o.= "<tr><th>Marker</th><th>Has module</th><th>Actions</th></tr>";
        foreach ($markers as $marker) {

            $module = null;
            foreach ($children as $child) {
                if ($child->markerid == $marker->id) {
                    $module = $child;
                    break;
                }

            }
            if(!$module){
                $nomodules++;
            }
            $href=$CFG->wwwroot.'/mod/emarking/crowd/marking.php?cmid='.$this->cm->id.'&act=includemarker';
            $createlink="";
            if(!$module){
                $createlink = '<form method="POST" action="'.$href.'"><input type="hidden" name="markerid" value="'.$marker->id.'"><input type="submit" value="Create"></form>';
            }

            $o.= '<tr><td>'.$marker->firstname.' '.$marker->lastname.'</td><td>'.($module?'yes':'no').'</td><td>'.$createlink.'</td></tr>';
        }
        $o.= '</table>';
        $href=$CFG->wwwroot.'/mod/emarking/crowd/marking.php?cmid='.$this->cm->id.'&act=includeallmarkers';


        $o.= $nomodules==0?"All markers have a module.":'<a class="btn" href="'.$href.'">Create all missing modules</a>';
        return $o;

    }

    private function make_create_for_all()
    {
        $children = $this->get_children($this->parentcm->instance);

        $markers = get_users_by_capability($this->context,'mod/emarking:grade','u.id,u.username');
        $markersWithNoModule=array();
        foreach($markers as $marker){
            $module=null;
            foreach($children as $child){
                if($child->markerid==$marker->id){
                    $module=$child;
                }
            }

            if(!$module){
                $markersWithNoModule[] = $marker;
            }
        }

        foreach($markersWithNoModule as $nmMarker){
            $this->create_marker_module($nmMarker->id);
        }
    }


}