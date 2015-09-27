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
// EMARKING TYPES WORKFLOW
$string['orsentexam'] = 'Asociar prueba impresa';
$string['orsentexam_help'] = 'Puede asociar una prueba que fue enviada a imprimir previamente.';
$string['print'] = 'Imprimir';
$string['onscreenmarking'] = 'Corrección en pantalla';
$string['scan'] = 'Digitalizar';
$string['none'] = 'Ninguno';
$string['activatemodules'] = 'Activar módulos';
$string['enablescan'] = 'Habilitar digitalización';
$string['scanisenabled'] = 'Digitalización está habilitada. La corrección es manual, las respuestas se digitalizan y se suben al sistema como respaldo.';
$string['scanwasenabled'] = 'Digitalización habilitada exitosamente.';
$string['osmisenabled'] = 'Digitalización está habilitada. Las respuestas se digitalizan y se suben al sistema para ser corregidas en pantalla usando una rúbrica.';
$string['enableosm'] = 'Habilitar corrección en pantalla';
$string['enableosm_help'] = 'Debe habilitar la digitalización para poder habilitar la corrección en pantalla';
$string['osmwasenabled'] = 'Corrección en pantalla habilitada exitosamente.';
$string['updateemarkingtype'] = 'Usted va a {$a->message} en {$a->name}. No hay riesgos en hacer esto, usted puede cambiarlo después en los ajustes de la actividad en cualquier momento.';

$string['printsettings'] = 'Configuración de impresión';
$string['printsettings_help'] = 'Help for print settings';

$string['selectemarkingtype'] = 'Seleccione...';
$string['markingtypemandatory'] = 'Debe seleccionar un tipo de corrección';
$string['selectexam'] = 'Enviar después';
$string['exam_help'] = 'Debe asociar esta corrección a una prueba enviada a imprimir. Seleccione la prueba entre las que ya se han enviado o indique que la enviará después.';

$string ['emarkingviewed'] = 'Ver prueba';

// REGRADES
$string ['justification'] = 'Justificación';
$string ['justification_help'] = 'Usted debe justificar su solicitud de recorrección';
$string ['noregraderequests'] = 'No hay solicitudes de recorrección';
$string ['regradedatecreated'] = 'Fecha creación';
$string ['regradelastchange'] = 'Último cambio';
$string ['score'] = 'Puntaje';
$string ['markingcomment'] = 'Comentario de corrección';
$string ['regrade'] = 'Recorrección';
$string ['regradingcomment'] = 'Comentario de recorrección';
$string ['missasignedscore'] = 'Puntaje mal calculado';
$string ['unclearfeedback'] = 'La corrección no explica qué está incorrecto';
$string ['statementproblem'] = 'Problemas con el enunciado';
$string ['errorcarriedforward'] = 'Error de arrastre';
$string ['correctalternativeanswer'] = 'Mi respuesta es correcta aunque no idéntica a la pauta';
$string ['other'] = 'Otro';
$string ['regradespending'] = 'recorrecciones';
$string ['regraderestricted'] = 'Ya no se aceptan nuevas solicitudes de recorrección. El período de recorrecciones cerró el {$a->regradesclosedate}.';
$string ['regraderestrictdates'] = 'Restringir fechas para recorrecciones';
$string ['regraderestrictdates_help'] = 'Los estudiantes podrán solicitar recorrecciones solamente dentro de límites de fecha de apertura y cierre.';
$string ['regradesopendate'] = 'Apertura recorrecciones';
$string ['regradesopendate_help'] = 'Fecha desde la cual los estudiantes pueden enviar solicitudes de recorrección';
$string ['regradesclosedate'] = 'Cierre recorrecciones';
$string ['regradesclosedate_help'] = 'Fecha límite para que los estudiantes envíen solicitudes de recorrección';
$string ['mustseeexambeforeregrade'] = 'Debes revisar la corrección de tu prueba antes de solicitar recorrección.';

$string ['markingduedate'] = 'Fecha límite corrección';
$string ['markingduedate_help'] = 'Si define una fecha límite para finalizar la corrección, ésta se usará para notificar a correctores y profesores respecto del avance de la corrección.';
$string ['enableduedate'] = 'Habilitar fecha límite';
$string ['verifyregradedate'] = 'Verificar que la fecha de apertura sea menor que el cierre';
$string ['original'] = 'Original';

