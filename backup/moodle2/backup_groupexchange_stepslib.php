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
 * Define all the backup steps that will be used by the backup_groupexchange_activity_task
 */

/**
 * Define the complete groupexchange structure for backup, with file and id annotations
 */
class backup_groupexchange_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $groupexchange = new backup_nested_element('groupexchange', array('id'), array(
            'name', 'intro', 'introformat', 'publish',
            'display', 'timeopen', 'timeclose', 
			'timemodified', 'anonymous', 'limitexchanges'));

        $groups = new backup_nested_element('groups');

        $group = new backup_nested_element('group', array('id'), array('groupid'));

        $offers = new backup_nested_element('offers');

        $offer = new backup_nested_element('offer', array('id'), array(
            'userid', 'group_offered', 'time_submitted', 'comment', 'accepted_by', 'accepted_groupid'));
			
		$offer_groups = new backup_nested_element('offer_groups');

        $offer_group = new backup_nested_element('offer_group', array('id'), array('groupid'));

        // Build the tree
        $groupexchange->add_child($groups);
        $groups->add_child($group);

        $groupexchange->add_child($offers);
        $offers->add_child($offer);
		
		$offer->add_child($offer_groups);
		$offer_groups->add_child($offer_group);

        // Define sources
        $groupexchange->set_source_table('groupexchange', array('id' => backup::VAR_ACTIVITYID));

        $group->set_source_sql('
            SELECT *
              FROM {groupexchange_groups}
             WHERE groupexchange = ?',
            array(backup::VAR_PARENTID));

        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            $offer->set_source_table('groupexchange_offers', array('groupexchange' => '../../id'));
			$offer_group->set_source_table('groupexchange_offers_groups', array('offerid' => '../../id'));
        }

        // Define id annotations
        $offer->annotate_ids('user', 'userid');
		$offer->annotate_ids('group', 'group_offered');
		$offer_group->annotate_ids('group', 'groupid');
		$group->annotate_ids('group', 'groupid');

        // Define file annotations
        $groupexchange->annotate_files('mod_groupexchange', 'intro', null); // This file area hasn't itemid

        // Return the root element (groupexchange), wrapped into standard activity structure
        return $this->prepare_activity_structure($groupexchange);
    }
}
