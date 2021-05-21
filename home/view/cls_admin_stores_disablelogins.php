<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/clsProperties.php";
require_once "session_check.php";


class cls_admin_stores_disablelogins extends cls_renderer {
	function __construct($params=null) {
             $this->currStore = getCurrUser(); 
		//parent::__construct(array(UserType::Admin, UserType::CKAdmin));
//		$dbProperties = new dbProperties();
//		$dbProperties->setBoolean(Properties::DisableUserLogins, true);
//		header("Location: ".DEF_SITEURL."admin/stores");
	}

	function extraHeaders() {
	}

	public function pageContent() {
            
            if (getCurrUser()) {
            $menuitem="sedreason";
            include "sidemenu.".$this->currStore->usertype.".php";
        }
        $formResult = $this->getFormResult();
            
?>
   <div class="grid_10">
    <div class="grid_2">&nbsp;</div>
    <div class="grid_8" style="overflow:auto">
        <legend>Enter Stores Logins Disable Reason</legend>
        <form id="edreasonform" name="edreasonform" method="post" action="formpost/storeLoginDisableReason.php">
            <table>                  
                <tr>
                    <td colspan="4">Reason for Disabling Stores:</td>
                    <td colspan="10"><textarea name="edreason" id="edreason" rows="5" cols="50"></textarea></td>
                </tr> 
                <tr>
                    <td colspan="4"><input type="hidden" id="cid" name="cid" value="-1">                         
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
	
	}
}
