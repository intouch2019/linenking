<?php
require_once("../../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
//require_once "lib/grnPDFClass/GRN_PDF_Mail.php";
require('fpdf.php');

$img_path = '../../images/stock/';
$pdf = new FPDF();
$db = new DBConn();
$todays_date = date('Y-m-d');

$pdf->AddPage();
$all_items = array(
    (object) array(
        "ctg_name" => "Formal Shirts",
        "design_no" => "D123",
        "mrp" => "995",
        "image_name" => "2.2116.jpeg"
    ),
    (object) array(
        "ctg_name" => "Formal Shirts2",
        "design_no" => "D1232",
        "mrp" => "9952",
        "image_name" => "5.10.jpeg"
    )
);
foreach ($all_items as $item) {
    //  print_r($item);    
    $category_name = $item->ctg_name;
    $item_mrp = $item->mrp;
    $design_no = $item->design_no;
    $image = $item->image_name;

    $pdf->SetFont('Arial', 'B', 16);
    $pdf->ln();
    $pdf->ln();
//    $pdf->ln();
//    $pdf->ln();
    $pdf->Image($img_path . $image, $pdf->GetX() + 100, $pdf->GetY(), 40);
    // $pdf->ln();
    $pdf->Cell(40, 10, $category_name);
    $pdf->Ln();
//$pdf->Image($img_path.$image,$pdf->GetX(), $pdf->GetY(),50 ,60);
    // $pdf->Ln();
    $pdf->Cell(40, 10, $item_mrp);
    $pdf->Ln();
    $pdf->Cell(40, 10, $design_no);
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
}
//$pdf->Output();

$dir='pdf_files/';
$date = date('Y-m-d h::i::s');
$filename = $category_name."_".$date.".pdf";
$pdf_path = $db->safe($dir.$filename);

//Insert into new table it_grn_pdfs.
$query = "insert into it_grn_pdfs set pdf_file_path=$pdf_path,createtime=now()";
$pdf_id = $db->execInsert($query);


//Downlaod pdf file into pdf_files folder
$fname = $dir.$category_name."_".$date;
$pdf->Output($fname.'.pdf','F');
//echo $dir.$filename;


//$GRN_PDF_Mail = new GRN_PDF_Mail();
$ids = $GRN_PDF_Mail->sendMail($pdf_id);

//echo DEF_SITEURL.$redirect;

