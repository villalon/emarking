<?php
require_once (dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . '/config.php');

global $OAS;
$OAS = json_decode('[
{"curso":1, "oa":[
    {"id":13, "descripcion":"Experimentar con la escritura para comunicar hechos, ideas y sentimientos, entre otros."},
    {"id":14, "descripcion":"Escribir oraciones completas para transmitir mensajes."},
    {"id":15, "descripcion":"Escribir con letra clara, separando las palabras con un espacio para que puedan ser leídas por otros con facilidad."},
    {"id":16, "descripcion":"Incorporar de manera pertinente en la escritura el vocabulario nuevo extraído de textos escuchados o leídos."}]},
{"curso":2, "oa":[
    {"id":12, "descripcion":"Escribir frecuentemente, para desarrollar la creatividad y expresar sus ideas, textos como poemas, diarios de vida, anécdotas, cartas, recados, etc."},
    {"id":13, "descripcion":"Escribir creativamente narraciones (experiencias personales, relatos de hechos, cuentos, etc.) que tengan inicio, desarrollo y desenlace."},
    {"id":14, "descripcion":"Escribir artículos informativos para comunicar información sobre un tema."},
    {"id":15, "descripcion":"Escribir con letra clara, separando las palabras con un espacio para que puedan ser leídas por otros con facilidad."},
    {"id":16, "descripcion":"Planificar la escritura, generando ideas a partir de: observación de imágenes; conversaciones con sus pares o el docente sobre experiencias personales y otros temas."},
    {"id":17, "descripcion":"Escribir, revisar y editar sus textos para satisfacer un propósito y transmitir sus ideas con claridad. Durante este proceso: organizan las ideas en oraciones que comienzan con mayúscula y terminan con punto; utilizan un vocabulario variado; mejoran la redacción del texto a partir de sugerencias de los pares y el docente; corrigen la concordancia de género y número, la ortografía y la presentación."},
    {"id":18, "descripcion":"Incorporar de manera pertinente en la escritura el vocabulario nuevo extraído de textos escuchados o leídos."},
    {"id":19, "descripcion":"Comprender la función de los artículos, sustantivos y adjetivos en textos orales y escritos, y reemplazarlos o combinarlos de diversas maneras para enriquecer o precisar sus producciones."},
    {"id":20, "descripcion":"Identificar el género y número de las palabras para asegurar la concordancia en sus escritos."},
    {"id":21, "descripcion":"Escribir correctamente para facilitar la comprensión por parte del lector, usando de manera apropiada: combinaciones ce-ci, que-qui, ge-gi, gue-gui, güe-güi; r-rr-nr; mayúsculas al iniciar una oración y al escribir sustantivos propios; punto al finalizar una oración; signos de interrogación y exclamación al inicio y final de preguntas y exclamaciones (Unidad 2)."}]},
{"curso":3, "oa":[
    {"id":12, "descripcion":"Escribir frecuentemente, para desarrollar la creatividad y expresar sus ideas, textos como poemas, diarios de vida, cuentos, anécdotas, cartas, comentarios sobre sus lecturas, etc."},
    {"id":13, "descripcion":"Escribir creativamente narraciones (experiencias personales, relatos de hechos, cuentos, etc.) que incluyan: una secuencia lógica de eventos; inicio, desarrollo y desenlace; conectores adecuados."},
    {"id":14, "descripcion":"Escribir artículos informativos para comunicar información sobre un tema: organizando las ideas en párrafos; desarrollando las ideas mediante información que explica el tema."},
    {"id":15, "descripcion":"Escribir cartas, instrucciones, afiches, reportes de una experiencia, entre otros, para lograr diferentes propósitos: usando un formato adecuado; transmitiendo el mensaje con claridad."},
    {"id":16, "descripcion":"Escribir con letra clara para que pueda ser leída por otros con facilidad."},
    {"id":17, "descripcion":"Planificar la escritura: estableciendo propósito y destinatario; generando ideas a partir de conversaciones, investigaciones, lluvia de ideas u otra estrategia."},
    {"id":18, "descripcion":"Escribir, revisar y editar sus textos para satisfacer un propósito y transmitir sus ideas con claridad. Durante este proceso: organizan las ideas en párrafos separados con punto aparte; utilizan conectores apropiados; utilizan un vocabulario variado; mejoran la redacción del texto a partir de sugerencias de los pares y el docente; corrigen la ortografía y la presentación."},
    {"id":19, "descripcion":"Incorporar de manera pertinente en la escritura el vocabulario nuevo extraído de textos escuchados o leídos."},
    {"id":20, "descripcion":"Comprender la función de los artículos, sustantivos y adjetivos en textos orales y escritos, y reemplazarlos o combinarlos de diversas maneras para enriquecer o precisar sus producciones."},
    {"id":21, "descripcion":"Comprender la función de los pronombres en textos orales y escritos, y usarlos para ampliar las posibilidades de referirse a un sustantivo en sus producciones."},
    {"id":22, "descripcion":"Escribir correctamente para facilitar la comprensión por parte del lector, aplicando lo aprendido en años anteriores y usando de manera apropiada: mayúsculas al iniciar una oración y al escribir sustantivos propios; punto al finalizar una oración y punto aparte al finalizar un párrafo; plurales de palabras terminadas en z; palabras con ge-gi, je-ji; palabras terminadas en cito-cita; coma en enumeración."}]},
{"curso":4, "oa":[
    {"id":11, "descripcion":"Escribir frecuentemente, para desarrollar la creatividad y expresar sus ideas, textos como poemas, diarios de vida, cuentos, anécdotas, cartas, comentarios sobre sus lecturas, noticias, etc."},
    {"id":12, "descripcion":"Escribir creativamente narraciones (experiencias personales, relatos de hechos, cuentos, etc.) que incluyan: una secuencia lógica de eventos; inicio, desarrollo y desenlace; conectores adecuados; descripciones; un lenguaje expresivo para desarrollar la acción."},
    {"id":13, "descripcion":"Escribir artículos informativos para comunicar información sobre un tema: presentando el tema en una oración; desarrollando una idea central por párrafo; utilizando sus propias palabras."},
    {"id":14, "descripcion":"Escribir cartas, instrucciones, afiches, reportes de una experiencia o noticias, entre otros, para lograr diferentes propósitos: usando un formato adecuado; transmitiendo el mensaje con claridad."},
    {"id":15, "descripcion":"Escribir con letra clara para que pueda ser leída por otros con facilidad."},
    {"id":16, "descripcion":"Planificar la escritura: estableciendo propósito y destinatario; generando ideas a partir de conversaciones, investigaciones, lluvia de ideas u otra estrategia."},
    {"id":17, "descripcion":"Escribir, revisar y editar sus textos para satisfacer un propósito y transmitir sus ideas con claridad. Durante este proceso: organizan las ideas en párrafos separados con punto aparte; utilizan conectores apropiados; emplean un vocabulario preciso y variado; adecuan el registro al propósito del texto y al destinatario; mejoran la redacción del texto a partir de sugerencias de los pares y el docente; corrigen la ortografía y la presentación."},
    {"id":18, "descripcion":"Incorporar de manera pertinente en la escritura el vocabulario nuevo extraído de textos escuchados o leídos."},
    {"id":19, "descripcion":"Comprender la función de los adverbios en textos orales y escritos, y reemplazarlos o combinarlos para enriquecer o precisar sus producciones."},
    {"id":20, "descripcion":"Comprender la función de los verbos en textos orales y escritos, y usarlos manteniendo la concordancia con el sujeto."},
    {"id":21, "descripcion":"Escribir correctamente para facilitar la comprensión por parte del lector, aplicando todas las reglas de ortografía literal y puntual aprendidas en años anteriores, además de: palabras con b-v; palabras con h de uso frecuente; escritura de ay, hay, ahí; acentuación de palabras agudas, graves, esdrújulas y sobreesdrújulas."}]},
{"curso":5, "oa":[
    {"id":13, "descripcion":"Escribir frecuentemente, para desarrollar la creatividad y expresar sus ideas, textos como poemas, diarios de vida, cuentos, anécdotas, cartas, blogs, etc."},
    {"id":14, "descripcion":"Escribir creativamente narraciones (relatos de experiencias personales, noticias, cuentos, etc.) que: tengan una estructura clara; utilicen conectores adecuados; incluyan descripciones y diálogo (si es pertinente) para desarrollar la trama, los personajes y el ambiente."},
    {"id":15, "descripcion":"Escribir artículos informativos para comunicar información sobre un tema: presentando el tema en una oración; desarrollando una idea central por párrafo; agregando las fuentes utilizadas."},
    {"id":16, "descripcion":"Escribir frecuentemente para compartir impresiones sobre sus lecturas, desarrollando un tema relevante del texto leído y fundamentando sus comentarios con ejemplos."},
    {"id":17, "descripcion":"Planificar sus textos: estableciendo propósito y destinatario; generando ideas a partir de sus conocimientos e investigación; organizando las ideas que compondrán su escrito."},
    {"id":18, "descripcion":"Escribir, revisar y editar sus textos para satisfacer un propósito y transmitir sus ideas con claridad. Durante este proceso: desarrollan las ideas agregando información; emplean un vocabulario preciso y variado, y un registro adecuado; releen a medida que escriben; aseguran la coherencia y agregan conectores; editan, en forma independiente, aspectos de ortografía y presentación; utilizan las herramientas del procesador de textos para buscar sinónimos, corregir ortografía y gramática, y dar formato (cuando escriben en computador)."},
    {"id":19, "descripcion":"Incorporar de manera pertinente en la escritura el vocabulario nuevo extraído de textos escuchados o leídos."},
    {"id":20, "descripcion":"Distinguir matices entre sinónimos al leer, hablar y escribir para ampliar su comprensión y capacidad expresiva."},
    {"id":21, "descripcion":"Conjugar correctamente los verbos regulares al utilizarlos en sus producciones escritas."},
    {"id":22, "descripcion":"Escribir correctamente para facilitar la comprensión por parte del lector, aplicando las reglas ortográficas aprendidas en años anteriores, además de: uso de c-s-z; raya para indicar diálogo; acento diacrítico y dierético; coma en frases explicativas."}]},
{"curso":6, "oa":[
    {"id":13, "descripcion":"Escribir frecuentemente, para desarrollar la creatividad y expresar sus ideas, textos como poemas, diarios de vida, cuentos, anécdotas, cartas, blogs, etc."},
    {"id":14, "descripcion":"Escribir creativamente narraciones (relatos de experiencias personales, noticias, cuentos, etc.) que: tengan una estructura clara; utilicen conectores adecuados; tengan coherencia en sus oraciones; incluyan descripciones y diálogo (si es pertinente) que desarrollen la trama, los personajes y el ambiente."},
    {"id":15, "descripcion":"Escribir artículos informativos para comunicar información sobre un tema: organizando el texto en una estructura clara; desarrollando una idea central por párrafo; agregando las fuentes utilizadas."},
    {"id":16, "descripcion":"Escribir frecuentemente para compartir impresiones sobre sus lecturas, desarrollando un tema relevante del texto leído y fundamentando sus comentarios con ejemplos."},
    {"id":17, "descripcion":"Planificar sus textos: estableciendo propósito y destinatario; generando ideas a partir de sus conocimientos e investigación; organizando las ideas que compondrán su escrito."},
    {"id":18, "descripcion":"Escribir, revisar y editar sus textos para satisfacer un propósito y transmitir sus ideas con claridad. Durante este proceso: agregan ejemplos, datos y justificaciones para profundizar las ideas; emplean un vocabulario preciso y variado, y un registro adecuado; releen a medida que escriben; aseguran la coherencia y agregan conectores; editan, en forma independiente, aspectos de ortografía y presentación; utilizan las herramientas del procesador de textos para buscar sinónimos, corregir ortografía y gramática, y dar formato (cuando escriben en computador)."},
    {"id":19, "descripcion":"Incorporar de manera pertinente en la escritura el vocabulario nuevo extraído de textos escuchados o leídos."},
    {"id":20, "descripcion":"Ampliar su capacidad expresiva, utilizando los recursos que ofrece el lenguaje para expresar un mismo mensaje de diversas maneras; por ejemplo: sinónimos, hipónimos e hiperónimos, locuciones, comparaciones, otros."},
    {"id":21, "descripcion":"Utilizar correctamente los participios irregulares (por ejemplo, roto, abierto, dicho, escrito, muerto, puesto, vuelto) en sus producciones escritas."},
    {"id":22, "descripcion":"Escribir correctamente para facilitar la comprensión por parte del lector, aplicando todas las reglas de ortografía literal, acentual y puntual aprendidas en años anteriores, además de: escritura de los verbos haber, tener e ir, en los tiempos más utilizados; coma en frases explicativas; coma en presencia de conectores que la requieren; acentuación de pronombres interrogativos y exclamativos."}]},
{"curso":7, "oa":[
    {"id":12, "descripcion":"Expresarse en forma creativa por medio de la escritura de textos de diversos géneros (por ejemplo, cuentos, crónicas, diarios de vida, cartas, poemas, etc.), escogiendo libremente: El tema. El género. El destinatario."},
    {"id":13, "descripcion":"Escribir, con el propósito de explicar un tema, textos de diversos géneros (por ejemplo, artículos, informes, reportajes, etc.), caracterizados por: Una presentación clara del tema. La presencia de información de distintas fuentes. La inclusión de hechos, descripciones, ejemplos o explicaciones que desarrollen el tema. Una progresión temática clara, con especial atención al empleo de recursos anafóricos. El uso de imágenes u otros recursos gráficos pertinentes. Un cierre coherente con las características del género. El uso de referencias según un formato previamente acordado."},
    {"id":14, "descripcion":"Escribir, con el propósito de persuadir, textos breves de diversos géneros (por ejemplo, cartas al director, editoriales, críticas literarias, etc.), caracterizados por: La presentación de una afirmación referida a temas contingentes o literarios. La presencia de evidencias e información pertinente. La mantención de la coherencia temática."},
    {"id":15, "descripcion":"Planificar, escribir, revisar, reescribir y editar sus textos en función del contexto, el destinatario y el propósito: Recopilando información e ideas y organizándolas antes de escribir. Adecuando el registro, específicamente el vocabulario (uso de términos técnicos, frases hechas, palabras propias de las redes sociales, términos y expresiones propios del lenguaje hablado), el uso de la persona gramatical y la estructura del texto al género discursivo, contexto y destinatario. Incorporando información pertinente. Asegurando la coherencia y la cohesión del texto. Cuidando la organización a nivel oracional y textual. Usando conectores adecuados para unir las secciones que componen el texto. Usando un vocabulario variado y preciso. Reconociendo y corrigiendo usos inadecuados, especialmente de pronombres personales y reflejos, conjugaciones verbales, participios irregulares, y concordancia sujeto-verbo, artículo-sustantivo y sustantivo-adjetivo. Corrigiendo la ortografía y mejorando la presentación. Usando eficazmente las herramientas del procesador de textos."},
    {"id":16, "descripcion":"Aplicar los conceptos de oración, sujeto y predicado con el fin de revisar y mejorar sus textos: Produciendo consistentemente oraciones completas. Conservando la concordancia entre sujeto y predicado. Ubicando el sujeto para determinar de qué o quién se habla."},
    {"id":17, "descripcion":"Usar en sus textos recursos de correferencia léxica: Empleando adecuadamente la sustitución léxica, la sinonimia y la hiperonimia. Reflexionando sobre las relaciones de sinonimia e hiperonimia y su papel en la redacción de textos cohesivos y coherentes."},
    {"id":18, "descripcion":"Escribir correctamente para facilitar la comprensión al lector: Aplicando todas las reglas de ortografía literal y acentual. Verificando la escritura de las palabras cuya ortografía no está sujeta a reglas. Usando correctamente punto, coma, raya y dos puntos."}]},
{"curso":8, "oa":[
    {"id":13, "descripcion":"Expresarse en forma creativa por medio de la escritura de textos de diversos géneros (por ejemplo, cuentos, crónicas, diarios de vida, cartas, poemas, etc.), escogiendo libremente: --El tema. --El género. --El destinatario."},
    {"id":14, "descripcion":"Escribir, con el propósito de explicar un tema, textos de diversos géneros (por ejemplo, artículos, informes, reportajes, etc.) caracterizados por: --Una presentación clara del tema en que se esbozan los aspectos que se abordarán. --La presencia de información de distintas fuentes. --La inclusión de hechos, descripciones, ejemplos o explicaciones que desarrollen el tema. --Una progresión temática clara, con especial atención al empleo de recursos anafóricos. --El uso de imágenes u otros recursos gráficos pertinentes. --Un cierre coherente con las características del género. --El uso de referencias según un formato previamente acordado."},
    {"id":15, "descripcion":"Escribir, con el propósito de persuadir, textos breves de diversos géneros (por ejemplo, cartas al director, editoriales, críticas literarias, etc.), caracterizados por: --La presentación de una afirmación referida a temas contingentes o literarios. --La presencia de evidencias e información pertinente. --La mantención de la coherencia temática."},
    {"id":16, "descripcion":". Planificar, escribir, revisar, reescribir y editar sus textos en función del contexto, el destinatario y el propósito: --Recopilando información e ideas y organizándolas antes de escribir. --Adecuando el registro, específicamente, el vocabulario (uso de términos técnicos, frases hechas, palabras propias de las redes sociales, términos y expresiones propios del lenguaje hablado), el uso de la persona gramatical, y la estructura del texto al género discursivo, contexto y destinatario. --Incorporando información pertinente. --Asegurando la coherencia y la cohesión del texto. --Cuidando la organización a nivel oracional y textual. --Usando conectores adecuados para unir las secciones que componen el texto y relacionando las ideas dentro de cada párrafo. --Usando un vocabulario variado y preciso. --Reconociendo y corrigiendo usos inadecuados, especialmente de pronombres personales y reflejos, conjugaciones verbales, participios irregulares, y concordancia sujeto-verbo, artículo-sustantivo y sustantivo-adjetivo. --Corrigiendo la ortografía y mejorando la presentación. --Usando eficazmente las herramientas del procesador de textos."},
    {"id":17, "descripcion":"Usar adecuadamente oraciones complejas: --Manteniendo un referente claro. --Conservando la coherencia temporal. --Ubicando el sujeto, para determinar de qué o quién se habla."},
    {"id":18, "descripcion":"Construir textos con referencias claras: --Usando recursos de correferencia como deícticos -en particular, pronombres personales tónicos y átonos- y nominalización, sustitución pronominal y elipsis, entre otros. --Analizando si los recursos de correferencia utilizados evitan o contribuyen a la pérdida del referente, cambios de sentido o problemas de estilo."},
    {"id":19, "descripcion":"Conocer los modos verbales, analizar sus usos y seleccionar el más apropiado para lograr un efecto en el lector, especialmente al escribir textos con finalidad persuasiva."},
    {"id":20, "descripcion":"Escribir correctamente para facilitar la comprensión al lector: --Aplicando todas las reglas de ortografía literal y acentual. --Verificando la escritura de las palabras cuya ortografía no está sujeta a reglas. --Usando correctamente punto, coma, raya y dos puntos."}]}
]');

