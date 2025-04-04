<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";

class cls_report_design_search extends cls_renderer {

    var $params;
    var $result;
    var $param_design_no;
    var $dtrange = null,$ptype = 0;
    var $storeidreport = null;
    
    function __construct($params=null) {
	parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Dispatcher, UserType::Manager));
        $this->params = $params;
        if (isset($params['dno']))
            $this->param_design_no = $params['dno'];
        if (isset($_SESSION['design_dtrange'])) { $this->dtrange = $_SESSION['design_dtrange']; }
            else { $this->dtrange = date("d-m-Y"); }
        if(isset($params['storeid'])){
            $this->storeidreport = $params['storeid'];
        }  
        if(isset($params['ptype'])){
            $this->ptype = $params['ptype'];
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
    <script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
    <link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
    <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
<script type="text/javascript" src="js/expand.js"></script>
<link rel="stylesheet" type="text/css" href="css/dark-glass/sidebar.css" />
<script type="text/javascript" src="js/sidebar/jquery.sidebar.js"></script>
<link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
<script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<link rel="stylesheet" href="js/chosen/chosen.css" />
<script type="text/javascript">
    <!--//--><![CDATA[//><!--
    $(function() {
//        $('#dateselect').datepicker({
//	 	dateFormat: 'dd-mm-yy',
//		arrows:false,
//		closeOnSelect:true,
//		onOpen: function() { isOpen=true; },
//		onClose: function() { isOpen=false; },
//		onChange: function() {
//		if (isOpen) { return; }
//		var dtrange = $("#dateselect").val();
//		$.ajax({
//			url: "savesession.php?name=design_dtrange&value="+dtrange,
//			success: function(data) {}
//		});
//		}
//	});
        // --- Using the default options:
        //$("h2.expand").toggler();
        // --- Other options:
        //$("h2.expand").toggler({method: "toggle", speed: "slow"});
        //$("h2.expand").toggler({method: "toggle"});
        //$("h2.expand").toggler({speed: "fast"});
        //$("h2.expand").toggler({method: "fadeToggle"});
        $("h2.expand").toggler({method: "slideFadeToggle"});
        $("#content").expandAll({trigger: "h2.expand"});
    });

    $(function(){
	$("a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'fast',slideshow:3000, hideflash: true});
	$("#design_no").keyup(function(event){
		if(event.keyCode == 13){
			$("#searchBtn").click();
		}
	});
        
        $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true});    
        $('#dateselect').daterangepicker({
            dateFormat: 'dd-mm-yy',
            arrows:false,
            closeOnSelect:true,
            onOpen: function() { isOpen=true; },
            onClose: function() { isOpen=false; },
            onChange: function() {
            if (isOpen) { return; }
            var dtrange = $("#dateselect").val();
            $.ajax({
                    url: "savesession.php?name=account_dtrange&value="+dtrange,
                    success: function(data) {
                            //window.location.reload();
                    }
            });
            }
	});
     });
    //--><!]]>
 
    function search()
    {
        var dtrange = $("#dateselect").val();
        $.ajax({
                url: "savesession.php?name=design_dtrange&value="+dtrange,
                success: function(data) {
                    var design_no=document.getElementById("design_no").value;
                    var store_id =document.getElementById("store") .value;
                    var ptype_id = document.getElementById("prod_type").value;
                    var addstore="",addptype="";
                    if(store_id != -1){ addstore = "/storeid="+store_id;}
                    if(ptype_id != 0){ addptype = "/ptype="+ptype_id;}
                    window.location.href="report/design/search/dno="+design_no+addstore+addptype;
                }
        });
    }
    
//    function activate(form_id) {
//            var formname=eval("order_"+form_id);
//            var params = $(formname).serialize();
//            var ajaxUrl = "ajax/activateDesign.php?"+params;
//            //alert(ajaxUrl);
//            $.getJSON(ajaxUrl, function(data){
//                if (data.error == "0") {
//                    $("#status_"+form_id).removeClass().addClass("success");
//                } else {
//                    $("#status_"+form_id).removeClass().addClass("error");
//                }
//                $("#status_"+form_id).html(data.message);
//            });
//
//            return false;
//    }

