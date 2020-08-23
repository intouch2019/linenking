<?php 
require_once "../../it_config.php";
require_once("session_check.php");
require_once "lib/db/DBConn.php";

ini_set("memory_limit", "-1");
ini_set("set_time_limit", "0");
set_time_limit(0);
if (isset($_SESSION['zprofile'])) {
  $username = $_SESSION['zprofile']['username'];            // user's name
  $usermail = $_SESSION['zprofile']['useremail'];         // user's emailid
  $usercompany = $_SESSION['zprofile']['usercompany'];    // user's company
}else{
  $username = "Cottonking";
  $usermail = "";
  $usercompany = "cottonking";
  //header('Location: ../index.php?e=0');
}
if(!isset($_GET['var'])) {
  echo "<br />No Table Variable Present, nothing to Export.";
  exit;
}else{
  $tablevar = $_GET['var'];
  //echo $tablevar;
}
if(!isset($_GET['limit'])) {
  $limit = 1;                            // maximum number of Excel tabs to create, optional
}else{
  //$limit = $_GET['limit'];
}
if(!isset($_GET['debug'])) {             // optional, debug script by writing out to log file
  $debug = false;
}else{
  $debug = true;
  $handle = fopen("Auditlog/exportdebug_log.txt", "w");
  fwrite($handle, "\nDebugging On...");
}
if(!isset($_SESSION[$tablevar]) OR $_SESSION[$tablevar] == '') {
  echo "<br />Empty HTML Table, nothing to Export.";
  exit;
}else{
  $htmltable = $_SESSION[$tablevar];
  //echo $htmltable;
}
if(strlen($htmltable) == strlen(strip_tags($htmltable)) ) {     // anything left after we strip HTML?
  echo "<br />Invalid HTML Table after Stripping Tags, nothing to Export.";
  exit;
}
if($debug) {
  fwrite($handle, "\n-------------------------------------------");
  fwrite($handle, "\nHTML before prep: \n".$htmltable);
  fwrite($handle, "\n-------------------------------------------");
}
$htmltable = strip_tags($htmltable, "<table><tr><th><thead><tbody><tfoot><td><br><b><span>");
$htmltable = str_replace("<br />", "\n", $htmltable);
$htmltable = str_replace("<br/>", "\n", $htmltable);
$htmltable = str_replace("<br>", "\n", $htmltable);
$htmltable = str_replace("&nbsp;", " ", $htmltable);
$htmltable = str_replace("\n\n", "\n", $htmltable);
if($debug) {
  fwrite($handle, "\n-------------------------------------------");
  fwrite($handle, "\nHTML after prep: \n".$htmltable);
  fwrite($handle, "\n-------------------------------------------");
}
//
//  Create Document Object Model from HTML table contents
//
$dom = new domDocument;
//echo $htmltable;
$dom->loadHTML($htmltable);
if(!$dom) {
  echo "<br />Invalid HTML DOM, nothing to Export.";
  exit;
}
$dom->preserveWhiteSpace = false;             // remove redundant whitespace
$tables = $dom->getElementsByTagName('table');
if(!is_object($tables)) {
  echo "<br />Invalid HTML Table DOM, nothing to Export.";
  exit;
}
if($debug) {
  fwrite($handle, "\nTable Count: ".$tables->length);
}
if($tables->length < 1) {
  echo "<br />DOM Table Count is ".$tables->length.", nothing to Export.";
  exit;
}
$tbcnt = $tables->length - 1;                 // count minus 1 for 0 indexed loop over tables
if($tbcnt > $limit) {
  $tbcnt = $limit;
}
//
//
// Create new PHPExcel object with default attributes
//
require_once ('Classes/PHPExcel.php');
$objPHPExcel = new PHPExcel();
$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
$objPHPExcel->getDefaultStyle()->getFont()->setSize(9);
//$tm = date(dmY);
$pos = strpos($usermail, "@");
$user = substr($usermail, 0, $pos);
$user = str_replace(".","",$user);
$db=new DBConn();
if (isset($_SESSION['storeid'])) {
    $storeid=$_SESSION['storeid'];
    if (is_numeric($storeid)) {
        $store=$db->fetchObject("select store_name from it_codes where id=$storeid");
        $storename=str_replace(" ", "-", $store->store_name);
    } else $storename ="allStores";
} else {
    $storename="allStores";
}
if (isset($_SESSION['account_dtrange']))
$date = str_replace(" - ", "->", $_SESSION['account_dtrange']);
else $date = date('d-m-Y');
$tfn = "DSCreditnote_".$storename."_".$date.".xls";
//$fname = "AuditLog/".$tfn;
$fname = $tfn;
//$username = getCurrUser();

