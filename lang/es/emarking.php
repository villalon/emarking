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
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2012 Jorge Villalon <jorge.villalon@uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string ['emarkingsecuritycode'] = 'Código de seguridad eMarking';

$string ['markersqualitycontrol'] = 'Correctores asignados a Control de Calidad';
$string ['markersqualitycontrol_help'] = 'Los correctores asignados a Control de Calidad son los que corregirán las pruebas con las que se calculará luego el acuerdo entre correctores.';
$string ['enablequalitycontrol'] = 'Habilitar Control de Calidad';
$string ['enablequalitycontrol_help'] = 'Si se habilita CC, un grupo de pruebas serán asignados a los correctores de CC para que sean corregidos nuevamente y así calcular el acuerdo entre correctores.';
$string ['notenoughmarkersfortraining'] = 'No hay suficientes correctores para un entrenamiento. Por favor matricule correctores como profesores sin permiso de edición para realizar el entrenamiento.';
$string ['notenoughmarkersforqualitycontrol'] = 'No ha seleccionado correctores para que realicen el control de calidad. Por favor seleccione al menos un corrector como responsable de corregir las pruebas de control.';

$string ['studentanonymous_markervisible'] = 'Estudiante anónimo / Corrector visible';
$string ['studentanonymous_markeranonymous'] = 'Estudiante anónimo / Corrector anónimo';
$string ['studentvisible_markervisible'] = 'Estudiante visible / Corrector visible';
$string ['studentvisible_markeranonymous'] = 'Estudiante visible / Corrector anónimo';

$string ['markingtype'] = 'Tipo de corrección';
$string ['markingtype_help'] = "<h2>Tipos de corrección</h2><br>
		Hay cuatro tipos de corrección en eMarking:
		<ul>
			<li><b>Normal</b>: Correctores corrigen las pruebas normalmente de acuerdo a una rúbrica. Las pruebas pueden ser corregidas por más de un corrector para control de calidad.</li>
			<li><b>Entrenamiento de correctores</b>: Las pruebas no pertenecen a estudiantes del curso. Todos los correctores corrigen todas las pruebas y deben alcanzar un acuerdo de 100% para finalizar el proceso.</li>
			<li><b>Entrenamiento de estudiantes</b>: Las pruebas no pertenecen a estudiantes del curso. Los estudiantes corrigen como práctica para su próxima evaluación.</li>
			<li><b>Revisión entre pares</b>: Los estudiantes se corrigen entre si de acuerdo a la configuración de grupos. Si hay grupos configurados (visibles o separados), cada estudiante corrige todas las pruebas de otro grupo.</li>
		</ul>";
$string ['type_normal'] = 'Normal';
$string ['type_markers_training'] = 'Entrenamiento de correctores';
$string ['type_student_training'] = 'Entrenamiento de estudiantes';
$string ['type_peer_review'] = 'Revisión entre pares';

$string ['invalidcustommarks'] = 'Marcadores personalizados inválidos, línea(s): ';
$string ['exporttoexcel'] = 'Exportar a Excel';

$string ['comparativereport'] = 'Comparativo';
$string ['comparativereport_help'] = 'Comparativo';
$string ['youmustselectemarking'] = 'Debe seleccionar una actividad eMarking para comparar';
$string ['rubrcismustbeidentical'] = 'Las rúbricas deben ser idénticas para poder comparar';

$string ['adjustslope'] = 'Ajustar pendiente de calificaciones';
$string ['adjustslope_help'] = 'Ajusta cómo eMarking calculará la calificación final, usando una calificación y puntaje de ajuste. Las nuevas calificaciones se calcularán linealmente con una pendiente entre 0 puntos y la calificación mínima, y la calificación/puntaje de ajuste, para luego continuar hasta la calificación máxima.';
$string ['adjustslopegrade'] = 'Calificación de ajuste';
$string ['adjustslopegrade_help'] = 'La calificación usada para calcular la pendiente de ajuste, i.e. entre la calificación mínima y la calificación de ajuste.';
$string ['adjustslopescore'] = 'Puntaje de ajuste';
$string ['adjustslopescore_help'] = 'El puntaje usado para calcular la pendiente de ajuste, i.e. entre 0 y el puntaje de ajuste.';
$string ['adjustslopegrademustbegreaterthanmin'] = 'Calificación de ajuste debe ser mayor que la calificación mínima';
$string ['adjustslopegrademustbelowerthanmax'] = 'Calificación de ajuste debe ser menor que la calificación máxima';
$string ['adjustslopescoregreaterthanzero'] = 'Puntaje de ajuste debe ser mayor que 0';

$string ['heartbeatenabled'] = 'Habilitar seguimiento a estudiantes';
$string ['heartbeatenabled_help'] = 'Habilita almacenamiento de registros de cuánto tiempo pasa un estudiante frente a la retroalimentación.';

$string ['downloadrubricpdf'] = 'Descarga pdf con rúbrica';
$string ['downloadrubricpdf_help'] = 'Estudiantes pueden descargar su prueba con la rúbrica en la última página';

