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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2012-onwards Jorge Villalon <jorge.villalon@uai.cl>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// EMARKING TYPES WORKFLOW.
$string ['orsentexam'] = 'Asociar prueba impresa';
$string ['orsentexam_help'] = 'Puede asociar una prueba que fue enviada a imprimir previamente.';
$string ['print'] = 'Imprimir';
$string ['onscreenmarking'] = 'Corrección en pantalla';
$string ['scan'] = 'Digitalizar';
$string ['enablescan'] = 'Habilitar digitalización';
$string ['scanisenabled'] = 'Digitalización está habilitada. La corrección es manual, las respuestas se digitalizan y se suben al sistema como respaldo.';
$string ['scanwasenabled'] = 'Digitalización habilitada exitosamente.';
$string ['osmisenabled'] = 'Digitalización está habilitada. Las respuestas se digitalizan y se suben al sistema para ser corregidas en pantalla usando una rúbrica.';
$string ['enableosm'] = 'Habilitar corrección en pantalla';
$string ['enableosm_help'] = 'Debe habilitar la digitalización para poder habilitar la corrección en pantalla';
$string ['emarkingwithnoexam'] = 'Existe un problema de configuración con su actividad. Por favor notifique al administrador.';
$string ['printsettings'] = 'Configuración de impresión';
$string ['printsettings_help'] = 'Help for print settings';
$string ['markingtypemandatory'] = 'Debe seleccionar un tipo de corrección';
$string ['emarkingviewed'] = 'Ver prueba';
$string['updateemarkingtype'] = 'Usted va a {$a->message} en {$a->name}. No hay riesgos en hacer esto, usted puede cambiarlo después en los ajustes de la actividad en cualquier momento.';
// REGRADES.
$string ['justification'] = 'Justificación';
$string ['justification_help'] = 'Usted debe justificar su solicitud de recorrección';
$string ['noregraderequests'] = 'No hay solicitudes de recorrección';
$string ['regrade'] = 'Recorrección';
$string ['regradingcomment'] = 'Comentario de recorrección';
$string ['missasignedscore'] = 'Asignaron menor puntaje del correspondiente';
$string ['unclearfeedback'] = 'No queda claro dónde está el error';
$string ['statementproblem'] = 'Problemas con el enunciado';
$string ['errorcarriedforward'] = 'Error de arrastre';
$string ['correctalternativeanswer'] = 'Mi respuesta tiene un enfoque distinto al de la pauta, pero de igual manera correcto';
$string ['other'] = 'Otro';
$string ['regradespending'] = 'recorrecciones';
$string ['regraderestrictdates'] = 'Restringir fechas para recorrecciones';
$string ['regraderestrictdates_help'] = 'Los estudiantes podrán solicitar recorrecciones solamente dentro de límites de fecha de apertura y cierre.';
$string ['regradesopendate'] = 'Apertura recorrecciones';
$string ['regradesopendate_help'] = 'Fecha desde la cual los estudiantes pueden enviar solicitudes de recorrección';
$string ['regradesclosedate'] = 'Cierre recorrecciones';
$string ['regradesclosedate_help'] = 'Fecha límite para que los estudiantes envíen solicitudes de recorrección';
$string ['mustseeexambeforeregrade'] = 'Debes revisar la corrección de tu prueba antes de solicitar recorrección.';
$string ['viewmore'] = 'Ver más';
$string ['cannotmodifyacceptedregrade'] = 'No se puede modificar una recorrección ya aceptada';
$string ['criterionrequired'] = 'Debe seleccionar un criterio';
$string ['justificationrequired'] = 'Debe justificar su solicitud';
// MARKERS AND PAGES OSM CONFIGURATION.
$string ['markerspercriteria'] = 'Correctores';
$string ['pagespercriteria'] = 'Páginas';
$string ['markerscanseewholerubric'] = 'Correctores pueden ver la rúbrica completa.';
$string ['markerscanseeallpages'] = 'Correctores ven todas las páginas.';
$string ['markerscanseeselectedcriteria'] = 'Correctores ven solamente criterios que se le han asignado.';
$string ['markerscanseenothing'] = 'Hay páginas asignadas a criterios, pero no correctores. Esto provocará que solo los administradores puedan ver las páginas.';
$string ['markerscanseepageswithcriteria'] = 'Correctores ven solo las páginas de los criterios que tienen asignados.';
$string ['assignedmarkers'] = 'Correctores asignados';
$string ['assignedoutcomes'] = 'Resultados asignados';
$string ['nooutcomesassigned'] = 'No hay resultados asociados a la rúbrica de esta prueba';
$string ['assignmarkerstocriteria'] = 'Agregar correctores a criterios';
$string ['assignoutcomestocriteria'] = 'Agregar resultados a criterios';
$string ['currentstatus'] = 'Configuración actual';
$string ['noneditingteacherconfiguration'] = 'Como ayudante no puedes modificar la configuración.';
$string ['coursehasnooutcomes'] = 'El curso no tiene resultados de aprendizaje asociados. Además deberá asociar resultados de aprendizaje a la prueba emarking. Al menos un resultado debe asociarse para poder relacionarlo con la rúbrica.';
$string ['gotooutcomessettings'] = 'Ir a configuración de resultados de aprendizaje';
$string ['emarkinghasnooutcomes'] = 'La prueba no tiene asociado ningún resultado de aprendizaje. Al menos un resultado debe asociarse para poder relacionarlo con la rúbrica.';
$string ['gotoemarkingsettings'] = 'Ir a configuración de emarking';
$string ['emarkingdst'] = 'eMarking destino';
$string ['emarkingdst_help'] = 'Seleccione las pruebas eMarking a las que copiará la configuración';
$string ['override'] = 'Sobreescribir rúbrica';
$string ['override_help'] = 'Sobreescribe la rúbrica de la prueba eMarking destino, usando la de esta prueba';
$string ['overridemarkers'] = 'Sobreescribir correctores';
$string ['overridemarkers_help'] = 'Sobreescribe los correctores asignados en la prueba eMarking destino';
$string ['noparallelemarkings'] = 'No hay pruebas eMarking en los cursos paralelos';
$string ['scalelevels'] = 'Porcentajes para escala';
$string ['studentachievement'] = '% de estudiantes';
$string ['level'] = 'Nivel de logro';
$string ['outcomesnotconfigured'] = 'No se han configurado resultados de aprendizaje para esta prueba E-Marking';
// GENERAL.
$string ['criteria'] = 'Criterios';
$string ['deleterow'] = 'Borrar fila';
$string ['nodejspath'] = 'Ruta de NodeJS';
$string ['nodejspath_help'] = 'Ruta completa del servidor Node JS incluyendo protocolo, dirección ip y puerto. p.ej: http://127.0.0.1:9091';
$string ['emarkinggraded'] = 'Corrección eMarking';
$string ['answerkey'] = 'Pauta';
// PERMISSIONS
$string['emarking:activatedelphiprocess'] = 'Activar Delphi';
$string['emarking:addinstance'] = 'Crear instancia de EMarking';
$string['emarking:assignmarkers'] = 'Asignar correctores';
$string['emarking:configuredelphiprocess'] = 'Configurar Delphi';
$string['emarking:grade'] = 'Corregir';
$string['emarking:manageanonymousmarking'] = 'Administrar anonimato';
$string['emarking:managedelphiprocess'] = 'Administrar proceso Delphi';
$string['emarking:managespecificmarks'] = 'Administrar anotaciones personalizadas';
$string['emarking:regrade'] = 'Recorregir';
$string['emarking:submit'] = 'Tener respuestas';
$string['emarking:supervisegrading'] = 'Supervisar corrección';
$string['emarking:uploadexam'] = 'Subir respuestas';
$string['emarking:view'] = 'Ver instancia';
$string['emarking:viewpeerstatistics'] = 'Ver estadísticas de pares';
// SMS SECURITY.
$string ['download'] = 'Descargar';
$string ['cancel'] = 'Cancelar';
$string ['resendcode'] = 'Reenviar código de seguridad';
$string ['smsservertimeout'] = 'Se agotó el tiempo de espera para enviar el código. Por favor avise al administrador.';
$string ['smsservererror'] = 'Tuvimos problemas de comunicación con el servidor de mensajes celulares. Por favor reintente más tarde.';
// EXAMS.
$string ['examdetails'] = 'Detalles de la prueba';
$string ['examalreadysent'] = 'La prueba ya fue impresa, no puede modificarse.';
$string ['examdate'] = 'Fecha y hora de la prueba';
$string ['examdate_help'] = 'La fecha y hora en que se tomará la prueba. Solo se pueden solicitar impresiones con al menos 48 horas de anticipación (sin incluir fines de semana).';
$string ['examdateinvalid'] = 'Solo se pueden solicitar impresiones con al menos {$a->mindays} días de anticipación (sin incluir fines de semana)';
$string ['examdateinvaliddayofweek'] = 'Fecha de prueba inválida, solo de Lunes a Viernes y Sábados hasta las 4pm.';
$string ['examdateprinted'] = 'Fecha de impresión';
$string ['examdeleted'] = 'Prueba borrada. Por favor espera mientras está siendo redirigido.';
$string ['examid'] = 'Nº de orden';
$string ['examinfo'] = 'Información de la prueba';
$string ['examhasnopdf'] = 'Exam id has no PDF associated. This is a terrible error, please notify the administrator.';
$string ['examname'] = 'Nombre de la prueba';
$string ['examname_help'] = 'Nombre de la prueba, por ejemplo: Control 2, Prueba final, Exámen.';
$string ['exam'] = 'Prueba';
$string ['exams'] = 'Pruebas';
$string ['examnotavailable'] = 'Tu prueba no está disponible';
$string ['examstatusdownloaded'] = 'Descargada';
$string ['examstatusprinted'] = 'Impresa';
$string ['examstatussent'] = 'Enviada';
$string ['downloadexam'] = 'Descargar prueba';
$string ['comment_help'] = 'Comentario para hacer llegar a la impresión.';
// JUSTICE PERCEPTION.
$string ['er-4'] = '-4 (mucho peor de lo que merecía)';
$string ['er-3'] = '-3';
$string ['er-2'] = '-2';
$string ['er-1'] = '-1';
$string ['er0'] = '0 (más o menos lo que merecía)';
$string ['er1'] = '1';
$string ['er2'] = '2';
$string ['er3'] = '3';
$string ['er4'] = '4 (mucho más de lo que merecía)';
$string ['of-4'] = '-4 (extremadamente injusta)';
$string ['of-3'] = '-3';
$string ['of-2'] = '-2';
$string ['of-1'] = '-1';
$string ['of0'] = '0 (ni justa ni injusta)';
$string ['of1'] = '1';
$string ['of2'] = '2';
$string ['of3'] = '3';
$string ['of4'] = '4 (extremadamente justa)';
$string ['justiceperceptionprocess'] = '¿Cómo evaluaría cuan justa fue la corrección de esta evaluación?';
$string ['justiceperceptionexpectation'] = '¿Cómo se compara su calificación en esta evaluación con la que usted piensa que se merecía?';
$string ['justiceperceptionprocesscriterion'] = '¿Cómo evaluaría cuan justa fue la corrección de cada pregunta?';
$string ['justiceperceptionexpectationcriterion'] = '¿Cómo se compara su puntaje en cada pregunta con el que usted piensa que se merecía?';
$string ['thanksforjusticeperception'] = 'Gracias por expresar su opinión';
$string ['justicedisabled'] = 'Deshabilitada';
$string ['justicepersubmission'] = 'Solicitar una opinión por prueba';
$string ['justicepercriterion'] = 'Solicitar una opinión por criterio de la rúbrica';
$string ['justice'] = 'Justicia';
$string ['justiceperception'] = 'Preguntar percepción de justicia';
$string ['justiceperception_help'] = 'Esta opción permite a los estudiantes entregar su percepción de justicia respecto del proceso de corrección (justicia procedural) y su resultado (justicia distributiva). Se puede preguntar por la prueba en general o por cada criterio de la rúbrica.';
$string ['agreementflexibility'] = 'Flexibilidad de acuerdo';
$string ['agreementflexibility_help'] = 'Define la diferencia máxima entre las calificaciones entregadas por un corrector y el promedio de los demás correctores para ser considerado fuera de rango.';
$string ['agreementflexibility00'] = 'Estricto (calificaciones deben ser iguales)';
$string ['agreementflexibility20'] = 'Flexible (permite diferencias de 20%)';
$string ['agreementflexibility40'] = 'Laxo (permite diferencias de 40%)';
$string ['firststagedate'] = 'Fecha límite para corregir';
$string ['firststagedate_help'] = 'Fecha límite en la que los correctores deben corregir todas sus pruebas';
$string ['secondstagedate'] = 'Fecha límite para acuerdo';
$string ['secondstagedate_help'] = 'Fecha límite en la que los correctores deben alcanzar el acuerdo';
$string ['mustseefeedbackbeforejustice'] = 'Debes revisar la corrección de tu prueba antes de entregar tu opinión.';
$string ['reviewpeersfeedback'] = 'Revisar compañeros';
// PREDEFINED COMMENTS.
$string ['datahasheaders'] = 'Ignorar primera fila';
$string ['predefinedcomments'] = 'Comentarios predefinidos';
$string ['predefinedcomments_help'] = 'Pegue una columna de comentarios desde Excel (con o sin encabezado), cada fila se creará como un comentario predefinido.';
$string ['onlyfirstcolumn'] = 'Solo la primera columna es importada. Un ejemplo de los datos a importar se muestra abajo:';
$string ['onecolumnrequired'] = 'Debes ingresar una o mas columnas';
$string ['twolinesrequired'] = 'Debes ingresar dos o mas líneas';
$string ['mobilephoneregex'] = 'Expresión regular de celulares';
$string ['mobilephoneregex_help'] = 'Una expresión regular que valide un número de teléfono celular en su país. (p.ej: +569\d{8})';
$string ['invalidphonenumber'] = 'Número de celular inválido, se requiere un teléfono completo en formato internacional (ej: +56912345678)';
$string ['errorsendingemail'] = 'Se produjo un error con el servidor de correo';
$string ['second'] = 'Segundo';
$string ['seconds'] = 'Segundos';
$string ['processomr'] = 'Procesar OMR';
$string ['signature'] = 'Firma';
$string ['advanced'] = 'Avanzado';
$string ['photo'] = 'Fotografía';
$string ['settingupprinting'] = 'Configurando impresiones';
$string ['printing'] = 'Imprimiendo';
$string ['tokenexpired'] = 'El código de seguridad ha expirado. Obtenga uno nuevo.';
$string ['otherenrolment'] = 'Otro método de matriculación.';
$string ['sent'] = 'Enviada';
$string ['replied'] = 'Contestada';
$string ['usernotloggedin'] = 'Usuario no está logueado';
$string ['invalidsessionkey'] = 'Clave de sesión inválida';
$string ['emarkingsecuritycode'] = 'Código de seguridad eMarking';
$string ['savechanges'] = 'Guardar cambios';
$string ['changessaved'] = 'Cambios guardados exitosamente';
$string ['qualitycontrol'] = 'Control de Calidad';
$string ['markersqualitycontrol'] = 'Correctores asignados a Control de Calidad';
$string ['markersqualitycontrol_help'] = 'Los correctores asignados a Control de Calidad son los que corregirán las pruebas con las que se calculará luego el acuerdo entre correctores.';
$string ['enablequalitycontrol'] = 'Habilitar Control de Calidad';
$string ['enablequalitycontrol_help'] = 'Si se habilita CC, un grupo de pruebas serán asignados a los correctores de CC para que sean corregidos nuevamente y así calcular el acuerdo entre correctores.';
$string ['qualitycontroldescription'] = 'Un grupo de pruebas serán asignadas a los correctores seleccionados para que sean corregidas nuevamente y así calcular el acuerdo entre correctores.';
// MARKERS TRAINING.
$string ['notenoughmarkersfortraining'] = 'No hay suficientes correctores para un entrenamiento. Por favor matricule correctores como profesores sin permiso de edición para realizar el entrenamiento.';
$string ['notenoughmarkersforqualitycontrol'] = 'No ha seleccionado correctores para que realicen el control de calidad. Por favor seleccione al menos un corrector como responsable de corregir las pruebas de control.';
$string ['markerstrainingnotforstudents'] = 'Esta es una actividad de entrenamiento para correctores. Usted no tiene acceso a ella.';
$string ['updatemark'] = 'Cambiar corrección';
// PEER REVIEW.
$string ['notenoughstudenstforpeerreview'] = 'No hay suficientes estudiantes para revisión entre pares';
$string ['reassignpeers'] = 'Reasignar pares';
// ANONYMOUS.
$string ['studentanonymous_markervisible'] = 'Estudiante anónimo / Corrector visible';
$string ['studentanonymous_markeranonymous'] = 'Estudiante anónimo / Corrector anónimo';
$string ['studentvisible_markervisible'] = 'Estudiante visible / Corrector visible';
$string ['studentvisible_markeranonymous'] = 'Estudiante visible / Corrector anónimo';
$string ['anonymous'] = 'Corrección anónima';
$string ['yespeerisanonymous'] = 'Si (Par es anónimo)';
$string ['anonymous_help'] = 'Seleccione para que el proceso de corrección sea anónimo, en cuyo caso los nombres de los estudiantes serán escondidos.';
$string ['anonymousstudent'] = 'Estudiante anónimo';
$string ['viewpeers'] = 'Estudiantes ven pruebas de otros estudiantes';
$string ['viewpeers_help'] = 'Se le permite a los estudiantes revisar pruebas de sus compañeros de manera anónima';
// EMARKING IMPORT RUBRIC.
$string ['rubriclevel'] = 'Nivel';
$string ['importrubric'] = 'Importar rúbrica';
$string ['pastefromexcel'] = 'Pegar desde Excel';
$string ['pastefromexcel_help'] = 'Seleccione las celdas deseadas en Excel, cópielas y luego péguelas en el cuadro de texto';
$string ['rubricneeded'] = 'eMarking requiere el uso de rúbricas para la corrección. Por favor cree una manualmente o impórtela desde Excel.';
$string ['rubricdraft'] = 'eMarking requiere una rúbrica lista, la rúbrica se encuentra en estado de borrador. Por favor completar rúbrica';
$string ['confirmimport'] = 'A continuación se muestra la rúbrica que será creada, por favor verifique que todos los detalles están correctos. NOTA: La rúbrica puede modificarse posteriormente en el editor.';
// E-MARKING TYPES.
$string ['markingtype'] = 'Tipo de corrección';
$string ['markingtype_help'] = '<h2>Tipos de corrección</h2><br>
		Hay cuatro tipos de corrección en eMarking:
		<ul>
			<li><b>Solo impresión</b>: Las pruebas se imprimen a través del sistema, la corrección es manual, y opcionalmente se pueden subir las notas al libro de calificaciones.</li>
			<li><b>Imprimir y digitalizar</b>: Las pruebas se imprimen a través del sistema, la corrección es manual, las respuestas se digitalizan y se suben al sistema como respaldo. Opcionalmente se pueden subir las notas al libro de calificaciones.</li>
            <li><b>Corrección en pantalla</b>: Pruebas se imprimen, digitalizan y la corrección se realiza en línea de acuerdo a una rúbrica. Las pruebas pueden ser corregidas por más de un corrector para control de calidad.</li>
			<li><b>Entrenamiento de correctores</b>: Las pruebas no pertenecen a estudiantes del curso. Todos los correctores corrigen todas las pruebas y deben alcanzar un acuerdo de 100% para finalizar el proceso.</li>
			<li><b>Entrenamiento de estudiantes</b>: Las pruebas no pertenecen a estudiantes del curso. Los estudiantes corrigen como práctica para su próxima evaluación.</li>
			<li><b>Revisión entre pares</b>: Los estudiantes se corrigen entre si de acuerdo a la configuración de grupos. Si hay grupos configurados (visibles o separados), cada estudiante corrige todas las pruebas de otro grupo.</li>
		</ul>';
