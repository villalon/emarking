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
 * This file keeps track of upgrades to the emarking module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do. The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2013 Jorge Villalón
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

/**
 * Execute emarking upgrade from the given old version
 *
 * @param int $oldversion        	
 * @return bool
 */
function xmldb_emarking_upgrade($oldversion) {
	global $DB;

	$dbman = $DB->get_manager (); // loads ddl manager and xmldb classes
	
	if ($oldversion < 2014021901) {
		
		// Define field regraderestrictdates to be added to emarking.
		$table = new xmldb_table ( 'emarking' );
		$field = new xmldb_field ( 'regraderestrictdates', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'anonymous' );
		
		// Conditionally launch add field regraderestrictdates.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		$field = new xmldb_field ( 'regradesopendate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'regraderestrictdates' );
		
		// Conditionally launch add field regradesopendate.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		$field = new xmldb_field ( 'regradesclosedate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'regradesopendate' );
		
		// Conditionally launch add field regradesclosedate.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014021901, 'emarking' );
	}
	
	if ($oldversion < 2014031802) {
		
		// Define table emarking_task to be created.
		$table = new xmldb_table ( 'emarking_task' );
		
		// Adding fields to table emarking_task.
		$table->add_field ( 'id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null );
		$table->add_field ( 'masteractivity', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'markerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'criterion', XMLDB_TYPE_INTEGER, '10', null, null, null, null );
		$table->add_field ( 'stage', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0' );
		
		// Adding keys to table emarking_task.
		$table->add_key ( 'primary', XMLDB_KEY_PRIMARY, array (
				'id' 
		) );
		
		// Conditionally launch create table for emarking_task.
		if (! $dbman->table_exists ( $table )) {
			$dbman->create_table ( $table );
		}
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014031802, 'emarking' );
	}
	
	if ($oldversion < 2014031803) {
		
		// Define table emarking_markers to be created.
		$table = new xmldb_table ( 'emarking_markers' );
		
		// Adding fields to table emarking_markers.
		$table->add_field ( 'id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null );
		$table->add_field ( 'masteractivity', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'markerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'activityid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		
		// Adding keys to table emarking_markers.
		$table->add_key ( 'primary', XMLDB_KEY_PRIMARY, array (
				'id' 
		) );
		
		// Conditionally launch create table for emarking_markers.
		if (! $dbman->table_exists ( $table )) {
			$dbman->create_table ( $table );
		}
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014031803, 'emarking' );
	}
	if ($oldversion < 2014040600) {
		
		// Define table emarking_arguments to be created.
		$table = new xmldb_table ( 'emarking_arguments' );
		
		// Adding fields to table emarking_arguments.
		$table->add_field ( 'id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null );
		$table->add_field ( 'markerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'text', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'levelid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'bonus', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0.00' );
		
		// Adding keys to table emarking_arguments.
		$table->add_key ( 'primary', XMLDB_KEY_PRIMARY, array (
				'id' 
		) );
		
		// Conditionally launch create table for emarking_arguments.
		if (! $dbman->table_exists ( $table )) {
			$dbman->create_table ( $table );
		}
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014040600, 'emarking' );
	}
	
	if ($oldversion < 2014040601) {
		
		// Define table emarking_argument_votes to be created.
		$table = new xmldb_table ( 'emarking_argument_votes' );
		
		// Adding fields to table emarking_argument_votes.
		$table->add_field ( 'id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null );
		$table->add_field ( 'markerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'argumentid', XMLDB_TYPE_INTEGER, '10', null, null, null, null );
		
		// Adding keys to table emarking_argument_votes.
		$table->add_key ( 'primary', XMLDB_KEY_PRIMARY, array (
				'id' 
		) );
		$table->add_key ( 'fk_arguments', XMLDB_KEY_FOREIGN, array (
				'argumentid' 
		), 'emarking_arguments', array (
				'id' 
		) );
		
		// Conditionally launch create table for emarking_argument_votes.
		if (! $dbman->table_exists ( $table )) {
			$dbman->create_table ( $table );
		}
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014040601, 'emarking' );
	}
	if ($oldversion < 2014040602) {
		
		// Define field studentid to be added to emarking_arguments.
		$table = new xmldb_table ( 'emarking_arguments' );
		$field = new xmldb_field ( 'studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'bonus' );
		
		// Conditionally launch add field studentid.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014040602, 'emarking' );
	}
	
	if ($oldversion < 2014041300) {
		
		// Define table emarking_debate_timings to be created.
		$table = new xmldb_table ( 'emarking_debate_timings' );
		
		// Adding fields to table emarking_debate_timings.
		$table->add_field ( 'id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null );
		$table->add_field ( 'parentcm', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'criteriondesc', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'markerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'hasvotes', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0' );
		$table->add_field ( 'lastargumentchange', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0' );
		$table->add_field ( 'lastvote', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0' );
		$table->add_field ( 'timehidden', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0' );
		
		// Adding keys to table emarking_debate_timings.
		$table->add_key ( 'primary', XMLDB_KEY_PRIMARY, array (
				'id' 
		) );
		
		// Conditionally launch create table for emarking_debate_timings.
		if (! $dbman->table_exists ( $table )) {
			$dbman->create_table ( $table );
		}
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014041300, 'emarking' );
	}
	
	if ($oldversion < 2014041301) {
		
		// Changing type of field criteriondesc on table emarking_debate_timings to text.
		$table = new xmldb_table ( 'emarking_debate_timings' );
		$field = new xmldb_field ( 'criteriondesc', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'studentid' );
		
		// Launch change of type for field criteriondesc.
		$dbman->change_field_type ( $table, $field );
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014041301, 'emarking' );
	}
	
	if ($oldversion < 2014041803) {
		
		// Changing type of field criteriondesc on table emarking_debate_timings to text.
		$table = new xmldb_table ( 'emarking_exams' );
		$field = new xmldb_field ( 'courseshortname', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'course' );
		
		// Launch change of type for field criteriondesc.
		// Conditionally launch add field studentid.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		$examstoupdate = $DB->get_records ( 'emarking_exams', array (
				'courseshortname' => null 
		) );
		
		foreach ( $examstoupdate as $exam ) {
			$currentcourse = $DB->get_record ( 'course', array (
					'id' => $exam->course 
			) );
			$exam->courseshortname = $currentcourse->shortname;
			$DB->update_record ( 'emarking_exams', $exam );
		}
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014041803, 'emarking' );
	}
	
	if ($oldversion < 2014042501) {
		
		// Changing type of field criteriondesc on table emarking_debate_timings to text.
		$table = new xmldb_table ( 'emarking' );
		$field = new xmldb_field ( 'peervisibility', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0' );
		
		// Launch change of type for field criteriondesc.
		// Conditionally launch add field studentid.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014042501, 'emarking' );
	}
	if ($oldversion < 2014042702) {
		
		// Define field predefined to be added to emarking_comment.
		$table = new xmldb_table ( 'emarking_comment' );
		$field = new xmldb_field ( 'predefined', XMLDB_TYPE_BINARY, null, null, null, null, null, 'timemodified' );
		
		// Conditionally launch add field predefined.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014042702, 'emarking' );
	}
	
	if ($oldversion < 2014042703) {
		
		// Changing type of field predefined on table emarking_comment to int.
		$table = new xmldb_table ( 'emarking_comment' );
		$field = new xmldb_field ( 'predefined', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timemodified' );
		
		// Launch change of type for field predefined.
		$dbman->change_field_type ( $table, $field );
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014042703, 'emarking' );
	}
	
	if ($oldversion < 2014051002) {
		
		// Define field sort to be added to emarking_submission.
		$table = new xmldb_table ( 'emarking_submission' );
		$field = new xmldb_field ( 'sort', XMLDB_TYPE_INTEGER, '10', null, true, null, 0, 'timemodified' );
		
		// Conditionally launch add field predefined.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		// Updating default sort values for submissions
		$submissions = $DB->get_records_sql ( 'SELECT * FROM {emarking_submission} WHERE sort = 0' );
		foreach ( $submissions as $submission ) {
			$submission->sort = rand ( 1, 9999999 );
			$DB->update_record ( 'emarking_submission', $submission );
		}
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014051002, 'emarking' );
	}
	
	if ($oldversion < 2014051101) {
		
		// Define table emarking_marker_criterion to be created.
		$table = new xmldb_table ( 'emarking_marker_criterion' );
		
		// Adding fields to table emarking_marker_criterion.
		$table->add_field ( 'id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null );
		$table->add_field ( 'emarking', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0' );
		$table->add_field ( 'marker', XMLDB_TYPE_INTEGER, '10', null, null, null, '0' );
		$table->add_field ( 'criterion', XMLDB_TYPE_INTEGER, '10', null, null, null, '0' );
		$table->add_field ( 'timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0' );
		
		// Adding keys to table emarking_marker_criterion.
		$table->add_key ( 'primary', XMLDB_KEY_PRIMARY, array (
				'id' 
		) );
		
		// Conditionally launch create table for emarking_marker_criterion.
		if (! $dbman->table_exists ( $table )) {
			$dbman->create_table ( $table );
		}
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014051101, 'emarking' );
	}
	
	if ($oldversion < 2014051103) {
		
		// Changing type of field comment on table emarking_regrade to text.
		$table = new xmldb_table ( 'emarking_regrade' );
		$field = new xmldb_field ( 'comment', XMLDB_TYPE_TEXT, null, null, null, null, null, 'motive' );
		
		// Launch change of type for field comment.
		$dbman->change_field_type ( $table, $field );
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014051103, 'emarking' );
	}
	
	if ($oldversion < 2014051104) {
		
		// Changing type of field comment on table emarking_regrade to text.
		$table = new xmldb_table ( 'emarking_regrade' );
		$field = new xmldb_field ( 'markercomment', XMLDB_TYPE_TEXT, null, null, null, null, null, 'motive' );
		
		// Launch change of type for field comment.
		$dbman->change_field_type ( $table, $field );
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014051104, 'emarking' );
	}
	
	if ($oldversion < 2014051502) {
		
		// Define field sort to be added to emarking_submission.
		$table = new xmldb_table ( 'emarking' );
		$field = new xmldb_field ( 'totalpages', XMLDB_TYPE_INTEGER, '10', null, true, null, 0, 'timemodified' );
		
		// Conditionally launch add field predefined.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014051502, 'emarking' );
	}
	
	if ($oldversion < 2014051503) {
		
		// Define table emarking_marker_criterion to be created.
		$table = new xmldb_table ( 'emarking_page_criterion' );
		
		// Adding fields to table emarking_marker_criterion.
		$table->add_field ( 'id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null );
		$table->add_field ( 'emarking', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0' );
		$table->add_field ( 'page', XMLDB_TYPE_INTEGER, '10', null, null, null, '0' );
		$table->add_field ( 'criterion', XMLDB_TYPE_INTEGER, '10', null, null, null, '0' );
		$table->add_field ( 'timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null );
		$table->add_field ( 'timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0' );
		
		// Adding keys to table emarking_marker_criterion.
		$table->add_key ( 'primary', XMLDB_KEY_PRIMARY, array (
				'id' 
		) );
		
		// Conditionally launch create table for emarking_marker_criterion.
		if (! $dbman->table_exists ( $table )) {
			$dbman->create_table ( $table );
		}
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014051503, 'emarking' );
	}
	
	if ($oldversion < 2014051703) {
		
		// Changing type of field comment on table emarking_regrade to text.
		$table = new xmldb_table ( 'emarking_submission' );
		$field = new xmldb_field ( 'generalfeedback', XMLDB_TYPE_TEXT, null, null, null, null, null, 'teacher' );
		
		// Conditionally launch add field predefined.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
		
		$grades = $DB->get_records_sql ( "
		SELECT gg.id, gg.finalgrade, gg.feedback, gi.iteminstance, gg.userid
		FROM {grade_items} AS gi
		INNER JOIN {grade_grades} AS gg ON (gi.itemtype = 'mod' and gi.itemmodule = 'emarking' and gi.id = gg.itemid)
		WHERE gg.finalgrade IS NOT NULL" );
		
		foreach ( $grades as $grade ) {
			if ($submission = $DB->get_record ( 'emarking_submission', array (
					'emarking' => $grade->iteminstance,
					'student' => $grade->userid 
			) )) {
				$submission->grade = $grade->finalgrade;
				$submission->generalfeedback = $grade->feedback;
				$DB->update_record ( 'emarking_submission', $submission );
			}
		}
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014051703, 'emarking' );
	}
	
	if ($oldversion < 2014052301) {
		
		// Define index idx_id_emarking (not unique) to be added to emarking_submission.
		$table = new xmldb_table ( 'emarking_submission' );
		$index = new xmldb_index ( 'idx_id_emarking', XMLDB_INDEX_NOTUNIQUE, array (
				'emarking' 
		) );
		
		// Conditionally launch add index idx_id_emarking.
		if (! $dbman->index_exists ( $table, $index )) {
			$dbman->add_index ( $table, $index );
		}
		
		// Define index idx_id_submission (not unique) to be added to emarking_page.
		$table = new xmldb_table ( 'emarking_page' );
		$index = new xmldb_index ( 'idx_id_submission', XMLDB_INDEX_NOTUNIQUE, array (
				'submission' 
		) );
		
		// Conditionally launch add index idx_id_submission.
		if (! $dbman->index_exists ( $table, $index )) {
			$dbman->add_index ( $table, $index );
		}
		
		// Define index idx_id_page (not unique) to be added to emarking_comment.
		$table = new xmldb_table ( 'emarking_comment' );
		$index = new xmldb_index ( 'idx_id_page', XMLDB_INDEX_NOTUNIQUE, array (
				'page' 
		) );
		
		// Conditionally launch add index idx_id_page.
		if (! $dbman->index_exists ( $table, $index )) {
			$dbman->add_index ( $table, $index );
		}
		
		// Define index idx_id_emarking (not unique) to be added to emarking_marker_criterion.
		$table = new xmldb_table ( 'emarking_marker_criterion' );
		$index = new xmldb_index ( 'idx_id_emarking', XMLDB_INDEX_NOTUNIQUE, array (
				'emarking' 
		) );
		
		// Conditionally launch add index idx_id_emarking.
		if (! $dbman->index_exists ( $table, $index )) {
			$dbman->add_index ( $table, $index );
		}
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014052301, 'emarking' );
	}
	
	if ($oldversion < 2014052302) {
		
		// Define field enrolments to be added to emarking_exams.
		$table = new xmldb_table ( 'emarking_exams' );
		$field = new xmldb_field ( 'enrolments', XMLDB_TYPE_CHAR, '250', null, null, null, null, 'notified' );
		
		// Conditionally launch add field enrolments.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}

		// Update all previous registers
		$DB->set_field('emarking_exams', 'enrolments', 'database,manual');
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014052302, 'emarking' );
	}
	
	
	
	if ($oldversion < 2014061501) {
	
		// Define field sort to be added to emarking_submission.
		$table = new xmldb_table ( 'emarking' );
		$field = new xmldb_field ( 'heartbeatenabled', XMLDB_TYPE_INTEGER, '10', null, true, null, 0, 'timemodified' );
	
		// Conditionally launch add field predefined.
		if (! $dbman->field_exists ( $table, $field )) {
			$dbman->add_field ( $table, $field );
		}
	
		// Emarking savepoint reached.
		upgrade_mod_savepoint ( true, 2014061501, 'emarking' );
	}
	
	if ($oldversion < 2014061901) {
	
		// Define field adjustslope to be added to emarking.
		$table = new xmldb_table('emarking');
		$field = new xmldb_field('adjustslope', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'totalpages');
	
		// Conditionally launch add field adjustslope.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		
		$field = new xmldb_field('adjustslopegrade', XMLDB_TYPE_NUMBER, '5, 2', null, XMLDB_NOTNULL, null, '0.00', 'adjustslope');
		
		// Conditionally launch add field adjustslopegrade.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		
		$field = new xmldb_field('adjustslopescore', XMLDB_TYPE_NUMBER, '5, 2', null, XMLDB_NOTNULL, null, '0.00', 'adjustslopegrade');
		
		// Conditionally launch add field adjustslopescore.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		
		// Emarking savepoint reached.
		upgrade_mod_savepoint(true, 2014061901, 'emarking');
	}
	
	if ($oldversion < 2014061902) {
	
		// Changing precision of field adjustslopescore on table emarking to (10, 5).
		$table = new xmldb_table('emarking');
		$field = new xmldb_field('adjustslopescore', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0.00', 'adjustslopegrade');
	
		// Launch change of precision for field adjustslopescore.
		$dbman->change_field_precision($table, $field);
	
		// Emarking savepoint reached.
		upgrade_mod_savepoint(true, 2014061902, 'emarking');
	}
	
	if ($oldversion < 2014062902) {
	
		// Define field markingduedate to be added to emarking.
		$table = new xmldb_table('emarking');
		$field = new xmldb_field('markingduedate', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'adjustslopescore');
	
		// Conditionally launch add field markingduedate.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	
		// Emarking savepoint reached.
		upgrade_mod_savepoint(true, 2014062902, 'emarking');
	}
	if ($oldversion < 2014062903) {
	
		// Define field downloadrubricpdf to be added to emarking.
		$table = new xmldb_table('emarking');
		$field = new xmldb_field('downloadrubricpdf', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'heartbeatenabled');
	
		// Conditionally launch add field downloadrubricpdf.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	
		// Emarking savepoint reached.
		upgrade_mod_savepoint(true, 2014062903, 'emarking');
	}
	
	    if ($oldversion < 2014063001) {

        // Define field printrandom to be added to emarking_exams.
        $table = new xmldb_table('emarking_exams');
        $field = new xmldb_field('printrandom', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'headerqr');

        // Conditionally launch add field printrandom.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Emarking savepoint reached.
        upgrade_mod_savepoint(true, 2014063001, 'emarking');
    }

    if ($oldversion < 2014071301) {

        // Define table emarking_crowd_actions to be created.
        $table = new xmldb_table('emarking_crowd_actions');

        // Adding fields to table emarking_crowd_actions.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('markerid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('time', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('parentcmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('studentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('action', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('rawparams', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('criteriondesc', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('basescore', XMLDB_TYPE_NUMBER, '10, 2', null, XMLDB_NOTNULL, null, null);
        $table->add_field('bonusscore', XMLDB_TYPE_NUMBER, '10, 2', null, XMLDB_NOTNULL, null, null);
        $table->add_field('text', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);

        // Adding keys to table emarking_crowd_actions.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for emarking_crowd_actions.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Emarking savepoint reached.
        upgrade_mod_savepoint(true, 2014071301, 'emarking');
    }

    if ($oldversion < 2014072001) {
    
    	// Define field levelid to be added to emarking_regrade.
    	$table = new xmldb_table('emarking_regrade');
    	$field = new xmldb_field('levelid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'markercomment');
    
    	// Conditionally launch add field levelid.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    	 
    	// Define field markerid to be added to emarking_regrade.
    	$field = new xmldb_field('markerid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'levelid');
    
    	// Conditionally launch add field markerid.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    
    	// Define field bonus to be added to emarking_regrade.
    	$field = new xmldb_field('bonus', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0.00', 'markerid');
    	 
    	// Conditionally launch add field bonus.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    	 
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2014072001, 'emarking');
    }
    
    if ($oldversion < 2014081201) {
    
    	// Define field printlist to be added to emarking_exams.
    	$table = new xmldb_table('emarking_exams');
    	$field = new xmldb_field('printlist', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'printrandom');
    
    	// Conditionally launch add field printlist.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2014081201, 'emarking');
    }
    //comento todo lo agregado por el napu, tiene el siguiente error
    //Table "emarking_predefined_comment" does not exist
    if ($oldversion < 2014081304) {
    
    	// Define table emarking_predefined_comment to be created.
    	$table = new xmldb_table('emarking_predefined_comment');
    
    	// Adding fields to table emarking_predefined_comment.
    	$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    	$table->add_field('emarkingid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    	$table->add_field('text', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
    
    	// Adding keys to table emarking_predefined_comment.
    	$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    
    	// Conditionally launch create table for emarking_predefined_comment.
    	if (!$dbman->table_exists($table)) {
    		$dbman->create_table($table);
    	}
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true,2014081304 , 'emarking');
    }
    if ($oldversion < 2014081601) {
    
    	// Define field markerid to be added to predefined_comment.
    	$table = new xmldb_table('emarking_predefined_comment');
    	$field = new xmldb_field('markerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'text');
    
    	// Conditionally launch add field markerid.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2014081601, 'emarking');
    }
    
    if ($oldversion < 2014082100) {
    	global $DB;
    	// Define field criterionid to be added to emarking_comment.
    	$table = new xmldb_table('emarking_comment');
    	$field = new xmldb_field('criterionid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'levelid');
    
    	// Conditionally launch add field criterionid.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    	
    	//add criterionid to the row that have levelid defined
    	$comments = $DB->get_records("emarking_comment");
    	foreach ($comments as $comment){
    		if($level = $DB->get_record("gradingform_rubric_levels", array("id"=>$comment->levelid))){
    			$comment->criterionid = $level->criterionid;
    			$DB->update_record("emarking_comment", $comment);
    		}
    	}
    	
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2014082100, 'emarking');
    }
    if ($oldversion < 2014090300) {
    
    	// Define field linkrubric to be added to emarking.
    	$table = new xmldb_table('emarking');
    	$field = new xmldb_field('linkrubric', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'adjustslopescore');
    
    	// Conditionally launch add field linkrubric.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2014090300, 'emarking');
    }
    if ($oldversion < 2014091401) {
    	upgrade_mod_savepoint(true, 2014091401, 'emarking');
    }
    
    if ($oldversion < 2014101301) {
    
    	// Define field collaborativefeatures to be added to emarking.
    	$table = new xmldb_table('emarking');
    	$field = new xmldb_field('collaborativefeatures', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'timemodified');
    
    	// Conditionally launch add field collaborativefeatures.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2014101301, 'emarking');
    }
    if ($oldversion < 2014102500) {
    
    	// Define field experimentalgroups to be added to emarking.
    	$table = new xmldb_table('emarking');
    	$field = new xmldb_field('experimentalgroups', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'collaborativefeatures');
    
    	// Conditionally launch add field experimentalgroups.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2014102500, 'emarking');
    }
    
    if ($oldversion < 2014102600) {
    
    	// Define table emarking_experimenal_groups to be created.
    	$table = new xmldb_table('emarking_experimental_groups');
    
    	// Adding fields to table emarking_experimenal_groups.
    	$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    	$table->add_field('emarkingid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    	$table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    	$table->add_field('datestart', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    	$table->add_field('dateend', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    	$table->add_field('linkrubric', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
    
    	// Adding keys to table emarking_experimenal_groups.
    	$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    
    	// Conditionally launch create table for emarking_experimenal_groups.
    	if (!$dbman->table_exists($table)) {
    		$dbman->create_table($table);
    	}
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2014102600, 'emarking');
    }    
    
    if ($oldversion < 2014110100) {
    
    	// Define table emarking_draft to be created.
    	$table = new xmldb_table('emarking_draft');
    
    	// Adding fields to table emarking_draft.
    	$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    	$table->add_field('submissionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    	$table->add_field('emarkingid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    	$table->add_field('student', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
    	$table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
    	$table->add_field('status', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
    	$table->add_field('grade', XMLDB_TYPE_NUMBER, '5, 2', null, XMLDB_NOTNULL, null, '0');
    	$table->add_field('generalfeedback', XMLDB_TYPE_TEXT, null, null, null, null, null);
    	$table->add_field('teacher', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
    	$table->add_field('sort', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    	$table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    	$table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
    
    	// Adding keys to table emarking_draft.
    	$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    
    	// Conditionally launch create table for emarking_draft.
    	if (!$dbman->table_exists($table)) {
    		$dbman->create_table($table);
    	}
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2014110100, 'emarking');
    }
    if ($oldversion < 2014110101) {
    
    	// Define field draft to be added to emarking_page.
    	$table = new xmldb_table('emarking_page');
    	$field = new xmldb_field('draft', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'mailed');
    
    	// Conditionally launch add field draft.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2014110101, 'emarking');
    }
    /**
     * Cambio de todas submission a draft
     */
    if ($oldversion < 2014110700) {
    
    	$DB->delete_records("emarking_draft");
    	
    	if($submissions = $DB->get_records("emarking_submission")){
	    	foreach ($submissions as $submission ){
	    		$draft = new stdClass();
	    		$draft->submissionid = $submission->id;
	    		$draft->emarkingid = $submission->emarking;
	    		$draft->student = $submission->student;
	    		$draft->groupid = 0;
	    		$draft->status = $submission->status;
	    		$draft->grade = $submission->grade;
	    		$draft->generalfeedback = $submission->generalfeedback;
	    		$draft->teacher = $submission->teacher;
	    		$draft->sort = $submission->sort;
	    		$draft->timecreated = $submission->timecreated;
	    		$draft->timemodified = $submission->timemodified;
	    		
	    		$DB->insert_record("emarking_draft", $draft);
	    	}
    	}
    	
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2014110700, 'emarking');
    	
    }
    /**
     * Todas las pages apuntan a los draft correspondientes
     */
    if ($oldversion < 2014110800) {
    
    	if($pages = $DB->get_records("emarking_page")){
	    	foreach($pages as $page){
	    		$draft = $DB->get_record("emarking_draft", array("submissionid"=>$page->submission));
	    		$page->submission = $draft->id;
	    		$DB->update_record("emarking_page", $page);	
	    	}
    	}
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2014110800, 'emarking');
    }
    
    /**
     * Todas intancias de corrección apuntan a los draft correspondientes
     */
    if ($oldversion < 2014111101) {
    
    	if($instances = $DB->get_records_sql("SELECT gi.* FROM {grading_instances} gi
										INNER JOIN {grading_definitions} gd ON(gd.id = gi.definitionid)
										INNER JOIN {grading_areas} ga ON(ga.id = gd.areaid AND ga.component = 'mod_emarking')
    									 ")){
    		foreach($instances as $instance){
    			if($draft = $DB->get_record("emarking_draft", array("submissionid"=>$instance->itemid, 'groupid'=>0))){
    				$instance->itemid = $draft->id;
    				$DB->update_record("grading_instances", $instance);
    			}
    		}
    	}
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2014111101, 'emarking');
    }
    if ($oldversion < 2015012308) {
    
    	// Changing type of field posx on table emarking_comment to number.
    	$table = new xmldb_table('emarking_comment');
    	$field = new xmldb_field('posx', XMLDB_TYPE_NUMBER, '10', null, null, null, '0', 'page');
    	
    	// Launch change of type for field posx.
    	$dbman->change_field_type($table, $field);
    	
    	$table = new xmldb_table('emarking_comment');
    	$field = new xmldb_field('posy', XMLDB_TYPE_NUMBER, '10', null, XMLDB_NOTNULL, null, '0', 'posx');
    	 
    	// Launch change of type for field posy.
    	$dbman->change_field_type($table, $field);
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2015012308, 'emarking');
    }
    
    if ($oldversion < 2015012309) {
    
    	// Changing type of field posx on table emarking_comment to number.
    	$table = new xmldb_table('emarking_comment');
    	$field = new xmldb_field('posx', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, '0', 'page');
    
    	// Launch change of type for field posx.
    	$dbman->change_field_type($table, $field);
    	
    	$table = new xmldb_table('emarking_comment');
    	$field = new xmldb_field('posy', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0', 'posx');
    	
    	// Launch change of type for field posy.
    	$dbman->change_field_type($table, $field);
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2015012309, 'emarking');
    }

    if ($oldversion < 2015012501) {
    
    	// Changing type of field posx on table emarking_comment to int.
    	$table = new xmldb_table('emarking_comment');
    	$field = new xmldb_field('posx', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'page');
    
    	// Launch change of type for field posx.
    	$dbman->change_field_type($table, $field);
    	
    	$table = new xmldb_table('emarking_comment');
    	$field = new xmldb_field('posy', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'posx');
    	
    	// Launch change of type for field posy.
    	$dbman->change_field_type($table, $field);
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2015012501, 'emarking');
    }
    
    if ($oldversion < 2015012502) {
    
    	// Changing type of field posx on table emarking_comment to number.
    	$table = new xmldb_table('emarking_comment');
    	$field = new xmldb_field('posx', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0', 'page');
    
    	// Launch change of type for field posx.
    	$dbman->change_field_type($table, $field);
    	
    	$table = new xmldb_table('emarking_comment');
    	$field = new xmldb_field('posy', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0', 'posx');
    	
    	// Launch change of type for field posy.
    	$dbman->change_field_type($table, $field);
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2015012502, 'emarking');
    }
    
    if ($oldversion < 2015021300) {
    
    	// Define field draft to be added to emarking_page.
    	$table = new xmldb_table('emarking');
    	$field = new xmldb_field('type', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'markingduedate');
    
    	// Conditionally launch add field draft.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    
    	// Update all emarking objects for type 1
    	if($instances = $DB->get_records('emarking')) {
    		foreach($instances as $instance) {
    			if($emarking = $DB->get_record('emarking', array('id'=>$instance->id))){
    				$instance->type = 1;
    				$DB->update_record('emarking', $instance);
    			}
    	    }
    	}
    	 
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2015021300, 'emarking');

    }
    
    if ($oldversion < 2015021900) {
    
    	// Rename field emarking on table emarking_markers to NEWNAMEGOESHERE.
    	$table = new xmldb_table('emarking_markers');
    	$field = new xmldb_field('masteractivity', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');
    
    	// Launch rename field emarking.
    	$dbman->rename_field($table, $field, 'emarking');
    
    	$field = new xmldb_field('markerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'emarking');
    
    	// Launch rename field emarking.
    	$dbman->rename_field($table, $field, 'marker');
    
    	$field = new xmldb_field('activityid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'marker');
    
    	// Launch rename field emarking.
    	$dbman->rename_field($table, $field, 'qualitycontrol');
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2015021900, 'emarking');
    }
    
    if ($oldversion < 2015021901) {
    
    	// Rename field submission on table emarking_regrade to NEWNAMEGOESHERE.
    	$table = new xmldb_table('emarking_regrade');
    	$field = new xmldb_field('submission', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'id');
    
    	// Launch rename field submission.
    	$dbman->rename_field($table, $field, 'draft');
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2015021901, 'emarking');
    }
    if ($oldversion < 2015021902) {
    
    	// Define field id to be added to emarking_comment.
    	$table = new xmldb_table('emarking_comment');
    	$field = new xmldb_field('draft', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'page');
    
    	// Conditionally launch add field id.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2015021902, 'emarking');
    }        
    
    if ($oldversion < 2015022300) {
    
    	// Define field id to be added to emarking_comment.
    	$table = new xmldb_table('emarking_draft');
    	$field = new xmldb_field('qualitycontrol', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'sort');
    
    	// Conditionally launch add field id.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2015022300, 'emarking');
    }
        
    if ($oldversion < 2015022301) {
    
    	// Define field id to be added to emarking_comment.
    	$table = new xmldb_table('emarking');
    	$field = new xmldb_field('qualitycontrol', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'type');
    
    	// Conditionally launch add field id.
    	if (!$dbman->field_exists($table, $field)) {
    		$dbman->add_field($table, $field);
    	}
    
    	// Emarking savepoint reached.
    	upgrade_mod_savepoint(true, 2015022301, 'emarking');
    }
    
    if ($oldversion < 2015050101) {
        // Define index idx_id_emarking (not unique) to be added to emarking_submission.
        $table = new xmldb_table ( 'emarking_comment' );
        $index = new xmldb_index ( 'idx_id_draft', XMLDB_INDEX_NOTUNIQUE, array (
            'draft'
        ) );
    
        // Conditionally launch add index idx_id_emarking.
        if (! $dbman->index_exists ( $table, $index )) {
            $dbman->add_index ( $table, $index );
        }
    
        // Emarking savepoint reached.
        upgrade_mod_savepoint ( true, 2015050101, 'emarking' );
    }
    return true;
}