$string ['linkrubric'] = 'Conectar rúbricas con comentarios';
$string ['linkrubric_help'] = "Capacidad de asignar colores a criterios de las rúbricas para conectarlos con comentarios.";

$string ['collaborativefeatures'] = 'Habilitar features colaborativas';
$string ['collaborativefeatures_help'] = "Habilitar muro, chat y solicitud de ayuda de correctores.";

$string ['experimentalgroups'] = 'Grupos experimentales';
$string ['experimentalgroups_help'] = "Habilitar corrección separada a través de los grupos del curso";

$string ['emarking:assignmarkers'] = 'Asignar correctores a preguntas';
$string ['emarking:activatedelphiprocess'] = 'Activar delphi';
$string ['emarking:configuredelphiprocess'] = 'Configurar delphi';
$string ['emarking:managedelphiprocess'] = 'Administrat delphi';

$string ['emarking_webexperimental'] = 'eMarking Web experimental';
$string ['emarking_webexperimental_help'] = 'Habilita la interfaz experimental';

$string ['enrolmanual'] = 'Matriculaciones manuales';
$string ['enrolself'] = 'Auto-Matriculaciones';
$string ['enroldatabase'] = 'Matriculaciones de base de datos externa';
$string ['enrolmeta'] = 'Matriculaciones de metacurso';

$string ['includestudentsinexam'] = 'Matriculaciones que incluir para impresión personalizada';
$string ['permarkercontribution'] = 'Contribución por corrector';
$string ['notpublished'] = 'No publicada';
$string ['markingstatusincludingabsents'] = 'Avance por estado (incluyendo ausentes)';
$string ['markingreport'] = 'Avance';
$string ['markingreport_help'] = 'Este reporte muestra el avance de la corrección';

$string ['of'] = 'de';
$string ['missingpages'] = 'Faltan páginas';
$string ['transactionsuccessfull'] = 'Transacción exitosa';
$string ['setasabsent'] = 'Marcar como ausente';
$string ['setassubmitted'] = 'Marcar como enviada';
$string ['markers'] = 'Correctores';
$string ['assignmarkerstocriteria'] = 'Asignar correctores a criterios';

$string ['pctmarked'] = '% corregido';
$string ['saved'] = 'Cambios guardados';
$string ['downloadform'] = 'Descargar formulario de impresión';
$string ['selectprinter'] = 'Escoger impresora';
$string ['enableprinting'] = 'Habilitar impresiones';
$string ['enableprinting_help'] = 'Habilita utilizar cups (lp) para imprimir desde el servidor de Moodle a una impresora en red';
$string ['enableprintingrandom'] = 'Permite la impresión al azar';
$string ['enableprintingrandom_help'] = 'permite la impresión al azar, basado en un grupo creado';
$string ['enableprintinglist'] = 'Permite imprimir una lista de estudiantes';
$string ['enableprintinglist_help'] = 'permite la impresión de una lista de estudiantes, esto ayuda a la asistencia en las clases';
$string ['printername'] = 'Nombre de la impresora en red';
$string ['printername_help'] = 'Nombre de la impresora de acuerdo a la configuración de cups';

$string ['minimumdaysbeforeprinting'] = 'Días de anticipación para enviar pruebas';
$string ['minimumdaysbeforeprinting_help'] = 'Los profesores podrán enviar pruebas a impresión al menos este número de días antes, después no se permitirá.';
$string ['showcoursesfrom'] = 'Mostrar cursos de';
$string ['donotinclude'] = 'No incluir';
$string ['parallelcourses'] = 'Cursos paralelos';
$string ['forcescale17'] = 'Forzar escala de 1 a 7';
$string ['configuration'] = 'Configuración';
$string ['overallfairnessrequired'] = 'El campo es obligatorio';
$string ['expectationrealityrequired'] = 'El campo es obligatorio';
$string ['choose'] = 'Escoger';

$string ['regradespending'] = 'recorrecciones';
$string ['regraderestricted'] = 'Ya no se aceptan nuevas solicitudes de recorrección. El período de recorrecciones cerró el {$a->regradesclosedate}.';
$string ['regraderestrictdates'] = 'Restringir fechas para recorrecciones';
$string ['regraderestrictdates_help'] = 'Restringe las recorrecciones dentro de límites de fecha de apertura y cierra';
$string ['regradesopendate'] = 'Apertura recorrecciones';
$string ['regradesopendate_help'] = 'Fecha desde la cual los estudiantes pueden enviar solicitudes de recorrección';
$string ['regradesclosedate'] = 'Cierre recorrecciones';
$string ['regradesclosedate_help'] = 'Fecha límite para que los estudiantes pueden enviar solicitudes de recorrección';
$string ['markingduedate'] = 'Fecha entrega calificaciones';
$string ['markingduedate_help'] = 'Si define una fecha de entrega de calificaciones, ésta se usará para notificar a correctores y profesores respecto del avance de la corrección';
$string ['enableduedate'] = 'Habilitar fecha entrega';

