<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once 'Classes/PHPExcel/IOFactory.php';

$dir = "../data/";
$errors = array();
$success = "";
$err = "";

$g_total_qty = 0;
$g_total_amt = 0;

if ($_FILES["file"]["error"] > 0) {
    $errors['err'] = "Error: " . $_FILES["file"]["error"] . "<br>";
} else {
    $db = new DBConn();
    $storeid = getCurrUserId();
    $date = date('Ymd_His');
    $textname = $_FILES['file']['name'];
    $ext = pathinfo($textname, PATHINFO_EXTENSION);
    $textnamediv = explode(".", $textname);
    if ($textnamediv[0]) {
        $name = $textnamediv[0];
    } else {
        $name = $textname;
    }
    $newname = $date . ".Order." . $storeid . "." . "$name" . ".$ext";
    $newdir = $dir . $newname;
//    print_r($newdir);    exit();
    if (move_uploaded_file($_FILES['file']['tmp_name'], $newdir)) {
        $success = "File is valid.<br/>";
        $err .= checkfile($newdir);

        if (trim($err) != "") {
            $errors['chkfile'] = $err;
        }

        if (count($errors) == 0) {
            $success .= createitems($newdir);             
        }

        $success .= "<br><br>Total Qty: $g_total_qty, Total Amount: $g_total_amt<br>";
    } else {
        $errors['file'] = "The file failed to upload";
    }
}

if (count($errors) > 0) {
    unset($_SESSION['form_success'], $_SESSION['fpath'], $_SESSION['orderplace']);
    $_SESSION['form_errors'] = $errors;
} else {
    unset($_SESSION['form_errors'], $_SESSION['orderplace']);
    $_SESSION['form_success'] = $success;
    $_SESSION['fpath'] = $newdir;
}

session_write_close();
header("Location: " . DEF_SITEURL . "admin/strordersmembership");
exit;

// --------------------------- Functions --------------------------- //

function checkfile($newdir) {
    $db = new DBConn();
    $objPHPExcel = PHPExcel_IOFactory::load($newdir);
    $objWorksheet = $objPHPExcel->getActiveSheet();

    $store_items = array();
    $fixed_barcode = "8900001609474";

    foreach ($objWorksheet->getRowIterator() as $rowIndex => $row) {
        if ($rowIndex === 1) continue; // Skip header row
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $cells = [];
        foreach ($cellIterator as $cell) {
            $cells[] = trim(strval($cell->getValue()));
        }
//        print_r($cells);exit();
        if (count($cells) >= 3) {
            $storename = $cells[0];
            $qty = intval($cells[2]);

            if (!isset($store_items[$storename])) {
                $store_items[$storename] = [];
            }

            if ($qty > 0) {
                $store_items[$storename][] = array('code' => $fixed_barcode, 'qty' => $qty);
            }
        }
    }

    $return = "";
    foreach ($store_items as $storename => $items) {
        $return .= checkForItems($storename, $items);
    }

    return $return;
}

function checkForItems($storename, $items) {
    $db = new DBConn();
    $store = $db->safe($storename);
    
    $storeinfo = $db->fetchObject("SELECT inactive, is_closed FROM it_codes WHERE id = $store");

    if (!$storeinfo) {
        return "ERROR: Store $store not found<br/>";
    }

    if ($storeinfo->inactive == 1 || $storeinfo->is_closed == 1) {
        return "";
    }

    $sum = 0;
    foreach ($items as $item) {
        $itemcode = $db->safe($item['code']);
        $qty = $item['qty'];
        
        $totalqty = $db->fetchObject("SELECT curr_qty FROM it_items WHERE barcode = $itemcode AND ctg_id = 65"); // check for membershipcategory
        
        if (!$totalqty) {
            return "<br/>ERROR: Only Membership category barcode order can be placed<br/>";
        }
        $sum += $totalqty->curr_qty;
        $sum = 100000; //dummy stock for membership barcode
    }

    if(empty($qty) || $qty==0){
        return "<br/>ERROR: Invalid Quantity for store id : $store<br/>";
    }
    if ($sum <= 0) {
        return "<br/>ERROR: No stock available for any of the items in your order - store: $store<br/>";
    }

    return "";
}

