<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This form is used to upload a zip file containing digitized answers
 *
 * @package local
 * @subpackage ciae
 * @copyright 2016 Francisco Ralph <francisco.garcia@ciae.uchile.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once ($CFG->libdir . '/formslib.php');
require_once ($CFG->dirroot . '/course/lib.php');


class mod_emarking_activities_create_activity_basic extends moodleform {

    public function definition() {
        global $CFG, $OUTPUT, $COURSE, $DB;
        
        require ($CFG->dirroot. '/mod/emarking/activities/generos.php');
       array_unshift($generos, "Seleccione un género");
       $result = $DB->get_records_sql('
         SELECT gd.id,
                gd.name,
                gd.description
         FROM {grading_definitions} as gd,
              {grading_areas} as ga
         WHERE ga.id=gd.areaid AND
               gd.method=? AND
               ga.contextid=? AND
               ga.component=?', array('rubric',1,'core_grading'));
       $rubrics[0]= 'Seleccione una rúbrica';
        foreach ($result as $data) {
            $rubrics[$data->id]=$data->name;
        }
        //pc= Proposito comunicativo, obtenidos de la agencia de calidad
        $pc=array('0'=>'Seleccione un propósito comunicativo','Argumentar'=>'Argumentar','Informar'=>'Informar','Narrar'=>'Narrar');

        $mform = $this->_form; // Don't forget the underscore! 
        // Paso 1 Información básica
        $mform->addElement('header', 'db', 'Información Básica', null);
        //Título
        $mform->addElement('text', 'title','Título'); 
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('required'), 'required');
        //descripción
        $mform->addElement('static', '', '','Pequeña descrición sobre la actividad a realizar, max 500 caracteres.');
        $mform->addElement('textarea', 'description', "Descripción", 'wrap="virtual" rows="10" cols="50" maxlength="300"'); 
        $mform->setType('description', PARAM_TEXT);
 
        $mform->addElement('static', '', '','Los objetivos que entrega el ministerio de educación.');
        //Curso
        $courseArray=array('0'=>'Seleccione un curso','1'=>'1° básico','2'=>'2° básico','3'=>'3° básico',
            '4'=>'4° básico','5'=>'5° básico','6'=>'6° básico',);
        $mform->addElement('select', 'C1', 'Objetivos de Aprendizaje',$courseArray);
      
        
        $oacheckboxarray = array();
        //creating days of the week
        $oacheckboxarray[] =& $mform->createElement('static', '', '','13  ');
        $oacheckboxarray[] =& $mform->createElement('checkbox', 'C1OA13');
        $oacheckboxarray[] =& $mform->createElement('static', '', '','14  ');
        $oacheckboxarray[] =& $mform->createElement('checkbox', 'C1OA14');
        $oacheckboxarray[] =& $mform->createElement('static', '', '','15  ');
        $oacheckboxarray[] =& $mform->createElement('checkbox', 'C1OA15');
        $oacheckboxarray[] =& $mform->createElement('static', '', '','16  ');
        $oacheckboxarray[] =& $mform->createElement('checkbox', 'C1OA16');
        $oacheckboxarray[] =& $mform->createElement('static', '', '','17  ');
        $oacheckboxarray[] =& $mform->createElement('checkbox', 'C1OA17');
        $oacheckboxarray[] =& $mform->createElement('static', '', '','18  ');
        $oacheckboxarray[] =& $mform->createElement('checkbox', 'C1OA18');
        $oacheckboxarray[] =& $mform->createElement('static', '', '','19  ');
        $oacheckboxarray[] =& $mform->createElement('checkbox', 'C1OA19');
        $oacheckboxarray[] =& $mform->createElement('static', '', '','20  ');
        $oacheckboxarray[] =& $mform->createElement('checkbox', 'C1OA20');
        $oacheckboxarray[] =& $mform->createElement('static', '', '','21  ');
        $oacheckboxarray[] =& $mform->createElement('checkbox', 'C1OA21');
        $oacheckboxarray[] =& $mform->createElement('static', '', '','22  ');
        $oacheckboxarray[] =& $mform->createElement('checkbox', 'C1OA22');
		//display them into one row
        $mform->addGroup($oacheckboxarray, 'CODC1');
       
        $mform->addElement('hidden', 'oacount', 1,array('id'=>'oacount'));
        $mform->setType('oacount', PARAM_INT);
        $mform->addElement('html', '<div id="CODC2" style="display:none;">');
        $mform->addElement('select', 'C2', '',$courseArray);
        $oacheckboxarray2 = array();
        //creating days of the week
        $oacheckboxarray2[] =& $mform->createElement('static', '', '','13 ');
        $oacheckboxarray2[] =& $mform->createElement('checkbox', 'C2OA13');
        $oacheckboxarray2[] =& $mform->createElement('static', '', '','14 ');
        $oacheckboxarray2[] =& $mform->createElement('checkbox', 'C2OA14');
        $oacheckboxarray2[] =& $mform->createElement('static', '', '','15 ');
        $oacheckboxarray2[] =& $mform->createElement('checkbox', 'C2OA15');
        $oacheckboxarray2[] =& $mform->createElement('static', '', '','16 ');
        $oacheckboxarray2[] =& $mform->createElement('checkbox', 'C2OA16');
        $oacheckboxarray2[] =& $mform->createElement('static', '', '','17 ');
        $oacheckboxarray2[] =& $mform->createElement('checkbox', 'C2OA17');
        $oacheckboxarray2[] =& $mform->createElement('static', '', '','18 ');
        $oacheckboxarray2[] =& $mform->createElement('checkbox', 'C2OA18');
        $oacheckboxarray2[] =& $mform->createElement('static', '', '','19 ');
        $oacheckboxarray2[] =& $mform->createElement('checkbox', 'C2OA19');
        $oacheckboxarray2[] =& $mform->createElement('static', '', '','20 ');
        $oacheckboxarray2[] =& $mform->createElement('checkbox', 'C2OA20');
        $oacheckboxarray2[] =& $mform->createElement('static', '', '','21 ');
        $oacheckboxarray2[] =& $mform->createElement('checkbox', 'C2OA21');
        $oacheckboxarray2[] =& $mform->createElement('static', '', '','22 ');
        $oacheckboxarray2[] =& $mform->createElement('checkbox', 'C2OA22');
        //display them into one row
        $mform->addGroup($oacheckboxarray2, 'CODC2');
        $mform->addElement('html', '</div>');
        
        $mform->addElement('html', '<div id="CODC3" style="display:none;">');
        $mform->addElement('select', 'C3', '',$courseArray);
        $oacheckboxarray3 = array();
        //creating days of the week
        $oacheckboxarray3[] =& $mform->createElement('static', '', '','13 ');
        $oacheckboxarray3[] =& $mform->createElement('checkbox', 'C3OA13');
        $oacheckboxarray3[] =& $mform->createElement('static', '', '','14 ');
        $oacheckboxarray3[] =& $mform->createElement('checkbox', 'C3OA14');
        $oacheckboxarray3[] =& $mform->createElement('static', '', '','15 ');
        $oacheckboxarray3[] =& $mform->createElement('checkbox', 'C3OA15');
        $oacheckboxarray3[] =& $mform->createElement('static', '', '','16 ');
        $oacheckboxarray3[] =& $mform->createElement('checkbox', 'C3OA16');
        $oacheckboxarray3[] =& $mform->createElement('static', '', '','17 ');
        $oacheckboxarray3[] =& $mform->createElement('checkbox', 'C3OA17');
        $oacheckboxarray3[] =& $mform->createElement('static', '', '','18 ');
        $oacheckboxarray3[] =& $mform->createElement('checkbox', 'C3OA18');
        $oacheckboxarray3[] =& $mform->createElement('static', '', '','19 ');
        $oacheckboxarray3[] =& $mform->createElement('checkbox', 'C3OA19');
        $oacheckboxarray3[] =& $mform->createElement('static', '', '','20 ');
        $oacheckboxarray3[] =& $mform->createElement('checkbox', 'C3OA20');
        $oacheckboxarray3[] =& $mform->createElement('static', '', '','21 ');
        $oacheckboxarray3[] =& $mform->createElement('checkbox', 'C3OA21');
        $oacheckboxarray3[] =& $mform->createElement('static', '', '','22 ');
        $oacheckboxarray3[] =& $mform->createElement('checkbox', 'C3OA22');
        //display them into one row
        $mform->addGroup($oacheckboxarray3, 'CODC3');
        $mform->addElement('html', '</div>');
        
        
        $buttonar=array();
        $buttonar[]=$mform->createElement('button', 'more', '+', array('onclick'=>'showDiv()'));
        $buttonar[]=$mform->createElement('button', 'less', '-', array('onclick'=>'hideDiv()','style'=>'display:none;'));
        $mform->addGroup($buttonar, 'buttonarr');
        
        // Propósito comunicativo
        $mform->addElement('select', 'comunicativepurpose', 'Propósito Comunicativo', $pc);
        $mform->addRule('comunicativepurpose', get_string('required'), 'required');
        $mform->setType('comunicativepurpose', PARAM_TEXT);
        // $mform->addHelpButton('comunicativepurpose', 'pc','ciae');
        // Género
        $mform->addElement('select', 'genre', 'Género', $generos);
        $mform->addRule('genre', get_string('required'), 'required');
        $mform->setType('genre', PARAM_TEXT);
        //$mform->addHelpButton('genre', 'genero','ciae');
        // Audiencia
        $mform->addElement('text', 'audience','Audiencia');
        $mform->setType('audience', PARAM_TEXT);
        //$mform->addHelpButton('audience', 'audiencia','ciae');
        // Tiempo estimado
        $tiempoEstimado=array('45'=>'45 minutos','90'=>'90 minutos','135'=>'135 minutos','180'=>'180 minutos');
        $mform->addElement('select', 'estimatedtime', 'Tiempo Estimado', $tiempoEstimado);
        $mform->addRule('estimatedtime', get_string('required'), 'required');
        $mform->setType('estimatedtime', PARAM_TEXT);
        $mform->addElement('hidden', 'step', 2);
        $mform->setType('step', PARAM_INT);
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'editing', 0);
        $mform->setType('editing', PARAM_INT);
        

