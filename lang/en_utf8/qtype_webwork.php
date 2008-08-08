<?php // $Id: qtype_webwork.php,v 1.1 2007/09/11 22:52:59 mleventi Exp $
/**
 * The language strings for the qtype_webwork question type.
 *    
 * @copyright &copy; 2006 Matthew Leventi
 * @author mleventi@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package qtype_webwork
 */

 
//module name
$string['webwork'] = 'WeBWorK';
$string['emailinstructor'] = 'Email Instructor';
$string['wwquestion'] = 'WeBWorK Question';
$string['viewquestion'] = 'View Question';
$string['emailconfirm'] = 'WeBWorK Email was Sent';

//form editing
$string['addingwebwork'] = 'Adding a WeBWorK Question';
$string['editingwebwork'] = 'Editing a WeBWorK Question';
$string['edit_codeheader'] = 'WeBWorK Code';
$string['edit_code'] = 'Code';
$string['view'] = 'View';

//errors
$string['error_quiz_id'] = 'No Quiz with the ID';
$string['error_question_id'] = 'The parent question ID was not set.';
$string['error_question_id_no_child'] = 'There is no record of a webwork question with a parent ID:';
$string['error_no_filepath'] = 'No filepath key was found.';
$string['error_no_filepath_record'] = 'No record was found for the filepath key:';
$string['error_db_failure'] = 'Failed to change the Database.';
$string['error_no_wwquestion'] = 'The WeBWorK Question object was not found.';
$string['error_no_seed'] = 'No Seed Found.';
$string['error_no_derivation_id'] = 'No derivation ID found.';
$string['error_no_derivation'] = 'Derivation was not found.';
$string['error_failed_pick_random_question'] = 'Failed to pick a random WeBWorK question seed.';
$string['error_soap'] = 'Error in Communication with the Server Details:';

?>