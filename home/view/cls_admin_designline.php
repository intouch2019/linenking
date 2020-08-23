<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";

class cls_admin_designline extends cls_renderer {

    var $params;
    var $result;
    var $param_design_no;
    var $dno;
    var $currUser;

    function __construct($params=null) {
	//parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Dispatcher, UserType::Manager));
        $this->params = $params;
        $this->currUser = getCurrUser();
        if (isset($params['dno']))
            $this->param_design_no = $params['dno'];
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
        var design_no=document.getElementById("design_no").value;
        window.location.href="admin/designline/dno="+design_no;
    }

</script>


    <?php
    }

    //extra-headers close

    public function pageContent() {
	$menuitem = "designline";
        $formResult = $this->getFormResult();
        include "sidemenu.".$this->currUser->usertype.".php";
        ?>

<div class="grid_10">
            <?php
            $db = new DBConn();
		$allDesigns = array();
            if ($this->param_design_no && $this->param_design_no!="other") {
		$code = $db->safe($this->param_design_no);
                //echo "select d.design_no, d.id as designid,d.ctg_id,d.image,d.lineno,d.rackno,d.active from it_items i,it_ck_designs d where d.design_no=$code and i.design_no=d.design_no and i.ctg_id=d.ctg_id group by d.ctg_id,d.design_no,i.MRP";
               // $allDesigns = $db->fetchObjectArray("select i.MRP,d.design_no, d.id as designid,d.ctg_id,d.image,d.lineno,d.rackno,d.active,ctg.name as ctgname from it_items i,it_categories ctg,it_ck_designs d where d.design_no=$code and d.ctg_id=ctg.id and i.design_no=d.design_no and i.ctg_id=d.ctg_id group by d.ctg_id,d.design_no,i.MRP");
                 $allDesigns = $db->fetchObjectArray("select i.MRP,i.is_design_mrp_active,d.design_no, d.id as designid,d.ctg_id,d.image,d.lineno,d.rackno,d.active,ctg.name as ctgname from it_items i,it_categories ctg,it_ck_designs d where d.design_no=$code and d.ctg_id=ctg.id and i.design_no=d.design_no and i.ctg_id=d.ctg_id group by d.ctg_id,d.design_no,i.MRP");
            }
//            } else if ($this->param_design_no=="other") {
//                $allDesigns = $db->fetchObjectArray("select id as designid,image,lineno,rackno,active,design_no,ctg_id from it_ck_designs d where ctg_id='oth'");
//            }
            ?>

    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset class="login">
            <legend>Design Search</legend>
            <p class="notice">Enter the Design Number and press [Enter] or click on the [Search] button</p>
            <p>
                <label>Design Number: </label>
                <input type="text" id="design_no" style="width:170px;" name="design_no" value="<?php echo $this->param_design_no; ?>" >
                <button id="searchBtn" onclick="search()">Search</button>
            </p>
<?php if ($this->param_design_no != "" && count($allDesigns) == 0) { ?>
	<p class="error">No Designs found matching "<?php print_r ($allDesigns);echo $this->param_design_no; ?>"</p>	
<?php } ?>
        </fieldset>
    </div>
    <div class="block grid_10"><span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span></div>
            <?php
            $row_no = 0;
            if ($this->param_design_no=="other") {
                foreach ($allDesigns as $design) { ?>
                    <div class="block grid_10">
                        <div class="box">
                            <form method="post" action="formpost/updateLine.php">
                            <label style="width:10%; font-weight: bold;"><?php echo $design->design_no; ?></label>
                            <p style="margin-left:15px;">
                                <input type="hidden" name="designctg" value="<?php echo $design->ctg_id?>"/>
                                <input type="hidden" name="designid" value="<?php echo $design->designid?>"/>
                                Line No : <input type="text" name="lineno" style="width:4%; margin-right:20px;" value="<?php if ($design->lineno) echo $design->lineno; ?>"/> 
                                Rack No : <input type="text" name="rackno" style="width:4%; margin-right:20px;" value="<?php if ($design->rackno) echo $design->rackno; ?>"/> 
                                <input type="submit" value="Update">
                            </p>
                            </form>
                        </div>
                    </div>
                <?php
                }
            } else { //echo "sdfsdfs";
            foreach ($allDesigns as $design) {
                $row_no++;
                $design_no = $db->safe($design->design_no);
		$ctg_id = $db->safe($design->ctg_id);

//                $query = "select sum(i.curr_qty) as total from it_items i, it_ck_styles t where i.design_no = $design_no and i.MRP=$design->MRP and i.ctg_id = t.ctg_id and i.style_id = t.style_id group by i.ctg_id,i.design_no, i.MRP";
//                $obj = $db->fetchObject($query);
//
//                if (!$obj) {
//                    continue;
//                }
              //  $state = $design->active ? "ACTIVE " : "INACTIVE ";
                $state = $design->is_design_mrp_active ? "ACTIVE " : "INACTIVE ";
                $divid = "accordion-" . $row_no;
                ?>
    <div class="clear"></div>
    <div class="box">
        <h2 class="expand">[<?php echo $state; ?>] Category: <?php echo $design->ctgname; ?> | Design No: <?php echo $design->design_no." [ MRP: ".$design->MRP." ]"; ?> [ Line No: <?php echo $design->lineno; ?> ] [ Rack No: <?php echo $design->rackno; ?> ]</h2>
        <div class="collapse" id="<?php echo $divid; ?>">
<?php if ($design->image) { ?>
                <a href="images/stock/<?php echo $design->image; ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="images/stock/<?php echo $design->image; ?>" width="170" /></a>
<?php } else { ?>
                <a href="images/stock/<?php echo $design->image; ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="images/stock/nophoto.jpeg" width="170" /></a>
<?php } ?>

                <div class="block grid_10">
                    <table>
                                    <?php
                                    $styleobj = $db->fetchObjectArray("select s1.style_id, s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.style_id=s2.id and ctg_id=$ctg_id order by sequence");
                                    $no_styles = count($styleobj);
                                    $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.size_id=s2.id and ctg_id=$ctg_id order by sequence");
                                    $no_sizes = count($sizeobj);
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
                                                        $query = "select id,curr_qty as qty from it_items where design_no = $design_no and MRP=$design->MRP and ctg_id=$ctg_id and style_id = '$stylcod' and size_id = '$sizeid'";
                                                        $getitm = $db->fetchObject($query);
                                                        //$fname = "qty_" . $styleobj[$k]->style_id . "_" . $getitm->size_id;
                                                        //check to see if specific item exists in order, if exist -> stores qty in order_qty  
                                                        if ($getitm) {
                                                                print "[ $getitm->qty ]";
                                                        }
                                                ?></td><?php
                                            }
                                            print "</tr>";
                                        }
                                        ?>
                        </tbody>
                    </table>
                    <form method="post" action="formpost/updateLine.php">
                        <input type="hidden" name="designctg" value="<?php echo $design->ctg_id?>"/>
                        <input type="hidden" name="designid" value="<?php echo $design->designid?>"/>
                        <label style="margin-left:20px; font-weight:bolder;">Line No :</label><input style="width:6%;" type="text" name="lineno" value="<?php if ($design->lineno) echo $design->lineno; ?>" />
                        <label style="margin-left:20px; font-weight:bolder;">Rack No :</label><input style="width:6%;" type="text" name="rackno" value="<?php if ($design->rackno) echo $design->rackno; ?>"/>
                        <input style="margin-left:15px;"type="submit" value="Update"/>
                    </form>
                </div> <!-- end class=grid_10 -->
        </div> <!-- end class="block" --><div class="clear"></div>
    </div> <!-- end class="box" -->

    <div class="clear"></div>
            <?php
            } }
            ?>
</div>
    <?php
    }
}
?>
