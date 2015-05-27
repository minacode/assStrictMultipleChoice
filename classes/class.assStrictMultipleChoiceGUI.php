<?php

require_once("Modules/TestQuestionPool/classes/class.assMultipleChoiceGUI.php");


/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @extends assMultipleChoiceGUI
 * 
 * @ingroup     ModulesTestQuestionPool
 * 
 * @ilctrl_iscalledby assStrictMultipleChoiceGUI: assStrictMultipleChoiceGUI, ilObjQuestionPoolGUI, ilObjTestGUI, ilQuestionEditGUI, ilTestExpressPageObjectGUI
 */
class assStrictMultipleChoiceGUI extends assMultipleChoiceGUI {
    var $plugin = null;
    var $object = null;
        
    public function __construct($id = -1) {
        parent::__construct();
        include_once("./Services/Component/classes/class.ilPlugin.php");
        $this->plugin = ilPlugin::getPluginObject(IL_COMP_MODULE, "TestQuestionPool", "qst", "assStrictMultipleChoice");
        $this->plugin->includeClass("class.assStrictMultipleChoice.php");
        $this->object = new assStrictMultipleChoice();
        if ($id >= 0) {
            $this->object->loadFromDb($id);
        }
    }
        
    public function populateAnswerSpecificFormPart(ilPropertyFormGUI $form ) {
        // Choices
        include_once(ilPlugin::getPluginObject(IL_COMP_MODULE, 'TestQuestionPool', 'qst', 'assStrictMultipleChoice')->getDirectory() ."/classes/class.ilStrictMultipleChoiceWizardInputGUI.php");
        $choices = new ilStrictMultipleChoiceWizardInputGUI($this->lng->txt("answers"), "choice");
        $choices->setRequired(true);
        $choices->setQuestionObject( $this->object );
        $isSingleline = ($this->object->lastChange == 0 && !array_key_exists('types', $_POST)) ? (($this->object->getMultilineAnswerSetting()) ? false : true) : $this->object->isSingleline;
        $choices->setSingleline( $isSingleline );
        $choices->setAllowMove( false );
        if ($this->object->getSelfAssessmentEditingMode()) {
            $choices->setSize( 40 );
            $choices->setMaxLength( 800 );
        }
        if ($this->object->getAnswerCount() == 0)
            $this->object->addAnswer( "", 0, 0, 0 );
        $choices->setValues( $this->object->getAnswers() );
        $form->addItem( $choices );
    }
    
    public function populateQuestionSpecificFormPart(ilPropertyFormGUI $form ) {
        $parent_return = parent::populateQuestionSpecificFormPart($form);
        $points_input = new ilNumberInputGUI("points", "points");
        $points_input->setMinValue(0);
        $points_input->setDecimals(0);
        $points_input->setValue( $this->object->getPointsForCorrectAnswers() );
        $points_input->setRequired(true);
        $form->addItem( $points_input );
        return $parent_return;
    }
    
    // set write points-input to object
    public function writeQuestionSpecificPostData($always = false) {
        parent::writeQuestionSpecificPostData($always);
        $this->object->setPointsForCorrectAnswers( $_POST["points"] );
    }
        
    // overwritten, same code
    public function writeAnswerSpecificPostData($always = false) {
        // Delete all existing answers and create new answers from the form data
        $this->object->flushAnswers();
        if ($this->object->isSingleline) {
            foreach ($_POST['choice']['answer'] as $index => $answertext) {
                $picturefile    = $_POST['choice']['imagename'][$index];
                $file_org_name  = $_FILES['choice']['name']['image'][$index];
                $file_temp_name = $_FILES['choice']['tmp_name']['image'][$index];
                if (strlen( $file_temp_name )) {
                    // check suffix                                         
                    $suffix = strtolower( array_pop( explode( ".", $file_org_name ) ) );
                    if (in_array( $suffix, array( "jpg", "jpeg", "png", "gif" ) )) {
                        // upload image
                        $filename = $this->object->createNewImageFileName( $file_org_name );
                        if ($this->object->setImageFile( $filename, $file_temp_name ) == 0) {
                            $picturefile = $filename;
                        }
                    }
                }
                if ( $_POST['choice']['correct_answer'][$index]) {
                    $points_checked = 1;
                    $points_unchecked = 0;
                } else {
                    $points_checkd = 0;
                    $points_unchecked = 1;
                }    
                $this->object->addAnswer( 
                    $answertext,
                    $points,
                    $points_unchecked,
                    $index,
                    $picturefile
                );
            }
        } else {
            foreach ($_POST['choice']['answer'] as $index => $answer) {
                $answertext = $answer;
                if ( $_POST['choice']['correct_answer'][$index]) {
                    $points_checked = 1;
                    $points_unchecked = 0;
                } else {
                    $points_checkd = 0;
                    $points_unchecked = 1;
                }
                $this->object->addAnswer(
                    $answertext,
                    $points,
                    $points_unchecked,
                    $index
                );
            }
        }
    }
}

?>