$string ['type_normal'] = 'Corrección en pantalla';
$string ['type_markers_training'] = 'Entrenamiento de correctores';
$string ['type_student_training'] = 'Entrenamiento de estudiantes';
$string ['type_peer_review'] = 'Revisión entre pares';
$string ['type_print_only'] = 'Solo impresión';
$string ['type_print_scan'] = 'Imprimir y digitalizar';
// EMARKING PRINTING.
$string ['digitizedanswersreminder'] = 'Recordatorio pruebas digitalizadas';
$string ['daysbeforedigitizingreminder'] = 'Días previos al recordatorio';
$string ['daysbeforedigitizingreminder_help'] = 'Número de días tras el cual se enviará el mensaje recordatorio a profesores respecto de la digitalización de sus pruebas.';
$string ['digitizedanswersmessage'] = 'Mensaje del recordatorio de pruebas digitalizadas';
$string ['digitizedanswersmessage_desc'] = 'Este mensaje se enviará a profesores luego de cumplido un período de días tras la digitalización de sus pruebas.';
$string ['viewadminprints'] = '<a href="{$a}">Administrar impresoras</a>';
$string ['viewpermitsprinters'] = '<br/><a href="{$a}">Administrar permisos de impresoras</a>';
$string ['aofb'] = '{$a->identified} de {$a->total}';
$string ['printserver'] = 'Servidor de impresiones (número IP)';
$string ['printserver_help'] = 'Hace que Moodle imprima las evaluaciones de E-Marking en un servidor de impresiones cups (dejar en blanco si no utiliza un servidor de impresiones).';
// EMARKING UPLOAD ANSWERS.
$string ['confirmprocess'] = 'Confirmar proceso';
$string ['confirmprocessfile'] = 'You are about to process file {$a->file} as student submissions for assignment {$a->assignment}.<br> This will delete any previous submissions from students on that assignment. Are you sure?';
$string ['uploadanswers'] = 'Subir respuestas digitalizadas';
$string ['uploadanswers_help'] = 'En esta página usted puede subir las respuestas digitalizadas de sus estudiantes. El format es un archivo ZIP que contiene dos archivos de imagen PNG por cada página de la prueba (una de ellas es la versión anónima). Este archivo se obtiene usando la aplicación eMarking desktop que se puede descargar <a href="">aquí</a>';
$string ['uploadanswersuccessful'] = 'Respuestas digitalizadas subidas exitosamente';
// REPORTS.
$string ['reports'] = 'Reportes';
$string ['gradereport'] = 'Grades report';
$string ['gradereport_help'] = 'This report shows basic statistics and a three graphs. It includes the grades from a particular eMarking activity but other activities from other courses can be added if the parallel courses settings are configured.<br/>
			<strong>Basic statistics:</strong>Shows the average, quartiles and ranges for the course.<br/>
			<strong>Average graph:</strong>Shows the average and standard deviation.<br/>
			<strong>Grades histogram:</strong>Shows the number of students per range.<br/>
			<strong>Approval rate:</strong>Shows the approval rate for the course.<br/>
			<strong>Criteria efficiency:</strong>Shows the average percentage of the maximum score obtained by the students.';