/**
 * Function to create the table for rubrics
 *
 * @param string $id        	
 *
 * @return the table with the rubric's data
 */
function show_rubric($id) {
	global $DB;
	$sql = "SELECT grl.id,
			 grc.id as grcid,
			 grl.score,
			 grl.definition,
			 grc.description,
			 grc.sortorder,
			 gd.name
	  FROM mdl_gradingform_rubric_levels as grl,
	 	   mdl_gradingform_rubric_criteria as grc,
    	   mdl_grading_definitions as gd
	  WHERE gd.id='$id' AND grc.definitionid=gd.id AND grc.id=grl.criterionid
	  ORDER BY grcid, grl.id";
	
	$rubric = $DB->get_records_sql ( $sql );
	
	foreach ( $rubric as $data ) {
		
		$tableData [$data->description] [$data->definition] = $data->score;
	}
	
	$col = 0;
	foreach ( $tableData as $calc ) {
		
		$actualcol = sizeof ( $calc );
		if ($col < $actualcol) {
			$col = $actualcol;
		}
	}
	
	$table = "";
	$table .= '<table class="table table-bordered">';
	$table .= '<thead>';
	$table .= '<tr>';
	$table .= '<td>';
	$table .= '</td>';
	
	for($i = 1; $i <= $col; $i ++) {
		$table .= '<th>Nivel ' . $i . '</th>';
	}
	
	$table .= '</tr>';
	$table .= '</thead>';
	$table .= '<tbody>';
	
	foreach ( $tableData as $key => $value ) {
		
		$table .= '<tr>';
		$table .= '<th>' . $key . '</th>';
		foreach ( $value as $level => $score ) {
			$table .= '<th>' . $level . '</th>';
		}
		
		$table .= '</tr>';
	}
	$table .= '</tbody>';
	$table .= '</table>';
	
	return $table;
}
/**
 * Gets four random activities to show in the home page
 * @return moodle_url[][]|NULL[][]
 */
