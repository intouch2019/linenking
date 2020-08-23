<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");


class cls_inward_gateentry extends cls_renderer {

        var $currUser;
        var $userid;
        var $dtrange;
        function __construct($params=null) {
        parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::GoodsInward));            
            $this->currUser = getCurrUser();
            $this->params = $params;
            if (!$this->currUser) { return; }
            $this->userid = $this->currUser->id;
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
        }?>
<script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
<link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
<link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
<script type="text/javascript">
    
     function transporterSelect(dropdown){
        var idx = dropdown.selectedIndex;
        var value = dropdown.options[idx].value;
        if (value == 'other') {
            $("#transporterinfo").show();
        } else {
            $("#transporterinfo").hide();
        }
    }
    
    
    $(function(){
	var isOpen=false;
        //$("a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'fast',slideshow:3000, hideflash: true});
	$('#dtreceived').daterangepicker({
	 	dateFormat: 'yy-mm-dd',
		arrows:false,
		closeOnSelect:true,
		onOpen: function() { isOpen=true; },
		onClose: function() { isOpen=false; },
		onChange: function() {
		if (isOpen) { return; }
		var dtrange = $("#dtreceived").val();
		$.ajax({
			url: "savesession.php?name=account_dtrange&value="+dtrange,
			success: function(data) {
				//window.location.reload();
			}
		});
		}
	});
    });
    
    function reload() {
            var dtrange = $("#dtreceived").val();
            $.ajax({
                    url: "savesession.php?name=account_dtrange&value="+dtrange,
                    success: function(data) {
                            window.location.reload();
                    }
            });
    }

</script>
        
        <?php
        }
        
        public function pageContent() {
            $currUser = getCurrUser();
            $menuitem = "gateentry";
            include "sidemenu.".$currUser->usertype.".php";
            $formResult = $this->getFormResult();
            $db = new DBConn();
?>
<div class="grid_10">
    <?php $_SESSION['form_post'] = array(); ?>
    <?php
    $objs = $db->fetchObjectArray("select id, name from it_suppliers where inactive=0");
    $objt = $db->fetchObjectArray("select id, name from it_transporters where inactive=0");
    $obju = $db->fetchObjectArray("select id, name from it_users where inactive=0 and usertype=2");
    $display="none";
    ?>
    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset>
            <legend>Gate Entry</legend>
            <form action="formpost/gateEntry.php" method="post">
                <select name="supplier">
                    <option value="">Select Supplier</option>
                    <?php foreach ($objs as $obj) {
                    ?>                            
                    <option value="<?php echo $obj->id;?>"><?php echo $obj->name; ?></option>
                    <?php } ?>
                </select>
                <select name="transporter" onchange="transporterSelect(this);">
                    <option value="">Select Transporter</option>
                    <?php foreach ($objt as $obj) {  ?>                            
                    <option value="<?php echo $obj->id;?>"><?php echo $obj->name; ?></option>
                    <?php } ?>
                    <option value="other">Other</option>
                </select>
                <p></p>
                <span id="transporterinfo" style="display:<?php echo $display; ?>">
                    <p>
                    <label>Transporter Name: </label>
                    <input type="text" name="transname" value="<?php echo $this->getFieldValue('transname'); ?>">
                    </p>
                </span>    
                <p>
                   <label>Transporter Details:</label> 
                   <input type="text" name="transporterDetails" value="<?php echo $this->getFieldValue('transporterDetails')?>">
                </p>    
                <p>
                    <label>Quantity :</label>
                    <input type="text" name="quantity" value="<?php echo $this->getFieldValue('quantity')?>">    
                </p>
                    <p>
                    <label>Date Received :</label>
                    </p>
                    <input type="text" id="dtreceived" name="dtreceived" value="<?php echo $this->dtrange; ?>" />(Click in the box to see date options)
                <p></p>
                <select name="users">
                    <option value="">Received By</option>
                    <?php foreach ($obju as $obj) {  ?>                            
                    <option value="<?php echo $obj->id; ?>"><?php echo $obj->name; ?></option>
                    <?php } ?>
                </select>
                       <?php if ($formResult) { ?>
                <p>
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
                <input type="submit" value="Create">
            </form>
        </fieldset>
    </div>
</div>
<?php
	}
}
?>
