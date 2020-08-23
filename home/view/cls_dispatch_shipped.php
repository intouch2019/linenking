<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_dispatch_shipped extends cls_renderer {
    var $currStore;
    var $storeid;
    var $pickgroup_id;
    function __construct($params=null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (!$this->currStore) { return; }
        $this->storeid = $this->currStore->id;
        if (isset($params['pid']))
            $this->pickgroup_id = $params['pid'];
    }

    function extraHeaders() {
        if (!$this->currStore) {
            ?>
<h2>Session Expired</h2>
Your session has expired. Click <a href="">here</a> to login.
            <?php
            return;
        }
    }

    //extra-headers close
    public function pageContent() {
//        if ($this->currStore->usertype != UserType::Dispatcher) { print "Unauthorized Access"; return; }
	$menuitem="";
        include "sidemenu.".$this->currStore->usertype.".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
	$obj = $db->fetchObject("select * from it_ck_pickgroup where id = $this->pickgroup_id");
	if (!$obj) { print "Pickgroup [$this->pickgroup_id] not found"; return; }
	if ($this->currStore->id != $obj->dispatcher_id) {
		print "Unauthorized Access. Only the Dispatcher who picked the order is allowed to Complete it.";
		return;
	}
        ?>
<div class="grid_10">
    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset>
            <legend>Order No(s): <?php echo $obj->order_nos; ?> - Details<br />Amount: <?php echo $obj->order_amount; ?> | Quantity: <?php echo $obj->order_qty; ?> | Designs: <?php echo $obj->num_designs; ?></legend>
            <form action="formpost/orderShipped.php" method="post"  onsubmit="subbtn.disabled = true; return true;">
		<input type="hidden" name="pickgroup_id" value="<?php echo $this->pickgroup_id; ?>" />
<!--                <p>
                    <label>Invoice No: </label>
                    <input type="text" name="invoice_no" value="<?php echo $this->getFieldValue('invoice_no'); ?>">
                </p>-->
<!--                <p>
                    <label>Shipped Quantity: </label>
                    <input type="text" name="shipped_qty" value="<?php echo $this->getFieldValue('shipped_qty'); ?>">
                </p>-->
<!--                <p>
                    <label>Shipped MRP: </label>
                    <input type="text" name="shipped_mrp" value="<?php echo $this->getFieldValue('shipped_mrp'); ?>">
                </p>
                <p>
                    <label>Cheque Amount: </label>
                    <input type="text" name="cheque_amt" value="<?php echo $this->getFieldValue('cheque_amt'); ?>">
                </p>
                <p>
                    <label>Cheque Details: </label>
                    <input type="text" name="cheque_dtl" value="<?php echo $this->getFieldValue('cheque_dtl'); ?>">
                </p>-->
<!--                <p>
                    <label>Transport Details: </label>
                    <input type="text" name="transport_dtl" value="<?php echo $this->getFieldValue('transport_dtl'); ?>">
                </p>
                <p>
                    <label>Additional Remarks: </label>
                    <textarea cols="50" rows="7" name="remarks"><?php echo $this->getFieldValue('remarks'); ?></textarea>
                </p>-->
                <p>
                    <label>Picker: </label>
		    <select name="picker_id">
			<option value="">Please Select</option>
<?php
	$users = $db->fetchObjectArray("select id,store_name as fullname from it_codes where usertype=".UserType::Picker." order by fullname");
	foreach ($users as $picker) {
		if ($picker->id == $this->getFieldValue('picker_id')) {
			$selected = "selected";
		}
		else { $selected = ""; }
?>
			<option value="<?php echo $picker->id; ?>" <?php echo $selected; ?>><?php echo $picker->fullname; ?></option>
<?php } ?>
		    </select>
                </p>
                        <?php if ($formResult) { ?>
                <p>
                <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
                <input type="submit" name="subbtn"  value="Submit">
                <a href="dispatch/vieworder/pid=<?php echo $this->pickgroup_id; ?>"><button>Cancel</button></a>
            </form>
        </fieldset>
    </div>
</div>
    <?php
    }
}
?>