function emarking_get_random_activities() {
    global $DB, $CFG;
    
    $query = "SELECT a.id, g.name genrename, a.description, a.title
		FROM {emarking_activities} a
        LEFT JOIN {emarking_activities_genres} g ON (g.id = a.genre)
		WHERE status = 1 AND parent IS NULL
		ORDER BY RAND()
		LIMIT 4";
    
    $activities = $DB->get_records_sql($query);
    $activityArray = Array();
    foreach ($activities as $activity){
        $url = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/activity.php', array('id'=>$activity->id));
        $activityArray[] = Array(
            'title'=>$activity->title,
            'genre'=>$activity->genrename,
            'description'=>$activity->description,
            'link'=>$url
        );
    }
    return $activityArray;
}

function activities_show_result($data, $genreclass) {
	GLOBAL $CFG, $DB;
	
	$data->url = new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/activity.php', array (
			'id' => $data->id 
	) );
	$coursesOA=oas_string($data);
	
	//Busca toda la información de la comunidad en esta actividad
	$communitysql = $DB->get_record('emarking_social', array('activityid' => $data->id));	
	if( !$communitysql ){	
		$communitysql=new stdClass ();
		$communitysql->activityid 			= $data->id;
		$communitysql->timecreated         	= time();
		$communitysql->data					= null;
		$DB->insert_record ( 'emarking_social', $communitysql );
		$average = 0;
	}
	$countvotes = 0;
	$countcomments = 0;
	$average = 0;
	if( isset($communitysql->data) && $communitysql->data != null ){
		$recordcleaned = emarking_activities_clean_string_to_json($communitysql->data);
		$decode = json_decode($recordcleaned);
		$social = $decode->data;
		$comments = is_array($social->Comentarios) ? $social->Comentarios : array();
		$votes = is_array($social->Vote) ? $social->Vote : array();
		$countvotes = count($votes);
		$countcomments = count($comments);
		if($countvotes > 0){
			$average = get_average($votes);
		}		
	}
	include ($CFG->dirroot. '/mod/emarking/activities/views/showresult.php');
}

