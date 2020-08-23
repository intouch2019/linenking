<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_suppliers extends cls_renderer {
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
    function deleteUser(supplierid)
    {
        var r=confirm("Delete this Supplier?");
        if (r==true) {
		window.location = "suppliers/delete/supplierid="+supplierid;
		exit;
        }
        //window.location.reload();
    }

</script>
<?php
    }

    //extra-headers close
    public function pageContent() { 
        //if ($this->currUser->usertype == UserType::Admin || $this->currUser->usertype == UserType::CKAdmin) {} else { print "Unauthorized Access"; return; }
	$menuitem = "suppliers";
        include "sidemenu.".$this->currUser->usertype.".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
        ?>
<div class="grid_10">
            <?php $_SESSION['form_post'] = array(); ?>
            <?php
            $objs = $db->fetchObjectArray("select id, name, address, emailaddress, phoneno, date(createtime) as createdate from it_suppliers where inactive=0");
            ?>

    <div style="float:right;margin-right:10px;">
    <a href="suppliers/add"><button>Add New Supplier</button></a><br />
    </div>
    <div class="grid_12">
        <table align="center">
            <tr>
                <th>Supplier</th>
                <th>Address</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Create Date</th>
                <th></th>
            </tr>
                    <?php foreach ($objs as $obj) { ?>
            <tr>
                <td><?php echo $obj->name; ?></td>
                <td><?php echo $obj->address; ?></td>
                <td><?php echo $obj->emailaddress; ?></td>                
                <td><?php echo $obj->phoneno; ?></td>
                <td><?php echo $obj->createdate; ?></td>
                <td><a href="suppliers/edit/id=<?php echo $obj->id; ?>"><button>Edit</button></a><button onclick="deleteUser(<?php echo $obj->id; ?>);">Delete</button></td>
            </tr>
                    <?php } ?>
        </table>
    </div>

</div>
    <?php
    }
}
?>
