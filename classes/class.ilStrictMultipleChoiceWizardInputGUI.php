<?php

include_once("./Modules/TestQuestionPool/classes/class.ilMultipleChoiceWizardInputGUI.php");

class ilStrictMultipleChoiceWizardInputGUI extends ilMultipleChoiceWizardInputGUI {
    function checkInput() {
        global $lng;
        
        include_once("./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php");
        if (is_array($_POST[$this->getPostVar()])) { 
            $_POST[$this->getPostVar()] = ilUtil::stripSlashesRecursive(
                $_POST[$this->getPostVar()], 
                false, 
                ilObjAdvancedEditing::_getUsedHTMLTagsAsString("assessment")
            );
        }
        $foundvalues = $_POST[$this->getPostVar()];
        if (is_array($foundvalues)) {
            // check answers
            if (is_array($foundvalues['answer'])) {
                foreach ($foundvalues['answer'] as $aidx => $answervalue) {
                    if (((strlen($answervalue)) == 0) && (strlen($foundvalues['imagename'][$aidx]) == 0)) {
                        $this->setAlert($lng->txt("msg_input_is_required"));
                        return FALSE;
                    }
                }
            }
            if (is_array($_FILES) && count($_FILES) && $this->getSingleline()) {
                if (!$this->hideImages) {
                    if (is_array($_FILES[$this->getPostVar()]['error']['image'])) {
                        foreach ($_FILES[$this->getPostVar()]['error']['image'] as $index => $error) {
                            // error handling
                            if ($error > 0) {
                                switch ($error) {
                                    case UPLOAD_ERR_INI_SIZE:
                                        $this->setAlert($lng->txt("form_msg_file_size_exceeds"));
                                        return false;
                                        break;
                                    case UPLOAD_ERR_FORM_SIZE:
                                        $this->setAlert($lng->txt("form_msg_file_size_exceeds"));
                                        return false;
                                        break;
                                    case UPLOAD_ERR_PARTIAL:
                                        $this->setAlert($lng->txt("form_msg_file_partially_uploaded"));
                                        return false;
                                        break;
                                    case UPLOAD_ERR_NO_FILE:
                                        if ($this->getRequired()) {
                                            if ((!strlen($foundvalues['imagename'][$index])) && (!strlen($foundvalues['answer'][$index]))) {
                                                $this->setAlert($lng->txt("form_msg_file_no_upload"));
                                                return false;
                                            }
                                        }
                                        break;
                                    case UPLOAD_ERR_NO_TMP_DIR:
                                        $this->setAlert($lng->txt("form_msg_file_missing_tmp_dir"));
                                        return false;
                                        break;
                                    case UPLOAD_ERR_CANT_WRITE:
                                        $this->setAlert($lng->txt("form_msg_file_cannot_write_to_disk"));
                                        return false;
                                        break;
                                    case UPLOAD_ERR_EXTENSION:
                                        $this->setAlert($lng->txt("form_msg_file_upload_stopped_ext"));
                                        return false;
                                        break;
                                }
                            }
                        }
                    } else {
                        if ($this->getRequired()) {
                            $this->setAlert($lng->txt("form_msg_file_no_upload"));
                            return false;
                        }
                    }
                    if (is_array($_FILES[$this->getPostVar()]['tmp_name']['image'])) {
                        foreach ($_FILES[$this->getPostVar()]['tmp_name']['image'] as $index => $tmpname) {
                            $filename = $_FILES[$this->getPostVar()]['name']['image'][$index];
                            $filename_arr = pathinfo($filename);
                            $suffix = $filename_arr["extension"];
                            $mimetype = $_FILES[$this->getPostVar()]['type']['image'][$index];
                            $size_bytes = $_FILES[$this->getPostVar()]['size']['image'][$index];
                            // check suffixes
                            if (strlen($tmpname) && is_array($this->getSuffixes())) {
                                if (!in_array(strtolower($suffix), $this->getSuffixes())) {
                                    $this->setAlert($lng->txt("form_msg_file_wrong_file_type"));
                                    return false;
                                }
                            }
                        }
                    }
                    if (is_array($_FILES[$this->getPostVar()]['tmp_name']['image'])) {
                        foreach ($_FILES[$this->getPostVar()]['tmp_name']['image'] as $index => $tmpname) {
                            $filename = $_FILES[$this->getPostVar()]['name']['image'][$index];
                            $filename_arr = pathinfo($filename);
                            $suffix = $filename_arr["extension"];
                            $mimetype = $_FILES[$this->getPostVar()]['type']['image'][$index];
                            $size_bytes = $_FILES[$this->getPostVar()]['size']['image'][$index];
                            // virus handling
                            if (strlen($tmpname)) {
                                $vir = ilUtil::virusHandling($tmpname, $filename);
                                if ($vir[0] == false) {
                                    $this->setAlert($lng->txt("form_msg_file_virus_found")."<br />".$vir[1]);
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return FALSE;
        }
        return $this->checkSubItemsInput();
    }

    function insert(&$a_tpl) {
        global $lng;
        
        $tpl_path = ilPlugin::getPluginObject(IL_COMP_MODULE, 'TestQuestionPool', 'qst', 'assStrictMultipleChoice')->getDirectory();
        $tpl = new ilTemplate('tpl.prop_strictmultiplechoicewizardinput.html', true, true, $tpl_path);
        $i = 0;
        foreach ($this->values as $value) {
            if ($this->getSingleline()) {
                if (!$this->hideImages) {
                    if (strlen($value->getImage())) {
                        $imagename = $this->qstObject->getImagePathWeb() . $value->getImage();
                        if (($this->getSingleline()) && ($this->qstObject->getThumbSize())) {
                            if (@file_exists($this->qstObject->getImagePath() . $this->qstObject->getThumbPrefix() . $value->getImage())) {
                                $imagename = $this->qstObject->getImagePathWeb() . $this->qstObject->getThumbPrefix() . $value->getImage();
                            }
                        }
                        $tpl->setCurrentBlock('image');
                        $tpl->setVariable('SRC_IMAGE', $imagename);
                        $tpl->setVariable('IMAGE_NAME', $value->getImage());
                        $tpl->setVariable('ALT_IMAGE', ilUtil::prepareFormOutput($value->getAnswertext()));
                        $tpl->setVariable('TXT_DELETE_EXISTING', $lng->txt('delete_existing_file'));
                        $tpl->setVariable('IMAGE_ROW_NUMBER', $i);
                        $tpl->setVariable('IMAGE_POST_VAR', $this->getPostVar());
                        if($this->disable_upload) {
                            $tpl->setVariable('DISABLED_UPLOAD', 'type="hidden" disabled="disabled"');
                        }
                        $tpl->parseCurrentBlock();
                    }
                    $tpl->setCurrentBlock('addimage');
                    $tpl->setVariable('IMAGE_ID', $this->getPostVar() . '[image][$i]');
                    $tpl->setVariable('IMAGE_SUBMIT', $lng->txt('upload'));
                    $tpl->setVariable('IMAGE_ROW_NUMBER', $i);
                    $tpl->setVariable('IMAGE_POST_VAR', $this->getPostVar());
                    if($this->disable_upload) {
                        $tpl->setVariable('DISABLED_UPLOAD', "type='hidden', disabled='disabled'");
                    }
                    $tpl->parseCurrentBlock();
                }
        
                if (is_object($value)) {
                    $tpl->setCurrentBlock("prop_text_propval");
                    $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getAnswertext()));
                    $tpl->parseCurrentBlock();
                }
                $tpl->setCurrentBlock('singleline');
                if($this->disable_text) {
                    $tpl->setVariable("DISABLED_SINGLELINE", 'readonly="readonly"');
                    $tpl->setVariable("DISABLED_SINGLELINE_BTN", 'readonly="readonly"');
                }
                $tpl->setVariable("SIZE", $this->getSize());
                $tpl->setVariable("SINGLELINE_ID", $this->getPostVar() . "[answer][$i]");
                $tpl->setVariable("SINGLELINE_ROW_NUMBER", $i);
                $tpl->setVariable("SINGLELINE_POST_VAR", $this->getPostVar());
                $tpl->setVariable("MAXLENGTH", $this->getMaxLength());
                if ($this->getDisabled()) {
                    $tpl->setVariable("DISABLED_SINGLELINE", " disabled=\"disabled\"");
                }
                $tpl->parseCurrentBlock();
            } else if (!$this->getSingleline()) {
                if (is_object($value)) {
                    $tpl->setCurrentBlock("prop_points_propval");
                    $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getPoints()));
                    $tpl->parseCurrentBlock();
                    $tpl->setCurrentBlock("prop_points_unchecked_propval");
                    $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getPointsUnchecked()));
                    $tpl->parseCurrentBlock();
                }
                $tpl->setCurrentBlock('multiline');
                if($this->disable_text) {
                    $tpl->setVariable("DISABLED_MULTILINE", 'readonly="readonly"');
                }
        
                $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($value->getAnswertext()));
                $tpl->setVariable("MULTILINE_ID", $this->getPostVar() . "[answer][$i]");
                $tpl->setVariable("MULTILINE_ROW_NUMBER", $i);
                $tpl->setVariable("MULTILINE_POST_VAR", $this->getPostVar());
                if ($this->getDisabled()) {
                    $tpl->setVariable("DISABLED_MULTILINE", " disabled=\"disabled\"");
                }
                $tpl->parseCurrentBlock();
            }
            if ($this->getAllowMove()) {
                $tpl->setCurrentBlock("move");
                $tpl->setVariable("CMD_UP", "cmd[up" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("CMD_DOWN", "cmd[down" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("MOVE_ID", $this->getPostVar() . "[$i]");
                $tpl->setVariable("UP_BUTTON", ilUtil::getImagePath('a_up.png'));
                $tpl->setVariable("DOWN_BUTTON", ilUtil::getImagePath('a_down.png'));
                $tpl->parseCurrentBlock();
            }
            $tpl->setCurrentBlock("row");
            $class = ($i % 2 == 0) ? "even" : "odd";
            if ($i == 0) $class .= " first";
            if ($i == count($this->values)-1) $class .= " last";
            $tpl->setVariable("ROW_CLASS", $class);
            $tpl->setVariable("POST_VAR", $this->getPostVar());
            $tpl->setVariable("ROW_NUMBER", $i);
            // correct answer?
            if(!$this->disable_actions) {
                $tpl->setVariable( "CMD_ADD", "cmd[add" . $this->getFieldId() . "][$i]" );
                $tpl->setVariable("CMD_REMOVE", "cmd[remove" . $this->getFieldId() . "][$i]");
                $tpl->setVariable("ID", $this->getPostVar() . "[answer][$i]");
            }
            if($this->disable_actions) {
                //$tpl->setVariable( 'DISABLE_ACTIONS', 'disabled="disabled"' );
            } else {
                $tpl->setVariable("ADD_BUTTON", ilUtil::getImagePath('edit_add.png'));
                $tpl->setVariable("REMOVE_BUTTON", ilUtil::getImagePath('edit_remove.png'));
            }
            $tpl->parseCurrentBlock();
            $i++;
        }
        
        if ($this->getSingleline()) {
            if (!$this->hideImages) {
                if (is_array($this->getSuffixes())) {
                    $suff_str = $delim = "";
                    foreach($this->getSuffixes() as $suffix) {
                        $suff_str.= $delim.".".$suffix;
                        $delim = ", ";
                    }
                    $tpl->setCurrentBlock('allowed_image_suffixes');
                    $tpl->setVariable("TXT_ALLOWED_SUFFIXES", $lng->txt("file_allowed_suffixes")." ".$suff_str);
                    $tpl->parseCurrentBlock();
                }
        
                $tpl->setCurrentBlock("image_heading");
                $tpl->setVariable("ANSWER_IMAGE", $lng->txt('answer_image'));
                $tpl->setVariable("TXT_MAX_SIZE", ilUtil::getFileSizeInfo());
                $tpl->parseCurrentBlock();
            }
        }
        
        $tpl->setVariable("ELEMENT_ID", $this->getPostVar());
        $tpl->setVariable("TEXT_YES", $lng->txt('yes'));
        $tpl->setVariable("TEXT_NO", $lng->txt('no'));
        $tpl->setVariable("DELETE_IMAGE_HEADER", $lng->txt('delete_image_header'));
        $tpl->setVariable("DELETE_IMAGE_QUESTION", $lng->txt('delete_image_question'));
        $tpl->setVariable("ANSWER_TEXT", $lng->txt('answer_text'));
        $tpl->setVariable("POINTS_TEXT", $lng->txt('points'));
        if(!$this->disable_actions) {
            $tpl->setVariable("COMMANDS_TEXT", $lng->txt('actions'));
        }
        //$tpl->setVariable("CORRECT_ANSWER_TEXT", $lng->txt('correct_answer'));
        
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $tpl->get());
        $a_tpl->parseCurrentBlock();
        
        global $tpl;
        include_once "./Services/YUI/classes/class.ilYuiUtil.php";
        ilYuiUtil::initDomEvent();
        $tpl->addJavascript("./Modules/TestQuestionPool/templates/default/multiplechoicewizard.js");
    }

    function setValue($a_value) {
        if (is_array($a_value)) {
            if (is_array($a_value['anser'])) {
                // change all correct_answers
                foreach ($a_value['answer']['correct_answer'] as $index => $value) {
                    if ($value) { // not sure if this works
                        $a_value['answer']['points'][$index] = 1;
                        $a_value['answer']['unchecked_points'][$index] = 0;
                    } else {
                        $a_value['answer']['points'][$index] = 0;
                        $a_value['answer']['unchecked_points'][$index] = 1;
                    }
                }
            }
        }
        parent::setValue($a_value);
    }

}

?>
