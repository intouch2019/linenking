<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "session_check.php";

class cls_admin_stock_current extends cls_renderer {

	var $params;
	var $currStore;
        var $brand=0,$ctg=0,$mrp=0;
	function __construct($params=null) {
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
//        parent::__construct(array(UserType::Admin, UserType::CKAdmin));
        $this->currStore = getCurrUser();    
        $this->params = $params;
        if (isset($params['ctg']))
            $this->ctg = $params['ctg'];//sprintf("%03d",intval($params['ctg']));
        if (isset($params['mrp']))
            $this->mrp = $params['mrp'];//intval($params['mrp']);
        if (isset($params['brand']))
            $this->brand = ($params['brand']);
	
	}
        

	function extraHeaders() {
?>
<link rel="stylesheet" href="js/chosen/chosen.css" />
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
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />
<script type="text/javascript" src="js/expand.js"></script>
<script src="js/common.js"></script>
<script type="text/javascript">
var mrp = '<?php echo $this->mrp; ?>';

$(function() {
    $("input:checkbox[name=brand]").click(function(){
        var array = new Array();
        $("input:checkbox[name=brand]:checked").each(function()
        {
            array.push($(this).val())
        });
        var len = array.length;
        if (len == 1) {
            var brand = array[0];
            window.location.href="admin/stock/current/brand="+brand;
        } else {
            window.location.href="admin/stock/current/";
        }
    });
});
        
function loadByCategory(cat) {
    var brand = '<?php echo $this->brand; ?>';
    //alert(brand);
    if (brand != '0') {
        window.location.href="admin/stock/current/brand="+brand+"/ctg="+cat;
    } else { 
        window.location.href="admin/stock/current/ctg="+cat;
    } 
}

function loadByMrp(sel) {
    var brand = '<?php echo $this->brand; ?>';
    var bradd;
    if (brand!='0') { bradd = "/brand="+brand; } else bradd = '';
    var value = sel.options[sel.selectedIndex].value;  
    mrp=value;
    //alert(mrp);
    window.location.href="admin/stock/current"+bradd+"/ctg="+<?php echo $this->ctg; ?>+"/mrp="+mrp;
    //loadTopLow();
}

function loadpage() {
    //alert("hello");
    var catid='<?php echo $this->ctg; ?>';
    var ajaxUrl = "ajax/getMRP.php?cat="+catid;
    //alert(ajaxUrl);
    $.getJSON(ajaxUrl, function(data){
         var options = $('.mrpselect').attr('options');
         options.length=1;
         for (var i=0;i<data.length;i++) {
             if (data[i]==mrp) { 
                 options[options.length] = new Option(data[i], data[i], false, true);
             } else {
                options[options.length] = new Option(data[i], data[i], false, false);
             }
             //$("select .mrpselect").append("<option value=" + data[i] + ">" + data[i] + "</option>");
         }  
    });
}
//$(function(){
    loadpage();
//});
function loadActiveStock(){
//    alert("here");
    var ctgs = $("#category").val();
    var mrps = $("#mrpselect").val();   
    var ctgadd,mrpadd;
    if(ctgs==null && mrps==null){
        alert("Please select either Category or MRP");
    }else{
//        alert("CTGS IDS:"+ctgs+"mrps IDS:"+mrps);
         if (ctgs!==null) { ctgadd = "/ctg="+ctgs; } else ctgadd = '';
         if (mrps!==null) { mrpadd = "/mrp="+mrps; } else mrpadd = '';
//         alert("CTGADD: "+ctgadd+" MRPADDS: "+mrpadd);
//          alert("window.location.href=admin/stock/current"+ctgadd+""+mrpadd");
        window.location.href="admin/stock/current"+ctgadd+mrpadd;
       
    }
}    
</script>
<?php
	}
	public function pageContent() {
            $menuitem = "currstock";
            include "sidemenu.".$this->currStore->usertype.".php";
            //global $g_categories;
            $db = new DBConn();
            $ctgids=explode(",",$this->ctg);
//            print_r($ctgids);
            if($this->ctg==0 && $this->mrp!=0){
//                print "empty";
                //fetch active stock all ctgids
                $q = "select distinct i.ctg_id from it_items i, it_categories c where  i.ctg_id=c.id and c.active=1 order by c.sequence";
                $cs = $db->fetchObjectArray($q);
                //print_r($c); 
                $ctgids=array();
                foreach($cs as $c){ $ctgids[] = $c->ctg_id;}
            }
            $mrpids = explode(",",$this->mrp);
?>
<!-- div=colOne -->
<div class="grid_10">
		<h2>Current Active Stock</h2>
<div class="grid_12">
    <div class="box" style="margin-top:10px;">
        <form name="activeStk" id="activeStk" method="post" onsubmit="loadActiveStock(); return false;">
        Select Brand : 
        <input type="checkbox" name="brand" id="brand" value="l" <?php if ($this->brand=="l") echo "checked"; else echo ""; ?>  style="margin-top:5px;">Linon</input> 
        <input type="checkbox" name="brand" id="brand" value="z" <?php if ($this->brand=="z") echo "checked"; else echo ""; ?>  style="margin-top:5px;">Zinon</input>
        <hr/>
        <!--width:10%; margin-top:5px; margin-right:-20px;-->
        <?php $brquery = '';
        if ($this->brand!='0') {
            if ($this->brand=='l') $br = '5'; else $br='6';
            $brquery = " i.brand_id=$br and ";
        }
        echo "Select Category :";
        $cquery = "select distinct i.ctg_id,c.name from it_items i, it_categories c where $brquery i.ctg_id=c.id and c.active=1 order by c.name";
        $catgs = $db->fetchObjectArray($cquery);
        ?>
        &nbsp;<select name="category[]" id="category" data-placeholder="Choose Category" class="chzn-select" multiple style="width:32%;" onchange="">
            <option value="-1"></option> 
        <?php
         foreach ($catgs as $cat) { 
                $selected="";                        
                foreach($ctgids as $cid){
                    if($cat->ctg_id==$cid) 
                    { $selected = "selected"; }
                }   
        ?>
        <!--<input type="radio" name="chartType" <?php // if ($this->ctg==$cat->ctg_id) { ?>checked <?php // }?> value="<?php // echo $cat->ctg_id; ?>" onclick="loadByCategory(this.value);"><?php // echo $cat->name; ?></input>&nbsp;&nbsp;-->
        <option value="<?php echo $cat->ctg_id; ?>" <?php echo $selected; ?>><?php echo $cat->name; ?></option> 
        <?php } ?>
        </select>
        <hr>
<!--        Select MRP :
        <select class="mrpselect" onchange="loadByMrp(this)" style="margin-left: 25px; width:100px;">
            <option value="0">All MRPs</option>
        </select><br><br/>-->
         Select MRP :
            <?php
             //$mquery = "select distinct(i.MRP) from it_items i,it_ck_designs d where  i.design_no=d.design_no and i.ctg_id=d.ctg_id and d.active=1 group by MRP order by MRP desc";
            $mquery = "select distinct(i.MRP) from it_items i,it_ck_designs d where  i.design_no=d.design_no and i.ctg_id=d.ctg_id and i.is_design_mrp_active=1 group by MRP order by MRP desc";
             $allmrps = $db->fetchObjectArray($mquery);
            ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<select name="mrpselect[]" id="mrpselect" data-placeholder="Choose MRP" class="chzn-select" multiple onchange="" style="margin-left: 25px; width:32%;">
                <option value="-1"></option>
             <?php 
               foreach($allmrps as $mrp){
                   $selected="";
                   if(!empty($mrpids)){
                       foreach($mrpids as $mrpid){
                          if($mrp->MRP==$mrpid && $mrpid!=0){                              
                              $selected="selected";
                          } 
                       }
                   }
             ?>   
                <option value="<?php echo $mrp->MRP;?>" <?php echo $selected; ?>><?php echo $mrp->MRP; ?></option>
               <?php } ?>   
            </select><br><br/>
            <input type="Submit" name="Submit" value="Submit">
        </form>
    </div>
    <?php 
    //$ctgid=$db->safe($this->ctg);
     foreach($ctgids as $ctgid){
        $ftot = 0; $ftot_dsg = 0;
        $brandqry = ''; $brn = '';
        //echo $this->brand;
        if ($this->brand != '0') { if ($this->brand == 'l') $brn='5'; else $brn='6'; $brandqry = " and i.brand_id=$brn "; }
        if (($this->mrp !=0) && trim($this->mrp != "")) { $mrpqry=" and i.MRP in ( $this->mrp ) "; }
        else $mrpqry="";
        //$query0 = "select sum(i.curr_qty) as stkqty,i.style_id,i.size_id,i.MRP,count(distinct(d.design_no)) as num_designs from it_items i,it_ck_designs d where i.curr_qty > 0 and i.ctg_id=$ctgid $brandqry $mrpqry and i.ctg_id=d.ctg_id and i.design_no=d.design_no and d.active=1 group by i.style_id,i.size_id";
        $query0 = "select sum(i.curr_qty) as stkqty,i.style_id,i.size_id,i.MRP,count(distinct(d.design_no)) as num_designs from it_items i,it_ck_designs d where i.curr_qty > 0 and i.ctg_id=$ctgid $mrpqry and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.is_design_mrp_active=1 group by i.style_id,i.size_id"; //$brandqry 
//        echo $query0;
        $stockinfo=$db->fetchObjectArray($query0);

        $styleobj = $db->fetchObjectArray("select s.id,s.name as style_name from it_styles s,it_ck_styles st where st.ctg_id=$ctgid and st.style_id=s.id order by sequence");
        $no_styles = count($styleobj);
        $sizeobj = $db->fetchObjectArray("select s.id,s.name as size_name from it_sizes s,it_ck_sizes si where si.ctg_id=$ctgid and si.size_id=s.id order by sequence");
        $no_sizes = count($sizeobj);
         if($this->ctg!=0) {$cat = $db->fetchObject("select * from it_categories where id=$ctgid");
         $cat_name = $cat->name;
        }else{ $cat_name=""; }
        ?>
      <div class="box" id="categorystock">
            <h2 class="expand">CATEGORY: <?php echo $cat_name ; ?> | ALL ACTIVE DESIGNS STOCK <?php if ($this->mrp==0) { ?> | MRP = ALL MRPs <?php } else { ?> | MRP = <?php echo $this->mrp; } ?></h2>
            <div class="collapse" id="accordion"><Br/>
                    <div class="block grid_12">
                        <table align="center">
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
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                //for each unique style print style
                                for ($k = 0; $k < $no_styles; $k++) {
                                //print style names
                                    $row_tot = 0;$row_tot_desg=0;
                                    print "<tr><th>";
                                    echo $styleobj[$k]->style_name;
                                    print"</th>";
                                    //store style id in $stylecod
                                    for ($i = 0; $i < $no_sizes; $i++) {
                                        ?><td><?php
                                                $total=0;$total_designs=0;
                                                foreach ($stockinfo as $prod) {
                                                    if ($prod->size_id == $sizeobj[$i]->id && $prod->style_id==$styleobj[$k]->id) {
//                                                       {$total+=intval($prod->stkqty);}
//                                                       $total_designs+=intval($prod->num_designs);
                                                        $total+=intval($prod->stkqty);
                                                        $row_tot+=intval($total);
                                                        $total_designs+=intval($prod->num_designs);
                                                        $row_tot_desg+=intval($total_designs);
                                                    }  
                                                }
                                                print "$total [$total_designs]";
                                        ?></td><?php
                                    }
                                    print"<td><b>$row_tot [$row_tot_desg]</b></td>";
                                    print "</tr>";
                                    $ftot+=$row_tot;
                                    $ftot_dsg+=$row_tot_desg;
                                }
                                
                                //below loop to show div col tots
                                    
                                    print "<tr><th>Total</th>";
                                    
                                    //store style id in $stylecod
                                    for ($i = 0; $i < $no_sizes; $i++) {
                                        ?><td><?php
                                                $col_tot = 0;$col_tot_desg=0;
                                                $total=0; $total_designs=0;
                                                foreach ($stockinfo as $prod) {
                                                    if ($prod->size_id == $sizeobj[$i]->id ) {
                                                       $col_tot+=intval($prod->stkqty);                                                       
                                                       $col_tot_desg+=intval($prod->num_designs);                                                       
                                                    }  
                                                }
                                                print "<b>$col_tot [$col_tot_desg]</b>";
                                                $ftot+=$col_tot;
                                                $ftot_dsg+=$col_tot_desg;
                                        ?></td><?php 
                                    }
                                    //last corner td  to show last row n col total
                                    print"<td><b>$ftot [$ftot_dsg]<b></td>";
                                ?>
                            </tbody>
                        </table>
                    </div>&nbsp;<br/> <!-- end class=grid_10 -->
                 </div><div class="clear"></div>
            </div>  
        <?php }  ?>
</div>
<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect:true}); </script>

<?php
	} // pageContent()

}

