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
 * @package   groupexchange
 * @copyright 2012 onwards Olexandr Savchuk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
 /**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @param object $addhandler ad
 * @return int
 */
function groupexchange_add_instance($groupexchange) {
    global $DB;

    $groupexchange->timemodified = time();

    if (empty($groupexchange->timerestrict)) {
        $groupexchange->timeopen = 0;
        $groupexchange->timeclose = 0;
    }

    $groupexchange->id = $DB->insert_record("groupexchange", $groupexchange);
    
	foreach ($groupexchange->group as $groupid => $value) {
		if ($value == 1) {
			$option = new stdClass();
			$option->groupexchange = $groupexchange->id;
			$option->groupid = $groupid;
			$DB->insert_record("groupexchange_groups", $option);
		}
	}

    return $groupexchange->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global object
 * @param object $groupexchange
 * @return bool
 */
function groupexchange_update_instance($groupexchange) {
    global $DB;

    $groupexchange->id = $groupexchange->instance;
    $groupexchange->timemodified = time();


    if (empty($groupexchange->timerestrict)) {
        $groupexchange->timeopen = 0;
        $groupexchange->timeclose = 0;
    }

    //update, delete or insert accepted groups
	$DB->delete_records("groupexchange_groups", array('groupexchange' => $groupexchange->id));
    foreach ($groupexchange->group as $groupid => $value) {
		if ($value == 1) {
			$option = new stdClass();
			$option->groupexchange = $groupexchange->id;
			$option->groupid = $groupid;
			$DB->insert_record("groupexchange_groups", $option);
		}
	}

    return $DB->update_record('groupexchange', $groupexchange);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global object
 * @param int $id
 * @return bool
 */
function groupexchange_delete_instance($id) {
    global $DB;

    if (!$groupexchange = $DB->get_record("groupexchange", array("id" => "$id"))) {
        return false;
    }

    $result = true;

    if (!$DB->delete_records("groupexchange_groups", array("groupexchange" => "$groupexchange->id"))) {
        $result = false;
    }
	
	$offers = $DB->get_records('groupexchange_offers', array('groupexchange' => "$groupexchange->id"));
	foreach ($offers as $offer) {
		$DB->delete_records("groupexchange_offers_groups", array("offerid" => "$offer->id"));
	}

    if (!$DB->delete_records("groupexchange_offers", array("groupexchange" => "$groupexchange->id"))) {
        $result = false;
    }

    if (!$DB->delete_records("groupexchange", array("id" => "$groupexchange->id"))) {
        $result = false;
    }

    return $result;
}