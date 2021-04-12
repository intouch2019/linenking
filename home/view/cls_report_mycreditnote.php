<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "lib/core/Constants.php";
require_once "lib/core/strutil.php";
require_once "session_check.php";

class cls_report_mycreditnote extends cls_renderer {
    var $currStore;
    var $params;
    var $startdated;
    var $enddate;
    var $yeartpess;
    var $id;
    function __construct($params=null) {
        $this->currStore = getCurrUser();
        $this->params = $params;
        
                if($params && isset($params['startdated'])){
                    $this->startdated = $params['startdated'];
                }
                 if($params && isset($params['enddate'])){
                    $this->enddate = $params['enddate'];
                }
                if($params && isset($params['yeartpes'])){
                    $this->yeartpess = $params['yeartpes'];
                }
 
         if (isset($_SESSION['id'])) {
             $this->id = $_SESSION['id']; 
             
         }
               else { $this->id = "3"; 
               
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
<script type="text/javascript" src="jqueryui/js/jquery-ui-1.7.1.custom.min.js"></script>
<script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
<link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
<link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />

<script type="text/javascript" src="js/ajax.js"></script>
<style type="text/css" title="currentStyle">
     @import "js/datatables/media/css/demo_page.css";
     @import "js/datatables/media/css/demo_table.css";
     @import "css/redmond/jquery-ui-1.7.1.custom.css";
</style>

<script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
<script src="js/datatables/media/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="js/chosen/chosen.css" />
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />

<script type="text/javascript">

$(function(){

                    $("#cn").change(function () {
                        var id= $("#cn").val();
                        
                        $.ajax({
                                url: "savesession.php?name=id&value="+id,
                                success: function(data) {
                                        //window.location.reload();
                                         window.location.href = "report/mycreditnote";
                                }
                        });

                    });
                    $("#cn1").change(function () {
                        var id= $("#cn1").val();

                        $.ajax({
                                url: "savesession.php?name=id&value="+id,
                                success: function(data) {
                                        
                                        //window.location.reload();
                                         window.location.href = "report/mycreditnote";
                                }
                        });
                    });
        });
        
        
function setyear(){
    
     var yr= $( "#yrtype" ).val();
     var yrtypes=$("#yrtype").val();
     if(yr!='fd'){
                 $yearsssss=yr;
                 $yearset=$yearsssss.split("-");
                 $syear=$yearset[0];
                 $eyear=$yearset[1];
                 $fromdate=$syear+"-04-01";
                 $todate=$eyear+"-03-31";
                 window.location.href = "report/mycreditnote/startdated="+$fromdate+"/enddate="+$todate+"/yeartpes="+yrtypes;
      }
      else{
           document.getElementById('yearlabel').style.display = 'inline';
      }
}

function yrlablehide(){
     document.getElementById('yearlabel').style.display = 'none';
    
}

</script>
    <?php
    }
  

    public function pageContent() {
        $menuitem = "mycredinote";
        include "sidemenu.".$this->currStore->usertype.".php";
        ?>

<div class="grid_10">
            <?php
            
            
           $db = new DBConn();
           $store_id = getCurrUserId();

           if (date('m') > 3) {
           $current_financial_year = date('Y') . '-' . (date('Y')+1);    /// current finacial year calculation start
            // echo 'current fy'.$current_financial_year;
           $prev_financial_year = (date('Y')-1) . '-' . date('Y');   // Previous  finacial year calculation start
            //echo 'previous fy'.$prev_financial_year;
           } 
           else{
            // echo 'hello';
           $current_financial_year = (date('Y')-1) . '-' . (date('Y'));    /// current finacial year calculation start
            // echo 'current fy'.$current_financial_year;
           $prev_financial_year = (date('Y')-2) . '-' . (date('Y')-1);   // Previous  finacial year calculation start
          //echo 'previous fy'.$prev_financial_year;
           }
           



            ?>
    <div class="box" style="clear:both;">
       <fieldset class="login">
            <legend>My CreditNote</legend>
            <br>
                          <div class="grid_12">  
                               <input type="radio" id="cn" name="cn" value="1" <?php if ($this->id == 1) { ?>checked <?php } ?>>Turnover And Discount Scheme Credit note  &nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;  &nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;  &nbsp; 
<input type="radio" id="cn1" name="cn" value="2" <?php if ($this->id == 2) { ?>checked <?php } ?>>Defective Garment Credit Note
            </div>
            <div class="grid_12"> 
            <div class="grid_6">  <b>Select Year:</b>
            <br/>
            <select name="yrtype" id="yrtype" onchange="yrlablehide()">
            <?php
            $atypes = array(
            "fd"=>"Select Year",
            "$current_financial_year"=>"$current_financial_year",
            "$prev_financial_year"=>"$prev_financial_year");
            $form_atype=$this->getFieldValue('yrtype');
            foreach ($atypes as $avalue => $yrtype) {
                    $selected="";
                    if ($avalue == $this->yeartpess) { $selected = "selected"; }
            ?>
            <option value="<?php echo $avalue; ?>" <?php echo $selected; ?> ><?php echo $yrtype; ?></option>
            <?php } ?>
            </select>
                   
            <div class="grid_12"><label><h5 style="color:#FF0000;" ><span id="yearlabel" style="display:none;"  >Please Select Year</span></h5> </label>     

            </div>
            </div > 
            
            </div>
            <div class="grid_12" style="padding:10px;" >
            <input type="submit" name="save" id="addattr" value="Show Creditnote" onclick="setyear();"/>
            <br/>
            <br/>
            </div>
            <br/>
            <br/>
<?php 
if($this->startdated!=null && $this->enddate!=null && $this->id==1){?>
<?php 
      $db = new DBConn();
      $cn_number = array();
      //echo "select cn_no from it_creditnote_ds where store_id=$store_id and date(createtime) >='$this->startdated'  and date(createtime) <= '$this->enddate' order by createtime desc";
//     print_r($cn_number);
     //$current_dscn=$db->fetchObjectArray("select cn_no from it_creditnote_ds where store_id=$store_id and createtime between '$this->startdated' and '$this->enddate' order by createtime desc");
          $current_dscn=$db->fetchObjectArray("select cn_no from it_creditnote_ds where store_id=$store_id and date(createtime) >='$this->startdated'  and date(createtime) <= '$this->enddate' order by createtime desc");
     
      foreach ($current_dscn as $dscn_no) {
          array_push($cn_number,"$dscn_no->cn_no");
      }
//      print_r($cn_number);
      //$current_tdcn = $db->fetchObjectArray("select cn_no from  it_creditnote_td where store_id=$store_id  and createtime between '$this->startdated' and '$this->enddate' order by createtime desc");
      $current_tdcn = $db->fetchObjectArray("select cn_no from  it_creditnote_td where store_id=$store_id  and to_datetime >= '$this->startdated' and date(to_datetime) <= '$this->enddate' order by createtime desc");

//     echo "select * from  it_creditnote_td where store_id=$store_id  and createtime between '$startdated' and '$enddate' order by createtime desc";
      foreach ($current_tdcn as $tdcn_no) {
          array_push($cn_number,"$tdcn_no->cn_no");
      }
//      print_r($cn_number);
      rsort($cn_number); // sorting array in desc order for ordering creditnote number



    ?>
            <div id="accordion">
                <table>
                            <thead>
                            <tr>
                            <th>Sr no</th>
                            <th>CreditNote.No</th>
                            <th>Generate Date </th>
                            <th>Ammount</th> 
                            <th>Remark</th>  
                            <th>Download CreditNote</th>
                            </tr>
                            </thead>
                         
                      
<?php
    $srno=1;
    foreach ($cn_number as $key => $value) { ?>
                              <?php
    $tdcn_query="select cn_no, createtime,gst_total as ammount,remark from it_creditnote_td where cn_no=$value and store_id=$store_id";
    $tdcn= $db->fetchObject($tdcn_query);
    $dscn_query="select cn_no, createtime, taxable_amt as ammount,ds_remark from it_creditnote_ds where cn_no=$value and store_id=$store_id";
    $dscn= $db->fetchObject($dscn_query);
         if(isset($tdcn)){?>
                              
                                <tr>
                                <td><?php echo $srno; ?></td>
                                <td><?php echo 'CN-'.$tdcn->cn_no; ?></td>
                                <td><?php echo mmddyy($tdcn->to_datetime); ?></td>
                                <td><?php echo $tdcn->ammount; ?></td>
                                <td><?php echo $tdcn->remark; ?></td>
                                <td><a href="formpost/storewiseTdCn.php?cn_no=<?php echo $tdcn->cn_no;?>"><button>Download</button></a></td>
                                </tr>
        
        
        <?php }?>
        <?php if(isset($dscn)){?>
                              
                                <tr>
                                <td><?php echo $srno; ?></td>
                                <td><?php echo 'CN-'.$dscn->cn_no; ?></td>
                                <td><?php echo mmddyy($dscn->createtime); ?></td>
                                <td><?php echo $dscn->ammount; ?></td>
                                <td><?php echo $dscn->ds_remark; ?></td>
                                <td><a href="formpost/storewiseDsCn.php?cn_no=<?php echo $dscn->cn_no;?>"><button>Download</button></a></td>
                                </tr>
        
        
       <?php }?>                
    
<?php
 $srno++;
} 
unset($cn_number);

?>
                </table>
            </div>

<?php }  elseif ($this->startdated != null && $this->enddate != null && $this->id==2) {?>
            
            <?php
            $db = new DBConn();
            $dcn_number = array();

            $def_cn = $db->fetchObjectArray("select invoice_no from it_portalinv_creditnote where store_id=$store_id and createtime >='$this->startdated 00:00:00'  and createtime <= '$this->enddate 23:59:59'  order by createtime desc");
//print_r($def_cn);
            if(isset($def_cn)){
            foreach ($def_cn as $d_no) {
                array_push($dcn_number, "$d_no->invoice_no");
            }
            rsort($dcn_number);
            
            
            ?>
                    <div id="accordion">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Sr no</th>
                                        <th>CreditNote.No</th>
                                        <th>Generate Date </th>
                                        <th>Amount</th> 
                                        <!--<th>Remark</th>-->  
                                        <th>Download Defected </br>Garment CreditNote</th>
                                    </tr>
                                </thead>


            <?php
            $srno2 = 1;
            foreach ($dcn_number as $key => $value) {
                ?>
                <?php
                $defectedgarment_query = "select * from it_portalinv_creditnote where invoice_no='$value' and store_id=$store_id";
//               print $defectedgarment_query;
               
                $dfcn = $db->fetchObject($defectedgarment_query);
                $totalval=0;
                if($dfcn->igst_total!=0){
                $totalval=$dfcn->rate_subtotal+$dfcn->cgst_total+$dfcn->cgst_total;
                }else{
                      $totalval=$dfcn->rate_subtotal+$dfcn->igst_total;
                }
                if (isset($dfcn)) {
                    ?>

                                        <tr>
                                            <td><?php echo $srno2; ?></td>
                                            <td><?php echo  $dfcn-> invoice_no; ?></td>
                                            <td><?php echo mmddyy($dfcn->createtime); ?></td>
                                            <td><?php echo $totalval; ?></td>
                                        
                                            <td><a href="formpost/generateStoreDGpdfs.php?invid=<?php echo $dfcn->id; ?>"><button>Download</button></a></td>
                                        </tr>


                <?php } ?>
                              

                <?php
                $srno2++;
            }
            unset($dcn_number);
            ?>
                            </table>
                        </div>
                    
                    
                    
                    
                    
            
            
                       <?php
                            }
    }
                            
               ?>
    </div>
  </fieldset>
</div>
    <?php
    }
}
?>