$string ['printdigitize'] = 'Imprimir/Escanear';
$string ['reports'] = 'Reportes';
$string ['gradereport'] = 'Grades report';
$string ['gradereport_help'] = 'This report shows basic statistics and a three graphs. It includes the grades from a particular eMarking activity but other activities from other courses can be added if the parallel courses settings are configured.<br/>
			<strong>Basic statistics:</strong>Shows the average, quartiles and ranges for the course.<br/>
			<strong>Average graph:</strong>Shows the average and standard deviation.<br/>
			<strong>Grades histogram:</strong>Shows the number of students per range.<br/>
			<strong>Approval rate:</strong>Shows the approval rate for the course.<br/>
			<strong>Criteria efficiency:</strong>Shows the average percentage of the maximum score obtained by the students.';
$string ['annotatesubmission_help'] = 'eMarking allows to mark digitized exams using rubrics. In this page you can see the course list and their submissions (digitized answers). It also shows the exam status, that can be missing for a student with no answers, submitted if it has not been graded, responded when the marking is finished and regrading when a regrade request was made by a student.';
$string ['regrades_help'] = 'This page shows the regrade requests made by students.';
$string ['uploadanswers_help'] = 'In this page you can upload the digitized answers from your students. The format is a zip file containing two png files for each page a student has (one is the anonymous version). This file can be obtained using the eMarking desktop application that can be downloaded <a href="">here</a>';

$string ['gradescale'] = 'Escala de calificaciones';
$string ['rubricscores'] = 'Puntaje total';

$string ['justiceinstructions'] = 'Considerando una escala de -4 a 4 donde -4 significa muy injusto y 4 muy justo, por favor conteste las siguientes preguntas relativas a la evaluación:';
$string ['justiceperceptionprocess'] = '¿Cuán justo le pareció el proceso de corrección?';
$string ['justiceperceptionexpectation'] = '¿Cómo se compara su calificación con el resultado que usted merecía?';
$string ['thanksforjusticeperception'] = 'Gracias por expresar su opinión';
$string ['ranking'] = 'Ranking';

$string ['noregraderequests'] = 'No hay solicitudes de recorrección';
$string ['regradedatecreated'] = 'Fecha creación';
$string ['regradelastchange'] = 'Último cambio';

$string ['score'] = 'Puntaje';
$string ['markingcomment'] = 'Comentario de corrección';
$string ['regrade'] = 'Recorrección';
$string ['regradingcomment'] = 'Comentario de recorrección';

$string ['missasignedscore'] = 'Puntaje mal asignado';
$string ['unclearfeedback'] = 'Retroalimentación poco clara';
$string ['statementproblem'] = 'Problemas con el enunciado';
$string ['other'] = 'Otro';

$string ['stdev'] = 'Desviación';
$string ['min'] = 'Mínimo';
$string ['quartile1'] = '1er Cuartil';
$string ['median'] = 'Mediana';
$string ['quartile3'] = '3er Cuartil';
$string ['max'] = 'Máximo';
$string ['lessthan'] = 'Menor {$a}';
$string ['between'] = '{$a->min} a {$a->max}';
$string ['greaterthan'] = 'Mayor {$a}';

