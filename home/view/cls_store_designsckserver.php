<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/orders/clsOrders.php";

class cls_store_designs extends cls_renderer {

    var $params;
    var $currStore;
    var $ctg;
    var $result;
    var $des_code="";
    var $MRP;
    var $size;
    var $size_group;

    function __construct($params=null) {
        $this->currStore = getCurrStore();
        $this->params = $params;
        if (isset($params['ctg']))
            $this->ctg = $params['ctg'];
        if (isset($params['dno']))
            $this->des_code = $params['dno'];
        if (isset($params['mrp']))
            $this->MRP = $params['mrp'];
        if (isset($params['size']))
            $this->size = $params['size'];
    }

    function extraHeaders() {
        if (!$this->currStore) {
            ?>
<h2>Session Expired</h2>
Your session has expired. Click <a href="">here</a> to login.
            <?php
            return;
        }
        ?>
<script type="text/javascript" src="js/expand.js"></script>
<link rel="stylesheet" type="text/css" href="css/dark-glass/sidebar.css" />
<script type="text/javascript" src="js/sidebar/jquery.sidebar.js"></script>
<link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
<script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
    <!--//--><![CDATA[//><!--
    $(function() {
        // --- Using the default options:
        //$("h2.expand").toggler();
        // --- Other options:
        //$("h2.expand").toggler({method: "toggle", speed: "slow"});
        //$("h2.expand").toggler({method: "toggle"});
        //$("h2.expand").toggler({speed: "fast"});
        //$("h2.expand").toggler({method: "fadeToggle"});
        $("h2.expand").toggler({method: "slideFadeToggle"});
        $("#content").expandAll({trigger: "h2.expand"});
	$("#srch").keyup(function(event){
		if(event.keyCode == 13){
			$("#searchBtn").click();
		}
	});
    });

    $(function(){
	$("a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'fast',slideshow:3000, hideflash: true});
     });
    //--><!]]>
    $(function() {
        $("ul#demo_menu1").sidebar({
            width : 160,
            height : 110,
            injectWidth : 50,
            events:{
                item : {
                    enter : function(){
                        $(this).find("a").animate({color:"red"}, 250);
                    },
                    leave : function(){
                        $(this).find("a").animate({color:"white"}, 250);
                    }
                }
            }
        });
    });

    function addToCart(theForm) {
        var formName = theForm.name;
        var arr = formName.split("_");
        var form_id = arr[1];
        var params = $(theForm).serialize();
        var ajaxUrl = "ajax/setOrder.php?"+params;
        $.getJSON(ajaxUrl, function(data){
//alert(data.error+":"+data.message+":"+data.cartinfo+":"+data.totqt+":"+data.totmrp);
            if (data.error == "0") {
                $("#status_"+form_id).removeClass().addClass("success");
            } else {
                $("#status_"+form_id).removeClass().addClass("error");
            }

            $("#status_"+form_id).html(data.message);
            $("#carttop").html(data.cartinfo);
            if (data.totqt) {
            $("#side_qty").html("QTY: "+data.totqt);
            $("#side_price").html("AMT: "+data.totmrp);
	    }
        });

        return false;
    }

    function setMRP(dropdown)
    {
        var idx = dropdown.selectedIndex;
        var value = dropdown.options[idx].value;
        window.location.href="store/designs/ctg=<?php echo $this->ctg; ?>/mrp="+value;
    }

    function setSize(dropdown)
    {
        var idx = dropdown.selectedIndex;
        var value = dropdown.options[idx].value;
        window.location.href="store/designs/ctg=<?php echo $this->ctg; ?>/size="+value;
    }

    function search()
    {
        var des_code=document.getElementById("srch").value;
        // var cat=document.getElementById("cat").value;
        // window.location.href="store/designs/dno="+des_code;
        //  return;
        var ajaxUrl ="ajax/getDesign.php?ctg_id=<?php echo $this->ctg; ?>"
        ajaxUrl += "&design_no=" + des_code;
        if (des_code)
        {$.getJSON(ajaxUrl, function(data){
                if (data.error == "0")
                { window.location.href = data.redirect; }
                else
                { alert(data.message); }
            });
        }
        else
        { alert ("Please enter a design code"); }
    }

</script>

    <?php
    }

    //extra-headers close

    public function pageContent() {
        $menuitem = $this->ctg;
        include "sidemenu.".$this->currStore->usertype.".php";
        ?>

<div class="grid_10">
            <?php
            include "cartinfo.php";

            $db = new DBConn();
            $ctg_id = $db->safe($this->ctg);
	    $query = "select i.MRP, sum(i.curr_qty) as tot_qty from it_ck_items i,it_ck_designs d where i.ctg_id=$ctg_id and i.ctg_id=d.ctg_id and i.design_no=d.design_no and d.active=1 group by i.MRP having tot_qty > 0";
            $allprices = $db->fetchObjectArray($query);
            ?>
    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset class="login">
            <legend><?php echo $g_categories[$this->ctg]; ?></legend>
            <p class="notice">Select by MRP or Search by Design Number</p>
            <p>
                <label>Select MRP: </label>
    
                <select class="setprice" name ="setprice" style="width:160px" onchange="setMRP(this);">
                    <option value=0 selected="selected">Select Price</option>
            <?php
                            foreach ($allprices as $price) {
                                $selected = "";
				if ($price->tot_qty <= 0) { continue; }
                                if ($price->MRP == $this->MRP) {
                                    $selected = "selected";
                                }
                                ?>
                    <option value="<?php echo $price->MRP; ?>" <?php echo $selected; ?>><?php echo "Rs. $price->MRP [$price->tot_qty units]"; ?></option>
                            <?php
                            }
                            ?>
                </select>
            </p>
            <p>
                <label>OR Select Size: </label>
    
                <select class="setprice" name ="setsize" style="width:160px" onchange="setSize(this);">
                    <option value=0 selected="selected">Select Size</option>
            <?php
			$objs = $db->fetchObjectArray("select * from it_ck_sizes where ctg_id=$ctg_id order by display_name");
                            foreach ($objs as $obj) {
                                $selected = "";
				
				$size_group = $obj->size_group ? $obj->size_group : "'".$obj->size_id."'";
                                if ($obj->display_name == $this->size) {
					$selected = "selected";
					$this->size_group = $size_group;
                                }
	    			$obj2 = $db->fetchObject("select sum(i.curr_qty) as tot_qty from it_ck_items i,it_ck_designs d where i.ctg_id=$ctg_id and i.ctg_id=d.ctg_id and i.design_no=d.design_no and d.active=1 and i.size_id in ($size_group)");
				$units = 0;
				if ($obj2 && $obj2->tot_qty) { $units = $obj2->tot_qty; }
                                ?>
                    <option value="<?php echo $obj->display_name; ?>" <?php echo $selected; ?>><?php echo "$obj->display_name [$units units]"; ?></option>
                            <?php
                            }
                            ?>
                </select>
            </p>
            <p>
                <label>OR by Design Number: </label>
                <input type="text" id="srch" style="width:170px;" name="srch" value="<?php echo $this->des_code; ?>" >
                <button id="searchBtn" onclick="search()">Search</button>
            </p>
        </fieldset>
    </div>
            <?php
            // choose high and low prices from filter table for each design category.
$t1 = microtime(true);
            $code = $db->safe($this->des_code);
            if ($this->des_code) {
                $allDesigns = $db->fetchObjectArray("select i.*,d.image,d.prod_type,sum(i.curr_qty) as tot_qty from it_ck_items i,it_ck_designs d where i.ctg_id=$ctg_id and i.ctg_id=d.ctg_id and i.design_no=$code and d.active=1 and i.design_no=d.design_no group by i.design_no,MRP having tot_qty > 0");
            } else if ($this->MRP && $this->ctg) {
                    $query = "Select i.*,d.image,d.prod_type,sum(i.curr_qty) as tot_qty from it_ck_items i,it_ck_designs d where i.ctg_id=$ctg_id and i.MRP=$this->MRP and i.ctg_id=d.ctg_id and i.design_no=d.design_no and d.active=1 group by i.design_no having tot_qty > 0 order by tot_qty";
                    $allDesigns = $db->fetchObjectArray($query);
            } else if ($this->size_group && $this->ctg) {
		    $query = "Select i.*,d.image,d.prod_type,sum(i.curr_qty) as tot_qty from it_ck_items i,it_ck_designs d where i.ctg_id=$ctg_id and i.size_id in ($this->size_group) and i.ctg_id=d.ctg_id and i.design_no=d.design_no and d.active=1 group by i.design_no,i.MRP having tot_qty > 0 order by tot_qty, MRP";
                    $allDesigns = $db->fetchObjectArray($query);
            } else {
                $allDesigns = array();
            }
$t2 = microtime(true);
$time1=$t2-$t1;
            ?>

                                    <?php
                                    $styleobj = $db->fetchObjectArray("select style_id,shortcode,display_name as style_name from it_ck_styles where ctg_id=$ctg_id order by sequence");
                                    $no_styles = count($styleobj);
                                    $sizeobj = $db->fetchObjectArray("select size_id, shortcode, display_name as size_name,size_group from it_ck_sizes where ctg_id=$ctg_id order by sequence");
                                    $no_sizes = count($sizeobj);
                                    ?>
            <?php
$time2=0;
$time3=0;
            $row_no = 0;
            foreach ($allDesigns as $design) {
                $row_no++;
                $design_no = $db->safe($design->design_no);

/*
                $query = "select sum(i.curr_qty) as total from it_ck_items i, it_ck_styles t where i.ctg_id=$ctg_id and i.design_no = $design_no and i.MRP=$design->MRP and i.ctg_id = t.ctg_id and i.style_id = t.style_id group by i.design_no, i.MRP";
                $obj = $db->fetchObject($query);

                if (!$obj || !$obj->total) {
                    continue;
                }
*/
                $divid = "accordion-" . $row_no;
                ?>
    <div class="clear"></div>
    <div class="box">
        <h2 class="expand">Design No: <?php echo $design->design_no; if ($design->prod_type) { echo " [ Production Type: ".$design->prod_type." ]"; } echo " [ MRP: ".$design->MRP." ] [ Total Qty: $design->tot_qty ]"; ?></h2>
        <div class="collapse" id="<?php echo $divid; ?>">
            <form name="order_<?php echo $row_no; ?>" method="post" action="" onsubmit="addToCart(this); return false;">
                <input type="hidden" name="ctg_id" value="<?php echo $this->ctg; ?>" />
                <input type="hidden" name="design_no" value="<?php echo $design->design_no; ?>" />
                <input type="hidden" name="mrp" value="<?php echo $design->MRP; ?>" />
                <a href="images/stock/<?php echo $design->image; ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="images/stock/<?php echo $design->image; ?>" width="170" /></a>

                <div class="block grid_10">
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
                                            print "<tr id='styles::".$design->id."::".$styleobj[$k]->style_id."'><th>";
                                            echo $styleobj[$k]->style_name;
                                            print"</th>";
                                            //store style id in $stylecod
                                            $stylcod = $styleobj[$k]->style_id;
                                            for ($i = 0; $i < $no_sizes; $i++) {
                                                ?><td><?php
                                                        //to get the quantity and stock id of specific item
                                                        $size_group= isset($sizeobj[$i]->size_group) ? $sizeobj[$i]->size_group : "'".$sizeobj[$i]->size_id."'";
                                                        //echo $size_group;
							$query = "select *,sum(curr_qty) as qty from it_ck_items where design_no = $design_no and MRP=$design->MRP and ctg_id=$ctg_id and style_id = '$stylcod' and size_id in ($size_group)";
$t1 = microtime(true);
                                                        $getitm = $db->fetchObject($query);
$t2 = microtime(true);
$time2 += ($t2-$t1);
                                                        $fname = "qty_" . $styleobj[$k]->style_id . "_" . $sizeobj[$i]->size_id;
                                                        //check to see if specific item exists in order, if exist -> stores qty in order_qty  
                                                        if ($getitm) {
                                                            $db_design_no = $db->safe($getitm->design_no);
//                                                            $query = "select order_qty from it_ck_orderitems where order_id=$cart->id and design_no=$db_design_no and ctg_id=$getitm->ctg_id and style_id=$getitm->style_id and size_id=$getitm->size_id";
                                                            $query = "select order_qty from it_ck_orderitems where order_id=$cart->id and design_no=$db_design_no and ctg_id=$getitm->ctg_id and style_id=$getitm->style_id and size_id in ($size_group)";
$t1 = microtime(true);
                                                            $exist = $db->fetchObject($query);
/*
if ($getitm->design_no == '40882') {
print $query."::".print_r($exist,true)."<br />";
}
*/
$t2 = microtime(true);
$time3 += ($t2-$t1);
                                                            ?><input type='text' style='width: 30px;' id="<?php echo $fname; ?>" name="<?php echo $fname; ?>" <?php

                                                                if ($exist) {
                                                                    print "value='";
                                                                    echo $exist->order_qty;
                                                                    print "'";
                                                                }
                                                                ?>/><br>[ <?php echo $getitm->qty; ?> ]<?php
                                                        }
                                                ?></td><?php
                                            }
                                            print "</tr>";
                                        }
                                        ?>
                        </tbody>
                    </table>
                    <div id="status_<?php echo $row_no; ?>"></div>
                    <input class="blueglassbutton" type="submit" value="ADD TO CART"/>
                    <input class="blueglassbutton" type="reset" value="RESET"/>
                </div> <!-- end class=grid_10 -->
            </form>
            <br>
        </div> <!-- end class="block" -->
    </div> <!-- end class="box" -->

    <div class="clear"></div>
            <?php
                }
//print "$time1:$time2:$time3<br />";
            ?>
</div>
    <?php
    }
}
?>