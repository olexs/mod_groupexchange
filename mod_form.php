<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot . '/course/moodleform_mod.php');

class mod_groupexchange_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $groupexchange_SHOWRESULTS, $groupexchange_PUBLISH, $groupexchange_DISPLAY, $DB, $COURSE, $groupexchange_csvcols_importgroups;

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
		$mform->addHelpButton('groupshdr', 'choose_groups', 'groupexchange');
		
		$db_groups = $DB->get_records('groups', array('courseid' => $COURSE->id), 'name');
        foreach ($db_groups as $group) {
            $mform->addElement('advcheckbox', 'group['.$group->id.']', '', $group->name, null);
        }
		
//-------------------------------------------------------------------------------
        $mform->addElement('header', 'miscellaneoussettingshdr', get_string('miscellaneoussettings', 'form'));
        
        $mform->addElement('checkbox', 'anonymous', get_string('setting_anonymous', 'groupexchange'));
		$mform->addHelpButton('anonymous', 'setting_anonymous', 'groupexchange');
		
        $limitoptions = array(0 => get_string('unlimited', 'groupexchange'), 1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        $mform->addElement('select', 'limitexchanges', get_string('setting_limitexchanges', 'groupexchange'), $limitoptions);
        $mform->addHelpButton('limitexchanges', 'setting_limitexchanges', 'groupexchange');
		
//-------------------------------------------------------------------------------
        $mform->addElement('header', 'timerestricthdr', get_string('timerestrict', 'groupexchange'));
        $mform->addElement('checkbox', 'timerestrict', get_string('timerestrict', 'groupexchange'));

        $mform->addElement('date_time_selector', 'timeopen', get_string("groupexchangeopen", "groupexchange"));
        $mform->disabledIf('timeopen', 'timerestrict');

        $mform->addElement('date_time_selector', 'timeclose', get_string("groupexchangeclose", "groupexchange"));
        $mform->disabledIf('timeclose', 'timerestrict');
        
//-------------------------------------------------------------------------------
        $this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values) {
        global $DB;
        // Determine whether this groupexchange has already been created [if it has options]
        if (!empty($this->_instance)
                && ($options = $DB->get_records_menu('groupexchange_options', array('groupexchangeid' => $this->_instance), 'id', 'id,text'))
                && ($options3 = $DB->get_records_menu('groupexchange_options', array('groupexchangeid' => $this->_instance), 'id', 'id,grouping'))
                && ($options2 = $DB->get_records_menu('groupexchange_options', array('groupexchangeid' => $this->_instance), 'id', 'id,maxanswers'))) {
            $groupexchangeids = array_keys($options);
            $options = array_values($options);
            $options2 = array_values($options2);
            $options3 = array_values($options3);

            foreach (array_keys($options) as $key) {
                $default_values['option[' . $key . ']'] = $options[$key];
                if ($options2[$key] <= 0)
                    $options2[$key] = 1;
                $default_values['limit[' . $key . ']'] = $options2[$key];
                $default_values['grouping[' . $key . ']'] = $options3[$key];
                $default_values['optionid[' . $key . ']'] = $groupexchangeids[$key];
            }
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
        

        // ensure at least one choice is made
        $choices = 0;
        foreach ($data['option'] as $option) {
            if (trim($option) != '') {
                $choices++;
            }
        }

        if ($choices < 1) {
            $errors['option[0]'] = get_string('fillinatleastoneoption', 'groupexchange');
        }

        if ($choices < 2) {
            $errors['option[1]'] = get_string('fillinatleastoneoption', 'groupexchange');
        }

        return $errors;
    }

    function get_data() {
        global $CFG, $COURSE, $USER;
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        if (isset($data->usecsvimport)) {
            // Set options from csv
            $usercontext = get_context_instance(CONTEXT_USER, $USER->id);
            $fs = get_file_storage();
            if (!$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $data->csvfile, 'sortorder, id', false)) {
                return false;
            }
            if (count($files) != 1) {
                return false;
            }
            $csvfile = reset($files);
            $csv = readCSV($csvfile->get_content_file_handle(), true);
            
            // Manipulate $data to reflect form content (options, limit, grouping, optionid)
            $data = options_from_csv($data, $COURSE->id, $csv);
            
        }

        // Set up completion section even if checkbox is not ticked
        if (empty($data->completionsection)) {
            $data->completionsection = 0;
        }
        return $data;
    }

    function add_completion_rules() {
        $mform = & $this->_form;

        $mform->addElement('checkbox', 'completionsubmit', '', get_string('completionsubmit', 'groupexchange'));
        return array('completionsubmit');
    }

    function completion_rule_enabled($data) {
        return!empty($data['completionsubmit']);
    }

}