/**
 * Creates a pdf from selected activity.
 *
 * @param unknown $activityid        	
 * @return boolean|multitype:unknown NULL Ambigous <boolean, number>
 */
function emarking_get_pdf_activity($activity, $download = false, $sections = null) {
	GLOBAL $USER,$CFG, $DB;
	require_once ($CFG->libdir . '/pdflib.php');
	require_once ($CFG->dirroot . "/mod/emarking/print/locallib.php");	
	
	$user_object = $DB->get_record('user', array('id' => $activity->userid));
	
	$usercontext=context_user::instance($user_object->id);
	
	$fs = get_file_storage();
	// create new PDF document
	
	$pdf = emarking_create_activity_pdf($user_object, $activity);
	
	if(isset($sections->header) && $sections->header == 1) {
		$pdf->writeHTML('<h1>'.$activity->title.'</h1> ', true, false, false, false, '');
	}
	if(isset($sections->instructions)&&$sections->instructions==1){
		$pdf->writeHTML('<h3>Instrucciones</h3> ', true, false, false, false, '');
		$instructionshtml=emarking_activities_add_images_pdf($activity->instructions);
		$instructionshtml=emarking__activities_clean_html_to_print($instructionshtml);
		$pdf->writeHTML($instructionshtml, true, false, false, false, '');
	}
	
	if(isset($sections->planification)&&$sections->planification==1) {
		$planificationhtml=emarking_activities_add_images_pdf($activity->planification);
		$pdf->writeHTML('<h3>Planificación</h3>', true, false, false, false, '');
		$planificationhtml=emarking__activities_clean_html_to_print($planificationhtml);
		$pdf->writeHTML($planificationhtml, true, false, false, false, '');
	}
	
	if(isset($sections->writing)&&$sections->writing==1) {
		$writinghtml=emarking_activities_add_images_pdf($activity->writing);
		$writinghtml=emarking__activities_clean_html_to_print($writinghtml);
	
		$pdf->writeHTML('<h3>Escritura</h3>', true, false, false, false, '');
		$pdf->writeHTML($writinghtml, true, false, false, false, '');
	
		emarking_pdf_fill_writing_table($pdf);	
		$pdf->AddPage();
		
		$height = 0;
		if(isset($sections->editing) && $sections->editing==1){
			$editinghtml=emarking_activities_add_images_pdf($activity->editing);
			$editinghtml=emarking__activities_clean_html_to_print($editinghtml);
			$pdf2 = emarking_create_activity_pdf($user_object, $activity);
			$height = $pdf2->GetY();
			$pdf2->writeHTML('<h3>Revisión y edición</h3>', true, false, false, false, '');
			$pdf2->writeHTML($editinghtml, true, false, false, false, '');
			$height = $pdf2->getY() - $height;
			$pdf2->Close();
			$pdf2 = null;
			emarking_pdf_fill_writing_table($pdf, $height);
			$pdf->writeHTML('<h3>Revisión y edición</h3>', true, false, false, false, '');
			$pdf->writeHTML($editinghtml, true, false, false, false, '');
		} else {
			emarking_pdf_fill_writing_table($pdf);	
		}
	}
	
	if(isset($sections->teaching) && $sections->teaching==1) {
		$teachinghtml=emarking_activities_add_images_pdf($activity->teaching);
		$pdf->writeHTML('<h3>Sugerencias didácticas</h3>', true, false, false, false, '');
		$teachinghtml=emarking__activities_clean_html_to_print($teachinghtml);
		$pdf->writeHTML($teachinghtml, true, false, false, false, '');
	}
	
	if(isset($sections->resources) && $sections->resources==1) {
		$languageresourceshtml=emarking_activities_add_images_pdf($activity->languageresources);
		$pdf->writeHTML('<h3>Recursos del lenguaje</h3>', true, false, false, false, '');
		$languageresourceshtml=emarking__activities_clean_html_to_print($languageresourceshtml);
		$pdf->writeHTML($languageresourceshtml, true, false, false, false, '');
	}
	
	if(isset($sections->rubric) && $sections->rubric==1) {
		$pdf->AddPage();
		$rubrichtml=show_rubric($activity->rubricid);
		$pdf->writeHTML('<h3>Evaluación</h3>', true, false, false, false, '');
		$rubrichtml=emarking__activities_clean_html_to_print($rubrichtml);
		$pdf->writeHTML($rubrichtml, true, false, false, false, '');
	}
	
	if($download==true){
		$pdf->Output($activity->title.'.pdf', 'D');
		
	} else{
		$tempdir = emarking_get_temp_dir_path($activity->id);
		if (!file_exists($tempdir)) {
			emarking_initialize_directory($tempdir, true);
		}
		$pdffilename=$activity->title.'.pdf';
		$pathname = $tempdir . '/' . $pdffilename;
		if (@file_exists($pathname)) {
			unlink($pathname);
		}
		$numpages = $pdf->getNumPages();
		 $pdf->Output($pathname, 'F');
		
		$itemid=rand(1,32767);
		$filerecord = array(
		 		'contextid' => $usercontext->id,
		 		'component' => 'user',
		 		'filearea' => 'exam_files',
		 		'itemid' => $itemid,
		 		'filepath' => '/',
		 		'filename' => $pdffilename,
		 		'timecreated' => time(),
		 		'timemodified' => time(),
		 		'author' =>'pepito',
		 		'license' => 'allrightsreserved'
		 );
		 // Si el archivo ya existía entonces lo borramos.
		 if ($fs->file_exists($usercontext->id, 'mod_emarking', 'user', $itemid, '/', $pdffilename)) {
		 	$contents = $file->get_content();
		 }
		 $fileinfo = $fs->create_file_from_pathname($filerecord, $pathname);
	
		 $filedata [] = array(
		 		'pathname' => $pathname,
		 		'filename' => $pdffilename
		 );
		 
		return array (
			'itemid' => $itemid,
			'numpages' => $numpages,
			'filedata' => $filedata,
			'activitytitle' => $activity->title,
			'rubricid' => $activity->rubricid
		);
	}
}

function emarking_create_activity_pdf($user_object, $activity) {
	global $USER;
	
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	
	// set document information
	$pdf->SetCreator($USER->firstname.' '.$USER->lastname);
	$pdf->SetAuthor($user_object->firstname.' '.$user_object->lastname);
	$pdf->SetTitle($activity->title);
	$pdf->SetPrintHeader(false);
	$pdf->SetPrintFooter(false);
	$pdf->SetFont('times', '', 12);
	
	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, 10);
	$pdf->SetTopMargin(40);
	$pdf->SetRightMargin(10);
	$pdf->SetLeftMargin(10);
	// Add a page
	// This method has several options, check the source code documentation for more information.
	$pdf->AddPage();
	$css='
	
<style>
   body {
        font-family: "Trebuchet MS", "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", Tahoma, sans-serif;
    }
	p {
		font-size: 12pt;
	}
	h3, p {
		margin-top: 1px;
		margin-bottom: 1px;
	}
	table {
    	border: 1px solid black;
	}
	td {
		width: 100%;
		border-bottom: 1px dotted #999;
		height: 10px;
	}
    </style>
   ';
	$html =$css;
	
	$pdf->writeHTML($css, true, false, false, false, '');
	
	return $pdf;
}
function emarking_pdf_fill_writing_table(TCPDF $pdf, $saveHeight = 0) {
	$footermargin = $pdf->getFooterMargin();
	$pdf->SetAutoPageBreak(false);
	$linewidth = emarking_pdf_linewidth($pdf);
	$lineheight = 8;
	$spaceleft = emarking_pdf_spaceleft($pdf) - $saveHeight;
	
	$rows = $spaceleft / ($lineheight + 1);
	$bordercolorrgb = array(0, 0, 0);
	$linecolorrgb = array(0, 0, 0);
	for($i=0; $i<$rows; $i++) {
		if($i==0)
			$pdf->Cell($linewidth, $lineheight,' ',
					array(
							'TRL'=>
							array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $bordercolorrgb),
							'B'=>
							array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => $linecolorrgb)
					)
					,1);
			elseif($i<$rows-1)
			$pdf->Cell($linewidth, $lineheight,' ',
					array(
							'RL'=>
							array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $bordercolorrgb),
							'B'=>
							array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => $linecolorrgb)
					)
					,1);
			else
				$pdf->Cell($linewidth, $lineheight,' ',
						array(
								'BRL'=>
								array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $bordercolorrgb)
						)
						,1);
	}
	$pdf->SetAutoPageBreak(true, $footermargin);
}