// MARKERS AND PAGES OSM CONFIGURATION
$string['markerspercriteria']='Correctores por criterio';
$string['pagespercriteria']='Páginas por criterio';
$string['markerscanseewholerubric']='Correctores pueden ver la rúbrica completa.';
$string['markerscanseeallpages'] = 'Correctores ven todas las páginas.';
$string['markerscanseeselectedcriteria'] = 'Correctores ven solamente criterios que se le han asignado.';
$string['markerscanseenothing'] = 'Hay páginas asignadas a criterios, pero no correctores. Esto provocará que solo los administradores puedan ver las páginas.';
$string['markerscanseepageswithcriteria'] = 'Correctores ven solo las páginas de los criterios que tienen asignados.';
$string['assignedmarkers'] = 'Correctores asignados';
$string['currentstatus'] = 'Configuración actual';

// GENERAL
$string['criteria'] = 'Criterios';
$string['deleterow'] = 'Borrar fila';

$string['nodejspath'] = 'Ruta de NodeJS';
$string['nodejspath_help'] = 'Ruta completa del servidor Node JS incluyendo protocolo, dirección ip y puerto. p.ej: http://127.0.0.1:9091';

// SMS SECURITY
$string ['download'] = 'Descargar';
$string ['cancel'] = 'Cancelar';
$string ['resendcode'] = 'Reenviar código de seguridad';
$string ['smsservertimeout'] = 'Se agotó el tiempo de espera para enviar el código. Por favor avise al administrador.';
$string ['smsservererror'] = 'Tuvimos problemas de comunicación con el servidor de mensajes celulares. Por favor reintente más tarde.';
    
// EXAM
$string ['examdetails'] = 'Detalles de la prueba';
$string ['examalreadysent'] = 'La prueba ya fue impresa, no puede modificarse.';
$string ['examdate'] = 'Fecha y hora de la prueba';
$string ['examdate_help'] = 'La fecha y hora en que se tomará la prueba. Solo se pueden solicitar impresiones con al menos 48 horas de anticipación (sin incluir fines de semana).';
$string ['examdateinvalid'] = 'Solo se pueden solicitar impresiones con al menos {$a->mindays} días de anticipación (sin incluir fines de semana)';
$string ['examdateinvaliddayofweek'] = 'Fecha de prueba inválida, solo de Lunes a Viernes y Sábados hasta las 4pm.';
$string ['examdateprinted'] = 'Fecha de impresión';
$string ['examdatesent'] = 'Fecha de envío';
$string ['examdeleteconfirm'] = 'Está a punto de borrar {$a}. ¿Desea continuar?';
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