$string ['areyousure'] = '¿Está seguro?';
$string ['actions'] = 'Acciones';
$string ['annotatesubmission'] = 'Corregir eMarking';
$string ['anonymous'] = 'Corrección anónima';
$string ['anonymous_help'] = 'Seleccione para que el proceso de corrección sea anónimo, en cuyo caso los nombres de los estudiantes serán escondidos.';
$string ['anonymousstudent'] = 'Estudiante anónimo';
$string ['aofb'] = '{$a->identified} de {$a->total}';
$string ['attempt'] = 'Intento';
$string ['average'] = 'Promedio';
$string ['backcourse'] = 'Regresar al curso';
$string ['cancelorder'] = 'Cancelar orden';
$string ['checkdifferentpage'] = 'Verificar otra página';
$string ['close'] = 'Cerrar';
$string ['comment'] = 'Comentario';
$string ['completerubric'] = 'Completar rúbrica';
$string ['confirmprocess'] = 'Confirmar proceso';
$string ['confirmprocessfile'] = 'Usted esta a punto de procesar el archivo {$a->file} como respuestas de los estudiantes en {$a->assignment}.<br/> Esto reemplazará las respuestas anteriores.<br/> ¿Desea continuar?';
$string ['confirmprocessfilemerge'] = 'Usted esta a punto de procesar el archivo {$a->file} como respuestas de los estudiantes en {$a->assignment}.<br> Las nuevas respuestas se mezclarán con respuestas anteriores que los estudiantes pudiesen tener.<br/> ¿Desea continuar?';
$string ['copycenterinstructions'] = 'Instrucciones para centro de copiado';
$string ['corrected'] = 'Corregido';
$string ['couldnotexecute'] = 'No se puede ejecutar el comando pdftk.';
$string ['createrubric'] = 'Crear rúbrica';
$string ['criterion'] = 'Criterio';
$string ['criteriaefficiency'] = 'Eficiencia por criterio';
$string ['crowd'] = 'Crowd';
$string ['crowdexperiment'] = 'Funcionalidad crowd';
$string ['crowdexperiment_help'] = 'Activa experimento para manejo de correctores';
$string ['crowdexperiment_rtm_secret'] = 'RTMarking Secret';
$string ['crowdexperiment_rtm_secret_help'] = 'Codigo secreto para RTMarking auth';
$string ['crowdexperiment_rtm_appid'] = 'RTMarking App-id';
$string ['crowdexperiment_rtm_appid_help'] = 'Appid para autenticar en RTMarking';
$string ['decodeddata'] = 'Datos decodificados';
$string ['digitizedfile'] = 'Archivo con respuestas';
$string ['doubleside'] = 'Doble cara';
$string ['doublesidescanning'] = 'Respuestas digitalizadas por ambos lados';
$string ['doublesidescanning_help'] = 'Esta opción se debe seleccionar cuando las respuestas de los estudiantes fueron escaneadas por ambos lados.';
$string ['downloadfeedback'] = 'Descargar corrección';
$string ['downloadsuccessfull'] = 'Descarga de prueba exitosa';
$string ['editorder'] = 'Editar orden de impresión';
$string ['email'] = 'Correo';
$string ['emailinstructions'] = 'Ingrese el código de seguridad enviado al correo: {$a->email}';
$string ['messageprovider:notification'] = 'Notificación';
$string ['emarking'] = 'eMarking';
$string ['enablejustice'] = 'Habilitar percepción de justicia';
$string ['enablejustice_help'] = 'Habilita la opción de expresar la percepción de justicia ante una corrección';
$string ['enrolincludes'] = 'Tipos de matriculaciones para eMarking';
$string ['enrolincludes_help'] = 'Los tipos de matriculaciones que se utilizarán para incluir el encabezado personalizado en eMarking';
$string ['errors'] = 'Errores';
$string ['errorprocessingcrop'] = 'Error procesando crop de QR';
$string ['errorprocessingextraction'] = 'Error procesando extracción desde ZIP';
$string ['errorsavingpdf'] = 'Error al guardar archivo ZIP';
$string ['examalreadysent'] = 'La prueba ya fue impresa, no puede modificarse.';
$string ['examdate'] = 'Fecha y hora de la prueba';
$string ['examdate_help'] = 'La fecha y hora en que se tomará la prueba. Solo se pueden solicitar impresiones con al menos 48 horas de anticipación (sin incluir fines de semana).';
$string ['examdateinvalid'] = 'Solo se pueden solicitar impresiones con al menos {$a->mindays} días de anticipación (sin incluir fines de semana)';
$string ['examdateprinted'] = 'Fecha de impresión';
$string ['examdatesent'] = 'Fecha de envío';
$string ['examdeleteconfirm'] = 'Está a punto de borrar {$a}. ¿Desea continuar?';
$string ['examdeleted'] = 'Prueba borrada. Por favor espera mientras está siendo redirigido.';
$string ['examid'] = 'Nº de orden';
$string ['examinfo'] = 'Información de la prueba';
$string ['examname'] = 'Nombre de la prueba';
$string ['examname_help'] = 'Nombre de la prueba, por ejemplo: Control 2, Prueba final, Exámen.';
$string ['exam'] = 'Prueba';
$string ['exams'] = 'Pruebas';
$string ['examstatusdownloaded'] = 'Descargada';
$string ['examstatusprinted'] = 'Impresa';
$string ['examstatussent'] = 'Enviada';
$string ['experimental'] = 'Experimental';
$string ['experimental_help'] = 'Funcionalidades experimentales (puede ser riesgoso)';
$string ['extractingpreview'] = 'Extrayendo páginas';
$string ['extraexams'] = 'Pruebas extra';
$string ['extraexams_help'] = 'Pruebas extra que se imprimirán con un usuario NN. Es útil para casos en que aparecen estudiantes que no estén inscritos en el sistema.';
$string ['extrasheets'] = 'Hojas extra';
$string ['extrasheets_help'] = 'Número de hojas extra que se incluirán por cada estudiante.';
$string ['fatalerror'] = 'Error fatal';
$string ['fileisnotpdf'] = 'El archivo no es del tipo PDF';
$string ['fileisnotzip'] = 'El archivo no es el tipo ZIP';
$string ['filerequiredpdf'] = 'Un archivo PDF con las respuestas';
$string ['filerequiredpdf_help'] = 'Se requiere un archivo PDF con las respuestas de los estudiantes digitalizadas';
$string ['filerequiredzip'] = 'Un archivo ZIP con las respuestas';
$string ['filerequiredzip_help'] = 'Se requiere un archivo ZIP con las respuestas de los estudiantes digitalizadas';
$string ['filerequiredtosend'] = 'Se requiere un archivo ZIP';
$string ['filerequiredtosendnewprintorder'] = 'Se requiere un archivo PDF';
$string ['finalgrade'] = 'Calificación final';
$string ['grade'] = 'Calificación';
$string ['headerqr'] = 'Encabezado personalizado para eMarking';
$string ['headerqr_help'] = 'El encabezado personalizado de eMarking permite imprimir la prueba personalizada para cada estudiante. Esto permite luego procesarla automáticamente para su corrección y entrega usando la actividad eMarking.<br/>
		Ejemplo de encabezado:<br/>
		<img src="' . $CFG->wwwroot . '/mod/emarking/img/preview.jpg">
		<div class="required">Advertencia<ul>
				<li>Para usar el encabezado la prueba debe tener un margen superior de al menos 3cm</li>
		</ul></div>';