</script>


    <?php
    }

    //extra-headers close

    public function pageContent() {
        $formResult = $this->getFormResult();
	$menuitem = "gridDesignSearch";
        include "sidemenu.".$this->currUser->usertype.".php";
        $usertype = $this->currUser->usertype;
        $db = new DBConn();
        $allDesigns = array();
        $storeClause="";$pClause="";
        ?>

<div class="grid_10">
            <?php   
            if ($this->param_design_no) {
		$code = $db->safe($this->param_design_no);
                $dtarr = explode(" - ", $this->dtrange);
                    if (count($dtarr) == 1) {
                            list($dd,$mm,$yy) = explode("-",$dtarr[0]);
                            $sdate = "$yy-$mm-$dd";
                            $dQuery = " and date(o.bill_datetime) > '$sdate'";
                    }else if (count($dtarr) == 2) {
                            list($dd,$mm,$yy) = explode("-",$dtarr[0]);
                            $sdate = "$yy-$mm-$dd";
                            list($dd,$mm,$yy) = explode("-",$dtarr[1]);
                            $edate = "$yy-$mm-$dd";
                            $dQuery = " and date(o.bill_datetime) >= '$sdate' and date(o.bill_datetime) <= '$edate'";
                    } else {
                            $dQuery = "";
                    }
                //$query = "select i.design_no,i.ctg_id,i.MRP,ctg.name as category,d.image,d.lineno,d.rackno,d.active,sum(i.curr_qty) from it_items i,it_categories ctg,it_ck_designs d where i.design_no=$code and i.ctg_id = d.ctg_id and i.ctg_id=ctg.id and i.design_no=d.design_no group by i.ctg_id,i.design_no,MRP";
               if(isset($this->storeidreport) && trim($this->storeidreport) != "" && $this->storeidreport != -1 ){
                   $storeClause = " and c.store_id in ( $this->storeidreport ) ";
               }
               if($this->ptype != 0){
                  $pClause = " and i.prod_type_id in ( $this->ptype ) ";
               }
              // select i.id,i.barcode,i.design_no,i.ctg_id,i.MRP,i.style_id,i.size_id,ctg.name as category,d.image,d.lineno,d.rackno,d.active from it_items i,it_categories ctg,it_ck_designs d where i.design_no= '1532' and i.ctg_id = d.ctg_id and i.ctg_id=ctg.id and i.design_no=d.design_no  and i.barcode in (select c.barcode from it_current_stock c, it_items i where c.barcode = i.barcode and c.store_id in (76)) group by i.ctg_id
//               if(isset($this->storeidreport) && trim($this->storeidreport) != ""){     
                //$query_old = "select i.id,i.barcode,i.design_no,i.ctg_id,i.MRP,i.style_id,i.size_id,ctg.name as category,d.image,d.lineno,d.rackno,d.active  from it_items i,it_categories ctg,it_ck_designs d  where i.design_no= $code and i.ctg_id = d.ctg_id and i.ctg_id=ctg.id and i.design_no=d.design_no   $pClause  group by i.ctg_id "; //and c.barcode = i.barcode
                $query = "select i.id,i.barcode,i.design_no,i.ctg_id,i.MRP,i.style_id,i.size_id,ctg.name as category,d.image,d.lineno,d.rackno,d.active from it_items i,it_categories ctg,it_ck_designs d where i.design_no= '1532' and i.ctg_id = d.ctg_id and i.ctg_id=ctg.id and i.design_no=d.design_no  and i.barcode in (select c.barcode from it_current_stock c, it_items i where c.barcode = i.barcode $storeClause ) group by i.ctg_id ";
                echo $query;
                $allDesigns = $db->fetchObjectArray($query);
//               }
                // new_qry = select c.barcode ,i.style_id,i.size_id,i.ctg_id from it_current_stock c , it_items i  where c.barcode in (select barcode from it_items where design_no = '30611') and c.barcode = i.barcode  group by barcode;

            }
            ?>

    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset class="login">
            <legend>Design Search</legend>
            <b>Select Store*:</b><br/>
            <select name="store" id="store" data-placeholder="Choose Store" class="chzn-select" multiple style="width:75%;">
                <?php
                   if((! isset($this->storeidreport) && trim($this->storeidreport) == "" ) || ( $this->storeidreport == -1 )){
                   $defaultSel = "selected";
                  }else{ $defaultSel = ""; }
                ?>
                <option value="-1" <?php echo $defaultSel;?> >All Stores</option> 
                <?php
                $objs = $db->fetchObjectArray("select * from it_codes where usertype= ".UserType::Dealer." and inactive = 0 ");

                if ($this->storeidreport == "-1") {
                    $storeid = array();
                    $allstoreArrays = $db->fetchObjectArray("select id from it_codes where usertype = ".UserType::Dealer );
                    foreach ($allstoreArrays as $storeArray) {
                        foreach ($storeArray as $store) {
                            array_push($storeid, $store);
                        }
                    }
                } else {
                    $storeid = explode(",", $this->storeidreport);
                }

                foreach ($objs as $obj) {
                    $selected = "";
//	if (isset($this->storeidreport) && $obj->id==$this->storeidreport) { $selected = "selected"; }
                    if (isset($this->storeidreport)) {
                        foreach ($storeid as $sid) {
                            if ($obj->id == $sid) {
                                $selected = "selected";
                            }else{ $selected = "";}
                        }
                    }
                    ?>
                    <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?>><?php echo $obj->store_name; ?></option> 
<?php } ?>
            </select><br/>
            Select Production Type:<br/>
                 <?php
                   $pTypequery = "select * from it_prod_types order by name ";
                   $prodObjs = $db->fetchObjectArray($pTypequery);
                   $ptypeIds = explode(",",$this->ptype);
                 ?>
                 <select name="prod_type" id="prod_type" data-placeholder="Choose Production Type" class="chzn-select" multiple style="width:75%;">
                     <option value="0">ALL Production Types</option> 
                     <?php foreach($prodObjs as $obj){
                         $selected = "";
                         if ($this->ptype != 0) {
                              foreach ($ptypeIds as $pid) {
                                  if ($obj->id == $pid) {
                                      $selected = "selected";
                                  }
                              }
                          }
                     ?>                           
                     <option value="<?php echo $obj->id;?>" <?php echo $selected; ?>><?php echo $obj->name; ?></option>
                     <?php } ?>
                 </select>
            <p class="notice">Enter the Design Number and press [Enter] or click on the [Search] button</p>
            <p>
                <label>Date Range : </label> <input type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click in the box to see date options)

            </p>
            <p>
                <label>Design Number: </label>
                <input type="text" id="design_no" style="width:170px;" name="design_no" value="<?php echo $this->param_design_no; ?>" >
                <button id="searchBtn" onclick="search()">Search</button>
            </p>