function emarking_pdf_spaceleft(TCPDF $pdf) {
	$currentY = $pdf->GetY();
	$fmargin = $pdf->getFooterMargin();
	$height = $pdf->getPageHeight();
	$spaceleft = $height - $fmargin - $currentY;
	return $spaceleft;
}
function emarking_pdf_linewidth(TCPDF $pdf) {
	$lmargin = $pdf->getMargins()['left'];
	$rmargin = $pdf->getMargins()['right'];
	$width = $pdf->getPageWidth();
	$linewidth = $width - $lmargin - $rmargin;
	return $linewidth;
}
/**
 * Creates a new instance of emarking, with the data obteined in 
 * the bank of activities.
 *
 * @param object data
 *        	An object from data of the new instance emarking
 * @param inst $destinationcourse
 * 			The course where the instance will be create        	
 * @return int $itemid
 * 			The id of the pdf for emarking
 */
function emarking_create_activity_instance(stdClass $data,$destinationcourse,$itemid,$numpages,$filedata) {
	global $DB, $CFG, $COURSE, $USER;
	require_once ($CFG->dirroot . "/course/lib.php");
	
	require_once ($CFG->dirroot . "/mod/emarking/lib.php");
	require_once ($CFG->dirroot . "/mod/emarking/print/locallib.php");
	
	$emarkingmod = $DB->get_record ( 'modules', array (
			'name' => 'emarking'
	) );

	$data->id = null;
	$data->course = $destinationcourse;
	
	$data->timecreated = time ();
	$id = $DB->insert_record ( 'emarking', $data );
	$data->id = $id;
	$course = $data->course;
	emarking_grade_item_update ( $data );
	

	// entregar id del curso
	$context = context_course::instance ( $course );
	
	$examfiles = $filedata;

	// If there's no previous exam to associate, and we are creating a new
	// EMarking, we need the PDF file.
	
	$studentsnumber = emarking_get_students_count_for_printing ( $course );
	
	// A new exam object is created and its attributes filled from form data.
	 
		$exam = new stdClass ();
		$exam->course = $course;
		$exam->courseshortname = $COURSE->shortname;
		$exam->name = $data->name;
		$exam->examdate = time();
		$exam->emarking = $id;
		$exam->headerqr = 1;
		$exam->printrandom = 0;
		$exam->printlist = 0;
		$exam->extrasheets = 0;
		$exam->extraexams = 0;
		$exam->usebackside = 0;
		$exam->timecreated = time ();
		$exam->timemodified = 0;
		$exam->requestedby = $USER->id;
		$exam->totalstudents = $studentsnumber;
		$exam->comment = "comment";
		// Get the enrolments as a comma separated values.
		$exam->enrolments = "manual";
		$exam->printdate = 0;
		$exam->status = 10;
		// Calculate total pages for exam.
		$exam->totalpages = $numpages;
		$exam->printingcost = 0;
		$exam->id = $DB->insert_record ( 'emarking_exams', $exam );
		$fs = get_file_storage ();
		foreach ( $examfiles as $exampdf ) {
			
			// Save the submitted file to check if it's a PDF.
			$filerecord = array (
					'component' => 'mod_emarking',
					'filearea' => 'exams',
					'contextid' => $context->id,
					'itemid' => $exam->id,
					'filepath' => '/',
					'filename' => $exampdf ['filename'] 
			);
			$file = $fs->create_file_from_pathname ( $filerecord, $exampdf ['pathname'] );
		}
		// Update exam object to store the PDF's file id.
		$exam->file = $file->get_id ();
		if (! $DB->update_record ( 'emarking_exams', $exam )) {
			$fs->delete_area_files ( $contextid, 'emarking', 'exams', $exam->id );
			print_error ( get_string ( 'errorsavingpdf', 'mod_emarking' ) );
		}
	
	$headerqr = 1;
	setcookie ( "emarking_headerqr", $headerqr, time () + 3600 * 24 * 365 * 10, '/' );
	$defaultexam = new stdClass ();
	$defaultexam->headerqr = $exam->headerqr;
	$defaultexam->printrandom = $exam->printrandom;
	$defaultexam->printlist = $exam->printlist;
	$defaultexam->extrasheets = $exam->extrasheets;
	$defaultexam->extraexams = $exam->extraexams;
	$defaultexam->usebackside = $exam->usebackside;
	$defaultexam->enrolments = $exam->enrolments;
	setcookie ( "emarking_exam_defaults", json_encode ( $defaultexam ), time () + 3600 * 24 * 365 * 10, '/' );
	
	$mod = new stdClass ();
	$mod->course = $destinationcourse;
	$mod->module = $emarkingmod->id;
	$mod->instance = $data->id;
	$mod->section = 0;
	$mod->visible = 1; // Hide the forum.
	$mod->visibleold = 0; // Hide the forum.
	$mod->groupmode = 0;
	$mod->grade = 100;
	if (! $cmid = add_course_module ( $mod )) {
		return false;
	}
	$sectionid = course_add_cm_to_section ( $mod->course, $cmid, 0 );
	return array (
			'id'=>$data->id,
			'cmid'=>$cmid,
			'sectionid'=>$sectionid
	);
}
function emarking_activities_add_images_pdf($html){
	global $DB, $CFG, $OUTPUT;

	$tmpdir = random_string();
	// Inclusión de librerías
	require_once ($CFG->dirroot . '/mod/emarking/orm/locallib.php');
	require_once ($CFG->dirroot . '/mod/emarking/print/locallib.php');
	$filedir = $CFG->dataroot . "/temp/emarking/$tmpdir";
	emarking_initialize_directory($filedir, false);

	$fileimg = $CFG->dataroot . "/temp/emarking/$tmpdir/images";
	emarking_initialize_directory($fileimg, false);


	$fullhtml = array();
	$numanswers = array();
	$attemptids = array();
	$images = array();
	$imageshtml = array();

				$currentimages = emarking_extract_images_url($html);
				$idx = 0;
				foreach ($currentimages[1] as $imageurl) {
					if (! array_search($imageurl, $images)) {
						$images[] = $imageurl;
						$imageshtml[] = $currentimages[0][$idx];
					}
					$idx ++;
				}
				
	// Bajar las imágenes del HTML a dibujar
	$search = array();
	$replace = array();
	$replaceweb = array();
	$imagesize = array();
	$idx = 0;
			
	foreach ($images as $image) {
		
			if (! list ($filename, $imageinfo) = emarking_activities_get_file_from_url($image, $fileimg)) {
				echo "Problem downloading file $image <hr>";
			} else {
				// Buscamos el src de la imagen
				$search[] = 'src="' . $image . '"';
				$replacehtml = ' src="' . $filename . '"';
				$replacehtmlxweb = ' src="' . $image . '"';
				// Si el html de la misma contiene ancho o alto, se deja tal cual
				$imghtml = $imageshtml[$idx];
				if (substr_count($imghtml, "width") + substr_count($imghtml, "height") == 0) {
					$width = $imageinfo[0];
					$height = $imageinfo[1];
					$ratio = floatval(10) / floatval($height);
					$height = 10;
					$width = (int) ($ratio * floatval($width));
					$sizehtml = 'width="' . $width . '" height="' . $height . '"';
					$replacehtml = $sizehtml . ' ' . $replacehtml;
					$replacehtmlxweb = $sizehtml . ' ' . $replacehtmlxweb;
				}
				$replace[] = $replacehtml;
				$replaceweb[] = $replacehtmlxweb;
				$imagesize[] = $imageinfo;
			}
			$idx ++;
	}
	$fullhtml = str_replace($search, $replace, $html);
	return $fullhtml;
}

