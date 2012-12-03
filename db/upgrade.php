<?php

// This file keeps track of upgrades to
// the groupreg module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the methods of database_manager class
//
// Please do not forget to use upgrade_set_timeout()
// before any action that may take longer time to finish.

function xmldb_groupexchange_upgrade($oldversion) {
    global $CFG, $DB;
    
    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
	
	if ($oldversion < 2012120300) {

        // Define field completionsubmit to be added to choicegroup
        $table = new xmldb_table('groupexchange');
        $field = new xmldb_field('studentsonly', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'limitexchanges');

        // Conditionally launch add field completionsubmit
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // choicegroup savepoint reached
        upgrade_mod_savepoint(true, 2012120300, 'groupexchange');
    }
    
    return true;
}


