<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_historydetails extends cls_renderer {

    var $params;
    var $id;
    var $dep;
    var $storeid;

    function __construct($params = null) {
//	parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Manager));//
        $this->params = $params;
        $this->currStore = getCurrUser();
        print_r($params['ids']);
//        if (isset($this->params['ids'])) {
//            $this->id = $this->params['ids'];
//            
//        } 
        if (isset($params['ids'])) {
            $this->id = $params['ids'];
        }
   if (isset($params['rece_id'])) {
            $this->rece_id = $params['rece_id'];
        }
    }

    function extraHeaders() {
        ?>
        <script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
        <script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
        <link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
        <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script type="text/javascript">
        </script>
        <link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
        <script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
        <script type="text/javascript">
       
    $(function(){
	$("a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'fast',slideshow:3000, hideflash: true});
	$("#design_no").keyup(function(event){
		if(event.keyCode == 13){
			$("#searchBtn").click();
		}
	});
        
        
        
        </script>
        <?php
    }

    //extra-headers close

    public function pageContent() {
        $menuitem = "edittaskmanager";
        include "sidemenu." . $this->currStore->usertype . ".php";
        $formResult = $this->getFormResult();
        $write_htm = true;
        ?>


        <div class="grid_10" >
            <!--<div class="grid_10" style="position: absolute; top: 0; right: 0;">-->
            <?php
            $db = new DBConn();
            $store_id = getCurrUserId();
            ?>
            <div class="box">
                <h2>Task Details</h2><br>

                <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow:auto; ">
                    <div class="grid_12">

                        <form action="formpost/edittaskmanager.php" method="POST">
                            <?php
                            if (isset($this->id) && $this->id !== "") {


                                $query = "select id,s_department,s_name,r_department,task_info,subject,received,image from it_task_manager where id=$this->id";
                                //   $allStores = $db->fetchObjectArray("select id,store_name,roles from it_codes where usertype=$currUser->usertype and code='$currUser->code'");
                                $result = $db->fetchObject($query);
                            }
                            if (isset($this->id) && $this->id !== "") {
                             $imagequery = "select id,image from it_reassign_task_image where task_id=$this->id";
                             $image = $db->fetchObjectArray($imagequery);
                             }
//                            print_r($imagequery);exit();
                            
                            $usr = "select id,code,store_name,roles from it_codes where usertype=$currUser->usertype and code='$currUser->code' ";
                            //   $allStores = $db->fetchObjectArray("select id,store_name,roles from it_codes where usertype=$currUser->usertype and code='$currUser->code'");
                            $usr_name = $db->fetchObject($usr);

                            $depp = "select distinct roles from it_codes where roles is not null ";
                            //   $allStores = $db->fetchObjectArray("select id,store_name,roles from it_codes where usertype=$currUser->usertype and code='$currUser->code'");
                            $dep_name = $db->fetchObjectArray($depp);
                            ?>

                            <?php if (isset($this->id)) { ?>
                                <input type="hidden" name="id" value="<?php echo $this->id ?>">   
                            <?php } ?>

                            <div class="grid_2"><label><h6>Subject   :</h6> </label>  </div>
                            <input type="text" name="subject" style="width: 462px;height: 30px;"value="<?php
                                if (isset($result) && $result !== "") {
                                    echo $result->subject;
                                } else {
                                    echo "";
                                }
                                ?>">
                            
                            <?php // if ($result->image) { ?>
                            
                <!--<a href="<?php // echo $this->TaskImageUrl($result->image); ?>" rel="prettyPhoto"><img class="grid_3" align="right" src="<?php echo $this->TaskImageUrl($result->image); ?>" width="170" style="float: right;border: 1px solid #333; border-radius: 5px;"  /></a>-->
<?php // } ?>
                            
                            <br><br>

                            <div class="grid_2"><label><h6>Description   :</h6> </label>  </div>
                            <textarea id="w3review" name="description" rows="6" cols="60"> <?php
                    if (isset($result) && $result !== "") {
                        echo $result->task_info;
                    } else {
                        echo "";
                    }
                                ?></textarea>

           
                            <!--<br><br>-->

                        
                <!--<td><a  href="edittaskmanager/ids=<?php // echo $result->id ?>"><button> Reassign Task </button> <a/></td>--> 
                <!--<a  href="edittaskmanager/ids=<?php // echo $result->id ?>"> REASSIGN TASK</a>-->
                <!--<td><button onclick="window.location.href='edittaskmanager/ids=<?php // echo $result->id ?>'">REASSIGN TASK</button></td>-->
                <!--<a href="edittaskmanager"><button>New Batch</button></a>-->
                
<!--                <p style="text-align: center;">
                 <button>
                 <a href="edittaskmanager/ids=<?php // echo $result->id ?>"> Reassign Task</a> 
                 </button>
                </p>-->

                
                
                
                <!--<br><br>-->   
<!--                            <input type="submit" style="font-weight:bold; margin-right:9px;cursor: pointer;padding: 8px 16px; text-align: center;" value="<?php
                        
         
//                            if (isset($this->id) && ($this->id !== "")) {
//                            echo "Update";
//                        } else {
//                            echo "Submit";
//                        }
//                      
                         
                            
        
                                ?>">-->
                                
  <div class="right-div">
  <?php foreach ($image as $img) {
    if ($img->image) { ?>
      <div class="image-grid-item">
        <a href="<?php echo "images/task/$img->image"; ?>" rel="prettyPhoto">
          <img src="<?php echo "images/task/$img->image"; ?>" width="270" style="border: 1px solid #333; border-radius: 5px;">
        </a>
      </div>
    <?php }
  } ?>
</div>
<div class="clear"></div> 

 <style>
      .left-div {
        float: left;
        width: 60%;
        padding: 20px;
        box-sizing: border-box;
      }

      .right-div {
        float: right;
        width: 40%;
        padding: 20px;
        box-sizing: border-box;
      }

      .clear {
        clear: both;
      }
</style>                                
                                
                            <br><br>
                 
                        </form>

                    </div>

                </div>
                <?php
            }

        }
        ?>