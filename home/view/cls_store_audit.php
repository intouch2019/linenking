
<?php
ini_set('max_execution_time', -1);
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_store_audit extends cls_renderer {

    var $currUser;
    var $userid;
    var $dtrange;
    var $sid;
    var $currStore;

    function __construct($params = null) {
        //parent::__construct(array(UserType::Admin, UserType::CKAdmin));
        $this->currUser = getCurrUser();
        $this->userid = $this->currUser->id;
        $this->currStore = getCurrUser();
        $this->params = $params;
        if (!$this->currStore) {
            return;
        }
        if ($params && isset($params['sid'])) {
            $this->sid = $params['sid'];
        }
        if (isset($_GET["dtrange"]) && $_GET["dtrange"] != "") {
      $this->dtrange = $_GET["dtrange"];} 
       else { $this->dtrange = date("d-m-y"); }
    }

    function extraHeaders() {
        ?>
        <link rel="stylesheet" href="jqueryui/css/custom-theme/jquery-ui-1.8.14.custom.css" type="text/css" media="screen" charset="utf-8" />
        <script type="text/javascript" src="js/expand.js"></script>
        <script language="JavaScript" src="js/tigra/validator.js"></script>
        <script type="text/javascript">
            
     
            
            /************************************************************************************************************
             (C) www.dhtmlgoodies.com, April 2006
                     
             This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.	
                     
             Terms of use:
             You are free to use this script as long as the copyright message is kept intact. However, you may not
             redistribute, sell or repost it without our permission.
                     
             Thank you!
                     
             www.dhtmlgoodies.com
             Alf Magne Kalleland
                     
             ************************************************************************************************************/

        </script>
        <link rel="stylesheet" href="css/bigbox.css" type="text/css" />
        <script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
        <script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
        <link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
        <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
                <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>

        <script type="text/javascript">
            
            $(function () {
                //                $(".chzn-select").chosen();
                $(".chzn-select-deselect").chosen({allow_single_deselect: true});
                var isOpen = false;
                $('#dateselect').daterangepicker({
                    dateFormat: 'dd-mm-yy',
                    arrows: false,
                    closeOnSelect: true,
                    onOpen: function () {
                        isOpen = true;
                    },
                    onClose: function () {
                        isOpen = false;
                    },
                    onChange: function () {
                        if (isOpen) {
                            return;
                        }
                        var dtrange = $("#dateselect").val();
//                        $.ajax({
//                            url: "savesession.php?name=account_dtrange&value=" + dtrange,
//                            success: function (data) {
//                                //window.location.reload();
//                            }
//                        });
                        var getstoreid = document.getElementById("avalue").value;
                        // alert(getstoreid);
                        window.location.href = "store/audit/sid=" + getstoreid +"&dtrange="+dtrange;

                    }
                });


                //        $('#dwnloadbtn').hide();
            });
            function addstoreaudit1() {

                var getstoreid = document.getElementById("avalue").value;
                // alert(getstoreid);
                window.location.href = "addstoreaudit/sid=" + getstoreid;
                //  window.location.href = "designs/search/dno=" + design_no;
            }

            function getstoredetails() {

                var getstoreid = document.getElementById("avalue").value;
                // alert(getstoreid);
                window.location.href = "store/audit/sid=" + getstoreid;
                //  window.location.href = "designs/search/dno=" + design_no;
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

    public function pageContent() {
        $currUser = getCurrUser();
        $menuitem = "store_audit";
        include "sidemenu." . $currUser->usertype . ".php";
        $formResult = $this->getFormResult();
        $db = new DBConn();
        $sids = explode(',', $this->sid);
        $dtarr = explode(" - ", $this->dtrange);
        ?>
        <div class="grid_10">

            <fieldset>                         

                <legend>Stores</legend>

                <div class="grid_2">
                    <lable style="font-size:15px;">Select Store</lable>
                </div>   

                <div class="grid_4">

                    <select name="avalue" id="avalue" data-placeholder="Select Store" class="chzn-select" single style="width:100%;" onchange= getstoredetails()>

                        <option value="1" selected>Select Store</option>
                         <?php
                        if ($currUser->usertype == UserType::Admin || $currUser->usertype == UserType::CKAdmin) { ?>
                        <option value="-1" <?php echo($this->sid == -1) ? "selected" : "" ?>>All Stores</option>
                        
                        <?php }
                        if ($currUser->usertype == 4) {
                            $storeqry = " and id=$currUser->id";
                        } else {
                            $storeqry = "";
                        }
                        $objs = $db->fetchObjectArray("select id,store_name from it_codes where usertype=" . UserType::Dealer . "  and is_closed=0 $storeqry order by store_name");
                        if ($objs) {
                            foreach ($objs as $obj) {
                                if (in_array($obj->id, $sids)) {

                                    $sel = 'selected';
                                } else {
                                    $sel = '';
                                }
                                ?>
                                <option value="<?php echo $obj->id; ?>"<?php echo $sel; ?> ><?php echo $obj->store_name; ?></option>
                                <?php
                            }
                        }
                        ?>
                        <?php ?>
                    </select>
                </div>
                 <?php
                        if ($currUser->usertype == UserType::Admin || $currUser->usertype == UserType::CKAdmin) { ?>
                <div class="grid_1">
                    <lable style="font-size:15px;">Date Filter</lable>
                </div>   

                <div class="grid_5">
                    <input size="17" type="text" id="dateselect" name="dateselect" autocomplete="off" value="<?php echo $this->dtrange; ?>" /> (Click to see date options)
                </div>
                        <?php } ?>
                
                <div class="grid_6">
                    <button><a href="https://cottonking.intouchrewards.com/tmp1/StoreAuditform.pdf" download >Download Store Audit Form</a></button>
                </div>
                <?php
                if ($currUser->usertype == UserType::Admin || $currUser->usertype == UserType::CKAdmin) { ?>
                <div class="grid_6">
                    <br /><div id="dwnloadbtn" style='margin-left:40px; padding-left:15px; height:24px;width:130px;border: solid gray 1px;background:#F5F5F5;padding-top:4px;'>
                        <a href='<?php echo "util/auditDetails.php?dtrange=$this->dtrange"; ?>' title='Export table to CSV'><img src='images/excel.png' width='20' hspace='3' style='margin-bottom:-6px;' /> Export To Excel</a>
                    </div><br />
                </div>
                <?php } ?>
                <br><br>
                <div><table border="3">

                        <tr>
                            <th colspan="15"><h4>Store Audit Details</h4></th>
                        </tr>
                        <tr>
                            <th>Audit id</th>
                            <th>Store Name</th>
                            <th>Manager Name</th>
                            <th>Manager Mobile Number</th>
                            <th>Auditor Name</th>
                            <th>Audit Date</th>
                            <th>Submitted Date</th>
                            <th>Score</th>
                            <th>Remarks</th>
                            <?php
                            if ($this->sid == -1) {
                                ?> <th></th><?php
                            } else {
                                ?> <th>Action</th><?php } ?>
                        </tr>


                        <?php
                       $dQuery = "";
                        if ($currUser->usertype == UserType::Admin || $currUser->usertype == UserType::CKAdmin){
                            $dtarr = explode(" - ", $this->dtrange);
                            if (count($dtarr) == 1) {
                                    list($dd,$mm,$yy) = explode("-",$dtarr[0]);
                                    $sdate = "$yy-$mm-$dd";		
                                    $dQuery = " and a.SubmittedDate >= '$sdate 00:00:00' and a.SubmittedDate <= '$sdate 23:59:59' ";
                            } else if (count($dtarr) == 2) {
                                    list($dd,$mm,$yy) = explode("-",$dtarr[0]);
                                    $sdate = "$yy-$mm-$dd";
                                    list($dd,$mm,$yy) = explode("-",$dtarr[1]);
                                    $edate = "$yy-$mm-$dd";		
                                    $dQuery = " and a.SubmittedDate >= '$sdate 00:00:00' and a.SubmittedDate <= '$edate 23:59:59' ";
                            }
                            else {
                                    $dQuery = "";
                            }
                        }
                        
                        
                        
                      
                        if (isset($this->sid) && $this->sid !== "" && $this->sid == -1) {
                            $qsid = "order by s.store_name desc";
                        } else if (isset($this->sid) && $this->sid !== "") {
                            $qsid = "and a.store_id=$this->sid order by a.id desc";
                        } else {
                            $qsid = "and a.store_id=-1 order by a.id desc";
                        }
                        $objsdetails = $db->fetchObjectArray("select a.*,s.store_name ,(select count(*)as score from it_auditresponse where "
                                . "audit_id = a.id and is_opted=1)as score,(select count(*)as score from it_auditresponse where audit_id = a.id )as outof from "
                                . "it_auditdetails a, it_codes s  where a.store_id= s.id $dQuery $qsid  ");
                       
                        $max = 0;
                        $i = sizeof($objsdetails);
                        if ($i == 0 && $this->sid > 1) {
                            ?><h6 span class="error" style="color:white;"> No Store Audit Report Available For Selected Store </h6></span><br><div align='right'class="grid_4"><button onclick = "addstoreaudit1()">Create Store Audit Report</button><br><br></div><?php
                        }
                        foreach ($objsdetails as $obj) {
                            ?>      
                            <tr>
                                <td><?php echo $i ?></td>
                                <td><?php echo $obj->store_name; ?></td>
                                <td><?php echo $obj->Manager_name; ?></td>
                                <td><?php echo $obj->Managerphone; ?></td>
                                <td><?php echo $obj->Auditor_name; ?></td>
                                <td><?php echo $obj->AuditDate; ?></td>
                                <td><?php echo $obj->SubmittedDate; ?></td>
                                <td><?php echo $obj->score . "/" . $obj->outof ?></td>
                                <td><div class="tooltip">Show Remark<span class="tooltiptext"><?php echo $obj->remark; ?></span></div></td> 

                                <?php if ($this->sid == -1) {
                                    ?> <td></td> <?php
                                } else if ($currUser->usertype == 4) {
                                    ?> <td><a href='addstoreaudit/aid=<?php echo $obj->id; ?>/sid=<?php echo $obj->store_id; ?>/view=<?php echo 1; ?>'>View</a></td>                   
                                    <?php
                                } else {
                                    if ($max == 0) {
                                        ?><td><a href='addstoreaudit/aid=<?php echo $obj->id; ?>/sid=<?php echo $obj->store_id; ?>'>View & Edit</a></td> <?php
                                        $max++;
                                    } elseif ($max > 0 && $max <= 2) {
                                        ?><td><a href='addstoreaudit/aid=<?php echo $obj->id; ?>/sid=<?php echo $obj->store_id; ?>/view=<?php echo 1; ?>'>View</a></td> <?php
                                        $max++;
                                    } else {
                                        ?> <td></td> <?php
                                    }
                                }
                                ?>



                            </tr>
                            <?php
                            $i--;
                        }
                        ?></table></div>
            </fieldset> </div>


        <?php
    }

}
?>