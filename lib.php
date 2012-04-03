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
 
require_once($CFG->dirroot . '/group/lib.php');
 
// ------------------------------------------------------------------------------------------
//                                     Standard functions
// ------------------------------------------------------------------------------------------
 
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

// ------------------------------------------------------------------------------------------
//                                     Internal functions
// ------------------------------------------------------------------------------------------

function groupexchange_get_instance($id) {
	global $DB;
	
	$groupexchange = $DB->get_record('groupexchange', array('id' => $id));
	
	// get groups data
	$groupexchange->groups = array();
	$groups = $DB->get_records_sql('select g.* from 
										{groupexchange_groups} gg, 
										{groups} g
									where gg.groupexchange = ?
										and g.id = gg.groupid
									order by g.name asc', array($id));
	foreach($groups as $group) {
		$groupexchange->groups[$group->id] = $group;
	}
	
	// get active offers data
	$groupexchange->offers = array();
	$offers = $DB->get_records_sql('select 
										o.*, 
										u.firstname,
										u.lastname 
									from 
										{groupexchange_offers} o,
										{user} u
									where o.groupexchange = ?
										and o.accepted_by = 0
										and u.id = o.userid
									order by o.time_submitted asc', array($id));
	foreach($offers as $offer) {
		$offer->groups = array();
		$groups = $DB->get_records('groupexchange_offers_groups', array('offerid' => $offer->id));
		foreach($groups as $group)
			$offer->groups[$group->groupid] = $groupexchange->groups[$group->groupid];
		$offer->group = $groupexchange->groups[$offer->group_offered];
		$groupexchange->offers[] = $offer;
	}
	
	// TODO: sort offers. user's offer first, acceptable offers next, all others afterwards
	
	return $groupexchange;
}

function groupexchange_get_offer($offer_id) {
	global $DB;
	
	$offer = $DB->get_record_sql('select 
										o.*, 
										u.firstname,
										u.lastname 
									from 
										{groupexchange_offers} o,
										{user} u
									where o.id = ?
										and u.id = o.userid
									order by o.time_submitted asc', array($offer_id));
	$groups = $DB->get_records_sql('select g.* from 
										{groupexchange_offers_groups} og, 
										{groups} g
									where og.offerid = ?
										and g.id = og.groupid
									order by g.name asc', array($offer_id));
	foreach($groups as $group)
		$offer->groups[$group->id] = $group;
		
	$offer->group = $DB->get_record('groups', array('id' => $offer->group_offered));
		
	return $offer;
}

function groupexchange_get_user_groups($user) {
	global $DB;
	$db_groups = $DB->get_records('groups_members', array('userid' => $user->id));
	$groupmembership = array();
	foreach ($db_groups as $m)
		$groupmembership[] = $m->id;
	return $groupmembership;
}

/**
 * Returns true of the currently logged in user can accept the given exchange offer
 */
function groupexchange_offer_acceptable($offer, $groupmembership = null) {
	global $DB, $USER;
	
	if ($USER->id == $offer->userid)
		return false;

	if ($groupmembership === null) {
		$groupmembership = groupexchange_get_user_groups($USER);
	}
	
	foreach($offer->groups as $groupid => $group) {
		if(in_array($groupid, $groupmembership))
			return true;
	}
	
	return false;
}

/**
 * Given submitted "create offer" form data, check if there is a standing offer satisfying the conditions. Return the found offer object or false
 */
function groupexchange_find_offer() {
	global $DB;
	
	// TODO: implement
	
	return false;
}

/**
 * Creates a new offer from submitted form data
 */
function groupexchange_create_offer($exchange, $offer_group, $request_groups) {
	global $DB, $USER;

	$offer = new stdClass();
	$offer->groupexchange = $exchange->id;
	$offer->userid = $USER->id;
	$offer->time_submitted = time();
	$offer->group_offered = $offer_group;
	
	$offer->id = $DB->insert_record('groupexchange_offers', $offer);
	
	foreach($request_groups as $groupid => $x) {
		$obj = new stdClass();
		$obj->offerid = $offer->id;
		$obj->groupid = $groupid;
		$DB->insert_record('groupexchange_offers_groups', $obj);
	}	
}

function groupexchange_delete_offer($offerid) {
	global $DB, $USER;
	
	if (!$DB->record_exists('groupexchange_offers', array('id' => $offerid, 'userid' => $USER->id)))
		return false;
	
	if (!$DB->delete_records('groupexchange_offers_groups', array('offerid' => $offerid)))
		return false;
	
	return $DB->delete_records('groupexchange_offers', array('id' => $offerid));
}

/**
 * If possible, accepts the given offer (with the logged in user). 
 *
 * Switches logged in user and offer author between groups
 * Deactivates the offer
 * Sends out email confirmations to both users
 */
function groupexchange_accept_offer($offer) {
	
}
