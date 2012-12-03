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
 * Strings for component 'groupreg', language 'de'
 *
 * @package   groupreg
 * @copyright 2011 onwards Olexandr Savchuk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
$string['modulename'] = 'Group exchange';
$string['pluginname'] = 'Group exchange';
$string['modulename_help'] = 'The group exchange module allows teachers to set up group exchange activities in courses, letting students exchange their group memberships.';
$string['modulenameplural'] = 'Group exchanges';
$string['pluginadministration'] = 'Group exchange settings';

$string['messageprovider:offer_accepted'] = 'Notification of accepted group exchange offers';
$string['email_subject'] = '{$a->course}: {$a->exchange}: group exchange offer accepted';
$string['email_body'] = 
'Your group exchange offer in \'{$a->exchange}\' (course: \'{$a->course}\') was accepted.

You were switched from group \'{$a->groupfrom}\' to \'{$a->groupto}\'.';

$string['groupexchangename'] = 'Group exchange name';
$string['choose_groups_help'] = 'Check the groups you want to enable for this group exchange. Students belonging to one of those groups will be able to participate in the exchange, offer their group membership and request a membership in one of the other groups checked here.<br>Unchecking groups will not remove standing offers for them.';
$string['choose_groups'] = 'Choose groups available for exchange';
$string['setting_anonymous'] = 'Anonymous users';
$string['setting_anonymous_help'] = 'If set, no user names will be shown next to the standing offers; students won\'t know, who they exchange group memberships with, only which group they exchanged into.';
$string['setting_limitexchanges'] = 'Limit exchanges per user';
$string['setting_limitexchanges_help'] = 'Set a limit to how often a student can exchange his group membership in this activity. Default is no limit.';
$string['unlimited'] = 'No limit';
$string['timerestrict'] = 'Restrict the exchange to this time interval';
$string['expired'] = 'This activity expired on {$a} and is no longer available.';
$string['notopenyet'] = 'This activity will not be open until {$a}.';
$string['groupexchangeopen'] = 'Open';
$string['groupexchangeclose'] = 'Until';
$string['checkallornone'] = 'Check all/none';
$string['standing_offers'] = 'Standing group exchange offers';
$string['author_name'] = 'Offered by:';
$string['group_offer'] = 'Offering group:';
$string['groups_accepted'] = 'Accepting groups:';
$string['action'] = 'Action:';
$string['your_offer'] = 'This is your offer.';
$string['not_acceptable'] = 'You cannot accept this offer.';
$string['accept'] = 'Accept offer';
$string['cancel'] = 'Remove offer';
$string['no_offers'] = 'There are no standing offers at the moment.';
$string['cannot_offer'] = 'You must be a member in one of the exchangeable groups.';
$string['post_offer'] = 'Post a new group exchange offer';
$string['offer_group'] = 'Offer group for exchange:';
$string['request_group'] = 'Request group(s) for exchange:';
$string['submit_offer'] = 'Submit group exchange offer';
$string['offer_created'] = 'Group exchange offer successfully created.';
$string['error_request_group_not_enough'] = 'You must choose at least one group you want to accept in exchange.';
$string['error_request_group_bad'] = 'Bad group data submitted.';
$string['error_offer_group'] = 'Bad offered group data submitted: must be one admissible group ID';
$string['error_double_offer'] = 'You already have an active offer offering this group. The offer could not be created.';
$string['error_delete_offer'] = 'Offer could not be deleted.';
$string['offer_deleted'] = 'Your offer was removed.';
$string['confirm_delete'] = 'Are you sure you want to remove your offer?';
$string['confirm_accept'] = 'Are you sure you want to accept this offer and exchange groups? The exchange cannot be reversed.';
$string['offer_doesnt_exist'] = 'Selected offer does not exist. Maybe it was removed by the offerer.';
$string['offer_already_taken'] = 'Selected offer does was accepted by someone else. Too slow.';
$string['offer_accepted'] = 'Exchange offer accepted, groups were exchanged.';
$string['error_notenoughgroups'] = 'You must choose at least 2 groups fot the exchange!';
$string['fitting_offers'] = 'Following standing offers were found matching your chosen groups. You might want to accept one of these offers instead of creating a new one.';
$string['submit_offer_ignore'] = 'Ignore the standing offers and post my new offer';
$string['setting_students_only'] = 'Students only';
$string['setting_students_only_help'] = 'Allow only students to participate in this group exchange.';
$string['error_students_only'] = 'Only students can participate in this group exchange activity.';
