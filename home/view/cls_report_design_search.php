<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";

class cls_report_design_search extends cls_renderer {

    var $params;
    var $result;
    var $param_design_no;
    var $dtrange = null,$ptype = null;
    var $storeidreport = null;
    var $currUser;
    function __construct($params=null) {
	//parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Dispatcher, UserType::Manager));
        $this->params = $params;
        $this->currUser = getCurrUser();
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
        var store_id =$('#store').val();
        if(store_id == null){
                    alert("Please Select a store");
        }else{
            $.ajax({
                    url: "savesession.php?name=design_dtrange&value="+dtrange,
                    success: function(data) {
                        var design_no=document.getElementById("design_no").value;
                        //var store_id =document.getElementById("store") .value;

                        //alert(store_id);
                      //  var ptype_id = document.getElementById("prod_type").value;
                        var ptype_id = $("#prod_type").val();

                            var addstore="",addptype="";
                            if(store_id != -1){ addstore = "/storeid="+store_id;}
                            if(ptype_id != 0){ addptype = "/ptype="+ptype_id;}
                            window.location.href="report/design/search/dno="+design_no+addstore+addptype;

                    }
            });
        }
    }
    
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
        $storeClause="";$pClause="";$sClause="";$ptab="";$pchk="";
        ?>

<div class="grid_10">
            <?php   
            if ($this->param_design_no) {
		$code = $db->safe(trim($this->param_design_no));
                $dtarr = explode(" - ", $this->dtrange);
                    if (count($dtarr) == 1) {
                            list($dd,$mm,$yy) = explode("-",$dtarr[0]);
                            $sdate = "$yy-$mm-$dd";
                            $dQuery = " and o.bill_datetime > '$sdate 00:00:00'";
                    }else if (count($dtarr) == 2) {
                            list($dd,$mm,$yy) = explode("-",$dtarr[0]);
                            $sdate = "$yy-$mm-$dd";
                            list($dd,$mm,$yy) = explode("-",$dtarr[1]);
                            $edate = "$yy-$mm-$dd";
                            $dQuery = " and o.bill_datetime >= '$sdate 00:00:00' and o.bill_datetime <= '$edate 23:59:59'";
                    } else {
                            $dQuery = "";
                    }
                //$query = "select i.design_no,i.ctg_id,i.MRP,ctg.name as category,d.image,d.lineno,d.rackno,d.active,sum(i.curr_qty) from it_items i,it_categories ctg,it_ck_designs d where i.design_no=$code and i.ctg_id = d.ctg_id and i.ctg_id=ctg.id and i.design_no=d.design_no group by i.ctg_id,i.design_no,MRP";
               if(isset($this->storeidreport) && trim($this->storeidreport) != "" && $this->storeidreport != -1 ){
                   $storeClause = " and c.store_id in ( $this->storeidreport ) ";
                   $sClause = " and o.store_id in ( $this->storeidreport ) ";
               }else if($this->storeidreport == "-1" || $this->storeidreport == ""){
                       $storeClause = " and o.store_id in ( select store_id from executive_assign where exe_id=".getCurrUser()->id." ) ";
                    $sClause = " and o.store_id in ( select store_id from executive_assign where exe_id=".getCurrUser()->id." ) ";
                       
               }else{ $storeClause = "";$sClause = "";}
               if($this->ptype != 0){
                  $ptab  = " ,it_items i";
                  $pchk= " and oi.item_id = i.id and i.prod_type_id in ( $this->ptype ) ";
                  $pClause = " and i.prod_type_id in ( $this->ptype ) ";
               }
              
                $query = " select c.barcode, sum(c.quantity) as currqty,i.barcode,i.ctg_id,i.MRP,i.style_id,i.size_id,i.prod_type_id ,i.design_no,i.is_design_mrp_active ,d.active,d.image,d.lineno,d.rackno from it_current_stock c, it_items i , it_ck_designs d  where c.barcode = i.barcode $storeClause and i.design_no = d.design_no and i.ctg_id = d.ctg_id and  i.design_no = $code $pClause  group by i.ctg_id ";
//                print $query;
                $allDesigns = $db->fetchObjectArray($query);

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
                $objs = $db->fetchObjectArray("select * from it_codes where usertype= ".UserType::Dealer." and inactive = 0 and id in (select store_id from executive_assign where exe_id=".getCurrUser()->id." )");

                if ($this->storeidreport == "-1") {
                    $storeid = array();
                    $allstoreArrays = $db->fetchObjectArray("select id from it_codes where usertype = ".UserType::Dealer." and id in (select store_id from executive_assign where exe_id=".getCurrUser()->id." )" );
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
                    if ($this->storeidreport != -1) {
                        foreach ($storeid as $sid) {
                            if ($obj->id == $sid) {
                                $selected = "selected";
                            }
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
                     <?php if($this->ptype == 0){$defaultPSel = "selected";}else{ $defaultPSel = ""; } ?>
                     <option value="0" <?php echo $defaultPSel; ?> >ALL Production Types</option> 
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
            <p class="notice">Select Store & other options and enter the Design Number and  click on the [Search] button</p>
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
	<p class="error">No records found matching "<?php echo $this->param_design_no; ?>" Select different options and try again</p>
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
                $state = $design->is_design_mrp_active ? "ACTIVE" : "INACTIVE";
                $divid = "accordion-" . $row_no;
                  $prod_name = "";
                if(isset($this->ptype) && trim($this->ptype) != "" && $this->ptype != 0 ){
                $prodObj = $db->fetchObjectArray("select name from it_prod_types where id in ($this->ptype)");                            
    //                echo "select name from it_prod_types where id in ($this->ptype)";
                  
                    if($prodObj){
                        foreach($prodObj as $prod){$prod_name .= $prod->name.",";}
                        $prod_name = substr($prod_name, 0, -1);
                    }
                }
                $ctgobj = $db->fetchObject("select name from it_categories  where id = $design->ctg_id ");
                if($ctgobj){ $category = $ctgobj->name;}else{ $category = "";}
                ?>
    <div class="clear"></div>
    <div class="box">
        <h2 class="expand"><?php echo $category; ?> [<?php echo $state; ?>] Design No: <?php echo $design->design_no." [ MRP: ALL ]"; ?>  [Prod Type: <?php if( ($this->ptype == null) || ($this->ptype == "0")){ echo "ALL ";}else{ echo $prod_name; } ?>]</h2> <!--[ Line No: <?php // echo $design->lineno; ?> ] [ Rack No: <?php // echo $design->rackno; ?> ]-->
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
                                                       // $currstkqry = "select c.id,sum(c.quantity) as qty from it_current_stock c, it_items i where c.barcode = i.barcode and c. barcode = $barcode and i.style_id = $stylcod and i.size_id = $sizeid $storeClause";
                                                        //print $currstkqry;
                                                        $currstkqry = "select sum(c.quantity) as qty from it_current_stock c, it_items i  where c.barcode = i.barcode $storeClause and i.ctg_id = $ctg_id and i.style_id = ".$stylcod." and i.size_id = ".$sizeid."  $pClause ";
                                                        
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
                                                        //print "\nsale qry".$query."\n";
                                                        $getitm = $db->fetchObject($query);//check to see if specific item exists in order, if exist -> stores qty in order_qty
                                                        if ($getitm) {
                                                            //echo "select sum(oi.order_qty) as sum_qty from it_ck_orderitems oi,it_ck_orders o,it_ck_pickgroup p where oi.item_id=$getitm->id and oi.order_id=o.id and o.status = 3 and o.id in (p.order_ids) $dQuery";
                                                              $saleqry = "select sum(oi.quantity) as sum_qty from it_order_items oi,it_orders o $ptab where oi.order_id = o.id and oi.item_id=$getitm->id and oi.order_id=o.id  $dQuery $sClause $pchk ";
                                                              //print "\n$saleqry\n";
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