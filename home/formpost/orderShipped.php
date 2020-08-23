<?php
require_once("../../it_config.php");
require_once("session_check.php");
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "lib/serverChanges/clsServerChanges.php";

extract($_POST);

$errors=array();
$success=array();
try {
	$_SESSION['form_post'] = $_POST;
	$db = new DBConn();
        $serverCh = new clsServerChanges();
	$pickgroup_id = getValue($pickgroup_id);
	if (!$pickgroup_id) { $errors['pickgroup_id'] = "Pickgroup ID Missing. Please contact Administrator"; }
//	$invoice_no = $db->safe(getValue($invoice_no));
//	if (!$invoice_no) { $errors['invoice_no'] = "Please enter the Invoice No"; }
//	$shipped_qty = getValue($shipped_qty);
//	if (!$shipped_qty) { $errors['shipped_qty'] = "Please enter the Shipped Quantity"; }
//        else { $shipped_qty = intval($shipped_qty); }
//	$shipped_mrp = getValue($shipped_mrp);
//	if ($shipped_mrp === false) { $errors['shipped_mrp'] = "Please enter the Shipped MRP"; }
//        else $shipped_mrp = floatval($shipped_mrp);
//	$cheque_amt = getValue($cheque_amt);
//	if ($cheque_amt === false) { $errors['cheque_amt'] = "Please enter the Cheque Amount"; }
//        else $cheque_amt = floatval($cheque_amt);
//	$cheque_dtl = $db->safe(getValue($cheque_dtl));
//	if (!$cheque_dtl) { $errors['cheque_dtl'] = "Please enter the Cheque Details"; }
//	$transport_dtl = $db->safe(getValue($transport_dtl));
//	if (!$transport_dtl) { $errors['transport_dtl'] = "Please enter the Transport Details"; }
	$picker_id = getValue($picker_id);
	if (!$picker_id) { $errors['picker_id'] = "Please select a Picker from the dropdown"; }
	else { $picker_id = intval($picker_id); }
//	$remarks = $db->safe(getValue($remarks));

	if (count($errors) == 0) {
		$pickgroup = $db->fetchObject("select * from it_ck_pickgroup where id=$pickgroup_id");
		if ($pickgroup) {
			$query = "update it_ck_pickgroup set pickingComplete_time = now()";
//			if ($invoice_no) { $query .= ", invoice_no=$invoice_no"; }
//			if ($shipped_qty) { $query .= ", shipped_qty=$shipped_qty"; }
//			if ($shipped_mrp) { $query .= ", shipped_mrp=$shipped_mrp"; }
//			if ($cheque_amt) { $query .= ", cheque_amt=$cheque_amt"; }
//			if ($cheque_dtl) { $query .= ", cheque_dtl=$cheque_dtl"; }
			//if ($transport_dtl) { $query .= ", transport_dtl=$transport_dtl"; }
			//if ($remarks) { $query .= ", remark=$remarks"; }
			if ($picker_id) { $query .= ", picker_id=$picker_id"; }
			$query .= " where id = $pickgroup_id";
//print "$query<br />";
			$db->execUpdate($query);
			$query = "update it_ck_orders set status = ".OrderStatus::Picking_Complete." where id in ($pickgroup->order_ids)";
//print "$query<br />";
			$db->execUpdate($query);
			$success = 'Order has been marked as Picking Complete.';
			unset($_SESSION['form_post']);
//                        $store = $db->fetchObject("select * from it_codes where id=$pickgroup->storeid");
//                        if ($store && $store->phone) {
//                                require_once "lib/sms/clsSMSServe.php";
//                                $cls = new clsSMSServe();
//                                $msg = "Dear $store->owner, Your payment is deposited against PO:$pickgroup->order_nos , Ship:$transport_dtl. Thank You." ; // Invoice No:$invoice_no, Chq Amt:$cheque_amt, Chq No:$cheque_dtl, Ship:$transport_dtl. Thank You.";
//                                $cls->saveSMSreply($store->phone, $msg);
//                                if ($remarks) $cls->saveSMSreply($store->phone, "Message from CK: ".trim($remarks,"'"));
//                                if ($store->phone2) {
//                                        $cls->saveSMSreply($store->phone2, $msg);
//                                        if ($remarks) $cls->saveSMSreply($store->phone2, "Message from CK: ".trim($remarks,"'"));
//                                }
//                        }
//			if ($store && ($store->email || $store->email2)) {
//				$pickgroup = $db->fetchObject("select p.*, c.store_name from it_ck_pickgroup p, it_codes c where p.id=$pickgroup_id and p.storeid = c.id");
//				$message = $db->safe(getMessage($pickgroup));
//				$subject = $db->safe("Your order [$pickgroup->order_nos] has been shipped");
//				if ($store->email) {
//					$db->execInsert("insert into it_emails set storeid=$pickgroup->storeid, emailaddress='$store->email', subject=$subject, body=$message");
//				}
//				if ($store->email2) {
//					$db->execInsert("insert into it_emails set storeid=$pickgroup->storeid, emailaddress='$store->email2', subject=$subject, body=$message");
//				}
//			}
		
               // $pquery = "select * from it_ck_pickgroup where id = $pickgroup_id ";
                $pquery = "select order_nos,id as pickingId,storeid from it_ck_pickgroup where id = $pickgroup_id ";
                $obj = $db->fetchObject($pquery);
                //fetch pickgroup barcode n qty
                //$piquery = "select i.barcode,ci.order_qty as qty from it_ck_orders c , it_ck_orderitems ci, it_items i where ci.order_id = c.id and ci.item_id = i.id and c.pickgroup = $pickgroup_id ";
                $piquery = "select i.barcode,sum(ci.order_qty) as qty from it_ck_orders c , it_ck_orderitems ci, it_items i where ci.order_id = c.id and ci.item_id = i.id and c.pickgroup = $pickgroup_id group by i.barcode order by i.barcode";
                $piobj = $db->fetchObjectArray($piquery);
                $pitems = json_encode($piobj);
                $obj->items = $pitems;
                $server_ch = "[".json_encode($obj)."]";
                $ser_type = changeType::ck_pickgroup;
                $store_id = DEF_CK_WAREHOUSE_ID;
          //here $obj->pickingId is id of table it_ck_pickgroup so it becomes the data_id
                $serverCh->save($ser_type, $server_ch, $store_id,$obj->pickingId );
                }
	}
} catch (Exception $xcp) {
	$clsLogger = new clsLogger();
	$clsLogger->logError("Failed to add $fullname:".$xcp->getMessage());
	$errors['status']="There was a problem processing your request. Please try again later";
}
if (count($errors) > 0) {
	$_SESSION['form_errors'] = $errors;
	$redirect = "dispatch/shipped/pid=$pickgroup_id";
} else {
	unset($_SESSION['form_errors']);
        $_SESSION['form_success'] = $success;
	$redirect = "orders/picking/complete";	 
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
$message .= "<td>Order No:</td><td>$pickgroup->order_nos</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Order Quantity:</td><td>$pickgroup->order_qty</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Order Amount:</td><td>$pickgroup->order_amount</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Designs:</td><td>$pickgroup->num_designs</td>";
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

/*
 $message .= "<tr>";
$message .= "<td>Invoice No:</td><td>$pickgroup->invoice_no</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Shipped Qty:</td><td>$pickgroup->shipped_qty</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Shipped MRP:</td><td>$pickgroup->shipped_mrp</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Cheque Amount:</td><td>$pickgroup->cheque_amt</td>";
$message .= "</tr>";
$message .= "<tr>";
$message .= "<td>Cheque Detail:</td><td>$pickgroup->cheque_dtl</td>";
$message .= "</tr>";
 */
?>