/**
 *
 * @param unknown $url
 * @param unknown $pathname
 * @return boolean
 */
function emarking_activities_get_file_from_url($url, $pathname)
{
	// Calculate filename
	$parts = explode('/', $url);
	$filename = $parts[count($parts) - 1];
	 
	$ispluginfile = false;
	$ispixfile = false;
	$index = 0;
	foreach ($parts as $part) {
		if ($part === 'pluginfile.php') {
			$ispluginfile = true;
			break;
		}
		if ($part === 'pix.php') {
			$ispixfile = true;
			break;
		}
		$index ++;
	}

	$fs = get_file_storage();

	// If the file is part of Moodle, we get it from the filesystem
	if ($ispluginfile) {
		$contextid = $parts[$index + 1];
		$component = $parts[$index + 2];
		$filearea = $parts[$index + 3];
		$itemid = $parts[$index + 4];
		$filepath = '/';
		if ($fs->file_exists($contextid, $component, $filearea, $itemid, $filepath, $filename)) {
			$file = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);
		
			$file->copy_content_to($pathname . $filename);		
			$imageinfo = getimagesize($pathname . $filename);
			return array(
					$pathname . $filename,
					$imageinfo
			);
		}
		return false;
	}

	// Open binary stream and read it
	$handle = fopen($url, "rb");
	$content = stream_get_contents($handle);
	fclose($handle);

	// Save the binary file
	$file = fopen($pathname . $filename, "wb+");
	fputs($file, $content);
	fclose($file);

	$imageinfo = getimagesize($pathname . $filename);
	return array(
			$pathname . $filename,
			$imageinfo
	);
}
/**
 * Limpia el HTML producido por una pregunta de un quiz
 *
 * @param String $html
 * @return String
 */
function emarking__activities_clean_html_to_print($html)
{
	$html = preg_replace ( '!\s+!', ' ', $html );
	$html = preg_replace('/<tbody\s*>/', '', $html);
	$html = preg_replace('/<\/tbody>/', '', $html);
	$html = preg_replace('/<td(.*?)>/', '<td>', $html);
	$html = preg_replace('/border="\d+"/', '', $html);
	$html = preg_replace('/<table(.*?)>/', '<br/><table border="1">', $html);
	$html = preg_replace('/<div>(<input.*?)<\/div>/', '<br/>$1', $html);

	return $html;
}
/**
 * Limpia el texto de una actividad
 *
 * @param String $html
 * @return String
 */
function emarking_activities_clean_html_text($html)
{
	$html = preg_replace ( '!\s+!', ' ', $html );
	$html = preg_replace ( '/<p(.*?)>/', '<p align="justify">', $html);
	$html = preg_replace ( '/<span(.*?)>/', '<span>', $html);
	$html = preg_replace('/<table(.*?)>/', '<table class="table table-bordered">', $html);
	$html = preg_replace ( '/<td(.*?)>/', '<td>', $html);
	$html = preg_replace ( '/<tbody(.*?)>/', '', $html );
	$html = preg_replace ( '/<td> <\/td>/', '', $html);
	$html = preg_replace ( '/<h1(.*?)>/', '<h1>', $html );
	$html = preg_replace ( '/<h2(.*?)>/', '<h2>', $html );
	$html = preg_replace ( '/<h3(.*?)>/', '<h3>', $html );
	$html = preg_replace ( '/<h4(.*?)>/', '<h4>', $html );
	$html = preg_replace ( '/<h5(.*?)>/', '<h5>', $html );
	
	return $html;
}
/**
 * Limpia una cadena de string para ser transformado en json
 *
 * @param String $html
 * @return String
 */
function emarking_activities_clean_string_to_json($string) {
	$bodytag = str_replace ( '"[\\', "[", $string );
	$bodytag2 = str_replace ( '\\"', '"', $bodytag );
	$bodytag3 = str_replace ( ']"', ']', $bodytag2 );
	$bodytag4 = str_replace ( '"[', '[', $bodytag3 );
	
	return $bodytag4;
}
function rating($userid, $activityid, $stars) {
	global $DB;
	$communitysql = $DB->get_record ( 'emarking_social', array (
			'activityid' => $activityid 
	) );
	
	if (isset ( $communitysql->data ) && $communitysql->data != null) {
		
		$recordcleaned = emarking_activities_clean_string_to_json ( $communitysql->data );
		$decode = json_decode ( $recordcleaned );
		$social = $decode->data;
		$comments = $social->Comentarios;
		$commentsjson = json_encode ( $comments, JSON_UNESCAPED_UNICODE );
		$votes = $social->Vote;
		if (! isset ( $votes )) {
			
			$votes = array (
					array (
							'userid' => $userid,
							'rating' => $stars 
					) 
			);
			$votesjson = json_encode ( $votes, JSON_UNESCAPED_UNICODE );
			$data = Array (
					"Vote" => $votesjson,
					"Comentarios" => $commentsjson 
			)
			;
			$communitysql->data = $data;
			$dataarray = Array (
					"data" => $data 
			);
			$datajson = json_encode ( $dataarray, JSON_UNESCAPED_UNICODE );
			$communitysql->data = $datajson;
			
			$DB->update_record ( 'emarking_social', $communitysql );
			
			return get_average ( $votes );
		} else {
			
			if (if_user_has_voted ( $votes, $userid )) {
				
				$rating = new stdClass ();
				$rating->userid = $userid;
				$rating->rating = $stars;
				$votes [] = $rating;
				$votesjson = json_encode ( $votes, JSON_UNESCAPED_UNICODE );
				$newdata = Array (
						"Vote" => $votes,
						"Comentarios" => $commentsjson 
				);
				
				$dataarray = Array (
						"data" => $newdata 
				);
				
				$datajson = json_encode ( $dataarray, JSON_UNESCAPED_UNICODE );
				$communitysql->data = $datajson;
				
				$DB->update_record ( 'emarking_social', $communitysql );
				return get_average ( $votes );
			}
		}
	} else {
		$votes = array (
				array (
						'userid' => $userid,
						'rating' => $stars 
				) 
		);
		$votesjson = json_encode ( $votes, JSON_UNESCAPED_UNICODE );
		$data = Array (
				"Vote" => $votesjson,
				"Comentarios" => null 
		)
		;
		$dataarray = Array (
				"data" => $data 
		);
		$datajson = json_encode ( $dataarray, JSON_UNESCAPED_UNICODE );
		$communitysql->data = $datajson;
		$voteObject = new stdClass ();
		$voteObject->userid=$userid;
		$voteObject->rating=$stars;
		$arrayVote[]=$voteObject;
		$DB->update_record ( 'emarking_social', $communitysql );

		return get_average ( $arrayVote );
	}
}

function if_user_has_voted($array, $userid) {
	foreach ( $array as $object ) {
		if (isset ( $object->userid ) && $object->userid == $userid){
			return $object->rating;
		}
	}
	return false;
}
function get_average($array) {
	$sum = 0;
	$count = 0;
	foreach ( $array as $object ) {
		$sum = $sum + ( int ) $object->rating;
		$count ++;
	}
	$average = $sum / $count;
	return round($average);
}

