<?php
require_once "view/cls_renderer.php";
//require_once ("lib/codes/clsStocks.php");
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_dispatch_addship extends cls_renderer {
    var $currStore;
    var $storeid;
    //var $pickgroup_id;
    function __construct($params=null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (!$this->currStore) { return; }
        $this->storeid = $this->currStore->id;
//        if (isset($params['pid']))
//            $this->pickgroup_id = $params['pid'];
    }

    function extraHeaders() {
        if (!$this->currStore) {
            ?>
<h2>Session Expired</h2>
Your session has expired. Click <a href="">here</a> to login.
            <?php
            return; 
        } ?>
	<style type="text/css">
	/* Big box with list of options */
	#ajax_listOfOptions{
		position:absolute;	/* Never change this one */
		width:200px;	/* Width of box */
		height:250px;	/* Height of box */
		overflow:auto;	/* Scrolling features */
		border:1px solid #317082;	/* Dark green border */
		background-color:#FFF;	/* White background color */
		text-align:left;
		font-size:0.9em;
		z-index:100;
	}
	#ajax_listOfOptions div{	/* General rule for both .optionDiv and .optionDivSelected */
		margin:1px;		
		padding:1px;
		cursor:pointer;
		font-size:1.9em;
	}
	#ajax_listOfOptions .optionDiv{	/* Div for each item in list */
		
	}
	#ajax_listOfOptions .optionDivSelected{ /* Selected item in the list */
		background-color:#317082;
		color:#FFF;
	}
	#ajax_listOfOptions_iframe{
		background-color:#F00;
		position:absolute;
		z-index:5;
	}
	
	form{
		display:inline;
	}
	</style>
	<script type="text/javascript" src="js/ajax.js"></script>
	<script type="text/javascript" src="js/ajax-dynamic-list.js">	
        /************************************************************************************************************
	(C) www.dhtmlgoodies.com, April 2006
	
	This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.	
	
	Terms of use:
	You are free to use this script as long as the copyright message is kept intact. However, you may not
	redistribute, sell or repost it without our permission.
	
	Thank you!
	
	www.dhtmlgoodies.com
	Alf Magne Kalleland
	
	************************************************************************************************************/	
	</script>
        <script type="text/javascript">
$(function() {
    $('#billamt').keyup(function(e) {
        fillChequeAmount();
    });
    $('#credamt').keyup(function(e) {
        fillChequeAmount();
    });
});

function fillChequeAmount() {
    var billamt = $('#billamt').val();
    var creditamt = $('#credamt').val();
    var creditamt = $('#credamt').val();
    if (!isNaN(billamt) && !isNaN(creditamt)) {
       var chequeamt;
       if (creditamt == '') chequeamt = parseFloat(billamt);
       else chequeamt = parseFloat(billamt) - parseFloat(creditamt);
       
       if (billamt == '') $('#cheqamt').val('');
       else if (!isNaN(chequeamt)) $('#cheqamt').val(chequeamt);
    }
}
</script>
  <?php  }

    //extra-headers close
    public function pageContent() {
        if ($this->currStore->usertype != UserType::Dispatcher) { print "Unauthorized Access"; return; }
	$menuitem="addship";
        include "sidemenu.".$this->currStore->usertype.".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
        $storetype = UserType::Dealer;
        $allstores = $db->fetchObjectArray("select * from it_codes where usertype=$storetype order by store_name");
        ?>
<div class="grid_10">
    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset>
            <legend>New Shipment</legend>
            <form action="formpost/orderShipped2.php" method="post">
                <p>
                    <select name='store_id'>
                        <?php foreach ($allstores as  $store) { ?>
                            <option value='<?php echo $store->id; ?>'><?php echo $store->store_name; ?></option>
                        <?php } ?>
                    </select>
                </p>
                <p>
                    <label>Invoice No: </label>
                    <input required type="text" name="invoice_no" value="<?php echo $this->getFieldValue('invoice_no'); ?>">
                </p>
                <p>
                    <label>Shipped Quantity: </label>
                    <input required type="text" name="shipped_qty" value="<?php echo $this->getFieldValue('shipped_qty'); ?>">
                </p>
                <p>
                    <label>Bill Amount: </label>
                    <input required id="billamt" type="text" name="bill_amt" value="<?php echo $this->getFieldValue('bill_amt'); ?>">
                </p>
                <p style="width:50%; float:left;">
                    <label>Credit Note Number</label>
                    <input style="width:90%;" type="text" name="credit_num" value="<?php echo $this->getFieldValue('credit_num'); ?>">
                </p>
                <p style="width:50%; float:left;">
                    <label>Credit Note Amount</label>
                    <input id="credamt" style="width:95%;" type="text" name="credit_amt" value="<?php echo $this->getFieldValue('credit_amt'); ?>">
                </p>
                <p>
                    <label>Cheque Amount</label>
                    <input style="background: #eee;" required readonly id="cheqamt" type="text" align="center" name="cheque_amt" value="<?php echo $this->getFieldValue('cheque_amt'); ?>">
                </p>
                <p>
                    <label>Cheque Number: </label>
                    <input required type="text" name="cheque_dtl" value="<?php echo $this->getFieldValue('cheque_dtl'); ?>">
                </p>
                <p style="width:50%; float:left;">
                    <label>Bank : </label>
                    <input required style="width:90%;" type="text" name="bank" value="<?php echo $this->getFieldValue('bank'); ?>" onkeyup='ajax_showOptions(this,"storeid=<?php echo $obj->storeid; ?>&bank",event)'>
                </p>
                <p style="width:50%; float:left;">
                    <label>Branch : </label>
                    <input required style="width:95%;" type="text" name="branch" value="<?php echo $this->getFieldValue('branch'); ?>" onkeyup='ajax_showOptions(this,"storeid=<?php echo $obj->storeid; ?>&branch",event)'>
                </p>
                <p>
                    <label>Transport Details: </label>
                    <input required type="text" name="transport_dtl" value="<?php echo $this->getFieldValue('transport_dtl'); ?>">
                </p>
                <p>
                    <label>Additional Remarks: </label>
                    <textarea cols="50" rows="7" name="remarks"><?php echo $this->getFieldValue('remarks'); ?></textarea>
                </p>
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
                <input type="submit" value="Submit">
                <a href="dispatch/vieworder/pid=<?php echo $this->pickgroup_id; ?>"><button>Cancel</button></a>
            </form>
        </fieldset>
    </div>
</div>
    <?php
    }
}
?>
