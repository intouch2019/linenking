<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_edittaskmanager extends cls_renderer {

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
//        print_r($this->currStore);exit();
//        print_r($params['ids']);
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
        <!--<script type="text/javascript" src="<?php // CdnUrl('js/ajax-dynamic-list.js');  ?>">-->

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
                <h2>Assign Task</h2><br>

                <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow:auto; ">
                    <div class="grid_12">

                        <form action="formpost/edittaskmanager.php" method="POST"  enctype="multipart/form-data">
                            <?php
                            if (isset($this->id) && $this->id !== "") {


                                $query = "select id,s_department,s_name,r_department,task_info,subject,received from it_task_manager where id=$this->id";
                                //   $allStores = $db->fetchObjectArray("select id,store_name,roles from it_codes where usertype=$currUser->usertype and code='$currUser->code'");
                                $result = $db->fetchObject($query);
                            }
                            
                            if (isset($this->id) && $this->id !== "") {
                             $imagequery = "select id,image from it_reassign_task_image where task_id=$this->id";
                             $image = $db->fetchObjectArray($imagequery);
                             }
                            
//                            $usr = "select id,code,store_name,roles from it_codes where usertype=$currUser->usertype and code='$currUser->code' ";
                            $usr = "select id,code,store_name,roles from it_codes where roles is not null ";
                            //   $allStores = $db->fetchObjectArray("select id,store_name,roles from it_codes where usertype=$currUser->usertype and code='$currUser->code'");
                            $usr_name = $db->fetchObjectArray($usr);

                            $depp = $db->fetchObject("select distinct roles from it_codes where roles is not null ");
//                            $depp = "select distinct roles from it_codes where roles is not null ";
                            //   $allStores = $db->fetchObjectArray("select id,store_name,roles from it_codes where usertype=$currUser->usertype and code='$currUser->code'");
//                            $dep_name = $db->fetchObjectArray($depp);
                            ?>

                            <?php if (isset($this->id)) { ?>
                                <input type="hidden" name="id" value="<?php echo $this->id ?>">   
        <?php } ?>

                            <!--<div class="grid_2"><label>  <h6>Department   :</h6>   </label>  </div>-->
                            <!--<select id="depselect" name="department[]" class="chzn-select" style="width:462px;" multiple >-->
        <!--                            <select name="department[]" id="depselect" data-placeholder="Choose department" class="chzn-select" multiple style="width:462px;">    
                                <option value=0 disabled="" selected="">Select department </option>   
        <?php // foreach ($dep_name as $d_name) {  ?><option value="<?php // echo $d_name->roles;  ?>" ><?php // echo $d_name->roles;  ?></option><?php // }  ?></select><br /><br />  -->

        <?php if ($this->id == "") {?>                    
        <div class="grid_2"><label>  <h6>Department   :</h6>   </label>  </div>

                            <select name="department[]" id="department" data-placeholder="Choose department" class="chzn-select"  style="width:462px;" onchange="departmentSelect(this);" required="">    
                                <option value=""></option>  
                                <?php
                                $dep_array = explode(',', $this->dep);
                                $allrolltype = RollType::getALL();
                                foreach ($allrolltype as $key => $value) {
                                    $selected = "";
//                                    if ($key == $this->dep) {
                                    if (in_array($key , $dep_array)) {
                                        $selected = "selected";
                                    }
                                    ?>
 <?php if($this->currStore->roles == RollType::Warehouse && $key == RollType::Stores ||$this->currStore->roles == RollType::Stores && $key == RollType::Warehouse ){?>
                      
                                    <!--<option value="<?php // echo $key; ?>" <?php // echo $selected; ?>><?php // echo $value; ?></option>-->
                                    <option value="<?php echo $key; ?>" disabled <?php echo $selected; ?>><?php echo $value; ?></option>
                                <?php }
                                else {
?>
        <option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value; ?></option>
<?php
    }
 } ?>
                            </select><br /><br />        


                            <!--new code-->
                            <div class="grid_2"><label> <h6>User Type(Role): </h6> </label></div>
                            <select name="usertype[]" style="width:462px;height: 30px;" class="chzn-select" onchange="usertypeSelect(this.value);" required>
                                <option value="">Please Select</option>
                                <?php
                                if (isset($this->dep) && ($this->dep > 0 )) {
                                    $query = " select distinct usertype from it_codes where roles in (" . $this->dep . ")";
                                    $objs = $db->fetchObjectArray($query);
                                    foreach ($objs as $obj) {
                                        $selected = "";
                                        if ($obj->usertype == $this->usrtype) {
                                            $selected = "selected";
                                        }
                                        ?>
                                        <option value="<?php echo $obj->usertype; ?>" <?php echo $selected; ?>><?php echo UserType::getName($obj->usertype); ?></option>
            <?php }
        } ?>

                            </select><br /><br />    

                            <!--New code-->
                            <div class="grid_2"><label>  <h6>User Name   :</h6>   </label>  </div>
                            <select name="username" id="username" data-placeholder="Choose user" class="chzn-select" style="width:462px;" required>    
                                <!--<option value="">Please Select</option>-->
                                <?php
//                  if(isset($this->usrtype)){
                                $query = " select id,store_name,usertype from it_codes where roles in (" . $this->dep . ")and usertype= " . $this->usrtype . "";
                                $objs = $db->fetchObjectArray($query);
                                foreach ($objs as $obj) {
                                    ?>
                                    <option value="<?php echo $obj->store_name; ?>"><?php echo $obj->store_name; ?></option>
        <?php } ?>

                            </select><br /><br /> 
<?php }?>
                            <!--old code-->
                            <!--<div class="grid_2"><label>  <h6>User Name   :</h6>   </label>  </div>-->

        <!--<select name="username" id="username" data-placeholder="Choose user" class="chzn-select" style="width:462px;">-->    
                            <!--<option value=0 disabled="" selected="">Select department </option>-->   

                            <div class="grid_2"><label><h6>Subject   :</h6> </label>  </div>
                            <input type="text" name="subject" style="width: 462px;height: 30px;"value="<?php
        if (isset($result) && $result !== "") {
            echo $result->subject;
        } else {
            echo "";
        }
        ?>"required>
                            <br><br>

                            <div class="grid_2"><label><h6>Description   :</h6> </label>  </div>
                            <textarea id="w3review" name="description" rows="6" cols="60" required> <?php
                        if (isset($result) && $result !== "") {
                             echo preg_replace('/\s+/', ' ', trim($result->task_info));
                        } else {
                            echo "";
                        }
                        ?></textarea>
<?php if ($this->id == "") {?>
                            <br><br>    
                            <div class="grid_2"><label><h6>Upload Image   :</h6> </label>  </div>
                            <div class="clsText"><input type="file" id="file" name="file" >
                                <!--<div class="grid_2"><label><h6></h6> </label>  </div>-->    
                                <div style="text-align:center; margin-left: -520px;">(Only JPG,PNG and GIF files are allowed.)</div>
                            </div>
                            
<?php }?>                             
                            <br><br>
                            <?php if (isset($this->id) && $this->id !== "") {
                              foreach ($image as $img) {
    if ($img->image) { ?>
      <div class="image-grid-item">
        <a href="<?php echo "images/task/$img->image"; ?>" rel="prettyPhoto">
          <img src="<?php echo "images/task/$img->image"; ?>" width="270" style="border: 1px solid #333; border-radius: 5px;">
        </a>
      </div>
    <?php }
                              }} ?>
                   <br><br>   
        <!--                            <input type="submit" style="font-weight:bold; margin-right:9px;cursor: pointer;padding: 8px 16px; text-align: center;" value="<?php
//                            if (isset($this->id) && ($this->id !== "")) {
//                            echo "Update";
//                        } else {
//                            echo "Submit";
//                        }
                            ?>" onclick="return confirm('Are you sure you want to submit the given task?')">-->

<?php if ($this->id == "") {?>
                            <button id="submitBtn" style="font-weight:bold; margin-right:9px;cursor: pointer;padding: 8px 16px; text-align: center;" value="<?php
                            if (isset($this->id) && ($this->id !== "")) {
                                echo "Update";
                            } else {
                                echo "Submit";
                            }
                            ?>">Submit</button>

                 <?php }?>  
                            
                            <?php if ($formResult) { ?>
                                <p>
                                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                                </p>
                                    <?php } ?>

                            <div id="confirmModal" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; padding: 20px;">
                                <p>Are you sure you want to submit the given task?</p>
                                <button id="yesBtn">Yes</button>
                                <button id="noBtn">No</button>
                            </div>
                            <div id="successMessage" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: white; padding: 20px;">
                                <p>The task has been submitted successfully!</p>
                            </div>                             


                            <br><br>

                        </form>

                        <style>
                            #confirmModal,
                            #successMessage{
                                display: none;
                                position: absolute;
                                top: 50%;
                                left: 50%;
                                transform: translate(-50%, -50%);
                                background-color: white;
                                padding: 20px;
                                border: 1px solid #ccc;
                            }
                        </style>

                        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
                        <script>

                                $(function () {
                                    $(".chzn-select").chosen();
                                    $(".chzn-select-deselect").chosen({allow_single_deselect: true});
                                });

                                const submitBtn = document.getElementById("submitBtn");
                                const confirmModal = document.getElementById("confirmModal");
                                const successMessage = document.getElementById("successMessage");
                                const yesBtn = document.getElementById("yesBtn");
                                const noBtn = document.getElementById("noBtn");

                                submitBtn.addEventListener("click", function (event) {
                                    event.preventDefault();
                                    confirmModal.style.display = "block";
                                });

                                yesBtn.addEventListener("click", function () {
                                    confirmModal.style.display = "none";
                                    document.querySelector("form").submit();
                                    successMessage.style.display = "block";
                                });

                                noBtn.addEventListener("click", function (event) {
                                    event.preventDefault();
                                    confirmModal.style.display = "none";
                                });

                                function departmentSelect(dep)
                                {
                                    //  alert("dep");
                                    //  alert(document.getElementById("department").value);
                                    //  window.location.href="edittaskmanager/dep="+ dep.value;
                                    window.location.href = "edittaskmanager/dep=" + dep;

                                }

                                function departmentSelect(selectElement) {
                                  var selectedOptions = [];
                                  if (selectElement.multiple) {
                                    // Handle multi-selection
                                    for (var i = 0; i < selectElement.options.length; i++) {
                                      var option = selectElement.options[i];
                                      if (option.selected) {
                                        selectedOptions.push(option.value);
                                      }
                                    }
                                    var dep = selectedOptions.join(',');
                                  } else {
                                    // Handle single-selection
                                    var dep = selectElement.value;
                                  }
                                  window.location.href = "edittaskmanager/dep=" + dep;
                                }


                                function usertypeSelect(usrtype)
                                {
                                    //  alert("usr");
                                    var dep = document.getElementById("department").value;
                                    //  window.location.href="edittaskmanager/dep="+ dep.value;
                                    window.location.href = "edittaskmanager/dep=" + dep + "/usrtype=" + usrtype;


                                }


                                //  function validform() {
                                //    var dep = document.forms["myForm"]["department[]"].value;
                                //    var usertype = document.forms["myForm"]["usertype"].value;
                                //    var username = document.forms["myForm"]["username"].value;
                                //
                                //    if (dep == "") {
                                //        alert("Please select a department");
                                //        return false;
                                //    }
                                //    if (usertype == "") {
                                //        alert("Please select a user type (role)");
                                //        return false;
                                //    }
                                //    if (username == "") {
                                //        alert("Please select a user name");
                                //        return false;
                                //    }
                                //    return true;
                                //}    

                        </script>


                    </div>

                </div>
        <?php
    }

}
?>