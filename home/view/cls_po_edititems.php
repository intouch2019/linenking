<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");


class cls_po_edititems extends cls_renderer {

        var $currUser;
        var $userid;
        var $dtrange;
        var $cuurUserName;
        var $poitems=array();
	var $poline_id;
        var $sup_id;
        var $po_no;
        
        function __construct($params=null) {
		parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::GoodsInward));            
		$this->currUser = getCurrUser();
		$this->params = $params;
		if (!$this->currUser) { return; }
		$this->userid = $this->currUser->id;
		$this->currUserName = $this->currUser->name;
		if (isset($this->params['id'])) {
			$this->poline_id = $this->params['id'];
		} else {
			throw new Exception("Unauthorized access");
		}
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
        var v = value.split(",");
        $("#suppdesignfld").val(v[0]);
        $("#suppdesigndiv").show();
        var supId = $("#supId").val();
        var sdesign = v[0];
        if(v[1] != null){
            $('input[name="ckdesign"]').val(v[1]);
            $.ajax({
                    url: "ajax/getCKDesign.php?supId="+supId+"&sdesign="+sdesign,
                    success: function(data) {
                            $("#ckdesignfld").val(data);
                    }
            });
        }
    }
    
    function fabricTypeSelect(dropdown){
        var idx = dropdown.selectedIndex;
        var value = dropdown.options[idx].value;
        if(value == "-1") { return; }
        $("#fabrictypeselect").hide();
        $("#fabrictypefld").val(value);
        $("#fabrictypediv").show();
    }
    
    function productSelect(dropdown){
        var idx = dropdown.selectedIndex;
        var value = dropdown.options[idx].value;
        if(value == "-1") { return; }
        $("#productselect").hide();
        $("#productfld").val(value);
        $("#productdiv").show();
    }

    function productTypeSelect(dropdown){
        var idx = dropdown.selectedIndex;
        var value = dropdown.options[idx].value;
        if(value == "-1") { return; }
        $("#producttypeselect").hide();
        $("#producttypefld").val(value);
        $("#producttypediv").show();
    }

        
    $(function(){
	var isOpen=false;
	$('#exdate').daterangepicker({
	 	dateFormat: 'yy-mm-dd',
		arrows:false,
		closeOnSelect:true,
		onOpen: function() { isOpen=true; },
		onClose: function() { isOpen=false; },
		onChange: function() {
		if (isOpen) { return; }
		var dtrange = $("#exdate").val();
		$.ajax({
			url: "savesession.php?name=account_dtrange&value="+dtrange,
			success: function(data) {
				//window.location.reload();
			}
		});
		}
	});
        
        $('input[name="rate"]').keyup(function() {
            var a = $('input[name="qty"]').val();
            var b = $(this).val();
            $('input[name="value"]').val(a * b);
        });
        
        $('input[name="qty"]').keyup(function() {
            var a = $('input[name="rate"]').val();
            var b = $(this).val();
            $('input[name="value"]').val(a * b);
        });
        
        $('input[name="sdesign"]').focusout(function(){
           var supId = $("#supId").val();            
           var sdesign = $(this).val();
           $.ajax({
                url: "ajax/getCKDesign.php?supId="+supId+"&sdesign="+sdesign,
                success: function(data) {
                        $("#ckdesignfld").val(data);
                }
           });
        });
        
        
    });

    function uomSelect(dropdown){
        var idx = dropdown.selectedIndex;
        var value = dropdown.options[idx].value;
        if(value == "-1") { return; }
        $("#uomselect").hide();
        $("#uomfld").val(value);
        $("#uomdiv").show();
    }

    function reload() {
            var dtrange = $("#exdate").val();
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
            $menuitem = "purchaseorder";
            include "sidemenu.".$currUser->usertype.".php";
            $formResult = $this->getFormResult();
            $db = new DBConn();
?>
<div class="grid_10">
    <?php $_SESSION['form_post'] = array(); ?>
    <?php

    /*$objpoline = $db->fetchObject("select po.po_id as poid, sup.designno as suppdesign, ck.designno as ck_design, po.qty as qty, po.uom as uom, po.rate as rate, po.expected_date as ex_date, f.name as fabrictype, c.name ".
    "as color, prod.name as product, prodtype.name as productiontype from it_supplierdesign sup, it_ckdesign ck, it_polines po, it_itemtype f, it_color c, it_product ".
    "prod, it_productiontype prodtype where po.suppdesign_id = sup.id and po.ckdesign = ck.id and ck.itemtype_id = f.id and ck.color_id = c.id and ck.product_id = prod.id ". 
    "and ck.productiontype_id = prodtype.id and po.id = $this->poline_id");*/
   
    $objpoline = $db->fetchObject("select po.po_id as poid, sup.designno as suppdesign, ck.designno as ck_design, po.qty as qty,".
    " po.uom as uom, po.rate as rate, po.expected_date as ex_date, it_itemtype.name as fabrictype,".
    " it_color.name as color, it_product.name as product, it_productiontype.name as productiontype from it_polines po,".
    " it_supplierdesign sup, it_ckdesign ck left outer join it_itemtype on ck.itemtype_id = it_itemtype.id left outer join".
    " it_color on ck.color_id = it_color.id left outer join it_product on ck.product_id = it_product.id left outer join".
    " it_productiontype on ck.productiontype_id = it_productiontype.id where po.suppdesign_id = sup.id and po.ckdesign = ck.id".
    " and po.id = $this->poline_id");

    
    $this->po_id = $objpoline->poid;
    
    $objpo = $db->fetchObject("select s.name as supplier, p.supplier_id as supplier_id,p.potype as potype, p.pono as pono, p.consignee as consignee, u.name as preparedby, p.createtime from it_suppliers s, it_purchaseorder p, it_users u where s.id = p.supplier_id and u.id = p.preparedby_id and p.id=$this->po_id");
    $objuom = $db->fetchObjectArray("select id, name from it_uom");    

    $objf = $db->fetchObjectArray("select id, name from it_itemtype");
    $objprod = $db->fetchObjectArray("select id, name from it_product");
    $objprodtype = $db->fetchObjectArray("select id, name from it_productiontype");
    
    $this->sup_id = $objpo->supplier_id;
    $objsd = $db->fetchObjectArray("select id, designno as supp_design, ckdesign_id as ckdesign from it_supplierdesign where supplier_id = $this->sup_id");
    
    
    $display="none";
    ?>
    <div class="box" style="clear:both;">
	<fieldset class="login">
	<legend>
		PO No: <?php echo $objpo->pono ?> | 
                PO Type: <?php echo $objpo->potype ?> |
                Supplier: <?php echo $objpo->supplier ?> | 
                Consignee: <?php echo $objpo->consignee ?> | 
                Prepared By: <?php echo $objpo->preparedby ?> | 
                Create Date: <?php echo $objpo->createtime ?>
	</legend>
            <form action="formpost/poEditItem.php" method="post">
                <input type="hidden" name="polineid" value="<?php echo $this->poline_id ?>" />
                <input type="hidden" name="potype" value="<?php echo $objpo->potype ?>">
                <input type="hidden" name="poid" value="<?php echo $this->po_id ?>" />
                <input type="hidden" id="supId" name="supId" value="<?php echo $this->sup_id?>" />
                <legend>Edit PO Line</legend>
		<div class="block grid_10">
		<div class="grid_2">
                Supplier Design:<br />
                    <input id="suppdesignfld" type="text" name="sdesign" value="<?php echo $objpoline->suppdesign?>" onkeyup="ajax_showOptions(this,'getSupplierDesignByLetters',event)"/>
 		</div>
		<div class="grid_2">
		CK Design: 
                <input type="text" name="ckdesign" id="ckdesignfld" value="<?php echo $objpoline->ck_design?>"/>
		</div>
		<div class="grid_2">
		Quantity: 
                <input type="text" name="qty" id="qty" value="<?php echo $objpoline->qty?>"/>
		</div>
		<div class="grid_2">
                Unit of Measure:<br/> 
                <input type="text" name="uom" id="uomfld" value="<?php echo $objpoline->uom ?>" onkeyup="ajax_showOptions(this,'getUOMByLetters',event)"/>                    
		</div>
		<div class="grid_2">
		Rate: 
                <input type="text" name="rate" id="rate" value="<?php echo $objpoline->rate ?>"/>
		</div>
		<div class="grid_1">
		Value: 
                <input type="text" name="value" id="value" value="<?php 
                    $q = $objpoline->qty;
                    $r = $objpoline->rate;
                    $v = $q * $r;
                    echo $v ?>"/>
		</div>
		<div class="grid_2">
		&nbsp;
		</div>
		<div class="grid_2">
                    Product:<br />
                    <input id="productfld" type="text" name="nproduct" value="<?php echo $objpoline->product?>" onkeyup="ajax_showOptions(this,'getProductByLetters',event)"/>
		</div>
		<div class="grid_2">
                    Item Type:<br />
                    <input id="fabrictypefld" type="text" name="fabtype" value="<?php echo $objpoline->fabrictype?>" onkeyup="ajax_showOptions(this,'getItemTypeByLetters',event)"/>
                </div>
		<div class="grid_2">
		Color: 
                <input type="text" id="color" name="color" value="<?php echo $objpoline->color?>" onkeyup="ajax_showOptions(this,'getColorByLetters',event)"/>
		</div>
		<div class="grid_2">
                    Production Type:<br />
                    <input id="producttypefld" type="text" name="nproducttype" value="<?php echo $objpoline->productiontype?>" onkeyup="ajax_showOptions(this,'getProductionTypeByLetters',event)"/>
		</div>
                <div class="grid_2">
        		Expected Date: 
                <input type="text" id="exdate" name="exdate" value="<?php echo $objpoline->ex_date?>"/>
		</div>
                    
		<div class="grid_2">
		&nbsp;
		</div>
		<div class="grid_2">
                <input type="submit" name="add" id="add" value="Save"/>
                <a href="po/additems/id=<?php echo $this->po_id?>"><Button>Cancel</Button></a>
		</div>
		</div> <!-- grid_10 -->
                
                       <?php if ($formResult) { ?>
                <p>
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
            </form>
	</fieldset>
    </div> <!-- class=box -->
</div>
<?php
	}
}
?>
