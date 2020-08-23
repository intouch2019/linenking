<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/orders/clsOrders.php";
require_once "lib/items/clsItems.php";

class cls_store_designs_others extends cls_renderer {

    var $params;
    var $currStore;
    var $ctg;
    var $result;
    var $des_code="";
    var $MRP;
    var $size;
    var $size_group;
    var $brand="";

    function __construct($params=null) {
        $this->currStore = getCurrUser();
//        $this->params = $params;
//        if (isset($params['ctg']))
//            { $this->ctg = $params['ctg']; }
//        else 
//            { $this->ctg = null; }
//        if (isset($params['dno']))
//            $this->des_code = $params['dno'];
//        if (isset($params['mrp']))
//            $this->MRP = $params['mrp'];
//        if (isset($params['size']))
//            $this->size = $params['size'];
//        if (isset($params['brand']))
//            $this->brand = $params['brand'];
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
        //accodian dropdown
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
        //pretty photo pop up
	$("a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'fast',slideshow:3000, hideflash: true});
        
        //brand setting
        $("input:checkbox[name=brand]").click(function(){
            var array = new Array();
            $("input:checkbox[name=brand]:checked").each(function()
            {
                array.push($(this).val())
            });
            var len = array.length;
            if (len == 1) {
                var brand = array[0];
                window.location.href="store/designs/brand="+brand+"/ctg=<?php echo $this->ctg; ?>";
            } else {
                window.location.href="store/designs/ctg=<?php echo $this->ctg; ?>";
            }
        });
    //--><!]]>
        //sidebar
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
        //alert(params);
        var ajaxUrl = "ajax/setOrderOthers.php?"+params;
        $.getJSON(ajaxUrl, function(data){
            console.log(data);
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
        window.location.href="store/designs/brand=<?php echo $this->brand; ?>/ctg=<?php echo $this->ctg; ?>/mrp="+value;
    }

    function setSize(dropdown)
    {
        var idx = dropdown.selectedIndex;
        var value = dropdown.options[idx].value;
        window.location.href="store/designs/brand=<?php echo $this->brand; ?>/ctg=<?php echo $this->ctg; ?>/size="+value;
    }

    function search()
    {
        var des_code=document.getElementById("srch").value;
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
        $menuitem = 29; // category 'others' id is 29
        include "sidemenu.".$this->currStore->usertype.".php";
        ?>

<div class="grid_10">
           <?php 
               include "cartinfo.php";
               $db = new DBConn();
//               $query = "select d.* from it_ck_designs d where d.active=1 and d.ctg_id=(select id from it_categories where name = 'Others')";
              $query = "select d.*,i.id as item_id,i.MRP from it_ck_designs d, it_items i where i.is_design_mrp_active = 1 and d.ctg_id = i.ctg_id and d.design_no  = i.design_no  and d.ctg_id = 29";
               $allDesigns= $db->fetchObjectArray($query);
           
           ?>
           <?php
            $row_no = 0;
                foreach ($allDesigns as $design) {
                    $row_no++;
                    $design_no = $db->safe($design->design_no);
//                    $mrp = $db->safe($design->prod_type);
//                    $query = "select order_qty,remarks from it_ck_orderitems where order_id=$cart->id and design_no=$design_no and ctg_id=(select id from it_categories where name = 'Others')";
                    $query="select order_qty,remarks from it_ck_orderitems where order_id=$cart->id and design_no=$design_no";
                    $exist = $db->fetchObject($query);
                    ?>
                <div class="box">
                <h2 class="expand">Product : <?php echo $design->design_no;  ?> </h2>
                    <div class="collapse" id="<?php echo $divid; ?>">
                    <form name="order_<?php echo $row_no; ?>" method="post" action="" onsubmit="addToCart(this); return false;">
                        <input type="hidden" name="ctg_id" value="29" />
                        <input type="hidden" name="design_no" value="<?php echo $design->design_no; ?>" />
                        <input type="hidden" name="item_id" value="<?php echo $design->item_id; ?>" />
                        <input type="hidden" name="mrp" value="<?php echo $design->MRP; ?>" />
                        <a href="images/stock/<?php echo $design->image;?>" rel=""><img class="grid_2" align="left" src="images/stock/<?php echo $design->image?>" width="170" /></a>
                        <div class="block grid_10" style="margin-top:30px;">
                            <div id="status_<?php echo $row_no; ?>"></div>  
                            <label style="font-weight: bold; margin-left:15px;">Quantity * : </label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="quantity" style="width:35%;" <?php if ($exist) { print "value='".$exist->order_qty."'"; }?> ></input><br/><br/>                            
                            <label style="font-weight: bold; margin-left:15px;">Requirements * : </label><input type="text" name="remarks" style="width:35%;" <?php if ($exist) { print "value='".$exist->remarks."'"; }?> ></input>
                            <div id="status_<?php echo $row_no; ?>"></div><br>
                            <input class="greenbutton" type="submit" value="ADD TO CART"/>
                            <input class="greenbutton" type="reset" value="RESET"/>
                        </div> <!-- end class=grid_10 -->
                    </form>
                    <br>
                    </div> <!-- end class="block" -->
                </div>
    <?php       } ?>

    <div class="clear"></div>
    <div class="box">
        
        </div> <!-- end class="block" -->
    </div> <!-- end class="box" -->
    
    <div class="clear"></div>
            
</div>
    <?php
    }
}
?>
