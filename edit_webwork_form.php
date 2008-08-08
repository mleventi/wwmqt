<?php
/**
 * The editing form code for this question type.
 *
 * @copyright &copy; 2008 Matthew Leventi
 * @author mleventi@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package webwork_qtype
 **/
 
 
require_once("$CFG->dirroot/question/type/webwork/config.php");
require_once("$CFG->dirroot/question/type/webwork/lib/question.php");
require_once("$CFG->dirroot/question/type/webwork/lib/questionfactory.php");
require_once("$CFG->dirroot/question/type/edit_question_form.php");
require_once("$CFG->dirroot/backup/lib.php");

/**
 * webwork editing form definition.
 * 
 * See http://docs.moodle.org/en/Development:lib/formslib.php for information
 * about the Moodle forms library, which is based on the HTML Quickform PEAR library.
 */
class question_edit_webwork_form extends question_edit_form {
 
    
    function definition_inner(&$mform) {
        
        $mform->removeElement('questiontext');
        $mform->removeElement('questiontextformat');
        $mform->removeElement('image');
            
        //CODE HEADER
        $mform->addElement('header', 'codeheader', get_string("edit_codeheader", 'qtype_webwork'));
        
        //CODE DISPLAY
        if(isset($this->question->webwork)) {
          $mform->addElement('static', 'questiontext', get_string('questiontext', 'quiz'));
          $mform->setHelpButton('questiontext', array(array('questiontext', get_string('questiontext', 'quiz'), 'quiz'), 'richtext'), false, 'editorhelpbutton');
        }
        
        //CODE
        $mform->addElement('textarea', 'code', get_string('edit_code', 'qtype_webwork'),
                array('rows' => 20,'cols' => 60));
        $mform->setType('code', PARAM_RAW);
        $mform->setHelpButton('code', array('code', get_string('edit_code', 'qtype_webwork'), 'webwork'));
        
        //STOREKEY
        $mform->addElement('hidden', 'storekey');
        $mform->setType('storekey', PARAM_INT);
        $mform->setDefault('storekey', WebworkQuestionFactory::MakeNewKey());
        
    }
     
    /**
    * @desc Sets the data in the question object based on old form data. Do some tricks to get file managers to work.
    */
    function set_data($question) {
        if(isset($this->question->webwork)) {
            $wwquestion = $this->question->webwork;
            //set fields to old values
            $question->code = $wwquestion->getCodeText();
            $question->codecheck = $wwquestion->getCodeCheck();
        }
        parent::set_data($question);   
    }
    
    /**
    * @desc Validates that the question is without PG errors as mandated by codecheck level.
    * @param $data array The form data that needs to be validated.
    * @return array An array of errors indexed by field.
    */
    function validation($data) {
        //init
        $errors = array();
        //build dataobject
        $dataobject = new stdClass;
        $dataobject->code = $data['code'];
        
        //attempt to find an ID (if updating)
        if(isset($this->question->webwork)) {
            $dataobject->id = $this->question->webwork->getId();
        }
        
        //try and build the question object
        try {
            if(isset($this->_form->_submitValues['makecopy'])) {
                $wwquestion = WebworkQuestionFactory::CreateFormCopy($dataobject);
            } else {
                if(isset($dataobject->id)) {
                    $wwquestion = WebworkQuestionFactory::CreateFormUpdate($dataobject);
                } else {
                    $wwquestion = WebworkQuestionFactory::CreateFormNew($dataobject);
                }
            }
            WebworkQuestionFactory::Store($data['storekey'],$wwquestion);
        } catch(Exception $e) {
            $errors['code'] = $e->getMessage();
        }
        return $errors;
    }

    function qtype() {
        return 'webwork';
    }
}
?>
