<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "Classes/html2pdf/html2pdf.class.php";
require_once 'lib/users/clsUsers.php';

extract($_POST);
$user = getCurrUser();
$user = getCurrUser();
$db = new DBConn();
$userpage = new clsUsers();
$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select pagecode from it_pages where pagecode = $pagecode");
if ($page) {
    $allowed = $userpage->isAuthorized($user->id, $page->pagecode);
    if (!$allowed) {
        header("Location: " . DEF_SITEURL . "unauthorized");
        return;
    }
} else {
    header("Location:" . DEF_SITEURL . "nopagefound");
    return;
}

$errors = array();
$success = array();
$_SESSION['form_post'] = $_POST;
//if (!$pack_dt || trim($pack_dt) == "") {
//    $pack_dt = strtoupper(date("M Y"));
//}

if (isset($material)) {
    $material = trim($material);
} else
    $material = false;
if ($material == "") {
    $material = false;
}
try {
    $print_ids = array();
    $total = 0;
    if (!$batch_id) {
        $errors['batch_id'] = "Missing Batch ID";
    }
    if (!$line) {
        $errors['line'] = "Missing BarcodeLine";
    }
    foreach ($_POST as $name => $value) {
        if (!startsWith($name, "qty_")) {
            continue;
        }
        if (!$value || trim($value) == "") {
            continue;
        }
        if (!isNumber($value)) {
            $errors[$name] = "Incorrect Number '$value'";
            continue;
        }
        $item_id = substr($name, 4);
        $print_ids[$item_id] = intval($value);
        $total += intval($value);
    }
    if ($line == 1) {

    if (count($errors) == 0 && count($print_ids) > 0) {
        $db = new DBConn();
        $html = "";
        $html2pdf = new HTML2PDF('P', array(45, 50), 'en', true, 'UTF-8', array(1, 0, 1, 0));
        foreach ($print_ids as $item_id => $num_copies) {
            $item = $db->fetchObject("select i.id, i.barcode, i.num_units, m.name as mfg_by, c.name as category,i.ctg_id, i.design_no, i.MRP, b.name as brand, st.name as style, si.name as size, p.name as prod_type, mt.name as material, f.name as fabric_type from it_items i left outer join it_mfg_by m on i.mfg_id = m.id left outer join it_categories c on i.ctg_id = c.id left outer join it_brands b on i.brand_id = b.id left outer join it_styles st on i.style_id = st.id left outer join it_sizes si on i.size_id = si.id left outer join it_prod_types p on i.prod_type_id = p.id left outer join it_materials mt on i.material_id = mt.id left outer join it_fabric_types f on i.fabric_type_id = f.id where i.id=$item_id");
            for ($i = 0; $i < $num_copies; $i++) {
                if (!$material)
                    $print_material = $item->material;
                else
                    $print_material = $material;
                if ($item->ctg_id == '24') { //ctg_id for SHIRT PIECE
                    $mtr = "/mtr";
                    $fontsz = "10px";
                } else {
                    $mtr = "";
                    $fontsz = "11px";
                }
                $cnt = 0;
                $cnt = strlen($item->category) + strlen($print_material);
                $br = "";
                if($cnt > 35){$br = "<br>";}
                
                $html .=
                        "<page>
<br />
<div style=\"width:155px;\">
<table width=\"100%\" style=\"font-size:9px;\">
<tr><th> </th><th> </th><th> </th><th> </th></tr>
<tr><td style=\"font-weight:bold;\" colspan=\"4\"></td></tr>
<tr><td style=\"font-weight:bold;font-size:7px;\" colspan=\"4\">Product: $item->category&nbsp;&nbsp;&nbsp;$br$print_material</td></tr>
<tr><td colspan=\"4\" style=\"font-size:8px;\">Design no: $item->design_no&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Qty in Nos: $item->num_units</td></tr>.";
                if ($item->size == "45cm X 45cm") {
                        $html .= "<tr style=\"font-weight:bold;\"><td colspan=\"4\" align=\"center\" style=\"font-size:10px;\"><span style=\"font-size:8px;\"></span> SIZE-$item->size  - $item->style</td></tr>.";
                    } else {
                        $html .= "<tr style=\"font-weight:bold;\"><td colspan=\"4\" align=\"center\" style=\"font-size:10px;\"><span style=\"font-size:8px;\">TO FIT</span> SIZE-$item->size cm - $item->style</td></tr>.";
                    }
                    $html.="<tr><td align=\"center\" colspan=\"4\"><barcode type=\"EAN13\" value=\"$item->barcode\" label=\"label\" style=\"width:36mm; height:6mm; font-size: 2mm\"></barcode></td></tr>
<tr style=\"font-weight:bold;\"><td colspan=\"2\" style=\"font-size:$fontsz;\">Rs.$item->MRP.00$mtr</td><td colspan=\"2\" style=\"font-size:5px;\">Maximum Retail Price<br />(Incl. All Taxes)</td></tr>

</table>
</div>
</page>";
//<tr><td colspan=\"4\" style=\"font-size:6px;\">Pack Dt :$pack_dt</td></tr>
            }
        }
        $html2pdf->writeHTML($html);
        $fname = "barcodes/$total-barcodes.pdf";
        $html2pdf->Output("../$fname", "F");
        $db->closeConnection();
        $success = "$total barcode(s) printed. <a target=\"_blank\" href=\"$fname\">DOWNLOAD</a>";
// We'll be outputting a PDF 
    } else if (count($errors) == 0 && count($print_ids) == 0) {
        $errors['status'] = "Please enter the quantity to print";
    }
 }
 
 else if ($line == 2) {

    if (count($errors) == 0 && count($print_ids) > 0) {
        $db = new DBConn();
        $html = "";
        $html2pdf = new HTML2PDF('L', array(93, 50), 'en', true, 'UTF-8', array(1, 0, 1, 0));
        foreach ($print_ids as $item_id => $num_copies) {
            $item = $db->fetchObject("select i.id, i.barcode, i.num_units, m.name as mfg_by, c.name as category,i.ctg_id, i.design_no, i.MRP, b.name as brand, st.name as style, si.name as size, p.name as prod_type, mt.name as material, f.name as fabric_type from it_items i left outer join it_mfg_by m on i.mfg_id = m.id left outer join it_categories c on i.ctg_id = c.id left outer join it_brands b on i.brand_id = b.id left outer join it_styles st on i.style_id = st.id left outer join it_sizes si on i.size_id = si.id left outer join it_prod_types p on i.prod_type_id = p.id left outer join it_materials mt on i.material_id = mt.id left outer join it_fabric_types f on i.fabric_type_id = f.id where i.id=$item_id");
            for ($i = 0; $i < $num_copies; $i++) {
                if (!$material)
                    $print_material = $item->material;
                else
                    $print_material = $material;
                if ($item->ctg_id == '24') { //ctg_id for SHIRT PIECE
                    $mtr = "/mtr";
                    $fontsz = "10px";
                } else {
                    $mtr = "";
                    $fontsz = "11px";
                }
                
                $cnt = 0;
                $cnt = strlen($item->category) + strlen($print_material);
                $br = "";
                if($cnt > 35){$br = "<br>";}
                
                $html .=
                        "<page>
    
    <div style=\"width:160px;\">
    <table><tr>
        <td width=\"30%\"><br />
            <div style=\"width:75px;\">
                <table width=\"100%\" style=\"font-size:9px;margin-top:6px;\">
                <tr><th> </th><th> </th><th> </th><th> </th></tr>
                <tr><td style=\"font-weight:bold;font-size:7px;\" colspan=\"4\">Product: $item->category&nbsp;&nbsp;&nbsp;$br$print_material</td></tr>
                <tr><td colspan=\"4\" style=\"font-size:8px;\">Design no: $item->design_no&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Qty in Nos: $item->num_units</td></tr>.";
                if($item->size == "45cm X 45cm"){
                    $html.="<tr style=\"font-weight:bold;\"><td colspan=\"4\" align=\"center\" style=\"font-size:10px;\"><span style=\"font-size:8px;\"></span> SIZE-$item->size  - $item->style</td></tr>.";
                }else{
                    $html.="<tr style=\"font-weight:bold;\"><td colspan=\"4\" align=\"center\" style=\"font-size:10px;\"><span style=\"font-size:8px;\">TO FIT</span> SIZE-$item->size cm - $item->style</td></tr>.";
                }
                
                    
                $html.="<tr><td align=\"center\" colspan=\"4\"><barcode type=\"EAN13\" value=\"$item->barcode\" label=\"label\" style=\"width:36mm; height:6mm; font-size: 2mm;\"></barcode></td></tr>
                <tr style=\"font-weight:bold;\"><td colspan=\"2\" style=\"font-size:$fontsz;\">Rs.$item->MRP.00$mtr</td><td colspan=\"2\" style=\"font-size:6px;\">Maximum Retail Price<br />(Incl. All Taxes)</td></tr>
                
                </table>
            </div>
        </td>
        <td width=\"20%\">
        </td>
        <td width=\"10%\"><div style=\"width:10px;\"><table width=\"10%\" style=\"font-size:9px;margin-top:6px;\"> </table></div></td>
        <td width=\"30%\"><br />
            <div style=\"width:75px;\">
                <table width=\"100%\" style=\"font-size:9px;margin-top:6px;\">
                <tr><th> </th><th> </th><th> </th><th> </th></tr>
                <tr><td style=\"font-weight:bold;font-size:7px;\" colspan=\"4\">Product: $item->category&nbsp;&nbsp;&nbsp;$br$print_material</td></tr>
                <tr><td colspan=\"4\" style=\"font-size:8px;\">Design no: $item->design_no&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Qty in Nos: $item->num_units</td></tr>.";
                if($item->size == "45cm X 45cm"){
                    $html.="<tr style=\"font-weight:bold;\"><td colspan=\"4\" align=\"center\" style=\"font-size:10px;\"><span style=\"font-size:8px;\"></span> SIZE-$item->size  - $item->style</td></tr>.";
                }else{
                    $html.="<tr style=\"font-weight:bold;\"><td colspan=\"4\" align=\"center\" style=\"font-size:10px;\"><span style=\"font-size:8px;\">TO FIT</span> SIZE-$item->size cm - $item->style</td></tr>.";
                }
                $html.="<tr><td align=\"center\" colspan=\"4\"><barcode type=\"EAN13\" value=\"$item->barcode\" label=\"label\" style=\"width:36mm; height:6mm; font-size: 2mm;\"></barcode></td></tr>
                <tr style=\"font-weight:bold;\"><td colspan=\"2\" style=\"font-size:$fontsz;\">Rs.$item->MRP.00$mtr</td><td colspan=\"2\" style=\"font-size:6px;\">Maximum Retail Price<br />(Incl. All Taxes)</td></tr>
                
                </table>
           </div>
        </td></tr>
    </table>
    </div>
</page>";
//<tr><td colspan=\"4\" style=\"font-size:7px;\">Pack Dt :$pack_dt</td></tr>
            }
        }
        $total=$total*2;
        $html2pdf->writeHTML($html);
        $fname = "barcodes/$total-barcodes.pdf";
        $html2pdf->Output("../$fname", "F");
        $db->closeConnection();
        $success = "$total barcode(s) printed. <a target=\"_blank\" href=\"$fname\">DOWNLOAD</a>";
// We'll be outputting a PDF 
    } else if (count($errors) == 0 && count($print_ids) == 0) {
        $errors['status'] = "Please enter the quantity to print";
    }
 }
 
} catch (Exception $xcp) {
    $clsLogger = new clsLogger();
    $clsLogger->logError("Failed to print barcodes:" . $xcp->getMessage());
    $errors['status'] = "There was a problem processing your request. Please try again later";
}

if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
} else {
    unset($_SESSION['form_errors']);
    $_SESSION['form_success'] = $success;
}


session_write_close();
$location = DEF_SITEURL . "barcode/batch/id=" . $batch_id . "/";
//print "Location=$location<br />";
header("Location: $location");
exit;
