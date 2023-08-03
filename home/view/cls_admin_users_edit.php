<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_admin_users_edit extends cls_renderer {
    var $currUser;
    var $userid;
    function __construct($params=null) {
        $this->currUser = getCurrUser();
        $this->params = $params;
	if (isset($this->params['id'])) {
		$this->userid = $this->params['id'];
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
<script>
    function selectall(){

                let selectedStore;
                var listRight = document.getElementById('selectRight');
                    for (selectedStore = 0; selectedStore <= listRight.options.length - 1; selectedStore++) {
                       listRight.options[selectedStore].selected = true;
                }
            }
            function moveToRightOrLeft(side) {
                var listLeft = document.getElementById('selectLeft');
                var selectedAllStoreleft = listLeft.options.selectedIndex;
                var listRight = document.getElementById('selectRight');
                var selectedAllStoreright = listRight.options.selectedIndex;
                if (listRight.options.selectedIndex == -1 && listLeft.options[selectedAllStoreleft].text == "All Stores") {
        //console.log(listLeft);

                    var selectElement = document.getElementById('selectLeft');
                    var allOptions = selectElement.querySelectorAll('option');
                    let selectedStoreCount;
                    for (selectedStoreCount = 0; selectedStoreCount <= allOptions.length - 1; selectedStoreCount++) {
                        move(listRight, allOptions[selectedStoreCount].value, allOptions[selectedStoreCount].textContent);
                        listLeft.remove(0);
        //console.log(allOptions[1].value);
        //      if(listLeft.length>0){
        //      //allOptions[selectedStoreCount].selected=true;
        //      }
                    }

                } else if (listLeft.options.selectedIndex == -1 && listRight.options[selectedAllStoreright].text == "All Stores") {

                    var selectElement = document.getElementById('selectRight');
                    var allOptions = selectElement.querySelectorAll('option');
                    let selectedStoreCount;
                    for (selectedStoreCount = 0; selectedStoreCount <= allOptions.length - 1; selectedStoreCount++) {
                        move(listLeft, allOptions[selectedStoreCount].value, allOptions[selectedStoreCount].textContent);
                        listRight.remove(0);

                    }

                } else if (side == 1) {
                    if (listLeft.options.length == 0) {
                        alert('You have already moved all fields to Right');
                        return false;
                    } else {
                        var selectedCountry = listLeft.options.selectedIndex;

                        move(listRight, listLeft.options[selectedCountry].value, listLeft.options[selectedCountry].text);
                        listLeft.remove(selectedCountry);

                        if (listLeft.options.length > 0) {
                            listLeft.options[selectedCountry].selected = true;
                        }
                    }
                } else if (side == 2) {
                    var selectedCountry = listRight.options.selectedIndex;
                    let selectedStore;
                    move(listLeft, listRight.options[selectedCountry].value, listRight.options[selectedCountry].text);
                    listRight.remove(selectedCountry);

                    if (listRight.options.length > 0) {
                        listRight.options[selectedCountry].selected = true;
                    }
                        for (selectedStore = 0; selectedStore <= listRight.options.length - 1; selectedStore++) {
                       listRight.options[selectedStore].selected = true;
                }

                }
            }

            function move(listBoxTo, optionValue, optionDisplayText) {
        //     alert(optionValue);

                var newOption = document.createElement("option");
                newOption.value = optionValue;
                newOption.text = optionDisplayText;

                //alert(newOption.value);
                newOption.selected = true;
                listBoxTo.add(newOption, null);
                return true;
            }
        </script>

    <?php
    }

    //extra-headers close
    public function pageContent() {
        //if ($this->currUser->usertype == UserType::Admin || $this->currUser->usertype == UserType::CKAdmin) {} else { print "Unauthorized Access"; return; }
	$menuitem = "users";
        include "sidemenu.".$this->currUser->usertype.".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
	$user = $db->fetchObject("select * from it_codes where id=$this->userid");
	if ($user->usertype == UserType::NoLogin) { $display = "none"; } else { $display = "block"; }
        ?>
<div class="grid_10">
    <div class="grid_3">&nbsp;</div>
    <div class="grid_5">
        <fieldset>
            <legend>Edit User<?php if ($user->usertype != UserType::NoLogin) echo " [ $user->store_name ]"; else echo " [ $user->store_name ]"; ?></legend>
            <form action="formpost/updateUser.php" method="post">
		<input type="hidden" name="userid" value="<?php echo $this->userid; ?>" />
                <p>
                    <label>Full Name: </label>
                    <input type="text" name="fullname" value="<?php echo $this->getFieldValue('fullname',$user->store_name); ?>">
                </p>
                <span id="otherinfo" style="display:<?php echo $display; ?>">
                    <p>
                        <label>Email: </label>
                        <input type="text" name="email" value="<?php echo $this->getFieldValue('email',$user->email); ?>">
                    </p>
                     <!--		user assign store start-->
                            <div class="grid_12" id="itemselection">

                                <div class="grid_7">
                                    <table border="0" colspan="4">
                                        <tr>
                                            <td colspan="5">Assign the store to user:</td><?php
        ?>
                                        </tr>

                                        <tr>
                                            <td colspan="2">All Stores </td>
                                            <td colspan="1">&nbsp;</td>
                                            <td colspan="2">User Assigned Stores</td>
                                        </tr>
                                        <tr>
                                            <td rowspan="3" colspan="2" align="right"><label>
                                                    <select name="selectLeft[]" multiple size="10" width="100%" style="width:200px;" id="selectLeft"> 
                                                        <?php
                                                        $asgnexe = "select store_id from executive_assign where exe_id in (" . $this->userid . ")";

                                                        $fasnid = $db->fetchObjectArray($asgnexe);
                                                        //print_r($fasnid);
                                                        $st = "";
                                                        foreach ($fasnid as $exe) {
                                                            $fasnid = $exe->store_id;
                                                            //print_r($fasnid);
                                                            $st = $st . $fasnid . ",";
                                                        }
                                                        $rmvcoln = substr($st, 0, -1);

                                                        if (!empty($fasnid)) {
                                                            $query = "select id,store_name,roles from it_codes  where usertype =4 and is_closed=0 and id not in ($rmvcoln)";
                                                        }
                                                        $obj_aRegion = $db->fetchObjectArray($query);
                                                        if (!empty($obj_aRegion)) {
                                                            echo '<option value="allstore">All Stores</option>';
                                                        }
                                                        ?>  
                                                        <?php
                                                        if (true) {
                                                            $asgnexe = "select store_id from executive_assign where exe_id in (" . $this->userid . ")";

                                                            $fasnid = $db->fetchObjectArray($asgnexe);
                                                            //print_r($fasnid);
                                                            $st = "";
                                                            foreach ($fasnid as $exe) {
                                                                $fasnid = $exe->store_id;
                                                                //print_r($fasnid);
                                                                $st = $st . $fasnid . ",";
                                                            }
                                                            $rmvcoln = substr($st, 0, -1);
                                                            if (!empty($fasnid)) {
                                                                $query = "select id,store_name,roles from it_codes  where usertype =4 and is_closed=0 and id not in ($rmvcoln)";
                                                            } else {
                                                                $query = "select id,store_name,roles from it_codes  where usertype =4 and is_closed=0";
                                                            }
                                                            $obj_aRegion = $db->fetchObjectArray($query);
                                                        } $count = 0;
                                                        foreach ($obj_aRegion as $region) {

                                                            $count++;
                                                            ?>
                                                            <option value="<?php echo $region->id; ?>"><?php echo $region->store_name; ?></option>
        <?php } ?> 
                                                    </select>
                                                </label></td>
                                            <td colspan="1" rowspan="3" style="vertical-align:middle;">
                                                <input name="btnRight" type="button" id="btnRight" value="&gt;&gt;" onClick="javaScript:moveToRightOrLeft(1);">
                                                <br/><br/>
                                                <input name="btnLeft" type="button" id="btnLeft" value="&lt;&lt;" onClick="javaScript:moveToRightOrLeft(2);">
                                            </td>
                                            <td rowspan="3" colspan="2" align="left">
                                                <select name="selectRight[]"  multiple size="10" style="width:200px;" id="selectRight">
                                                    <?php
                                                    $asgnexe = "select store_id from executive_assign where exe_id in (" . $this->userid . ")";

                                                    $fasnid = $db->fetchObjectArray($asgnexe);
                                                    //print_r($fasnid);
                                                    $st = "";
                                                    foreach ($fasnid as $exe) {
                                                        $fasnid = $exe->store_id;
                                                        //print_r($fasnid);
                                                        $st = $st . $fasnid . ",";
                                                    }
                                                    $rmvcoln = substr($st, 0, -1);

                                                    $query = "select * from it_codes where id in ($rmvcoln)";
                                                    $obj_aRegion = $db->fetchObjectArray($query);
                                                    if (!empty($obj_aRegion)) {
                                                        echo '<option value="allstore">All Stores</option>';
                                                    }
                                                    ?>
                                                    <?php
                                                    $asgnexe = "select store_id from executive_assign where exe_id in (" . $this->userid . ")";

                                                    $fasnid = $db->fetchObjectArray($asgnexe);
                                                    //print_r($fasnid);
                                                    $st = "";
                                                    foreach ($fasnid as $exe) {
                                                        $fasnid = $exe->store_id;
                                                        //print_r($fasnid);
                                                        $st = $st . $fasnid . ",";
                                                    }
                                                    $rmvcoln = substr($st, 0, -1);

                                                    $query = "select * from it_codes where id in ($rmvcoln)";
                                                    $obj_aRegion = $db->fetchObjectArray($query);

                                                    foreach ($obj_aRegion as $region) {
                                                        ?>

                                                        <option selected="selected" value="<?php echo $region->id; ?>"><?php echo $region->store_name; ?></option>
        <?php } ?>

                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <!--		user assign store end-->
                    <p>
                        <label>Mobile No: </label>
                        <input type="text" name="mobile" value="<?php echo $this->getFieldValue('mobile',$user->phone); ?>">
                    </p>
                    
                    <p>
                                <label> Department: </label>
                                <select name="rolltype" id="rolltype" >
                                    <option <?php echo ($user->roles == "" || $user->roles == NULL) ? "selected" : "" ?> value="">Select Department</option>
                                    <?php
                                        $allRollTypes = RollType::getALL();
                                        foreach ($allRollTypes as $usertype => $typename) { ?>
                                            <option <?php echo ( $user->roles == $usertype ) ? "selected" : "" ?> value="<?php echo $usertype; ?>" <?php echo $selected; ?>><?php echo $typename; ?></option>
                                    <?php } ?>
                                </select> 
                    </p>
                    <p>
                        <label>Password: (leave blank if you donot want to change)</label>
                        <input type="password" name="password" value="">
                    </p>
                    <p>
                        <label>Confirm Password: </label>
                        <input type="password" name="password2" value="">
                    </p>
                </span>
                        <?php if ($formResult) { ?>
                <p>
                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                </p>
                        <?php } ?>
                <input type="submit" value="Update" onClick="javaScript:selectall();">
                <a href="admin/users"><Button>Cancel</Button></a>                
            </form>
        </fieldset>
    </div>

</div>
    <?php
    }
}
?>