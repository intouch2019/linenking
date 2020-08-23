<?php
class cls_footer {

	public function __construct() {
	}

	public function pageFooter($renderObj) {
?>
<div class="grid_12">&nbsp;</div>
<div class="grid_12"><hr></div>
<div class="grid_6" style="text-align:left;color:#303030;">
<p>Copyright &copy 2011-2012, <a target="_blank" href="http://www.intouchrewards.com/">Intouch Consumer Care Solutions Pvt. Ltd.</a> All rights reserved.</p>
</div>
 <?php 
         $this->currUser = getCurrUser();
         if(isset($this->currUser)){         

            $authkey=DEF_ISSUE_AUTHKEY;
            $link= DEF_ISSUE_URL;
            $params = array(
               "id" => DEF_ISSUE_PROJECTID,
               "username"=>$this->currUser->code,
               "name"=>$this->currUser->store_name,
               "email"=>$this->currUser->email, 
               "back_url"=>DEF_SITEURL,
               "t"=>time()
            );           
            ksort($params);
            $hash = sha1(serialize($params).",".$authkey);     
    ?>    
    <div class="grid_6" style="text-align:right;color:#303030;">        
        <form name="issueTracker" id="issueTracker" target="_blank" method="post" action="<?php echo DEF_ISSUE_URL?>">
            <input type="hidden" name="id" value="<?php echo DEF_ISSUE_PROJECTID; ?>">
            <input type="hidden" name="username" value="<?php echo $this->currUser->code; ?>">
            <input type="hidden" name="name" value="<?php echo $this->currUser->store_name; ?>">
            <input type="hidden" name="email" value="<?php echo $this->currUser->email; ?>">
            <input type="hidden" name="back_url" value="<?php echo DEF_SITEURL; ?>">
            <input type="hidden" name="t" value="<?php echo time(); ?>">
            <input type="hidden" name="hash" value="<?php echo $hash; ?>">
            <input type="submit" value="Issue Tracker">            
        </form>
    </div>
         <?php } ?>
</div> <!-- end class=container_12 -->
<script type="text/javascript" src="fluid960gs/js/jquery-ui.js"></script>
<script type="text/javascript" src="fluid960gs/js/jquery-fluid16.js"></script>
</body>
</html>
<?php
	}
}
?>