$string ['stdev'] = 'Desviación';
$string ['min'] = 'Mínimo';
$string ['quartile1'] = '1er Cuartil';
$string ['median'] = 'Mediana';
$string ['quartile3'] = '3er Cuartil';
$string ['max'] = 'Máximo';
$string ['lessthan'] = 'Menor {$a}';
$string ['between'] = '{$a->min} a {$a->max}';
$string ['greaterthan'] = 'Mayor {$a}';
$string ['pagesperexam'] = 'Páginas por prueba';
$string ['printdetails'] = 'Detalles impresión';
$string ['apply'] = 'Aplicar';
$string ['statuspercriterion'] = 'Avance por pregunta';
// EMARKING COST REPORT.
$string ['period'] = 'Periodo';
$string ['subcategoryname'] = 'Nombre de la sub-categoría';
$string ['reports'] = 'Reportes';
$string ['teacherrankingtitle'] = 'Ranking: nombre del profesor v/s actividades';
$string ['courserankingtitle'] = 'Ranking: nombre del curso v/s páginas impresas';
$string ['costreport'] = 'Reportes';
$string ['totalactivies'] = 'Número de actividades';
$string ['emarkingcourses'] = 'Cursos con eMarking';
$string ['meantestlenght'] = 'Largo promedio de prueba';
$string ['totalprintedpages'] = 'Hojas totales impresas';
$string ['reportbuttonsheader'] = 'Costos de eMarking';
$string ['secondarybuttonsheader'] = 'Costos de sub-categorías de eMarking';
$string ['courseranking'] = 'Nombre del cursos';
$string ['teacherranking'] = 'Nombre del profesores';
$string ['printingcost'] = 'Costo por hoja impresa';
$string ['printingcost_help'] = 'El costo monetario de cada hoja impresa';
$string ['totalprintingcost'] = 'Costo total de impresiones';
$string ['costsettings'] = 'Configuración';
$string ['costconfigtab'] = 'Definir costo de categorías';
$string ['costcategorytable'] = 'Ver costo de categorías';
$string ['editcost'] = 'Editar';
$string ['activities'] = 'Actividades';
$string ['emarkingcourses'] = 'Cursos con eMarking';
$string ['meanexamlength'] = 'Largo promedio prueba';
$string ['totalprintedpages'] = 'Paginas impresas';
$string ['totalcost'] = 'Costo total';
$string ['numericvalue_help'] = 'Ingresar un valor numérico para el costo por hoja';
$string ['numericvalue'] = 'Ingresa un valor numérico';
$string ['validcostcenter'] = 'Ingresa un valor numérico';
$string ['validcostcenter_help'] = 'Ingresar un número válido para el centro de costos';
$string ['categoryselection_help'] = 'Selecciona la categoria que deseas agregar/editar';
$string ['categoryselect_help'] = 'Selecciona la categoria a la que deseas ir';
$string ['categoryselect'] = 'Elegir una categoria';
$string ['categoryselection'] = 'Elegir una categoria';
$string ['downloadexcel'] = 'Descargar excel';
$string ['costbyperiod'] = 'Costos por periodo';
$string ['categorynavegation'] = 'Navegación por categoría';
$string ['category'] = 'Categoría';
$string ['categorycost'] = 'Costo de categoría';
$string ['costcenter'] = 'Central de costos';
$string ['costofonepage'] = 'Costo impresión por hoja';
$string ['costcenternumber'] = 'Número central de costo';
$string ['costremember'] = 'Recuerde que en el gráfico principal se muestra información de cursos que no se encuentran en las categorías inferiores';
$string ['month'] = 'Mes';
$string ['year'] = 'a&ntilde;o';
$string ['gotosubcategory'] = 'Bajar';
$string ['gotouppercategory'] = 'Subir';
$string ['coursename'] = 'Nombre del curso';
$string ['teachername'] = 'Nombre del profesor';
$string ['studentnumber'] = 'Número de estudiantes';
$string ['categorychart'] = 'Gráfico de la categoría';
$string ['subcategorychart'] = 'Gráfico de la sub-categoría';
$string ['changeconfiguration'] = 'Modificar configuración de costos';
$string ['cost'] = 'Costo por hoja';
$string ['exammodification'] = 'Ingresar nuevo valor para costo de impresión y central de costos';
$string ['numericplease'] = 'Ingresa un valor numérico';
$string ['costconfiguration'] = 'Configuración de costo';
$string ['costconfiguration_help'] = 'Para un analisis de costos correcto, ingrese un costo por hoja impresa distinto de 0';
$string ['defaultcost'] = 'Costo de impresión';
$string ['defaultcost_cost'] = 'Costo por defecto de imprimir una hoja';
$string ['invalidcustommarks'] = 'Marcadores personalizados inválidos, línea(s): ';
$string ['exporttoexcel'] = 'Exportar a Excel';
$string ['exportgrades'] = 'Exportar calificaciones';
$string ['exportagreement'] = 'Exportar acuerdo';
$string ['comparativereport'] = 'Comparativo';
$string ['comparativereport_help'] = 'Comparativo';
$string ['rubrcismustbeidentical'] = 'Las rúbricas deben ser idénticas para poder comparar';
$string ['gradescheck'] = 'La nota mínima no puede ser igual o mayor que la nota máxima.';
$string ['adjustslope'] = 'Ajustar pendiente de calificaciones';
$string ['adjustslope_help'] = 'Ajusta cómo eMarking calculará la calificación final, usando una calificación y puntaje de ajuste. Las nuevas calificaciones se calcularán linealmente con una pendiente entre 0 puntos y la calificación mínima, y la calificación/puntaje de ajuste, para luego continuar hasta la calificación máxima.';
$string ['adjustslopegrade'] = 'Calificación de ajuste';
$string ['adjustslopegrade_help'] = 'La calificación usada para calcular la pendiente de ajuste, i.e. entre la calificación mínima y la calificación de ajuste.';
$string ['adjustslopescore'] = 'Puntaje de ajuste';
$string ['adjustslopescore_help'] = 'El puntaje usado para calcular la pendiente de ajuste, i.e. entre 0 y el puntaje de ajuste.';
$string ['adjustslopegrademustbegreaterthanmin'] = 'Calificación de ajuste debe ser mayor que la calificación mínima';
$string ['adjustslopegrademustbelowerthanmax'] = 'Calificación de ajuste debe ser menor que la calificación máxima';
$string ['adjustslopescoregreaterthanzero'] = 'Puntaje de ajuste debe ser mayor que 0';
$string ['linkrubric'] = 'Rúbrica multicolor';
$string ['linkrubric_help'] = 'Una rúbrica multicolor mostrará un color diferente para cada criterio, tanto para las correcciones (cruces o ticks) como para los comentarios.';
$string ['collaborativefeatures'] = 'Colaboración entre correctores';
$string ['collaborativefeatures_help'] = 'Habilita el chat, el muro y el SOS para la colaboración de correctores. El chat permite a correctores comunicarse entre si. El muro permite a supervisores (profesor o administrador) enviar mensajes, los correctores no pueden escribir en el muro. El SOS permite a correctores solicitar ayuda respecto de una prueba que están corrigiendo.';
$string ['includeenrolments'] = 'Incluir estudiantes de';
$string ['enrolments'] = 'Métodos de matriculación';
$string ['enrolments_help'] = 'Solo se incluirán los estudiantes matriculados en los métodos de matriculación seleccionados.';
$string ['enrolmanual'] = 'Matriculaciones manuales';
$string ['enrolself'] = 'Auto-Matriculaciones';
$string ['enroldatabase'] = 'Matriculaciones de base de datos externa';
$string ['enrolmeta'] = 'Matriculaciones de metacurso';
$string ['enrolcohort'] = 'Matriculaciones por cohorte';
$string ['includestudentsinexam'] = 'Matriculaciones que incluir para impresión personalizada';
$string ['permarkercontribution'] = 'Contribución por corrector';
$string ['permarkerscores'] = 'Puntajes por corrector';
$string ['markingstatusincludingabsents'] = 'Avance por estado (incluyendo ausentes)';
$string ['markingreport'] = 'Avance';
$string ['markingreport_help'] = 'Este reporte muestra el avance de la corrección';
$string ['of'] = 'de';
$string ['missingpages'] = 'Faltan páginas';
$string ['transactionsuccessfull'] = 'Transacción exitosa';
$string ['setasabsent'] = 'Ausente';
$string ['setassubmitted'] = 'Presente';
$string ['markers'] = 'Correctores';
$string ['saved'] = 'Cambios guardados';
$string ['downloadform'] = 'Descargar formulario de impresión';
$string ['selectprinter'] = 'Escoger impresora';
$string ['enableprinting'] = 'Habilitar impresión desde Moodle';
$string ['enableprinting_help'] = 'Habilita utilizar cups (lp) para imprimir desde el servidor de Moodle a una impresora en red';
$string ['printername'] = 'Nombre de la impresora en red';
$string ['printername_help'] = 'Nombre de la impresora de acuerdo a la configuración de cups';
$string ['yourcodeis'] = 'Su código de seguridad es';
$string ['minimumdaysbeforeprinting'] = 'Días de anticipación para enviar pruebas';
$string ['minimumdaysbeforeprinting_help'] = 'Los profesores podrán enviar pruebas a impresión al menos este número de días antes, después no se permitirá. Si se configuran en 0 días se deshabilita la verificación.';
$string ['parallelcourses'] = 'Cursos paralelos';
$string ['configuration'] = 'Configuración';
$string ['overallfairnessrequired'] = 'El campo es obligatorio';
$string ['expectationrealityrequired'] = 'El campo es obligatorio';
$string ['choose'] = 'Escoger';
$string ['markingduedate'] = 'Fecha límite de corrección';
$string ['markingduedate_help'] = 'Define una fecha límite para genrar recordatorios para correctores y profesores';
$string ['enableduedate'] = 'Habilitar fecha límite de corrección';
$string ['verifyregradedate'] = 'Verificar que la fecha de apertura es menor que la de cierre';
$string ['emarkingprints'] = 'Emarking impresoras';
$string ['adminprints'] = 'Administrar impresoras';
$string ['permitsviewprinters'] = 'Permisos para ver impresoras';
$string ['notallowedprintermanagement'] = 'No tiene permitido acceder a la administración de impresoras';
$string ['printerdoesnotexist'] = 'La impresora no existe';
$string ['ip'] = 'ip';
$string ['commandcups'] = 'Comando cups';
$string ['insertiondate'] = 'Fecha de inclusión';
$string ['delete'] = 'Eliminar';
$string ['doyouwantdeleteprinter'] = '¿Quieres eliminar esta impresora?';
$string ['edit'] = 'Editar';
$string ['doyouwanteditprinter'] = '¿Quieres editar esta impresora?';
$string ['addprinter'] = 'Agregar impresora';
$string ['editprinter'] = 'Editar impresora';
$string ['required'] = 'Requerido';
$string ['nameexist'] = 'El nombre de la impresora ya existe';
$string ['ipexist'] = 'La ip ingresa esta relacionada con otra impresora';
$string ['ipproblem'] = 'La ip posee caracteres no numéricos';
$string ['emptyprinters'] = 'No hay impresoras en el sistema';
$string ['selectusers'] = 'Seleccione usuario(s)';
$string ['selectprinters'] = 'Selecione impresora(s)';
$string ['dontexistrelationship'] = 'La relación usuario-impresora no existe';
$string ['username'] = 'Nombre de usuario';
$string ['doyouwantdeleterelationship'] = '¿Quieres eliminar el permiso?';
$string ['managepermissions'] = 'Administrar permisos de impresoras';
$string ['emptypermissions'] = 'No existen permisos';
$string ['addpermission'] = 'Agregar permiso';
$string ['annotatesubmission_help'] = 'eMarking allows to mark digitized exams using rubrics. In this page you can see the course list and their submissions (digitized answers). It also shows the exam status, that can be missing for a student with no answers, submitted if it has not been graded, responded when the marking is finished and regrading when a regrade request was made by a student.';
$string ['regrades_help'] = 'This page shows the regrade requests made by students.';
$string ['ranking'] = 'Ranking';
$string ['areyousure'] = '¿Está seguro?';
$string ['actions'] = 'Acciones';
$string ['annotatesubmission'] = 'Corregir';
$string ['attempt'] = 'Intento';
$string ['average'] = 'Promedio';
$string ['backcourse'] = 'Regresar al curso';
$string ['close'] = 'Cerrar';
$string ['comment'] = 'Comentario';
$string ['completerubric'] = 'Completar rúbrica';
$string ['copycenterinstructions'] = 'Instrucciones para centro de copiado';
$string ['corrected'] = 'Corregido';
$string ['createrubric'] = 'Crear rúbrica';
$string ['criterion'] = 'Criterio';
$string ['criteriaefficiency'] = 'Eficiencia por criterio';
$string ['digitizedfile'] = 'Subir respuestas digitalizadas';
$string ['doubleside'] = 'Doble cara';
$string ['downloadfeedback'] = 'PDF';
$string ['downloadsuccessfull'] = 'Descarga de prueba exitosa';
$string ['email'] = 'Correo';
$string ['emailinstructions'] = 'Ingrese el código de seguridad enviado a: {$a->email}';
$string ['smsinstructions'] = 'Ingrese el código de seguridad enviado al teléfono: {$a->phone2}';
$string ['emarking'] = 'eMarking';
$string ['enrolincludes'] = 'Métodos de matriculación por defecto';
$string ['enrolincludes_help'] = 'Los métodos de matriculación que por defecto se seleccionarán al enviar a imprimir una prueba.';
$string ['errors'] = 'Errores';
$string ['errorprocessingextraction'] = 'Error procesando extracción desde ZIP';
$string ['errorsavingpdf'] = 'Error al guardar archivo ZIP';
$string ['extraexams'] = 'Pruebas extra';
$string ['extraexams_help'] = 'Pruebas extra que se imprimirán con un usuario NN. Es útil para casos en que aparecen estudiantes que no estén inscritos en el sistema.';
$string ['extrasheets'] = 'Hojas extra';
$string ['extrasheets_help'] = 'Número de hojas extra que se incluirán por cada estudiante.';
$string ['fatalerror'] = 'Error fatal';
$string ['fileisnotzip'] = 'El archivo no es el tipo ZIP';
$string ['filerequiredpdf'] = 'Un archivo PDF con las respuestas';
$string ['filerequiredpdf_help'] = 'Se requiere un archivo PDF con las respuestas de los estudiantes digitalizadas';
$string ['filerequiredzip'] = 'Un archivo ZIP con las respuestas';
$string ['filerequiredzip_help'] = 'Se requiere un archivo ZIP con las respuestas de los estudiantes digitalizadas';
$string ['filerequiredtosendnewprintorder'] = 'Se requiere un archivo PDF';
$string ['grade'] = 'Calificación';
$string ['headerqr'] = 'Encabezado personalizado';
$string ['headerqr_help'] = 'El encabezado personalizado de eMarking permite imprimir la prueba personalizada para cada estudiante. Esto permite luego procesarla automáticamente para su corrección y entrega usando la actividad eMarking.<br/>
		Ejemplo de encabezado:<br/>
		<img width="380" src="' . $CFG->wwwroot . '/mod/emarking/img/preview.jpg">
		<div class="required">Advertencia<ul>
				<li>Para usar el encabezado la prueba debe tener un margen superior de al menos 3cm</li>
		</ul></div>';
