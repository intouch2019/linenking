<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "Classes/html2pdf/html2pdf.class.php";
require_once 'lib/users/clsUsers.php';

$user = getCurrUser();
$db = new DBConn();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }
extract($_GET);
$db = new DBConn();
$order = $db->fetchObject("select cheque_dtl,cheque_amt from it_ck_pickgroup where id=$id");
$date = date("dmY");
$datearray = array();
while ($date!="") {
    $datearray[] = substr($date,0,1);
    $date = substr($date,1);
}
$words = no_to_words($order->cheque_amt)." Only";
$formattednum = no_to_formatnum($order->cheque_amt);

//5,11,5,8,596,110,580,15 -> left/top/right/bottom/dateleftpadding/wordsleftpadding/amountleftpadding/wordfontsize
//read margins from properties
$propobj = $db->fetchObject("select value from it_ck_properties where name = 'html2pdf.margins'");
$marstring = $propobj->value;
$allmargins = explode(",", $marstring);
$margins = array($allmargins[0],$allmargins[1],$allmargins[2],$allmargins[3]);
$datepadding = $allmargins[4];
$wordpadding = $allmargins[5];
$amountpadding = $allmargins[6];
$fontsize = $allmargins[7];
//creating the html to scale of cheque.
$table = "<table width='770px'><tr height='103px'><td width='596px' style='padding-left:".$datepadding."px;'> </td>";
//insert date
foreach ($datearray as $datedigits) {
    $table .= "<td width='23px' style='font-size:17px; padding-top:-3px; padding-bottom:71px; padding-right:4px; padding-left:4px;'>$datedigits</td>";
}
$table .= "<td width='11px' style='padding-left:10px;'></td></tr>";
$table .= "<tr height='33px'><td colspan='10' style='padding-bottom:10px; padding-left:".$wordpadding."px; font-size:".$fontsize."px;'>$words</td></tr>";
$table .= "<tr><td width='580px' style='padding-left:".$amountpadding."px;'> </td><td colspan='9' width='191px' style='font-size:19px;'>$formattednum</td></tr></table>";
//echo $table;

//writing the html to pdf format and saving.

$filename="$order->cheque_dtl-chequeprint";
$html2pdf = new HTML2PDF('P', 'A4', 'en',$unicode=true, $encoding='UTF-8', $margins);
$html2pdf->writeHTML($table);
$html2pdf->Output("../cheques/$filename.pdf", "F");

if (file_exists("../cheques/$filename.pdf")) {
    $db->execUpdate("update it_ck_pickgroup set cheque_print=1 where id=$id");
}
header('Content-Description: File Transfer');
header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="'.$filename.'.pdf"');
readfile("../cheques/$filename.pdf"); //<<< Note the " " surrounding the file name
header('Connection: Keep-Alive');
header('Expires: 0');


//header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
//header('Pragma: public');
//header('Content-Length: ' . filesize($file));


function no_to_formatnum($num) {
    $len = strlen($num);
    if ($len > 7) { 
        $split1 = substr($num,0,-7); // 
        $split2 = substr($num,-7);
        $num = $split1.",".$split2;
    }
    if ($len > 5) { 
        $split1 = substr($num,0,-5); // 
        $split2 = substr($num,-5);
        $num = $split1.",".$split2;
    }
    if ($len > 3) { 
        $split1 = substr($num,0,-3); // 
        $split2 = substr($num,-3);
        $num = $split1.",".$split2;
    }
    return "**".$num.".00 /-";
}
// function which converts numbers into words
function no_to_words($no)
{   
 $words = array('0'=> '' ,'1'=> 'One' ,'2'=> 'Two' ,'3' => 'Three','4' => 'Four','5' => 'Five','6' => 'Six','7' => 'Seven','8' => 'Eight','9' => 'Nine','10' => 'Ten','11' => 'Eleven','12' => 'Twelve','13' => 'Thirteen','14' => 'Fouteen','15' => 'Fifteen','16' => 'Sixteen','17' => 'Seventeen','18' => 'Eighteen','19' => 'Nineteen','20' => 'Twenty','30' => 'Thirty','40' => 'Fourty','50' => 'Fifty','60' => 'Sixty','70' => 'Seventy','80' => 'Eighty','90' => 'Ninty','100' => 'Hundred','1000' => 'Thousand','100000' => 'Lakh','10000000' => 'Crore');
    if($no == 0)
        return ' ';
    else {
	$novalue='';
	$highno=$no;
	$remainno=0;
	$value=100;
	$value1=1000;       
            while($no>=100)    {
                if(($value <= $no) &&($no  < $value1))    {
                $novalue=$words["$value"];
                $highno = (int)($no/$value);
                $remainno = $no % $value;
                break;
                }
                $value= $value1;
                $value1 = $value * 100;
            }       
          if(array_key_exists("$highno",$words))
              return $words["$highno"]." ".$novalue." ".no_to_words($remainno);
          else {
             $unit=$highno%10;
             $ten =(int)($highno/10)*10;            
             return $words["$ten"]." ".$words["$unit"]." ".$novalue." ".no_to_words($remainno);
           }
    }
}
?>
