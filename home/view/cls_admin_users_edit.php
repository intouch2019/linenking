<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_admin_users_edit extends cls_renderer {
    var $currUser;
    var $userid;
    function __construct($params=null) {
        $this->currUser = getCurrUser();
        $this->params = $params;
	if (isset($this->params['id'])) {
		$this->userid = $this->params['id'];
	}
        if (!$this->currUser) { return; }
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

    <?php
    }

    //extra-headers close
    public function pageContent() {
        //if ($this->currUser->usertype == UserType::Admin || $this->currUser->usertype == UserType::CKAdmin) {} else { print "Unauthorized Access"; return; }
	$menuitem = "users";
        include "sidemenu.".$this->currUser->usertype.".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
	$user = $db->fetchObject("select * from it_codes where id=$this->userid");
	if ($user->usertype == UserType::NoLogin) { $display = "none"; } else { $display = "block"; }
        ?>
<div class="grid_10">
    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset>
            <legend>Edit User<?php if ($user->usertype != UserType::NoLogin) echo " [ $user->store_name ]"; else echo " [ $user->store_name ]"; ?></legend>
            <form action="formpost/updateUser.php" method="post">
		<input type="hidden" name="userid" value="<?php echo $this->userid; ?>" />
                <p>
                    <label>Full Name: </label>
                    <input type="text" name="fullname" value="<?php echo $this->getFieldValue('fullname',$user->store_name); ?>">
                </p>
                <span id="otherinfo" style="display:<?php echo $display; ?>">
                    <p>
                        <label>Email: </label>
                        <input type="text" name="email" value="<?php echo $this->getFieldValue('email',$user->email); ?>">
                    </p>
                    <p>
                        <label>Password: (leave blank if you donot want to change)</label>
                        <input type="password" name="password" value="">
                    </p>
                    <p>
                        <label>Confirm Password: </label>
                        <input type="password" name="password2" value="">
                    </p>
                </span>
                        <?php if ($formResult) { ?>
                <p>
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
                <input type="submit" value="Update">
                <a href="admin/users"><Button>Cancel</Button></a>                
            </form>
        </fieldset>
    </div>

</div>
    <?php
    }
}
?>
