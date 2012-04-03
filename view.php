<?php

    require_once("../../config.php");
    require_once("lib.php");
	require_once($CFG->dirroot.'/group/lib.php'); 

    $id         = required_param('id', PARAM_INT);                 // Course Module ID
    $action     = optional_param('action', 'view', PARAM_ALPHA);
    $attemptids = optional_param('attemptid', array(), PARAM_INT); // array of attempt ids for delete action

    $url = new moodle_url('/mod/groupexchange/view.php', array('id'=>$id));
    if ($action !== 'view') {
        $url->param('action', $action);
    }
	
    $PAGE->set_url($url);
	
	/*
	 * Handling errors of misconfiguration and course access
	 */
    if (! $cm = get_coursemodule_from_id('groupexchange', $id)) {
        print_error('invalidcoursemodule');
    }

    if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
        print_error('coursemisconf');
    }

    require_course_login($course, false, $cm);

    if (!$exchange = groupexchange_get_instance($cm->instance)) {
        print_error('invalidcoursemodule');
    }
    
    if (!$context = get_context_instance(CONTEXT_MODULE, $cm->id)) {
        print_error('badcontext');
    }
	
	$groupmemberships = groupexchange_get_user_groups($USER);
			
    $PAGE->set_title(format_string($exchange->name));
    $PAGE->set_heading($course->fullname);
	
	$errors = array();
		
	echo $OUTPUT->header();
   
	$renderer = $PAGE->get_renderer('mod_groupexchange');
	echo $renderer->show_styles();
	
    echo '<div class="clearer"></div>';

    if ($exchange->intro) {
        echo $OUTPUT->box(format_module_intro('groupexchange', $exchange, $cm->id), 'generalbox', 'intro');
    }
   
    /*
	 * Creating a new offer
	 */
    if ($action == 'offer') {
		// get input
		$offer_group = optional_param('offer_group', 0, PARAM_INT);
		$request_group = optional_param('request_group', array(), PARAM_RAW);
	
		// validate input
		if (!in_array($offer_group, $groupmemberships) || !isset($exchange->groups[$offer_group]))
			$errors['offer_group'] = get_string('error_offer_group', 'groupexchange');	
		if (sizeof($request_group) < 1)
			$errors['request_group'] = get_string('error_request_group_not_enough', 'groupexchange');
		foreach($request_group as $groupid => $x) {
			if (!isset($exchange->groups[$groupid]) || in_array($groupid, $groupmemberships))
				$errors['request_group'] = get_string('error_request_group_bad', 'groupexchange') . ": " . $groupid;
		}
		
		if (!$errors) {
			// TODO: check, if offer can be filled by one of the existing ones
				
			// finally, create new offer
			groupexchange_create_offer($exchange, $offer_group, $request_group);
			echo $OUTPUT->notification(get_string('offer_created', 'groupexchange'), 'notifysuccess');
			// reload the exchange object
			$exchange = groupexchange_get_instance($cm->instance);
		}
	}
	
	/*
	 * Deleting an offer
	 */
    if ($action == 'delete') {  
		$delete_offer = optional_param('offer', 0, PARAM_INT);
		
		if (groupexchange_delete_offer($delete_offer)) {
			echo $OUTPUT->notification(get_string('offer_deleted', 'groupexchange'), 'notifysuccess');
			// reload the exchange object
			$exchange = groupexchange_get_instance($cm->instance);
		} else {
			echo $OUTPUT->notification(get_string('error_delete_offer', 'groupexchange'), 'notifyerror');
		}
		
		$action = 'view';
	}
	
    $groupexchangeopen = true;
    $timenow = time();
    if ($exchange->timeclose !=0) {
        if ($exchange->timeopen > $timenow ) {
            echo $OUTPUT->box(get_string("notopenyet", "groupexchange", userdate($exchange->timeopen)), "generalbox notopenyet");
            echo $OUTPUT->footer();
            exit;
        } else if ($timenow > $exchange->timeclose) {
            echo $OUTPUT->box(get_string("expired", "groupexchange", userdate($exchange->timeclose)), "generalbox expired");
			echo $OUTPUT->footer();
            exit;
        }
    }
	
	// render standing offers
	if ($action == 'view' 
			|| ($action == 'offer' && empty($errors))) {
		echo $renderer->show_offers($cm, $exchange);
		echo '<br><br>';
	}
	
	// if no offer is standing, render offer form
	if (($action == 'offer' && !empty($errors)) 
			|| ($action == 'view' && !$DB->record_exists('groupexchange_offers', array('groupexchange' => $exchange->id, 'userid' => $USER->id, 'accepted_by' => 0))))
		echo $renderer->show_offer_form($cm, $exchange, $errors);
	
    
    echo $OUTPUT->footer();
