<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'Classes/PHPExcel/IOFactory.php';

//step 1:- checkfile fn reads the file n sends store info to checkForItems fn
//step 2:- checkForItems fn checks for whether items curr_qty is available
//step 3:- If items available it sends to fn createItems
//step 4:- createItems fn sends to saveOrder fn which calc avail tot order qty n amt

$dir = "../data/";

$errors = array();
$success = "";
$err="";
if ($_FILES["file"]["error"] > 0)
  {
    $errors['err'] = "Error: " . $_FILES["file"]["error"] . "<br>";
  }
else {
      $db = new DBConn();
      $storeid = getCurrUserId();
      $date = date('Ymd_His');
      $textname = $_FILES['file']['name'];
      $ext = pathinfo($textname, PATHINFO_EXTENSION);
      $textnamediv = explode(".", $textname);
      if ($textnamediv[0]) {$name=$textnamediv[0]; } else {$name=$textname; }
      $newname = $date.".Order.".$storeid."."."$name".".$ext";
      $newdir = $dir.$newname;
$g_total_qty = 0;
$g_total_amt = 0;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $newdir)) {
            $success = "File is valid .<br/>";
            $err .= checkfile($newdir);
            if(trim($err)!=""){
                $errors['chkfile']= $err;
//                $errors[]= $err;
            }
            if(count($errors)==0){
             $success .= createitems($newdir);             
            }
$success.= "<br><br>Total Qty:$g_total_qty, Total Amount:$g_total_amt<br>";
        } else {
            $errors['file']= "The file failed to upload";
        }
  }
  if (count($errors)>0) {
        unset($_SESSION['form_success']);
        unset($_SESSION['fpath']);
        unset($_SESSION['orderplace']);
        $_SESSION['form_errors'] = $errors;
  } else {
        unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
        $_SESSION['fpath']=$newdir;
        unset($_SESSION['orderplace']);
  }

session_write_close();
header("Location: ".DEF_SITEURL."admin/strorders");
exit;

function checkfile($newdir){
    $db = new DBConn();
    $itemfound= "";$resp="";
    $objPHPExcel = PHPExcel_IOFactory::load($newdir);
    $objWorksheet = $objPHPExcel->getActiveSheet();
    $return=""; $first = 1; $code='';
    foreach ($objWorksheet->getRowIterator() as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $colno=0;
        foreach ($cellIterator as $cell) {
            $value = trim(strval($cell->getValue()));
            if ($colno==0 && !is_numeric($value)) {
                if ($first!=1) {
                    $return .= "\n".checkForItems($storename,$items);
                }
                $storename = $value;

                $items = array();
                $itmcnt=0;
                $first=2;
            } else {
                if ($colno==0) {
                    $code = $value;
                } else if (intval($value) > 0) {    
                    $items[$itmcnt] = array('code' => $code, 'qty'=>$value);
                    $itmcnt++;
                }
            }
            $colno++;
        }
    }
    $return = $return."\n".checkForItems($storename,$items);
    return $return;
}

function checkForItems($storename,$items){
      $totqty=0;
      $totamt=0;
      $msg = "";
      $db = new DBConn();
      $store=$db->safe($storename);
      $storeinfo = $db->fetchObject("select id,store_number,inactive,is_closed  from it_codes where code =$store");
//      $qry = "select id,store_number from it_codes where code =$store";
      if (!$storeinfo) {
         return "ERROR:Store $store not found";
      } else {
           if($storeinfo->inactive == 1){ //means store is disabled
//            return "<br> ERROR:Store $store is disabled so order against it cannot be placed ";  
              return;
          }
          if($storeinfo->is_closed == 1){ //means store is closed
//            return "<br> ERROR:Store $store is closed so order against it cannot be placed ";  
              return;
          }
          //check if at least 1 qty exist for the items.
          $sum = 0;
          foreach ($items as $item) {
              $itemcode = $db->safe($item['code']);
              //even inactive categories and designs are selected.              
              $totalqty = $db->fetchObject("select curr_qty from it_items where barcode=$itemcode and curr_qty > 0");
	      if (!$totalqty) { continue; }
              $sum += $totalqty->curr_qty;
          }
          if($sum <= 0){
            return "<br/> ERROR:No stock available for any of the items in your order - store:$store <br/> ";
          }
}
}


