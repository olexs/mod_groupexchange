<?php

    require_once("../../config.php");
    require_once("lib.php");

    $id = required_param('id',PARAM_INT);   // course

    $PAGE->set_url('/mod/groupexchange/index.php', array('id'=>$id));

    if (!$course = $DB->get_record('course', array('id'=>$id))) {
        print_error('invalidcourseid');
    }

    require_course_login($course);
    $PAGE->set_pagelayout('incourse');