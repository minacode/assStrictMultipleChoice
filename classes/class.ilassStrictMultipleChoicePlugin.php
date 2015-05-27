<?php
        
/**
* StrictMultipleChoiceQuestion plugin
*
* @author Fred Neumann <frd.neumann@fau.de>
* @version $Id$
* @ingroup ModulesTestQuestionPool
*/

require_once("./Modules/TestQuestionPool/classes/class.ilQuestionsPlugin.php");

class ilassStrictMultipleChoicePlugin extends ilQuestionsPlugin {
        final function getPluginName() {
                return "assStrictMultipleChoice";
        }
        
        final function getQuestionType() {
                return "assStrictMultipleChoice";
        }
        
        final function getQuestionTypeTranslation() {
                return $this->txt($this->getQuestionType());
        }
}
?>