function get_criteria($id,$bool=false,$need_in_json=true){
	GLOBAL $DB,$USER;
$sql="SELECT rl.*, rc.description as criterion, i.max
FROM mdl_emarking_rubrics_levels as rl
INNER JOIN mdl_emarking_rubrics_criteria rc ON (rc.id = rl.criterionid )
LEFT JOIN (select criterionid, max(score) as max FROM mdl_emarking_rubrics_levels as rl group by criterionid) as i on (i.criterionid=rl.criterionid)
WHERE rl.criterionid=?
ORDER BY rl.criterionid ASC, rl.score DESC";
$result = $DB->get_records_sql($sql, array($id));
$criteriaarray=Array();
$levelarray=Array();
$levelidarray=Array();

foreach ($result as $level){
	$criteriaarray['criteria']=$level->criterion;
	$levelarray[$level->score]=$level->definition;
	$criteriaarray['maxscore']=$level->max;
	if($bool){
		$criteriaarray['criterionid']=$level->criterionid;
		$levelidarray[$level->score]=$level->id;
	}
}
if (!array_key_exists(1, $levelarray))
	$levelarray[1]="";
if (!array_key_exists(2, $levelarray))
	$levelarray[2]="";
if (!array_key_exists(3, $levelarray))
	$levelarray[3]="";
if (!array_key_exists(4, $levelarray))
	$levelarray[4]="";
krsort($levelarray);
$criteriaarray['levels']=$levelarray;
if($bool){
$criteriaarray['levelids']=$levelidarray;
}
$criteriaarray['bool']=$bool;
if($need_in_json){
	$tojson=json_encode($criteriaarray);
	return $tojson;
}
return $criteriaarray;

}

function insert_rubric($data,$activityid){
	GLOBAL $DB, $USER, $CFG;
	$rubric = new stdClass ();
	$rubric->name = $data ['rubricname'];
	$rubric->description = $data ['rubricdescription'];
	$rubric->usercreated = $USER->id;
	$rubric->timecreated = time ();
	$rubricid = $DB->insert_record ( 'emarking_rubrics', $rubric );
	if(isset($data ['criteria'])&& $criterias = $data ['criteria']){
	foreach ( $criterias as $key => $criteria ) {

		$crit = new stdClass ();
		$crit->rubricid = $rubricid;
		$crit->description = $criteria;
		$inssertcriteria = $DB->insert_record ( 'emarking_rubrics_criteria', $crit );
		if(isset($data ['level'])&& $levels = $data ['level']){
		foreach ( $levels [$key] as $score => $level ) {
			if($level!=null){
				$lev = new stdClass ();
				$lev->criterionid=$inssertcriteria;
				$lev->score=$score;
				$lev->definition=$level;
				$DB->insert_record ( 'emarking_rubrics_levels', $lev );
			}
		}
		}
	}
	}
	$activity=$DB->get_record('emarking_activities',array('id'=>$activityid));
	$activity->rubricid=$rubricid;
	$DB->update_record('emarking_activities', $activity);
	$activityUrl = new moodle_url($CFG->wwwroot.'/mod/emarking/activities/activity.php', array('id' => $activity->id));
	redirect($activityUrl, 0);
}
function update_rubric($id,$data){
	Global $USER,$DB;
	$rubric = new stdClass ();
	$rubric->id=$id;
	$rubric->name = $data['rubricname'];
	$rubric->description = $data ['rubricdescription'];
	$rubric->usermodified = $USER->id;
	$rubric->timemodified = time ();
	$DB->update_record('emarking_rubrics', $rubric);
	$criterias = $data['criteria'];
	$criteriaid=$data['criteriaid'];
	$levels=$data['level'];
	$levelsid=$data['levelid'];
	//elimino criterios borrados de la rúbrica
	$criteriosDB=$DB->get_records('emarking_rubrics_criteria', array('rubricid'=>$rubric->id));

	foreach($criteriosDB as $criterioDB){
		if (!in_array($criterioDB->id, $criteriaid)) {
			$DB->delete_records('emarking_rubrics_criteria', array('id'=>$criterioDB->id));
		}
	}
	for ($i = 1; $i <= count($criterias); $i++) {
		$criteriaRecord = new stdClass ();
		$criteriaRecord->rubricid=$rubric->id;
		$criteriaRecord->description=$criterias[$i];
		//si existe un criterio con ese id, se hace update en la BD
		if($criteriaid[$i]!=null&&$criteriaRecord->id=$criteriaid[$i]){
		$DB->update_record('emarking_rubrics_criteria', $criteriaRecord);
		}//No existe criterio por lo tanto se crea
		else{

			$criteriaRecord->id = $DB->insert_record ( 'emarking_rubrics_criteria', $criteriaRecord );
			
		}
		
		
		for ($k = 1; $k <= 4; $k++) {
			$levelRecord = new stdClass ();
			$levelRecord->criterionid=$criteriaRecord->id;
			$levelRecord->score=$k;
			$levelRecord->definition=$levels[$i][$k];
			if($levelsid[$i][$k]!=null&&$levelRecord->id =$levelsid[$i][$k]){
			$DB->update_record('emarking_rubrics_levels', $levelRecord);
			}else{
			$DB->insert_record ( 'emarking_rubrics_levels', $levelRecord );
			}
		}
	}
}
function add_row($data,$type){
	$bol=true;
	$next=4;
	foreach ($data as $level){
		if($level->score == $level->max){
			$cols ='<tr">';
			$cols .= '<td class="col-sm-2" style="text-align: center;vertical-align: middle;">';
			$cols .= '<span>'.$level->criteria.'</span></td>';
		}
	
		if($next!=$level->score){
			if($next > $level->score){
				$cols .= '<td class="col-sm-2" style="vertical-align: middle;">';
				$cols .= '<span ></span></td>';
	
			}
			 
		}
		$cols .= '<td class="col-sm-2" style="vertical-align: middle;">';
		$cols .= '<span >'.$level->definition.'</span></td>';
	
	
		$next=$level->score-1;
		 
		 
		 
		if($level->score == 1){
			if($type==1){
			$cols .= '<td class="col-sm-1" style="vertical-align: middle;"><input type="button" class="ibtnDel btn btn-md btn-danger "  value="Borrar"></td>';
			}
			if($type==2){
		      		$cols .= '<td class="col-sm-1" style="vertical-align: middle;"><input type="button" id='.$level->criterionid.' class="ibtnAdd btn btn-md btn-success "  value="Agregar"></td>';
			}
			$cols .='</tr>';
			echo $cols;
			$next=4;
		}
	}
}
function add_new_activity_basic($fromform){
	global $DB,$USER, $CFG;

	
	$record = new stdClass ();
	$record->title = $fromform->title;
	$record->description = $fromform->description;
	$record->learningobjectives = implode(',',$fromform->learningobjectives);
	$record->comunicativepurpose = $fromform->comunicativepurpose;
	$record->status = $fromform->status;
	$record->genre = $fromform->genre;
	$record->audience = $fromform->audience;
	$record->estimatedtime = $fromform->estimatedtime;
	$record->timecreated = time ();
	$record->userid = $USER->id;
	$instertnewactivity = $DB->insert_record ( 'emarking_activities', $record );
	
	$socialrecord=new stdClass ();
	$socialrecord->activityid 			= $instertnewactivity;
	$socialrecord->timecreated         	= time();
	$socialrecord->data					= null;
	$DB->insert_record ( 'emarking_social', $socialrecord );
	
	return $instertnewactivity;
}
function edit_activity_basic($fromform,$activityid) {
	global $DB,$CFG,$USER;
	
	$record=$DB->get_record('emarking_activities',array('id'=>$activityid));
	$record->title = $fromform->title;
	$record->description = $fromform->description;
	$record->learningobjectives = implode(',',$fromform->learningobjectives);
	$record->comunicativepurpose = $fromform->comunicativepurpose;
	$record->status = $fromform->status;
	$record->genre = $fromform->genre;
	$record->audience = $fromform->audience;
	$record->estimatedtime = $fromform->estimatedtime;
	$DB->update_record('emarking_activities', $record);
	
}
/**
 * Extract learning objectives from an activity.
 * It uses a backward compatibility approach.
 * @param unknown $activity
 * @return string|unknown|NULL
 */
