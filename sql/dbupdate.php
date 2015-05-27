<#1>
<?php

$res = $ilDB->queryF("SELECT * FROM qpl_qst_type WHERE type_tag = %s", array("text"), array("assStrictMultipleChoice"));

if ($res->numRows() == 0) {
    $res = $ilDB->query("SELECT MAX(question_type_id) maxid FROM qpl_qst_type");
    $data = $ilDB->fetchAssoc($res);
    $max = $data["maxid"] + 1;

    $affectedRows = $ilDB->manipulateF(
        "INSERT INTO qpl_qst_type (question_type_id, type_tag, plugin) VALUES (%s, %s, %s)",
        array("integer", "text", "integer"),
        array($max, "assStrictMultipleChoice", 1)
    );
}

?>
<#2>
<#3>
<#4>
<?php

if (!$ilDB->tableExists("qpl_qst_smc")) {
    $fields = array(
        "question_id" => array(
                    "type"   => "integer",
                    "length" => 4
        ),
        "points" => array(
                    "type"   => "integer",
                    "length" => 4
        )
    );

    $ilDB->createTable("qpl_qst_smc", $fields);
}

?>
