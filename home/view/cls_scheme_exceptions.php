<?php
require_once "view/cls_renderer.php";
require_once "lib/items/clsItems.php";
require_once "lib/core/Constants.php";
require_once "lib/db/DBConn.php";

class cls_scheme_exceptions extends cls_renderer {
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
    } // extraHeaders

    public function pageContent() {
        $formResult = $this->getFormResult();
        $menuitem = "schemeXcpList";
        include "sidemenu.".$this->currStore->usertype.".php";
        ?>
<div class="grid_10">

    <div class="grid_3">&nbsp;</div>
    <div class="grid_6">
        <fieldset>
            <legend>Create New Expception List</legend>
    <form id="addform" name="addform" enctype="multipart/form-data" method="post" action="formpost/postAddExceptionList.php">
        <div class="clsDiv">Exception List Name</div>
        <div class="clsText"><input id="name" name="name" size="40" value="<?php if (isset($_SESSION['form_name'])) {echo $_SESSION['form_name'];} ?>"/></div>
<br />
        <div class="clsDid">Barcodes File (A .txt file that contains a list of barcodes one on each line)</div>
        <div class="clsText"><input type="file" id="xcp_file" name="xcp_file" ></div>
<br />
        <input type="submit" value="Create Exception List"/>
        <input type="hidden" name="form_id" value="1"/>
        <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
    </form>
        </fieldset>
    </div>
</div>
<div class="grid_10">
<div class="grid_2">&nbsp;</div>
<div class="grid_8">
<table>
<tr>
<th style="width:10%;">ID</th>
<th style="width:70%;">Name</th>
<th style="width:20%;">&nbsp;</th>
</tr>
<?php
$db = new DBConn();
$count=1;
$xcplists = $db->fetchObjectArray("select * from it_rule_exceptions order by id");
foreach ($xcplists as $xcp) { ?>
<tr>
<td><?php echo $xcp->ID; ?></td>
<td><?php echo $xcp->NAME; ?></td>
<td><a href="<?php echo DEF_SITEURL.'download_scheme.php?id='.$xcp->ID; ?>">Download</a></td>
</tr>
<? }
if (count($xcplists) == 0) { ?>
<tr><td colspan="3" style="text-align:center;">No data</td></tr>
<?php }
?>
</table>
</div>
</div>
    <?php
    } //pageContent
}//class
?>
