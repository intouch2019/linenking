<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_transporters extends cls_renderer {
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
    function deleteUser(transporterid)
    {
        var r=confirm("Delete this User?");
        if (r==true) {
		window.location = "transporters/delete/transporterid="+transporterid;
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
	$menuitem = "transporters";
        include "sidemenu.".$this->currUser->usertype.".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
        ?>
<div class="grid_10">
            <?php $_SESSION['form_post'] = array(); ?>
            <?php
            $objs = $db->fetchObjectArray("select id, name, date(createtime) as createdate from it_transporters where inactive=0");
            ?>

    <div style="float:right;margin-right:10px;">
    <a href="transporters/add"><button>Add New Transporter</button></a><br />
    </div>
    <div class="grid_12">
        <table align="center">
            <tr>
                <th>Transporter</th>
                <th>Create Date</th>
                <th></th>
            </tr>
                    <?php foreach ($objs as $obj) { ?>
            <tr>
                <td><?php echo $obj->name; ?></td>
                <td><?php echo $obj->createdate; ?></td>
                <td><a href="transporters/edit/id=<?php echo $obj->id; ?>"><button>Edit</button></a><button onclick="deleteUser(<?php echo $obj->id; ?>);">Delete</button></td>
            </tr>
                    <?php } ?>
        </table>
    </div>

</div>
    <?php
    }
}
?>
