<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once "lib/core/Constants.php";
require_once ("lib/db/DBConn.php");

class cls_admin_featureddesign extends cls_renderer {
    var $params;
    var $currStore;
    var $storeid;
    function __construct($params=null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (!$this->currStore) { return; }
        $this->storeid = $this->currStore->id;
    }

    function extraHeaders() {
        if (!$this->currStore) {
            return;
	}
?>
<link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
<script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>

<script type="text/javascript">
     $(function(){
	$("a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'fast',slideshow:3000, hideflash: true});
});

</script>
<?php

    } // extraHeaders

    public function pageContent() {
        //if ($this->currStore->usertype != UserType::Admin && $this->currStore->usertype != UserType::CKAdmin && $this->currStore->usertype != UserType::Manager) { print "Unauthorized Access"; return; }
        $formResult = $this->getFormResult();
        $menuitem="featureddesigns";
        include "sidemenu.".$this->currStore->usertype.".php";
        $db = new DBConn();
        //$featdes = $db->fetchObjectArray("select * from it_ck_featureddesigns");
        $featdes = $db->fetchObjectArray("select fd.*,c.name from it_ck_featureddesigns fd , it_categories c where fd.ctg_id = c.id ");
        //print_r ($featdes);
        $clsItems = new clsItems();
        //$categories = $clsItems->getAllCategories();
        $categories = $db->fetchObjectArray("select * from it_categories where active = 1 ");
        ?>
<div class="grid_10">

    
    <div class="grid_4">
        <fieldset>
            <legend>Upload Featured Design</legend>
    <form id="addform" name="addform" enctype="multipart/form-data" method="post" action="formpost/addFeaturedDesign.php">
        <div class="clsDiv">Item Category</div>
        <div class="clsText" style="margin-bottom: 15px;"><select  name="category">
                        <?php foreach ($categories as $ctg) {
			if ($ctg->id == $this->getFieldValue('category')) { $selected = "selected"; }
			else { $selected = ""; }
			?>
                <option value="<?php echo $ctg->id; ?>" <?php echo $selected; ?>><?php echo $ctg->name; ?></option>
                        <?php } ?>
            </select></div>

        <div class="clsDiv"  >Design Number</div>
        <div class="clsText" style="margin-bottom: 15px;"><input id="design_no" name="design_no" value="<?php if (isset($_SESSION['form_design_no'])) {echo $_SESSION['form_design_no'];} ?>"/></div>  
        <input type="submit" value="Add Design"/>
        <input type="hidden" name="form_id" value="1"/>
    </form>
        </fieldset>
    </div>
    <div class="grid_8">
        <fieldset>
            <legend>Currently Featured Designs</legend>
            <?php if (count($featdes)==0) { ?>
                <div>There are no designs to be featured in store home page.</div>
            <?php } else { foreach ($featdes as $design) { ?> 
                <div class="grid_6" style="margin-bottom:20px;">
                    <form method="post" action="formpost/removeFeaturedDesign.php">
                        <a href="images/stock/<?php echo $design->image; ?>" rel="prettyPhoto"><img class="grid_5" align="left" src="images/stock/<?php echo $design->image; ?>" width="100" /></a>
                        <label style="font-weight:bold; font-size:small;margin-left:10px;">Category : </label>
                        <label style="font-style: italic; font-weight:bold; font-size:small;margin-left:10px;"><?php echo $design->name; //echo $g_categories[$design->ctg_id]; ?></label>
                        <input type="hidden" name ="category" value="<?php echo $design->ctg_id; ?>"/>
                        <label style="font-weight:bold; font-size:small;margin-left:10px;">Design No : </label>
                        <label style="font-style: italic; font-weight:bold; font-size:small;margin-left:10px;"><?php echo $design->design_no; ?></label>
                        <input type="hidden" name="design_no" value="<?php echo $design->design_no; ?>"/>
                        <input style="margin:20px;margin-left:35px;" type="submit" value="REMOVE"/>
                    </form>
                </div>
            <?php } } ?>
        </fieldset>
    </div>
    <div class="grid_4">
        <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
    </div>
    
</div>
    <?php
    } //pageContent
}//class
?>