// JUSTICE PERCEPTION
$string['er-4'] = '-4 (mucho peor de lo que merecía)';
$string['er-3'] = '-3';
$string['er-2'] = '-2';
$string['er-1'] = '-1';
$string['er0'] = '0 (más o menos lo que merecía)';
$string['er1'] = '1';
$string['er2'] = '2';
$string['er3'] = '3';
$string['er4'] = '4 (mucho más de lo que merecía)';
$string['of-4'] = '-4 (extremadamente injusta)';
$string['of-3'] = '-3';
$string['of-2'] = '-2';
$string['of-1'] = '-1';
$string['of0'] = '0 (ni justa ni injusta)';
$string['of1'] = '1';
$string['of2'] = '2';
$string['of3'] = '3';
$string['of4'] = '4 (extremadamente justa)';
$string ['justiceperceptionprocess'] = '¿Cómo evaluaría cuan justa fue la corrección de esta evaluación?';
$string ['justiceperceptionexpectation'] = '¿Cómo se compara su calificación en esta evaluación con la que usted piensa que se merecía?';
$string['justiceperceptionprocesscriterion'] = '¿Cómo evaluaría cuan justa fue la corrección de cada pregunta?';
$string['justiceperceptionexpectationcriterion'] = '¿Cómo se compara su puntaje en cada pregunta con el que usted piensa que se merecía?';
$string ['thanksforjusticeperception'] = 'Gracias por expresar su opinión';
$string['justicedisabled'] = 'Deshabilitada';
$string['justicepersubmission'] = 'Solicitar una opinión por prueba';
$string['justicepercriterion'] = 'Solicitar una opinión por criterio de la rúbrica';
$string['justice'] = 'Justicia';
$string['justiceperception'] = 'Preguntar percepción de justicia';
$string['justiceperception_help'] = 'Esta opción permite a los estudiantes entregar su percepción de justicia respecto del proceso de corrección (justicia procedural) y su resultado (justicia distributiva). Se puede preguntar por la prueba en general o por cada criterio de la rúbrica.';
$string['agreementflexibility'] = 'Flexibilidad de acuerdo';
$string['agreementflexibility_help'] = 'Define la diferencia máxima entre las calificaciones entregadas por un corrector y el promedio de los demás correctores para ser considerado fuera de rango.';
$string['agreementflexibility00'] = 'Estricto (calificaciones deben ser iguales)';
$string['agreementflexibility20'] = 'Flexible (permite diferencias de 20%)';
$string['agreementflexibility40'] = 'Laxo (permite diferencias de 40%)';
$string['firststagedate'] = 'Fecha límite para corregir';
$string['firststagedate_help'] = 'Fecha límite en la que los correctores deben corregir todas sus pruebas';
$string['secondstagedate'] = 'Fecha límite para acuerdo';
$string['secondstagedate_help'] = 'Fecha límite en la que los correctores deben alcanzar el acuerdo';
$string['mustseefeedbackbeforejustice'] = 'Debes revisar la corrección de tu prueba antes de entregar tu opinión.';

// PREDEFINED COMMENTS
$string['emarkingsecuritycode'] = 'Código de seguridad eMarking';
$string['datahasheaders'] = 'Ignorar primera fila';
$string['confirmimportpredefinedcomments'] = 'Los comentarios que se muestran en vista previa serán importados. ¿Está seguro(a)?';
$string['addpredefinedcomments'] = 'Importar comentarios desde Excel';
$string['predefinedcomments'] = 'Comentarios predefinidos';
$string['predefinedcomments_help'] = 'Pegue una columna de comentarios desde Excel (con o sin encabezado), cada fila se creará como un comentario predefinido.';
$string['onlyfirstcolumn'] = 'Solo la primera columna es importada. Un ejemplo de los datos a importar se muestra abajo:';

$string['mobilephoneregex'] = 'Expresión regular de celulares';
$string['mobilephoneregex_help'] = 'Una expresión regular que valide un número de teléfono celular en su país. (p.ej: +569\d{8})';
$string['invalidphonenumber'] = 'Número de celular inválido, se requiere un teléfono completo en formato internacional (ej: +56912345678)';
$string['errorsendingemail'] = 'Se produjo un error con el servidor de correo';
$string['second'] = 'Segundo';

$string['processomr'] = 'Procesar OMR';

$string['signature'] = 'Firma';
$string['advanced'] = 'Avanzado';
$string['photo'] = 'Fotografía';
$string['settingupprinting'] = 'Configurando impresiones';
$string['printing'] = 'Imprimiendo';
$string['tokenexpired'] = 'El código de seguridad ha expirado. Obtenga uno nuevo.';
$string['otherenrolment'] = 'Otro método de matriculación.';
$string['sent'] = 'Enviada';
$string['replied'] = 'Contestada';

$string['usernotloggedin'] = 'Usuario no está logueado';
$string['invalidsessionkey'] = 'Clave de sesión inválida';

$string['savechanges'] = 'Guardar cambios';
$string ['changessaved'] = 'Cambios guardados exitosamente';

$string['qualitycontrol'] = 'Control de Calidad';
$string['markersqualitycontrol'] = 'Correctores asignados a Control de Calidad';
$string['markersqualitycontrol_help'] = 'Los correctores asignados a Control de Calidad son los que corregirán las pruebas con las que se calculará luego el acuerdo entre correctores.';
$string['enablequalitycontrol'] = 'Habilitar Control de Calidad';
$string['enablequalitycontrol_help'] = 'Si se habilita CC, un grupo de pruebas serán asignados a los correctores de CC para que sean corregidos nuevamente y así calcular el acuerdo entre correctores.';
$string['notenoughmarkersfortraining'] = 'No hay suficientes correctores para un entrenamiento. Por favor matricule correctores como profesores sin permiso de edición para realizar el entrenamiento.';
$string['notenoughmarkersforqualitycontrol'] = 'No ha seleccionado correctores para que realicen el control de calidad. Por favor seleccione al menos un corrector como responsable de corregir las pruebas de control.';

