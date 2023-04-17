<?php

ini_set('max_execution_time', 60);
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once 'lib/users/clsUsers.php';
require_once 'lib/core/Constants.php';
require_once "lib/serverChanges/clsServerChanges.php";
require_once "lib/logger/clsLogger.php";

$logger = new clsLogger();

extract($_POST);
$oldinvoiceno = "";
$newinvoiceno = "";
//$role='';
$user = getCurrUser();
$db = new DBConn();
$serverCh = new clsServerChanges();
$userpage = new clsUsers();
$enpass = null;
$errors = array();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["oldinvoiceno"])) {
        $oldinvoiceno = "";
    } else {
        $oldinvoiceno = $_POST["oldinvoiceno"];
    }

    if (empty($_POST["newinvoiceno"])) {
        $newinvoiceno = "";
    } else {
        $newinvoiceno = $_POST["newinvoiceno"];
    }

    if (empty($_POST["store"])) {
        $errors["store"] = 'Store Not Selected';
    } else {
        $store_id = $_POST["store"];
    }
}

$pagecode = $db->safe($_SESSION['pagecode']);
$page = $db->fetchObject("select * from it_pages where pagecode = $pagecode");
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


$success = array();
try {
    $db = new DBConn();
    $currUser = getCurrUser();
    if (trim($oldinvoiceno) == "" || trim($newinvoiceno) == "") {
        $errors['version'] = 'Invoice number cannot be empty';
    } else {
        if ($_POST) {
            //check this invoice present on ck portal or not
            $records = $oldinvoiceno . '<>' . $newinvoiceno . '<>' . $store_id . '<>' . '|||||';
            $logger->logInfo("PushToCK:$records");
            //extract data from the post
            //set POST variables
            $url = 'http://cottonking.intouchrewards.com/obsync/updateInvReportsLK.php';
            //$url = 'http://192.168.0.15/ck_new_y/home/obsync/updateInvReportsLK.php';
            $fields = array('records' => urlencode($records));

            $fields_string = "";
            //url-ify the data for the POST
            foreach ($fields as $key => $value) {
                $fields_string .= $key . '=' . $value . '&';
            }
            rtrim($fields_string, '&');

            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

            //execute post
            $result = curl_exec($ch);
            $responseInfo = curl_getinfo($ch);
            //print $result;
            //close connection
            curl_close($ch);
            if ($result != "0::success") {
                $errors["invoice"] = "Invoice is not present on CK portal.Please contact intouch";
            } else {
                $checkqry = "select id,invoice_no,store_id from it_sp_invoices where invoice_no=$oldinvoiceno and store_id=$store_id";
                $chobjs = $db->fetchObject($checkqry);
                if (isset($chobjs)) {
                    //check into it_invoices_reports table for that invoice
                    $chkreports = "select id,invoice_no,store_id from it_invoices where invoice_no= $oldinvoiceno and store_id=$store_id";
                    $chkrepobj = $db->fetchObject($chkreports);
                    if (isset($chkrepobj)) {  //if present then update 
                        $rpqry = "update it_invoices set invoice_no='$newinvoiceno' where invoice_no='$oldinvoiceno' and id=$chkrepobj->id";
                        $updatereport = $db->execUpdate($rpqry);
                    }
                    // now update into it_invoices table    
                    $query = "update it_sp_invoices set invoice_no='$newinvoiceno' where invoice_no='$oldinvoiceno' and id=$chobjs->id";
                    $updated = $db->execUpdate($query);
                    $insertQry = "insert into it_update_invoices set invoice_id=$chobjs->id,oldinvno='$oldinvoiceno',newinvno='$newinvoiceno',store_id=$chobjs->store_id,updatetime=now()";
                    $inserted_id = $db->execInsert($insertQry);

                    if ($inserted_id) {            //insert into serverchanges
                        $qryserch = "select * from it_update_invoices where id = $inserted_id";
                        $invoiceupdate = $db->fetchObject($qryserch);
                        $st = "select store_type as storetype from it_codes where id = $store_id";
                        $getst = $db->fetchObject($st);
                        $json_obj = array();
                        $json_invoice = array();
                        $json_invoice['invoice_id'] = $invoiceupdate->invoice_id;
                        $json_invoice['oldinvno'] = $invoiceupdate->oldinvno;
                        $json_invoice['newinvno'] = $invoiceupdate->newinvno;

                        $server_ch = json_encode($json_invoice);
                        $ser_type = changeType::updateinvoice;
                        $store_id = $chobjs->store_id;
                        $serverCh->save($ser_type, $server_ch, $store_id, $inserted_id);
                        if ($getst->store_type == 2) {
                            $store_id = DEF_50CK_WAREHOUSE_ID;
                        } else {
                            $store_id = DEF_CK_WAREHOUSE_ID;
                        }
                        $serverCh->save($ser_type, $server_ch, $CKWH_id, $inserted_id);
                    } else {
                        $errors["invoice"] = "Problem while inserting record into server changes.";
                    }
                } else {
                    $errors["invoice"] = "wrong invoice number OR wrong store selection. Please Enter invoice number properly";
                }
                $success = 'Invoice ' . $oldinvoiceno . ' updated to' . $newinvoiceno;
            }
        }
    }
} catch (Exception $xcp) {
    $clsLogger = new clsLogger();
    $clsLogger->logError("Failed to update invoice:$userid:" . $xcp->getMessage());
    $errors['password'] = "There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
    $_SESSION['form_errors'] = $errors;
    $redirect = "admin/invoicenochange";
} else {
    unset($_SESSION['form_errors']);
    $_SESSION['form_success'] = $success;
    $redirect = "admin/invoicenochange";
}
session_write_close();
header("Location: " . DEF_SITEURL . $redirect);
exit;
?>