$string ['headerqrrequired'] = 'Encabezado personalizado es obligatorio para corrección en pantalla.';
$string ['identifieddocuments'] = 'Respuestas subidas';
$string ['idnumber'] = 'RUT';
$string ['ignoreddocuments'] = 'Respuestas ignoradas';
$string ['includelogo'] = 'Incluir logo';
$string ['includelogo_help'] = 'Incluir logo en el encabezado de las pruebas.';
$string ['includeuserpicture'] = 'Incluir imagen de usuario';
$string ['includeuserpicture_help'] = 'Incluir la imagen del usuario en el encabezado de las pruebas';
$string ['initializedirfail'] = 'No se pudo inicializar directorio de trabajo {$a}. Por favor avisar al administrador.';
$string ['invalidaccess'] = 'Acceso inválido, intentando cargar la prueba';
$string ['invalidcategoryid'] = 'Categoría inválida';
$string ['invalidcourse'] = 'Asignación de curso inválida';
$string ['invalidcourseid'] = 'Id del curso inválido';
$string ['invalidcoursemodule'] = 'Módulo del curso inválido';
$string ['invalidexamid'] = 'ID de la prueba inválido';
$string ['invalidfilenotpdf'] = 'Archivo inválido, no es un PDF';
$string ['invalidid'] = 'ID inválido';
$string ['invalididnumber'] = 'N&uacute;mero Id inválido';
$string ['invalidemarkingid'] = 'Id de assignment inválido';
$string ['invalidparametersforpage'] = 'Parámetros inválidos para la página';
$string ['invalidpdfnopages'] = 'Archivo PDF inválido, no se reconocen páginas.';
$string ['invalidpdfnumpagesforms'] = 'Archivos de pruebas deben tener el mismo número de páginas.';
$string ['invalidstatus'] = 'Estado inválido';
$string ['invalidtoken'] = 'Código de seguridad no válido al intentar descargar prueba.';
$string ['invalidzipnoanonymous'] = 'Archivo ZIP inválido, no contiene versiones anónimas de las respuestas. Es posible que haya sido generado con una versión antigua de la herramienta desktop.';
$string ['lastmodification'] = 'Última Modificación';
$string ['logo'] = 'Logo para encabezado';
$string ['logodesc'] = 'Logo para incluir en encabezado de pruebas';
$string ['marking'] = 'Corrección';
$string ['modulename'] = 'E-Marking';
$string ['modulename_help'] = 'El módulo E-Marking permite:<br/>
    <strong>Imprimir</strong>
    <ul>
    <li>Imprima pruebas con hojas personalizadas con el nombre del estudiante, un log y un código QR que facilita la digitalización.</li>
    <li>Imprima la lista de estudiantes para tomar asistencia en la prueba.</li>
    <li>Envíe a imprimir una misma prueba para varios cursos (1).</li>
    </ul>
    <strong>Digitalizar</strong>
    <ul>
    <li>Digitalice las respuestas de los estudiantes y califíquelas de manera sencilla o usando <span style="font-style:italic;">Corrección En Pantalla</span>.</li>
    </ul>
    <strong>Corrección En Pantalla</strong>
    <ul>
    <li>Corrija las pruebas usando rúbricas, marcadores personalizados y comentarios predefinidos para entregar retroalimentación de calidad. Varios correctores pueden colaborar y compartir los comentarios que dejan.</li>
    <li>Corrija anónimamente para que correctores no puedan sesgarse de conocer a un estudiante.</li>
    <li>Corrija doblemente una muestra de las pruebas para control de calidad.</li>
    <li>Ayude a correctores a colaborar interactuando a través de un chat, con un muro de mensajes del supervisor y pidiendo ayuda cuando no estén seguros de una corrección (1).</li>
    <li>Entrene correctores en interpretar una rúbrica usando pruebas seleccionadas y forzándolos a alcanzar un consenso.</li>
    <li>Supervise el proceso de corrección y obtenga reportes por estudiante, por criterio de la rúbrica y por corrector.</li>
    </ul>
    <strong>Retroalimentación</strong>
    <ul>
    <li>Los estudiantes pueden revisar sus pruebas, sus calificaciones y su retroalimentación desde cualquier lugar. Además pueden solicitar recorrecciones.</li>
    <li>Consulte la percepción de justicia de sus estudiantes respecto del proceso de corrección y sus calificaciones.</li>
    <li>Los estudiantes pueden ver un ranking del curso o revisar las pruebas de sus compañeros (anónimamente) para comprender mejor qué hicieron bien o mal.</li>
    </ul>
    (1): Requiere configuración extra del servidor.';
