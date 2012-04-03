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
 * Moodle renderer used to display special elements of the groupexchange module
 *
 * @package   groupexchange
 * @copyright 2012 onwards Olexandr Savchuk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

class mod_groupexchange_renderer extends plugin_renderer_base {

	public function show_styles() {
		return "<style type='text/css'>table.gex td {font-weight: normal; vertical-align: top} p.error {font-weight: bold; color: darkred}</style>";
	}

	public function show_offers($cm, $exchange) {
		global $OUTPUT, $USER;
		
		// prefetch logged in user group memberships
		$groupmemberships = groupexchange_get_user_groups($USER);
		
		$html = html_writer::tag('h2', get_string('standing_offers', 'groupexchange'));

		if (sizeof($exchange->offers) == 0) {
					
			$html .= html_writer::tag('i', get_string('no_offers', 'groupexchange'));
		
		} else {
		
			$html .= html_writer::start_tag('table', array('class' => 'gex'));
			
			// table header
			$html .= html_writer::start_tag('tr');
			if (!$exchange->anonymous)
				$html .= html_writer::tag('th', get_string('author_name', 'groupexchange'));
			$html .= html_writer::tag('th', get_string('groups_accepted', 'groupexchange'));
			$html .= html_writer::tag('th', get_string('group_offer', 'groupexchange'));
			$html .= html_writer::tag('th', get_string('action', 'groupexchange'));
			$html .= html_writer::end_tag('tr');
			
			// render offers as table rows
			foreach ($exchange->offers as $offer) {
				$html .= html_writer::start_tag('tr');
				
				if (!$exchange->anonymous) {
					$url = new moodle_url('/user/view.php', array('id'=>$offer->userid, 'course'=>$exchange->course));
					$link_html = html_writer::link($url, $offer->firstname.' '.$offer->lastname);
					$html .= html_writer::tag('td', $link_html);
				}
				
				$groups_html = array();
				foreach($offer->groups as $group) {
					if(in_array($group->id, $groupmemberships))
						$groups_html[] = '<b>'.$group->name.'</b>';
					else
						$groups_html[] = $group->name;
				}
				$html .= html_writer::tag('td', implode('<br>', $groups_html));

				$html .= html_writer::tag('td', '<b>'.$offer->group->name.'</b>');
				
				$action = '<i>'.get_string('not_acceptable', 'groupexchange').'</i>';
				if (groupexchange_offer_acceptable($offer, $groupmemberships)) {
					$action = html_writer::start_tag('form', array('action' => 'view.php', 'method' => 'post',
						'onsubmit' => 'javascript: return confirm("'.get_string('confirm_accept', 'groupexchange').'");'));
					$action .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $cm->id));
					$action .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'accept'));
					$action .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'offer', 'value' => $offer->id));
					$action .= html_writer::empty_tag('input', array('type' => 'submit', 'class' => 'pos', 'value' => get_string('accept', 'groupexchange')));
					$action .= html_writer::end_tag('form');
				}
				if ($offer->userid == $USER->id) {
					$action = html_writer::start_tag('form', array('action' => 'view.php', 'method' => 'post',
						'onsubmit' => 'javascript: return confirm("'.get_string('confirm_delete', 'groupexchange').'");'));
					$action .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $cm->id));
					$action .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'delete'));
					$action .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'offer', 'value' => $offer->id));
					$action .= html_writer::empty_tag('input', array('type' => 'submit', 'class' => 'neg', 'value' => get_string('cancel', 'groupexchange')));
					$action .= html_writer::end_tag('form');
				}
				
				$html .= html_writer::tag('td', $action);
				
				$html .= html_writer::end_tag('tr');
			}
			
			$html .= html_writer::end_tag('table');
		}
		
		return $html;
	}
	
	public function show_offer_form($cm, $exchange, $error = array()) {
	
		global $OUTPUT, $USER;
	
		$html = html_writer::tag('h2', get_string('post_offer', 'groupexchange'));
		
		// prefetch logged in user group memberships
		$groupmemberships = groupexchange_get_user_groups($USER);
		
		$can_offer = false;
		foreach($groupmemberships as $groupid) {
			if (isset($exchange->groups[$groupid]))
				$can_offer = true;
		}
		if (!$can_offer) {
			$html .= html_writer::tag('i', get_string('cannot_offer', 'groupexchange'));
			return $html;		
		}
		
		// fetch submitted data (if any)
		$offer_group = optional_param('offer_group', 0, PARAM_INT);
		$request_group = optional_param('request_group', array(), PARAM_RAW);
				
		$html .= html_writer::start_tag('form', array('action' => 'view.php', 'method' => 'post'));
		
		$html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $cm->id));
		$html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'offer'));
		
		$html .= html_writer::start_tag('table', array('class' => 'gex'));
		
		// offer group for exchange
		$html .= html_writer::start_tag('tr');
		$html .= html_writer::tag('td', get_string('offer_group', 'groupexchange'));
		$html .= html_writer::start_tag('td');
		if (isset($error['offer_group'])) {
			$html .= '<p class="error">'.$error['offer_group'].'</p>';
		}
		$groups_html = array();
		foreach($groupmemberships as $groupid) {
			if (!isset($exchange->groups[$groupid]))
				continue;
			if ($offer_group == 0)
				$offer_group = $groupid;
			$attributes = array('type' => 'radio', 'name' => 'offer_group', 'value' => $groupid, 'id' => 'offer_group_'.$groupid);
			if ($offer_group == $groupid)
				$attributes['checked'] = 'checked';
			$ghtml = html_writer::empty_tag('input', $attributes);
			$ghtml .= html_writer::tag('label', $exchange->groups[$groupid]->name, array('for' => 'offer_group_'.$groupid));
			$groups_html[] = $ghtml;
		}
		$html .= implode('<br>', $groups_html);
		$html .= html_writer::end_tag('td');
		$html .= html_writer::end_tag('tr');
		
		// request memberships in other groups
		$html .= html_writer::start_tag('tr');
		$html .= html_writer::tag('td', get_string('request_group', 'groupexchange'));
		$html .= html_writer::start_tag('td');
		if (isset($error['request_group'])) {
			$html .= '<p class="error">'.$error['request_group'].'</p>';
		}
		$groups_html = array();
		foreach($exchange->groups as $groupid => $group) {
			if (in_array($groupid, $groupmemberships))
				continue; // don't show groups user is a member of
			$attributes = array('type' => 'checkbox', 'name' => 'request_group['.$groupid.']', 'value' => '1', 'id' => 'request_group_'.$groupid);
			if (isset($request_group[$groupid]) && $request_group[$groupid] == 1)
				$attributes['checked'] = 'checked';
			$ghtml = html_writer::empty_tag('input', $attributes);
			$ghtml .= html_writer::tag('label', $group->name, array('for' => 'request_group_'.$groupid));
			$groups_html[] = $ghtml;
		}
		$html .= implode('<br>', $groups_html);
		$html .= html_writer::end_tag('td');
		$html .= html_writer::end_tag('tr');
		
		$html .= html_writer::start_tag('tr');
		$html .= html_writer::empty_tag('td');
		$html .= html_writer::start_tag('td');
		$html .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('submit_offer', 'groupexchange')));
		$html .= html_writer::end_tag('td');
		$html .= html_writer::end_tag('tr');
		
		$html .= html_writer::end_tag('table');
		
		$html .= html_writer::end_tag('form');
		
		
		
		return $html;
	
	}

}

