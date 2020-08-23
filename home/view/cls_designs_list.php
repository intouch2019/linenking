<?php
require_once ("view/cls_renderer.php");
require_once ("lib/db/DBConn.php");
require_once "lib/core/Constants.php";
require_once "lib/items/clsItems.php";

class cls_designs_list extends cls_renderer {
    var $params;
    var $storeid;
    var $ctg="";
    var $pg=0;
    function __construct($params=null) {
	parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Dispatcher));
        $this->params = $params;
        if (isset($params['ctg']))
            $this->ctg = $params['ctg'];
        if (isset($params['pg']))
            $this->pg = $params['pg'];
        if (!$this->currUser) { return; }
        $this->storeid = $this->currUser->id;
    }
    function extraHeaders() {
        if (!$this->currUser) {
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
        window.location.href="designs/list/ctg="+value;
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
        $formResult = $this->getFormResult();
        $menuitem="designlist";
        include "sidemenu.".$this->currUser->usertype.".php";
        $active = "";
        $imgex = "";
        ?>
<div class="grid_10">
            <?php
            $db = new DBConn();
            $clsItems = new clsItems();
            $categories = $clsItems->getAllCategories();
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
                            foreach ($categories as $catg) {
                                ?>
                    <option value="<?php echo $catg->id; ?>"><?php echo $catg->name; ?></option>
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
                //echo $ctg;
		$pgstart = intval($this->pg) * 10;
                $q = "Select i.design_no,i.MRP,sum(i.curr_qty) as tot_qty,d.active,d.image from it_items i left outer join it_ck_designs d on i.design_no = d.design_no and i.ctg_id = d.ctg_id where i.ctg_id=$ctg group by i.design_no, i.MRP having tot_qty > 0 order by d.active desc,i.design_no, i.MRP limit $pgstart,10";
                $query = "Select i.design_no,i.MRP,sum(i.curr_qty) as tot_qty,d.active,d.image from it_items i left outer join it_ck_designs d on i.design_no = d.design_no and i.ctg_id = d.ctg_id where i.ctg_id=$ctg group by i.design_no, i.MRP having tot_qty > 0 order by d.active desc,i.design_no, i.MRP limit $pgstart,10";
                $activedesigns = $db->fetchObjectArray($query);
              //  $passivedesigns = $db->fetchObjectArray("select * from it_ck_items i,it_ck_designs d where i.ctg_id=$ctg and i.ctg_id!=d.ctg_id and i.design_no!=d.design_no group by i.design_no,i.mrp");
            } else {
                    $activedesigns = array();
            }
            $row_no = 0;
            $prev_design="";
	    if (intval($this->pg) > 0) { $prevpg = intval($this->pg) - 1; } else { $prevpg = false; }
	    if (count($activedesigns) == 10) { $nextpg = intval($this->pg) + 1; } else { $nextpg = false; }
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
            foreach ($activedesigns as $design) {

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
                                    $styleobj = $db->fetchObjectArray("select id,shortcode,display_name as style_name from it_ck_styles where ctg_id=$ctg order by sequence");
                                    $no_styles = count($styleobj);
                                    $sizeobj = $db->fetchObjectArray("select id,shortcode, display_name as size_name, size_group from it_ck_sizes where ctg_id=$ctg order by sequence");
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
                                            print "<tr id='styles::".$design->design_no."::".$styleobj[$k]->style_id."'><th>";
                                            echo $styleobj[$k]->style_name;
                                            print"</th>";
                                            //store style id in $stylecod
                                            $stylcod = $styleobj[$k]->style_id;
                                            for ($i = 0; $i < $no_sizes; $i++) {
                                                ?><td><?php
							$size_group = isset($sizeobj[$i]->size_group) ? $sizeobj[$i]->size_group : "'".$sizeobj[$i]->size_id."'";
                                                        $query = "select *,sum(curr_qty) as qty from it_ck_items where design_no = $design_no and MRP=$design->MRP and ctg_id=$ctg and style_id = '$stylcod' and size_id in ($size_group)";
//if ($design_no == "'99925'") { print "$query<br />"; }
                                                        $getitm = $db->fetchObject($query);
                                                        //check to see if specific item exists in order, if exist -> stores qty in order_qty
                                                        if ($getitm) {
                                                                ?><?php echo $getitm->qty; ?><?php
                                                        }
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
