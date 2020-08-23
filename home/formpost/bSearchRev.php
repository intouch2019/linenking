<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "Classes/html2pdf/html2pdf.class.php";
require_once 'lib/users/clsUsers.php';

$size_name_change_categories = array (2, 3, 20);
$display_sizes = array(
"28" => "28 / 71",
"30" => "30 / 76",
"32" => "32 /  81",
"34" => "34 /  86",
"36" => "36 /  91",
"38" => "38 /  97",
"40" => "40 /  102",
"42" => "42  /  107",
"44" => "44  /  112"
);

extract($_POST);
$user = getCurrUser();

$db = new DBConn();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
if($page){
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) { header("Location: ".DEF_SITEURL."unauthorized"); return; }
}else{ header("Location:".DEF_SITEURL."nopagefound"); return; }


$errors=array();
$success=array();
$_SESSION['form_post'] = $_POST;
if (isset($_POST['submitSearch'])) {
if (isset($mrp) && trim($mrp) != "") {
$arr = explode(",",$mrp);
foreach ($arr as $val) {
if (!ctype_digit(strval($val))) {
	$errors['mrp']='Please enter a number for "MRP"';
	break;
}
}
$mrp_str = $mrp;
}

if (count($errors) == 0) {
	$_SESSION['form_post']['success']=true;
//} else {
//	$_SESSION['form_post']['success']=false;
}
} else
if (isset($_POST['submitPrint'])) {
	$_SESSION['form_post']['success']=true;
	if (!$pack_dt || trim($pack_dt) == "") { $pack_dt = strtoupper(date("M Y")); }
	try {
		$print_ids = array(); $total = 0;
		foreach ($_POST as $name=>$value) {
			if (!startsWith($name,"qty_")) { continue; }
			if (!$value || trim($value) == "") { continue; }
			if (!isNumber($value)) { $errors[$name] = "Incorrect Number '$value'"; continue; }
			$item_id = substr($name,4);
			$print_ids[$item_id] = intval($value);
			$total += intval($value);
		}
		if (count($errors) == 0 && count($print_ids) > 0) {
			$db = new DBConn();
			$length = 53*$total;
			$length = 53;
			$html = "";
			$html2pdf = new HTML2PDF('P', array(45,50), 'en', true, 'UTF-8', array(1,0,1,0));
			foreach ($print_ids as $item_id => $num_copies) {
				$item = $db->fetchObject("select i.id, i.ctg_id, i.barcode, i.num_units, m.name as mfg_by, c.name as category, i.design_no, i.MRP, b.name as brand, st.name as style, si.name as size, p.name as prod_type, mt.name as material, f.name as fabric_type from it_items i left outer join it_mfg_by m on i.mfg_id = m.id left outer join it_categories c on i.ctg_id = c.id left outer join it_brands b on i.brand_id = b.id left outer join it_styles st on i.style_id = st.id left outer join it_sizes si on i.size_id = si.id left outer join it_prod_types p on i.prod_type_id = p.id left outer join it_materials mt on i.material_id = mt.id left outer join it_fabric_types f on i.fabric_type_id = f.id where i.id=$item_id");
				for ($i=0; $i<$num_copies; $i++) {
/** Begin - change size names for trousers, jeans and narrow trousers */
$item_size = $item->size;
if (in_array($item->ctg_id, $size_name_change_categories)) {
	if (array_key_exists($item_size, $display_sizes)) {
		$item_size = $display_sizes[$item_size];
	}
}
/** End - change size names for trousers, jeans and narrow trousers */
$html .=
"<page>
<br />
<div style=\"width:155px;\">
<table width=\"100%\" style=\"font-size:9px;\">
<tr><th> </th><th> </th><th> </th><th> </th></tr>
<tr><td style=\"font-weight:bold;\" colspan=\"4\"></td></tr>
<tr><td style=\"font-weight:bold;\" colspan=\"4\"></td></tr>
<tr><td style=\"font-weight:bold;\" colspan=\"4\">Product: $item->category</td></tr>
<tr><td colspan=\"4\">Design no: $item->design_no&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Qty in Nos: $item->num_units</td></tr>
<tr><td style=\"font-weight:bold;\" colspan=\"4\">$item->prod_type</td></tr>
<tr style=\"font-weight:bold;\"><td colspan=\"4\" align=\"center\" style=\"font-size:11px;\">$item_size CM - $item->style</td></tr>
<tr><td align=\"center\" colspan=\"4\"><barcode type=\"EAN13\" value=\"$item->barcode\" label=\"label\" style=\"width:36mm; height:6mm; font-size: 2mm\"></barcode></td></tr>
<tr style=\"font-weight:bold;\"><td colspan=\"2\" style=\"font-size:14px;\">Rs.$item->MRP.00</td><td colspan=\"2\" style=\"font-size:8px;\">Maximum Retail Price<br />(Incl. All Taxes)</td></tr>
<tr><td colspan=\"4\" style=\"font-size:6px;\">Pack Dt :$pack_dt</td></tr>
</table>
</div>
</page>";
				}
			}
//<tr><td colspan=\"4\"><img src=\"http://localhost/limelight/home/barcode.php?code=11$item->barcode&encoding=EAN&scale=1&mode=png\" /></td></tr>
			$html2pdf->writeHTML($html);
			$fname = "barcodes/$total-barcodes.pdf";
			$html2pdf->Output("../$fname", "F");
			$db->closeConnection();
			$success="$total barcode(s) printed. <a target=\"_blank\" href=\"$fname\">DOWNLOAD</a>";
// We'll be outputting a PDF 
/*
header('Content-type: application/pdf'); 
header('Content-Disposition: attachment; filename="'.$total.'-barcodes.pdf"'); 
readfile('abc.pdf'); 
*/
		} else if (count($errors) == 0 && count($print_ids) == 0) {
			$errors['status'] = "Please enter the quantity to print";
		}
	} catch (Exception $xcp) {
		$clsLogger = new clsLogger();
		$clsLogger->logError("Failed to print barcodes:".$xcp->getMessage());
		$errors['status']="There was a problem processing your request. Please try again later";
	}
}

if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
} else {
	unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
}
session_write_close();
header("Location: ".DEF_SITEURL."barcode/search/revised");
exit;
