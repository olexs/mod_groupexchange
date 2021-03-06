<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot . '/course/moodleform_mod.php');

class mod_groupexchange_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $DB, $COURSE;

        $mform = & $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('groupexchangename', 'groupexchange'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->add_intro_editor(true, get_string('chatintro', 'chat'));

        $mform->addElement('hidden', 'assigned', '', '0');

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'groupshdr', get_string('choose_groups', 'groupexchange'));
		//$mform->addHelpButton('groupshdr', 'choose_groups', 'groupexchange');
		$mform->addElement('static', 'groups', '', get_string('choose_groups_help', 'groupexchange'));
		
		$this->add_checkbox_controller(1, get_string("checkallornone", 'groupexchange'));
		$db_groups = $DB->get_records('groups', array('courseid' => $COURSE->id), 'name');
        foreach ($db_groups as $group) {
            $mform->addElement('advcheckbox', 'group['.$group->id.']', '', $group->name, array('group' => 1));
        }
		
//-------------------------------------------------------------------------------
        $mform->addElement('header', 'miscellaneoussettingshdr', get_string('miscellaneoussettings', 'form'));
        
        $mform->addElement('advcheckbox', 'anonymous', get_string('setting_anonymous', 'groupexchange'));
		$mform->addHelpButton('anonymous', 'setting_anonymous', 'groupexchange');
		
		$mform->addElement('advcheckbox', 'studentsonly', get_string('setting_students_only', 'groupexchange'));
		$mform->addHelpButton('studentsonly', 'setting_students_only', 'groupexchange');
		
        $limitoptions = array(0 => get_string('unlimited', 'groupexchange'), 1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        $mform->addElement('hidden', 'limitexchanges', 0);
        //$mform->addHelpButton('limitexchanges', 'setting_limitexchanges', 'groupexchange');
		
//-------------------------------------------------------------------------------
        $mform->addElement('header', 'timerestricthdr', get_string('timerestrict', 'groupexchange'));
        $mform->addElement('checkbox', 'timerestrict', get_string('timerestrict', 'groupexchange'));

        $mform->addElement('date_time_selector', 'timeopen', get_string("groupexchangeopen", "groupexchange"));
        $mform->disabledIf('timeopen', 'timerestrict');

        $mform->addElement('date_time_selector', 'timeclose', get_string("groupexchangeclose", "groupexchange"));
        $mform->disabledIf('timeclose', 'timerestrict');
        
//-------------------------------------------------------------------------------
        $features = new object();
	  $features->groups           = false;
	  $features->groupings        = false;
	  $features->groupmembersonly = true;
	  $this->standard_coursemodule_elements($features);
//-------------------------------------------------------------------------------
        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values) {
        global $DB;
		
		if (!empty($this->_instance) 
				&& $groups = $DB->get_records('groupexchange_groups', array('groupexchange' => $this->_instance))) {
			foreach ($groups as $group)
				$default_values['group'][$group->groupid] = 1;
		}
        
		if (empty($default_values['timeopen'])) {
            $default_values['timerestrict'] = 0;
        } else {
            $default_values['timerestrict'] = 1;
        }
    }

    function validation($data, $files) {
        global $USER, $COURSE;
        $errors = parent::validation($data, $files);

        // ensure at least group is selected
        $choices = 0;
        foreach ($data['group'] as $group) {
            if ($group == '1') {
                $choices++;
            }
        }

        if ($choices < 2) {
            $errors['groups'] = get_string('error_notenoughgroups', 'groupexchange');
        }

        return $errors;
    }

}

