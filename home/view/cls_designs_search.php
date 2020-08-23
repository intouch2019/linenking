<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";

class cls_designs_search extends cls_renderer {

    var $params;
    var $result;
    var $param_design_no;
    var $currUser;

    function __construct($params=null) {
	//parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Dispatcher, UserType::Manager));
        $this->params = $params;
        $this->currUser = getCurrUser();
        if (isset($params['dno']))
            $this->param_design_no = $params['dno'];
        if (isset($_SESSION['design_dtrange'])) { $this->dtrange = $_SESSION['design_dtrange']; }
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
    <script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
    <link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
    <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
<script type="text/javascript" src="js/expand.js"></script>
<link rel="stylesheet" type="text/css" href="css/dark-glass/sidebar.css" />
<script type="text/javascript" src="js/sidebar/jquery.sidebar.js"></script>
<link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
<script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
    <!--//--><![CDATA[//><!--
    $(function() {
        $('#dateselect').datepicker({
	 	dateFormat: 'dd-mm-yy',
		arrows:false,
		closeOnSelect:true,
		onOpen: function() { isOpen=true; },
		onClose: function() { isOpen=false; },
		onChange: function() {
		if (isOpen) { return; }
		var dtrange = $("#dateselect").val();
		$.ajax({
			url: "savesession.php?name=design_dtrange&value="+dtrange,
			success: function(data) {}
		});
		}
	});
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
     });
    //--><!]]>

    function search()
    {
        var dtrange = $("#dateselect").val();
        $.ajax({
                url: "savesession.php?name=design_dtrange&value="+dtrange,
                success: function(data) {
                    var design_no=document.getElementById("design_no").value;
                    window.location.href="designs/search/dno="+design_no;
                }
        });
    }
    
    function activate(form_id) {
            var formname=eval("order_"+form_id);
            var params = $(formname).serialize();
            var ajaxUrl = "ajax/activateDesign.php?"+params;
            //alert(ajaxUrl);
            $.getJSON(ajaxUrl, function(data){
                if (data.error == "0") {
                    $("#status_"+form_id).removeClass().addClass("success");
                } else {
                    $("#status_"+form_id).removeClass().addClass("error");
                }
                $("#status_"+form_id).html(data.message);
            });

            return false;
    }

</script>


    <?php
    }

    //extra-headers close

