<?php
require_once "view/cls_renderer.php";
require_once "lib/core/SimpleDateTime.class.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/strutil.php";

class cls_admin_graph_products extends cls_renderer {

	var $params;
	var $currStore;
	var $minDate, $maxDate, $rangeMin, $rangeMax;
        var $ctg,$dno,$mrp;
	function __construct($params=null) {
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
//        parent::__construct(array(UserType::Admin, UserType::CKAdmin));
         parent::__construct(array());    
        $this->currStore = getCurrUser(); 
        $this->params = $params;
        $this->db = new DBConn();
	$query = "select DATE_FORMAT(NOW() ,'%Y-%m-01') as mindate, date(now()) as maxdate";
		$obj = $this->db->fetchObject($query);
		if ($obj) {
			$this->rangeMin = ddmmyy($obj->mindate);
			$this->rangeMax = ddmmyy($obj->maxdate);
                        //print_r ($this->rangeMin);
		}
		if (isset($_SESSION['daterange'])) {
			list($this->minDate,$this->maxDate) = explode(",",$_SESSION['daterange']);
		} else {
			$this->minDate = ddmmyy($obj->mindate);
			$this->maxDate = ddmmyy($obj->maxdate);
                        $_SESSION['daterange'] = $this->minDate.",".$this->maxDate;
		}
                if (isset($params['ctg']))
                   // $this->ctg = sprintf("%03d",intval($params['ctg']));
                     $this->ctg = intval($params['ctg']);
                if (isset($params['dno']))
                    $this->dno = ($params['dno']);
                if (isset($params['mrp']))
                    $this->mrp = intval($params['mrp']);
        
        if (isset($_SESSION['storeid'])) { $this->storeid = $_SESSION['storeid']; }	
        else { $this->storeid = "All Stores"; }
	}

