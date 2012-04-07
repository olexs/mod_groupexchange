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
 * Group exchange plugin event handler definition.
 *
 * @package   mod_groupexchange
 * @copyright 2012 Olexandr Savchuk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* List of handlers */
$handlers = array (
    'groups_group_deleted' => array (
        'handlerfile'      => '/mod/groupexchange/lib.php',
        'handlerfunction'  => 'groupexchange_eventhandler_groupdelete',
        'schedule'         => 'instant'
    ),
	'groups_member_removed' => array (
        'handlerfile'      => '/mod/groupexchange/lib.php',
        'handlerfunction'  => 'groupexchange_eventhandler_memberremove',
        'schedule'         => 'instant'
    )
);