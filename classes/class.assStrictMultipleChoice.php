<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @extends assMultipleChoice
 * 
 * @ingroup     ModulesTestQuestionPool
 */

require_once('Modules/TestQuestionPool/classes/class.assMultipleChoice.php');
require_once('export/qti12/class.assStrictMultipleChoiceExport.php');
require_once('import/qti12/class.assStrictMultipleChoiceImport.php');

class assStrictMultipleChoice extends assMultipleChoice {
        protected $points;

        /**
        * assStrictMultipleChoice constructor
        *
        * The constructor takes possible arguments an creates an instance of the assStrictMultipleChoice object.
        *
        * @param string     $title                 A title string to describe the question
        * @param string     $comment               A comment string to describe the question
        * @param string     $author                A string containing the name of the questions author
        * @param integer    $owner                 A numerical ID to identify the owner/creator
        * @param string     $question              The question string of the MultipleChoice question
        * @param int|string $output_type           The output order of the MultipleChoice answers
        */
        function _construct(
                $title = "", 
                $comment = "", 
                $author = "", 
                $owner = -1, 
                $question = "", 
                $output_type = OUTPUT_ORDER,
                $points = 0
        ) {
                parent::_construct($title, $comment, $author, $owner, $question, $output_type);
                $this->pointsForCorrectAnswers = 0;
        }
        
        public function getQuestionType() {
                return "assStrictMultipleChoice";
        }

        public function duplicate($for_test = true, $title = "", $author = "", $owner = "", $testObjId = null) {
            return parent::duplicate($for_test, $title, $author, $owner, $testObjId);
        }

        public function getPointsForCorrectAnswers() {
                return $this->pointsForCorrectAnswers;
        }

        public function setPointsForCorrectAnswers($points) {
                $this->pointsForCorrectAnswers = $points;
        }

        public function savePointsForCorrectAnswersToDb($original_id) {
                global $ilDB;
                
                $id = $this->getId();
                
                $result = $ilDB->queryF(
                        "SELECT * FROM qpl_smc WHERE question_id = %s",
                        array("integer"),
                        array( $id ) 
                );
        
                if ($result->numRows() <= 0) {
                    $affectedRows = $ilDB->insert(
                        "qpl_smc",
                        array(
                            "question_id" => array( "integer", $id ),
                            "points"      => array( "integer", $this->getPoints())
                        )
                    );
                } else {
                    $affectedRows = $ilDB->update(
                        "qpl_smc", 
                        array(
                            "points" => array( "integer", $this->getPoints() )
                        ),
                        array(
                            "question_id" => array( "integer", $id )
                        )
                    );
                }
        }

        public function loadPointsForCorrectAnswersFromDb($question_id) {
                global $ilDB;
                $result = $ilDB->queryF(
                        "SELECT points FROM qpl_qst_smc WHERE question_id = %s;",
                        array("integer"),
                        array($this->getId())
                );
                if ($result->numRows() == 1) {
                        $data = $ilDB->fetchAssoc($result);
                        $this->setPoints($data["points"]);
                }
        }

        public function saveToDb($original_id = "") {
                $this->savePointsForCorrectAnswersToDb($original_id);
                parent::saveToDb($original_id);
        }
                
        public function loadFromDb($question_id) {
                parent::loadFromDb($question_id);
                $this->loadPointsForCorrectAnswersFromDb($question_id);
        }

        public function delete($question_id) {
                global $ilDB;

                $affectedRows = $ilDB->manipulate( "DELETE FROM qpl_smc WHERE question_id = ". $question_id );
                parent::delete($question_id);
        }

        public function toJSON() {
                $result = json_decode( parent::toJSON() );
                $result['points'] = $this->getPointsForCorrectAnswers();
                return json_encode($result);
        }
        
        public function getMaximumPoints() {
            return $this->getPointsForCorrectAnswers();
        }
        
        public function calculateReachedPoints($active_id, $pass = NULL, $returndetails = FALSE) {
                $reached_points = parent::calculateReachedPoints($active_id, $pass, $returndetails);
                $maximum_points = parent::getMaximumPoints();
                if ($reached_points == $maximum_points) {
                    return $this->getPointsForCorrectAnswers();
                }
                return 0;
        }
}

?>