function createitems($newdir) {
    $db = new DBConn();
    $objPHPExcel = PHPExcel_IOFactory::load($newdir);
    $objWorksheet = $objPHPExcel->getActiveSheet();

    $store_items = array();
    $fixed_barcode = "8900001609474";

    foreach ($objWorksheet->getRowIterator() as $rowIndex => $row) {
        if ($rowIndex === 1) continue; // Skip header row
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        $cells = [];
        foreach ($cellIterator as $cell) {
            $cells[] = trim(strval($cell->getValue()));
        }

        if (count($cells) >= 3) {
            $storename = $cells[0];
            $qty = intval($cells[2]);

            if (!isset($store_items[$storename])) {
                $store_items[$storename] = [];
            }

            if ($qty > 0) {
                $store_items[$storename][] = array('code' => $fixed_barcode, 'qty' => $qty);
            }
        }
    }

    $return = "";
    foreach ($store_items as $storename => $items) {
        $return .= saveOrder($storename, $items);
    }

    return $return;
}

function saveOrder($store, $items) {
    global $g_total_qty, $g_total_amt;
    $totqty = 0;
    $totamt = 0;
    $msg = "";

    $db = new DBConn();
    $store = $db->safe($store);
    $storeinfo = $db->fetchObject("SELECT id,store_name, inactive, is_closed, store_number FROM it_codes WHERE id = $store");

    if (!$storeinfo) {
        return "ERROR: Store $store not found<br/>";
    }

    if ($storeinfo->inactive == 1 || $storeinfo->is_closed == 1) {
        return "";
    }

    $sum = 0;
    foreach ($items as $item) {
        $itemcode = $db->safe($item['code']);
        $totalqty = $db->fetchObject("SELECT curr_qty FROM it_items WHERE barcode = $itemcode");
        if (!$totalqty) continue;
        $sum += $totalqty->curr_qty;
        $sum = 100000; //dummy stock for membership barcode
    }

    if ($sum > 0) {
        $storeid = $storeinfo->id;
        $store_number = $storeinfo->store_number;

        if (!$store_number) {
            return "ERROR: Store number missing for store $store.<br/>";
        }

        $obj = $db->fetchObject("SELECT order_no FROM it_ck_orders WHERE store_id = $storeid ORDER BY id DESC LIMIT 1");
        $new_order_no = ($obj) ? intval(substr($obj->order_no, -3)) + 1 : 1;
        if ($new_order_no == 1000) {
            $new_order_no = 1;
        }

        $order_no = $db->safe(sprintf("MT%03d%03d", $store_number, $new_order_no));

        foreach ($items as $item) {
            $itemcode = $db->safe($item['code']);
            $orderqty = $item['qty'];

            $itemdbinfo = $db->fetchObject("SELECT i.id AS itemid, i.design_no, i.MRP, i.curr_qty, i.is_design_mrp_active FROM it_items i, it_ck_designs d WHERE i.barcode = $itemcode AND i.ctg_id = d.ctg_id AND i.design_no = d.design_no");
            if (!$itemdbinfo) continue;

            $design_no = $db->safe($itemdbinfo->design_no);
            $itemid = $itemdbinfo->itemid;
            
            $totqty += intval($orderqty);
            $totamt += intval($orderqty) * $itemdbinfo->MRP;
            
        }

        $g_total_qty += $totqty;
        $g_total_amt += $totamt;
        return "<br/>Order No: $order_no, Qty: $totqty, Amount: $totamt store: $storeinfo->store_name";
//        return "<br/>Should Order be placed for Order No: $order_no, Qty: $totqty, Amount: $totamt for store $storeinfo->store_name ?<br/>$msg<br/>";
    } else {
        return "ERROR: No stock available for any of the items in your order - store: $store<br/>";
    }
}
?>
