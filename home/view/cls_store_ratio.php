<?php
require_once "view/cls_renderer.php";
require_once "lib/db/DBConn.php";
require_once "session_check.php";
require_once "lib/core/strutil.php";
require_once "lib/core/Constants.php";

class cls_store_ratio extends cls_renderer {

    var $params;
    var $result;
    var $param_design_no;
    var $currUser;
    var $cat;
    var $sid = "";
    var $design_id = "";
    var $rtype = "";
    var $des_code="";

    function __construct($params = null) {
        //parent::__construct(array(UserType::Admin, UserType::CKAdmin, UserType::Dispatcher, UserType::Manager));
        $this->params = $params;
        $this->currUser = getCurrUser();
        if (isset($params['dno']))
            $this->param_design_no = $params['dno'];
        if (isset($_SESSION['design_dtrange'])) {
            $this->dtrange = $_SESSION['design_dtrange'];
        } else {
            $this->dtrange = date("d-m-Y");
        }

        if ($params && isset($params['cat'])) {
            $this->cat = $params['cat'];
        }
        if ($params && isset($params['sid'])) {
            $this->sid = $params['sid'];
            }
        if ($params && isset($params['design_id'])) {
            $this->design_id = $params['design_id'];
        }
        if ($params && isset($params['rtype'])) {
            $this->rtype = $params['rtype'];
        }
        if ($params && isset($params['dno']))
            $this->des_code = $params['dno'];
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
        <script type="text/javascript" src="js/daterangepicker.jQuery.js"></script>
        <link rel="stylesheet" href="css/ui.daterangepicker.css" type="text/css" />
        <link rel="stylesheet" href="css/redmond/jquery-ui-1.7.1.custom.css" type="text/css" title="ui-theme" />
        <script type="text/javascript" src="js/expand.js"></script>
        <link rel="stylesheet" type="text/css" href="css/dark-glass/sidebar.css" />
        <script type="text/javascript" src="js/sidebar/jquery.sidebar.js"></script>
        <link rel="stylesheet" href="css/prettyPhoto.css" type="text/css" media="screen" title="prettyPhoto main stylesheet" charset="utf-8" />
        <script src="js/prettyPhoto/jquery.prettyPhoto.js" type="text/javascript" charset="utf-8"></script>
        <link rel="stylesheet" href="js/chosen/chosen.css" />
        <script type="text/javascript">
            <!--//--><![CDATA[//><!--
            $(function () {
                $('#dateselect').datepicker({
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
                        $.ajax({
                            url: "savesession.php?name=design_dtrange&value=" + dtrange,
                            success: function (data) {
                            }
                        });
                    }
                });

                $("h2.expand").toggler({method: "slideFadeToggle"});
                $("#content").expandAll({trigger: "h2.expand"});
            });

            $(function () {
                $("a[rel^='prettyPhoto']").prettyPhoto({animation_speed: 'fast', slideshow: 3000, hideflash: true});
                $("#design_no").keyup(function (event) {
                    if (event.keyCode == 13) {
                        $("#searchBtn").click();
                    }
                });
            });
            //--><!]]>

            function search()
            {
                var dtrange = $("#dateselect").val();
                $.ajax({
                    url: "savesession.php?name=design_dtrange&value=" + dtrange,
                    success: function (data) {
                        var design_no = document.getElementById("design_no").value;
                        window.location.href = "designs/search/dno=" + design_no;
                    }
                });
            }

            function activate(form_id) {
                var formname = eval("order_" + form_id);
                var params = $(formname).serialize();
                var ajaxUrl = "ajax/activateDesign.php?" + params;
                //alert(ajaxUrl);
                $.getJSON(ajaxUrl, function (data) {
                    if (data.error == "0") {
                        $("#status_" + form_id).removeClass().addClass("success");
                    } else {
                        $("#status_" + form_id).removeClass().addClass("error");
                    }
                    $("#status_" + form_id).html(data.message);
                });

                return false;
            }


            function searchcat(cat_id) {
                var ajaxURL = "ajax/getDesignNumbers.php?cat_id=" + cat_id;
                //alert(ajaxURL);
                $.ajax({
                    url: ajaxURL,
                    dataType: 'json',
                    success: function (data) {
                        if (data.error == "1") {
                            alert(data.message);
                            $("#sel_des").empty();
                            $("#sel_des").append(" <option value=" + 0 + ">Select Design number</option> ");
                            $("#sel_des").append(" <option value=" + -1 + ">All Designs</option> ");
                        } else {
                            $("#sel_des").empty();
                            $("#sel_des").append(" <option value=" + 0 + ">Select Design number</option> ");
                            $("#sel_des").append(" <option value=" + -1 + ">All Designs</option> ");
                            //alert(data.message);
                            var design_nos = data.message;
                            for (var i = 0; i < design_nos.length; i++) {
                                var a1 = design_nos[i].split('<>', 2);

                                $("#sel_des").append("<option value=" + a1[0] + ">" + a1[1] + "</option>");
                            }
                        }
                    }
                });
            }

            function searchstore(store_id) {
//                alert(store_id);
                //document.getElementById("sel_cat").selectedIndex = 0;
                //  document.getElementById("sel_des").selectedIndex = 0;
                document.getElementById("sel_ratio_type").selectedIndex = 0;
                //window.location.href="store/ratio";
                $("#expand_collapse").hide();
                
               // $("#dwnCSV").style.visibility=visible;
               if(store_id>0){
                document.getElementById("dwnCSV").style.visibility = "visible";
               }else{
                document.getElementById("dwnCSV").style.visibility = "hidden";   
               }
            }

            function searchdesign(des_id) {
                document.getElementById("sel_ratio_type").selectedIndex = 0;
            }
            
            function ctgwise(ctg_id){
                 <?php if ($this->currUser->usertype == UserType::Dealer) { ?>
                    var store_id = '<?php echo $this->currUser->id; ?>';
        <?php } else { ?>
                    var store_id = $("#sel_store").val();
        <?php  } ?>
               // var cat_id = $("#sel_cat").val();
                //alert(cat_id);
                var ratio_type = $("#sel_ratio_type").val();
                if(store_id == 0){
                   alert("Please select store");
                }else if(ratio_type == 0){
                    alert("Please select Ratio Type");
                }else{
                //window.location.href = "store/ratio/sid=" + store_id + "/cat=" + cat_id;
                //var des_id = $("#sel_des").val();
                //window.location.href = "store/ratio/sid=" + store_id + "/cat=" + cat_id + "/design_id=" + des_id + "/rtype=" + ratio_type;
                window.location.href = "store/ratio/sid=" + store_id + "/rtype=" + ratio_type +"/cat=" + ctg_id;
               }
            }
            
