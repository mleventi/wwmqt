<?php // $Id: preview.php,v 1.13.2.6 2008-01-24 15:06:46 tjhunt Exp $
/**
 * The email instructor form and page.
 *
 * @copyright &copy; 2008 Matthew Leventi
 * @author mleventi@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package webwork_qtype
 **/

    require_once("../../../config.php");
    require_once($CFG->libdir.'/questionlib.php');
    require_once($CFG->libdir.'/weblib.php');
    require_once($CFG->dirroot.'/mod/quiz/locallib.php'); // We really want to get rid of this

    $questionid = required_param('qid', PARAM_INT);
    $attemptid = required_param('aid', PARAM_INT);
    $message = optional_param('message', '', PARAM_RAW);
    $send = optional_param('send',0,PARAM_INT);
    
    if (!$attempt = get_record('quiz_attempts', 'id', $attemptid)) {
        error('No such attempt ID exists');
    }
    if (!$neweststateid = get_field('question_sessions', 'newest', 'attemptid', $attempt->uniqueid, 'questionid', $questionid)) {
        // newest_state not set, probably because this is an old attempt from the old quiz module code
        if (!$state = get_record('question_states', 'question', $questionid, 'attempt', $attempt->uniqueid)) {
            error('Invalid question id');
        }
    } else {
        if (! $state = get_record('question_states', 'id', $neweststateid)) {
            error('Invalid state id');
        }
    }
    
    if (! $question = get_record('question', 'id', $state->question)) {
        error('Question for this state is missing');
    }
    if (! $quiz = get_record('quiz', 'id', $attempt->quiz)) {
        error('Course module is incorrect');
    }
    if (! $course = get_record('course', 'id', $quiz->course)) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('quiz', $quiz->id, $course->id)) {
        error('Course Module ID was incorrect');
    }
    
    require_login($course->id, false, $cm);
    
    // Find where the question is in the quiz
    $questionorder = explode(',',$quiz->questions);
    $count = 1;
    for($i=0;$i<$questionorder;$i++) {
        if($questionorder[$i] != 0) {
            if($questionorder[$i] == $question->id) {
                break;
            } else {
                $count++;
            }
        }
    }
    $questioninquiz = $count;
    
    $key = $question->id;
    $questions[$key] = &$question;
    if (!get_question_options($questions)) {
        error("Unable to load questiontype specific question information");
    }
    
    $session = get_record('question_sessions', 'attemptid', $attempt->uniqueid, 'questionid', $question->id);
    $state->sumpenalty = $session->sumpenalty;
    $state->manualcomment = $session->manualcomment;
    restore_question_state($question, $state);
    $state->last_graded = $state;
    
    $seed = $state->responses['seed'];
    //Send the email
    if ($send) {
        $previewlink = $CFG->wwwroot . '/question/preview.php?';
        $previewlink .= 'id=' . $questionid;
        $previewlink .= '&amp;seed=' . $seed;
        $previewlink .= '&amp;uid='.$USER->id;
        $previewlink .= '&amp;quizid='.$quiz->id;
        
        $historylink = $CFG->wwwroot . '/mod/quiz/reviewquestion.php?';
        $historylink .= 'attempt=' . $attemptid;
        $historylink .= '&amp;question=' . $questionid;
        
        
        
        $info =  "<h3>".get_string('wwquestion','qtype_webwork')."</h3>";
        $info .= "<table>";
        $info .= "<tr><td><b>Course</b></td><td>" . $course->fullname . "</td></tr>";
        $info .= "<tr><td><b>Quiz</b></td><td>" . $quiz->name . "</td></tr>";
        $info .= "<tr><td><b>Student</b></td><td>" . $USER->firstname . ' ' . $USER->lastname . "</td></tr>";
        $info .= "<tr><td><b>Question #</b></td><td>". $questioninquiz . "</td></tr>";
        $info .= "<tr><td><b>Response History</b></td><td><a href='".$historylink."'>".get_string('view','qtype_webwork')."</a></td></tr>";
        $info .= "<tr><td><b>Question Preview</b></td><td><a href='".$previewlink."'>".get_string('view','qtype_webwork')."</a></td></tr>";
        $info .= "</table>";
        
        $info .= "";
        $info .= "<br><br>";
        $message = $info . $message;
        $msghtml = $message;
        $msgtext = html_to_text($message);
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
        $sentsomewhere = false;
        if ($users = get_users_by_capability($context, 'moodle/course:viewcoursegrades')) {
            foreach ($users as $user) {
                if(email_to_user($user,$USER,get_string('wwquestion','qtype_webwork'),$msgtext,$msghtml,NULL,NULL,$USER->email,$USER->email,$USER->firstname . ' ' . $USER->lastname)) {
                    $sentsomewhere = true;
                }
            }
        }
        if($sentsomewhere) {
            echo get_string('emailconfirm','qtype_webwork').'<br><br>';
            echo '<BUTTON onclick="window.close();">Close</BUTTON>';
            exit(1);
        } else {
            print_error('error_send_email','qtype_webwork');
        }
    }
    
    

    $options = quiz_get_reviewoptions($quiz, $attempt, $context);
    $options->validation = ($state->event == QUESTION_EVENTVALIDATE);
    $options->readonly = true;
    

    $strpreview = get_string('emailinstructor','qtype_webwork');
    
    print_header($strpreview);
    print_heading($strpreview);

    echo "<b>Question</b><br>";
    print_question($question, $state, $number, $quiz, $options);
    
    
    echo "<br><br>\n";
    echo "<form method=\"post\" action=\"emailinstructor.php\">\n";
    echo "<b>Message</b><br>";
    print_textarea(true,15,30,NULL,NULL,'message','');
    
    echo "<br><br>";
    echo '<input type="hidden" name="qid" value="'.$question->id.'"/>';
    echo '<input type="hidden" name="aid" value="'.$attemptid.'"/>';
    echo '<input type="hidden" name="send" value="1"/>';
    echo '<input type="submit" value="Send Email" />';
    echo "</form>";
    use_html_editor('message');
    print_footer();
?>