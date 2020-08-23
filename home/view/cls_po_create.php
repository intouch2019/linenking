<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");


class cls_po_create extends cls_renderer {

        var $currUser;
        var $userid;
        
        function __construct($params=null) {
        parent::__construct(array(UserType::Admin, UserType::CKAdmin));            
            $this->currUser = getCurrUser();
            $this->params = $params;
            if (!$this->currUser) { return; }
            $this->userid = $this->currUser->id;
            $this->currUserName = $this->currUser->name;
            if (isset($_SESSION['account_dtrange'])) { $this->dtrange = $_SESSION['account_dtrange']; }
            else { $this->dtrange = date("d-m-Y"); }            

        }

	function extraHeaders() {
        if (!$this->currUser) {
            ?>
<h2>Session Expired</h2>
Your session has expired. Click <a href="">here</a> to login.
            <?php
            return;
        }

        ?>

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
<script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />
<link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
<link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />

<script type="text/javascript">
    function suppDesignSelect(dropdown){
        var idx = dropdown.selectedIndex;
        var value = dropdown.options[idx].value;
	if (value == "-1") { return; }
        $("#suppdesignselect").hide();
        $("#suppdesignfld").val(value);
        $("#suppdesigndiv").show();
    }
</script>
        
        <?php
        }

        public function pageContent() {
            $currUser = getCurrUser();
            $menuitem = "purchaseorder";
            include "sidemenu.".$currUser->usertype.".php";
            $formResult = $this->getFormResult();
            $db = new DBConn();
?>
<div class="grid_10">
    <?php $_SESSION['form_post'] = array(); ?>
    <?php
    $objs = $db->fetchObjectArray("select id, name from it_suppliers where inactive=0");
    $obju = $db->fetchObjectArray("select id, name from it_users where inactive=0 and usertype=2");
    $objpo = $db->fetchObject("select id from it_posnum");
    $display="none";
    ?>
    <div class="grid_3">&nbsp;</div>
    <div class="grid_12">
            <form action="formpost/poCreate.php" method="post">
        <fieldset>
            <input type="hidden" name="userid" value="<?php echo $this->userid?>"/>
            <legend>Create Purchase Order</legend>
                <div style="clear:both;">
                   <label class="grid_2">PO Type: </label> 
                <select name="potype">
                    <option value="">Select PO Type</option>
                                <?php
                                $allPOTypes = PoType::getAll();
                                $display = "block";
                                foreach ($allPOTypes as $potype => $typename) {
                                    ?>
                        <option value="<?php echo $potype; ?>" <?php echo $selected; ?>><?php echo $typename; ?></option>
                                <?php } ?>
                </select>
		</div>
                <div style="clear:both;">
                   <label class="grid_2">Supplier: </label> 
                   <textarea name="supplier" id="supplier" type="text" value="" onkeyup="ajax_showOptions(this,'getSuppliersByLetters',event)" cols="22" rows="1"></textarea>
                <!--<select name="supplier" onChange="selectDesign(this);">
                    <option value="">Select Supplier</option>
                    <?php foreach ($objs as $obj) {
                    ?>                            
                    <option value="<?php echo $obj->id;?>"><?php echo $obj->name; ?></option>
                    <?php } ?>
                </select>-->
		</div>
                <div style="clear:both;">
                   <label class="grid_2">Consignee Name: </label> 
                   <input type="text" name="consignee" value="Cotton King">
		</div>
                <?php if ($formResult) { ?>
                <p>
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                <?php } ?>
                <input type="submit" value="Create">
        </fieldset>
            </form>
    </div>
</div>
<?php
	}
}
?>
