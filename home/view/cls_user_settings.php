<?php
require_once ("view/cls_renderer.php");
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");
require_once ("session_check.php");

class cls_user_settings extends cls_renderer {
    var $currUser;
    var $userid;
    function __construct($params=null) {
        $this->currUser = getCurrUser();
        if (!$this->currUser) { return; }
        $this->userid = $this->currUser->id;
    }

    function extraHeaders() { ?>

<script language="JavaScript" src="js/tigra/validator.js"></script>
<script>
    function changeInfo(){
        $("#storeInfo").hide();
        $("#storeAdd").show();
    }
</script>
    <?php
    } //end of extra headers

    public function pageContent() {
        $formResult = $this->getFormResult();
        $menuitem="settings";
        include "sidemenu.".$this->currUser->usertype.".php";

        ?>
<div class="grid_10">
            <?php
            $db = new DBConn();
            $userInfo=$db->fetchObject("select * from it_codes where id=$this->userid");
            ?>
    <div class="box">
        <h2>
            <a id="toggle-forms">User Details</a>
        </h2>
        <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow-x: hidden; overflow-y: hidden; ">
            <div class="block" style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; ">
                <form action="formpost/changePassword.php" method="post">
                    <fieldset class="login">
                        <legend>Login Information</legend>
                        <p>
                            <label>Username: </label>
                            <?php echo $userInfo->code; ?>
                        </p>
                        <p>
                            <label>Password: </label>
                            <input type="password" name="password">
                        </p>
                        <p>
                            <label>Re-type Password: </label>
                            <input type="password" name="password2">
                        </p>
                        <p>
                            <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                        </p>
                        <input class="confirm button" type="submit" value="Change Password">
                    </fieldset>
                </form>

            </div>
        </div>
    </div>

</div> <!-- end class=grid_10 -->
    <?php
    }
}
?>
