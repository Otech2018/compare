<?php

class textEditor
{
    //*********************************************************************************//
    //*********************************************************************************//
    //*********************************************************************************//
    public function renderForm($POST, $uniqueID)
    //The $uniqueID parameter needs to be used when you render two istance of the text editor
    //into the same web page 
    {
        global $INDEX_PHP;
        $res = '';

        //Check the existence of the file name
        if(isset($POST["filename"]) && $POST["filename"]!='')  $fullNamePath=$POST["filename"];
        else 
        {
            return "Invalid filename. (".$POST["filename"].")";
        }

        $filename = basename ($fullNamePath);

        //check to see if the mime-type starts with 'text'
        $finfo = finfo_open(FILEINFO_MIME);
        $conType = finfo_file($finfo, $fullNamePath);
        finfo_close($finfo);
        if( !(substr( $conType, 0, 4)=='text' || substr($conType, 0, 19) == 'application/x-empty') )
        {
            return "
            <script>
              alert('File format not supported <".finfo_file($finfo, $fullNamePath).$filename.">');
              window.location.href = '/$INDEX_PHP';
            </script>";
        }           
                    
        //Load javscript function for Input File managment
        $res .= "<script>";
        $res .= $this->renderScript($POST,$uniqueID);
        $res .= "</script>";

        //READING THE WHOLE CONTENT FILE
        $strFile = file_get_contents($fullNamePath);

        $res .= "<div>";      
        $res .= "<textarea id=\"fileTextArea".$uniqueID."\" style=\"width: 100%; font-size:8pt;\">".$strFile."</textarea>";
        $res .= "</div>";

        return $res;
    }
    //*********************************************************************************//
    //*********************************************************************************//
    //*********************************************************************************//
    private function renderScript($POST,$uniqueID)
    {
        $res = '';       
        $res .= "
                $(document).ready(function()
                {
                    var h = $(window).height();
                    if( h < $(document).height() ) h = $(document).height() ;
                    $('#fileTextArea".$uniqueID."').height( ( h - 70) );
                });
            ";
        
        return $res;
    }
    
}
