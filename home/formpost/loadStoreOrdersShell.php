<?php 
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'Classes/PHPExcel/IOFactory.php';
//require_once 'PHPExcel/IOFactory.php';
$commit=false;
$errors = array();
$success = "";
$err="";
$filename=$argv[0];

if(!isset($filename) && trim($filename) == ""){
    $errors['file'] = "File not found";
}else{
    $commit = true;
}
 if(count($errors)==0){
      $db = new DBConn();
             $success .= createitems($filename);
 }        
  if (count($errors)>0) {
        unset($_SESSION['form_success']);
        unset($_SESSION['fpath']);
        $_SESSION['form_errors'] = $errors;
  } else {
        unset($_SESSION['form_errors']);
        unset($_SESSION['fpath']);
        $_SESSION['form_success'] = $success;
        $_SESSION['orderplace']="done";
        
  }

exit;


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
        $value = strval($cell->getValue());
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
global $commit;
$totqty=0;
$totamt=0;
      $msg = "";
      $db = new DBConn();
      $store=$db->safe($store);
      $storeinfo = $db->fetchObject("select id,store_number from it_codes where code =$store");
      $qry = "select id,store_number from it_codes where code =$store";
      if (!$storeinfo) {
          $commit = false;
         return "ERROR:Store $store not found";
      } else {
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
                  $commit = false;
                  return "ERROR:Store number missing for store $store.";
              }
                $obj = $db->fetchObject("select order_no from it_ck_orders where store_id=$storeid order by id desc limit 1");
                $new_order_no = 1;
                if ($obj) {
                    $new_order_no = intval(substr($obj->order_no,-3)) + 1;
                }
                if ($new_order_no == 1000) { $new_order_no = 1; }
                $order_no = $db->safe(sprintf("AT%03d%03d",$store_number, $new_order_no));
                //echo "orderno".$order_no."\n";
                $query = "insert into it_ck_orders set store_id=$storeid, status=".OrderStatus::Active.", order_no=$order_no, order_qty=0";
		if ($commit) {
                $order_id=$db->execInsert("insert into it_ck_orders set store_id=$storeid, status=".OrderStatus::Active.", order_no=$order_no, order_qty=0");
		} else {
			$order_id="-1";
//			print "$query \n";
		}
                //echo "orderid".$order_id."\n";
                //
              foreach ($items as $item) {
                    $itemcode = $db->safe($item['code']);
                    $orderqty = $item['qty'];
                    $itemdbinfo= $db->fetchObject("select i.id as itemid,i.design_no,i.MRP,i.curr_qty,d.active from it_items i, it_ck_designs d where i.barcode=$itemcode and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.curr_qty > 0");
                    if (!$itemdbinfo) {
                          continue;
                    }
                    if (!$itemdbinfo->active) {
                          $msg .= "Design [print_r($itemdbinfo,true)] is inactive \n";
                          continue;
                    }
                    if ($itemdbinfo->curr_qty < $orderqty) {
                          $orderqty = $itemdbinfo->curr_qty;
                          $newstock = 0;
                    } else { 
                          $newstock = $itemdbinfo->curr_qty - $orderqty;
                    }
                    //update orderitems table with the orderqty 
                    //order_id / store_id / item_id / design_no / mrp / order_qty /order_no
                    $design_no = $db->safe($itemdbinfo->design_no);
                    $itemid = $itemdbinfo->itemid;
                    $query = "insert into it_ck_orderitems set order_id=$order_id,store_id=$storeid,item_id=$itemid, design_no = $design_no, MRP = $itemdbinfo->MRP, order_qty = $orderqty";
                    $totqty += intval($orderqty);
                    $totamt += intval($orderqty)*$itemdbinfo->MRP;
                    if ($commit) {
                        $orderitem_id = $db->execInsert($query);
                    } else {
                        $orderitem_id=-1;
//                        print "$query \n";
                    }
                    //remove orderqty from curr_qty. 
                    $query = "update it_items set curr_qty=$newstock where barcode=$itemcode";
                    if ($commit) {
                        $updateitem = $db->execUpdate($query);
                    } else {
//                        print "$query \n";
                    }
              }
          } else {
              return "ERROR:No stock available for any of the items in your order - store:$store";
          }
      }
    //get total summary from it_ck_orderitems to update it_ck_orders and it_ck_pickgroup.
    $summary = $db->fetchObject("select sum(order_qty) as tot_qty,sum(order_qty*MRP) as tot_sum,count(distinct(design_no)) as num_designs from it_ck_orderitems where order_id=$order_id and store_id=$storeid");  
    //update it_ck_orders
    $query = "update it_ck_orders set order_qty =$summary->tot_qty, order_amount=$summary->tot_sum,num_designs=$summary->num_designs,active_time=now() where id=$order_id";
if ($commit) {
    $updateord = $db->execUpdate("update it_ck_orders set order_qty =$summary->tot_qty, order_amount=$summary->tot_sum,num_designs=$summary->num_designs,active_time=now() where id=$order_id");
} else {
//    print "$query \n";
}
    //insert into it_ck_pickgroup
    $query = "insert into it_ck_pickgroup set storeid=$storeid, order_ids=$order_id,order_nos=$order_no, order_qty=$summary->tot_qty, order_amount=$summary->tot_sum,num_designs=$summary->num_designs,active_time=now()";
if ($commit) {
    $inspickgr = $db->execInsert("insert into it_ck_pickgroup set storeid=$storeid, order_ids=$order_id,order_nos=$order_no, order_qty=$summary->tot_qty, order_amount=$summary->tot_sum,num_designs=$summary->num_designs,active_time=now()");
} else {
//    print "$query \n";
}
    return "<br />Order $order_no placed for qty:$summary->tot_qty, $totqty, amount:$summary->tot_sum . <br />$msg<br />";
}
