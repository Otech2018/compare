<?php

class diffTool
{

    //*********************************************************************************//
    //*********************************************************************************//
    //*********************************************************************************//
    public function renderDiffTool($fullNamePath1, $fullNamePath2)
    {
      global $INDEX_PHP;
      $res = '';

      $filename1=basename($fullNamePath1);
      $filename2=basename($fullNamePath2);

      //What file I need to show on top
      $file2show = '1';
      if(isset($POST["file2show"]) && $POST["file2show"]!='')       $file2show=$POST["file2show"];

      //check to see if the mime-type starts with 'text'
      $finfo = finfo_open(FILEINFO_MIME);
      $conType1 = finfo_file($finfo, $fullNamePath1);
      $conType2 = finfo_file($finfo, $fullNamePath2);
      finfo_close($finfo);
      if( !(substr( $conType1, 0, 4)=='text' || substr($conType1, 0, 19) == 'application/x-empty') ||
          !(substr( $conType2, 0, 4)=='text' || substr($conType2, 0, 19) == 'application/x-empty'))
      {
      return "<script>
          alert('File format not supported');
          window.location.href = '/$INDEX_PHP';
        </script>";
      }        

      $res .= "<div style=\"position:absolute;top:0px;left:0px;z-index:10;background:#373737; width:100%;min-height:100%;font-size:8pt;\">";

      $disp1 = "";
      $bg1   = "background:#FF6600;";
      $disp2 = "display:none;";
      $bg2   = "";
      if($file2show == '2')
      {
        $disp2 = "";
        $bg2   = "background:#FF6600;";
        $disp1 = "display:none;";
        $bg1   = "";
      }

       $res .= "
        <div class=\"listM_Oriz\" style=\"float:left;margin-left:2%;\">
            <ul>
               <li style=\"width:470px;\">
                <a id=\"fn1\" href=\"\" style=\"".$bg1."\" onclick=\"$('#textEditor2').hide();$('#fn2').css('background','');
                         $('#textEditor1').show();$('#fn1').css('background','#FF6600');
                         return false;\">".$filename1."</a>
               </li>
             </ul>
        </div>
        <div class=\"listM_Oriz\" style=\"float:right;margin-right:2%;\">
            <ul>
               <li style=\"width:470px;\"> 
               <a id=\"fn2\" href=\"\" style=\"".$bg2."\" onclick=\"$('#textEditor1').hide();$('#fn1').css('background','');
                         $('#textEditor2').show();$('#fn2').css('background','#FF6600');
                         return false;\">".$filename2."</a>
               </li>
            </ul>
        </div>"; 
           
        $res .= "<div style=\"clear:both;\"> </div><hr>";
        
        //FIRST TEXT AREA 
        $res .= "<div id=\"textEditor1\" style=\"".$disp1."\">";
          $res .= "<div style=\"float:left;width:48%;margin-right:1%;margin-left:1%;\">";
            $te1  = new textEditor();
            $po1  = Array();
            $po1['filename'] = $fullNamePath1;
            $res .= $te1->renderForm($po1, '1');
          $res .= "</div>";

          $res .= "<div style=\"float:left;width:48%;margin-right:1%;margin-left:1%;\">";
            $res .= $this->renderDiffColumn($fullNamePath2, $fullNamePath1);
          $res .= "</div>";
        $res .= "</div>";

        //SECOND TEXT AREA
        $res .= "<div id=\"textEditor2\" style=\"".$disp2."\">";
          $res .= "<div style=\"float:left;width:48%;margin-right:1%;margin-left:1%;\">";
            $res .= $this->renderDiffColumn($fullNamePath1, $fullNamePath2);
          $res .= "</div>";

          $res .= "<div style=\"float:left;width:48%;margin-right:1%;margin-left:1%;\">";
            $te2  = new textEditor();
            $po2  = Array();
            $po2['filename'] = $fullNamePath2;
            $res .= $te2->renderForm($po2, '2');
          $res .= "</div>";
        $res .= "</div>";

        $res .= "<div style=\"clear:both;\"> </div>";
        
        $res .= "</div>"; //Close the principal overlapped DIV

       return $res;         
    }  
    //*********************************************************************************//
    //*********************************************************************************//
    //*********************************************************************************//
    private function renderDiffColumn($fullNamePath1, $fullNamePath2)
    {
        $orig      =  explode("\n", file_get_contents($fullNamePath1) );
        $diffRes   =  explode("\n", shell_exec("diff $fullNamePath1 $fullNamePath2") );
        $finalFile = '';
        $action = Array();
        $res = '';
        $i=1;
        $lines=1;

        while(!empty($diffRes))
        {
          $diffRes = $this->retrieveDiffAction($diffRes, $action);

          //Preparazione al posizionamento
          switch($action['type'])
          {
            case 'a':
                $action['l11']++;
            break;
          }

          //Mi posiziono all'inizio della differenza
          while($i < $action['l11'])
          {
            $finalFile .=  str_pad ("$lines | ", 8, " ", STR_PAD_LEFT).array_shift($orig)."\n";
            $lines++;
            $i++;
          }

          while( ($d=array_shift($action['data'])) )
          {
            //Inserisco le differenze
            switch($action['type'])
            {
              case 'a':
                $str = substr($d,2);
                if($str == "") $str = "\n";
                $finalFile .= "<ins>".str_pad ("$lines | ", 8, " ", STR_PAD_LEFT).$str."</ins>\n";
                $lines++;
              break;

              case 'd':
                $str = substr($d,2);
                if($str == "") $str = "\n";
                $finalFile .= "<del>".str_pad ("      | ", 8, " ", STR_PAD_LEFT).$str."</del>\n";
              break;

              case 'c':
                $str = substr($d,2);
                if($str == "") $str = "\n";
                
                if($d[0] == '<' )
                {
                          $finalFile .= "<del>".str_pad ("      | ", 8, " ", STR_PAD_LEFT).$str."</del>\n";
                }
                else if($d[0] == '>' )
                {
                          $finalFile .= "<ins>".str_pad ("$lines | ", 8, " ", STR_PAD_LEFT).$str."</ins>\n";
                          $lines++;
                }
              break;
            }
          }

          //Preparazione al posizionamento
          switch($action['type'])
          {
            case 'd':
            case 'c':
                array_shift($orig);
                $i++;
            break;
          }

                        
          //Mi posiziono alla fine della differenza 
          while($i <= $action['l12'])
          {
            array_shift($orig);
            $i++;                   
          }
          
        }


        while( ($r = array_shift($orig)))
        {
          $finalFile .= str_pad ("$lines | ", 8, " ", STR_PAD_LEFT).$r."\n";
          $lines++;
        }
           
        $res .= "<div id=\"difference\" style=\"font-family:monospace;font-size:8.5pt;line-height:120%;margin-top:5px;\">";
                    $res .= "<pre >";
                    //$res .= "//".shell_exec("diff $fullNamePath1 $fullNamePath2")."//";
        $res .= $finalFile;
        $res .= "</pre >";
        $res .= "</div>";


      return $res;
    }
    //*********************************************************************************//
    //*********************************************************************************//
    //*********************************************************************************//
    private function retrieveDiffAction($diffRes, &$action)
    {
      $r=$diffRes[0];
      $action['l11'] = $action['l12'] = $action['l21'] = $action['l22'] = 0;
      
      array_shift($diffRes);	// Rimuovo la riga appena letta
      
      if( ($idxType=strpos ( $r , 'd')) )
      {
        $action['type']="d";
      }
      else if( ($idxType=strpos ( $r , 'c')) )
      {
         $action['type']="c";
      }
      else if( ($idxType=strpos ( $r , 'a')) )
      {
         $action['type']="a";
      }
     
      //Cerco la prima virgola
      $idxComma=strpos ( $r , ',');
      if(!$idxComma)
      {
        //Se la prima virgola non c'è ho un numero di linea prima e
        //uno dopo
        $action['l1n']=1;
        $action['l2n']=1;
        sscanf($r,"%d%c%d", $action['l11'], $action['type'], $action['l21']);
      }
      else if($idxComma < $idxType)
      {
        //Se la prima virgola e posizionata prima del type
        //allora ho un due numeri di linea prima
        $action['l1n']=2;

        $idxComma=strpos ( $r , ',', $idxComma);
        if(!$idxComma)
        {
          //qui ho un numero di linea dopo
          $action['l2n']=1;
          sscanf($r,"%d,%d%c%d", $action['l11'],$action['l12'], $action['type'], $action['l21']);
        }
        else
        {
          //qui ho due numeri di linea dopo
          //qui ho un numero di linea dopo
          $action['l2n']=1;
          sscanf($r,"%d,%d%c%d,%d", $action['l11'],$action['l12'], $action['type'], $action['l21'], $action['l22']);
        }
      }
      else
      {
        //Se la prima virgola e posizionata dopo il type
        //allora ho un numero di linea prima
        $action['l1n']=1;

        $idxComma=strpos ( $r , ',', $idxComma);
        if(!$idxComma)
        {
          //qui ho un numero di linea dopo
          $action['l2n']=1;
          sscanf($r,"%d%c%d", $action['l11'], $action['type'], $action['l21']);
        }
        else
        {
          //qui ho due numeri di linea dopo
          //qui ho un numero di linea dopo
          $action['l2n']=1;
          sscanf($r,"%d%c%d,%d", $action['l11'], $action['type'], $action['l21'], $action['l22']);
        }
      }

      //UNA VOLTA LETTA LA RIGA DELL'AZIONE RICAVO I DATI
      $action['data'] = Array();
      foreach($diffRes as $j => $r)
      {
        $exit=false;

        $c0 = substr($r, 0, 1);
        
        switch($action['type'])
        {
          case 'a':
            if( $c0 != '>') $exit=true;
          break;

          case 'd':
            if( $c0 != '<') $exit=true;
          break;

          case 'c':
            if($c0 != '<' && $c0 != '>' && $c0 != '-') $exit=true;
          break;
        }

        if($exit) break;
        $action['data'][$j]= $r;
        array_shift($diffRes);
      }

      return $diffRes;
    }
    //*********************************************************************************//
    //*********************************************************************************//
    //*********************************************************************************//    
}
?>
