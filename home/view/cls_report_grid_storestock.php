<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "session_check.php";

class cls_report_grid_storestock extends cls_renderer {

    var $params;
    var $currUser;
    var $ctg = 0, $mrp = 0;
    var $dtrange,$ptype = null;
    var $storeidreport = null;

    function __construct($params = null) {
       // parent::__construct(array(UserType::Admin, UserType::CKAdmin));
        $this->params = $params;
        $this->currUser = getCurrUser();
        if (isset($params['mrp']))
            $this->mrp = intval($params['mrp']);       
        if(isset($params['storeid'])){
            $this->storeidreport = $params['storeid'];
        }
        if(isset($params['ptype'])){
            $this->ptype = $params['ptype'];
        }
    }

    function extraHeaders() {
        ?>
        <script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
<link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
<link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script type="text/javascript" src="js/expand.js"></script>
        <script src="js/common.js"></script>
        <script type="text/javascript">
var mrp = '<?php echo $this->mrp; ?>';           
    $(function(){
          $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true});            
        loadMRP();
    
        });
            

              function loadMRP() {                
                //alert("hello");
                var sid = $("#store").val();
                var ajaxUrl = "ajax/getStockMRP.php?storeid="+sid;
                //alert(ajaxUrl);
                $.getJSON(ajaxUrl, function(data) {
                    var options = $('#mrpselect').attr('options');
                    options.length = 1;
//                    options.length = 1;
                    console.log(data);
                    for (var i = 0; i < data.length; i++) {
                        console.log(data[i]);
                        if (data[i] == mrp) {
                            options[options.length] = new Option(data[i], data[i], false, true);
                        } else {
                            options[options.length] = new Option(data[i], data[i], false, false);
                        }
//                        $("#mrpselect").append("<option value=" + data[i] + ">" + data[i] + "</option>");
                    }
                });
            }
            
            function generateSalesRep(){
                var storeid = $("#store").val();
                var mrpsel = $("#mrpselect").val();
                var ptype = $("#prod_type").val();
                var addmrp = "",addptype="";
                if(mrpsel != 0){
                    addmrp = '/mrp='+mrpsel;
                }
                if(ptype != null){
                    addptype = '/ptype='+ptype;
                }
                if(storeid == null){
                    alert("Please Select a store");
                }else{
                 //alert ("here: str "+storeid+" date sel: "+seldate+" mrp sel: "+mrpsel+" ptype sel: "+ptype );
                 window.location.href = "report/grid/storestock/storeid="+storeid+addmrp+addptype;
                }
            }
            

        </script>
        <link rel="stylesheet" href="css/bigbox.css" type="text/css" />
        <?php
    }

    public function pageContent() {
        ini_set('max_execution_time', 300);
        $menuitem = "gridViewStoreStock";
        include "sidemenu." . $this->currUser->usertype . ".php";
        //global $g_categories;
        $db = new DBConn();
        $storeClause="";
        ?>
        <!-- div=colOne -->
        <div class="grid_10">
            <h2>Grid View Store Stock</h2>
            <div class="grid_12">
                <div class="box" style="margin-top:10px;">
                    <p>Select options from below and click on 'Generate Report' to view the report<p><br/>
                    <form name="GStSales" action=""> 
                    <div class ="grid_6">
                      
                        <b>Select Store*:</b><br/>
                        <select name="store" id="store" data-placeholder="Choose Store" class="chzn-select" multiple style="width:75%; height:30%; " onchange="loadMRP();">
                            <?php if( $this->storeidreport == -1 ){
                                   $defaultSel = "selected";
                             }else{ $defaultSel = ""; } ?>
                            <option value="-1" <?php echo $defaultSel; ?>>All Stores</option> 
                            <?php
                            $objs = $db->fetchObjectArray("select * from it_codes where usertype= ".UserType::Dealer." and id in (select distinct store_id from it_current_stock ) and id in (select store_id from executive_assign where exe_id=".getCurrUser()->id." ) order by store_name");

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
                        </select>
                     </div>   
                    <hr>
                    <div class="grid_3">
                        Select MRP :                          
                        <select name="mrpselect" id="mrpselect" onchange="generateSalesRep();" style="margin-left: 25px; width:100px;">
                             <option value="0">All MRPs</option>                        
                        </select>
                    </div>
                    <div>
                            Select Production Type:
                       <?php
                         $pTypequery = "select * from it_prod_types order by name ";
                         $prodObjs = $db->fetchObjectArray($pTypequery);
                         $ptypeIds = explode(",",$this->ptype);
                       ?>
                       <select name="prod_type" id="prod_type" data-placeholder="Choose Production Type" class="chzn-select" multiple style="width:35%;">
                          <?php if($this->ptype == 0){$defaultPSel = "selected";}else{ $defaultPSel = ""; } ?>
                           <option value="0" <?php echo $defaultPSel;?>>ALL Production Types</option> 
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
                    </div>     
                    <br/><br/><br/><br/>       
                    <input type="button" name="Generate_Report" value="Generate Report" onclick="javascript:generateSalesRep();" >
                    </form>
                </div>
                <?php
                if ($this->mrp != 0) {
                    $mrpqry = " and i.MRP=$this->mrp ";
                }else { $mrpqry = ""; }
                $ptypeqry = "";
                if($this->ptype != 0) {
                    $ptypeqry = " and i.prod_type_id in ( $this->ptype ) ";
                }else{ $ptypeqry = "";}
                if(isset($this->storeidreport) && trim($this->storeidreport) != ""){
                   if( $this->storeidreport != -1 ){ 
                    $storeClause = " and c.store_id in ( $this->storeidreport ) ";
                   }else if($this->storeidreport == "-1"){
                       $storeClause = " and c.store_id in ( select store_id from executive_assign where exe_id=".getCurrUser()->id." ) ";
                   }else{ $storeClause = ""; }
                   $query = " select c.barcode, sum(c.quantity) as currqty,i.barcode,i.ctg_id,i.MRP,i.style_id,i.size_id,i.prod_type_id from it_current_stock c, it_items i  where c.barcode = i.barcode  $storeClause  $mrpqry $ptypeqry  group by i.ctg_id ";
                   //print $query;
                   $storeStockObjs = $db->fetchObjectArray($query);
              
                if($storeStockObjs){
                    foreach($storeStockObjs as $sStockObj){
                        $ctgid=$sStockObj->ctg_id;  
                        $styleid =$sStockObj->style_id;
                        $sizeid =$sStockObj->size_id;
                        $styleobj = $db->fetchObjectArray("select s.id,s.name as style_name from it_styles s,it_ck_styles st where st.ctg_id=$ctgid and st.style_id=s.id  and s.is_active = 1 order by sequence");                        
                        $no_styles = count($styleobj);                        
                        $sizeobj = $db->fetchObjectArray("select s.id,s.name as size_name from it_sizes s,it_ck_sizes si where si.ctg_id=$ctgid and si.size_id=s.id order by sequence");                        
                        $no_sizes = count($sizeobj);
                        $cat = $db->fetchObject("select * from it_categories where id=$ctgid");
                        if(isset($this->ptype) && trim($this->ptype) != "" && $this->ptype != 0 ){
                            $prodObj = $db->fetchObjectArray("select name from it_prod_types where id in ($this->ptype)");                            
                            $prod_name = "";
                            if($prodObj){
                                foreach($prodObj as $prod){$prod_name .= $prod->name.",";}
                                $prod_name = substr($prod_name, 0, -1);
                            }
                        }
                        ?>
                        <div class="box" id="categorysales">
                            <h2 class="expand">CATEGORY: <?php if ($ctgid != 0) {
                            echo $cat->name;
                        } ?>  <?php if ($this->mrp == 0) { ?> | MRP = ALL MRPs <?php } else { ?> | MRP = <?php echo $this->mrp;
                                    }
                               if($this->ptype == null || $this->ptype == 0){?> |PROD TYPE = ALL <?php }else { ?> | PROD TYPE = <?php echo $prod_name; } ?></h2>
                            <div class="collapse" id="accordion"><Br/>
                                <div class="block grid_12">
                                    <table align="center">
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
                                                print "<tr><th>";
                                                echo $styleobj[$k]->style_name;
                                                print"</th>";
                                                //store style id in $stylecod
                                                for ($i = 0; $i < $no_sizes; $i++) {
                                                    ?><td><?php
                                                $total = 0;                                                
                                                $stockqtyqry = "select sum(c.quantity) as currqty from it_current_stock c, it_items i  where c.barcode = i.barcode $storeClause and i.ctg_id = $ctgid and i.style_id = ".$styleobj[$k]->id." and i.size_id = ".$sizeobj[$i]->id."  $mrpqry $ptypeqry ";
//                                                print "<br/> stck qry: ".$stockqtyqry;
                                                $stockQty = $db->fetchObject($stockqtyqry);
                                                if( $stockQty ){$total=$stockQty->currqty;}
                                                print " [ ".$total." ] ";
                                                ?></td><?php
                                            }
                                            print "</tr>";
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>&nbsp;<br/> <!-- end class=grid_10 -->
                            </div><div class="clear"></div>
                        </div>  
    <?php } } } ?>
            </div>

            

        <?php
    }

// pageContent()
}
?>