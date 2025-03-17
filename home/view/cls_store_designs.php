<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/orders/clsOrders.php";
require_once "lib/items/clsItems.php";

class cls_store_designs extends cls_renderer {

    var $params;
    var $currStore;
    var $ctg;
    var $result;
    var $des_code="";
    var $MRP;
    var $size;
    var $size_group;
    var $brand="";
    var $reason="N_A";
    
    function __construct($params=null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (isset($params['ctg']))
            { $this->ctg = $params['ctg']; }
        else 
            { $this->ctg = null; }
            
             if (isset($params['showcurstck'])) {
            $this->showcurstck = $params['showcurstck'];
        } else {
            $this->showcurstck = 0;
        }
            
        if (isset($params['style'])) {
            $this->style = $params['style'];
        }

        if (isset($params['dno']))
            $this->des_code = $params['dno'];
        if (isset($params['mrp']))
            $this->MRP = $params['mrp'];
        if (isset($params['size']))
            $this->size = $params['size'];
        if (isset($params['brand']))
            $this->brand = $params['brand'];
         if ($this->currStore->inactive == 1) {
                $this->reason="You can not place Order Because, ".$this->currStore->inactivating_reason;
        }
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
    
    
           function setStyle(dropdown){
                var idx = dropdown.selectedIndex;
                var value = dropdown.options[idx].value;                
                window.location.href = "store/designs/brand=<?php echo $this->brand; ?>/ctg=<?php echo $this->ctg; ?>/mrp=<?php echo $this->MRP?>/style=" + value+"/size=<?php echo $this->size?>" ;
            }
    
    function validate(item_id)
            {
             var valid_id="id_"+item_id; 
//             alert(valid_id);
            var num =document.getElementById(valid_id).value; 
//            alert(num);
            if(num>10)
            {
                  alert("No of set/packet should not greater than 10. \n सेट / पॅकेटची संख्या 10 पेक्षा जास्त नसावी.");
                  document.getElementById(valid_id).value="";
                 }
            }
    
    
    
    function add_toCart()
            {
                var reason='<?php echo $this->reason; ?>';
               // alert(reason);
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
        
         var mrp = document.getElementById("setprice").value;
//        console.log(mrp);
        //alert(mrp);
        var mrpclause = "";
        if(mrp != null && mrp != 'undefined' && mrp != '0'){
            mrpclause = "/mrp="+mrp;
        }
        
        window.location.href="store/designs/brand=<?php echo $this->brand; ?>/ctg=<?php echo $this->ctg; ?>/size="+value+mrpclause;
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

            
            function showcurrStockview(){
                            let curretURL=window.location.href;
                                    if(!curretURL.includes("brand=")){
                                alert("Please Select MRP/Size/Style First");
                                
                                            }
                                            if(curretURL.includes("brand=")){
                                                if(!curretURL.includes("showcurstck")){
                                        let newURL=curretURL+(curretURL.endsWith("/") ? "showcurstck" : "/showcurstck=1");
                                        window.location.href=newURL;
                                    }
                                            }
                            
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
            $clsItems = new clsItems();
            $storeid = getCurrUserId();
            $clsOrders = new clsOrders();
            $query21 = "SELECT * from it_ck_orderitems where  order_id=$cart->id";
          //    print $query21;
            $datechecheck = $db->fetchObjectArray($query21);
          
             if($datechecheck){
              foreach ($datechecheck as $date){
                  $first = $date->createtime;
                  $item_id= $date->item_id;
                  $dt = new DateTime(); 
                  $last= $dt->format('Y-m-d H:i:s'); 
                  $hours = round((strtotime($last) - strtotime($first))/3600, 1);
                   //     echo 'hourssddd'.$hours;
                  if($hours>=48.00){
                         $query = "delete from it_ck_orderitems where order_id=$cart->id and store_id=$store_id and item_id=$item_id";
                         // echo 'Query'.$query;  
                         $db->execQuery($query);
	                 // update it_ck_orders
	                 $clsOrders->updateCartTotals($cart->id);
                         if($clsOrders){
                     
//                         echo "<script type=\"text/javascript\">window.alert('Your cart is empty..');
//                              window.location.href = 'store/designs/ctg= $this->ctg';
//                            </script>";
                               
                        }
        
                    }
    
                }
              
              
            }
            
            
            $category = $clsItems->getCategoryNameFromId($this->ctg);
            $ctg_id = $db->safe($this->ctg);
            //hardcoded zinon n linon values for queries zinon = 6 / linon = 5.; 
            if ($this->brand!="") { 
                if ($this->brand == "z") $brandquery = " and i.brand_id='6' " ;
                else $brandquery = " and i.brand_id='5' ";
            } else {
                $brandquery = "";
            }
            
            //$storetype=$this->currStore->store_type;
            if($this->currStore->store_type == StoreType::NormalStore){
                $stockclause= " and i.stock_type =  ".StockType::NormalStock;
            }
            
            
	    $query = "select i.MRP, sum(i.curr_qty) as tot_qty from it_items i,it_ck_designs d where i.ctg_id=$ctg_id $brandquery and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.is_design_mrp_active=1 and i.curr_qty > 0 group by i.MRP having tot_qty > 0";
            $db = new DBConn();
            $allprices = $db->fetchObjectArray($query);
            $db->closeConnection();
            ?>
     <div style="background-color: rgb(220,220,220); padding: 20px; border: 1px solid black; width: fit-content; border-radius: 5px;">
                        <div style="margin-bottom: 10px;">
                            <span style="display: inline-block; width: 50px; height: 5px; background-color: red; vertical-align: middle;"></span>
                            <span style="margin-left: 10px; vertical-align: middle;"><b> You have this Stock</b></span>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <span style="display: inline-block; width: 50px; height: 5px; background-color: green; vertical-align: middle;"></span>
                            <span style="margin-left: 10px; vertical-align: middle;" ><b>You can order, You Don't have this Stock</b></span>
                        </div>
                        <button id="availabilityButton" onclick="showcurrStockview()"> <b>Store to Warehouse Availability Report</b> </button>
                        <?php
                        $qrytimefetch=$db->fetchObject("select updatetime from it_current_stock where store_id=$storeid order by updatetime  desc limit 1");

                        ?>
                        <div id="timemessage" style="color: green; margin-top: 10px; text-align: right;">Last Sync Time <?php echo date("d-m-Y H:i:s", strtotime($qrytimefetch->updatetime)) ?></div>
                        <div id="message" style="color: red; margin-top: 10px;"></div>
                    </div>
    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset class="login">
            <legend><?php echo $category->name; ?></legend>
            <?php 
                $query = "select distinct(i.brand_id),b.name as brand from it_items i,it_brands b where i.ctg_id='$this->ctg' and i.brand_id=b.id group by brand_id";
                $db = new DBConn();
                $allbrands = $db->fetchObjectArray($query);
                $db->closeConnection();
                
            ?>
            <p class="notice">Select by MRP or Search by Design Number</p>
            <?php if ($this->ctg!=14) { /* Skip Tie's */ ?>
            <p>
                <label>Select Brand:</label>
                <?php foreach ($allbrands as $brand) { ?>
                <input type="checkbox" name="brand" id="brand" value="<?php if ($brand->brand=="Linon") echo "l"; else echo "z";?>" <?php if ($this->brand=="") echo "checked"; else if ($this->brand=="l" && $brand->brand=="Linon") echo "checked"; else if ($this->brand=="z" && $brand->brand=="Zinon") echo "checked"; else echo ""; ?>  style="width:10%; margin-top:5px;"/><lab style="margin-bottom:-20px; text-transform: uppercase;"><?php echo $brand->brand; ?></lab>
                <?php } ?>
            </p>
            <?php } ?>
            <p>
                <label>Select MRP: </label>
    
                <select class="setprice" name ="setprice" id="setprice" style="width:160px" onchange="setMRP(this);">
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
                <label>AND Select Size: </label>
    
                <select class="setprice" name ="setsize" id="setsize" style="width:160px" onchange="setSize(this);">
                    <option value=0 selected="selected">Select Size</option>
            <?php       $db = new DBConn();
			$objs = $db->fetchObjectArray("select s1.*,s2.name from it_ck_sizes s1,it_sizes s2 where s1.size_id = s2.id and s1.ctg_id=$ctg_id order by s1.sequence");
                        $db->closeConnection();    
                        foreach ($objs as $obj) {
                                $selected = "";
                                if ($obj->size_id == $this->size) {
					$selected = "selected";
                                }
                                if(isset($this->MRP) && trim($this->MRP) != ""){ $mStr = " and i.MRP = $this->MRP"; }else{ $mStr = "";}
	    			$obj2 = $db->fetchObject("select sum(i.curr_qty) as tot_qty from it_items i,it_ck_designs d where i.ctg_id=$ctg_id $brandquery and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.is_design_mrp_active=1 and i.size_id='$obj->size_id' $mStr and i.curr_qty > 0 having tot_qty >0");
				$units = 0;
				if ($obj2 && $obj2->tot_qty) { $units = $obj2->tot_qty; }
                                ?>
                    <option value="<?php echo $obj->size_id; ?>" <?php echo $selected; ?>><?php echo "$obj->name [$units units]"; ?></option>
                            <?php
                            }
                            ?>
                </select>   
            </p>
            
            
            <p>
                        <label> AND Select Style: </label>

                        <select name ="setStyle" style="width:160px" onchange="setStyle(this);">
                            <option value=0 selected="selected">Select Style</option>
                            <?php
//                            $q = "select s1.*,s2.name from it_ck_sizes s1,it_sizes s2  $mTab where s1.size_id = s2.id and s1.ctg_id=$ctg_id  $mrpClause order by s1.sequence";
                            $q = "select s2.name as name, s2.id as it_styles_id from it_ck_styles s1 inner join it_styles s2 on s1.style_id=s2.id where s1.ctg_id=$ctg_id";
                            $objs = $db->fetchObjectArray($q);
                            foreach ($objs as $obj) {
                                $selected = "";
                                if ($obj->it_styles_id == $this->style) {$selected = "selected"; }
                                if (isset($this->MRP) && trim($this->MRP) != "") {$mStr = " and i.MRP = $this->MRP"; } else {$mStr = "";}
                                if (isset($this->size) && !empty($this->size)) {$sizeCondition = " and i.size_id= $this->size"; } else {$sizeCondition = ""; }     
                                $temp="select sum(i.curr_qty) as tot_qty from it_items i,it_ck_designs d where i.ctg_id=$ctg_id $brandquery $sizeCondition and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.is_design_mrp_active=1 and i.style_id='$obj->it_styles_id' $mStr and i.curr_qty > 0 having tot_qty >0";
                                $obj2 = $db->fetchObject("select sum(i.curr_qty) as tot_qty from it_items i,it_ck_designs d where i.ctg_id=$ctg_id $brandquery $sizeCondition and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.is_design_mrp_active=1 and i.style_id='$obj->it_styles_id' $mStr and i.curr_qty > 0 having tot_qty >0"); //$brandquery
//                                    error_log("\nSTOCK MRP: $temp; \n", 3, "C:/xampp/htdocs/linenking/home/view/tmp.txt");
                                $query = "select sum(i.curr_qty) as tot_qty from it_items i,it_ck_designs d where i.ctg_id=$ctg_id $brandquery $sizeCondition and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.is_design_mrp_active=1 and i.style_id='$obj->it_styles_id' $mStr and i.curr_qty > 0 having tot_qty >0";

                                $units = 0;
                                if ($obj2 && $obj2->tot_qty) {
                                    $units = $obj2->tot_qty;
                                }
                                ?>
                                <option value="<?php echo $obj->it_styles_id; ?>" <?php echo $selected; ?>><?php echo "$obj->name [$units units]"; ?></option>
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
            $code = $db->safe($this->des_code);
            if ($this->des_code) {
                //echo "select i.id,i.MRP,i.brand_id,br.name as brandname, i.prod_type_id, pt.name as prodtype, i.material_id, mt.name as material, i.fabric_type_id, ft.name as fabric, d.image,d.design_no,sum(i.curr_qty) as tot_qty from it_items i,it_ck_designs d,it_brands br,it_prod_types pt,it_materials mt,it_fabric_types ft where i.design_no=$code and d.active=1 and i.design_no=d.design_no and i.ctg_id=$ctg_id and i.MRP=$this->MRP and i.brand_id=br.id and i.prod_type_id=pt.id and i.material_id=mt.id and i.fabric_type_id=ft.id and i.ctg_id=d.ctg_id group by i.design_no,MRP";
                $db = new DBConn();
                $allDesigns = $db->fetchObjectArray("select i.id,i.MRP,i.brand_id,br.name as brandname, i.prod_type_id, pt.name as prodtype, i.material_id, mt.name as material, i.fabric_type_id, ft.name as fabric, d.image,d.design_no,sum(i.curr_qty) as tot_qty from it_items i,it_ck_designs d,it_brands br,it_prod_types pt,it_materials mt,it_fabric_types ft where i.design_no=$code $brandquery and i.is_design_mrp_active=1 and i.design_no=d.design_no and i.design_id = d.id and i.ctg_id=$ctg_id and i.brand_id=br.id and i.prod_type_id=pt.id and i.material_id=mt.id and i.fabric_type_id=ft.id and i.ctg_id=d.ctg_id  $stockclause group by i.design_no,MRP");
                $db->closeConnection();               
                 
            } else if(!empty($this->style) && !empty($this->ctg)){
                  if(!empty($this->size)){$sizeCon="and i.size_id=$this->size";}else{$sizeCon="";}
                  if(!empty($this->MRP)){$MRPCon="and i.MRP=$this->MRP";}else{$MRPCon="";}

                  $query = "select i.id,i.MRP,i.brand_id,br.name as brandname, i.prod_type_id, pt.name as prodtype, i.material_id, mt.name as material, i.fabric_type_id, ft.name as fabric, d.image,d.design_no,sum(i.curr_qty) as tot_qty from it_items i,it_ck_designs d,it_brands br,it_prod_types pt,it_materials mt,it_fabric_types ft where i.style_id=$this->style $brandquery  $sizeCon $MRPCon and i.is_design_mrp_active=1 and i.design_no=d.design_no and i.design_id = d.id and i.ctg_id=$ctg_id and i.brand_id=br.id and i.prod_type_id=pt.id and i.material_id=mt.id and i.fabric_type_id=ft.id and i.ctg_id=d.ctg_id $stockclause group by i.design_no,MRP having tot_qty>0"; 
//                    error_log("\nSTOCK MRP: $query; \n", 3, "C:/xampp/htdocs/linenking/home/view/tmp.txt");                  
                    $allDesigns = $db->fetchObjectArray($query);
            }else if ($this->MRP && $this->ctg) {
                //$time1=0;
                $query = "Select i.id,i.MRP,i.brand_id,br.name as brandname,i.prod_type_id,pt.name as prodtype,i.material_id,mt.name as material,i.fabric_type_id,ft.name as fabric,d.image,d.design_no,sum(i.curr_qty) as tot_qty from it_items i,it_ck_designs d,it_brands br,it_prod_types pt,it_materials mt,it_fabric_types ft where i.ctg_id=$ctg_id and i.MRP=$this->MRP $brandquery and i.brand_id=br.id and i.prod_type_id=pt.id and i.material_id=mt.id and i.fabric_type_id=ft.id and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.design_id = d.id and i.is_design_mrp_active=1 $stockclause group by i.design_no having tot_qty > 0 order by tot_qty";
                //$time1= microtime(true);
                $db = new DBConn();
                $allDesigns = $db->fetchObjectArray($query);
                $db->closeConnection(); 
                if ($this->size){
                    $query = "Select i.id,i.MRP,i.brand_id,br.name as brandname,i.prod_type_id,pt.name as prodtype,i.material_id,mt.name as material,i.fabric_type_id,ft.name as fabric,d.image,d.design_no,sum(i.curr_qty) as tot_qty from it_items i,it_ck_designs d,it_brands br,it_prod_types pt,it_materials mt,it_fabric_types ft where i.ctg_id=$ctg_id and i.MRP=$this->MRP $brandquery and i.size_id=$this->size $brandquery and i.brand_id=br.id and i.prod_type_id=pt.id and i.material_id=mt.id and i.fabric_type_id=ft.id and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.design_id = d.id and i.is_design_mrp_active=1 $stockclause group by i.design_no having tot_qty > 0 order by tot_qty";
                   $db = new DBConn();
                    $allDesigns = $db->fetchObjectArray($query);
                    $db->closeConnection();  
                    }
               //$time2= microtime(true);
                //print $query."  2";
           }
//            else if ($this->MRP && $this->ctg ) {
//                //$time1=0;
//                $query = "Select i.id,i.MRP,i.brand_id,br.name as brandname,i.prod_type_id,pt.name as prodtype,i.material_id,mt.name as material,i.fabric_type_id,ft.name as fabric,d.image,d.design_no,sum(i.curr_qty) as tot_qty from it_items i,it_ck_designs d,it_brands br,it_prod_types pt,it_materials mt,it_fabric_types ft where i.ctg_id=$ctg_id and i.MRP=$this->MRP $brandquery and i.size_id=$this->size $brandquery and i.brand_id=br.id and i.prod_type_id=pt.id and i.material_id=mt.id and i.fabric_type_id=ft.id and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.design_id = d.id and i.is_design_mrp_active=1 $stockclause group by i.design_no having tot_qty > 0 order by tot_qty";
//                //$time1= microtime(true);
//                $allDesigns = $db->fetchObjectArray($query);
//                //$time2= microtime(true);
//                //print $query."  2";
//            }
            else if ($this->size && $this->ctg) {
                if(isset($this->MRP) && trim($this->MRP)!=""){
                    $mClause = " and i.MRP = $this->MRP ";
                }else{
                    $mClause = "";
                }
                $query = "Select i.id,i.MRP,i.brand_id,br.name as brandname,i.prod_type_id,pt.name as prodtype,i.material_id,mt.name as material,i.fabric_type_id,ft.name as fabric,d.image,d.design_no,sum(i.curr_qty) as tot_qty from it_items i,it_ck_designs d,it_brands br,it_prod_types pt,it_materials mt,it_fabric_types ft where i.ctg_id=$ctg_id and i.size_id=$this->size $brandquery and i.brand_id=br.id and i.prod_type_id=pt.id and i.material_id=mt.id and i.fabric_type_id=ft.id and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.design_id = d.id and i.is_design_mrp_active=1  $mClause $stockclause group by i.design_no,i.MRP having tot_qty > 0 order by tot_qty, MRP";
                $db = new DBConn();
                $allDesigns = $db->fetchObjectArray($query);
                $db->closeConnection();  
                //print $query."  3";
            } else {
                $allDesigns = array();
            }
            $row_no = 0;
            $db = new DBConn();
            $styleobj = $db->fetchObjectArray("select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.ctg_id=$ctg_id and s1.style_id=s2.id order by s1.sequence");
            $db->closeConnection();  
            $no_styles = count($styleobj);
            $db = new DBConn();
            $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.ctg_id=$ctg_id and s1.size_id=s2.id order by s1.sequence");
            $db->closeConnection();  
            $no_sizes = count($sizeobj);
            foreach ($allDesigns as $design) {
                $row_no++;
                $design_no = $db->safe($design->design_no);
                $divid = "accordion-" . $row_no;
                ?>
    <div class="clear"></div>
    <div class="box">
        <h2 class="expand">Design No: <?php echo $design->design_no." | Brand: $design->brandname"." | Material: $design->material"." | Fabric Type: $design->fabric"; if ($design->prodtype) { echo " [ Production Type: ".$design->prodtype." ]"; } echo " [ MRP: ".$design->MRP." ] [ Total Qty: $design->tot_qty ]"; ?></h2>
        <div class="collapse" id="<?php echo $divid; ?>">
            <form name="order_<?php echo $row_no; ?>" method="post" action="" onsubmit="addToCart(this); return false;">
                <input type="hidden" name="mrp" value="<?php echo $design->MRP; ?>" />
                <input type="hidden" name="design_no" value="<?php echo $design->design_no; ?>"/>
                <div class="grid_2" id="imagebackground">
                <a href="images/stock/<?php if ($design->image) echo $design->image; ?>" rel="prettyPhoto"><img id="orderimage" align="left" src="images/stock/<?php echo $design->image; ?>" width="130" /></a>
                </div>
                <div class="block grid_10" style="width:78%;">
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
                                                 
                                                        //to get the quantity and stock id of specific item
                                                        $sizeid = $sizeobj[$i]->size_id;
							$query = "select id,sum(curr_qty) as qty,is_avail_manual_order,barcode from it_items where design_no = $design_no and MRP=$design->MRP and ctg_id=$ctg_id and style_id = '$stylcod' and size_id = '$sizeid' and curr_qty > 0 order by curr_qty desc";
                                                       $db = new DBConn();
                                                        $getitm = $db->fetchObject($query);
                                                        
                                                        //check to see if specific item exists in order, if exist -> stores qty in order_qty  
                                                        if ($getitm) {
                                                            $query = "select sum(order_qty) as order_qty from it_ck_orderitems where order_id=$cart->id and store_id=$storeid and item_id=$getitm->id";
                                                            //echo $query;
                                                            $db = new DBConn();
                                                            $exist = $db->fetchObject($query);
                                                            $db->closeConnection(); 
                                                            if ($getitm->id) { $id = $getitm->id; } else { $id="0"; }
                                                            if ($getitm->qty) { $qty=$getitm->qty; 
                                                            if($this->showcurstck == 1){
      
                                                    $totqtyavil=0;
                                                    $stock_qry="SELECT IFNULL((SELECT SUM(quantity) FROM it_current_stock WHERE barcode = '$getitm->barcode' AND store_id = $storeid), 0) AS current_stock_qty, IFNULL((SELECT SUM(oi.quantity) FROM it_invoices o JOIN it_invoice_items oi ON oi.invoice_id = o.id WHERE o.invoice_type IN (0, 6) AND o.store_id = $storeid AND oi.item_code = '$getitm->barcode'), 0) AS intransit_stock_qty, IFNULL((SELECT SUM(oi.order_qty) FROM it_ck_orders o JOIN it_ck_orderitems oi ON o.id = oi.order_id JOIN it_items i ON oi.item_id = i.id WHERE o.store_id = $storeid AND i.barcode = '$getitm->barcode' AND o.status IN (" . OrderStatus::Active . ", " . OrderStatus::Picking . ", " . OrderStatus::Picking_Complete . ")), 0) AS active_picking_qty"; 
                                                    $store_stock_curr_qty1 = $db->fetchObject($stock_qry);

                                                    $current_stock_qty = isset($store_stock_curr_qty1->current_stock_qty) ? $store_stock_curr_qty1->current_stock_qty : 0;
                                                    $intransit_stock_qty = isset($store_stock_curr_qty1->intransit_stock_qty) ? $store_stock_curr_qty1->intransit_stock_qty : 0;
                                                    $active_picking_qty = isset($store_stock_curr_qty1->active_picking_qty) ? $store_stock_curr_qty1->active_picking_qty : 0;
                                                    // Calculate total available quantity
                                                    $totqtyavil = $current_stock_qty + $intransit_stock_qty + $active_picking_qty;
                                                            }
                                                            } else { $qty="0"; }
                                                            
                                                            
                                                            
                                                             $margin=1;
                                                        $ctg_name="";
                                                        $num_units=0; 
                                                        $validate="";
                                                        $ctg_id1=0;
                                                        $additional_msg="";
                                                        $margin_query="select c.name as ctg_name,i.num_units as units,i.ctg_id  as ctg_id,c.margin from it_items i,it_categories c where i.ctg_id=c.id and i.id=$getitm->id";
                                                        $mobj=$db->fetchObject($margin_query);
                                                        if($mobj){
                                                            $margin=$mobj->margin;
                                                            $ctg_name=$mobj->ctg_name;
                                                            $num_units=$mobj->units;
                                                            $ctg_id1=$mobj->ctg_id;
                                                            
                                                            if($margin==0 && $mobj->ctg_id !=41)
                                                            {
                                                               $validate="onkeyup='validate($getitm->id)'"; 
                                                            }
                                                            
                                                            if($mobj->ctg_id==48)
                                                            {
                                                             $additional_msg="<br><b><spam style=\"color:#e3341f\">Design Can be Different than the Picture</spam></b>";   
                                                            }
                                                        }
                                                           $db->closeConnection();
                                                           
                                                          ?> 
                                                           
                                                           
                                                           <?php if($margin==0 && $ctg_id1 !=41){ ?>
                                                           
                        <td width="70%"><input type='number' max="9" min="0" style="<?php if ($getitm->qty == 0) { echo ''; } elseif ($totqtyavil == 0 && $getitm->qty > 0 && ($this->showcurstck == 1 )) { echo 'border: 2px solid green;'; } elseif ($totqtyavil > 0 && $getitm->qty > 0 && ($this->showcurstck == 1)) { echo 'border: 2px solid red;'; } ?>" id="id_<?php echo $getitm->id;?>" placeholder="Enter no of set/packet" <?php echo $validate;?> pattern= "[0-9]+" title="ONLY NUMBER" name="item_<?php echo $id."_".$qty; ?>" <?php

                                                                if ($exist) {
                                                                    print "value='";
                                                                    echo $exist->order_qty;
                                                                    print "'";
                                                                }
                                                                ?>/></br><spam style="color:green"><b>1 Set/Packet(<?php echo $ctg_name;?>)= <?php echo $num_units." Qty ";?></spam></b><?php echo $additional_msg;?><br>[ <?php echo '<b>'.$getitm->qty.'</b>'; ?> ]
                                                                
                                                        
                                                                <?php } else { ?> 
                                                                
                                                                 <td><input type='number' max="9" min="0" style='width: 40px; <?php if ($getitm->qty == 0) { echo ''; } elseif ($totqtyavil == 0 && $getitm->qty > 0 && ($this->showcurstck == 1)) { echo 'border: 2px solid green;'; } elseif ($totqtyavil > 0 && $getitm->qty > 0 && ($this->showcurstck == 1)) { echo 'border: 2px solid red;'; } ?>' pattern= "[0-9]+" title="ONLY NUMBER" name="item_<?php echo $id."_".$qty; ?>" <?php

                                                                if ($exist) {
                                                                    print "value='";
                                                                    echo $exist->order_qty;
                                                                    print "'";
                                                                }
                                                                ?>/><br>[ <?php if($getitm->is_avail_manual_order==1){ echo '<b>'.$getitm->qty.'</b>';}else{echo '<b>' . 0 . '</b>';} ?> ] <?php //
                                                                
                                                                }
                                                              }
                                                ?></td><?php
                                            }
                                            print "</tr>";
                                        }
                                        ?>
                        </tbody>
                    </table>
                    <div id="status_<?php echo $row_no; ?>"></div>
                      <?php
                   $this->storeinfo = getCurrUser();
          
            $dbProperties = new dbProperties();
            if ((($dbProperties->getBoolean(Properties::DisableUserLogins)) && isset($this->storeinfo->usertype) && $this->storeinfo->usertype == 4)||((isset($this->storeinfo->inactive) && $this->storeinfo->inactive == 1 && isset($this->storeinfo->usertype) && $this->storeinfo->usertype == 4))  || (isset($this->storeinfo->inactive_bydatasync) && $this->storeinfo->inactive_bydatasync == 1 && isset($this->storeinfo->usertype) && $this->storeinfo->usertype == 4)) {
                          ?> <input class="blueglassbutton" type="reset" value="RESET"/>
                                   

                                    
            <?php } else { ?> <input class="blueglassbutton" type="submit" value="ADD TO CART" />  <input class="blueglassbutton" type="reset" value="RESET"/>
                              
            
                                  <?php 
                                    
            } ?>
                </div> <!-- end class=grid_10 -->
            </form>
            <br>
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