$string ['modulenameplural'] = 'E-Markings';
$string ['motive'] = 'Motivo';
$string ['motive_help'] = 'Indique el motivo de su recorrección para este criterio';
$string ['multicourse'] = 'Multicurso';
$string ['multicourse_help'] = 'Select other course for which this exam will also be printed';
$string ['singlepdf'] = 'PDF único con todos los estudiantes';
$string ['multiplepdfs'] = 'Múltiples pdfs en un archivo zip';
$string ['multiplepdfs_help'] = 'Si se selecciona, eMarking generará un archivo zip que contendrá un pdf personalizado por cada estudiante, si no se generará un solo pdf con todas las pruebas.';
$string ['myexams'] = 'Mis pruebas';
$string ['myexams_help'] = 'Esta página muestra todas las pruebas que han sido enviadas a imprimir para este curso. Usted puede editarlas o incluso cancelarlas mientras no hayan sido descargadas por el centro de copiado.';
$string ['names'] = 'Nombres/Apellidos';
$string ['emailsent'] = 'Security code sent to your email';
$string ['newprintorder'] = 'Enviar prueba a impresión';
$string ['newprintorder_help'] = 'Para enviar una prueba a imprimir debe indicar un nombre para la prueba (p.ej: Prueba 1), la fecha exacta en que se tomará la prueba y un archivo pdf con la prueba misma.<br/>
		<strong>Encabezado personalizado eMarking:</strong> Si escoge esta opción, la prueba será impresa con un encabezado personalizado para cada estudiante, incluyendo su foto si está disponible. Este encabezado permite luego procesar automáticamente las pruebas usando el módulo eMarking, que apoya el proceso de corrección, entrega de calificaciones y recepción de recorrecciones.<br/>
		<strong>Instrucciones para el centro de copiado:</strong> Instrucciones especiales pueden ser enviadas al centro de copiado, tales como imprimir hojas extra por cada prueba o pruebas extra.
		';
