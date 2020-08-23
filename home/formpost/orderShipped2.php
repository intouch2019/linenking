<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";

$_SESSION['form_post'] = $_POST;
extract($_POST);

$errors=array();
$success=array();
try {
	$db = new DBConn();
        $dispatcherid = getCurrUserId();
	$invoice_no = $db->safe(getValue($invoice_no));
	if (!$invoice_no) { $errors['invoice_no'] = "Please enter the Invoice No"; }
	$shipped_qty = getValue($shipped_qty);
	if (!$shipped_qty) { $errors['shipped_qty'] = "Please enter the Shipped Quantity"; }
        else { $shipped_qty = intval($shipped_qty); }
	$cheque_amt = getValue($cheque_amt);
	if ($cheque_amt === false) { $errors['cheque_amt'] = "Please enter the Cheque Amount"; }
        else $cheque_amt = floatval($cheque_amt);
	$cheque_dtl = $db->safe(getValue($cheque_dtl));
	if (!$cheque_dtl) { $errors['cheque_dtl'] = "Please enter the Cheque Details"; }
	$transport_dtl = $db->safe(getValue($transport_dtl));
	if (!$transport_dtl) { $errors['transport_dtl'] = "Please enter the Transport Details"; }
	$picker_id = getValue($picker_id);
	if (!$picker_id) { $errors['picker_id'] = "Please select a Picker from the dropdown"; }
	else { $picker_id = intval($picker_id); }
        if (!$bank) { $errors['bank'] = "Please enter/select a bank"; }
	else { $bank = $db->safe($bank); }
        if (!$branch) { $errors['branch'] = "Please enter/select the branch"; }
	else { $branch = $db->safe($branch); }
	$remarks = $db->safe(getValue($remarks));
        
	if (count($errors) == 0) {
            $query = "insert into it_ck_extrashipment set shipped_time = now(), storeid=$store_id, dispatcher_id=$dispatcherid ";
            if ($invoice_no) { $query .= ", invoice_no=$invoice_no"; }
            if ($shipped_qty) { $query .= ", shipped_qty=$shipped_qty"; }
            if ($cheque_amt) { $query .= ", cheque_amt=$cheque_amt"; }
            if ($cheque_dtl) { $query .= ", cheque_dtl=$cheque_dtl"; }
            if ($transport_dtl) { $query .= ", transport_dtl=$transport_dtl"; }
            if ($remarks) { $query .= ", remark=$remarks"; }
            if ($picker_id) { $query .= ", picker_id=$picker_id"; }
            if ($bank) { $query .= ", bank=$bank"; }
            if ($branch) { $query .= ", branch=$branch"; }
            if (isset($credit_num) && $credit_num!='') {
                $credit_num = $db->safe($credit_num);
                $query.= ",credit_no=$credit_num ";
            }
            if (isset($credit_amt) && $credit_amt!='') {
                $query.= ",credit_amt = $credit_amt ";
            }

//print "$query<br />";
            $insertid = $db->execInsert($query);
//print "$query<br />";
            unset($_SESSION['form_post']);
            $store = $db->fetchObject("select * from it_codes where id=$store_id");
            if ($store && $store->phone) {
                    require_once "lib/sms/clsSMSServe.php";
                    $cls = new clsSMSServe();
                    $msg = "Dear $store->owner, Your payment is deposited. Invoice No:$invoice_no, Chq Amt:$cheque_amt, Chq No:$cheque_dtl, Ship:$transport_dtl. Thank You.";
                    $cls->saveSMSreply($store->phone, $msg);
                    if ($remarks) $cls->saveSMSreply($store->phone, "Message from CK: ".trim($remarks,"'"));
                    if ($store->phone2) {
                            $cls->saveSMSreply($store->phone2, $msg);
                            if ($remarks) $cls->saveSMSreply($store->phone2, "Message from CK: ".trim($remarks,"'"));
                    }
            }
            if ($store && ($store->email || $store->email2)) {
                    $pickgroup = $db->fetchObject("select p.*, c.store_name from it_ck_extrashipment p, it_codes c where p.id=$insertid and p.store_id = c.id");
                    $message = $db->safe(getMessage($pickgroup));
                    $subject = $db->safe("Your order [$pickgroup->order_nos] has been shipped");
                    if ($store->email) {
                            $db->execInsert("insert into it_emails set storeid=$pickgroup->storeid, emailaddress='$store->email', subject=$subject, body=$message");
                    }
                    if ($store->email2) {
                            $db->execInsert("insert into it_emails set storeid=$pickgroup->storeid, emailaddress='$store->email2', subject=$subject, body=$message");
                    }
            }
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to add $fullname:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "dispatch/addship";
} else {
	unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
	$redirect = "orders/shipped";	 
}
session_write_close();
header("Location: ".DEF_SITEURL.$redirect);
exit;

function getValue($str) {
if (isset($str) && trim($str) != "") { return trim($str); }
else { return false; }
}

function getMessage($pickgroup) {
$message = '<table border="0">';
$message .= "<tr>";
$message .= "<th colspan=2>$pickgroup->store_name</th>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Invoice No:</td><td>$pickgroup->invoice_no</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Shipped Qty:</td><td>$pickgroup->shipped_qty</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Cheque Amount:</td><td>$pickgroup->cheque_amt</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Cheque Detail:</td><td>$pickgroup->cheque_dtl</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Transport Detail:</td><td>$pickgroup->transport_dtl</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= '<td colspan=2 style="font-weight:bold;color:#ff0000;">Remarks:<br />'.$pickgroup->remark.'</td>';
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Shipped Date:</td><td>".mmddyy($pickgroup->shipped_time)."</td>";
$message .= "</tr>";
$message .= '</table>';
return $message;
}

?>