// ANONYMOUS
$string['studentanonymous_markervisible'] = 'Estudiante anónimo / Corrector visible';
$string['studentanonymous_markeranonymous'] = 'Estudiante anónimo / Corrector anónimo';
$string['studentvisible_markervisible'] = 'Estudiante visible / Corrector visible';
$string['studentvisible_markeranonymous'] = 'Estudiante visible / Corrector anónimo';
$string['anonymous'] = 'Corrección anónima';
$string['yespeerisanonymous'] = 'Si (Par es anónimo)';
$string['anonymous_help'] = 'Seleccione para que el proceso de corrección sea anónimo, en cuyo caso los nombres de los estudiantes serán escondidos.';
$string['anonymousstudent'] = 'Estudiante anónimo';
$string['viewpeers'] = 'Estudiantes ven pruebas de otros estudiantes';
$string['viewpeers_help'] = 'Se le permite a los estudiantes revisar pruebas de sus compañeros de manera anónima';

// E-MARKING TYPES
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

$string ['invalidcustommarks'] = 'Marcadores personalizados inválidos, línea(s): ';
$string ['exporttoexcel'] = 'Exportar a Excel';

$string ['comparativereport'] = 'Comparativo';
$string ['comparativereport_help'] = 'Comparativo';
$string ['youmustselectemarking'] = 'Debe seleccionar una actividad eMarking para comparar';
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

$string ['heartbeatenabled'] = 'Habilitar seguimiento a estudiantes';
$string ['heartbeatenabled_help'] = 'Habilita almacenamiento de registros de cuánto tiempo pasa un estudiante frente a la retroalimentación.';

$string ['downloadrubricpdf'] = 'Descarga pdf con rúbrica';
$string ['downloadrubricpdf_help'] = 'Estudiantes pueden descargar su prueba con la rúbrica en la última página';

$string ['linkrubric'] = 'Rúbrica multicolor';
$string ['linkrubric_help'] = 'Una rúbrica multicolor mostrará un color diferente para cada criterio, tanto para las correcciones (cruces o ticks) como para los comentarios.';

$string ['collaborativefeatures'] = 'Colaboración entre correctores';
$string ['collaborativefeatures_help'] = 'Habilita el chat, el muro y el SOS para la colaboración de correctores. El chat permite a correctores comunicarse entre si. El muro permite a supervisores (profesor o administrador) enviar mensajes, los correctores no pueden escribir en el muro. El SOS permite a correctores solicitar ayuda respecto de una prueba que están corrigiendo.';

$string ['experimentalgroups'] = 'Grupos experimentales';
$string ['experimentalgroups_help'] = 'Habilitar corrección separada a través de los grupos del curso';

$string ['emarking:assignmarkers'] = 'Asignar correctores a preguntas';
$string ['emarking:activatedelphiprocess'] = 'Activar delphi';
$string ['emarking:configuredelphiprocess'] = 'Configurar delphi';
$string ['emarking:managedelphiprocess'] = 'Administrat delphi';

$string ['emarking_webexperimental'] = 'eMarking Web experimental';
$string ['emarking_webexperimental_help'] = 'Habilita la interfaz experimental';

$string['enrolments'] = 'Métodos de matriculación';
$string['enrolments_help'] = 'Solo se incluirán los estudiantes matriculados en los métodos de matriculación seleccionados.';
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
$string ['setasabsent'] = 'Ausente';
$string ['setassubmitted'] = 'Marcar como subida';
$string ['markers'] = 'Correctores';
$string ['assignmarkerstocriteria'] = 'Agregar correctores a criterios';