function extract_oas($activity) {
    if($activity->learningobjectives) {
        $matches = NULL;
        preg_match("/^(?<curso>\d+)\[(?<oas>\d+(\s*,\s*\d+)*)\]$/i", $activity->learningobjectives, $matches);
        if($matches != NULL && isset($matches['curso']) && isset($matches['oas'])) {
            $curso = $matches['curso'];
            $oas = explode(',',$matches['oas']);
            $lo = array();
            foreach($oas as $oa) {
                $lo[] = $curso . '-' .  $oa;
            }
            return $lo;
        }
        preg_match("/^(\d+\-\d+)(,\d+\-\d+)*$/i", $activity->learningobjectives, $matches);
        if($matches != NULL) {
            return explode(',', $activity->learningobjectives);
        }
    }
    return null;
}
/**
 * String with a lista of courses and learning objectives, separated by a dash.
 * @param unknown $activity
 * @return string
 */
function oas_string($activity) {
    $coursesOA="";
    if( isset($activity->learningobjectives) && $activity->learningobjectives != null){
        if($oas = extract_oas($activity)) {
            $lastcourse = '';
            foreach ( $oas as $oa ) {
                $parts = explode ( "-", $oa);
                $course = $parts[0];
                $oacode = $parts[1];
                if($course !== $lastcourse) {
                    $prefix = $lastcourse === '' ? '' : '&nbsp;-&nbsp;';
                    $coursesOA .= $prefix . $course . '° <span style="font-size:12px">' . $oacode . '</span>';
                    $lastcourse = $course;
                } else {
                    $coursesOA .= ',<span style="font-size:12px">' . $oacode . '</span>';
                }
            }
        } else {
            var_dump($activity->learningobjectives);
        }
    } else {
        $coursesOA .= '';
    }
    return $coursesOA;
}
function add_new_activity_instructions($fromform,$activityid,$context){
	global $DB, $USER;
	
	change_draft_area($fromform->instructions ['itemid'],$context->id,'instructions');
	
	$instructions = $fromform->instructions ['text'];
	$planification = $fromform->planification ['text'];
	$writing = $fromform->writing ['text'];
	$editing = $fromform->editing ['text'];
	
	//changing url of images 
	$instructions = change_images_url($instructions,$fromform->instructions ['itemid']);
	$planification = change_images_url($planification,$fromform->instructions ['itemid']);
	$writing = change_images_url($writing,$fromform->instructions ['itemid']);
	$editing = change_images_url($editing,$fromform->instructions ['itemid']);
	
	//cleaning html text
	$instructions = emarking_activities_clean_html_text($instructions);
	$planification = emarking_activities_clean_html_text($planification);
	$writing = emarking_activities_clean_html_text($writing);
	$editing = emarking_activities_clean_html_text($editing);
	
	$record=$DB->get_record('emarking_activities',array('id'=>$activityid));
	$record->instructions = $instructions;
	$record->planification = $planification;
	$record->writing = $writing;
	$record->editing = $editing;
	$DB->update_record('emarking_activities', $record);
}
function add_new_activity_teaching($fromform,$activityid,$context){
	global $DB,$USER;
	change_draft_area($fromform->teaching ['itemid'],$context->id,'instructions');
	$teaching= $fromform->teaching ['text'];
	$lenguageresources= $fromform->languageresources ['text'];
	
	//changing url of images
	$teaching = change_images_url($teaching,$fromform->teaching ['itemid']);
	$lenguageresources = change_images_url($lenguageresources,$fromform->teaching ['itemid']);
	
	//cleaning html text
	$teaching = emarking_activities_clean_html_text($teaching);
	$lenguageresources = emarking_activities_clean_html_text($lenguageresources);
	$record=$DB->get_record('emarking_activities',array('id'=>$activityid));
	$record->teaching = $teaching;
	$record->languageresources = $lenguageresources;
	$DB->update_record('emarking_activities', $record);
	
}
function change_draft_area($itemid,$contextid,$area){
	
	$fs = get_file_storage ();
	file_save_draft_area_files ( $itemid, $contextid, 'mod_emarking', $area, $itemid );
	$files = $fs->get_area_files ( $contextid, 'mod_emarking', $area, $itemid, 'itemid, filepath, filename', false );
	
}
function change_images_url($obj,$itemid){
	global $USER;
	
	$usercontext = context_user::instance ( $USER->id );
	$urlAntigua = '/draftfile.php/' . $usercontext->id . '/user/draft/' . $itemid . '/';
	$urlnueva = '/pluginfile.php/1/mod_emarking/instructions/' . $itemid . '/';
	$obj = str_replace ( $urlAntigua, $urlnueva, $obj );
	return $obj;
}
function emarking_activity_send_notification($cm,$userto) {
	global $CFG;
	
	$postsubject = 'Corrección asignada';
	$url= new moodle_url ( $CFG->wwwroot . '/mod/emarking/activities/marking.php', 
			array('id'=>$cm,
					'tab'=>1
			));
	// Create the email to be sent.
	$posthtml = '';
	$posthtml='<p>Estimado corrector se le ha asignado una actividad por corregir, para seguir el estado de esta actividad porfavor seguir este link <a href="'.$url.'">'.$url.'</a></p>';
	$posthtml .= '<p>Se le recuerda que tiene como plazo máximo 14 días</p>';
	// Create the email to be sent.
	$posttext = '';
		$eventdata = new stdClass();
		$eventdata->component = 'mod_emarking';
		$eventdata->name = 'notification';
		$eventdata->userfrom = $fromuser;
		$eventdata->userto = $userto;
		$eventdata->subject = $postsubject;
		$eventdata->fullmessage = $posttext;
		$eventdata->fullmessageformat = FORMAT_HTML;
		$eventdata->fullmessagehtml = $thismessagehtml;
		$eventdata->smallmessage = $postsubject;
		$eventdata->notification = 1;
		message_send($eventdata);
	
}

function emarking_activity_get_num_criteria($context) {
	Global $CFG;
	require_once ($CFG->dirroot . '/grade/grading/lib.php');
	
	$gradingmanager = get_grading_manager($context, 'mod_emarking', 'attempt');
	$gradingmethod = $gradingmanager->get_active_method();
	$definition = null;
	$rubriccontroller = null;
	if ($gradingmethod !== 'rubric') {
		$gradingmanager->set_active_method('rubric');
		$gradingmethod = $gradingmanager->get_active_method();
	}
	$rubriccontroller = $gradingmanager->get_controller($gradingmethod);
	$definition = $rubriccontroller->get_definition();
	if ($definition) {
		$numcriteria = count($definition->rubric_criteria);
		//var_dump($numcriteria);
	}
	
	return $numcriteria;
}
function emarking_activity_get_num_criteria_comments($emarkingid) {
	Global $DB;
	
	$sql = "SELECT SUM(IFNULL(c.count,0)) as totalcomments
FROM mdl_emarking as e
LEFT JOIN mdl_emarking_draft as ed on (e.id=ed.emarkingid)
LEFT JOIN (select draft, count(*) as count
			from mdl_emarking_comment
			where textformat=2
			group by draft) as c on (c.draft=ed.id)
WHERE e.id=?";
	$numcomments=$DB->get_record_sql ( $sql, array (
			$emarkingid
	) );
	return $numcomments->totalcomments;
}