$string ['nocostdata'] = 'No hay suficiente información para mostrar costos, confirme que las actividades fueron enviadas a imprimir.';
$string ['nototalcost'] = 'No hay suficiente información para mostrar costos, confirme que sus actividades de eMarking tienen un costo asociado.';
$string ['nocourseranking'] = 'No hay suficiente información para mostrar costos, confirme que hay actividades de eMarking.';
$string ['noteacherranking'] = 'No hay suficiente información para mostrar costos, confirme que las actividades de eMarking fueron enviadas a imprimir.';
$string ['nostudent'] = 'No hay estudiantes en esta categoría, confirme que asigno estudiantes a los cursos.';
$string ['nodata'] = 'No hay datos';
$string ['nopagestoprocess'] = 'Error. El archivo no contiene páginas a procesar, por favor suba las respuestas nuevamente.';
$string ['noprintorders'] = 'No hay órdenes de impresión para este curso';
$string ['nosubmissionsgraded'] = 'No hay pruebas corregidas aún';
$string ['nosubmissionspublished'] = 'No hay calificaciones publicadas aún';
$string ['nosubmissionsselectedforpublishing'] = 'No hay pruebas seleccionadas para publicar sus calificaciones';
$string ['noexamsforprinting'] = 'No hay pruebas para imprimir';
$string ['notcorrected'] = 'Por corregir';
$string ['page'] = 'Página';
$string ['pages'] = 'páginas';
$string ['assignpagestocriteria'] = 'Agregar páginas a criterios';
$string ['parallelregex'] = 'Regex para cursos paralelos';
$string ['parallelregex_help'] = 'Expresión regular para extraer el código del curso a partir del nombre corto, de manera de identificar cursos paralelos.';
$string ['pathuserpicture'] = 'Directorio de imágenes alternativas de usuarios';
$string ['pathuserpicture_help'] = 'Dirección absoluta del directorio que contiene las imágenes alternativas de los usuarios en formato PNG y cuyo nombre calza con userXXX.png en que XXX es el id de usuario. Si está vacío y se incluirá la imagen de usuarios, se utilizará la que el usuario tiene en su perfil.';
$string ['pdffile'] = 'Archivo(s) PDF de la prueba';
$string ['pdffile_help'] = 'Si incluye más de un archivo PDF, éstos se utilizarán como formas diferentes a asignar para los estudiantes.';
$string ['pdffileupdate'] = 'Reemplazar archivo(s) PDF de la prueba';
$string ['pluginadministration'] = 'Administración de emarking';
$string ['pluginname'] = 'eMarking';
$string ['printdoublesided'] = 'Doble cara';
$string ['printdoublesided_help'] = 'Al seleccionar doble cara, e-marking intentará imprimir la prueba por ambos lados del papel. Si CUPS (impresión en línea) no está configurada, se enviarán instrucciones a quien descargue la prueba.';
$string ['printexam'] = 'Imprimir prueba';
$string ['printsendnotification'] = 'Enviar notificación de impresión';
$string ['printrandom'] = 'Impresión aleatoria';
$string ['printrandominvalid'] = 'Debe crear un grupo para utilizar esta función';
$string ['printrandom_help'] = 'Impresión aleatoria basada en un grupo creado en un curso especifico';
$string ['printlist'] = 'Lista de estudiantes';
$string ['printlist_help'] = 'Se utiliza para imprimir una lista de los estudiantes del curso';
$string ['printnotification'] = 'Notificación';
$string ['printnotificationsent'] = 'Notificación de impresión enviada';
$string ['printorders'] = 'Órdenes de impresión';
$string ['processtitle'] = 'Subir respuestas';
$string ['publishselectededgrades'] = 'Publicar calificaciones seleccionadas';
$string ['publishtitle'] = 'Publicar calificaciones';
$string ['publishedgrades'] = 'Calificaciones publicadas';
$string ['publishinggrade'] = 'Publicando calificación';
$string ['publishinggradesfinished'] = 'Publicación de calificaciones finalizada';
$string ['qrdecodingfinished'] = 'Decodificación de QR finalizada';
$string ['qrprocessingtitle'] = 'Software para procesar respuestas';
$string ['qrprocessing'] = 'Descargar software para procesar respuestas';
$string ['records'] = 'Historial';
$string ['regrades'] = 'Recorrección';
$string ['regraderequest'] = 'Solicitud de recorrección';
$string ['requestedby'] = 'Solicitado Por';
$string ['results'] = 'Resultados';
$string ['selectcategory'] = 'Seleccione categoría';
$string ['selectall'] = 'Seleccionar todas';
$string ['selectnone'] = 'Seleccionar ninguna';
$string ['settings'] = 'Configuración';
$string ['settingsadvanced'] = 'Configuración avanzada';
$string ['settingsadvanced_help'] = 'Configuración avanzada para eMarking';
$string ['settingssecurity'] = 'Configuración de seguridad';
$string ['settingssecurity_help'] = 'Se puede agregar seguridad extra usando el servicio SMS de Twilio.com para validar la descarga de pruebas usando mensajes de texto.';
$string ['smspassword'] = 'Token de autorización de Twilio.com';
$string ['smspassword_help'] = 'Token de autorización de la cuenta en Twilio.com';
$string ['smsserverproblem'] = 'Error conectándose con servidor SMS';
$string ['smsurl'] = 'Número de teléfono Twilio.com';
$string ['smsurl_help'] = 'El número de teléfono internacional de su servicio contratado con Twilio.com. Es el que va como From en el mensaje.';
$string ['smsuser'] = 'Id de cuenta Twilio.com';
$string ['smsuser_help'] = 'Id de la cuenta en Twilio.com para el servicio de SMS';
$string ['specificmarks'] = 'Marcadores personalizados';
$string ['specificmarks_help'] = 'Marcadores personalizados, uno por línea separando código y descripción por un # por ejemplo:<br/>Oa#Ortografía acentual<br/>Op#Ortografía puntual<br/>G#Gramática';
$string ['statistics'] = 'Estadísticas';
$string ['statisticstotals'] = 'Estadísticas acumuladas';
$string ['status'] = 'Estado';
$string ['statusaccepted'] = 'Aceptada';
$string ['statusabsent'] = 'Ausente';
$string ['statusgrading'] = 'En corrección';
$string ['statusgradingfinished'] = 'Corregida';
$string ['statusmissing'] = 'No entregada';
$string ['statusnotsent'] = 'No enviada';
$string ['statusregrading'] = 'En recorrección';
$string ['statusregradingresponded'] = 'Recorregida';
$string ['statuspublished'] = 'Publicada';
$string ['statussubmitted'] = 'Subida';
$string ['statuserror'] = 'Error';
$string ['totalexams'] = 'Exámenes totales';
$string ['totalpagesprint'] = 'Páginas totales a imprimir';
$string ['uploadexamfile'] = 'Archivo Zip';
$string ['uploadinganswersheets'] = 'Subiendo respuestas de los estudiantes';
$string ['usesms'] = 'Usar Twilio.com para enviar SMS';
$string ['usesms_help'] = 'Usar mensaje SMS en vez de correo electrónico para verificar códigos de seguridad de eMarking';
$string ['viewsubmission'] = 'Ver prueba';
$string ['formnewcomment'] = 'Texto del comentario';
$string ['writecomment'] = 'Escriba un Comentario';
$string ['createcomment'] = 'Crear Comentario';
$string ['formeditcomment'] = 'Editar Comentario:';
$string ['editcomment'] = 'Editar Comentario';
$string ['adjustments'] = 'Ajustes';
$string ['questiondeletecomment'] = '¿Desea borrar el comentario?';
$string ['creator'] = 'Creador';
$string ['details'] = 'Detalles';
$string ['originals'] = 'Originales';
$string ['copies'] = 'Copias';
$string ['teacher'] = 'Profesor';
$string ['gradehistogram'] = 'Histograma de notas por curso';
$string ['gradehistogramtotal'] = 'Histograma de notas agregado';
$string ['courseaproval'] = 'Aprobación de curso';
$string ['range'] = 'Rango';
$string ['marker'] = 'Corrector';