$string ['pctmarked'] = '% corregido';
$string ['saved'] = 'Cambios guardados';
$string ['downloadform'] = 'Descargar formulario de impresión';
$string ['selectprinter'] = 'Escoger impresora';
$string ['enableprinting'] = 'Habilitar impresión desde Moodle';
$string ['enableprinting_help'] = 'Habilita utilizar cups (lp) para imprimir desde el servidor de Moodle a una impresora en red';
$string ['enableprintingrandom'] = 'Permite la impresión al azar';
$string ['enableprintingrandom_help'] = 'permite la impresión al azar, basado en un grupo creado';
$string ['enableprintinglist'] = 'Permite imprimir una lista de estudiantes';
$string ['enableprintinglist_help'] = 'permite la impresión de una lista de estudiantes, esto ayuda a la asistencia en las clases';
$string ['printername'] = 'Nombre de la impresora en red';
$string ['printername_help'] = 'Nombre de la impresora de acuerdo a la configuración de cups';

$string ['minimumdaysbeforeprinting'] = 'Días de anticipación para enviar pruebas';
$string ['minimumdaysbeforeprinting_help'] = 'Los profesores podrán enviar pruebas a impresión al menos este número de días antes, después no se permitirá. Si se configuran en 0 días se deshabilita la verificación.';
$string ['showcoursesfrom'] = 'Mostrar cursos de';
$string ['donotinclude'] = 'No incluir';
$string ['parallelcourses'] = 'Cursos paralelos';
$string ['forcescale17'] = 'Forzar escala de 1 a 7';
$string ['configuration'] = 'Configuración';
$string ['overallfairnessrequired'] = 'El campo es obligatorio';
$string ['expectationrealityrequired'] = 'El campo es obligatorio';
$string ['choose'] = 'Escoger';


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
$string ['ipproblem'] = 'La ip posee caracteres no numericos';
$string ['emptyprinters'] = 'No hay impresoras en el sistema';
$string ['emarking:manageprinters'] = 'Administrar impresoras';
$string ['enablemanageprinters'] = 'Habilitar administración de impresoras';
$string ['viewadminprints'] = '<a href="{$a}">Administrar impresoras</a>';
$string ['viewpermitsprinters'] = '<br/><a href="{$a}">Administrar permisos de impresoras</a>';
$string ['notenablemanageprinters'] = 'No esta habilitada la opción para la administración de impresoras, mas información <a href="{$a}">acá</a>';
$string ['selectusers'] = 'Seleccione usuario(s)';
$string ['selectprinters'] = 'Selecione impresora(s)';
$string ['dontexistrelationship'] = 'La relación usuario-impresora no existe';
$string ['username'] = 'Nombre de usuario';
$string ['doyouwantdeleterelationship'] = '¿Quieres eliminar el permiso?';
$string ['return'] = 'Volver';
$string ['notexistuserorprinter'] = 'No existen usuarios validos o impresoras';
$string ['managepermissions'] = 'Administrar permisos de impresoras';
$string ['emptypermissions'] = 'No existen permisos';
$string ['addpermission'] = 'Agregar permiso';

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

$string ['ranking'] = 'Ranking';

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
$string ['annotatesubmission'] = 'Corregir';
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
$string ['digitizedfile'] = 'Subir respuestas digitalizadas';
$string ['doubleside'] = 'Doble cara';
$string ['doublesidescanning'] = 'Respuestas digitalizadas por ambos lados';
$string ['doublesidescanning_help'] = 'Esta opción se debe seleccionar cuando las respuestas de los estudiantes fueron escaneadas por ambos lados.';
$string ['downloadfeedback'] = 'PDF';
$string ['downloadsuccessfull'] = 'Descarga de prueba exitosa';
$string ['editorder'] = 'Editar orden de impresión';
$string ['email'] = 'Correo';
$string ['emailinstructions'] = 'Ingrese el código de seguridad enviado al correo: {$a->email}';
$string ['messageprovider:notification'] = 'Notificación';
$string ['emarking'] = 'eMarking';
$string ['enablejustice'] = 'Habilitar percepción de justicia';
$string ['enablejustice_help'] = 'Habilita la opción de expresar la percepción de justicia ante una corrección';
$string ['enrolincludes'] = 'Métodos de matriculación por defecto';
$string ['enrolincludes_help'] = 'Los métodos de matriculación que por defecto se seleccionarán al enviar a imprimir una prueba.';
$string ['errors'] = 'Errores';
$string ['errorprocessingcrop'] = 'Error procesando crop de QR';
$string ['errorprocessingextraction'] = 'Error procesando extracción desde ZIP';
$string ['errorsavingpdf'] = 'Error al guardar archivo ZIP';
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
$string ['headerqr'] = 'Encabezado personalizado';
$string ['headerqr_help'] = 'El encabezado personalizado de eMarking permite imprimir la prueba personalizada para cada estudiante. Esto permite luego procesarla automáticamente para su corrección y entrega usando la actividad eMarking.<br/>
		Ejemplo de encabezado:<br/>
		<img width="380" src="' . $CFG->wwwroot . '/mod/emarking/img/preview.jpg">
		<div class="required">Advertencia<ul>
				<li>Para usar el encabezado la prueba debe tener un margen superior de al menos 3cm</li>
		</ul></div>';