        $this->add_action_buttons(false,'Siguiente');

            
        ?>
        <script>
        function showDiv() {
        	document.getElementById('oacount').value++;
            if(document.getElementById('oacount').value==2){
            	document.getElementById("id_buttonarr_less").style.display = "inline";
            
             }
            if(document.getElementById('oacount').value==3){
            	document.getElementById("id_buttonarr_more").style.display = "none";
            
             }
        		
        		var num ="CODC"+document.getElementById('oacount').value;
            	console.log(document.getElementById('oacount').value);
        	    document.getElementById(num).style.display = "block";
        	   
        	}
        function hideDiv() {
        
        var hidden = document.getElementById('oacount').value;
        var hiddenB =hidden-1;
        if(hidden==3){
        	document.getElementById("id_buttonarr_more").style.display = "inline";
        
         }
        if(hidden==2){
        	document.getElementById("id_buttonarr_less").style.display = "none";
        
         }
        var select = "id_C"+hidden;

        var num ="CODC"+document.getElementById('oacount').value;
     	document.getElementById(num).style.display = "none";
     	document.getElementById(select).value=0;
     	document.getElementById("id_CODC"+hidden+"_C"+hidden+"OA13").checked = false;
     	document.getElementById("id_CODC"+hidden+"_C"+hidden+"OA14").checked = false;
     	document.getElementById("id_CODC"+hidden+"_C"+hidden+"OA15").checked = false;
     	document.getElementById("id_CODC"+hidden+"_C"+hidden+"OA16").checked = false;
     	document.getElementById("id_CODC"+hidden+"_C"+hidden+"OA17").checked = false;
     	document.getElementById("id_CODC"+hidden+"_C"+hidden+"OA18").checked = false;
     	document.getElementById("id_CODC"+hidden+"_C"+hidden+"OA19").checked = false;
     	document.getElementById("id_CODC"+hidden+"_C"+hidden+"OA20").checked = false;
     	document.getElementById("id_CODC"+hidden+"_C"+hidden+"OA21").checked = false;
     	document.getElementById("id_CODC"+hidden+"_C"+hidden+"OA22").checked = false;	   	
     	
     	document.getElementById('oacount').value=hiddenB;
     	
     	}
        </script>
        <?php 
        

    }
    //Custom validation should be added here
    
}