$objPHPExcel->getProperties()->setCreator("CK")
                             ->setLastModifiedBy("CK")
                             ->setTitle("Automated Export")
                             ->setSubject("Automated Report Generation")
                             ->setDescription("Automated w3jam.com Report generation.")
                             ->setKeywords("Exported File")
                             ->setCompany($usercompany)
                             ->setCategory("Export");
//
// Loop over tables in DOM to create an array, each table becomes a worksheet
//
for($z=0;$z<=$tbcnt;$z++) {
  $maxcols = 0;
  $totrows = 0;
  $headrows = array();
  $bodyrows = array();
  $r = 0;
  $h = 0;
  $rows = $tables->item($z)->getElementsByTagName('tr');
  $totrows = $rows->length;
  if($debug) {
    fwrite($handle, "\nTotal Rows: ".$totrows);
  }
  //
  // Get TH values
  //
  foreach ($rows as $row) {
      $ths = $row->getElementsByTagName('th');
      if(is_object($ths)) {
        if($ths->length > 0) {
          $headrows[$h]['colcnt'] = $ths->length;
          if($ths->length > $maxcols) {
            $maxcols = $ths->length;
          }
          $nodes = $ths->length - 1;
          for($x=0;$x<=$nodes;$x++) {
            $thishdg = $ths->item($x)->nodeValue;
            $headrows[$h]['th'][] = $thishdg;
            $headrows[$h]['bold'][] = findBoldText(innerHTML($ths->item($x)));
            if($ths->item($x)->hasAttribute('style')) {
              $style = $ths->item($x)->getAttribute('style');
              $stylecolor = findStyleColor($style);
              if($stylecolor == '') {
                $headrows[$h]['color'][] = findSpanColor(innerHTML($ths->item($x)));
              }else{
                $headrows[$h]['color'][] = $stylecolor;
              }
            }else{
              $headrows[$h]['color'][] = findSpanColor(innerHTML($ths->item($x)));
            }
            if($ths->item($x)->hasAttribute('colspan')) {
              $headrows[$h]['colspan'][] = $ths->item($x)->getAttribute('colspan');
            }else{
              $headrows[$h]['colspan'][] = 1;
            }
            if($ths->item($x)->hasAttribute('align')) {
              $headrows[$h]['align'][] = $ths->item($x)->getAttribute('align');
            }else{
              $headrows[$h]['align'][] = 'left';
            }
            if($ths->item($x)->hasAttribute('valign')) {
              $headrows[$h]['valign'][] = $ths->item($x)->getAttribute('valign');
            }else{
              $headrows[$h]['valign'][] = 'top';
            }
            if($ths->item($x)->hasAttribute('bgcolor')) {
              $headrows[$h]['bgcolor'][] = str_replace("#", "", $ths->item($x)->getAttribute('bgcolor'));
            }else{
              $headrows[$h]['bgcolor'][] = 'FFFFFF';
            }
          }
          $h++;
        }
      }
  }
  //
  // Get TD values
  //
  foreach ($rows as $row) {
      $tds = $row->getElementsByTagName('td');
      if(is_object($tds)) {
        if($tds->length > 0) {
          $bodyrows[$r]['colcnt'] = $tds->length;
          if($tds->length > $maxcols) {
            $maxcols = $tds->length;
          }
          $nodes = $tds->length - 1;
          for($x=0;$x<=$nodes;$x++) {
            $thistxt = $tds->item($x)->nodeValue;
            $bodyrows[$r]['td'][] = $thistxt;
            $bodyrows[$r]['bold'][] = findBoldText(innerHTML($tds->item($x)));
            if($tds->item($x)->hasAttribute('style')) {
              $style = $tds->item($x)->getAttribute('style');
              $stylecolor = findStyleColor($style);
              if($stylecolor == '') {
                $bodyrows[$r]['color'][] = findSpanColor(innerHTML($tds->item($x)));
              }else{
                $bodyrows[$r]['color'][] = $stylecolor;
              }
            }else{
              $bodyrows[$r]['color'][] = findSpanColor(innerHTML($tds->item($x)));
            }
            if($tds->item($x)->hasAttribute('colspan')) {
              $bodyrows[$r]['colspan'][] = $tds->item($x)->getAttribute('colspan');
            } else {
              $bodyrows[$r]['colspan'][] = 1;
            }
            if($tds->item($x)->hasAttribute('align')) {
              $bodyrows[$r]['align'][] = $tds->item($x)->getAttribute('align');
            }else{
              $bodyrows[$r]['align'][] = 'left';
            }
            if($tds->item($x)->hasAttribute('valign')) {
              $bodyrows[$r]['valign'][] = $tds->item($x)->getAttribute('valign');
            }else{
              $bodyrows[$r]['valign'][] = 'top';
            }
            if($tds->item($x)->hasAttribute('bgcolor')) {
              $bodyrows[$r]['bgcolor'][] = str_replace("#", "", $tds->item($x)->getAttribute('bgcolor'));
            } else{
              $bodyrows[$r]['bgcolor'][] = 'FFFFFF';
            }
          }
          $r++;
        }
      }
  }
  if($z > 0) {
    $objPHPExcel->createSheet($z);
  }
  $suf = $z + 1;
  $tableid = $tablevar.$suf;
  $wksheetname = ucfirst($tableid);
  $objPHPExcel->setActiveSheetIndex($z);                      // each sheet corresponds to a table in html
  $objPHPExcel->getActiveSheet()->setTitle($wksheetname);     // tab name
  $worksheet = $objPHPExcel->getActiveSheet();                // set worksheet we're working on
  $style_overlay = array('font' =>
                    array('color' =>
                      array('rgb' => '000000'),'bold' => false,),
                          'fill' 	=>
                              array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'CCCCFF')),
                          'alignment' =>
                              array('wrap' => true, 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                         'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP),
                          'borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                             'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                             'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
                                             'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
                       );
  $xcol = '';
  $xrow = 1;
  $usedhdrows = 0;
  $heightvars = array(1=>'42', 2=>'42', 3=>'48', 4=>'52', 5=>'58', 6=>'64', 7=>'68', 8=>'76', 9=>'82');
  for($h=0;$h<count($headrows);$h++) {
    $th = $headrows[$h]['th'];
    $colspans = $headrows[$h]['colspan'];
    $aligns = $headrows[$h]['align'];
    $valigns = $headrows[$h]['valign'];
    $bgcolors = $headrows[$h]['bgcolor'];
    $colcnt = $headrows[$h]['colcnt'];
    $colors = $headrows[$h]['color'];
    $bolds = $headrows[$h]['bold'];
    $usedhdrows++;
    $mergedcells = false;
    for($t=0;$t<count($th);$t++) {
      if($xcol == '') {$xcol = 'A';}else{$xcol++;}
      $thishdg = $th[$t];
      $thisalign = $aligns[$t];
      $thisvalign = $valigns[$t];
      $thiscolspan = $colspans[$t];
      $thiscolor = $colors[$t];
      $thisbg = $bgcolors[$t];
      $thisbold = $bolds[$t];
      $strbold = ($thisbold==true) ? 'true' : 'false';
      if($thisbg == 'FFFFFF') {
        $style_overlay['fill']['type'] = PHPExcel_Style_Fill::FILL_NONE;
      }else{
        $style_overlay['fill']['type'] = PHPExcel_Style_Fill::FILL_SOLID;
      }
      $style_overlay['alignment']['vertical'] = $thisvalign;              // set styles for cell
      $style_overlay['alignment']['horizontal'] = $thisalign;
      $style_overlay['font']['color']['rgb'] = $thiscolor;
      $style_overlay['font']['bold'] = $thisbold;
      $style_overlay['fill']['color']['rgb'] = $thisbg;
      if($thiscolspan > 1) {                                                // spans more than 1 column
        $mergedcells = true;
        $lastxcol = $xcol;
        for($j=1;$j<$thiscolspan;$j++) {                                    // count to last column in span
          $lastxcol++;
        }
        $cellRange = $xcol.$xrow.':'.$lastxcol.$xrow;
        if($debug) {
          fwrite($handle, "\nmergeCells: ".$xcol.":".$xrow." ".$lastxcol.":".$xrow);
        }
        $worksheet->mergeCells($cellRange);                                // merge the columns
        $worksheet->setCellValue($xcol.$xrow, $thishdg);
        $worksheet->getStyle($cellRange)->applyFromArray($style_overlay);
        $worksheet->getStyle($cellRange)->getAlignment()->setWrapText(true);
        $num_newlines = substr_count($thishdg, "\n");                       // count number of newline chars
        if($num_newlines > 1) {
          $rowheight = $heightvars[1];                                      // default to 42
          if(array_key_exists($num_newlines, $heightvars)) {                // I couldn't find a PHPExcel method
            $rowheight = $heightvars[$num_newlines];                        // to do this, so I look to see how
          }else{                                                            // many newlines and just guess at
            $rowheight = 75;                                                // row height
          }
          $worksheet->getRowDimension($xrow)->setRowHeight($rowheight);     // adjust heading row height
          //$worksheet->getRowDimension($xrow)->setRowHeight(-1);           // this doesn't work in PHPExcel
        }
        if($debug) {
          fwrite($handle, "\n".$cellRange." ColSpan:".$thiscolspan." Color:".$thiscolor." Align:".$thisalign." VAlign:".$thisvalign." BGColor:".$thisbg." Bold:".$strbold." cellValue: ".$thishdg);
        }
        $xcol = $lastxcol;
      }else{
        $worksheet->setCellValue($xcol.$xrow, $thishdg);
        $worksheet->getStyle($xcol.$xrow)->applyFromArray($style_overlay);
        if($debug) {
          fwrite($handle, "\n".$xcol.":".$xrow." ColSpan:".$thiscolspan." Color:".$thiscolor." Align:".$thisalign." VAlign:".$thisvalign." BGColor:".$thisbg." Bold:".$strbold." cellValue: ".$thishdg);
        }
      }
    }
    $xrow++;
    $xcol = '';
  }
  //Put an auto filter on last row of heading only if last row was not merged
  if(!$mergedcells) {
    $worksheet->setAutoFilter("A$usedhdrows:" . $worksheet->getHighestColumn() . $worksheet->getHighestRow() );
  }
  if($debug) {
    fwrite($handle, "\nautoFilter: A".$usedhdrows.":".$worksheet->getHighestColumn().$worksheet->getHighestRow());
  }
  // Freeze heading lines starting after heading lines
  $usedhdrows++;
  $worksheet->freezePane("A$usedhdrows");
  if($debug) {
    fwrite($handle, "\nfreezePane: A".$usedhdrows);
  }
  //
  // Loop thru data rows and write them out
  //
  $xcol = '';
  $xrow = $usedhdrows;
  for($b=0;$b<count($bodyrows);$b++) {
    $td = $bodyrows[$b]['td'];
    $colcnt = $bodyrows[$b]['colcnt'];
    $colspans = $bodyrows[$b]['colspan'];
    $aligns = $bodyrows[$b]['align'];
    $valigns = $bodyrows[$b]['valign'];
    $bgcolors = $bodyrows[$b]['bgcolor'];
    $colors = $bodyrows[$b]['color'];
    $bolds = $bodyrows[$b]['bold'];
    for($t=0;$t<count($td);$t++) {
      if($xcol == '') {$xcol = 'A';}else{$xcol++;}
      $thistext = $td[$t];
      $thisalign = $aligns[$t];
      $thisvalign = $valigns[$t];
      $thiscolspan = $colspans[$t];
      $thiscolor = $colors[$t];
      $thisbg = $bgcolors[$t];
      $thisbold = $bolds[$t];
      $strbold = ($thisbold==true) ? 'true' : 'false';
      if($thisbg == 'FFFFFF') {
        $style_overlay['fill']['type'] = PHPExcel_Style_Fill::FILL_NONE;
      }else{
        $style_overlay['fill']['type'] = PHPExcel_Style_Fill::FILL_SOLID;
      }
      $style_overlay['alignment']['vertical'] = $thisvalign;              // set styles for cell
      $style_overlay['alignment']['horizontal'] = $thisalign;
      $style_overlay['font']['color']['rgb'] = $thiscolor;
      $style_overlay['font']['bold'] = $thisbold;
      $style_overlay['fill']['color']['rgb'] = $thisbg;
      if($thiscolspan > 1) {                                              // spans more than 1 column
        $lastxcol = $xcol;
        for($j=1;$j<$thiscolspan;$j++) {                                  // count spanned columns
          $lastxcol++;
        }
        $cellRange = $xcol.$xrow.':'.$lastxcol.$xrow;
        if($debug) {
          fwrite($handle, "\nmergeCells: ".$xcol.":".$xrow." ".$lastxcol.":".$xrow);
        }
        $worksheet->mergeCells($cellRange);                               // merge columns in span
        $worksheet->setCellValue($xcol.$xrow, $thistext);
        $worksheet->getStyle($cellRange)->applyFromArray($style_overlay);
        $worksheet->getStyle($cellRange)->getAlignment()->setWrapText(true);
        $num_newlines = substr_count($thistext, "\n");                       // count number of newline chars
        if($num_newlines > 1) {
          $rowheight = $heightvars[1];                                      // default to 42
          if(array_key_exists($num_newlines, $heightvars)) {                // I could not find a method in PHPExcel
            $rowheight = $heightvars[$num_newlines];                        // that would set row height automatically
          }else{                                                            // based on content, so I guess based
            $rowheight = 75;                                                // on number of newlines in the content
          }
          $worksheet->getRowDimension($xrow)->setRowHeight($rowheight);     // adjust heading row height
          //$worksheet->getRowDimension($xrow)->setRowHeight(-1);           // this doesn't work in PHPExcel
        }
        if($debug) {
          fwrite($handle, "\n".$cellRange." ColSpan:".$thiscolspan." Color:".$thiscolor." Align:".$thisalign." VAlign:".$thisvalign." BGColor:".$thisbg." Bold:".$strbold." cellValue: ".$thistext);
        }
        //$worksheet->getRowDimension($xrow)->setRowHeight(-1);
        $xcol = $lastxcol;
      }else{
        $worksheet->getColumnDimension($xcol)->setWidth(25);                // default width
        $worksheet->setCellValue($xcol.$xrow, $thistext);
        $worksheet->getStyle($xcol.$xrow)->applyFromArray($style_overlay);
        if($debug) {
          fwrite($handle, "\n".$xcol.":".$xrow." ColSpan:".$thiscolspan." Color:".$thiscolor." Align:".$thisalign." VAlign:".$thisvalign." BGColor:".$thisbg." Bold:".$strbold." cellValue: ".$thistext);
        }
      }
    }
    $xrow++;
    $xcol = '';
  }
  // autosize columns to fit data
  $azcol = 'A';
  for($x=1;$x==$maxcols;$x++) {
    $worksheet->getColumnDimension($azcol)->setAutoSize(true);
    $azcol++;
  }
  if($debug) {
    fwrite($handle, "\nHEADROWS: ".print_r($headrows, true));
    fwrite($handle, "\nBODYROWS: ".print_r($bodyrows, true));
  }
} // end for over tables
$objPHPExcel->setActiveSheetIndex(0);                      // set to first worksheet before close
//
// Write to Browser
//
if($debug) {
  fclose($handle);
}
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=$fname");
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
//$objWriter->save($fname);
$objWriter->save('php://output');
unset($_SESSION[$tablevar]);
exit;
function innerHTML($node) {
  $doc = $node->ownerDocument;
  $frag = $doc->createDocumentFragment();
  foreach ($node->childNodes as $child) {
    $frag->appendChild($child->cloneNode(TRUE));
  }
  return $doc->saveXML($frag);
}
function findSpanColor($node) {
  $pos = stripos($node, "color:");       // ie: looking for style='color: #FF0000;'
  if ($pos === false) {                  //                        12345678911111
    return '000000';                     //                                 01234
  }
  $node = substr($node, $pos);           // truncate to color: start
  $start = "#";                          // looking for html color string
  $end = ";";                            // should end with semicolon
  $node = " ".$node;                     // prefix node with blank
	$ini = stripos($node,$start);          // look for #
	if ($ini === false) return "000000";   // not found, return default color of black
	$ini += strlen($start);                // get 1 byte past start string
	$len = stripos($node,$end,$ini) - $ini; // grab substr between start and end positions
	return substr($node,$ini,$len);        // return the RGB color without # sign
}
function findStyleColor($style) {
  $pos = stripos($style, "color:");      // ie: looking for style='color: #FF0000;'
  if ($pos === false) {                  //                        12345678911111
    return '';                           //                                 01234
  }
  $style = substr($style, $pos);           // truncate to color: start
  $start = "#";                          // looking for html color string
  $end = ";";                            // should end with semicolon
  $style = " ".$style;                     // prefix node with blank
	$ini = stripos($style,$start);          // look for #
	if ($ini === false) return "";         // not found, return default color of black
	$ini += strlen($start);                // get 1 byte past start string
	$len = stripos($style,$end,$ini) - $ini; // grab substr between start and end positions
	return substr($style,$ini,$len);        // return the RGB color without # sign
}
function findBoldText($node) {
  $pos = stripos($node, "<b>");          // ie: looking for bolded text
  if ($pos === false) {                  //                        12345678911111
    return false;                        //                                 01234
  }
  return true;                           // found <b>
}
?>
