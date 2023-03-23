<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_admin_users_add extends cls_renderer {
    var $currUser;
    var $userid;
    function __construct($params=null) {
        $this->currUser = getCurrUser();
        $this->params = $params;
        if (!$this->currUser) { return; }
        $this->userid = $this->currUser->id;
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
<script type="text/javascript">
    function usertypeSelect(dropdown)
    {
        var idx = dropdown.selectedIndex;
        var value = dropdown.options[idx].value;
        if (value == null) {
            $("#otherinfo").hide();
        } else {
            $("#otherinfo").show();
        }
    }

    function search()
    {
        var des_code=document.getElementById("srch").value;
        // var cat=document.getElementById("cat").value;
        // window.location.href="store/designs/dno="+des_code;
        //  return;
        var ajaxUrl ="ajax/getDesign.php?design_code=" + des_code;

        if (des_code)
        {$.getJSON(ajaxUrl, function(data){
                if (data.error == "0")
                { window.location.href = data.redirect; }
                else
                { alert(data.message); }
            });
        }
        else
        { alert ("Please enter a design code"); }
    }

</script>


    <?php 
    }

    //extra-headers close
    public function pageContent() {
        
        //if ($this->currUser->usertype == UserType::Admin || $this->currUser->usertype == UserType::CKAdmin) {} else { print "Unauthorized Access"; return; }
	$menuitem = "users";
        include "sidemenu.".$this->currUser->usertype.".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
        ?>
<div class="grid_10">
    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset>
            <legend>Add User</legend>
            <form action="formpost/addUser.php" method="post">
                <p>
                    <label>User Type: </label>
                    <select name="usertype" onchange="usertypeSelect(this);">
                        <option value="">Please Select</option>
                                <?php
                                $allUserTypes = UserType::getAll();
                                $display = "block";
                                foreach ($allUserTypes as $usertype => $typename) {
                                    if ($usertype == UserType::Admin) { continue; }
                                    if ($usertype == $this->getFieldValue('usertype')) {
                                        $selected = "selected";
                                        if ($usertype == UserType::NoLogin) { $display="none"; }
                                    }
                                    else { $selected = ""; }
                                    ?>
                        <option value="<?php echo $usertype; ?>" <?php echo $selected; ?>><?php echo $typename; ?></option>
                                <?php } ?>
                    </select>
                </p>
                <p>
                    <label>Full Name: </label>
                    <input type="text" name="fullname" value="<?php echo $this->getFieldValue('fullname'); ?>">
                </p>
                <span id="otherinfo" style="display:<?php echo $display; ?>">
                    <p>
                        <label>Username: </label>
                        <input type="text" name="username" value="<?php echo $this->getFieldValue('username'); ?>">
                    </p>
                    <p>
                        <label>Email: </label>
                        <input type="text" name="email" value="<?php echo $this->getFieldValue('email'); ?>">
                    </p>
                     <p>
                        <label>Mobile No: </label>
                        <input type="text" name="mobile" value="<?php echo $this->getFieldValue('mobile'); ?>">
                    </p>
                    <p>
                    <label>Department: </label>
                    <select name="rolltype" >  
                        <option value="">Select Department </option>
                                <?php
                                $allRollTypes = RollType::getAll();
                                $display = "block";
                                foreach ($allRollTypes as $usertype => $typename) {
                                    if ($usertype == UserType::Admin) { continue; }
                                    if ($usertype == $this->getFieldValue('usertype')) {
                                        $selected = "selected";
                                        if ($usertype == UserType::NoLogin) { $display="none"; }
                                    }
                                    else { $selected = ""; }
                                    ?>
                        <option value="<?php echo $usertype; ?>" <?php echo $selected; ?>><?php echo $typename; ?></option>
                                <?php } ?>
                    </select>
                    </p>
                    <p>
                        <label>Password: </label>
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
                <input type="submit" value="Create">
            </form>
        </fieldset>
    </div>

</div>
    <?php
    }
}
?>