$string ['identifieddocuments'] = 'Respuestas subidas';
$string ['idnotfound'] = '{$a->id} identificador no encontrado';
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
$string ['invalidid'] = 'ID inválido';
$string ['invalididnumber'] = 'N&uacute;mero Id inválido';
$string ['invalidimage'] = 'Información inválida desde la imagen';
$string ['invalidemarkingid'] = 'Id de assignment inválido';
$string ['invalidparametersforpage'] = 'Parámetros inválidos para la página';
$string ['invalidpdfnopages'] = 'Archivo PDF inválido, no se reconocen páginas.';
$string ['invalidpdfnumpagesforms'] = 'Archivos de pruebas deben tener el mismo número de páginas.';
$string ['invalidsize'] = 'Tamaño inválido para la imagen';
$string ['invalidstatus'] = 'Estado inválido';
$string ['invalidtoken'] = 'Código de seguridad no válido al intentar descargar prueba.';
$string ['invalidzipnoanonymous'] = 'Archivo ZIP inválido, no contiene versiones anónimas de las respuestas. Es posible que haya sido generado con una versión antigua de la herramienta desktop.';
$string ['justice'] = 'Percepción de Justicia';
$string ['justice.area.under.construction'] = '';
$string ['justice.back'] = 'Volver';
$string ['justice.download'] = 'Ver prueba';
$string ['justice.evaluations.actions'] = 'Acciones';
$string ['justice.evaluations.grade'] = 'Calificación';
$string ['justice.evaluations.marker'] = 'Corrector';
$string ['justice.evaluations.mean'] = 'Promedio del curso';
$string ['justice.evaluations.name'] = 'Evaluación';
$string ['justice.evaluations.status'] = 'Estado';
$string ['justice.exam.not.found'] = 'Examen no encontrado';
$string ['justice.feature.not.available.short'] = 'Funcionalidad no disponible';
$string ['justice.feature.not.available.yet'] = 'Esta funcionalidad no está disponible aún.';
$string ['justice.feedback.already.given'] = 'Aviso! Ya nos has dado tu opinion. Si cambiaste de opinión, puedes volver a llenar el formulario.';
$string ['justice.feedback.welcome'] = 'Use este formulario cuando esté listo para aceptar su calificación';
$string ['justice.form.header'] = 'Mis evaluaciones';
$string ['justice.graph.student.name'] = 'Nombre';
$string ['justice.graph.test.performance'] = 'Rendimiento en la prueba';
$string ['justice.my.evaluations'] = 'Mis evaluaciones';
$string ['justice.peercheck'] = 'Revisar compañeros';
$string ['justice.question.unavailable'] = 'No disponible';
$string ['justice.question.not.answered'] = 'No Entregado';
$string ['justice.question.modify'] = 'Modificar';
$string ['justice.regrade.request'] = 'Recorrección';
$string ['justice.similars.actions'] = 'Acciones';
$string ['justice.similars.grade'] = 'Calificación';
$string ['justice.similars.name'] = 'Nombre';
$string ['justice.statistics'] = 'Estadísticas';
$string ['justice.statistics.locked'] = 'Antes de ver las estadísticas, por favor contesta estas preguntas.';
$string ['justice.status.grading'] = 'En Corrección';
$string ['justice.status.pending'] = 'Por revisar';
$string ['justice.status.regrading'] = 'En Recorrección';
$string ['justice.status.accepted'] = 'Calificación aceptada';
$string ['justice.thank.you.for.your.feedback'] = 'Su opinión ha sido guardada. Gracias por su tiempo.';
$string ['justice.unavailable'] = 'No disponible';
$string ['justice.question.instructions'] = 'Considere una escala de -4 a 4, donde -4 es muy injusto y 4 es muy justo, por favor conteste las siguientes preguntas en relación a la evaluación:';
$string ['justice.question.first'] = 'Como calificaría la justicia del proceso de corrección?';
$string ['justice.question.second'] = 'Como se compara tu calificación a lo que crees que merecías?';
$string ['justice.review'] = 'Revisar';
$string ['justice.yourgrade'] = 'Tu calificación';
$string ['justiceexperiment'] = 'Experimento en percepción de justicia';
$string ['justiceexperiment_help'] = 'Muestra a la mitad de los estudiantes las estadísticas de la evaluación, de manera de tener grupos experimental y control.';
$string ['lastmodification'] = 'Última Modificación';
$string ['logo'] = 'Logo para encabezado';
$string ['logodesc'] = 'Logo para incluir en encabezado de pruebas';
$string ['marking'] = 'Corrección';
$string ['merge'] = 'Reemplazar páginas existentes';
$string ['merge_help'] = 'Las páginas subidas en el archivo reemplazarán a las páginas existentes. Si no marca esta opción las páginas se agregarán al final.';
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
$string ['nopagestoprocess'] = 'Error. El archivo no contiene páginas a procesar, por favor suba las respuestas nuevamente.';
$string ['noprintorders'] = 'No hay órdenes de impresión para este curso';
$string ['nosubmissionsgraded'] = 'No hay pruebas corregidas aún';
$string ['nosubmissionspublished'] = 'No hay calificaciones publicadas aún';
$string ['nosubmissionsselectedforpublishing'] = 'No hay pruebas seleccionadas para publicar sus calificaciones';
$string ['nocomment'] = 'No hay comentario general';
$string ['noexamsforprinting'] = 'No hay pruebas para imprimir';
$string ['notcorrected'] = 'Por corregir';
$string ['page'] = 'Página';
$string ['pages'] = 'páginas';
$string ['assignpagestocriteria'] = 'Agregar páginas a criterios';
$string ['pagedecodingfailed'] = 'QR de página {$a} no pudo ser decodificado';
$string ['pagedecodingsuccess'] = 'QR de página {$a} decodificado exitosamente';
$string ['pagenumber'] = 'Número de página';
$string ['parallelregex'] = 'Regex para cursos paralelos';
$string ['parallelregex_help'] = 'Expresión regular para extraer el código del curso a partir del nombre corto, de manera de identificar cursos paralelos.';
$string ['pathuserpicture'] = 'Directorio de imágenes alternativas de usuarios';
$string ['pathuserpicture_help'] = 'Dirección absoluta del directorio que contiene las imágenes alternativas de los usuarios en formato PNG y cuyo nombre calza con userXXX.png en que XXX es el id de usuario. Si está vacío y se incluirá la imagen de usuarios, se utilizará la que el usuario tiene en su perfil.';
$string ['pdffile'] = 'Archivo(s) PDF de la prueba';
$string ['pdffile_help'] = 'Si incluye más de un archivo PDF, éstos se utilizarán como formas diferentes a asignar para los estudiantes.';
$string ['pdffileupdate'] = 'Reemplazar archivo(s) PDF de la prueba';
$string ['pluginadministration'] = 'Administración de emarking';
$string ['previewheading'] = 'Visualización de decodificación de códigos QR';
$string ['previewtitle'] = 'Visualizar errores de QR';
$string ['printsuccessinstructions'] = 'Instrucciones para orden de impresión exitosa';
$string ['printsuccessinstructionsdesc'] = 'Mensaje personalizado para mostrar a profesores y administrativo una vez que una orden de impresión fue correctamente enviada. Por ejemplo que retiren las pruebas en un centro de copiado o que descarguen la prueba por si mismos.';
$string ['printdoublesided'] = 'Doble cara';
$string ['printdoublesided_help'] = 'Al seleccionar doble cara, e-marking intentará imprimir la prueba por ambos lados del papel. Si CUPS (impresión en línea) no está configurada, se enviarán instrucciones a quien descargue la prueba.';
$string ['printexam'] = 'Imprimir prueba';
$string ['printrandom'] = 'Impresión aleatoria';
$string ['printrandominvalid'] = 'Debe crear un grupo para utilizar esta función';
$string ['printrandom_help'] = 'Impresión aleatoria basada en un grupo creado en un curso especifico';
$string ['printlist'] = 'Lista de estudiantes';
$string ['printlist_help'] = 'Se utiliza para imprimir una lista de los estudiantes del curso';
$string ['printnotification'] = 'Notificación';
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
$string ['settings'] = 'Configuración';
$string ['settingsadvanced'] = 'Configuración avanzada';
$string ['settingsadvanced_help'] = 'Configuración avanzada para eMarking';
$string ['settingsbasic'] = 'Configuración básica';
$string ['settingsbasic_help'] = 'Configuración básica para eMarking';
$string ['settingslogo'] = 'Configuración de encabezado';
$string ['settingslogo_help'] = 'Opciones para incluir logo de la institución o la foto del estudiante';
$string ['settingssecurity'] = 'Configuración de seguridad';
$string ['settingssecurity_help'] = 'Se puede agregar seguridad extra usando el servicio SMS de Twilio.com para validar la descarga de pruebas usando mensajes de texto.';
$string ['smsinstructions'] = 'Ingrese el código de seguridad enviado al teléfono: {$a->phone2}';
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
$string ['statuspublished'] = 'Publicada';
$string ['statussubmitted'] = 'Subida';
$string ['statuserror'] = 'Error';
$string ['submission'] = 'Subida manual de respuestas';
$string ['teachercandownload'] = 'Profesor puede descargar prueba';
$string ['teachercandownload_help'] = 'Mostar el link para descargar sus propios exámenes a profesores. Requiere configurar la capacidad de descargar exámenes para el rol de profesor';
$string ['totalexams'] = 'Exámenes totales';
$string ['totalpages'] = 'Páginas esperadas';
$string ['totalpages_help'] = 'Indica el número total de páginas esperadas por alumno. Esto no limita cuántas páginas pueden subirse, solamente permite asociar páginas a criterios de la rúbrica y advertencias visuales cuando faltan páginas de algún alumno.';
$string ['totalpagesprint'] = 'Páginas totales a imprimir';
$string ['uploadanswers'] = 'Subir respuestas digitalizadas';
$string ['uploaderrorsmanual'] = 'Subir respuestas manualmente';
$string ['uploadexamfile'] = 'Archivo Zip';
$string ['uploadinganswersheets'] = 'Subiendo respuestas de los estudiantes';
$string ['usesms'] = 'Usar Twilio.com para enviar SMS';
$string ['usesms_help'] = 'Usar mensaje SMS en vez de correo electrónico para verificar códigos de seguridad de eMarking';
$string ['viewsubmission'] = 'Ver prueba';
$string ['visualizeandprocess'] = 'Visualizar errores';
$string ['formnewcomment'] = 'Texto del comentario';
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
$string ['details'] = 'Detalles';
$string ['originals'] = 'Originales';
$string ['copies'] = 'Copias';
$string ['teacher'] = 'Profesor';

$string ['gradestats'] = 'Estadistica de notas por curso';
$string ['gradehistogram'] = 'Histograma de notas por curso';
$string ['gradehistogramtotal'] = 'Histograma de notas agregado';
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
$string ['grades'] = 'Calificaciones';

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
$string ['eventunauthorizedccessgranted'] = 'WARNING: Acceso no autorizado a la Interfaz Ajax de eMarking';
$string ['eventmarkersconfigcalled'] = 'Se ingresa al markers config';
$string ['eventmarkersassigned'] = 'Correctores han sido assignado';
$string ['eventemarkingcalled'] = 'Llamada al emarking';