    public function pageContent() {
        $formResult = $this->getFormResult();
	$menuitem = "designsearch";
        include "sidemenu.".$this->currUser->usertype.".php";
        $usertype = $this->currUser->usertype;
        $db = new DBConn();
        $allDesigns = array();
        ?>

<div class="grid_10">
            <?php   
            if ($this->param_design_no) {
		$code = $db->safe(trim($this->param_design_no));
                $dtarr = explode(" - ", $this->dtrange);
                    if (count($dtarr) == 1) {
                            list($dd,$mm,$yy) = explode("-",$dtarr[0]);
                            $sdate = "$yy-$mm-$dd";
                            $dQuery = " and date(p.shipped_time) > '$sdate'";
                    }/* else if (count($dtarr) == 2) {
                            list($dd,$mm,$yy) = explode("-",$dtarr[0]);
                            $sdate = "$yy-$mm-$dd";
                            list($dd,$mm,$yy) = explode("-",$dtarr[1]);
                            $edate = "$yy-$mm-$dd";
                            $dQuery = " and date(p.shipped_time) >= '$sdate' and date(p.shipped_time) <= '$edate'";
                    }*/ else {
                            $dQuery = "";
                    }
                $query = "select i.design_no,i.ctg_id,i.MRP,i.is_design_mrp_active,ctg.name as category,d.image,d.lineno,d.rackno,d.active,sum(i.curr_qty) from it_items i,it_categories ctg,it_ck_designs d where i.design_no=$code  and i.ctg_id = d.ctg_id and i.ctg_id=ctg.id and i.design_no=d.design_no group by i.ctg_id,i.design_no,MRP"; //and d.active=1
//                echo $query;
                $allDesigns = $db->fetchObjectArray($query);
            }
            ?>

    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset class="login">
            <legend>Design Search</legend>
            <p class="notice">Enter the Design Number and press [Enter] or click on the [Search] button</p>
            <p>
                <label>Date From : </label> <input type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" /> (Click in the box to see date options)

            </p>
            <p>
                <label>Design Number: </label>
                <input type="text" id="design_no" style="width:170px;" name="design_no" value="<?php echo $this->param_design_no; ?>" >
                <button id="searchBtn" onclick="search()">Search</button>
            </p>
<?php if ($this->param_design_no != "" && count($allDesigns) == 0) { ?>
	<p class="error">No Designs found matching "<?php echo $this->param_design_no; ?>"</p>	
<?php } ?>
        </fieldset>
    </div>

            <?php
            $row_no = 0;

            foreach ($allDesigns as $design) {
                $row_no++;
                $design_no = $db->safe($design->design_no);
		$ctg_id = $db->safe($design->ctg_id);

                //$query = "select sum(curr_qty) as total from it_items where i.design_no = $design_no and i.MRP=$design->MRP and i.ctg_id = $ and i.style_id = t.style_id group by i.design_no, i.MRP";
                //$obj = $db->fetchObject($query);

                //if (!$obj) {
                //    continue;
                //}
                
                $state = $design->is_design_mrp_active ? "ACTIVE" : "INACTIVE";
                $divid = "accordion-" . $row_no;
                ?>
    <div class="clear"></div>
    <div class="box">
        <h2 class="expand"><?php echo $design->category; ?> [<?php echo $state; ?>] Design No: <?php echo $design->design_no." [ MRP: ".$design->MRP." ]"; ?> [ Line No: <?php echo $design->lineno; ?> ] [ Rack No: <?php echo $design->rackno; ?> ]</h2>
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
                                    $styleobj = $db->fetchObjectArray("select s1.style_id, s2.name as style_name from it_ck_styles s1, it_styles s2 where s1.style_id=s2.id and ctg_id=$ctg_id and s2.is_active = 1 order by sequence");
//                                    echo "<br/>select s1.style_id, s2.name as style_name from it_ck_styles s1, it_styles s2 where s1.style_id=s2.id and ctg_id=$ctg_id and s2.is_active = 1 order by sequence<br/>";
                                    $no_styles = count($styleobj);
//                                    print_r($styleobj);
                                    $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1, it_sizes s2 where s1.size_id = s2.id and ctg_id=$ctg_id order by sequence");
                                    $no_sizes = count($sizeobj);
//                                    print_r($sizeobj);
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
							$query = "select id,sum(curr_qty) as qty from it_items where design_no = $design_no and MRP=$design->MRP and ctg_id=$ctg_id and style_id = $stylcod and size_id = $sizeid and curr_qty > 0 ";
//                                                        echo "<br/>$query<br/>";
                                                        $getitm = $db->fetchObject($query);

                                                        //check to see if specific item exists in order, if exist -> stores qty in order_qty  
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
                    
                    <label>Ordered Quantity From Chosen Date to Current Date</label>
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
                                                              $exist = $db->fetchObject("select sum(oi.order_qty) as sum_qty from it_ck_orderitems oi,it_ck_orders o,it_ck_pickgroup p where oi.item_id=$getitm->id and oi.order_id=o.id and o.status = 3 and concat(',',p.order_ids,',') like concat('%,',o.id,',%') $dQuery");
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
                    <?php if ($usertype=="0" || $usertype=="1" || $usertype=="3" || $usertype=="2") {  ?>
                    <form name="order_<?php echo $row_no; ?>" method="post" action="">
                        <input type="hidden" id="ctgid<?php echo $row_no?>" name="category" value="<?php echo $design->ctg_id; ?>" />
                        <input type="hidden" id="desno<?php echo $row_no?>" name="design_no" value="<?php echo $design->design_no; ?>" />
                       <input type="hidden" id="mrp<?php echo $row_no?>" name="mrp" value="<?php echo $design->MRP; ?>" />
                        <input type="hidden" name="active" value="<?php echo $state; ?>" />
                        <div id="status_<?php echo $row_no; ?>"></div>
                        <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                        <?php if ($state == "ACTIVE") { ?>
                        <button onclick="activate(<?php echo $row_no; ?>); return false;"style="float:right;">Deactivate</button>
                        <?php } else if ( $state == "INACTIVE" ){ ?>
                        <button onclick="activate(<?php echo $row_no; ?>); return false;"style="float:right;">Activate</button>
                        <?php } ?>    
                    </form>
                    <?php } ?>
                </div> <!-- end class=grid_10 --><div class="clear"></div>
        </div> <!-- end class="block" -->
    </div> <!-- end class="box" -->

    <div class="clear"></div>
            <?php
            }
            ?>
</div>
    <?php
    }
}
?>
