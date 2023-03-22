<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_reassigntaskmanager extends cls_renderer {

    var $params;
    var $id;
    var $dep;
    var $storeid;
    var $currUser;
    var $currStore;
    var $usrtype;
    
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
        if (isset($params['dep'])) {
            $this->dep = $params['dep'];
        }
        if (isset($params['usrtype'])) {
            $this->usrtype = $params['usrtype'];
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
        <script type="text/javascript"></script>
        
        <script type="text/javascript" src="js/ajax.js"></script>
        <script type="text/javascript" src="js2/custom.js"></script>
        <link rel="stylesheet" href="css/bigbox.css" type="text/css" />
        <?php
    }

    //extra-headers close

    public function pageContent() {
        $menuitem = "edittaskmanager";
        include "sidemenu." . $this->currStore->usertype . ".php";
        $formResult = $this->getFormResult();
        $write_htm = true;
        ?>


        <div class="grid_10">
            <?php
            $db = new DBConn();
            $store_id = getCurrUserId();
            ?>
            <div class="box">
                <h2>Reassign Task</h2><br>

                <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow:auto; ">
                    <div class="grid_12">

                        <form action="formpost/edittaskmanager.php" method="POST"  enctype="multipart/form-data">
                        <!--<form action="formpost/edittaskmanager.php" method="POST" >-->
                            <?php
                            if (isset($this->id) && $this->id !== "") {


                                $query = "select id,s_department,s_name,r_department,task_info,subject,received,image from it_task_manager where id=$this->id";
                                //   $allStores = $db->fetchObjectArray("select id,store_name,roles from it_codes where usertype=$currUser->usertype and code='$currUser->code'");
                                $result = $db->fetchObject($query);
                            }
                            $usr = "select id,code,store_name,roles from it_codes where usertype=$currUser->usertype and code='$currUser->code' ";
                            //   $allStores = $db->fetchObjectArray("select id,store_name,roles from it_codes where usertype=$currUser->usertype and code='$currUser->code'");
                            $usr_name = $db->fetchObject($usr);

                            $depp = "select distinct roles from it_codes where roles is not null ";
                            //   $allStores = $db->fetchObjectArray("select id,store_name,roles from it_codes where usertype=$currUser->usertype and code='$currUser->code'");
                            $dep_name = $db->fetchObjectArray($depp);
                            ?>

                            <?php if (isset($this->id)) { ?>
                                <input type="hidden" id="id" name="id" value="<?php echo $this->id ?>">   
                            <?php } ?>

                            <div class="grid_2"><label>  <h6>Department   :</h6>   </label>  </div>
                            
                            <select name="department[]" id="department" data-placeholder="Choose department" class="chzn-select"  style="width:462px;" onchange="departmentSelect(this.value);">    
                                 <option value=""></option>  
                                <?php
                                        $allrolltype = RollType::getALL();
                                        foreach ($allrolltype as $key => $value) {  $selected=""; 
                                        if($key == $this->dep){ $selected = "selected";}
                                        ?>
                                 <?php if($this->currStore->roles == RollType::Warehouse && $key == RollType::Stores ||$this->currStore->roles == RollType::Stores && $key == RollType::Warehouse ){?>
                                           <option value="<?php echo $key; ?>" disabled <?php echo $selected; ?>><?php echo $value; ?></option>
                                            <!--<option value="<?php // echo $key; ?>" <?php // echo $selected; ?>><?php // echo $value; ?></option>-->
                                    <?php }
                                    else{
?>
        <option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value; ?></option>
<?php
    }
                                        } ?>
                                            
                            </select><br /><br />     

                              <?php if ($result->image) { ?>

                                                <a href="<?php echo "images/task/$result->image"; ?>" rel="prettyPhoto"><img class="grid_3" align="right" src="<?php echo "images/task/$result->image"; ?>" width="270" style="float: right;border: 1px solid #333; border-radius: 5px;"  /></a>
                                <?php } ?>  
                            
                            
                              <!--new code-->
                         <div class="grid_2"><label> <h6>User Type(Role): </h6> </label></div>
                    <select name="usertype" style="width:462px;height: 30px;" class="chzn-select" onchange="usertypeSelect(this.value);">
                        <option value="">Please Select</option>
                <?php
                  if(isset($this->dep) && ($this->dep > 0 )){
                      $query = " select distinct usertype from it_codes where roles = ".$this->dep."";
                      $objs = $db->fetchObjectArray($query);
                      foreach($objs as $obj){
                           $selected="";
                           if($obj->usertype == $this->usrtype){ $selected = "selected";}
               ?>
                        <option value="<?php echo $obj->usertype;?>" <?php echo $selected; ?>><?php echo UserType::getName($obj->usertype);?></option>
                  <?php } }  ?>
             
                    </select><br /><br />    

                    <!--New code-->
                           <div class="grid_2"><label>  <h6>User Name   :</h6>   </label>  </div>
                       <select name="username" id="username" data-placeholder="Choose user" class="chzn-select" style="width:462px;">    
                       <!--<option value="">Please Select</option>-->
                    <?php
//                  if(isset($this->usrtype)){
                      $query = " select id,store_name,usertype from it_codes where roles in (" . $this->dep . ") and usertype= ".$this->usrtype."";
                      $objs = $db->fetchObjectArray($query);
                      foreach($objs as $obj){
                                                      
               ?>
                        <option value="<?php echo $obj->store_name;?>"><?php echo $obj->store_name;?></option>
                  <?php  }  ?>
             
                    </select><br /><br />                   
                                                

                            <div class="grid_2"><label><h6>Subject   :</h6> </label>  </div>
                            <input type="text" name="subject" style="width: 462px;height: 30px;"value="<?php
                                if (isset($result) && $result !== "") {
                                    echo $result->subject;
                                } else {
                                    echo "";
                                }
                                ?>">
                            <br><br>

                            <div class="grid_2"><label><h6>Description   :</h6> </label>  </div>
                            <textarea id="w3review" name="description" rows="6" cols="60"> <?php
                    if (isset($result) && $result !== "") {
                        echo $result->task_info;
                    } else {
                        echo "";
                    }
                                ?></textarea>

                          <br><br>    
            <div class="grid_2"><label><h6>Upload Image   :</h6> </label>  </div>
        <div class="clsText"><input type="file" name="file">
        <div style="text-align:center; margin-left: -520px;">(Only JPG,PNG and GIF files are allowed.)</div>
        </div>
                       <br><br>   
                            <input type="submit" style="font-weight:bold; margin-right:9px;cursor: pointer;padding: 8px 16px; text-align: center;" value="<?php
                        
         
                            if (isset($this->id) && ($this->id !== "")) {
                            echo "Update";
                        } else {
                            echo "Submit";
                        }
       
                                ?>">
                            <br><br>

                            <?php if ($formResult) { ?>
                                <p>
                                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                                </p>
                                    <?php } ?>
                            
                        </form>
                        
  <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script>
    
    $(function(){
        $(".chzn-select").chosen(); 
        $(".chzn-select-deselect").chosen({allow_single_deselect:true});
    });     
    
  function departmentSelect(dep)
  {
//  alert("dep");
  var ids =(document.getElementById("id").value);
//  window.location.href="edittaskmanager/dep="+ dep.value;
  window.location.href="reassigntaskmanager/ids="+ ids+"/dep="+ dep;
  
    }   
    
   function usertypeSelect(usrtype)
  {
//  alert("usr");
  var ids =(document.getElementById("id").value);
  var dep = document.getElementById("department").value;
//  window.location.href="edittaskmanager/dep="+ dep.value;
  window.location.href="reassigntaskmanager/ids="+ ids+"/dep="+ dep+"/usrtype="+ usrtype;
  
    }    
</script>
                    </div>

                </div>
                <?php
            }

        }
        ?>