            function designwise(){
                var designnos = $("#designos").val();
                $("#designids").val(designnos);
                //alert($("#design_ids").val());
            }
            
            function searchratiotype(ratio_type) {
        <?php if ($this->currUser->usertype == UserType::Dealer) { ?>
                    var store_id = '<?php echo $this->currUser->id; ?>';
        <?php } else { ?>
                    var store_id = $("#sel_store").val();
        <?php } ?>
                var cat_id = $("#sel_cat").val();
                if(store_id == 0){
                   alert("Please select store")     ;
                   $("#sel_ratio_type").val("0");
                }/*else if(cat_id == 0){
                    alert("Please select category");
                    $("#sel_ratio_type").val("0");
                }*/else{
                //var des_id = $("#sel_des").val();
                //window.location.href = "store/ratio/sid=" + store_id + "/cat=" + cat_id + "/design_id=" + des_id + "/rtype=" + ratio_type;
                 window.location.href = "store/ratio/sid=" + store_id + "/rtype=" + ratio_type;
                }
            }
            
            
            
function designratioreset(store_id,cat_id,design_id,design_no,user_id)
{
    var reset=confirm("Are you sure want to reset all ratios to 1 of design no '"+design_no+"'");
    
   if(reset==true)
   {
    var ratio_type = $("#sel_ratio_type").val();
//  
               $.ajax({
			type: "POST",
                        dataType: "json",
			url: "ajax/setDesignratioDefault.php",
			data: "sid="+store_id+"&cat_id="+cat_id+"&design_id="+design_id+"&r_type="+ratio_type+"&user_id="+user_id+"&design_no="+design_no,
                        success:function(data)
                        {
                            if(data.error==0)
                          {
                            alert(data.message);
//                            alert("success");
                          
                               window.location.href = "store/ratio/sid=" + store_id + "/rtype=" + ratio_type +"/cat=" + cat_id;
                           }  
                          
                        }
                            
                            
                        
                                
		});
            }
//    alert("done");
    
}


function masteratioreset(store_id,cat_id,cat_name,user_id)
{
    var reset=confirm("Are you sure want to reset all ratios to 1 of category '"+cat_name+"'");
    
   if(reset==true)
   {
           var ratio_type = $("#sel_ratio_type").val();
            window.location.href="formpost/setMasterRatio.php?sid="+store_id+"&cat_id="+cat_id+"&r_type="+ratio_type+"&user_id="+user_id+"&cat_name="+cat_name;
           
   
//  
//               $.ajax({
//			type: "POST",
//                        dataType: "json",
//			url: "ajax/setMasterRatio.php",
//			data: "sid="+store_id+"&cat_id="+cat_id+"&r_type="+ratio_type+"&user_id="+user_id+"&cat_name="+cat_name,
//                        success:function(data)
//                        {
//                            if(data.error==0)
//                          {
//                            alert(data.message);
////                            alert("success");
//                          
//                               window.location.href = "store/ratio/sid=" + store_id + "/rtype=" + ratio_type +"/cat=" + cat_id;
//                           }  
//                          
//                        }
//                            
//                            
//                        
//                                
//		});
            }
    
}


     function editDesignRatio(theForm) {
                var formName = theForm.name;
                var arr = formName.split("_");
                var form_id = arr[1];
                var params = $(theForm).serialize();
//                alert(params);
                var ajaxUrl = "ajax/editDesignRatio.php?" + params;
                $.getJSON(ajaxUrl, function (data) {
//                    alert(data.message);
                    if (data.error == "0") {
                        $("#status_" + form_id).removeClass().addClass("success");
                    } else {
                        $("#status_" + form_id).removeClass().addClass("error");
                    }
                    $("#status_" + form_id).html(data.message);
                    
                });

                return false;
            }
            
            function fetchRatiosCsv(){ // for users other than dealer
               var storeid = $("#sel_store").val();
               window.location.href = "formpost/getRatioDetailsCSV.php?store_id="+storeid;
//               alert(storeid);
            }
            