$string ['identifieddocuments'] = 'Respuestas subidas';
$string ['idnotfound'] = '{$a->id} identificador no encontrado';
$string ['idnumber'] = 'RUT';
$string ['ignoreddocuments'] = 'Respuestas ignoradas';
$string ['includelogo'] = 'Incluir logo';
$string ['includelogo_help'] = 'Incluir logo en el encabezado de las pruebas. El logo está en mod/emarking/img/logo.jpg';
$string ['includeuserpicture'] = 'Incluir imagen de usuario';
$string ['includeuserpicture_help'] = 'Incluir la imagen del usuario en el encabezado de las pruebas';
$string ['initializedirfail'] = 'No se pudo inicializar directorio de trabajo {$a}. Por favor avisar al administrador.';
$string ['invalidaccess'] = 'Acceso inválido, intentando cargar la prueba';
$string ['invalidcategoryid'] = 'Categoría inválida';
$string ['invalidcourse'] = 'Asignación de curso inválida';
$string ['invalidcourseid'] = 'Id del curso inválido';
$string ['invalidcoursemodule'] = 'Módulo del curso inválido';
$string ['invalidexamid'] = 'ID de la prueba inválido';
$string ['invalidid'] = 'ID inválido';
$string ['invalididnumber'] = 'N&uacute;mero Id inválido';
$string ['invalidimage'] = 'Información inválida desde la imagen';
$string ['invalidemarkingid'] = 'Id de assignment inválido';
$string ['invalidparametersforpage'] = 'Parámetros inválidos para la página';
$string ['invalidpdfnopages'] = 'Archivo ZIP inválido, está vació.';
$string ['invalidsize'] = 'Tamaño inválido para la imagen';
$string ['invalidtoken'] = 'Código de seguridad no válido al intentar descargar prueba.';
$string ['invalidzipnoanonymous'] = 'Archivo ZIP inválido, no contiene versiones anónimas de las respuestas. Es posible que haya sido generado con una versión antigua de la herramienta desktop.';
$string ["justice"] = "Percepción de Justicia";
$string ["justice.area.under.construction"] = "";
$string ["justice.back"] = "Volver";
$string ["justice.download"] = "Ver prueba";
$string ["justice.evaluations.actions"] = "Acciones";
$string ["justice.evaluations.grade"] = "Calificación";
$string ["justice.evaluations.marker"] = "Corrector";
$string ["justice.evaluations.mean"] = "Promedio del curso";
$string ["justice.evaluations.name"] = "Evaluación";
$string ["justice.evaluations.status"] = "Estado";
$string ["justice.exam.not.found"] = "Examen no encontrado";
$string ["justice.feature.not.available.short"] = "Funcionalidad no disponible";
$string ["justice.feature.not.available.yet"] = "Esta funcionalidad no está disponible aún.";
$string ["justice.feedback.already.given"] = "Aviso! Ya nos has dado tu opinion. Si cambiaste de opinión, puedes volver a llenar el formulario.";
$string ["justice.feedback.welcome"] = "Use este formulario cuando esté listo para aceptar su calificación";
$string ["justice.form.header"] = "Mis evaluaciones";
$string ["justice.graph.student.name"] = "Nombre";
$string ["justice.graph.test.performance"] = "Rendimiento en la prueba";
$string ["justice.my.evaluations"] = "Mis evaluaciones";
$string ["justice.peercheck"] = "Revisar compañeros";
$string ["justice.question.unavailable"] = "No disponible";
$string ["justice.question.not.answered"] = "No Entregado";
$string ["justice.question.modify"] = "Modificar";
$string ["justice.regrade.request"] = "Recorrección";
$string ["justice.similars.actions"] = "Acciones";
$string ["justice.similars.grade"] = "Calificación";
$string ["justice.similars.name"] = "Nombre";
$string ["justice.statistics"] = "Estadísticas";
$string ["justice.statistics.locked"] = "Antes de ver las estadísticas, por favor contesta estas preguntas.";
$string ["justice.status.grading"] = "En Corrección";
$string ["justice.status.pending"] = "Por revisar";
$string ["justice.status.regrading"] = "En Recorrección";
$string ["justice.status.accepted"] = "Calificación aceptada";
$string ["justice.thank.you.for.your.feedback"] = "Su opinión ha sido guardada. Gracias por su tiempo.";
$string ["justice.unavailable"] = "No disponible";
$string ["justice.question.instructions"] = "Considere una escala de -4 a 4, donde -4 es muy injusto y 4 es muy justo, por favor conteste las siguientes preguntas en relación a la evaluación:";
$string ["justice.question.first"] = "Como calificaría la justicia del proceso de corrección?";
$string ["justice.question.second"] = "Como se compara tu calificación a lo que crees que merecías?";
$string ["justice.review"] = "Revisar";
$string ["justice.yourgrade"] = "Tu calificación";
$string ['justiceexperiment'] = 'Experimento en percepción de justicia';
$string ['justiceexperiment_help'] = 'Muestra a la mitad de los estudiantes las estadísticas de la evaluación, de manera de tener grupos experimental y control.';
$string ['justification'] = 'Justificación';
$string ['justification_help'] = 'Usted debe justificar su solicitud de recorrección';
$string ['lastmodification'] = 'Última Modificación';
$string ['logo'] = 'Logo para encabezado';
$string ['logodesc'] = 'Logo para incluir en encabezado de pruebas';
$string ['marking'] = 'Corrección';
$string ['merge'] = 'Reemplazar páginas existentes';
$string ['merge_help'] = 'Las páginas subidas en el archivo reemplazarán a las páginas existentes. Si no marca esta opción las páginas se agregarán al final.';
$string ['modulename'] = 'eMarking';
$string ['modulename_help'] = 'Nombre evaluación';
$string ['modulenameplural'] = 'eMarkings';
$string ['motive'] = 'Motivo';
$string ['motive_help'] = 'Indique el motivo de su recorrección para este criterio';
$string ['multicourse'] = 'Multicurso';
$string ['multicourse_help'] = 'Select other course for which this exam will also be printed';
$string ['multiplepdfs'] = 'Generar múltiples pdfs';
$string ['multiplepdfs_help'] = 'Si se selecciona, eMarking generará un archivo zip que contendrá un pdf personalizado por cada estudiante, si no se generará un solo pdf con todas las pruebas.';
$string ['myexams'] = 'Mis pruebas';
$string ['myexams_help'] = 'Esta página muestra todas las pruebas que han sido enviadas a imprimir para este curso. Usted puede editarlas o incluso cancelarlas mientras no hayan sido descargadas por el centro de copiado.';
$string ['names'] = 'Nombres/Apellidos';
$string ['emarking:addinstance'] = 'Agregar instancia de emarking';
$string ['emarking:downloadexam'] = 'Descargar pruebas';
$string ['emarking:grade'] = 'Calificaciones';
$string ['emarking:manageanonymousmarking'] = 'Gestionar correcciones anónimas';
$string ['emarking:managespecificmarks'] = 'Gestionar anotaciones personalizadas';
$string ['emarking:printordersview'] = 'Ver órdenes de impresión';
$string ['emarking:receivenotification'] = 'Recibir notificación de impresiones';
$string ['emarking:regrade'] = 'Recorregir';
$string ['emarking:reviewanswers'] = 'Revisar respuestas';
$string ['emarking:submit'] = 'Enviar prueba a emarking';
$string ['emarking:supervisegrading'] = 'Supervisar corrección';
$string ['emarking:uploadexam'] = 'Enviar prueba';
$string ['emarking:view'] = 'Ver pruebas';
$string ['emarking:viewpeerstatistics'] = 'Ver pruebas de pares anónimamente';
$string ['newprintorder'] = 'Enviar prueba a impresión';
$string ['newprintorder_help'] = 'Para enviar una prueba a imprimir debe indicar un nombre para la prueba (p.ej: Prueba 1), la fecha exacta en que se tomará la prueba y un archivo pdf con la prueba misma.<br/>
		<strong>Encabezado personalizado eMarking:</strong> Si escoge esta opción, la prueba será impresa con un encabezado personalizado para cada estudiante, incluyendo su foto si está disponible. Este encabezado permite luego procesar automáticamente las pruebas usando el módulo eMarking, que apoya el proceso de corrección, entrega de calificaciones y recepción de recorrecciones.<br/>
		<strong>Instrucciones para el centro de copiado:</strong> Instrucciones especiales pueden ser enviadas al centro de copiado, tales como imprimir hojas extra por cada prueba o pruebas extra.
		';
