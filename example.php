<?php

$INDEX_PHP= "example.php";

include_once("class_textEditor.php");
include_once("class_diffTool.php");

$res = '';

$res .= "
        <!DOCTYPE html>
        <head>
            <meta charset=\"utf-8\">
            <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
             <link  href=\"style.css\" rel=\"stylesheet\" type=\"text/css\" />
            <script src=\"//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js\" ></script>
            <title>Diff Tool</title>
        </head>
        <body>";

        $tool = new diffTool;
        $res .= $tool->renderDiffTool("file1.txt","file2.txt");

$res .= "</body>";


echo $res;
?>