            function fetchDistRatiosCsv(storeid){ // for users of type dealer
               window.location.href = "formpost/getRatioDetailsCSV.php?store_id="+storeid; 
            }
        </script>
        <?php
    }

    public function pageContent() {
        $formResult = $this->getFormResult();
        $menuitem = "sbratio";
        include "sidemenu." . $this->currUser->usertype . ".php";
        $usertype = $this->currUser->usertype;
        $db = new DBConn();
        $designarr = array();
        if(isset($this->des_code) && trim($this->des_code)!=""){
         $designarr = explode(",",$this->des_code);
        }
            
        
        
        
        $allDesigns = array();
        
        ?>

        <div class="grid_10">

            <div class="grid_3">&nbsp;
                <p id="dwnCSV" name="dwnCSV" style="visibility:hidden">
                <?php if($this->currUser->usertype != UserType::Dealer){ ?>
                <input class="blueglassbutton" type = "button" name="rDwn" id="rDwn" value="Download Ratios Csv" onclick="javascript:fetchRatiosCsv();"/>
                <?php } ?>
                <p>
                <?php if($this->currUser->usertype == UserType::Dealer){ ?>
                    <input class="blueglassbutton" type = "button" name="rDwn" id="rDwn" value="Download Ratios Csv" onclick="javascript:fetchDistRatiosCsv(<?php echo $this->currUser->id ; ?>);"/>
                <?php } ?>   
                    
                    <p>
                    <?php  if ($this->cat !=null && $this->rtype==RatioType::Base) { 
//            $this->cat = $params['cat']; 
            $query = "select c.name from it_categories c where c.id=$this->cat ";
                $obj = $db->fetchObject($query);
                if (isset($obj)) {
                    $cat_name = $obj->name;
                }
            
            ?>
                    <button class="blueglassbutton" onclick="masteratioreset(<?php echo "'".$this->sid."','".$this->cat."','".$cat_name."','".$this->currUser->id."'";?>)"> Master Reset</button>
                              
      <?php  } ?>
                    
            </div>
            <div class="grid_5">
                <!--<fieldset class="login">-->
                    <legend>Standing / Base Ratio</legend>
                    <table>
                        <tr>
                        <td colspan="5">Select store:</td>
                        <td colspan="5">
                        <?php if ($this->currUser->usertype != UserType::Dealer) { ?>
                            <select id="sel_store" name="sel_store" data-placeholder="Search Store" class="chzn-select" multiple style="width:100%" onchange="searchstore(this.value);">
                                <option value="0">Select Store</option> 
                                <?php
                                $objs = $db->fetchObjectArray("select * from it_codes where usertype=" . UserType::Dealer . " and inactive=0  and is_closed=0 order by store_name");

                                $sids = split(',', $this->sid);
                                foreach ($objs as $obj) {
//                                    if ($this->sid == $obj->id) {
                                    if (in_array($obj->id, $sids)){
                                        $sel = 'selected';
                                    } else {
                                        $sel = '';
                                    }
                                    ?>
                                    <option value="<?php echo $obj->id; ?>" <?php echo $sel; ?>><?php echo $obj->store_name; ?></option> 
                                <?php } ?>
                            </select>
                        <?php } else { ?>
                            <input type="text" id="sel_store" name="sel_store" value="<?php echo $this->currUser->store_name; ?>" readonly>
                        <?php }
                        ?>
                            </td>
                    </tr>
                    <tr>
                        <td colspan="5">Select Ratio type:</td>
                        <td colspan="5">
                        <select id="sel_ratio_type" name="sel_ratio_type" data-placeholder="Search Ratio Type" style="width:100%" onchange="searchratiotype(this.value);">
                            <option value="0">Select Ratio Type</option> 
                            <?php
                            $objs = RatioType::getALL();
                            foreach ($objs as $id => $name) {
                                if ($this->rtype == $id) {
                                    $sel = "selected";
                                } else {
                                    $sel = "";
                                }
                                ?>
                                <option value="<?php echo $id; ?>" <?php echo $sel; ?>><?php echo $name; ?></option> 
                            <?php } ?>
                        </select>
                        </td>    
                    </tr>   
                    
                    <tr>
                        <td colspan="5">
                        Select category:
                        </td>
                        <td colspan="5">
                        <select id="sel_cat" name="sel_cat" data-placeholder="Search Category" style="width:100%" onchange="ctgwise(this.value);"> <!--searchcat(this.value);-->
                            <option value="0">Select Category</option> 
                            <?php
                            $objs = $db->fetchObjectArray("select * from it_categories where active=1 order by name");
                            foreach ($objs as $obj) {
                                if ($this->cat == $obj->id) {
                                    $sel = 'selected';
                                } else {
                                    $sel = '';
                                }
                                ?>
                                <option value="<?php echo $obj->id; ?>" <?php echo $sel; ?>><?php echo $obj->name; ?></option> 
                            <?php } ?>
                        </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <td colspan="5">Select Design:</td>
                        <td colspan="5">
                            <select id="designos" name="designnos" data-placeholder="All" style="width:75%" class="chzn-select" multiple onchange="designwise(this.value);"> 
                            <option value="-1">All</option>
                            <?php
                             if($this->rtype == RatioType::Base || $this->rtype == RatioType::Standing){ // show designs only for base ratio type
                                // $query="select d.id as design_id,d.design_no,c.name,c.id from it_ck_designs d , it_categories c where d.ctg_id = c.id  and image is not null"; //and d.active = 1
                               $query = "select id as design_id,design_no from it_ck_designs where ctg_id = ".$this->cat." order by design_no";
                               //print $query;
                               $objs = $db->fetchObjectArray($query);
                               foreach($objs as $obj){
                                   $selected = "";
                                   if(!empty($designarr)){
                                       if(in_array($obj->design_id, $designarr)){ 
                                           $selected = "selected";}
                                           else{ $selected = ""; }
                                   }
                                   $option_value = $obj->design_no;
                            ?>
                            <option value="<?php echo $obj->design_id; ?>" <?php echo $selected; ?>><?php echo $option_value; ?></option>
    <?php }} ?>
                        </select>
                            
                        </td>    
                    </tr>
                    
<!--                    <tr>
                        <td colspan="10"><b>NOTE: Standing/Base ratio setting against 'All Designs' option TAKES 2-3 Minutes to complete.<br />Please Wait for it to do so.<br />Donot Hit the Browser Refresh or any other Buttons</b></td>
                    </tr>-->
                    <tr>
                        <td colspan="10">
                          <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                        </td>
                    </tr>

                    <?php // if ($this->param_design_no != "" && count($allDesigns) == 0) { ?>
                        <!--<p class="error">No Designs found matching "//<?php // echo $this->param_design_no; ?>"</p>-->	
                    <?php // } ?>
                        </table>
                <!--</fieldset>-->
            </div>
            
            <?php
            if ($this->cat != "" && $this->cat != 0  && $this->sid != "" && $this->sid != 0  && $this->rtype == RatioType::Base) { //&& $this->design_id != "" && $this->design_id != 0
                //$query = "select c.id as ctg_id,c.name as category,c.active,d.design_no,d.image,i.mrp,d.lineno,d.rackno from it_categories c,it_ck_designs d,it_items i where c.id=d.ctg_id and d.id=i.design_id and c.id=i.ctg_id and c.id=$this->cat and d.id=$this->design_id group by d.design_no";
                $query = "select c.* from it_categories c where c.id=$this->cat ";
                $obj = $db->fetchObject($query);
                if (isset($obj)) {
                    $cat_name = $obj->name;
                    $state = $obj->active ? "ACTIVE" : "INACTIVE";
                    $state_str = "[ ".$state."]";
                }
                ?>
                <div class="clear"></div>
             <div id="expand_collapse">
                    <div class="box">
                        
                        <h2 class="expand"><?php echo $cat_name; ?> <?php echo $state_str; ?> &nbsp;&nbsp; (Set Selected Design's ratio)</h2> <!--Design No: <?php // echo $design_no.$mrp_str ; ?>-->
                        <div class="collapse" > 
                            
                            <?php // if (isset($obj->image)) { ?>
                                <!--<a href="<?php // echo $this->designImageUrl($obj->image); ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="<?php // echo $this->designImageUrl($obj->image); ?>" width="170" /></a>-->
                            <?php // } else { ?>
                                <!--<a href="" rel="prettyPhoto"><img class="grid_2" align="left" src="<?php // echo CdnUrl('images/stock/nophoto.jpeg'); ?>" width="170" /></a>-->
                            <?php // } ?>

                            <div class="block grid_10">
                                <form method="post" action="formpost/addRatioDetails.php">
                                    <input type="hidden" id="ctgid" name="category" value="<?php echo $this->cat; ?>" />
                                    <!--<input type="hidden" id="desno" name="designid" value="<?php // echo $this->design_id; ?>" />-->
                                    <?php if ($this->currUser->usertype == UserType::Dealer) { ?>
                                        <input type="hidden" id="sid" name="sid" value="<?php echo $this->currUser->id; ?>" />
                                    <?php } else { ?>
                                        <input type="hidden" id="sid" name="sid" value="<?php echo $this->sid; ?>" />
                                    <?php } ?>
                                    <input type="hidden" id="rtype" name="rtype" value="<?php echo $this->rtype; ?>" />
                                    <input type="hidden" id="userid" name="userid" value="<?php echo $this->currUser->id; ?>" />
                                    <!--<input type="hidden" id="mrp" name="mrp" value="<?php //echo $mrp; ?>" />-->
                                    <input type="hidden" id="designids" name="designids" value="-1"/>
                                    <table>
                                        <?php                                        
                                        $styleobj = $db->fetchObjectArray("select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.ctg_id=$this->cat and s1.style_id=s2.id  and s2.is_active = 1 order by s1.sequence");
                                        $no_styles = count($styleobj);
    //                                    print_r($styleobj);
                                        $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.ctg_id=$this->cat and s1.size_id=s2.id order by s1.sequence");
    //                                    print_r($sizeobj);
                                        $no_sizes = count($sizeobj);
                                        if ($this->currUser->usertype == UserType::Dealer) {                                            
                                            $storeid = $this->currUser->id;                                            
                                        } else {
                                             $storeid = $this->sid;                                           
                                        }
                                        ?>
                                        <thead>
                                            <tr><th></th>
                                                <?php
                                                $width = intval(100 / ($no_sizes + 1));
                                                for ($i = 0; $i < $no_sizes; $i++) {
                                                    print '<th style="text-align:left;" width="' . $width . '%">';
                                                    echo $sizeobj[$i]->size_name;
                                                    print "</th>";  //print sizes
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php                                            
                                            //for each unique style print style
                                            for ($k = 0; $k < $no_styles; $k++) {
                                                //print style names
                                                print "<tr><th>";
                                                echo $styleobj[$k]->style_name;
                                                print"</th>";
                                                $stylcod = $styleobj[$k]->style_id;
                                                 
                                                for ($i = 0; $i < $no_sizes; $i++) {
                                                    $sizeid = $sizeobj[$i]->size_id;
                                                    
//                                                    if($this->design_id == "-1"){
//                                                      $query = "select * from it_store_ratios where store_id=$storeid and ctg_id=$this->cat  and ratio_type=$this->rtype and style_id = $stylcod and size_id = $sizeid ";  
//                                                    }else{
                                                     //$query = "select * from it_store_ratios where store_id=$storeid and ctg_id=$this->cat and design_id=$this->design_id and ratio_type=$this->rtype and style_id = $stylcod and size_id = $sizeid and mrp = $mrp ";
                                                      $query = "select * from it_store_ratios where store_id=$storeid and ctg_id=$this->cat and ratio_type=$this->rtype and style_id = $stylcod and size_id = $sizeid ";
//                                                    }
//                                                    print "<br>".$query."<br>";
                                                      
                                                      
                                                    $getratio = $db->fetchObject($query);
                                                    ?><td>
                                                        <input type="text" id="<?php echo $styleobj[$k]->style_id . "_" . $sizeobj[$i]->size_id; ?>" name="<?php echo $styleobj[$k]->style_id . "_" . $sizeobj[$i]->size_id; ?>" style="width:40%" value="<?php if(isset($getratio)){ echo $getratio->ratio; } else {echo '1';}  ?>" >
                                                    </td><?php
                                                }
                                            }
                                            print "</tr>";                                        
                                        ?>
                                        </tbody>
                                    </table><Br>
                                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                                    <input class="blueglassbutton" type="submit" value="Save" style="float:right;">
                                </form>
                            </div> <!-- end class=grid_10 --><div class="clear"></div>
                        </div> <!-- end class="block" -->
                    </div> <!-- end class="box" -->
                </div>
                <div class="clear"></div>
               
        <!------------------------------All without exception start here------------------------------->
            <?php if(count($sids) == 1){?>    
                 <div class="clear"></div>
             <div id="expand_collapse">
                    <div class="box">
                       <h2 class="expand"> All without exception list</h2>  <!--Design No: <?php // echo $design_no.$mrp_str ; ?>-->
                        <div class="collapse" >
                            <?php // if (isset($obj->image)) { ?>
                                <!--<a href="<?php // echo $this->designImageUrl($obj->image); ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="<?php // echo $this->designImageUrl($obj->image); ?>" width="170" /></a>-->
                            <?php // } else { ?>
                                <!--<a href="" rel="prettyPhoto"><img class="grid_2" align="left" src="<?php // echo CdnUrl('images/stock/nophoto.jpeg'); ?>" width="170" /></a>-->
                            <?php // } ?>

                            <div class="block grid_10">
                              <!--  <form method="post" action="formpost/addRatioDetails.php">-->
                                    <input type="hidden" id="ctgid" name="category" value="<?php echo $this->cat; ?>" />
                                    <!--<input type="hidden" id="desno" name="designid" value="<?php // echo $this->design_id; ?>" />-->
                                    <?php if ($this->currUser->usertype == UserType::Dealer) { ?>
                                        <input type="hidden" id="sid" name="sid" value="<?php echo $this->currUser->id; ?>" />
                                    <?php } else { ?>
                                        <input type="hidden" id="sid" name="sid" value="<?php echo $this->sid; ?>" />
                                    <?php } ?>
                                    <input type="hidden" id="rtype" name="rtype" value="<?php echo $this->rtype; ?>" />
                                    <input type="hidden" id="userid" name="userid" value="<?php echo $this->currUser->id; ?>" />
                                    <!--<input type="hidden" id="mrp" name="mrp" value="<?php //echo $mrp; ?>" />-->
                                    <input type="hidden" id="designids" name="designids" value="-1"/>
                                    <table>
                                        <?php                                        
                                        $styleobj = $db->fetchObjectArray("select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.ctg_id=$this->cat and s1.style_id=s2.id  and s2.is_active = 1 order by s1.sequence");
                                        $no_styles = count($styleobj);
    
                                        $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.ctg_id=$this->cat and s1.size_id=s2.id order by s1.sequence");
   
                                        $no_sizes = count($sizeobj);
                                        if ($this->currUser->usertype == UserType::Dealer) {                                            
                                            $storeid = $this->currUser->id;                                            
                                        } else {
                                             $storeid = $this->sid;                                           
                                        }
                                        ?>
                                        <thead>
                                            <tr><th></th>
                                                <?php
                                                $width = intval(100 / ($no_sizes + 1));
                                                for ($i = 0; $i < $no_sizes; $i++) {
                                                    print '<th style="text-align:left;" width="' . $width . '%">';
                                                    echo $sizeobj[$i]->size_name;
                                                    //print_r(size_name);
                                                    print "</th>";  //print sizes
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php                                            
                                            //for each unique style print style
                                            for ($k = 0; $k < $no_styles; $k++) {
                                                //print style names
                                                print "<tr><th>";
                                                echo $styleobj[$k]->style_name;
                                                print"</th>";
                                                $stylcod = $styleobj[$k]->style_id;
                                                 
                                                for ($i = 0; $i < $no_sizes; $i++) {
                                                    $sizeid = $sizeobj[$i]->size_id;
                                                    
                                                    $query = "select * from it_store_ratios where store_id=$storeid and ctg_id=$this->cat and design_id = -1 and ratio_type=$this->rtype and style_id = $stylcod and size_id = $sizeid ";
                                                    $getratio = $db->fetchObject($query);
                                                    //print_r($query);
                                                    ?><td>
                                                        <input type="text" id="<?php echo $styleobj[$k]->style_id . "_" . $sizeobj[$i]->size_id; ?>" name="<?php echo $styleobj[$k]->style_id . "_" . $sizeobj[$i]->size_id; ?>" style="width:40%" value="<?php if(isset($getratio)){ echo $getratio->ratio; }?>" readonly>
                                                    </td><?php
                                                }
                                            }
                                            print "</tr>";                                        
                                        ?>
                                        </tbody>
                                    </table><Br>
                                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                                    <!--<input type="submit" value="Save" style="float:right;">
                                </form>-->
                            </div> <!-- end class=grid_10 --><div class="clear"></div>
                        </div> <!-- end class="block" -->
                    </div> <!-- end class="box" -->
                </div>
                <div class="clear"></div>
          <!------------------------All without Exception end here----------------------------------->
                   <?php 
                      $row_no = 1;
                     $dquery = "select s.id,s.store_id,s.ctg_id,ctg.name as ctgname,ctg.active,s.design_id,c.design_no,s.style_id,s.size_id,s.mrp,s.ratio_type,s.ratio,s.is_exceptional,s.is_exceptional_active,s.createtime,c.image from it_store_ratios s , it_ck_designs c , it_categories ctg where c.id = s.design_id and  s.store_id = $this->sid and  s.ctg_id = $this->cat and s.ratio_type = $this->rtype group by s.ctg_id,s.design_id";
                     $dObjs = $db->fetchObjectArray($dquery);
                     
                     foreach($dObjs as $dobj){ 
                               if(isset($dobj) && !empty($dobj) && $dobj != null){
                                   
                                   $setratio=1;
                                   
                                   $sr_query="select ratio from it_store_ratios where store_id = $this->sid and ctg_id = $this->cat and "
                                . "ratio_type = ".RatioType::Base." and "
                                . "design_id = $dobj->design_id";
                                   $srObjs = $db->fetchObjectArray($sr_query);
                     
                     foreach($srObjs as $sobj){ 
                         if($sobj->ratio !=1){
                            $setratio=0; 
                         }
                     }
                                   if($setratio==0){
                                   
                                   
                                   $row_no++;
                                   $divid = "accordion-" . $row_no;
//                                   $cat_name = $dobj->ctgname;
//                                   $state = $dobj->active ? "ACTIVE" : "INACTIVE";
//                                   $state_str = "[ ".$state."]";
                        ?>
                        <div class="box">
                        <h2 class="expand">Design No:<?php echo $dobj->design_no; ?></h2> <!--Design No: <?php // echo $design_no.$mrp_str ; ?>-->
                        <div class="collapse" id="<?php echo $divid; ?>" >
                            <?php // if (isset($obj->image)) { ?>
                               <!-- <a href="<?php // echo $this->designImageUrl($obj->image); ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="<?php // echo $this->designImageUrl($obj->image); ?>" width="170" /></a>-->
                            <?php // } else { ?>
                              <!--  <a href="" rel="prettyPhoto"><img class="grid_2" align="left" src="<?php // echo CdnUrl('images/stock/nophoto.jpeg'); ?>" width="170" /></a>-->
                            <?php // } ?>
                           <table><tr><td width="20%">
                            <div class="grid_2" id="imagebackground">
                                <a href="<?php echo $this->designImageUrl($dobj->image); ?>" rel="prettyPhoto"><img id="orderimage" align="left" src="<?php echo $this->designImageUrl($dobj->image); ?>" width="130" /></a>
                            </div></td><td width="80%">
                            <div class="block grid_10">
                                <form method="post" name="sbratio_<?php echo $row_no; ?>" action="" onsubmit="editDesignRatio(this); return false;">
            
                                   <input type="hidden" id="ctgid" name="category" value="<?php echo $this->cat; ?>" />
                                   
                                    <?php if ($this->currUser->usertype == UserType::Dealer) { ?>
                                        <input type="hidden" id="sid" name="sid" value="<?php echo $this->currUser->id; ?>" />
                                    <?php } else { ?>
                                        <input type="hidden" id="sid" name="sid" value="<?php echo $this->sid; ?>" />
                                    <?php } ?>
                                    <input type="hidden" id="rtype" name="rtype" value="<?php echo $this->rtype; ?>" />
                                    <input type="hidden" id="userid" name="userid" value="<?php echo $this->currUser->id; ?>" />
                                     <input type="hidden" id="desno" name="designids" value="<?php echo $dobj->design_id; ?>" />
                                    <table>
                                        <?php                                        
                                        $styleobj = $db->fetchObjectArray("select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.ctg_id=$dobj->ctg_id and s1.style_id=s2.id  and s2.is_active = 1 order by s1.sequence");
                                        $no_styles = count($styleobj);
    //                                    print_r($styleobj);
                                        $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.ctg_id=$dobj->ctg_id and s1.size_id=s2.id order by s1.sequence");
    //                                    print_r($sizeobj);
                                        $no_sizes = count($sizeobj);
                                        if ($this->currUser->usertype == UserType::Dealer) {                                            
                                            $storeid = $this->currUser->id;                                            
                                        } else {
                                             $storeid = $this->sid;                                           
                                        }
                                        ?>
                                        <thead>
                                            <tr><th></th>
                                                <?php
                                                $width = intval(100 / ($no_sizes + 1));
                                                for ($i = 0; $i < $no_sizes; $i++) {
                                                    print '<th style="text-align:left;" width="' . $width . '%">';
                                                    echo $sizeobj[$i]->size_name;
                                                    print "</th>";  //print sizes
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php                                            
                                            //for each unique style print style
                                            for ($k = 0; $k < $no_styles; $k++) {
                                                print "<tr><th>";
                                                echo $styleobj[$k]->style_name;
                                                print"</th>";
                                                $stylcod = $styleobj[$k]->style_id;
                                                 
                                                for ($i = 0; $i < $no_sizes; $i++) {
                                                    $sizeid = $sizeobj[$i]->size_id;
                                                    $query = "select * from it_store_ratios where store_id=$dobj->store_id and ctg_id=$dobj->ctg_id and design_id = $dobj->design_id and ratio_type=$dobj->ratio_type and style_id = $stylcod and size_id = $sizeid ";
                                                    $getratio = $db->fetchObject($query);
                                                   if(isset($getratio)){$gid = $getratio->id; }else{ $gid=0;}
                                                    ?><td>
                                                        <input type="text" id="<?php echo $row_no.$styleobj[$k]->style_id . "_" . $sizeobj[$i]->size_id; ?>" name="<?php echo "item_".$styleobj[$k]->style_id . "_" . $sizeobj[$i]->size_id; ?>" style="width:40%" value="<?php if(isset($getratio)){ echo $getratio->ratio; }   ?>" pattern= "[0-9]+" title="ONLY NUMBER" >
                                                    
                                                    </td><?php
                                                   
                                                }
                                            }
                                            print "</tr>";                                        
                                        ?>
                                        </tbody>
                                    </table><Br>
                                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                                  <!--  <input type="button" value="Save" style="float:right;" onclick="javascript:saveDivSpc">-->
                               <input class="blueglassbutton" type="reset" value="Rset to 1" style="float:right;" onclick="designratioreset(<?php echo "'".$dobj->store_id."','".$dobj->ctg_id."','".$dobj->design_id."','".$dobj->design_no."','".$this->currUser->id."'";?>)">
                                    <input class="blueglassbutton" type="submit" value="Save" style="float:right;">
                                </form>
                            </div> <!-- end class=grid_10--> <div class="clear"></div>   
                             <div id="status_<?php echo $row_no; ?>"></div>
                         </td></tr></table>
                        </div> <!-- end class="collapse"--> 
                    </div><!--  end class="block" -->
                       
            <?php } } } } ?>
                </div>
                <div class="clear"></div>
             
                <?php
            }
            
            
//            ..................
            
            
            else  if ($this->cat != "" && $this->cat != 0  && $this->sid != "" && $this->sid != 0  && $this->rtype == RatioType::Standing) { //&& $this->design_id != "" && $this->design_id != 0
                //$query = "select c.id as ctg_id,c.name as category,c.active,d.design_no,d.image,i.mrp,d.lineno,d.rackno from it_categories c,it_ck_designs d,it_items i where c.id=d.ctg_id and d.id=i.design_id and c.id=i.ctg_id and c.id=$this->cat and d.id=$this->design_id group by d.design_no";
                $query = "select c.* from it_categories c where c.id=$this->cat ";
                $obj = $db->fetchObject($query);
                if (isset($obj)) {
                    $cat_name = $obj->name;
                    $state = $obj->active ? "ACTIVE" : "INACTIVE";
                    $state_str = "[ ".$state."]";
                }
                ?>
                <div class="clear"></div>
             <div id="expand_collapse">
                    <div class="box">
                        
                        <h2 class="expand"><?php echo $cat_name; ?> <?php echo $state_str; ?> &nbsp;&nbsp; (Set Selected Design's ratio)</h2> <!--Design No: <?php // echo $design_no.$mrp_str ; ?>-->
                        <div class="collapse" > 
                            
                            <?php // if (isset($obj->image)) { ?>
                                <!--<a href="<?php // echo $this->designImageUrl($obj->image); ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="<?php // echo $this->designImageUrl($obj->image); ?>" width="170" /></a>-->
                            <?php // } else { ?>
                                <!--<a href="" rel="prettyPhoto"><img class="grid_2" align="left" src="<?php // echo CdnUrl('images/stock/nophoto.jpeg'); ?>" width="170" /></a>-->
                            <?php // } ?>

                            <div class="block grid_10">
                                <form method="post" action="formpost/addRatioDetails.php">
                                    <input type="hidden" id="ctgid" name="category" value="<?php echo $this->cat; ?>" />
                                    <!--<input type="hidden" id="desno" name="designid" value="<?php // echo $this->design_id; ?>" />-->
                                    <?php if ($this->currUser->usertype == UserType::Dealer) { ?>
                                        <input type="hidden" id="sid" name="sid" value="<?php echo $this->currUser->id; ?>" />
                                    <?php } else { ?>
                                        <input type="hidden" id="sid" name="sid" value="<?php echo $this->sid; ?>" />
                                    <?php } ?>
                                    <input type="hidden" id="rtype" name="rtype" value="<?php echo $this->rtype; ?>" />
                                    <input type="hidden" id="userid" name="userid" value="<?php echo $this->currUser->id; ?>" />
                                    <!--<input type="hidden" id="mrp" name="mrp" value="<?php //echo $mrp; ?>" />-->
                                    <input type="hidden" id="designids" name="designids" value="-1"/>
                                    <table>
                                        <?php                                        
                                        $styleobj = $db->fetchObjectArray("select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.ctg_id=$this->cat and s1.style_id=s2.id  and s2.is_active = 1 order by s1.sequence");
                                        $no_styles = count($styleobj);
    //                                    print_r($styleobj);
                                        $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.ctg_id=$this->cat and s1.size_id=s2.id order by s1.sequence");
    //                                    print_r($sizeobj);
                                        $no_sizes = count($sizeobj);
                                        if ($this->currUser->usertype == UserType::Dealer) {                                            
                                            $storeid = $this->currUser->id;                                            
                                        } else {
                                             $storeid = $this->sid;                                           
                                        }
                                        ?>
                                        <thead>
                                            <tr><th></th>
                                                <?php
                                                $width = intval(100 / ($no_sizes + 1));
                                                for ($i = 0; $i < $no_sizes; $i++) {
                                                    print '<th style="text-align:left;" width="' . $width . '%">';
                                                    echo $sizeobj[$i]->size_name;
                                                    print "</th>";  //print sizes
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php                                            
                                            //for each unique style print style
                                            for ($k = 0; $k < $no_styles; $k++) {
                                                //print style names
                                                print "<tr><th>";
                                                echo $styleobj[$k]->style_name;
                                                print"</th>";
                                                $stylcod = $styleobj[$k]->style_id;
                                                 
                                                for ($i = 0; $i < $no_sizes; $i++) {
                                                    $sizeid = $sizeobj[$i]->size_id;
                                                    
//                                                    if($this->design_id == "-1"){
//                                                      $query = "select * from it_store_ratios where store_id=$storeid and ctg_id=$this->cat  and ratio_type=$this->rtype and style_id = $stylcod and size_id = $sizeid ";  
//                                                    }else{
                                                     //$query = "select * from it_store_ratios where store_id=$storeid and ctg_id=$this->cat and design_id=$this->design_id and ratio_type=$this->rtype and style_id = $stylcod and size_id = $sizeid and mrp = $mrp ";
                                                      $query = "select * from it_store_ratios where store_id=$storeid and ctg_id=$this->cat and ratio_type=$this->rtype and style_id = $stylcod and size_id = $sizeid ";
//                                                    }
//                                                    print "<br>".$query."<br>";
                                                      
                                                      
                                                    $getratio = $db->fetchObject($query);
                                                    ?><td>
                                                        <input type="text" id="<?php echo $styleobj[$k]->style_id . "_" . $sizeobj[$i]->size_id; ?>" name="<?php echo $styleobj[$k]->style_id . "_" . $sizeobj[$i]->size_id; ?>" style="width:40%" value="<?php if(isset($getratio)){ echo $getratio->ratio; } else {echo '1';}  ?>" >
                                                    </td><?php
                                                }
                                                
                                            }
                                            print "</tr>";                                        
                                        ?>
                                        </tbody>
                                    </table><Br>
                                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                                    <input class="blueglassbutton" type="submit" value="Save" style="float:right;">
                                </form>
                            </div> <!-- end class=grid_10 --><div class="clear"></div>
                        </div> <!-- end class="block" -->
                    </div> <!-- end class="box" -->
                </div>
                <div class="clear"></div>
               
        <!------------------------------All without exception start here------------------------------->
            <?php if(count($sids) == 1){?>    
                 <div class="clear"></div>
             <div id="expand_collapse">
                    <div class="box">
                       <h2 class="expand"> All without exception list</h2>  <!--Design No: <?php // echo $design_no.$mrp_str ; ?>-->
                        <div class="collapse" >
                            <?php // if (isset($obj->image)) { ?>
                                <!--<a href="<?php // echo $this->designImageUrl($obj->image); ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="<?php // echo $this->designImageUrl($obj->image); ?>" width="170" /></a>-->
                            <?php // } else { ?>
                                <!--<a href="" rel="prettyPhoto"><img class="grid_2" align="left" src="<?php // echo CdnUrl('images/stock/nophoto.jpeg'); ?>" width="170" /></a>-->
                            <?php // } ?>

                            <div class="block grid_10">
                              <!--  <form method="post" action="formpost/addRatioDetails.php">-->
                                    <input type="hidden" id="ctgid" name="category" value="<?php echo $this->cat; ?>" />
                                    <!--<input type="hidden" id="desno" name="designid" value="<?php // echo $this->design_id; ?>" />-->
                                    <?php if ($this->currUser->usertype == UserType::Dealer) { ?>
                                        <input type="hidden" id="sid" name="sid" value="<?php echo $this->currUser->id; ?>" />
                                    <?php } else { ?>
                                        <input type="hidden" id="sid" name="sid" value="<?php echo $this->sid; ?>" />
                                    <?php } ?>
                                    <input type="hidden" id="rtype" name="rtype" value="<?php echo $this->rtype; ?>" />
                                    <input type="hidden" id="userid" name="userid" value="<?php echo $this->currUser->id; ?>" />
                                    <!--<input type="hidden" id="mrp" name="mrp" value="<?php //echo $mrp; ?>" />-->
                                    <input type="hidden" id="designids" name="designids" value="-1"/>
                                    <table>
                                        <?php                                        
                                        $styleobj = $db->fetchObjectArray("select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.ctg_id=$this->cat and s1.style_id=s2.id  and s2.is_active = 1 order by s1.sequence");
                                        $no_styles = count($styleobj);
    
                                        $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.ctg_id=$this->cat and s1.size_id=s2.id order by s1.sequence");
   
                                        $no_sizes = count($sizeobj);
                                        if ($this->currUser->usertype == UserType::Dealer) {                                            
                                            $storeid = $this->currUser->id;                                            
                                        } else {
                                             $storeid = $this->sid;                                           
                                        }
                                        ?>
                                        <thead>
                                            <tr><th></th>
                                                <?php
                                                $width = intval(100 / ($no_sizes + 1));
                                                for ($i = 0; $i < $no_sizes; $i++) {
                                                    print '<th style="text-align:left;" width="' . $width . '%">';
                                                    echo $sizeobj[$i]->size_name;
                                                    //print_r(size_name);
                                                    print "</th>";  //print sizes
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php                                            
                                            //for each unique style print style
                                            for ($k = 0; $k < $no_styles; $k++) {
                                                //print style names
                                                print "<tr><th>";
                                                echo $styleobj[$k]->style_name;
                                                print"</th>";
                                                $stylcod = $styleobj[$k]->style_id;
                                                 
                                                for ($i = 0; $i < $no_sizes; $i++) {
                                                    $sizeid = $sizeobj[$i]->size_id;
                                                    
                                                    $query = "select * from it_store_ratios where store_id=$storeid and ctg_id=$this->cat and design_id = -1 and ratio_type=$this->rtype and style_id = $stylcod and size_id = $sizeid ";
                                                    $getratio = $db->fetchObject($query);
                                                    //print_r($query);
                                                    ?><td>
                                                        <input type="text" id="<?php echo $styleobj[$k]->style_id . "_" . $sizeobj[$i]->size_id; ?>" name="<?php echo $styleobj[$k]->style_id . "_" . $sizeobj[$i]->size_id; ?>" style="width:40%" value="<?php if(isset($getratio)){ echo $getratio->ratio; }?>" readonly>
                                                    </td><?php
                                                }
                                            }
                                            print "</tr>";                                        
                                        ?>
                                        </tbody>
                                    </table><Br>
                                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                                    <!--<input type="submit" value="Save" style="float:right;">
                                </form>-->
                            </div> <!-- end class=grid_10 --><div class="clear"></div>
                        </div> <!-- end class="block" -->
                    </div> <!-- end class="box" -->
                </div>
                <div class="clear"></div>
          <!------------------------All without Exception end here----------------------------------->
                   <?php 
                      $row_no = 1;
                     $dquery = "select s.id,s.store_id,s.ctg_id,ctg.name as ctgname,ctg.active,s.design_id,c.design_no,s.style_id,s.size_id,s.mrp,s.ratio_type,s.ratio,s.is_exceptional,s.is_exceptional_active,s.createtime from it_store_ratios s , it_ck_designs c , it_categories ctg where c.id = s.design_id and  s.store_id = $this->sid and  s.ctg_id = $this->cat and s.ratio_type = $this->rtype group by s.ctg_id,s.design_id";
                     $dObjs = $db->fetchObjectArray($dquery);
                     
                     foreach($dObjs as $dobj){ 
                               if(isset($dobj) && !empty($dobj) && $dobj != null){
                                   $row_no++;
                                   $divid = "accordion-" . $row_no;
//                                   $cat_name = $dobj->ctgname;
//                                   $state = $dobj->active ? "ACTIVE" : "INACTIVE";
//                                   $state_str = "[ ".$state."]";
                        ?>
                        <div class="box">
                        <h2 class="expand">Design No:<?php echo $dobj->design_no; ?></h2> <!--Design No: <?php // echo $design_no.$mrp_str ; ?>-->
                        <div class="collapse" id="<?php echo $divid; ?>" >
                            <?php // if (isset($obj->image)) { ?>
                               <!-- <a href="<?php // echo $this->designImageUrl($obj->image); ?>" rel="prettyPhoto"><img class="grid_2" align="left" src="<?php // echo $this->designImageUrl($obj->image); ?>" width="170" /></a>-->
                            <?php // } else { ?>
                              <!--  <a href="" rel="prettyPhoto"><img class="grid_2" align="left" src="<?php // echo CdnUrl('images/stock/nophoto.jpeg'); ?>" width="170" /></a>-->
                            <?php // } ?>

                            <div class="block grid_10">
                               
                                <input type="hidden"  name="designid[<?php echo $dobj->design_id; ?>][category]" value="<?php echo $dobj->ctg_id; ?>" />
                                    <input type="hidden" id="desno" name="designid" value="<?php // echo $this->design_id; ?>" />
                                    <?php if ($this->currUser->usertype == UserType::Dealer) { ?>
                                <input type="hidden"  name="designid[<?php echo $dobj->design_id; ?>][sid]" value="<?php echo $this->currUser->id; ?>" />
                                    <?php } else { ?>
                                        <input type="hidden"  name="designid[<?php echo $dobj->design_id; ?>][sid]" value="<?php echo $dobj->store_id; ?>" />
                                    <?php } ?>
                                    <input type="hidden"  name="designid[<?php echo $dobj->design_id; ?>][rtype]" value="<?php echo $dobj->ratio_type; ?>" />
                                    <input type="hidden"  name="designid[<?php echo $dobj->design_id; ?>][userid]" value="<?php echo $this->currUser->id; ?>" />
                                    <input type="hidden" id="mrp" name="mrp" value="<?php //echo $mrp; ?>" />
                                    <input type="hidden" name="designid[<?php echo $dobj->design_id; ?>][designid]" value="<?php echo $dobj->design_id; ?>"/>
                                    <table>
                                        <?php                                        
                                        $styleobj = $db->fetchObjectArray("select s1.style_id,s2.name as style_name from it_ck_styles s1,it_styles s2 where s1.ctg_id=$dobj->ctg_id and s1.style_id=s2.id  and s2.is_active = 1 order by s1.sequence");
                                        $no_styles = count($styleobj);
    //                                    print_r($styleobj);
                                        $sizeobj = $db->fetchObjectArray("select s1.size_id, s2.name as size_name from it_ck_sizes s1,it_sizes s2 where s1.ctg_id=$dobj->ctg_id and s1.size_id=s2.id order by s1.sequence");
    //                                    print_r($sizeobj);
                                        $no_sizes = count($sizeobj);
                                        if ($this->currUser->usertype == UserType::Dealer) {                                            
                                            $storeid = $this->currUser->id;                                            
                                        } else {
                                             $storeid = $this->sid;                                           
                                        }
                                        ?>
                                        <thead>
                                            <tr><th></th>
                                                <?php
                                                $width = intval(100 / ($no_sizes + 1));
                                                for ($i = 0; $i < $no_sizes; $i++) {
                                                    print '<th style="text-align:left;" width="' . $width . '%">';
                                                    echo $sizeobj[$i]->size_name;
                                                    print "</th>";  //print sizes
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php                                            
                                            //for each unique style print style
                                            for ($k = 0; $k < $no_styles; $k++) {
                                                print "<tr><th>";
                                                echo $styleobj[$k]->style_name;
                                                print"</th>";
                                                $stylcod = $styleobj[$k]->style_id;
                                                 
                                                for ($i = 0; $i < $no_sizes; $i++) {
                                                    $sizeid = $sizeobj[$i]->size_id;
                                                    $query = "select * from it_store_ratios where store_id=$dobj->store_id and ctg_id=$dobj->ctg_id and design_id = $dobj->design_id and ratio_type=$dobj->ratio_type and style_id = $stylcod and size_id = $sizeid ";
                                                    $getratio = $db->fetchObject($query);
                                                   if(isset($getratio)){$gid = $getratio->id; }else{ $gid=0;}
                                                    ?><td>
                                                        <input type="text" name="designid[<?php echo $dobj->design_id ;?>][item][item_<?php echo $gid; ?>_<?php echo $stylcod; ?>_<?php echo $sizeid ; ?>]"  id="designid[<?php echo $dobj->design_id ;?>][item][item_<?php echo $gid; ?>_<?php echo $stylcod; ?>_<?php echo $sizeid ; ?>]" style="width:40%" value="<?php if(isset($getratio)){ echo $getratio->ratio; }?>" readonly >
                                                    </td><?php
                                                   
                                             
                                                    
                                                        }
                                            }
                                            print "</tr>";                                        
                                        ?>
                                        </tbody>
                                    </table><Br>
                                    <span id="statusMsg" class="<?php echo $formResult->cssClass; ?>" style="display:<?php echo $formResult->showhide; ?>;"><?php echo $formResult->status; ?></span>
                                  <!--  <input type="button" value="Save" style="float:right;" onclick="javascript:saveDivSpc">-->
                               
                            </div> <!-- end class=grid_10--> <div class="clear"></div>                                                   
                   
                        </div> <!-- end class="collapse"--> 
                    </div><!--  end class="block" -->
                       
                     <?php } } ?>
                </div>
                <div class="clear"></div>
             
                <?php
            }}
             ?>    
        </div> <!--end div class 10-->
        <script src="js/chosen/chosen.jquery.js" type="text/javascript"></script>
        <script type="text/javascript"> $(".chzn-select").chosen(); $(".chzn-select-deselect").chosen({allow_single_deselect: true});</script>

        <?php
    }

}
?>



 