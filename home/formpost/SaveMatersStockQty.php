<?php

require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";

extract($_POST);  // Extract the POST data

/*
  Eg- Post Data
  Array
  (
  [curruser] => 1
  [min_qty] => 50
  [max_qty] => 100
  [style_3size_5] => 10
  [style_3size_6] => 10
  [style_3size_7] => 10
  [style_3size_8] => 10
  [style_3size_9] => 10
  [style_3size_10] => 10
  [style_3size_3] => 0
  [style_3size_2] => 0
  [cat_id] => 20
  [store_id] => 442
  )
 */

//echo '<pre>';
//print_r($_POST);
//echo '<pre>';
//exit();
$db = new DBConn();
$errors = []; // Initialize error array
$success = ""; // Flag for success
// Validation 1 starts - if size and style wise quantity matches between min and max master qty
$size_and_style_wise_sum = 0;
foreach ($_POST as $key => $value) {
    try {
        if (preg_match('/style_(\d+)size_(\d+)/', $key, $matches)) {
            $size_and_style_wise_sum += $value;
        }
    } catch (Exception $ex) {
        $errors['status'] = "Error for store $store_id, category $cat_id:" . $ex->getMessage();
    }
}
// Validation 1 ends


if ($size_and_style_wise_sum < $min_qty) {
    $errors['status'] = "Style and size wise sum is less than minimum required quantity(Master)";
} elseif ($size_and_style_wise_sum > $max_qty) {
    $errors['status'] = "Style and size wise sum is greater than maximum allowed quantity(Master)";
}


if($min_qty>=$max_qty){
    $errors['status'] = "Minimum quantity cannot be greater or equal to maximum quantity";
}


if (count($errors) == 0) {
    $check_sql2 = "select id from stock_limit_ctg_wise where store_id=$store_id and category_id = $cat_id";
    $stmt_check2 = $db->fetchObject($check_sql2);

    if (!empty($stmt_check2)) {
        $update_sql2 = "update stock_limit_ctg_wise set min_qty_ctg_wise=$min_qty, "
                . "max_qty_ctg_wise=$max_qty, last_modified_by=$curruser, update_time = CURRENT_TIMESTAMP where "
                . "store_id=$store_id and category_id = $cat_id and id=$stmt_check2->id";
        $stmt_update2 = $db->execUpdate($update_sql2);
    } else {
        $insert_sql2 = "INSERT INTO stock_limit_ctg_wise(store_id, category_id,  min_qty_ctg_wise, max_qty_ctg_wise, "
                . "last_modified_by) VALUES ($store_id, $cat_id,  $min_qty, $max_qty, $curruser)";
        $stmt_insert2 = $db->execInsert($insert_sql2);
    }

    // Process each key in the POST array
    foreach ($_POST as $key => $value) {
        try {
            // Match the pattern store_<store_id>cat_<cat_id>style_<style_id>size_<size_id>
            if (preg_match('/style_(\d+)size_(\d+)/', $key, $matches)) {
                // Extract the store_id, cat_id, style_id, size_id from the regular expression matches
                $style_id = $matches[1];
                $size_id = $matches[2];

                // Sanitize inputs (basic sanitization)
                $style_id = (int) $style_id;
                $size_id = (int) $size_id;
                $value = (int) $value;

                // Check if the record already exists
                $check_sql = "SELECT id FROM stock_master_qty_wise 
                          WHERE store_id = $store_id AND category_id = $cat_id AND style_id = $style_id AND size_id = $size_id";
                $stmt_check = $db->fetchObject($check_sql);

                // Check if master stock quantity already exist in stock_master_qty_wise and stock_limit_ctg_wise. If exist then update otherwise insert.
                if (!empty($stmt_check)) {
                    // If record exists, update it
                    $update_sql = "UPDATE stock_master_qty_wise 
                               SET min_qty_allowed = $value, update_time = CURRENT_TIMESTAMP,last_modified_by=$curruser 
                               WHERE store_id = $store_id AND category_id = $cat_id AND style_id = $style_id AND size_id = $size_id and id=$stmt_check->id";
                    $stmt_update = $db->execUpdate($update_sql);
                    if ($stmt_update) {
                        $success = "Stock master quantity updated successfully!";
                        echo "Stock master quantity updated successfully!";
                    }
                } else {
                    // If record doesn't exist, insert a new record
                    $insert_sql = "INSERT INTO stock_master_qty_wise (store_id, category_id, style_id, size_id, min_qty_allowed,last_modified_by) "
                            . "VALUES ($store_id, $cat_id, $style_id, $size_id, $value, $curruser)";
                    $stmt_insert = $db->execInsert($insert_sql);

                    if ($stmt_insert) {
                        $success = "Stock master quantity inserted successfully!";
                        echo "Stock master quantity inserted successfully!";
                    } else {
                        throw new Exception("Error inserting stock quantity.");
                    }
                }
            }
        } catch (Exception $e) {
            // Handle any errors or exceptions
            $errors['status'] = "Error for store $store_id, category $cat_id, style $style_id, size $size_id: " . $e->getMessage();
        }
    }
}
// After processing the data, handle success or errors
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
    $redirect = "admin/master/stock/qty/storeid=$store_id/catid=$cat_id";
} else {
    unset($_SESSION['form_errors']);
    $_SESSION['form_success'] = $success;
    $redirect = "admin/master/stock/qty/storeid=$store_id/catid=$cat_id";
}

// Close session and redirect
session_write_close();
header("Location: " . DEF_SITEURL . "$redirect");
exit;
