
<?php
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
        <script type="text/javascript">

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
                                $objs = $db->fetchObjectArray("select id,store_name from it_codes where usertype=" . UserType::Dealer . " and inactive=0 and is_closed=0 order by store_name");

                                if ($objs) {
                                    foreach ($objs as $obj) {


                                    if (in_array($obj->id, $sids)){
                                    
                                        $sel = 'selected';
                                        
                                    } else {
                                        $sel = '';
                                    }
                                        ?>

                                        <option value="<?php echo $obj->id; ?>"<?php echo $sel; ?>  ><?php echo $obj->store_name; ?></option>
                                        <?php
                                    }
                                }
                                ?>

                                <?php ?>
                            </select>
                            </br>

                        </div>
               
                        
                        &nbsp;&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;   
                        <button><a href="https://cottonking.intouchrewards.com/tmp1/StoreAuditform.pdf" download >Download Store Audit Form</a></button>
                       <br><br> <div><table border="3">
                               
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
                        <th>Remarks</th>
                         <th>Action</th>
                    </tr>
                    
 
                       <?php
                       if (isset($this->sid)&& $this->sid!==""){
                           $qsid="and a.store_id=$this->sid"; 
                       }else{
                           $qsid="and a.store_id=-1";
                       }
                                $objsdetails = $db->fetchObjectArray("select a.*,s.store_name from it_auditdetails a, it_codes s  where a.store_id= s.id $qsid order by a.id desc ");
                               
                                $max=0; $i= sizeof($objsdetails);
                                if($i==0 && $this->sid > 1){ ?><h6 span class="error" style="color:white;"> No Store Audit Report Available For Selected Store </h6></span><br><div align='right'class="grid_4"><button onclick = "addstoreaudit1()">Create Store Audit Report</button><br><br></div><?php }
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
                                            <td><div class="tooltip">Show Remark<span class="tooltiptext"><?php echo $obj->remark; ?></span></div></td> 
                                                  
                                                                                                  
                                            <?php if($max==0){?><td><a href='addstoreaudit/aid=<?php echo $obj->id;?>/sid=<?php echo $obj->store_id;?>'>View & Edit</a></td> <?php $max++; }
                                             elseif($max > 0 && $max <=2){?><td><a href='addstoreaudit/aid=<?php echo $obj->id;?>/sid=<?php echo $obj->store_id;?>/view=<?php echo 1;?>'>View</a></td> <?php $max++; }else{?> <td></td> <?php }?>
                                            
                                            
                                            
                        </tr>
                        <?php $i--; }?></table></div>
</fieldset> </div>
        

                        <?php
                      
                    }

                }
                ?>