function createitems($newdir) {
$db = new DBConn();
$itemfound= "";
$objPHPExcel = PHPExcel_IOFactory::load($newdir);
$objWorksheet = $objPHPExcel->getActiveSheet();
$return=""; $first = 1; $code='';
foreach ($objWorksheet->getRowIterator() as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    $colno=0;
    foreach ($cellIterator as $cell) {
        $value = trim(strval($cell->getValue()));
        if ($colno==0 && !is_numeric($value)) {
            if ($first!=1) {
                $return .= "\n".saveOrder($storename,$items);
            }
            $storename = $value;
            
            $items = array();
            $itmcnt=0;
            $first=2;
        } else {
            if ($colno==0) {
                $code = $value;
            } else if (intval($value) > 0) {
                $items[$itmcnt] = array('code' => $code, 'qty'=>$value);
                $itmcnt++;
            }
        }
        $colno++;
    }
}
$return = $return."\n".saveOrder($storename,$items);
return $return;
}

function saveOrder($store,$items) {
global $g_total_qty, $g_total_amt;
      $totqty=0;
      $totamt=0;
      $msg = "";
      $db = new DBConn();
      $store=$db->safe($store);
      $storeinfo = $db->fetchObject("select id,store_number,inactive,is_closed  from it_codes where code =$store");
//      $qry = "select id,store_number from it_codes where code =$store";
      if (!$storeinfo) {
         return "ERROR:Store $store not found";
      } else {
           if($storeinfo->inactive == 1){ //means store is disabled
//            return "<br> ERROR:Store $store is disabled so order against it cannot be placed ";  
              return;
          }
          if($storeinfo->is_closed == 1){ //means store is closed
//            return "<br> ERROR:Store $store is closed so order against it cannot be placed ";  
              return;
          }
          //check if at least 1 qty exist for the items.
          $sum = 0;
          foreach ($items as $item) {
              $itemcode = $db->safe($item['code']);
              //even inactive categories and designs are selected.
              $totalqty = $db->fetchObject("select curr_qty from it_items where barcode=$itemcode and curr_qty > 0");
	      if (!$totalqty) { continue; }
              $sum += $totalqty->curr_qty;
          }
          //if at least 1 qty exist -> continue and  create a new order in it_ck_orders
          if ($sum>0) {
              $storeid = $storeinfo->id;
              $store_number = $storeinfo->store_number;
              //insert new order. 
              if (!$store_number) {
                  return "ERROR:Store number missing for store $store.";
              }
                $obj = $db->fetchObject("select order_no from it_ck_orders where store_id=$storeid order by id desc limit 1");
                $new_order_no = 1;
                if ($obj) {
                    $new_order_no = intval(substr($obj->order_no,-3)) + 1;
                }
                if ($new_order_no == 1000) { $new_order_no = 1; }
                $order_no = $db->safe(sprintf("AT%03d%03d",$store_number, $new_order_no));

              foreach ($items as $item) {
                    $itemcode = $db->safe($item['code']);
                    $orderqty = $item['qty'];
                    //$itemdbinfo= $db->fetchObject("select i.id as itemid,i.design_no,i.MRP,i.curr_qty,d.active from it_items i, it_ck_designs d where i.barcode=$itemcode and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.curr_qty > 0");
                    $itemdbinfo= $db->fetchObject("select i.id as itemid,i.design_no,i.MRP,i.curr_qty,i.is_design_mrp_active from it_items i, it_ck_designs d where i.barcode=$itemcode and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.curr_qty > 0");
                    if (!$itemdbinfo) {
                          continue;
                    }
//                    if (!$itemdbinfo->active) {
//unmesh                          $msg .= "Design [$itemdbinfo->design_no] is inactive \n";
//                          continue;
//                    }
                    if ($itemdbinfo->curr_qty < $orderqty) {
                          $orderqty = $itemdbinfo->curr_qty;
                          $newstock = 0;
                    } else { 
                          $newstock = $itemdbinfo->curr_qty - $orderqty;
                    }
                   
                    $design_no = $db->safe($itemdbinfo->design_no);
                    $itemid = $itemdbinfo->itemid;                   
                    $totqty += intval($orderqty);
                    $totamt += intval($orderqty)*$itemdbinfo->MRP;
              }
          } else {
              return "ERROR:No stock available for any of the items in your order - store:$store";
          }
      }
$g_total_qty += $totqty;
$g_total_amt += $totamt;
     return "<br />Should Order  be placed for Order No :-  $order_no , qty: $totqty, amount: $totamt. for store $store ? <br />$msg<br />";
}
?>