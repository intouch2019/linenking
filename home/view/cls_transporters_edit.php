<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_transporters_edit extends cls_renderer {
    var $currUser;
    var $transporterid;
    function __construct($params=null) {
        // set page permissions
        parent::__construct(array(UserType::Admin, UserType::CKAdmin));
        $this->currUser = getCurrUser();
        $this->params = $params;
	if (isset($this->params['id'])) {
		$this->transporterid = $this->params['id'];
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
	$menuitem = "transporters";
        include "sidemenu.".$this->currUser->usertype.".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
	$transporter = $db->fetchObject("select * from it_transporters where id=$this->transporterid");
        ?>
<div class="grid_10">
    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset>
            <legend>Edit Transporter</legend>
            <form action="formpost/updateTransporter.php" method="post">
		<input type="hidden" name="transporterid" value="<?php echo $this->transporterid; ?>" />
                <p>
                    <label>Full Name: </label>
                    <input type="text" name="fullname" value="<?php echo $this->getFieldValue('fullname',$transporter->name); ?>">
                </p>
                <p>
                    <label>Service Tax No: </label>
                    <input type="text" name="serviceTax" value="<?php echo $this->getFieldValue($transporter->servicetax); ?>">
                </p>
                        <?php if ($formResult) { ?>
                <p>
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
                <input type="submit" value="Update">
                <a href="transporters"><Button>Cancel</Button></a>
            </form>
        </fieldset>
    </div>

</div>
    <?php
    }
}
?>
