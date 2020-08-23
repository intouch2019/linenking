<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'Classes/FPDF/fpdf.php';

extract($_POST);
$user = getCurrUser();
if ($user->usertype != UserType::Admin && $user->usertype != UserType::GoodsInward) {
    print "You are not authorized to add a User";
    return;
}

$errors = array();
$success = array();

class showPDF extends FPDF{
    
    function Header(){
        $this->SetFont('Arial','',10);
        $this->Image('../images/ck-logo.jpg',160,8,33); // x-160,y-8,size-33
        $this->Ln(5);
        $this->Cell(183,10,"King of 100% Pure Cotton Readymades",0,0,'R');
        $this->Ln();
    }

    function addPoType($objpo){
        $this->Cell(183,10,PoType::getName($objpo->potype),0,0,'C');
        $this->Ln();
        $this->Cell(0,0,"",1,0);
        $this->Ln();
    }
    
    function addSupplierInfo($objpo){
        $this->Cell(140,10,"Supplier Name : ".$objpo->supplier,0,0,'L');
        $this->Cell(60,10,"P.O.NO : ".$objpo->pono,0,0,'L');
        $this->Ln();
        $this->Cell(140,10,"Consignee Name : ".$objpo->consignee,0,0,'L');
        $this->Cell(60,10,"Date : ".$objpo->submitdate,0,0,'L');
        $this->Ln();
        $this->Cell(200,10,"Prepared & Approved By : KOUSHIK MARATHE",0,0,'L');
        $this->Ln();        
        $this->Cell(0,0,"",1,0);
        $this->Ln(5);
    }
    
    function addTableHeader(){
        $header = array('Sr.No', 'Design No', 'Supplier Design No', 'Qty', 'UOM', 'Rate', 'Value', 'Expected Date');        
        //Column widths
        $w = array(12, 20, 30, 15, 15, 15, 25, 35);
        //Header
        for ($i = 0; $i < count($header); $i++){
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C');
        }
    }
    
    function Footer(){
        $this->SetY(-25);
        $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
        $this->Ln();
        $this->Cell(200,10,'Cotton King Pvt. Ltd., H.O : Nal Stop, Off Karve Road, Pune - 411 004. Tel. : 020-25431266/67 www.cottonking.in',0,0,'C');
    }
    
    
}

