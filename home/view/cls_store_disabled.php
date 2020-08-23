<?php
require_once "lib/db/dbobject.php";
require_once "view/cls_renderer.php";

class cls_store_disabled extends cls_renderer {
        var $storeinfo;
	function __construct($params=null) {
            $this->storeinfo = getCurrUser();
	}

	public function pageContent() {
		session_destroy();
                $db = new DBConn();
?>
		<div class="grid_9">
			<div class="error" style="font-size:1.5em;">Your store login has temporarily been disabled.<?php if(isset($this->storeinfo) && trim($this->storeinfo->inactivating_reason)!=""){ ?> <br>Reason is : <?php echo $this->storeinfo->inactivating_reason; ?> <br><?php } ?> Please contact Linen King Office for more details. Thank you.</div>
		</div>
<?php
	}

}
