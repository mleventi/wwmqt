<?php
/**
 * The WebworkQuestionFactory Class.
 * 
 * @copyright &copy; 2008 Matthew Leventi
 * @author mleventi@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package webwork_qtype
**/

/**
* @desc Factory that creates WebworkQuestion objects in different ways.
*/
class WebworkQuestionFactory {
    
//////////////////////////////////////////////////////////////////////////////////
//OBJECT HOLDER FUNCTIONS
//////////////////////////////////////////////////////////////////////////////////

    /**
    * @desc Holds the WebworkQuestions between event API calls.
    */
    protected static $_library;
    
    protected static $_currentkey;
    
    /**
    * @desc Stores a WebworkQuestion object into the library.
    * @param string $key The key of the object.
    * @param WebworkQuestion $wwquestion The object to be stored
    */
    public static function Store($key,$wwquestion) {
        if(!isset(WebworkQuestionFactory::$_library)) {
            WebworkQuestionFactory::$_library = array();
        }
        WebworkQuestionFactory::$_library[$key] = $wwquestion;
    }
    
    /**
    * @desc Retrieves a WebworkQuestion object from the library.
    * @param string $key The key to access the object.
    * @return WebworkQuestion The object out of the library.
    * @throws Exception when the object was not found.
    */
    public static function Retrieve($key) {
        if(!isset(WebworkQuestionFactory::$_library)) {
            WebworkQuestionFactory::$_library = array();
        }
        if(isset(WebworkQuestionFactory::$_library[$key])) {
            return WebworkQuestionFactory::$_library[$key];
        }
        throw new Exception();
    }
    
    /**
    * @desc Makes a new Store Key. This attaches a submitted forms data to the db insert event.
    * @return string The store key.
    */
    public static function MakeNewKey() {
       if(!isset(WebworkQuestionFactory::$_currentkey)) {
            WebworkQuestionFactory::$_currentkey = 46;
       }
       WebworkQuestionFactory::$_currentkey++;
       return WebworkQuestionFactory::$_currentkey;
    }
    
    /**
    * @desc Gets the current Store Key.
    * @return string The store key.
    */
    public static function GetCurrentKey() {
        return WebworkQuestionFactory::$_currentkey;  
    }

//////////////////////////////////////////////////////////////////////////////////
//LOADERS
//////////////////////////////////////////////////////////////////////////////////
    
    /**
    * @desc Loads a WebworkQuestion out of the db based on its Id.
    * @param integer $wwquestionid The Id.
    * @throws Exception if the $wwquestionid was not found.
    * @return WebworkQuestion object.
    */
    public static function Load($wwquestionid) {
        $record = get_record('question_webwork','id',$wwquestionid);
        if(!$record) {
            throw new Exception();
        }
        return new WebworkQuestion($record);
    }
    
    /**
    * @desc Loads a WebworkQuestion out of the db based on a question id.
    * @param integer $questionid The Id.
    * @throws Exception if the $questionid was not found.
    * @return WebworkQuestion object.
    */
    public static function LoadByParent($questionid) {
        $record = get_record('question_webwork','question',$questionid);
        if(!$record) {
            throw new Exception();
        }
        return new WebworkQuestion($record);
    }
    
    
//////////////////////////////////////////////////////////////////////////////////
//FORM CREATORS
//////////////////////////////////////////////////////////////////////////////////
    
    /**
    * @desc Creates a WebworkQuestion object based on form update data.
    * @param object $formdata The form data.
    * @return WebworkQuestion object.
    */
    public static function CreateFormUpdate($formdata) {
        return WebworkQuestionFactory::CreateFromForm($formdata);
    }
    
    /**
    * @desc Creates a WebworkQuestion object based on form copy data (create as a new question).
    * @param object $formdata The form data.
    * @return WebworkQuestion object.
    */
    public static function CreateFormCopy($formdata) {
        unset($formdata->id);
        return WebworkQuestionFactory::CreateFromForm($formdata);
    }
    
    /**
    * @desc Creates a WebworkQuestion object based on new form data.
    * @param object $formdata The form data.
    * @return WebworkQuestion object.
    */
    public static function CreateFormNew($formdata) {
        return WebworkQuestionFactory::CreateFromForm($formdata);
    }
    
//////////////////////////////////////////////////////////////////////////////////
//IMPORTER CREATORS
//////////////////////////////////////////////////////////////////////////////////

    /**
    * @desc Creates a WebworkQuestion from data given by the importer.
    * @param string $code The PG code.
    * @return WebworkQuestion object.
    */
    public static function Import($code) {
        $output = WebworkQuestionFactory::CodeCheck($code);
        $importdata = new stdClass;
        $importdata->codecheck = 1;
        $importdata->code = $code;
        $importdata->grading = $output->grading;
        $wwquestion = new WebworkQuestion($importdata,$output->derivation);   
        return $wwquestion;
    }
    
//////////////////////////////////////////////////////////////////////////////////
//UTILS
//////////////////////////////////////////////////////////////////////////////////

    
    public static function CreateFromForm($formdata) {
        WebworkQuestionFactory::ParseFormData($formdata);
        $output = WebworkQuestionFactory::CodeCheck($formdata->code);
        $formdata->grading = $output->grading;
        $wwquestion = new WebworkQuestion($formdata,$output->derivation);
        return $wwquestion;
    }
    
    /**
    * @desc Strips slashes and encodes the PG code out of the form.
    * @param object $formdata The form data.
    * @return true.
    */
    public static function ParseFormData(&$formdata) {
        $formdata->code = base64_encode(stripslashes($formdata->code));
        return true;
    }
    
    /**
    * @desc Checks if PG code is acceptable.
    * @param string $code The PG Code.
    * @throws Exception If there is an error with the PG code.
    * @return object Information on the question.
    */
    public function CodeCheck($code) {
        $haserrors = false;
        $haswarnings = false;
        $client = WebworkClient::Get();
        $env = WebworkQuestion::DefaultEnvironment();
        $result = $client->renderProblem($env,$code);
        
        if((isset($result->errors)) && ($result->errors != '') && ($result->errors != null)) {
            $haserrors = true;
        }
        
        if((isset($result->warnings)) && ($result->warnings != '') && ($result->warnings != null)) {
            $haswarnings = true;
        }
        
        //Validity Check
        if(($haserrors == false) && ($haswarnings == false)) {
            
            $output = new stdClass;
            $output->grading = $result->grading;
            
            $derivation = new stdClass;
            $derivation->html = $result->output;
            $derivation->seed = $result->seed;
            $output->derivation = $derivation;
        } else {
            //NOW WE ARE INVALID going to throw
            $msg = "<h2>PG Code Problems</h2>";
            if($haserrors) {
                $msg .= "<h3>Errors</h3>";
                $msg .= $result->errors;
            }
            if($haswarnings) {
                $msg .= "<h3>Warnings</h3>";
                $msg .= $result->warnings;
            }
            throw new Exception($msg);
        }
        return $output;   
    }
}
    
?>