// Events.
$string ['eventemarkinggraded'] = 'Emarking';
$string ['eventrotatepageswitched'] = 'Rotar pagina';
$string ['eventaddcommentadded'] = 'Agregar comentario';
$string ['eventaddregradeadded'] = 'Agregar recorreccion';
$string ['eventdeletecommentdeleted'] = 'Borrar Comentario';
$string ['eventaddmarkadded'] = 'Agregar marca';
$string ['eventdeletemarkdeleted'] = 'Borrar Marca';
$string ['eventinvalidaccessgranted'] = 'Acceso inválido, intentando cargar la prueba';
$string ['eventsuccessfullydownloaded'] = 'Descarga de prueba exitosa';
$string ['eventinvalidtokengranted'] = 'Código de seguridad no válido al intentar descargar prueba.';
$string ['eventunauthorizedccessgranted'] = 'WARNING: Acceso no autorizado a la Interfaz Ajax de eMarking';
$string ['eventmarkersconfigcalled'] = 'Se ingresa al markers config';
$string ['eventmarkersassigned'] = 'Correctores han sido assignado';
$string ['eventemarkingcalled'] = 'Llamada al emarking';
// Delphi's strings.
$string ['marking_progress'] = 'Progreso de corrección';
$string ['delphi_stage_one'] = 'Corrección';
$string ['marking_deadline'] = 'Termina en';
$string ['stage_general_progress'] = 'Progreso general';
$string ['delphi_stage_two'] = 'Discusión';
$string ['marking_completed'] = 'Bien hecho, ahora debes esperar a los demás correctores para la etapa dos.';
$string ['stage'] = 'Etapa';
$string ['agreement'] = 'Acuerdo';
$string ['yourmarking'] = 'Tu corrección';