<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once "lib/core/Constants.php";



class cls_stores_disablelogin_reason extends cls_renderer {
    var $storeid="-1";
    
    function __construct($params=null) {
        
        $this->currStore = getCurrUser();
        $this->params = $params;
	//parent::__construct(array(UserType::Admin, UserType::CKAdmin));
	if (isset($params['id'])) { $this->storeid = $params['id']; }
//	else { print "Nothing to enable"; return; }
//	$id = trim($id);
//	$db = new DBConn();
//	$db->execUpdate("update it_codes set inactive=0 where id=$id");
//	header("Location: ".DEF_SITEURL."admin/stores");
//	exit;
    }

    function extraHeaders() {
       ?>
<link rel="stylesheet" href="jqueryui/css/custom-theme/jquery-ui-1.8.14.custom.css" type="text/css" media="screen" charset="utf-8" />
<script type="text/javascript" src="js/expand.js"></script>
<script language="JavaScript" src="js/tigra/validator.js"></script>
<script type="text/javascript">

</script>
<?php
    } //end of extra headers

    public function pageContent() {
        if (getCurrUser()) {
            $menuitem="sedreason";
            include "sidemenu.".$this->currStore->usertype.".php";
        }
        $formResult = $this->getFormResult();
        $db = new DBConn();
        $query = "select * from it_codes where usertype = ".UserType::Dealer." and id = ".$this->storeid;
        $sobj = $db->fetchObject($query);
        if(isset($sobj)){
        
?>
<div class="grid_10">
    <div class="grid_2">&nbsp;</div>
    <div class="grid_8" style="overflow:auto">
        <legend>Enter Store Disable Reason</legend>
        <form id="edreasonform" name="edreasonform" method="post" action="formpost/storeLoginDisableReason.php">
            <table>
                <tr>
                    <td colspan="4">Store Name :</td>
                    <td colspan="10"><?php echo $sobj->store_name; ?></td>
                </tr>  
                <tr>
                    <td colspan="4">Reason for Disabling:</td>
                    <td colspan="10"><textarea name="edreason" id="edreason" rows="5" cols="50"></textarea></td>
                </tr> 
                <tr>
                    <td colspan="4"><input type="hidden" id="cid" name="cid" value="<?php echo $sobj->id ; ?>">                         
                    </td>
                    <td colspan="10"><input type="submit" name="submit" value="Save"></td>
                </tr> 
                <tr>
                    <td colspan="14"><span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span></td>
                </tr>
            </table>
        </form>    
    </div>
</div>
<?php
        }else{
?>
 <h5>Invalid Store ID provided.</h5>
<?php
        }
    }
}