	function extraHeaders() {
if (!$this->currStore) { return; }
?>
        
        <script src="jqueryui/ui/jquery.ui.datepicker.js"></script>
        <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
        <script type="text/javascript" src="ofc/js/swfobject.js"></script>
        <script src="js/common.js"></script>
        <script type="text/javascript" src="js/expand.js"></script>
      <!--  <link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
        <script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>-->
   
<script>
//var gType=1; // 1 = numOrders, 2=byAmount
var gFieldName="linequantity";
var catid='<?php echo $this->ctg; ?>';
var mrp = '<?php echo $this->mrp; ?>';
var gBarFrom = 0;
var gBarNum = 50;

$(function() {
        <?php if ($this->rangeMin && $this->rangeMax) { ?>
		var minDate = '<?php echo $this->rangeMin; ?>';
		var maxDate = '<?php echo $this->rangeMax; ?>';
        <?php } ?>
	var dates = $( "#from, #to" ).datepicker({
		changeMonth: true,
		numberOfMonths: 1,
		dateFormat: 'dd-mm-yy',
		onSelect: function( selectedDate ) {
			var option = this.id == "from" ? "minDate" : "maxDate",
			instance = $( this ).data( "datepicker" ),
			date = $.datepicker.parseDate(
				instance.settings.dateFormat ||
				$.datepicker._defaults.dateFormat,
				selectedDate, instance.settings );
			dates.not( this ).datepicker( "option", option, date );
		}
	});     
});

    $(function() {
        // --- Using the default options:
        //$("h2.expand").toggler();
        // --- Other options:
        //$("h2.expand").toggler({method: "toggle", speed: "slow"});
        //$("h2.expand").toggler({method: "toggle"});
        //$("h2.expand").toggler({speed: "fast"});
        //$("h2.expand").toggler({method: "fadeToggle"});
        $("h2.expand").toggler({method: "slideFadeToggle"});
        //$("#content").expandAll({trigger: "h2.expand"});
    });

    $(function(){
	$("a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'fast',slideshow:3000, hideflash: true});
     });
</script>

<script type="text/javascript">
    
function loadChart(divName, url,width, height) {
    var minDate = "<?php echo $this->minDate; ?>";
    var maxDate = "<?php echo $this->maxDate; ?>";
    var mnDate, mxDate;
    fromElem=document.getElementById("from");
    toElem=document.getElementById("to");
    if (fromElem != null) minDate = fromElem.value;
    if (toElem != null) maxDate = toElem.value;
    if (minDate != null && maxDate != null) {
        var ajaxUrl = "ajax/changedate.php?mindate="+minDate+"&maxdate="+maxDate;
        $.getJSON(ajaxUrl, function(data){
             mnDate=data.mindate;
             mxDate=data.maxdate;
            url+="&d1="+mnDate;
            url+="&d2="+mxDate;
            encodedUrl = urlencode(url);
            //alert(url);
            swfobject.embedSWF(
              "ofc/open-flash-chart.swf", divName, width, height,
              "9.0.0", "expressInstall.swf",
              {"data-file":encodedUrl},
              {wmode:"transparent"}
              );
        });
    }
}

function loadTopLow() {
        //window.location.href="admin/graph/products/ctg="+catid;
        //alert("hello");
        if (!catid) {
           catid='8';<?php if (!isset($this->ctg)) $this->ctg='8'; ?>
        }
        if (!mrp) {
           mrp=1;<?php if (!isset($this->mrp)) $this->mrp='1'; ?>
        }
        var mrparray=new Array();
//        alert(mrp);
        $("#mrpselect").val(mrp);
        var ajaxUrl = "ajax/getMRP.php?cat="+catid;
        //alert(ajaxUrl);
        $.getJSON(ajaxUrl, function(data){
             console.log(data);
             var options = $('.mrpselect').attr('options');
             options.length=1;
             for (var i=0;i<data.length;i++) {
                    options[options.length] = new Option(data[i], data[i], false, false);
             }  
        });
   var title;
   if (gFieldName=="linequantity") { title="10 Most Selling Items by Quantity"; }
   else {title="Top 10 High Grossing Item by Item MRP"; }
    loadChart("pie_topN","chartdata/products/pie_toplow.php?order=desc&num=10&field="+gFieldName+"&catid="+catid+"&mrp="+mrp+"&title="+title,"550","300");
}

function loadCategory() {
    var x = $(window).width()*(2/3);
    loadChart("bar_category","chartdata/product_category.php?type="+gFieldName+"&mrp="+mrp,x,"300");
}

function loadByOrders() {
    <? $_SESSION['top10type']="totQty"; ?>
gFieldName="linequantity";
loadCategory();
loadTopLow();
}
function loadByRevenue() {
    <? $_SESSION['top10type']="totAmt"; ?>
    gFieldName="linetotal";
    loadCategory();
    loadTopLow();
}
function loadByCategory(cat) {
    catid=cat;//alert(catid);
    mrp=1;
    window.location.href="admin/graph/products/ctg="+cat;    
}

function loadByMrp(sel) {
    var value = sel.options[sel.selectedIndex].value; 
    mrp=value;
    window.location.href="admin/graph/products/ctg="+catid+"/mrp="+mrp;    
}

function clickEvent(msg,ctgid,designno) {
msg = unescape(msg);
    if (mrp==1) { window.location.href="admin/graph/products/ctg="+ctgid+"/dno="+designno; }
    else { window.location.href="admin/graph/products/ctg="+ctgid+"/mrp="+mrp+"/dno="+designno; }
}

function reload() {
    fromElem=document.getElementById("from");
    toElem=document.getElementById("to");
    if (fromElem != null) minDate = fromElem.value;
    if (toElem != null) maxDate = toElem.value;
    var daterange=minDate+","+maxDate;
    $.ajax({
            url: "savesession.php?name=daterange&value="+daterange,
            success: function(data) {
                    window.location.reload();
            }
    });
}

loadTopLow();
loadCategory();
</script>
<?php
	}

	public function pageContent() {
            $menuitem = "productgraph";
            include "sidemenu.".$this->currStore->usertype.".php";          
?>
<!-- div=colOne -->
<div class="grid_10">
		<h2>Product Analysis</h2>
<div class="grid_12">
    <div class="box">
        <label>Date range:</label>
        <label for="from">From</label>
        <input type="text" id="from" name="from" class="datepick" value="<?php echo $this->minDate; ?>" />
        <label for="to">to</label>
        <input type="text" id="to" name="to" class="datepick" value="<?php echo $this->maxDate; ?>" /> <span class="help">Change the date range and click on the "Reload" button</span>
        <input type="button" onclick="reload();" value="Reload" />
        <hr>
        Select Category : <br/>
        <div id="bar_category"></div><hr/>
        Select MRP :
        <select class="mrpselect" onchange="loadByMrp(this)" style="margin-left: 25px; width:100px;">
            <option value="1">All MRPs</option>
        </select>
        <hr>
        <div style="clear:both;margin-bottom:5px;">
        <form name="chartTypeForm">
        Show <input type="radio" name="chartType" checked onclick="loadByOrders();">By Sales</input>
        <input type="radio" name="chartType" onclick="loadByRevenue();">By Value</input> <span class="help" style="font-style:italic; font-weight: bold; padding-left: 10px;">Choose to toggle the display between Units and Value</span>
        </form>
        </div>
    </div>
    <div id="pie_topN"></div><br></br>
    <?php
        $ctgid=$this->db->safe($this->ctg);
        $desno=$this->db->safe($this->dno);
        if ($this->mrp !=1) { $mrpqry=" and oi.MRP=$this->mrp "; }
        else $mrpqry="";
        $datemin=$this->db->safe(yymmdd($this->minDate));
        $datemax=$this->db->safe(yymmdd($this->maxDate));
        
        $query1="select sum(oi.order_qty) as sumqty,i.style_id,i.size_id,oi.MRP from it_ck_pickgroup p,it_ck_orderitems oi , it_items i where i.ctg_id=$ctgid $mrpqry and oi.item_id = i.id and oi.order_id in (p.order_ids) and date(p.shipped_time) >= $datemin and date(p.shipped_time) <= $datemax group by i.style_id,i.size_id";
//        echo "<br/> ALL DESIGNS tot_prod_qry: ".$query1."<br/>";
        $totalprod=$this->db->fetchObjectArray($query1);
        $topdesigns=""; $combinedtop10 ="";
        $desquery = "select i.ctg_id,d.design_no,sum(oi.order_qty) as totQty,sum(oi.MRP) as totAmt from it_ck_pickgroup o,it_ck_orderitems oi,it_ck_designs d , it_items i where i.ctg_id=$ctgid $mrpqry and oi.order_id in (o.order_ids) and i.ctg_id=d.ctg_id and i.design_no=d.design_no and oi.item_id = i.id and date(o.shipped_time) >= $datemin and date(o.shipped_time) <= $datemax group by ctg_id,design_no order by totQty desc limit 10";
        //echo $desquery;
        $topdes=$this->db->fetchObjectArray($desquery);
        if ($topdes) {
            foreach ($topdes as $design) { $topdesigns .= "'".$design->design_no."',"; }
            $topdesigns = substr($topdesigns, 0, -1);
            //echo $topdesigns;
            $query2="select sum(oi.order_qty) as sumqty,i.style_id,i.size_id,oi.MRP from it_ck_pickgroup p,it_ck_orderitems oi , it_items i where i.ctg_id=$ctgid $mrpqry and oi.item_id = i.id and i.design_no in ($topdesigns) and oi.order_id in (p.order_ids) and date(p.shipped_time) >= $datemin and date(p.shipped_time) <= $datemax group by i.style_id,i.size_id";
            //Echo "<br/>Comb_top_10: ".$query2."<br>"; 
            $combinedtop10=$this->db->fetchObjectArray($query2);
        }
       
        $query3="select sum(oi.order_qty) as sumqty,i.style_id,i.size_id,oi.MRP,pt.name as prod_type,d.image from it_ck_pickgroup p,it_ck_orderitems oi,it_ck_designs d , it_items i , it_prod_types pt where i.ctg_id=$ctgid $mrpqry and oi.item_id = i.id and i.design_no=$desno and oi.order_id in (p.order_ids) and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.prod_type_id = pt.id and date(p.shipped_time) >= $datemin and date(p.shipped_time) <= $datemax group by i.style_id,i.size_id";      
        $prodinfo=$this->db->fetchObjectArray($query3);
        
        $styleobj = $db->fetchObjectArray("select s1.style_id, s2.name as style_name from it_ck_styles s1, it_styles s2 where s1.style_id=s2.id and s2.is_active = 1 and  s1.ctg_id=$ctgid order by sequence");
        $no_styles = count($styleobj);       
        $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1, it_sizes s2 where s1.size_id = s2.id and s1.ctg_id=$ctgid order by sequence");       
        $no_sizes = count($sizeobj);        
        $ctgobj = $db->fetchObject("select name from it_categories where id = $this->ctg ");
                ?>  
 <div class="box" id="alldesigns">
        <h2 class="expand">CATEGORY: <?php echo $ctgobj->name; ?> | ALL DESIGNS COMBINED ORDERS DURING PERIOD<?php if ($this->mrp==1) { ?> | MRP = ALL MRPs <?php } else { ?> | MRP = <?php echo $this->mrp; } ?></h2>
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
                                            //to get the quantity and stock id of specific item                                            
                                            $sizeid = $sizeobj[$i]->size_id;
                                            $iqr ="select sum(oi.order_qty) as sumqty from it_ck_pickgroup p,it_ck_orderitems oi , it_items i where i.ctg_id=$ctgid $mrpqry and oi.item_id = i.id and oi.order_id in (p.order_ids) and date(p.shipped_time) >= $datemin and date(p.shipped_time) <= $datemax  and i.size_id = $sizeid and i.style_id = $stylcod ";
                                            $getitem = $db->fetchObject($iqr);
                                             if($getitem){ print $getitem->sumqty;}   
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
    <?php if ($combinedtop10) { ?>
    <div class="box" id="combinedtop10">
        <h2 class="expand">CATEGORY: <?php echo $ctgobj->name; ?> | TOP 10 DESIGNS COMBINED ORDERS DURING PERIOD<?php if ($this->mrp==1) { ?> | MRP = ALL MRPs <?php } else { ?> | MRP = <?php echo $this->mrp; } ?></h2>
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
                                            //to get the quantity and stock id of specific item
                                              $sizeid = $sizeobj[$i]->size_id;
                                              $getitemcomdes = $db->fetchObject("select sum(oi.order_qty) as sumqty  from it_ck_pickgroup p,it_ck_orderitems oi , it_items i where i.ctg_id=$ctgid $mrpqry and oi.item_id = i.id and i.design_no in ($topdesigns) and oi.order_id in (p.order_ids) and date(p.shipped_time) >= $datemin and date(p.shipped_time) <= $datemax and i.style_id = $stylcod and i.size_id = $sizeid ");
                                              if($getitemcomdes){
                                                  print $getitemcomdes->sumqty;
                                              }
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
    
<?php } if ($prodinfo) { ?>
    <div class="box" id="prod">
        <h2 class="expand">CATEGORY: <?php echo $ctgobj->name; if ($prodinfo[0]->prod_type != NULL) {?> | PRODUCTION TYPE: <?php echo $prodinfo[0]->prod_type; }?> | DESIGN NO: <?php echo $this->dno; ?><?php if ($this->mrp==1) { ?> | MRP = ALL MRPs <?php } else { ?> | MRP = <?php echo $this->mrp; } ?></h2>
        <div class="collapse" id="accordion"><Br/>
<?php if ($prodinfo[0]->image) { ?>
                <a href="images/stock/<?php echo $prodinfo[0]->image; ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="images/stock/<?php echo $prodinfo[0]->image; ?>" width="170" /></a>
<?php } else { ?>
                <a href="images/stock/<?php echo $prodinfo[0]->image; ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="images/stock/nophoto.jpeg" width="170" /></a>
<?php } ?>
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
                                            print "<tr><th>";
                                            echo $styleobj[$k]->style_name;
                                            print"</th>";
                                            //store style id in $stylecod
                                            $stylcod = $styleobj[$k]->style_id;
                                            for ($i = 0; $i < $no_sizes; $i++) {
                                                ?><td><?php
                                                         //to get the quantity and stock id of specific item
                                                          $sizeid = $sizeobj[$i]->size_id;
                                                          $getitemprodInfo = $db->fetchObject("select sum(oi.order_qty) as sumqty,i.style_id,i.size_id,oi.MRP,pt.name as prod_type,d.image from it_ck_pickgroup p,it_ck_orderitems oi,it_ck_designs d , it_items i , it_prod_types pt where i.ctg_id=$ctgid $mrpqry and oi.item_id = i.id and i.design_no=$desno and oi.order_id in (p.order_ids) and i.ctg_id=d.ctg_id and i.design_no=d.design_no and i.prod_type_id = pt.id and date(p.shipped_time) >= $datemin and date(p.shipped_time) <= $datemax and i.style_id = $stylcod and i.size_id = $sizeid ");
                                                          if($getitemprodInfo){
                                                              print $getitemprodInfo->sumqty;
                                                          }
                                                ?></td><?php
                                            }
                                            print "</tr>";
                                        }
                                        ?>
                        </tbody>
                    </table>
                </div>&nbsp;<br/> <!-- end class=grid_10 -->
             </div><div class="clear"></div>
        </div> <!-- end class="block" -->
        <?php } ?>
    </div>
    <!--<div id="bar_allitems"></div>-->
</div>



<?php
	} // pageContent()

}

?>
