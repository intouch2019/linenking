<?php
ini_set('max_execution_time', -1);
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";

class cls_addstoreaudit extends cls_renderer {

    var $currStore;
    var $params;
    var $storeid;
    var $dtrange;
    var $sid;
    var $aid;
    var $view;
     
    function __construct($params = null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
        $this->dtrange = date("d-m-Y");
        if (isset($params["sid"]) && $params["sid"] != "") {
            $this->sid = $params["sid"];
        }
        if (isset($params["aid"]) && $params["aid"] != "") {
            $this->aid = $params["aid"];
        }
        if (isset($params["view"]) && $params["view"] != "") {
            $this->view = $params["view"];
        }
    }

    function extraHeaders() {
        if (!$this->currStore) {
            ?>
            <h2>Session Expired</h2>
            Your session has expired. Click <a href="">here</a> to login.
            <?php
            return;
        }
        ?>
        <script type="text/javascript" src="js/expand.js"></script>
        <link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
        <script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
       

       
        </script>
<script>

    function getselecteddate(){
     auditdate=document.getElementById("AuditDate").value;
     auditdate2=document.getElementById("AuditDate2").value;
     if(auditdate==auditdate2){
         document.getElementById("submit1").disabled=true;
//         document.getElementById("statusmsg").innerHTML="*Please check Audit Date".fontcolor("red");
         
     }else{
         document.getElementById("submit1").disabled=false;
//         document.getElementById("statusmsg").hidden=true;
     }
    
}


function validationform(){
    var error=0;
    var managername=document.getElementById("Manager_name").value;
    var Managerphone=document.getElementById("Managerphone").value;
    var auditorname=document.getElementById("Auditor_name").value;
    var auditdate=document.getElementById("AuditDate").value;
    var remark=document.getElementById("remark").value;
//    alert(managername);
    if(managername==null || managername==""){
      alert("Field Manager_name Could Not be Blank");
      error++;
    } else if(Managerphone==null || Managerphone==""){
        alert("Field Managerphone Could Not be Blank"); 
        error++;
    }else if(auditorname==null || auditorname==""){
        alert("Field Auditor_name Could Not be Blank");
        error++;
    }else if(auditdate==null || auditdate==""){
        alert("Field AuditDate Could Not be Blank");
        error++;
    }else if(remark==null || remark==""){
       alert("Field remark Could Not be Blank");  
       error++;
    }
      totques=document.getElementById("totalquestions").value;
    for(i=1;i<=totques;i++){
        ansquestionyes=document.getElementById("queyes"+i).checked;
        ansquestionno=document.getElementById("queno"+i).checked;
       // alert("<b>"+ansquestionyes+"<b>"+i+ansquestionno);
        if(ansquestionyes == false){
            if(ansquestionno == false){
            alert("Please select the options for all questions");
            error++;
            break;
        }
        }
        }

    
    var phoneno = /^\d{10}$/;
    //alert(Managerphone.match(phoneno));
  if(!Managerphone.match(phoneno))
  {
        alert("incorrect Mobile Number");
        error++;
       document.getElementById("Managerphone").value="";
        }
      //alert(error);  
    if(error==0){
        var aconfirm = confirm("Are You Sure to submit the form ?");
        if(aconfirm == 1){
            var a=0;
             document.getElementById("myForm").submit();
        }else{
           document.getElementById("auditdate").value="";
        } 
    }
    
    
}


</script>
        <?php
    }


