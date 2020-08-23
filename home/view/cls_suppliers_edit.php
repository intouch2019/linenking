<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_suppliers_edit extends cls_renderer {
    var $currUser;
    var $supplierid;
    function __construct($params=null) {
        // set page permissions
        parent::__construct(array(UserType::Admin, UserType::CKAdmin));
       
        $this->currUser = getCurrUser();
        $this->params = $params;
	if (isset($this->params['id'])) {
		$this->supplierid = $this->params['id'];
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
	$menuitem = "suppliers";
        include "sidemenu.".$this->currUser->usertype.".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
	$supplier = $db->fetchObject("select * from it_suppliers where id=$this->supplierid");
        ?>
<div class="grid_10">
    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset>
            <legend>Edit Supplier</legend>
            <form action="formpost/updateSupplier.php" method="post">
		<input type="hidden" name="supplierid" value="<?php echo $this->supplierid; ?>" />
                <p>
                    <label>Firm Name: </label>
                    <input type="text" name="fullname" value="<?php echo $this->getFieldValue('fullname',$supplier->name); ?>">
                </p>
                <p>
                    <label>Contact Person: </label>
                    <input type="text" name="contactPerson" value="<?php echo $this->getFieldValue('fullname',$supplier->contact_person); ?>">
                </p>
                <p>
                    <label>Address: </label>
                    <input type="text" name="address" value="<?php echo $this->getFieldValue('address',$supplier->address); ?>">
                </p>
                <p>
                    <label>Email: </label>
                    <input type="text" name="email" value="<?php echo $this->getFieldValue('email',$supplier->emailaddress); ?>">
                </p>
                <p>
                    <label>Phone: </label>
                    <input type="text" name="phone" value="<?php echo $this->getFieldValue('phone',$supplier->phoneno); ?>">
                </p>
                <p>
                    <label>VatNo: </label>
                    <input type="text" name="vatno" value="<?php echo $this->getFieldValue('vatno',$supplier->vatno); ?>">
                </p>
                        <?php if ($formResult) { ?>
                <p>
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
                <input type="submit" value="Update">
                <a href="suppliers"><Button>Cancel</Button></a>
            </form>
        </fieldset>
    </div>

</div>
    <?php
    }
}
?>
