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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_groupexchange_activity_task
 */

/**
 * Structure step to restore one groupexchange activity
 */
class restore_groupexchange_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        /*$paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('groupexchange', '/activity/groupexchange');
        $paths[] = new restore_path_element('groupexchange_option', '/activity/groupexchange/options/option');
        if ($userinfo) {
            $paths[] = new restore_path_element('groupexchange_answer', '/activity/groupexchange/answers/answer');
			$paths[] = new restore_path_element('groupexchange_assigned', '/activity/groupexchange/assignments/assigned');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);*/
    }

    protected function process_groupexchange($data) {
        global $DB;

        /*$data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // insert the groupexchange record
        $newitemid = $DB->insert_record('groupexchange', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);*/
    }

    protected function process_groupexchange_option($data) {
        global $DB;

       /* $data = (object)$data;
        $oldid = $data->id;

        $data->groupexchangeid = $this->get_new_parentid('groupexchange');
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('groupexchange_options', $data);
        $this->set_mapping('groupexchange_option', $oldid, $newitemid);*/
    }

    protected function process_groupexchange_answer($data) {
        global $DB;

        /*$data = (object)$data;
        $oldid = $data->id;

        $data->groupexchangeid = $this->get_new_parentid('groupexchange');
        $data->optionid = $this->get_mappingid('groupexchange_option', $oldid);
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
		
        $newitemid = $DB->insert_record('groupexchange_answers', $data);*/
        // No need to save this mapping as far as nothing depend on it
        // (child paths, file areas nor links decoder)
    }
	
	protected function process_groupexchange_assigned($data) {
		global $DB;

        /*$data = (object)$data;
        $oldid = $data->id;
		
		$data->groupexchangeid = $this->get_new_parentid('groupexchange');
        $data->optionid = $this->get_mappingid('groupexchange_option', $oldid);
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timeassigned = $this->apply_date_offset($data->timeassigned);

        $newitemid = $DB->insert_record('groupexchange_answers', $data);*/
	}	

    protected function after_execute() {
        // Add groupexchange related files, no need to match by itemname (just internally handled context)
        //$this->add_related_files('mod_groupexchange', 'intro', null);
    }
}
