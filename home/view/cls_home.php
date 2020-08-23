<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "session_check.php";

class cls_home extends cls_renderer {
        var $params;
        var $currUser;
	var $userid;
	var $shipmentid;
        function __construct($params=null) {
		$this->currUser = getCurrUser();
                if (!$this->currUser) { header("Location: home/login"); exit; }
                $this->userid = $this->currUser->id;
	}

	function extraHeaders() {
            ?>
 <link rel="stylesheet" type="text/css" href="css/dark-glass/sidebar.css" />
 <script type="text/javascript" src="js/sidebar/jquery.sidebar.js"></script><script>
$(function() {
$("ul#demo_menu1").sidebar({
    width : 160,
    height : 110,
    injectWidth : 50,
    events:{
                item : {
                    enter : function(){
                        $(this).find("a").animate({color:"red"}, 250);
                    },
                    leave : function(){
                        $(this).find("a").animate({color:"white"}, 250);
                    }
                }
            }
    });
});
</script>
<?php
	} // extraHeaders

	public function pageContent() {
		$menuitem="dashboard";
	        include "sidemenu.".$this->currUser->usertype.".php";
		?>

 <div class="grid_10">
<div class="grid_10" style="margin-top:10px;">Please select an option from the Side Menu</div>
 </div>
<?php
	} //pageContent
}//class
?>