$string ['newprintordersuccess'] = 'La orden de impresión fue enviada exitosamente.';
$string ['newprintordersuccessinstructions'] = 'Su prueba {$a->name} fue enviada exitosamente a impresión.';
$string ['noemarkings'] = 'No quedan envíos';
$string ['nopagestoprocess'] = 'El archivo debe estar en formato ZIP';
$string ['noprintorders'] = 'No hay órdenes de impresión para este curso';
$string ['nosubmissionsgraded'] = 'No hay pruebas corregidas aún';
$string ['nosubmissionsselectedforpublishing'] = 'No hay pruebas seleccionadas para publicar sus calificaciones';
$string ['nocomment'] = 'No hay comentario general';
$string ['noexamsforprinting'] = 'No hay pruebas para imprimir';
$string ['notcorrected'] = 'Por corregir';
$string ['page'] = 'Página';
$string ['pages'] = 'páginas';
$string ['assignpagestocriteria'] = 'Asignar páginas a criterios';
$string ['pagedecodingfailed'] = 'QR de página {$a} no pudo ser decodificado';
$string ['pagedecodingsuccess'] = 'QR de página {$a} decodificado exitosamente';
$string ['pagenumber'] = 'Número de página';
$string ['parallelregex'] = 'Regex para secciones paralelas';
$string ['parallelregex_help'] = 'Expresión regular para extraer el código de la asignatura a partir del nombre corte de un curso, de manera de poder comparar evaluaciones entre cursos paralelos.';
$string ['pathuserpicture'] = 'Directorio de imágenes de usuarios';
$string ['pathuserpicture_help'] = 'Direccón absoluta del directorio que contiene las imágenes de los usuarios en formato PNG y cuyo nombre calza con userXXX.png en que XXX es el id de usuario';
$string ['pdffile'] = 'Archivo PDF';
$string ['pdffile_help'] = 'Por el momento el sistema solamente acepta archivos PDF';
$string ['pluginadministration'] = 'Administración de emarking';
$string ['previewheading'] = 'Visualización de decodificación de códigos QR';
$string ['previewtitle'] = 'Visualizar errores de QR';
$string ['printsuccessinstructions'] = 'Instrucciones para orden de impresión exitosa';
$string ['printsuccessinstructionsdesc'] = 'Mensaje personalizado para mostrar a profesores y administrativo una vez que una orden de impresión fue correctamente enviada. Por ejemplo que retiren las pruebas en un centro de copiado o que descarguen la prueba por si mismos.';
$string ['printdoublesided'] = 'Imprimir a doble cara';
$string ['printexam'] = 'Imprimir prueba';
$string ['printrandom'] = 'Impresión aleatoria';
$string ['printrandominvalid'] = 'Debe crear un grupo para utilizar esta función';
$string ['printrandom_help'] = 'Impresión aleatoria basada en un grupo creado en un curso especifico';
$string ['printlist'] = 'Imprimir listado de estudiantes';
$string ['printlist_help'] = 'Se utiliza para imprimir una lista de los estudiantes del curso';
$string ['printnotification'] = 'Notificación de impresión';
$string ['printnotificationsent'] = 'Notificación de impresión enviada';
$string ['printorders'] = 'Órdenes de impresión';
$string ['printsendnotification'] = 'Enviar notificación de impresión';
$string ['problem'] = 'Problema';
$string ['processanswers'] = 'Subir respuestas con proceso lento';
$string ['processtitle'] = 'Subir respuestas';
$string ['publishselectededgrades'] = 'Publicar calificaciones seleccionadas';
$string ['publishtitle'] = 'Publicar calificaciones';
$string ['publishedgrades'] = 'Calificaciones publicadas';
$string ['publishinggrade'] = 'Publicando calificación';
$string ['publishinggrades'] = 'Publicando calificaciones';
$string ['publishinggradesfinished'] = 'Publicación de calificaciones finalizada';
$string ['qrdecoding'] = 'Decodificando QR';
$string ['qrdecodingfinished'] = 'Decodificación de QR finalizada';
$string ['qrdecodingloadingtoram'] = 'Preparando páginas {$a->floor} a la {$a->ceil} para decodificación. Páginas totales: {$a->total}';
$string ['qrdecodingprocessing'] = 'Decodificando página {$a->current}. Nueva preparación al llegar a: {$a->ceil}. Páginas totales: {$a->total}';
$string ['qrerror'] = 'Error al codificar código QR';
$string ['qrimage'] = 'Imagen QR';
$string ['qrnotidentified'] = 'QR no pudo ser identificado';
$string ['qrprocessingtitle'] = 'Software para procesar respuestas';
$string ['qrprocessing'] = 'Descargar software para procesar respuestas';
$string ['records'] = 'Historial';
$string ['regrades'] = 'Recorrección';
$string ['regraderequest'] = 'Solicitud de recorrección';
$string ['requestedby'] = 'Solicitado Por';
$string ['results'] = 'Resultados';
$string ['rubricneeded'] = 'eMarking requiere el uso de rúbricas para la corrección. Por favor cree una.';
$string ['rubricdraft'] = 'eMarking requiere una rúbrica lista, la rúbrica se encuentra en estado de borrador. Por favor completar rúbrica';
$string ['selectall'] = 'Seleccionar todas';
$string ['selectnone'] = 'Seleccionar ninguna';
$string ['separategroups'] = 'Grupos separados';
$string ['settingsadvanced'] = 'Configuración avanzada';
$string ['settingsadvanced_help'] = 'Configuración avanzada para eMarking';
$string ['settingsbasic'] = 'Configuración básica';
$string ['settingsbasic_help'] = 'Configuración básica para eMarking';
$string ['settingslogo'] = 'Configuración de encabezado';
$string ['settingslogo_help'] = 'Opciones para incluir logo de la institución o la foto del estudiante';
$string ['settingssms'] = 'Configuración SMS';
$string ['settingssms_help'] = 'Configuración de un servicio SMS para validar la descarga de pruebas usando mensajes SMS';
$string ['smsinstructions'] = 'Ingrese el código de seguridad enviado al teléfono: {$a->phone2}';
$string ['smspassword'] = 'Contraseña SMS';
$string ['smspassword_help'] = 'Contraseña del servicio de SMS';
$string ['smsurl'] = 'URL servicio SMS';
$string ['smsurl_help'] = 'URL del proveedor de servicio SMS';
$string ['smsuser'] = 'Nombre usuario SMS';
$string ['smsuser_help'] = 'Nombre de usuario del servicio de SMS';
$string ['specificmarks'] = 'Marcadores personalizados';
$string ['specificmarks_help'] = 'Marcadores personalizados, uno por línea separando código y descripción por un # por ejemplo:<br/>Oa#Ortografía acentual<br/>Op#Ortografía puntual<br/>G#Gramática';
$string ['statistics'] = 'Estadísticas';
$string ['statisticstotals'] = 'Estadísticas acumuladas';
$string ['status'] = 'Estado';
$string ['statusaccepted'] = 'Aceptada';
$string ['statusabsent'] = 'Ausente';
$string ['statusgrading'] = 'En corrección';
$string ['statusmissing'] = 'No entregada';
$string ['statusregrading'] = 'En recorrección';
$string ['statusresponded'] = 'Corregida';
$string ['statussubmitted'] = 'Enviada';
$string ['statuserror'] = 'Error';
$string ['submission'] = 'Subida manual de respuestas';
$string ['teachercandownload'] = 'Profesor puede descargar prueba';
$string ['teachercandownload_help'] = 'Mostar el link para descargar sus propios exámenes a profesores. Requiere configurar la capacidad de descargar exámenes para el rol de profesor';
$string ['totalexams'] = 'Exámenes totales';
$string ['totalpages'] = 'Páginas totales';
$string ['totalpages_help'] = 'Indica el número total de páginas esperadas por alumno. Esto no limita cuántas páginas pueden subirse, solamente permite asociar páginas a criterios de la rúbrica y advertencias visuales cuando faltan páginas de algún alumno.';
$string ['totalpagesprint'] = 'Páginas totales a imprimir';
$string ['uploadanswers'] = 'Subir respuestas digitalizadas';
$string ['uploaderrorsmanual'] = 'Subir respuestas manualmente';
$string ['uploadexamfile'] = 'Archivo Zip';
$string ['uploadinganswersheets'] = 'Subiendo respuestas de los estudiantes';
$string ['usesms'] = 'Usar SMS';
$string ['usesms_help'] = 'Usar mensaje SMS en vez de correo electrónico para verificar códigos de seguridad de eMarking';
$string ['viewpeers'] = 'Estudiantes ven pruebas de otros estudiantes';
$string ['viewpeers_help'] = 'Se le permite a los estudiantes revisar pruebas de sus compañeros de manera anónima';
$string ['viewsubmission'] = 'Ver corrección';
$string ['visualizeandprocess'] = 'Visualizar errores';
$string ['formnewcomment'] = 'Nuevo Comentario:';
$string ['writecomment'] = 'Escriba un Comentario';
$string ['createcomment'] = 'Crear Comentario';
$string ['formeditcomment'] = 'Editar Comentario:';
$string ['editcomment'] = 'Editar Comentario';
$string ['createnewcomment'] = 'Crear Nuevo Comentario';
$string ['adjustments'] = 'Ajustes';
$string ['questioneditcomment'] = '¿Desea editar el comentario?';
$string ['questiondeletecomment'] = '¿Desea borrar el comentario?';
$string ['creator'] = 'Creador';
$string ['building'] = 'Edificio';

