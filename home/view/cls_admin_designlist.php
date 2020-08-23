<?php
require_once ("view/cls_renderer.php");
//require_once ("lib/codes/clsStocks.php");
require_once ("lib/db/DBConn.php");
require_once "lib/core/Constants.php";

class cls_admin_designlist extends cls_renderer {
    var $params;
    var $currStore;
    var $storeid;
    var $ctg="";
    var $pg=0;
    function __construct($params=null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (isset($params['ctg']))
            $this->ctg = $params['ctg'];
        if (isset($params['pg']))
            $this->pg = $params['pg'];
        if (!$this->currStore) { return; }
        $this->storeid = $this->currStore->id;
    }
    function extraHeaders() {
        if (!$this->currStore) {
            return; }
        ?>
<script type="text/javascript" src="js/expand.js"></script>
<link rel="stylesheet" type="text/css" href="css/dark-glass/sidebar.css" />
<script type="text/javascript" src="js/sidebar/jquery.sidebar.js"></script>
<link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
<script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>

<link href="uploadify/uploadify.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="uploadify/swfobject.js"></script>
<script type="text/javascript" src="uploadify/jquery.uploadify.v2.1.4.min.js"></script>


<script type="text/javascript">
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
    });
    
$(function(){
	$("a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'fast',slideshow:3000, hideflash: true});
});

function addimage(form_id)
{
        var ctg = $("#ctgid"+form_id).val();
        var des = $("#desno"+form_id).val();

        $("#imageUpload").uploadify({
		'uploader'       : 'uploadify/uploadify.swf',
		'script'         : 'test/load.php',
		'cancelImg'      : 'uploadify/cancel.png',
		'folder'         : 'images',
		'auto'           : false,
		'multi'          : false,
		'queueSizeLimit' : 1,
                'buttonText'     : 'Select Image',
		'fileDesc'	 : 'jpg, gif, png',
		'fileExt'        : '*.jpg;*.gif;*.png',
                'removeCompleted': false,
		'sizeLimit'      : '512000',//max size bytes - 500kb
		'checkScript'    : 'uploadify/check.php', //if we take this out, it will never replace files, otherwise asks if we want to replace
                'onError'        : function (event,ID,fileObj,errorObj) {
                            alert(errorObj.type + ' Error: ' + errorObj.info); }
                //'onAllComplete'  : function() {
                                    //alert("image added !");
                                        //$('#switch-effect').unbind('change');
                                        //$('#toggle-slideshow').unbind('click');
                                        //galleries[0].slideshow.stop();
                                        //start();
                                   // }
	});
}

    function setCat(dropdown)
    {
        var idx = dropdown.selectedIndex;
        var value = dropdown.options[idx].value;
        window.location.href="admin/designlist/ctg="+value;
    }

    function activate(form_id)
    {
            var formname=eval("order_"+form_id);
            var params = $(formname).serialize();
            var ajaxUrl = "ajax/activateDesign.php?"+params;
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

<style>
.box #activ a, .box #activ a.visible {
color: white;
background: #20562f;
display: block;
padding: 6px 12px;
margin: -6px -12px;
border: none;
}

.box #activ a:hover {
background-color: #2D8745;
}
</style>

    <?php
    }
    public function pageContent() {
        if ($this->currStore->usertype == UserType::Admin || $this->currStore->usertype == UserType::CKAdmin) {} else { print "Unauthorized Access"; return; }
        $formResult = $this->getFormResult();
        $menuitem="designlist";
        include "sidemenu.".$this->currStore->usertype.".php";
        $active = "";
        $imgex = "";
        ?>
<div class="grid_10">
            <?php
            $db = new DBConn();
//            global $g_categories;
            ?>
    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset class="login">
            <legend>Select Category</legend>
            <p class="notice">Select category to view all designs</p>
            <p>
                <label></label>
                <select name ="setprice" style="width:160px" onchange="setCat(this);">
                    <option value=0 selected="selected">Select Category</option>
                            <?php
                            $query = "select * from it_categories where active = 1";
                            $categories = $db->fetchObjectArray($query);
                            foreach ($categories as $catg) {
                                $selected = "";
                                if($catg->id == $this->ctg){$selected="selected";}
                                ?>
                    <option value="<?php echo $catg->id; ?>" <?php echo $selected;?>><?php echo $catg->name; ?></option>
                            <?php
                            }
                            ?>
                </select>
            </p>
        </fieldset>
    </div>

            <?php
            if ($this->ctg) {
                $ctg=$db->safe($this->ctg);
		$pgstart = intval($this->pg) * 10;
                $query = "Select i.design_no,i.MRP,sum(i.curr_qty) as tot_qty,d.active,d.image from it_items i left outer join it_ck_designs d on i.design_no = d.design_no and i.ctg_id = d.ctg_id where i.ctg_id=$ctg group by i.design_no, i.MRP having tot_qty > 0 order by d.active desc , i.design_no, i.MRP limit $pgstart,10";
                $alldesigns = $db->fetchObjectArray($query);
              //  $passivedesigns = $db->fetchObjectArray("select * from it_ck_items i,it_ck_designs d where i.ctg_id=$ctg and i.ctg_id!=d.ctg_id and i.design_no!=d.design_no group by i.design_no,i.mrp");
            } else {
                    $alldesigns = array();
            }
            $row_no = 0;
            $prev_design="";
	    if (intval($this->pg) > 0) { $prevpg = intval($this->pg) - 1; } else { $prevpg = false; }
	    if (count($alldesigns) == 10) { $nextpg = intval($this->pg) + 1; } else { $nextpg = false; }
?>
<div class="grid_12" style="text-align:right;">
<?php if ($prevpg !== false) { ?>
<a href="admin/designlist/ctg=<?php echo $this->ctg; ?>/pg=<?php echo $prevpg; ?>/">Prev</a>&nbsp;&nbsp;
<?php } ?>
<?php if ($nextpg !== false) { ?>
<a href="admin/designlist/ctg=<?php echo $this->ctg; ?>/pg=<?php echo $nextpg; ?>">Next</a>
<?php } ?>
</div>
<?php
            foreach ($alldesigns as $design) {

                $design_no = $db->safe($design->design_no);
                $divid = "accordion-" . $row_no;
        if ($design_no != $prev_design) {
            $row_no++;
        if ($prev_design != "") { ?>
                    <div class="clear"></div>
                    <div id="status_<?php echo ($row_no-1); ?>"></div>
                    <?php if ($active == "1") { ?>
                    <button onclick="activate(<?php echo ($row_no-1); ?>); return false;"style="float:right;">Deactivate</button>
                    <?php } else { ?>
                    <button onclick="activate(<?php echo ($row_no-1); ?>); return false;"style="float:right;">Activate</button>
                    <?php } ?>
                </div> <!-- end class=grid_10 -->               
            </form>
        </div> <!-- end class="block" -->
    </div> <!-- end class="box" -->
    <?php } ?>
    <div class="clear"></div>
    <div class="box">
        <?php if ($design->active == "1") { $active=1;?> <h2 class="expand" id="activ">Design No: <?php echo $design->design_no ?></h2> <?php }
              else { $active=0; ?> <h2 class="expand" id="notactiv">Design No: <?php echo $design->design_no ?></h2> <?php } ?>

        <div class="collapse" id="<?php echo $divid; ?>">
            <form name="order_<?php echo $row_no; ?>" method="post" action="">
                <input type="hidden" id="ctgid<?php echo $row_no?>" name="category" value="<?php echo $this->ctg; ?>" />
                <input type="hidden" id="desno<?php echo $row_no?>" name="design_no" value="<?php echo $design->design_no; ?>" />
                <input type="hidden" name="active" value="<?php echo $active; ?>" />
                <a href="images/stock/<?php if ($design->image){ echo $design->image; $imgex=1;} else $imgex=0; ?>" rel="prettyPhoto"><img class="grid_2" id="thumb" align="left" src="images/stock/<?php echo $design->image; ?>" width="170" /></a>
                <div class="block grid_10">
  <?php } ?>

                    <table>
                                    <?php
                                    $ctg=$this->ctg;
                                    $desno=$design->design_no;
                                    $query0 = "select sum(i.curr_qty) as stkqty,i.style_id,i.size_id,i.MRP from it_items i,it_ck_designs d where i.curr_qty > 0 and i.ctg_id=$ctg and i.ctg_id=d.ctg_id and i.design_no=d.design_no and d.active=1 group by i.style_id,i.size_id";
                                    //echo $query0;
                                    $stockinfo=$db->fetchObjectArray($query0);
//                                    $styleobj = $db->fetchObjectArray("select style_id,shortcode,display_name as style_name from it_ck_styles where ctg_id=$ctg order by sequence");
                                     $styleobj = $db->fetchObjectArray("select s.id,s.name as style_name from it_styles s,it_ck_styles st where st.ctg_id=$ctg and st.style_id=s.id order by sequence");
                                     $no_styles = count($styleobj);
//                                    $sizeobj = $db->fetchObjectArray("select size_id, shortcode, display_name as size_name, size_group from it_ck_sizes where ctg_id=$ctg order by sequence");
                                    $sizeobj = $db->fetchObjectArray("select s.id,s.name as size_name from it_sizes s,it_ck_sizes si where si.ctg_id=$ctg and si.size_id=s.id order by sequence");
                                    $no_sizes = count($sizeobj);
                                    ?>
                        <thead>
                            <tr><th><?php echo "MRP: $design->MRP"; ?></th>
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
                                for ($i = 0; $i < $no_sizes; $i++) {
                                    ?><td><?php
                                            $total=0;
                                            foreach ($stockinfo as $prod) {
                                                if ($prod->size_id == $sizeobj[$i]->id && $prod->style_id==$styleobj[$k]->id) {
                                                   {$total+=intval($prod->stkqty);}
                                                }  
                                            }
                                            print $total;
                                    ?></td><?php
                                }
                                print "</tr>";
                            }
                            ?>           
                        </tbody>

                    </table>
                    <?php
    $prev_design = $design_no;
?>

            <?php
            }
            ?> 
<?php if ($prev_design != "") { ?>
	<div id="status_<?php echo $row_no; ?>"></div>
                <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                
                <?php if ($active == "1") { ?>
                <button onclick="activate(<?php echo $row_no; ?>); return false;"style="float:right;">Deactivate</button>
                <?php } else if ( $active == "0" ){ ?>
                <button onclick="activate(<?php echo $row_no; ?>); return false;"style="float:right;">Activate</button>
                <?php } ?>    
                </div> <!-- end class=grid_10 -->
                
            </form>
        </div> <!-- end class="block" -->
    </div> <!-- end class="box" -->
<?php } ?>
</div>
    <?php
    }
}
?>
