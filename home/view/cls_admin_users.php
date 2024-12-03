<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_admin_users extends cls_renderer {
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
    function deleteUser(userid)
    {
        var r=confirm("Delete this User?");
        if (r==true) {
		window.location = "admin/users/delete/userid="+userid;
		exit;
        }
        //window.location.reload();
    }

</script>
<?php
    }

    //extra-headers close
    public function pageContent() {
//        if ($this->currUser->usertype == UserType::Admin || $this->currUser->usertype == UserType::CKAdmin) {} else { print "Unauthorized Access"; return; }
	$menuitem = "users";
        include "sidemenu.".$this->currUser->usertype.".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
        ?>
<div class="grid_10">
            <?php $_SESSION['form_post'] = array(); ?>
            <?php
            $objs = $db->fetchObjectArray("select id, usertype, code, store_name,email,phone,roles,date(createtime) as createdate from it_codes where inactive=0 and usertype not in (".UserType::Admin.") and is_closed=0 order by usertype");
            ?>

    <div style="float:right;margin-right:10px;">
    <a href="admin/users/add"><button>Add New User</button></a><br />
    </div>
    <div class="grid_12">
        <table align="center">
            <tr>
                <!--<th>User Type</th>-->
                <th>Username</th>
                <th>Full Name</th>
                <th>Email ID</th>
                <th>Mobile No</th>
                <th>Department</th>
                <th>User Type/Roles</th>
                <th>Create Date</th>
                <th></th>
            </tr>
                    <?php foreach ($objs as $obj) { ?>
            <tr>
                <!--<td><?php // echo UserType::getName($obj->usertype); ?></td>-->
                <td><?php echo $obj->code; ?></td>
                <td><?php echo $obj->store_name; ?></td>
                <td><?php echo $obj->email; ?></td>
                <td><?php echo $obj->phone; ?></td>
                <td><?php echo RollType::getName($obj->roles); ?></td>
                <td><?php echo UserType::getName($obj->usertype); ?></td>
                <td><?php echo $obj->createdate; ?></td>
                <td><a href="admin/users/edit/id=<?php echo $obj->id; ?>"><button>Edit</button></a><?php if( $this->currUser->usertype == UserType::Admin  || $this->currUser->roles == RollType::IT){ ?><button onclick="deleteUser(<?php echo $obj->id; ?>);">Delete</button><?php } ?></td>
            </tr>
                    <?php } ?>
        </table>
    </div>

</div>
    <?php
    }
}
?>