try {
    $_SESSION['form_post'] = $_POST;
    $db = new DBConn();
    $poid = isset($poid) ? intval($poid) : false;
    if ($poid <= 0) {
        $errors['poid'] = "Not able to get PO number";
    }
    //$ckdesign = isset($ckdesign) && trim($ckdesign) != "" ? $db->safe($ckdesign) : false;
    $remarks = isset($remarks) && trim($remarks) != "" ? $db->safe($remarks) : false;
    //if(!$remarks) {  }
    $tnc = isset($tnc) && trim($tnc) != "" ? $db->safe($tnc) : false;
    
    if (count($errors) == 0) {
        $objpolines = $db->fetchObjectArray("select po.id as id, sup.designno as suppdesign, ck.designno as ck_design, po.qty as qty," .
                " po.uom as uom, po.rate as rate, po.expected_date as ex_date, it_itemtype.name as fabrictype," .
                " it_color.name as color, it_product.name as product, it_productiontype.name as productiontype from it_polines po," .
                " it_supplierdesign sup, it_ckdesign ck left outer join it_itemtype on ck.itemtype_id = it_itemtype.id left outer join" .
                " it_color on ck.color_id = it_color.id left outer join it_product on ck.product_id = it_product.id left outer join" .
                " it_productiontype on ck.productiontype_id = it_productiontype.id where po.suppdesign_id = sup.id and po.ckdesign = ck.id" .
                " and po.po_id = $poid");

        if($objpolines == null){
            $errors['nullPO'] = "PO cannot be publish. Please enter the items.";
        }
        
    }
    
    if (count($errors) == 0){
        $db->execUpdate("update it_purchaseorder set po_status=1, remarks=$remarks, tnc=$tnc, submittedtime=now() where id=$poid");
        
        $objpo = $db->fetchObject("select s.name as supplier, p.submittedtime as submitdate, p.supplier_id as supplier_id, p.potype as potype, p.pono as pono, p.consignee as consignee,p.remarks, p.tnc, u.name as preparedby, p.createtime from it_suppliers s, it_purchaseorder p, it_users u where s.id = p.supplier_id and u.id = p.preparedby_id and p.id=$poid");
        
//        $pdf = new FPDF();
        
        $spdf = new showPDF();
        $spdf->AddPage();
//        $spdf->Cell(0,0,"",1,0);
//        $spdf->Ln(5);
        $spdf->addPoType($objpo);
        $spdf->addSupplierInfo($objpo);
        $spdf->addTableHeader();
        $spdf->Ln();
        //Data
        $totalQty = 0;
        $totalValue = 0;
        $num = 1;
        $count = 0;
        foreach ($objpolines as $eachResult) {
            $spdf->Cell(12, 6, number_format($num), 1, 0, 'R');
            $spdf->Cell(20, 6, $eachResult->ck_design, 1);
            $spdf->Cell(30, 6, $eachResult->suppdesign, 1);
            $spdf->Cell(15, 6, number_format($eachResult->qty, 2), 1, 0, 'R');
            $spdf->Cell(15, 6, $eachResult->uom, 1, 0, 'R');
            $spdf->Cell(15, 6, number_format($eachResult->rate, 2), 1, 0, 'R');
                $q = $eachResult->qty;
                $r = $eachResult->rate;
                $value = $q * $r;
            $spdf->Cell(25, 6, number_format($value,2), 1, 0, 'R');
            $spdf->Cell(35, 6, $eachResult->ex_date, 1, 0, 'R');
            $spdf->Ln();
            $num = $num + 1;
            $count = $count + 1;
            $totalQty = $totalQty + $q;
            $totalValue = $totalValue + $value;
            if($count >= 10){
                $spdf->AddPage();
                $spdf->Cell(0,0,"",1,0);
                $spdf->Ln(5);
                $spdf->addTableHeader();
                $spdf->Ln();
                $count = 0;
            }
        }

        $spdf->Cell(12, 6, "Totals:", 1, 0, 'R');
        $spdf->Cell(20, 6, "", 1);
        $spdf->Cell(30, 6, "", 1);
        $spdf->Cell(15, 6, number_format($totalQty, 2), 1, 0, 'R');
        $spdf->Cell(15, 6, "", 1);
        $spdf->Cell(15, 6, "", 1);        
        $spdf->Cell(25, 6, number_format($totalValue,2), 1, 0, 'R');
        $spdf->Cell(35, 6, "", 1);        
        $spdf->Ln();
        //Closure line
        //$pdf->Cell(array_sum($w), 0, '', 'T');
        $spdf->Ln(5);
        $spdf->Cell(0,0,"",1,0);   
        $spdf->Ln(5);
        $remarks = $objpo->remarks;
        $rm = explode("\n", $remarks);
        $spdf->Cell(180,10,'Remarks',0,0,'L');
        $spdf->Ln();
        foreach($rm as $r){
            $spdf->Cell(180,10,$r,0,0,'L');
            $spdf->Ln();
        }
        $tnc = $objpo->tnc;
        $tn = explode("\n", $tnc);
        $spdf->Cell(180,10,'Terms and Conditions',0,0,'L');
        $spdf->Ln();
        foreach($tn as $t){
            $spdf->Cell(180,10,$t,0,0,'L');
            $spdf->Ln();
        }
        $spdf->Cell(0,0,"",1,0);           
        $spdf->Ln(5);        
        $spdf->Cell(180,10,"For Cotton King Pvt. Ltd",0,0,'R');
        $spdf->Ln();
        $spdf->Image('../images/signature.gif',145); // x-160,y-8,size-33
        $spdf->Ln();
        $spdf->Cell(180,10,"Authorised Signatory",0,0,'R');        
        $spdf->Ln();
        $filename = $objpo->pono;
        $spdf->Output("../pofiles/$filename.pdf","F");
        
    }
} catch (Exception $xcp) {
    $clsLogger = new clsLogger();
    $clsLogger->logError("Failed to do gate entry:" . $xcp->getMessage());
    $errors['status'] = "There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
    $redirect = "po/additems/id=$poid/";
} else {
    unset($_SESSION['form_errors']);
//        $_SESSION['form_success'] = $success;
    $redirect = "po/home/postatus=1";
}
session_write_close();
header("Location: " . DEF_SITEURL . $redirect);
exit;
?>