    public function pageContent() {
        $menuitem = "addstoreaudit";
        $db = new DBConn();
        include "sidemenu." . $this->currStore->usertype . ".php";

        ?>

        <div class="grid_10">
                <div class="box">

                    <?php 
                 
                    $audit =$db->fetchObject("select * from it_auditdetails where id =$this->aid order by id desc ");
                    $storename =$db->fetchObject("select store_name from it_codes where id= $this->sid ");
                 ?> 
                  

                    <h2>
                        <a href="#" id="toggle-accordion" style="cursor: pointer; ">Store Audit Form</a>
                    </h2><br>
                    <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow-x: hidden; overflow-y: hidden; ">
                        <form action ="formpost/storeaudit.php" id="myForm" method="POST">
                        <div class="block" id="accordion" style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; ">
                            <div id="accordion">
                           

                                    <div class="grid_12">
                                        <h1><?php print_r($storename->store_name)?></h1>
                                        <div class="grid_4">
                                        <label><span style="color:red">*</span>Manager Name: </label>
                                        <input type="text"  id="Manager_name" name="Manager_name" style="width:100%;height:23px;font-size:14px;" required value="<?php if(isset($audit)){echo $audit->Manager_name;} ?>"required>
                                        </div>
                                       
                                        <div class="grid_4">
                                        <label><span style="color:red">*</span>Manager Mobile Number : </label>
                                        <input type="text" id="Managerphone" name="Managerphone"  style="width:100%;height:23px;font-size:14px;" required value="<?php if(isset($audit)){echo $audit->Managerphone;} ?>"required>
                                        </div>
                                    </div>
                              



                                    <div class="grid_12">
                                        <br>
                                        <div class="grid_4">
                                        <label><span style="color:red">*</span>Auditor Name: </label>
                                        <input type="text" id="Auditor_name" name="Auditor_name" style="width:100%;height:23px;font-size:14px;" required value="<?php if(isset($audit)){echo $audit->Auditor_name;} ?>"required>
                                        </div>
                                        
                                        <div class="grid_2">
                                    <lable style="font-size:10px;"><span style="color:red">*</span>Audit Date:</lable>
                                    
                                    <input type="date" id="AuditDate" name="AuditDate" min="<?= date("Y-m-d",strtotime($audit->AuditDate.'+1 day'))?>" max="<?= date('Y-m-d'); ?>" style="width:100%;height:23px;font-size:14px;" value="<?= date("Y-m-d",strtotime($audit->AuditDate))?>" onchange ="getselecteddate()" required>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="date" hidden id="AuditDate2" name="AuditDate2" min="<?= date("Y-m-d",strtotime($audit->AuditDate.'+1 day'))?>" max="<?= date('Y-m-d'); ?>" style="width:100%;height:23px;font-size:14px;" value="<?= date("Y-m-d",strtotime($audit->AuditDate))?>" onchange ="getselecteddate()" required>&nbsp;&nbsp;&nbsp;&nbsp;
                                    
                                        </div>
                                    </div>
                                  </div>
                                  </div>
                
                            <div class="box" style="margin-left:0px; overflow:auto; height:350px; padding-bottom: 20px">
                            <table> 
                                    <tr>
                                        <th>ID</th>
                                        <th> Description</th>
                                        <th> Yes</th>
                                        <th> No</th>


                                    </tr>
                                    <?php
                                  
                                    $rules = $db->fetchObjectArray("select * from it_auditquestions order by id");?>
                                    <input type="hidden" id="totalquestions"  value=<?php echo sizeof($rules);?>><?php

                                    foreach ($rules as $rule) {
                                        if(isset($this->aid) && $this->aid !==""){
                                        $audit_response =$db->fetchObject("select is_opted from it_auditresponse where audit_id =$this->aid  and question_id= $rule->ID");
                                       
                                    }?>
                                        <tr>
                                            <td><?php echo $rule->ID; ?></td>
                                            <td><?php echo $rule->description; ?></td>
                                            <td style="width:8%"><input type ="radio" name="que<?php echo $rule->ID?>" id="queyes<?php echo $rule->ID?>" style="width:20%" <?php if (isset ($audit_response) && $audit_response->is_opted == 1) { ?>checked <?php } ?> value="1" required>Yes</td>
                                            <td style="width:8%"><input type ="radio" name="que<?php echo $rule->ID?>" id="queno<?php echo $rule->ID?>" style="width:20%" <?php if (isset ($audit_response) && $audit_response->is_opted == 0) { ?>checked <?php } ?> value="0" required>No</td>
                                        </tr>
                                    <?php }?>
                                        <tr></tr>
                                </table>
                                <div class="grid_12">
                                    <label id="t_address"><b> <span style="color:red">*</span> Observations & Remarks:</b> </label><br>
                                    <!--<input type="text" name="remark" style="width:100%" style="length:100%" required value="<?php if(isset($audit)){echo $audit->remark;} ?>"required>-->
                                    <textarea id="remark" name="remark" required value="" rows="2" cols="100"><?php if(isset($audit)){echo $audit->remark;} ?></textarea>
                                    <br><br>
                                </div>
                                
                                <input type="hidden" value="<?php echo $this->sid;?>"   name="store_id" id="store_id">

                          
                            <?php if ($this->view!=1 ){?>
                            <div align='center'>
                                
                                <input type="submit" id="submit1" onclick="validationform()" value="Submit Audit Form">
<!--                                <br><span id="statusmsg"></span>-->
                            </div>
                            <?php }?>
                            </div>
                       
              </form>
                                     </div> 
                </div>  
            <script>getselecteddate();</script> 
        </div>
        <?php
    }

}
?>
  
