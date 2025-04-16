<?php
//require_once("../../../it_config.php");
//require_once "session_check.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/grnPDFClass/GRN_PDF_Mail.php";
require "fpdf.php";



class GeneratePDF {

    function __construct() {
        
    }
    
    public function genUnreleasedPDF($item_ids_arr){
        $img_path = '../images/stock/';
        //$img_path = '/var/www/cottonking_new/home/images/stock/';
        $pdf = new FPDF();
        $db = new DBConn();
//        $todays_date = date('Y-m-d');

        $pdf->AddPage();       
        if(!empty($item_ids_arr)){
            $itemids = implode(",", $item_ids_arr);
            //$query = "select c.name as ctg_name, i.design_no, i.mrp, cd.image as image_name,cd.extension , cdp.cdesp from it_items i  left join it_grn_ctg_desp cdp on i.ctg_id = cdp.ctg_id and i.design_id = cdp.design_id, it_categories c , it_ck_designs cd where i.ctg_id = c.id and c.id = cd.ctg_id and i.design_id = cd.id and i.id in ( $itemids ) group by i.ctg_id,i.design_id,i.mrp ";

            $query = "SELECT c.name AS ctg_name, i.design_no, i.mrp, cd.image AS image_name, cdp.cdesp, i.ctg_id, i.prod_type_id, (SELECT SUM(curr_qty) FROM it_items WHERE ctg_id = i.ctg_id AND design_no = i.design_no) AS total_available FROM it_items i LEFT JOIN it_grn_ctg_desp cdp ON i.ctg_id = cdp.ctg_id AND i.design_id = cdp.design_id JOIN it_categories c ON i.ctg_id = c.id JOIN it_ck_designs cd ON i.ctg_id = cd.ctg_id AND i.design_id = cd.id WHERE i.id IN ($itemids) GROUP BY i.ctg_id, i.design_id, i.mrp ORDER BY c.sequence, c.id";
//            print $query;

            $all_items = $db->fetchObjectArray($query);
//            $db->closeConnection();
            $count=0;
            if(!empty($all_items)){
                 $icnt = 0;
                 $pdf->SetAutoPageBreak(true);
            foreach ($all_items as $item) {
//                  print_r($item); 
//                 $avl_query="select sum(curr_qty) as total_available from it_items where ctg_id= $item->ctg_id and design_no='".$item->design_no."'";
////                echo "<br>".$avl_query;//exit();
//                 $avl = $db->fetchObject($avl_query);
//                if(isset($avl) && $avl->total_available >25)
                  if (isset($item->total_available) && $item->total_available > 10){
                $count++;
            
                if($icnt > 2){
                    $icnt=0;
                     $pdf->AddPage();
//                     $y = 0;
                }
                    $icnt = $icnt + 1;
                $category_name = $item->ctg_name;
                $item_mrp = $item->mrp;
                $design_no = $item->design_no;
                $image = $item->image_name;
                
                
                $productionid = $item->prod_type_id;
                $productName="-";
                if(isset($productionid)){
                     $prodnameQuery="select name from it_prod_types where id=$productionid";
                     $prodName = $db->fetchObject($prodnameQuery);
                         if(isset($prodName)){
                     
                         $productName=$prodName->name;
                         
                         }
                }
                
                $pdf->SetFont('Arial', 'B', 16);
                $pdf->ln();
                $pdf->ln();
            //    $pdf->ln();
            //    $pdf->ln();
                if(file_exists($img_path.$image)){
                 $pdf->Image($img_path . $image, $pdf->GetX() + 0, $pdf->GetY(), 40);
                }
                // $pdf->ln();
                $pdf->cell(50);
                $pdf->Cell(40, 10, "Product Category: ".$category_name);
                $pdf->Ln();
            //$pdf->Image($img_path.$image,$pdf->GetX(), $pdf->GetY(),50 ,60);
                // $pdf->Ln();
                $pdf->cell(50);
                $pdf->Cell(40, 10, "MRP: ".$item_mrp);
                $pdf->Ln();
                $pdf->cell(50);
                $pdf->Cell(40, 10, "Design No.: ".$design_no);
                if(trim($item->cdesp)!=""){
                    $pdf->Ln();
                    $ctxt = "Category Description: ".$item->cdesp;
                    //$pdf->Cell(40, 10, "Category Description: ".$item->cdesp);
                    $pdf->CellFitScale(0,10,$ctxt,0,1,'',0);
                    $pdf->Ln();  
                }
                $pdf->Ln();
                $pdf->cell(50);
                $pdf->Cell(40, 10, "Production Type: ".$productName);
                $pdf->Ln();
                $pdf->Ln();
                $pdf->Ln();
//                $pdf->SetAutoPageBreak(true);
            }
            }
//            exit();
            //$pdf->Output();
            if($count>0)
                {
            $parent_dir = __DIR__;
            
            $dir=$parent_dir.'/pdf_files/';
            
//            $dir='pdf_files/';
            $date = date('YmdHis');
            $ctg_space_replace = str_replace(" ", "_", $category_name);
            //$filename = $ctg_space_replace."_".$date.".pdf";
            $filename = "Designs_Released_".$date.".pdf";
            $pdf_path = $db->safe($dir.$filename);

            //Insert into new table it_grn_pdfs.
            $query = "insert into it_grn_pdfs set pdf_file_path=$pdf_path,createtime=now()";
            $pdf_id = $db->execInsert($query);
//            $db->closeConnection();

            //Downlaod pdf file into pdf_files folder
            //$fname = $dir.$ctg_space_replace."_".$date;
            $fname = $dir."Designs_Released_".$date;
            $pdf->Output($fname.'.pdf','F');
            chmod($fname.'.pdf', 0777);
            //echo $dir.$filename;


            $GRN_PDF_Mail = new GRN_PDF_Mail();
            $ids = $GRN_PDF_Mail->sendMail();
            
            /*$date = date('Y-m-d h::i::s');
            $filename = $category_name."_".$date.".pdf";
            $pdf_path = $db->safe($dir.$filename);

            //Insert into new table it_grn_pdfs.
            $query = "insert into it_grn_pdfs set pdf_file_path=$pdf_path,createtime=now()";
            $pdf_id = $db->execInsert($query);


            //Downlaod pdf file into pdf_files folder
            $fname = $dir.$category_name."_".$date;
            $pdf->Output($fname.'.pdf','F');
            //echo $dir.$filename;


//            $GRN_PDF_Mail = new GRN_PDF_Mail();
//            $ids = $GRN_PDF_Mail->sendMail($pdf_id);
            //echo $dir.$filename;

            //$redirect = "lib/grnPDFClass/GRN/PDF/Mail/pdf_ids=$objs";
            //header("Location: ".DEF_SITEURL.$redirect);
            //exit;
             * */
            
        }
      }
    }
    $db->closeConnection();
    }
}