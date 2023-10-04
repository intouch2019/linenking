<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_task_manager extends cls_renderer {

    var $params;
    var $id;
    var $status;

    function __construct($params = null) {
//	parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Manager));//
        $this->currStore = getCurrUser();

        if (isset($_SESSION['id'])) {
            $this->id = $_SESSION['id'];
        } else {
             if($this->currStore->usertype==UserType::CKAdmin){
                $this->id = "1";
            }else{
            $this->id = "2";
            }
        }
        
        if(isset($_SESSION['status'])){
              $this->status = $_SESSION['status'];
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
        <!--//--><![CDATA[//>

            function progressbar(sta)
            {
        
                    window.location = "formpost/task_manager_status.php?id=" + sta;
                    exit;
              
                //window.location.reload();
            }


            function reload() {
                var dtrange = $("#dateselect").val();
                //var id = $("#cn").val();
                //                alert("id");
                $.ajax({
                    url: "savesession.php?name=account_dtrange&value=" + dtrange,
                    success: function (data) {
                        window.location.reload();
                    }
                });
            }
 
            $(function (    ) {
                $("#cn").change(function () {
                    var id = $("#cn").val();
                    //alert();

                    $.ajax({
                        url: "savesession.php?name=id&value=" + id,
                        success: function (data) {
                            window.location.reload();

                        }
                    });

                });
                $("#cn1").change(function () {
                    var id = $("#cn1").val();
                    $.ajax({
                        url: "savesession.php?name=id&value=" + id,
                        success: function (data) {
                            //alert(data);
                            window.location.reload();
                        }
                    });
                });
                $("#cn2").change(function () {
                    var id = $("#cn2").val();
                    $.ajax({
                        url: "savesession.php?name=id&value=" + id,
                        success: function (data) {
                            //alert(data);
                            window.location.reload();
                        }
                    });
                });
            }
            );
            
            
    function reopenTask(rid){
    window.location = "formpost/task_manager_status.php?rid=" + rid;
    exit;
    }
 
 </script>
            <style>
            .tooltip {
                position: relative;
                display: inline-block;
                border-bottom: 1px dotted black;
            }

            .tooltip .tooltiptext {
                border:1px solid black;
                visibility: hidden;
                width: 200px;
                background-color: white;
                color:black;
                text-align: left;
                border-radius: 2px;
                padding: 5px;
                bottom: 100%;
                left: 50%;
                margin-left: 0px;

                /* Position the tooltip */
                position: absolute;
                z-index: 1;
            }

            .tooltip:hover .tooltiptext {
                visibility: visible;
            }


</style>
                                                
            <?php
        }

        //extra-headers close

        public function pageContent() {
            $menuitem = "taskmanager";
            include "sidemenu." . $this->currStore->usertype . ".php";
            $formResult = $this->getFormResult();
            $write_htm = true;
            ?>


            <div class="grid_10">
                <?php
                $db = new DBConn();
                $store_id = getCurrUserId();
                $user = getCurrUser();
//                print_r($user);
                ?>
                <div class="box">
                    <h2>Task Manager</h2><br>

                    <div style="margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; position: static; overflow:auto; ">
                        <div style="float:right">
                       
                            <button style="font-weight:bold; margin-left:20px;padding: 8px 16px;text-align: center;"><a  href="edittaskmanager">Assign Task</a></button>
               

                        </div>
                        <div class="grid_12">

                     
<!--                            <input type="radio" id="cn" name="cn" value="1"style="height:20px; width:20px;  <?php if ($this->id == 1) { ?>checked <?php } ?> onchange="reload()" ><b>Send &nbsp;&nbsp;&nbsp;</b>
                            <input type="radio" id="cn1" name="cn1" value="2"style="height:20px; width:20px;    <?php if ($this->id == 2) { ?>checked <?php } ?>  onchange="reload()"><b>Receive &nbsp;&nbsp;&nbsp;</b>
                            <input type="radio" id="cn2" name="cn2" value="3"style="height:20px; width:20px;    <?php if ($this->id == 3) { ?>checked <?php } ?>  onchange="reload()"><b>History &nbsp;&nbsp;&nbsp;</b>-->
                            
                            <input type="radio" id="cn" name="cn" value="1" style="height:20px; width:20px;" <?php echo $this->id == 1 ? 'checked' : ''; ?> onchange="reload()"><b>Send &nbsp;&nbsp;&nbsp;</b>
                            <input type="radio" id="cn1" name="cn1" value="2" style="height:20px; width:20px;" <?php echo $this->id == 2 ? 'checked' : ''; ?> onchange="reload"()><b>Receive &nbsp;&nbsp;&nbsp;</b>
                            <input type="radio" id="cn2" name="cn2" value="3" style="height:20px; width:20px;" <?php echo $this->id == 3 ? 'checked' : ''; ?> onchange="reload"()><b>Completed Task &nbsp;&nbsp;&nbsp;</b>

                            
                            
                            
                           <br><br>


                            <?php if ($this->id == 1) {
                                ?>
                                <!--                                <div class="grid_2">&nbsp;</div>-->
                                <div class="grid_20">
                                    <table>
                                        <caption><b><h4>TASK SEND BY YOU</h4></b></caption>

                                        <tr>
                                             <th>ID</th>
                                            <th>Subject</th>
                                            <th>Sender Department</th>
                                            <th>Task Create Date</th>
                                            <th><h1>&#8594;</h1></th>
                                            <th>Receiver Department</th>
                                            <th>Receiver Name</th>
                                            <th>Task_Info</th>
                                            <th>Progress</th>
                                            <th>Start Date</th>
                                            <th>Finished Date</th>
                                            <th>Action</th>

                                        </tr>
                                        <?php
                  
                                        $query = "Select id,subject,s_department,r_department,s_name,status,task_info,createtime,progress,startdate,finisheddate,receivername from it_task_manager where  (status = 1 OR status = 3) and s_name='$user->store_name' order by id desc "; // for differnt users
//                                        $query = "Select id,subject,s_department,r_department,s_name,status,task_info,createtime,progress,startdate,finisheddate,receivername from it_task_manager where  status=1 order by id desc ";
                                        $qresult = $db->fetchObjectArray($query);
                                   if(isset($qresult)){
                                        foreach ($qresult as $details) {
                                               
                                            ?>
                                            <tr>
                                                   <td><?php echo $details->id ?></td>
                                                   <td><div class="tooltip">Show Subject<span class="tooltiptext"><?php echo $details->subject; ?></span></div></td>  
                                                    <td><?php echo RollType::getName($details->s_department)."(". $details->s_name.")" ?></td>
                                                    <!--<td><?php // echo RollType::getName($details->s_department); ?></td>-->
                                                    <!--<td><?php // echo RollType::getName( $details->s_department)."(". $details->s_name.")" ?></td>-->
                                                    <td><?php echo $details->createtime ?></td>
                                                    <td><h1>&#8594;</h1></td>
                                                    <td><?php echo RollType::getName($details->r_department); ?></td>
                                                    <td><?php echo $details->receivername ?></td>
                                                    <!--<td><?php // echo $details->r_department ?></td>-->
                                                    <td><a  href="edittaskmanager/ids=<?php echo $details->id ?>">Info</a></td> 
                                                    <td>   <progress id="file" max="100" value="<?php echo $details->progress; ?>"> 70% </progress> </td> 
                                                    <td><?php echo $details->startdate ?></td><!-- comment -->
                                                    <td><?php echo $details->finisheddate ?></td>
                                                     <?php if ($details->status == 3 && $details->progress == 100) { ?>
                                                    <td><button type="button" style="width: 100px; background-color: #D7A4A3; border-radius: 4px; border: 1px solid black;  cursor: pointer;" onclick="reopenTask(<?php echo $details->id  ?>)">Reopen Task</button></td>
                                                      <?php } else { ?><td></td><?php } ?>
                                        
                                            </tr>
                                   <?php }} ?>
                                    </table>
                                </div>
                            <?php }
                            ?> 

                            <?php if ($this->id == 2) {
                                ?>
                                <!--                                <div class="grid_2">&nbsp;</div>-->
                                <div class="grid_20">
                                    <table>
                                        <caption><b><h4>TASK RECEIVED BY YOU</h4></b></caption>
                                        <tr>
                                               <th>ID</th>
                                            <th>Subject</th>
                                            <th>Sender Department</th>
                                            <th>Task Create Date</th>
                                            <th><h1>&#8594;</h1></th>
                                            <th>Receiver Department</th>
                                            <th>Receiver Name</th>
                                            <th>Task_Info</th>
                                            <th>Progress</th>
                                            <th>Start Date</th>
                                           
                                        </tr>
                                        <?php
                                        $query = "Select id,subject,s_department,r_department,s_name,status,task_info,createtime,progress,startdate,finisheddate,receivername from it_task_manager where  status=1 and r_department='$user->roles' order by id desc";
//                                        $query = "Select id,subject,s_department,r_department,s_name,status,task_info,createtime,progress,startdate,finisheddate,receivername from it_task_manager where  status=1 order by id desc";
//                                        print_r($query);exit();
                                        $qresult = $db->fetchObjectArray($query);
                                         if(isset($qresult)){
                                        foreach ($qresult as $details) {
                                        //    $oarr = explode(",",$order->order_ids);          
                                            
                                            ?>
                                            <tr>
                                              <td><?php echo $details->id ?></td>
                                                  <td><div class="tooltip">Show Subject<span class="tooltiptext"><?php echo $details->subject; ?></span></div></td>  
                                                    <td><?php echo RollType::getName( $details->s_department)."(". $details->s_name.")" ?></td>
                                                    <td><?php echo $details->createtime ?></td>
                                                    <td><h1>&#8594;</h1></td>
                                                    <td><?php echo RollType::getName($details->r_department); ?></td>
                                                    <td><?php echo $details->receivername ?></td>
                                                   <!--<td><?php // echo $details->r_department ?></td>-->
                                                      <td><a  href="showtaskmanager/ids=<?php echo $details->id ?>">Info</a></td>
                                                      
                                                      <?php if($details->progress=='0'){?>
                                                    <td><button onclick='progressbar(<?php echo $details->id ?>)'>Start</button>
                                                     <?php }else{?>
                                                    <td>
                                                    <?php }?>
                                                    <progress id="file" max="100" value="<?php echo $details->progress; ?>"> 75% </progress>
                                                    <?php if($details->progress=='50' || $details->progress=='75'){?>
                                                    
                                                    <button onclick='progressbar(<?php echo $details->id ?>)'>Finished</button>
                                                    <?php }?>
                                                    </td> 
                                                      <td><?php echo $details->startdate ?></td><!-- comment -->
                                                
                                            </tr>
                                         <?php } }?>
                                    </table>
          
                                </div>
  
                            <?php }
                            ?> 

                            <?php if ($this->id == 3) {
                                ?>
                       <!--                                <div class="grid_2">&nbsp;</div>-->
                                <div class="grid_20">
                                    <table>
                                        <caption><b><h4>TASK WORKED UPON</h4></b></caption>

                                        <tr>
                                             <th>ID</th>
                                            <th>Subject</th>
                                            <th>Sender Department</th>
                                            <th>Task Create Date</th>
                                            <th><h1>&#8594;</h1></th>
                                            <th>Receiver Department</th>
                                            <th>Receiver Name</th>
                                            <th>Task_Info</th>
                                            <th>Progress</th>
                                            <th>Start Date</th>
                                            <th>Finished Date</th>

                                        </tr>
                                        <?php
                  
                                        $query = "Select id,subject,s_department,r_department,s_name,status,task_info,createtime,progress,startdate,finisheddate,receivername from it_task_manager where  status=3 and r_department='$user->roles' order by id desc";
//                                        $query = "Select id,subject,s_department,r_department,s_name,status,task_info,createtime,progress,startdate,finisheddate,receivername from it_task_manager where  status=3 order by id desc";
//                                        print_r($query);exit();
                                        $qresult = $db->fetchObjectArray($query);
                                        if(isset($qresult)){
                                        foreach ($qresult as $details) {
                                            ?>
                                            <tr>
                                                   <td><?php echo $details->id ?></td>
                                                  <td><div class="tooltip">Show Subject<span class="tooltiptext"><?php echo $details->subject; ?></span></div></td>  
                                                    <td><?php echo RollType::getName($details->s_department)."(". $details->s_name.")" ?></td>
                                                    <td><?php echo $details->createtime ?></td>
                                                    <td><h1>&#8594;</h1></td> 
                                                    <td><?php echo RollType::getName($details->r_department); ?></td>
                                                    <td><?php echo $details->receivername ?></td>
                                                   <!--<td><?php // echo $details->r_department ?></td>-->
                                                    <!--<td><a  href="edittaskmanager/ids=<?php // echo $details->id ?>">Task Details</a></td>--> 
                                                    <td><a  href="historydetails/ids=<?php echo $details->id ?>">Task Details</a></td> 
                                                    <td>   <progress id="file" max="100" value="<?php echo $details->progress; ?>"> 70% </progress> </td> 
                                                    <td><?php echo $details->startdate ?></td>
                                                    <td><?php echo $details->finisheddate ?></td>
                                        
                                            </tr>
                            <?php }} ?>
                                    </table>
                                </div>
                            <?php }
                            ?>        
                        </div>
                    </div>
                    <?php
                }

            }
            ?>