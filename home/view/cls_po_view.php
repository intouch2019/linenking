<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");


class cls_po_view extends cls_renderer {

        var $currUser;
        var $userid;
        var $dtrange;
        var $cuurUserName;
        var $poitems=array();
	var $po_id;
        var $sup_id;
        var $p_type;
        
        function __construct($params=null) {
		parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::GoodsInward));            
		$this->currUser = getCurrUser();
		$this->params = $params;
		if (!$this->currUser) { return; }
		$this->userid = $this->currUser->id;
		$this->currUserName = $this->currUser->name;
		if (isset($this->params['id'])) {
			$this->po_id = $this->params['id'];
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

<script type="text/javascript" src="js/expand.js"></script>
<link rel="stylesheet" href="jqueryui/css/custom-theme/jquery-ui-1.8.14.custom.css" type="text/css" media="screen" charset="utf-8" />
<link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
<link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
<script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>

<script type="text/javascript">

    $(function() {
        // --- Using the default options:
        $("h2.expand").toggler();
        // --- Other options:
        $("h2.expand").toggler({method: "toggle", speed: "slow"});
        $("h2.expand").toggler({method: "toggle"});
        $("h2.expand").toggler({speed: "fast"});
        $("h2.expand").toggler({method: "fadeToggle"});
        $("h2.expand").toggler({method: "slideFadeToggle"});
        $("#content").expandAll({trigger: "h2.expand"});
    });

	$(function(){
		modal = $("#orderinfo").dialog({
			autoOpen: false,
			title: 'Order Details'
		});
	});

        function showDialog(content) {
		$("#orderinfo").html(content);
		modal.dialog('open');
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
<div id="orderinfo"></div>
<div class="grid_10">
    <?php $_SESSION['form_post'] = array(); ?>
    <?php
    $objpo = $db->fetchObject("select s.name as supplier, p.supplier_id as supplier_id, p.potype as potype, p.pono as pono, p.consignee as consignee, u.name as preparedby, p.createtime, p.submittedtime as submittime from it_suppliers s, it_purchaseorder p, it_users u where s.id = p.supplier_id and u.id = p.preparedby_id and p.id=$this->po_id");
    $this->sup_id = $objpo->supplier_id;
    $this->p_type = $objpo->potype;
    
    $objpolines = $db->fetchObjectArray("select po.id as id, sup.designno as suppdesign, ck.designno as ck_design, po.qty as qty,".
    " po.uom as uom, po.rate as rate, po.expected_date as ex_date, it_itemtype.name as fabrictype,".
    " it_color.name as color, it_product.name as product, it_productiontype.name as productiontype from it_polines po,".
    " it_supplierdesign sup, it_ckdesign ck left outer join it_itemtype on ck.itemtype_id = it_itemtype.id left outer join".
    " it_color on ck.color_id = it_color.id left outer join it_product on ck.product_id = it_product.id left outer join".
    " it_productiontype on ck.productiontype_id = it_productiontype.id where po.suppdesign_id = sup.id and po.ckdesign = ck.id".
    " and po.po_id = $this->po_id");
    
    
    /*$objpolines = $db->fetchObjectArray("select po.id as id, sup.designno as suppdesign, ck.designno as ck_design, po.qty as qty, po.uom as uom, po.rate as rate, po.expected_date as ex_date, f.name as fabrictype, c.name ".
    "as color, prod.name as product, prodtype.name as productiontype from it_supplierdesign sup, it_ckdesign ck, it_polines po, it_itemtype f, it_color c, it_product ".
    "prod, it_productiontype prodtype where po.suppdesign_id = sup.id and po.ckdesign = ck.id and ck.itemtype_id = f.id and ck.color_id = c.id and ck.product_id = prod.id ". 
    "and ck.productiontype_id = prodtype.id and po.po_id = $this->po_id");
    */
    
    //$display="none";
    ?>
    <div class="box" style="clear:both;">
	<legend>
		PO No: <?php echo $objpo->pono ?> | 
                PO Type: <?php echo PoType::getName($objpo->potype) ?> |
                Supplier: <?php echo $objpo->supplier ?> | 
                Consignee: <?php echo $objpo->consignee ?> | 
                Prepared By: <?php echo $objpo->preparedby ?> | 
                Create Date: <?php echo $objpo->createtime ?> |
                Submitted Date: <?php echo$objpo->submittime ?>
	</legend>
                <legend>PO Items List</legend>
        <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow-x: hidden; overflow-y: hidden; ">
        <div class="block" id="accordion" style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; ">
        <div id="accordion">
                <table id="polines" align="center" border="1">
                    <tr>
                        <th>Supplier Design No</th>
                        <th>Design No</th>
                        <th>Qty</th>
                        <th>UOM</th>
                        <th>Rate</th>
                        <th>Value</th>
                        <th>Expected Date</th>
                        <th></th>
                    </tr>
                    <?php foreach($objpolines as $obj) {
                    $dialogHtml = '<table border="0">';
                    $dialogHtml .= "<tr>";
                    $dialogHtml .= "<th colspan=2>Other Details</th>";
                    $dialogHtml .= "</tr>";
                    $dialogHtml .= "<tr>";
                    $dialogHtml .= "<td>Fabric Type:</td><td>$obj->fabrictype</td>";
                    $dialogHtml .= "</tr>";                    
                    $dialogHtml .= "<tr>";
                    $dialogHtml .= "<td>Color:</td><td>$obj->color</td>";
                    $dialogHtml .= "</tr>";                    
                    $dialogHtml .= "<tr>";
                    $dialogHtml .= "<td>Product:</td><td>$obj->product</td>";
                    $dialogHtml .= "</tr>";                    
                    $dialogHtml .= "<tr>";
                    $dialogHtml .= "<td>Production Type:</td><td>$obj->productiontype</td>";
                    $dialogHtml .= "</tr>";                    
                    $dialogHtml .= '</table>';
                    $dialogHtml = json_encode($dialogHtml);
                    ?>
                    <tr>
                        <td><?php echo $obj->suppdesign?></td>
                        <td><?php echo $obj->ck_design?></td>
                        <td><?php echo $obj->qty?></td>
                        <td><?php echo $obj->uom?></td>
                        <td><?php echo $obj->rate?></td>
                        <td><?php 
                            $q = $obj->qty;  
                            $r = $obj->rate;
                            $value = $q * $r;
                            echo $value?>
                        </td>
                        <td><?php echo $obj->ex_date?></td>
                        <td><a style="text-decoration:underline;" href="#" onclick='javascript:showDialog(<?php echo $dialogHtml; ?>);return false;'>Details</a></td>                        
                    </tr>
                    <?php }?>
		</table>
            </div>
           </div> 
        </div>                
    </div> <!-- class=box -->
</div>
<?php
	}
}
?>
