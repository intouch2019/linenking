<?php 
ini_set('max_execution_time', 300);
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";
class cls_arorder_details extends cls_renderer {

    var $params;
    var $desig;
    var $currStore;
    var $storeidreport = null;
    var $storeid;
    var $dtrange;
    var $categoryid;
    
    function __construct($params = null) {
        $this->currStore = getCurrUser();
        
        
              if (isset($_SESSION['account_dtrange'])) 
              { 
                  $this->dtrange = $_SESSION['account_dtrange'];
              }
              else { $this->dtrange = date("d-m-Y"); }
          
              if(isset($params['sid']))
              {
                  $this->storeid=$params['sid'];
              }
              
              if($params && isset($params['category']))
              {
                  $this->categoryid = $params['category'];
              }
                
  
    }

    function extraHeaders() {
        if (!$this->currStore) {
            return;
        }
        ?>
        <script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
        <script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
        <link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
        <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />


        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
        <link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
        <link rel="stylesheet" type="text/css" href="css/dark-glass/sidebar.css" />
        <script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
        <script type="text/javascript" src="js/sidebar/jquery.sidebar.js"></script>
      
        <script type="text/javascript">
            
          
            $(function () {
                $("a[rel^='prettyPhoto']").prettyPhoto({animation_speed: 'fast', slideshow: 3000, hideflash: true});
            });

            $(function () {
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
				 //				window.location.reload();reloadreport(-1);
                        var storeid = $('#store').val();
                        //window.location.href="arorder/details/sid="+storeid;
                        
                        
                    }
		});
		}
            });
                
            $("ul#demo_menu1").sidebar({
                width: 160,
                height: 110,
                injectWidth: 50,
                events: {
                    item: {
                        enter: function () {
                            $(this).find("a").animate({color: "red"}, 250);
                        },
                        leave: function () {
                            $(this).find("a").animate({color: "white"}, 250);
                        }
                    }
                }
            });
                
         
        
        
            });

    function genReport(){

                    var category = $('#category').val();
                    var storeid = $('#store').val();
                    var dtrange = $("#dateselect").val();


                    if(storeid!=0 && category!=0 && dtrange!="" )
                    {
                        window.location.href="arorder/details/sid="+storeid+"/category="+category+"/dtrange="+dtrange;
                        setfocus();
                    }
                    else
                    {
                         alert('Please Fill All Values');
                         if(storeid==0){
                             document.getElementById('storelabel').style.display = 'inline';
                         }
                         if(category==0){
                             document.getElementById('catlabel').style.display = 'inline';
                         }
                        if(dtrange==""){
                         document.getElementById('datelabel').style.display = 'inline';
                         }

                    }



            }
    
          function storelablehide()
            {
                  document.getElementById('storelabel').style.display = 'none';
            }
          function catlablehide()
            {
                 document.getElementById('catlabel').style.display = 'none';
            }
          function datelabelhide()
            {
                   document.getElementById('datelabel').style.display = 'none';
            }
        
        
        

        </script>

        <?php
    }
    public function pageContent() {
        
        $menuitem = "arorders";
        include "sidemenu." . $this->currStore->usertype . ".php";
        $sdate="";$edate=""; 
          
        ?>
        <h3>
            <div class="grid_10" style="float:left">Order Details(for All Store)</div>
        </h3>
        
      
<div class="grid_3">
    
          <b>Select Store*:</b><br/>
          <span style="font-weight:bold;">
              
              <select name="store" id="store" data-placeholder="Choose Store" class="chzn-select" style="width:100%;" onchange="storelablehide()">
               <option value="0">Select store</option>  
              <?php if( $this->storeid == -1 ){
                                   $defaultSel = "selected";
                             }else{ $defaultSel = ""; } ?>
                <option value="-1" <?php echo $defaultSel;?>>All Stores</option> 
<?php
$objs = $db->fetchObjectArray("select * from it_codes where usertype=4  order by store_name");
 

foreach ($objs as $obj) {        
	$selected="";
//	if (isset($this->storeidreport) && $obj->id==$this->storeidreport) { $selected = "selected"; }
        if ($this->storeid != -1){ 
                if($obj->id== $this->storeid) 
                { 
                    $selected = "selected";
                }
            
        }
?>
          <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?>><?php echo $obj->store_name; ?></option> 
<?php } ?>
		</select>
           
          </span>
           
          </div>
    
<div class="grid_5">
                <span style="font-weight:bold;">Date Filter : </span></br> <input size="17" type="text" id="dateselect" name="dateselect" value="<?php echo $this->dtrange; ?>" onclick="datelabelhide()" onchange="datelabelhide()"/> (Click to see date options)
		</div>
       
        <div class="grid_8">
             <div class="grid_5" ><label><h5 style="color:#FF0000;" ><span id="storelabel" style="display:none;"  >please select store</span></h5> </label>     
              
                </div>
            <div class="grid_3"><label><h5 style="color:#FF0000;" ><span id="datelabel" style="display:none;"  >please select Date</span></h5> </label>     
              
                </div>
        </div>
        <div class="grid_4">
            
          <b>Select Categories*:</b><br/>
          <span style="font-weight:bold;">
              <select name="category" id="category" data-placeholder="Choose category" class="chzn-select" style="width:50%;" onchange="catlablehide()">
   <option value="0">Select Category</option>                 
    <?php if( $this->categoryid == -1 ){
                                   $defaultSel = "selected";
                             }else{ $defaultSel = ""; } ?>
                <option value="-1" <?php echo $defaultSel;?>>All Category</option> 
<?php
$objs = $db->fetchObjectArray("select * from it_categories where  active=1  order by name");
 
//echo 'cat id'.$this->categoryid;
foreach ($objs as $obj) {        
	$selected="";
//	if (isset($this->storeidreport) && $obj->id==$this->storeidreport) { $selected = "selected"; }
        if ($this->categoryid != -1){ 
                if($obj->id== $this->categoryid) 
                { 
                    $selected = "selected";
                }
            
        }
?>
          <option value="<?php echo $obj->id; ?>" <?php echo $selected; ?>><?php echo $obj->name; ?></option> 
<?php } ?>
		</select>
          
          </span>
          
          </div>
        <div class="grid_8">
          <div ><label><h5 style="color:#FF0000;" ><span id="catlabel" style="display:none;"  >please select category</span></h5> </label>     
              
                </div>
           </br>
        </div>
        <div class="grid_5">
             <input type="button"   name="genRep1" id="genRep1" value="Generate Report" onclick="genReport()">
           
        </div>
          <?php if($this->storeid!=null && $this->dtrange!=null && $this->categoryid!=null)  { //22 fields ?>
        
        <div class="grid_10">
            
            <?php
            $currUri = $_SERVER["REQUEST_URI"];
            $cart_page = true;
            // include "cartinfo.php";
            $db = new DBConn();
            // $storeid = getCurrUserId(); 
            
            
            
        $dtarr = explode(" - ", $this->dtrange);
        $_SESSION['storeid'] = $this->storeidreport;
	if (count($dtarr) == 1) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";	
                $edate= "$yy-$mm-$dd";	
                $dQuery = " and o.bill_datetime >= '$sdate 00:00:00' and o.bill_datetime <= '$edate 23:59:59' ";
	} else if (count($dtarr) == 2) {
		list($dd,$mm,$yy) = explode("-",$dtarr[0]);
		$sdate = "$yy-$mm-$dd";
		list($dd,$mm,$yy) = explode("-",$dtarr[1]);
		$edate = "$yy-$mm-$dd";		
                $dQuery = " and o.bill_datetime >= '$sdate 00:00:00' and o.bill_datetime <= '$edate 23:59:59' ";
	} else {
		$dQuery = "";
	}
   
        

          $storeClause="";
          if($this->storeid == "-1"){               
               $storeClause = " and c.usertype = 4" ;
          }else{              
               $storeClause = " and c.id in ( $this->storeid ) ";
          }
            
       
          $categoryClause="";
          if($this->categoryid == "-1"){               
               $categoryClause = " " ;
          }else{              
               $categoryClause = " and i.ctg_id in ( $this->categoryid ) ";
          }
            
         try {
                $storequery1 = "select c.id as store_id, c.store_name  from it_codes c,it_orders o,it_order_items oi where c.is_closed = 0 $storeClause $dQuery and c.id=o.store_id  and o.tickettype in (0,1,6) and oi.order_id = o.id  group by c.id";
//                 echo $storequelry1;       
                $sobjs1 = $db->fetchObjectArray($storequery1); 
                if(empty($sobjs1)){
                    echo '<span style="font-weight:bold; color:red;" co><label><h3>Records not Available For Selected Store.</h3></lable></span>';
                }
                 if(!empty($sobjs1)) {
                   
                 $link="";
                 $date="$sdate"."to"."$edate";
                $categoryname="";
                 if($this->categoryid==-1 ){
                    $categoryname="AllCategories";
                 }else{
                     $catnamequery="select name as category_name from it_categories where id=$this->categoryid";
                        $category=$db->fetchObject($catnamequery);
                     $categoryname="$category->category_name";
                 }
                 
                 
                  if ($this->storeid == -1){ 
                        $link="tmp/Sales_Analysis_Report_AllStore_".$categoryname."_$date.csv";
                  }
                  else{
                      foreach ($sobjs1 as $storeobj) {
                          
                       
                          $store_name=str_replace("-","","$storeobj->store_name");
//                           $store_name=str_replace("\%/","","$storeobj->store_name");
                            $store_name = preg_replace('/[%()]/', '', $store_name);
//                             echo $store_name;
                        } 
                      $store_name1="$store_name"."_$categoryname"."_$date";
                      $link="tmp/Sales_Analysis_Report_$store_name1.csv";
                    
                  }
                  
                    ?>
            <br /><div style='margin-left:800px; padding-left:15px; height:24px;width:130px;border: solid gray 1px;background:#F5F5F5;padding-top:4px;'>
                    <a href='<?php echo $link; ?>' title='Export table to CSV'><img src="images/excel.png" width='20' hspace='3' style='margin-bottom:-6px;' /> Export To Excel</a>
                </div><br /> 
                    <?php
                   $fp = fopen($link, 'w');  // create link
                    $is_firsstore=1;
                foreach ($sobjs1 as $storeobj) {
                                 $query = "Select oi.item_id as item_id,sum(oi.quantity) as qty from it_orders o,it_order_items oi   where o.id = oi.order_id  $dQuery  and o.store_id = oi.store_id and o.tickettype in (0,6) and o.store_id = " . $storeobj->store_id . "   group by oi.item_id";
//                                   echo $query; 
                                $objs = $db->fetchObjectArray($query);

                                foreach ($objs as $obj) { 
                                    $qty=$obj->qty;
                                    $itemid=$obj->item_id;
                                             if ($qty > 0) {
                                                if (!isset($items1[$itemid]))
                                                    $items1[$itemid] = 0;
                                                if ($qty > 0) {
                                                    $items1[$itemid] += $qty;
//                                                    $itmcnt++;
                                                }
                                                 if (!isset($items[$itemid]))
                                                    $items[$itemid] = 0;
                                                if ($qty > 0) {
                                                    $items[$itemid] += $qty;
//                                                    $itmcnt++;
                                                }
                                            }
                                }
                                $query = "Select oi.item_id as item_id,sum(oi.quantity) as qty from it_orders o,it_order_items oi   where o.id = oi.order_id  $dQuery  and o.store_id = oi.store_id and o.tickettype in (1) and o.store_id = " . $storeobj->store_id . "   group by oi.item_id";
//                                   echo $query.";"; 
                                $objs = $db->fetchObjectArray($query);
                                $return=array();
                                foreach ($objs as $obj) { 
                                    $qty=$obj->qty;
                                    $itemid=$obj->item_id;

                                                if (!isset($return[$itemid])){
                                                    $return[$itemid] = 0;
                                                    
                                                }

                                                 $return[$itemid] += $qty;
                                                if (!isset($items[$itemid])){
                                                      $items[$itemid] = 0;
                                                      
                                                }
                                                 $items[$itemid] += $qty;
                                }
                                
                                
                    
                    if(count($items1)>0 || count($return)>0){
                     exporttoexcel($items1,$return,$storeobj->store_name,$is_firsstore,$fp,$sdate,$edate,$this->categoryid);
                                $is_firsstore=2;
                    unset($items1);
                     unset($return);
                    
                    }

                }
                fclose($fp);
                
                 $query = "Select i.design_no as design_no,i.mrp as mrp ,sum(oi.quantity) as qty from it_codes c, it_orders o,it_order_items oi,it_items i   where c.is_closed = 0 $storeClause and c.id=o.store_id  and o.id = oi.order_id and i.id=oi.item_id  $dQuery  and o.store_id = oi.store_id and o.tickettype in (0,6,1) and i.is_design_mrp_active=1   group by i.design_no,i.mrp order by sum(oi.quantity) desc";
//                                   echo $query.";";                     
                 $objs = $db->fetchObjectArray($query);

                    foreach ($objs as $obj) { 
                        $mrp=$obj->mrp;
                        $design_no=$obj->design_no;
                        $qty=$obj->qty;
                        $key=$design_no.":".$mrp;
                               if (!isset($design[$key])){
                               $design[$key] = 0;}
                                        $design[$key] = $qty;

                    }
                $i = 0;
                $item_id1 = "0";
                foreach ($items as $itemid => $qty) {
                    if ($i == 0) {
                        $item_id1 = $itemid;
                        $i++;
                    } else {
                        $item_id1 .= "," . $itemid;
                    }
                }
//                print_r($items);
              //  and i.id in($item_id)
                
            foreach ($design as $key => $value) {
                        $arr= explode(":",$key);
//                        print_r($arr);                       
                $query = "select i.id,i.ctg_id,c.name as category,i.design_no,i.mrp,d.image from it_items i,it_categories c,it_ck_designs d where i.ctg_id=c.id and i.is_design_mrp_active=1 and i.id in($item_id1) and d.id=i.design_id and i.design_no='$arr[0]' and i.mrp=$arr[1]  $categoryClause  group by i.mrp";
//                 echo $query;
                $arobj = $db->fetchObjectArray($query);
                $row_no = 0;
                ?>


                
            <?php
            foreach ($arobj as $obj) {
                $row_no++;
                $design_no = $db->safe($obj->design_no);
                $ctg_id = $db->safe($obj->ctg_id);
                $item_id = $obj->id;
                $MRP = $obj->mrp;
                ?>  <div class="clear"></div>  
                      <div class="box"> 
                    <h2>Category: <?php echo $obj->category; ?> | Design No: <?php echo $obj->design_no . " [ MRP: " . $obj->mrp . " ]"; ?></h2> <!-- Fabric: $design->fabric |-->
                        <div class="block" id="<?php echo $divid; ?>">
                            <form name="order_<?php echo $row_no; ?>" method="post" action=""  >
                <!--                <input type="hidden" name="design_no" value="<?php //echo $design->design_no;  ?>" />
                                <input type="hidden" name="mrp" value="<?php // echo $design->MRP;  ?>" />-->
                                <!--<input type="hidden" name="ctg" value="<?php //echo $design->ctg_id;  ?>" />-->
                                <a href="images/stock/<?php echo $obj->image; ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="images/stock/<?php echo $obj->image; ?>" height="170px" width="170px" /></a>
                                <div class="block grid_10">
                                    <table>  <?php
                $styleobj = $db->fetchObjectArray("select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.ctg_id=$ctg_id and s1.style_id=s2.id  and s2.is_active = 1 order by s1.sequence");
                $no_styles = count($styleobj);
//                                    print_r($styleobj);
                $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.ctg_id=$ctg_id and s1.size_id=s2.id order by s1.sequence");
//                                    print_r($sizeobj);
                $no_sizes = count($sizeobj);
                ?>
                                        <thead>
                                            <tr><th></th>
                                        <?php
                                        $width = intval(100 / ($no_sizes + 1));
                                        for ($i = 0; $i < $no_sizes; $i++) {
                                            print '<th style="text-align:left;" width="' . $width . '%">';
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
                                                    print "<tr id='styles::" . $obj->id . "::" . $styleobj[$k]->style_id . "'><th>";
                                                    echo $styleobj[$k]->style_name;
                                                    print"</th>";
//                                                  store style id in $stylecod
                                                    $stylcod = $styleobj[$k]->style_id;
                                                    for ($i = 0; $i < $no_sizes; $i++) {
                                                        ?><td><?php
                                                    //to get the quantity and stock id of specific item
                                                    $sizeid = $sizeobj[$i]->size_id;
                                                    $query = "select id,curr_qty as qty from it_items where design_no = $design_no and MRP=$obj->mrp and ctg_id=$ctg_id and style_id = '$stylcod' and size_id = '$sizeid' and barcode like '89%'";
//                                                        $query = "select id,sum(curr_qty) as qty from it_items where design_no = $design_no and MRP=$design->MRP and ctg_id=$ctg_id and style_id = '$stylcod' and size_id = '$sizeid' and curr_qty >= 0 order by curr_qty desc";
//                                                      echo $query;
                                                    $getitm = $db->fetchObjectArray($query);
                                                    $id=0;
                                                    $qty=0;
                                                    $curr_qty=0;
                                                    $found=0;
                                                    foreach($getitm as $itm){
                                                       
                                                            $id =$itm->id;
                                                            $curr_qty += $itm->qty;
                                                         if (isset($items[$itm->id])) { 
                                                              $found=1;
                                                                $qty += $items[$itm->id]; 
                                                            }
                                                    }
                                                    //check to see if specific item exists in order, if exist -> stores qty in order_qty  
//                                                    if ($getitm) {
//                                                           
                                                        ?><input type='text' style='width: 30px;' name="item_<?php echo $id . "_" . $qty; ?>" <?php
                                                            if ($found==1) {
                                                                print "value='";
                                                                echo $qty;
                                                                print "'";
                                                            }
                                                            ?>/><br>[ <?php echo $curr_qty; ?> ]<?php
//                                                    }
                                                    ?></td><?php
                                                }
                                                print "</tr>";
                                            }
                                          
                                            ?></td></tr> 
                                        </tbody>
                                    </table>
                                </div> <!-- end class=grid_10 -->
                            </form>
                        </div> <!-- end class="block" -->
                    </div> <!-- end class="box" -->
                    <div class="clear"></div>
                <?php
            } 
                 }// end foreach allDesigns
             }
                        }
                 catch (Exception $ex) {
            print $ex;
        }
    }
    }
}
    function exporttoexcel($items1 ,$return,$store,$is_firsstore,$fp,$sdate,$edate,$categoryid)
            {
         $ctgClause="";
           if($categoryid == "-1"){               
               $ctgClause = " " ;
          }else{              
              $ctgClause = " and i.ctg_id in ( $categoryid ) ";
            }
        $db=new DBConn();
//                echo "</br>Store name".$store."</br>";
//            print_r($items1);
            
            $item_id = "0";
            $return_item_id = "0";
            $i = 0;
            $j = 0;
            foreach ($items1 as $itemid => $qty) {
                if ($i == 0) {
                    $item_id = $itemid;
                    $i++;
                } else {
                    $item_id .= "," . $itemid;
                }//.":".$qty;  }
            }
            $total_return=0;
             foreach ($return as $itemid => $qty) {
                 $total_return +=$qty;
                if ($j == 0) {
                    $return_item_id = $itemid;
                    $j++;
                } else {
                    $return_item_id .= "," . $itemid;
                }//.":".$qty;  }
            }
//            echo $item_id;
//      $htm="<html><body><table align=\"center\" border=1>"; 
            
          if (isset($fp)) {
                  
           
                $tcell = array();
                 if($is_firsstore==1){
                $tableheaders = "Store Name:Category:Design no:Barcode:Sale Date From:Sale Date To:MRP:Corp.&Retail Sale Qty:Available Qty on Portal";
                $headerarr = explode(":", $tableheaders);
                foreach ($headerarr as $harr) {
                    if ($harr != "") {
                        $tcell[] .= $harr;
                    }
                }
                 fputcsv($fp, $tcell, ',', chr(0));}
                $query = "select i.id, c.name as category ,i.design_no,i.barcode as barcode,i.mrp,i.curr_qty as avl_qty from it_items i,it_categories c where i.ctg_id=c.id  and i.id in($item_id)  $ctgClause group by c.name,i.design_no,i.barcode";
//                echo $query; 
                $arobj = $db->fetchObjectArray($query);
                foreach ($arobj as $obj) {
                    $tcell = null;

                    $order_qty = 0;
                    if (isset($items1[$obj->id])) {
                        $order_qty = $items1[$obj->id];
                    }
                     $tcell[] .= trim($store);
                    $tcell[] .= trim($obj->category);
                    $tcell[] .= trim($obj->design_no);
                    $tcell[] .= trim($obj->barcode);
                    
//$sdate            
                    
                     $tcell[] .= trim(ddmmyy2($sdate));
                      $tcell[] .= trim(ddmmyy2($edate));
                      
                    $tcell[] .= trim($obj->mrp);
                    $tcell[] .= trim($order_qty);
                    $tcell[] .= trim($obj->avl_qty);
//                    print_r($tcell);
                    fputcsv($fp, $tcell, ',', chr(0));
                 }
                
                   $query = "select i.id, c.name as category ,i.design_no,i.barcode as barcode,i.mrp,i.curr_qty as avl_qty from it_items i,it_categories c where i.ctg_id=c.id  and i.id in($return_item_id)  $ctgClause  group by c.name,i.design_no,i.barcode";
//                echo $query; 
                $arobj = $db->fetchObjectArray($query);
                foreach ($arobj as $obj) {
                    $tcell = null;

                    $order_qty = 0;
                    if (isset($return[$obj->id])) {
                        $order_qty = $return[$obj->id];
                    }
                    $tcell[] .= trim($store);
                    $tcell[] .= trim($obj->category);
                    $tcell[] .= trim($obj->design_no);
                    $tcell[] .= trim($obj->barcode);
                    
//$sdate            
                    
                    $tcell[] .= trim(ddmmyy2($sdate));
                    $tcell[] .= trim(ddmmyy2($edate));
                      
                    $tcell[] .= trim($obj->mrp);
                    $tcell[] .= trim($order_qty);
                    $tcell[] .= trim($obj->avl_qty);
//                    print_r($tcell);
                    fputcsv($fp, $tcell, ',', chr(0));
              }
                
                
                

            } else {
                echo "<br/>Unable to create file.";
            } 
            
            
            }
            
           
            
 
?>
</div>