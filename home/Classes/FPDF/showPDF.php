<html>
<head>
<title></title>
</head>
<body>
<?php

require_once("../../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

extract($_GET);
$user = getCurrUser();
if ($user->usertype != UserType::Admin && $user->usertype != UserType::CKAdmin) { print "You are not authorized to add a User"; return; }

$errors=array();
$success=array();

require ('fpdf.php');

class showPDF extends FPDF{

//Load data
function LoadData($file)
{
	//Read file lines
	$lines=file($file);
	$data=array();
	foreach($lines as $line)
		$data[]=explode(';',chop($line));
	return $data;
}    
    
//Better table
function ImprovedTable($header,$data)
{
	//Column widths
	$w=array(20,30,55,25,25,25,25,25);
	//Header
	for($i=0;$i<count($header);$i++)
		$this->Cell($w[$i],7,$header[$i],1,0,'C');
	$this->Ln();
	//Data

	foreach ($data as $eachResult) 
	{
		$this->Cell(20,6,1,1);
		$this->Cell(30,6,$eachResult->ck_design,1);
		$this->Cell(55,6,$eachResult->suppdesign,1);
		//$this->Cell(25,6,$eachResult["Qty"],1,0,'C');
                $this->Cell(25,6,number_format($eachResult->qty,2),1,0,'R');
		$this->Cell(25,6,$eachResult->uom,1,0,'R');
		$this->Cell(25,6,number_format($eachResult->rate,2),1,0,'R');
                $this->Cell(25,6,number_format(2,2),1,0,'R');
                $this->Cell(25,6,$eachResult->ex_date,1,0,'R');                
		$this->Ln();
	}

        //Closure line
	$this->Cell(array_sum($w),0,'','T');
}
}    


$db = new DBConn();
$poId = isset($poId) ? intval($poId) : false;
if($poId <= 0) { $errors['poId'] = "Not able to get PO";  }


$pdf=new showPDF();
//Column titles
$header=array('Sr.No','Dsign No','Supplier Design No','Qty','UOM','Rate','Value','Expected Date');
//Data loading

//*** Load MySQL Data ***//
$objpolines = $db->fetchObjectArray("select po.id as id, sup.designno as suppdesign, ck.designno as ck_design, po.qty as qty,".
" po.uom as uom, po.rate as rate, po.expected_date as ex_date, it_itemtype.name as fabrictype,".
" it_color.name as color, it_product.name as product, it_productiontype.name as productiontype from it_polines po,".
" it_supplierdesign sup, it_ckdesign ck left outer join it_itemtype on ck.itemtype_id = it_itemtype.id left outer join".
" it_color on ck.color_id = it_color.id left outer join it_product on ck.product_id = it_product.id left outer join".
" it_productiontype on ck.productiontype_id = it_productiontype.id where po.suppdesign_id = sup.id and po.ckdesign = ck.id".
" and po.po_id = $poId");



//************************//
$pdf->SetFont('Arial','',10);

//*** Table 2 ***//
$pdf->AddPage();
//$pdf->Image('logo.gif',80,8,33);
$pdf->Ln(35);
$pdf->ImprovedTable($header,$objpolines);

    $pdf->Output("mypdf.pdf","F");
?>
</body>
</html>