<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_transporters_add extends cls_renderer {
    var $currUser;
    var $userid;
    function __construct($params=null) {
        // set page permissions
        parent::__construct(array(UserType::Admin, UserType::CKAdmin));
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
        if (value == <?php echo UserType::NoLogin; ?>) {
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
	$menuitem = "transporters";
        include "sidemenu.".$this->currUser->usertype.".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
        ?>
<div class="grid_10">
    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset>
            <legend>Add Transporter</legend>
            <form action="formpost/addTransporter.php" method="post">
                <p>
                    <label>Full Name: </label>
                    <input type="text" name="fullname" value="<?php echo $this->getFieldValue('fullname'); ?>">
                </p>
                <p>
                    <label>Service Tax No: </label>
                    <input type="text" name="serviceTax" value="<?php echo $this->getFieldValue('serviceTax'); ?>">
                </p>
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