$string ['gradestats'] = 'Estadistica de notas por curso';
$string ['gradehistogram'] = 'Histograma de notas por curso';
$string ['courseaproval'] = 'Aprobación de curso';
$string ['course'] = 'Curso';
$string ['range'] = 'Rango';
$string ['lessthan3'] = 'Menor a 3';
$string ['between3and4'] = '3 a 4';
$string ['morethan4'] = 'Mayor 4';

$string ['advacebycriteria'] = 'Avance por criterio';
$string ['pointsassignedbymarker'] = 'Puntajes asignados por corrector';
$string ['advancebymarker'] = 'Avance por corrector';
$string ['marker'] = 'Corrector';
$string ['published'] = 'Publicada';

/**
 * Events
 */

$string ['eventemarkinggraded'] = 'Emarking';
$string ['eventsortpagesswitched'] = 'Ordenar paginas';
$string ['eventrotatepageswitched'] = 'Rotar pagina';
$string ['eventaddcommentadded'] = 'Agregar comentario';
$string ['eventaddregradeadded'] = 'Agregar recorreccion';
$string ['eventupdcommentupdated'] = 'Subir Comentario';
$string ['eventdeletecommentdeleted'] = 'Borrar Comentario';
$string ['eventaddmarkadded'] = 'Agregar marca';
$string ['eventregradegraded'] = 'Recorreccion';
$string ['eventdeletemarkdeleted'] = 'Borrar Marca';
$string ['eventmarkingended'] = 'Terminar Emarking';
$string ['eventinvalidaccessgranted'] = 'Acceso inválido, intentando cargar la prueba';
$string ['eventsuccessfullydownloaded'] = 'Descarga de prueba exitosa';
$string ['eventinvalidtokengranted'] = 'Código de seguridad no válido al intentar descargar prueba.';
$string ['eventunauthorizedccessgranted'] = "WARNING: Acceso no autorizado a la Interfaz Ajax de eMarking";
$string ['eventmarkersconfigcalled'] = 'Se ingresa al markers config';
$string ['eventmarkersassigned'] = 'Correctores han sido assignado';
$string ['eventemarkingcalled'] = 'Llamada al emarking';