<?php  
if ($this->param_design_no != "" && count($allDesigns) == 0) { ?>
	<p class="error">No Designs found matching "<?php echo $this->param_design_no; ?>"</p>	
<?php } ?>
        </fieldset>
    </div>

            <?php
            $row_no = 0;
            //select o.store_id,i.design_no,i.ctg_id,i.MRP,ctg.name as category,d.image,d.lineno,d.rackno,d.active,sum(oi.quantity) as salesqty,i.style_id,i.size_id from it_items i,it_categories ctg,it_ck_designs d,it_orders o , it_order_items oi  where i.design_no='10' and i.ctg_id = d.ctg_id and i.ctg_id=ctg.id and i.design_no=d.design_no and oi.order_id = o.id and o.store_id = 92  group by i.ctg_id,i.style_id,i.size_id;
            if($allDesigns){
            foreach ($allDesigns as $design) {
                $row_no++;
                $design_no = $db->safe($design->design_no);
		$ctg_id = $db->safe($design->ctg_id);
                $barcode = $db->safe($design->barcode);

                //$query = "select sum(curr_qty) as total from it_items where i.design_no = $design_no and i.MRP=$design->MRP and i.ctg_id = $ and i.style_id = t.style_id group by i.design_no, i.MRP";
                //$obj = $db->fetchObject($query);

                //if (!$obj) {
                //    continue;
                //}
                
                $state = $design->active ? "ACTIVE" : "INACTIVE";
                $divid = "accordion-" . $row_no;
                $prodObj = $db->fetchObjectArray("select name from it_prod_types where id in ($this->ptype)");                            
//                echo "select name from it_prod_types where id in ($this->ptype)";
                $prod_name = "";
                if($prodObj){
                    foreach($prodObj as $prod){$prod_name .= $prod->name.",";}
                    $prod_name = substr($prod_name, 0, -1);
                }
                ?>
    <div class="clear"></div>
    <div class="box">
        <h2 class="expand"><?php echo $design->category; ?> [<?php echo $state; ?>] Design No: <?php echo $design->design_no." [ MRP: ".$design->MRP." ]"; ?>  [Production Type: <?php if($this->ptype == "0"){ echo "ALL PRODUCTION TYPE";}else{ echo $prod_name; } ?>]</h2> <!--[ Line No: <?php // echo $design->lineno; ?> ] [ Rack No: <?php // echo $design->rackno; ?> ]-->
        <div class="collapse" id="<?php echo $divid; ?>">
<?php if ($design->image) { ?>
                <a href="images/stock/<?php echo $design->image; ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="images/stock/<?php echo $design->image; ?>" width="170" /></a>
<?php } else { ?>
                <a href="images/stock/<?php echo $design->image; ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="images/stock/nophoto.jpeg" width="170" /></a>
<?php } ?>

                <div class="block grid_10">
                    <label>Current Stock</label>
                    <table>
                                    <?php
                                    $styleobj = $db->fetchObjectArray("select s1.style_id, s2.name as style_name from it_ck_styles s1, it_styles s2 where s1.style_id=s2.id and s2.is_active = 1 and  ctg_id=$ctg_id order by sequence");
                                    $no_styles = count($styleobj);
                                    $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1, it_sizes s2 where s1.size_id = s2.id and ctg_id=$ctg_id order by sequence");
                                    $no_sizes = count($sizeobj);
                                    ?>
                        <thead>
                            <tr><th></th>
                                            <?php
                                            $width = intval(100/($no_sizes+1));
                                            for ($i = 0; $i < $no_sizes; $i++) {
                                                print '<th style="text-align:left;" width="'.$width.'%">';
                                                echo $sizeobj[$i]->size_name;
                                                print "</th>";  //print sizes
                                            }
                                            ?>
                            </tr>
                        </thead>
                        <tbody>
                                        <?php
                                        //for each unique style print style
                                        for ($k = 0; $k < $no_styles; $k++) {
                                        //print style names
                                            print "<tr><th>";
                                            echo $styleobj[$k]->style_name;
                                            print"</th>";
                                            //store style id in $stylecod
                                            $stylcod = $styleobj[$k]->style_id;
                                            for ($i = 0; $i < $no_sizes; $i++) {
                                                ?><td><?php
                                                        $sizeid = $sizeobj[$i]->size_id;
							//$query = "select id,sum(curr_qty) as qty from it_items where design_no = $design_no and MRP=$design->MRP and ctg_id=$ctg_id and style_id = $stylcod and size_id = $sizeid";
                                                        $currstkqry = "select c.id,sum(c.quantity) as qty from it_current_stock c, it_items i where c.barcode = i.barcode and c. barcode = $barcode and i.style_id = $stylcod and i.size_id = $sizeid $storeClause";
                                                        //print $currstkqry;
                                                        $getitm = $db->fetchObject($currstkqry);

                                                        //check to see if specific item exists in store stock, if exist -> stores qty in order_qty  
                                                        if (isset($getitm)) {
                                                                print "[ $getitm->qty ]";
                                                        }
                                                ?></td><?php
                                            }
                                            print "</tr>";
                                        }
                                        ?>
                        </tbody>
                    </table><Br>
                    
                    <label>Sales Quantity with in date range selected</label>
                    <table>
                        <thead>
                            <tr><th></th>
                                            <?php
                                            $width = intval(100/($no_sizes+1));
                                            for ($i = 0; $i < $no_sizes; $i++) {
                                                print '<th style="text-align:left;" width="'.$width.'%">';
                                                echo $sizeobj[$i]->size_name;
                                                print "</th>";  //print sizes
                                            }
                                            ?>
                            </tr>
                        </thead>
                        <tbody>
                                        <?php
                                        //for each unique style print style
                                        for ($k = 0; $k < $no_styles; $k++) {
                                        //print style names
                                            print "<tr><th>";
                                            echo $styleobj[$k]->style_name;
                                            print"</th>";
                                            //store style id in $stylecod
                                            $stylcod = $styleobj[$k]->style_id;
                                            for ($i = 0; $i < $no_sizes; $i++) {
                                                ?><td><?php
                                                        $sizeid = $sizeobj[$i]->size_id;
                                                        $query = "select id from it_items where design_no = $design_no and MRP=$design->MRP and ctg_id=$ctg_id and style_id = '$stylcod' and size_id = '$sizeid'";
                                                        $getitm = $db->fetchObject($query);//check to see if specific item exists in order, if exist -> stores qty in order_qty  
                                                        if ($getitm) {
                                                            //echo "select sum(oi.order_qty) as sum_qty from it_ck_orderitems oi,it_ck_orders o,it_ck_pickgroup p where oi.item_id=$getitm->id and oi.order_id=o.id and o.status = 3 and o.id in (p.order_ids) $dQuery";
                                                              $saleqry = "select sum(oi.quantity) as sum_qty from it_order_items oi,it_orders o where oi.order_id = o.id and oi.item_id=$getitm->id and oi.order_id=o.id  $dQuery";
                                                              $exist = $db->fetchObject($saleqry);
                                                              if (isset($exist)) {
                                                                print "[ $exist->sum_qty ]";
                                                              }
                                                        }
                                                ?></td><?php
                                            }
                                            print "</tr>";
                                        }
                                        ?>
                        </tbody>
                    </table>
                    <?php if ($usertype=="0" || $usertype=="1" || $usertype=="3") {  ?>
                    <form name="order_<?php echo $row_no; ?>" method="post" action="">
                        <input type="hidden" id="ctgid<?php echo $row_no?>" name="category" value="<?php echo $design->ctg_id; ?>" />
                        <input type="hidden" id="desno<?php echo $row_no?>" name="design_no" value="<?php echo $design->design_no; ?>" />
                        <input type="hidden" name="active" value="<?php echo $state; ?>" />
                        <div id="status_<?php echo $row_no; ?>"></div>
                        <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>                           
                    </form>
                    <?php } ?>
                </div> <!-- end class=grid_10 --><div class="clear"></div>
        </div> <!-- end class="block" -->
    </div> <!-- end class="box" -->

    <div class="clear"></div>
            <?php
    } }
            ?>
</div>
    <?php
    }
}
?>

