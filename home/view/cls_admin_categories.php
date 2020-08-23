<?php
require_once "view/cls_renderer.php";
require_once ("lib/db/DBConn.php");
require_once ("lib/core/Constants.php");

class cls_admin_categories extends cls_renderer {
    var $currUser;
    var $userid;
    function __construct($params=null) {
        $this->currUser = getCurrUser();
        $this->params = $params;
        if (!$this->currUser) { return; }
        $this->userid = $this->currUser->id;
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
<!--<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/ajax-dynamic-list.js">

</script>
<style type="text/css" title="currentStyle">
    @import "js/datatables/media/css/demo_page.css";
    @import "js/datatables/media/css/demo_table.css";
</style>
<script src="js/datatables/media/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="js/chosen/chosen.css" />
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />-->

<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/ajax-dynamic-list.js">

</script>

 <style type="text/css" title="currentStyle">
    @import "js/datatables/media/css/demo_page.css";
    @import "js/datatables/media/css/demo_table.css";
</style>
<script src="js/datatables/media/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="js/chosen/chosen.css" />
<link rel="stylesheet" href="css/bigbox.css" type="text/css" />




<script type="text/javascript">
  $(function() {
     var url = "ajax/tb_store_categories.php";
     //alert(url);
      oTable = $('#tb_categories').dataTable({
                    "bProcessing": true,
                    "bServerSide": true,
               
        "aoColumns": [{"bSortable": false}, {"bSortable": false}, {"bSortable": false}],                   
                
                    "aaSorting": [[0,"desc"]],                  
                    "sAjaxSource": url,
                    "iDisplayLength": 25
                });
                //                oTable.fnSort([[0, 'desc']]);
                // search on pressing Enter key only
                $('.dataTables_filter input').unbind('keyup').bind('keyup', function(e) {
                    if (e.which == 13) {
                        oTable.fnFilter($(this).val(), null, false, true);
                    }
                });
               
});
 
  function setStatus(data) {
     // alert ("in set status"+data.value+data.id);
         data=data.value;
        var arr = data.split(":");
        var id = arr[0];
        var status = arr[1];
        var changeto;
        if (status == 1) changeto=0;
        else changeto=1;
        var ajaxUrl = "ajax/setCategoryStatus.php?cid="+id+"&status="+changeto;
        //alert(ajaxUrl);
           $.getJSON(ajaxUrl, function(data){
               if (data.error==0) {
                   $('#active_'+arr[0]).val(arr[0]+':'+changeto);
               }
               alert(data.message);
           });
    }
   
    function openEditBox(data) {
        //alert(data.value)
        data=data.value;
        var arr = data.split(':');
        $('#catname').html("Edit "+arr[0]);
        var catid = arr[1];
        //alert(catid);
        $('#catid').val(catid);
        //$('#catid').attr('value',catid);
        $('#editbox').show();
        var ajaxUrl = "ajax/getCatSizesAndStyles.php?cid="+catid;
        $('#sizes').empty();
        $('#styles').empty();
       
        //alert(ajaxUrl);
        $.getJSON(ajaxUrl, function(data){
            //insert size in select box
            var size = 0, key;
            for (key in data.size) {
                if (data.size.hasOwnProperty(key)) size++;
            }
            for (var x=1;x<=size;x++) {
               var nameid = data.size[x].split(":");
               $('#sizes').append('<option value="'+nameid[1]+'">'+nameid[0]+'</option>');
            }
            //insert style in select box
            var size = 0, key;
            for (key in data.style) {
                if (data.size.hasOwnProperty(key)) size++;
            }
            for (var x=1;x<=size;x++) {
               var nameid = data.style[x].split(":");
               $('#styles').append('<option value="'+nameid[1]+'">'+nameid[0]+'</option>');
            }
        });
    }

function addSize(sizeinfo) {
    var arr = sizeinfo.split(":");
    var sizeid=arr[0];
    var sizename=arr[1];
    var values = $("#sizes>option").map(function() { return $(this).val(); });
    if ($.inArray(sizeid, values)!=-1) {
        alert("this size already exists");
    } else {
        $('#sizes').append('<option value="'+sizeid+'">'+sizename+'</option>');
    }
}

function addStyle(styleinfo) {
    var arr = styleinfo.split(":");
    var styleid=arr[0];
    var stylename=arr[1];
    var values = $("#styles>option").map(function() { return $(this).val(); });
    if ($.inArray(styleid, values)!=-1) {
        alert("this size already exists");
    } else {
        $('#styles').append('<option value="'+styleid+'">'+stylename+'</option>');
    }
}

function removeSize() {
    $("#sizes option:selected").remove();
}

function removeStyle() {
    $("#styles option:selected").remove();
}

function moveSize(dir) {
    var $op = $('#sizes option:selected'), $this = $(this);
    if ($op.length) {
        (dir == 'up') ?
            $op.first().prev().before($op) :
            $op.last().next().after($op);
    }
}

function moveStyle(dir) {
    var $op = $('#styles option:selected'), $this = $(this);
    if ($op.length) {
        (dir == 'up') ?
            $op.first().prev().before($op) :
            $op.last().next().after($op);
    }
}

function saveList(type) {
    var catid = $('#catid').val();
    var finalids="";
    if (type=='size') {
        var len =  $('#sizes option').length;
        var ids = $("#sizes>option").map(function() { return $(this).val(); });
        for (var x=0;x<len;x++) {
            finalids += ids[x]+":";
        }
        finalids=finalids.slice(0,-1);
    } else if (type=='style') {
        var len =  $('#styles option').length;
        var ids = $("#styles>option").map(function() { return $(this).val(); });
        for (var x=0;x<len;x++) {
            finalids += ids[x]+":";
        }
        finalids=finalids.slice(0,-1);
    }
   
    var ajaxUrl = "ajax/setSizeStyleList.php?type="+type+"&catid="+catid+"&list="+finalids;
    //alert(ajaxUrl);
    $.getJSON(ajaxUrl, function(data){
        alert(data.message);
    });
}


</script>
<?php
    }

    //extra-headers close
    public function pageContent() {
        if ($this->currUser->usertype == UserType::Admin || $this->currUser->usertype == UserType::CKAdmin) {} else { print "Unauthorized Access"; return; }
    $menuitem = "catg";
        include "sidemenu.".$this->currUser->usertype.".php";
        $formResult = $this->getFormResult();
        //$db = new DBConn();
        ?>
<div class="grid_10">
            <?php
            $objs = $db->fetchObjectArray("select id,name, active from it_categories order by name");
            ?>
    <div class="grid_5" id="39" style="overflow:auto;">
    <table align="left" align="center" border="1" cellpadding="0" cellspacing="0" border="0" class="display" id="tb_categories">
        <thead>
        <tr>
            <th style="background-color: white;">Category Name</th>
            <th style="background-color: white;">Active</th>
            <th style="background-color: white;">Edit Styles+Sizes</th>
        </tr>
       </thead>
    </table>
    </div>
   

    <div class="grid_7" id ="editbox" style="display:none;" >
        <h3 id="catname"></h3>
        <input type="hidden" id="catid" value=""/>
       
        <div class="grid_6">
            <h4>Set Size</h4>
            <b>Add Size :</b>
            <select id="allsizes" onchange="addSize(this.value);">
                <option value="0">Select</option>
                <?php $allsizes = $db->fetchObjectArray("select * from it_sizes");
                foreach ($allsizes as $size) {
                ?>
                <option width="30%" value="<?php echo $size->id.":".$size->name; ?>"><?php echo $size->name; ?></option>
                <?php } ?>
            </select>
            <h5>Current display sizes (Sequenced)</h5>
            <div class="grid_9">
                <select id="sizes" name="selectLeft" size="10" width="100%" style="width:200px;">
                </select>
            </div>
            <div class="grid_3">
                <input name="btnTop" type="button" id="btnTop" value="^" onClick="javaScript:moveSize('up');"><br/>
                <input name="btnDown" type="button" id="btnDown" value="v" onClick="javaScript:moveSize('down');">
            </div>
            <div class="grid_12">
                <div class="grid_6">
                    <button onclick="removeSize();">Remove Selected</button>
                </div>
                <div class="grid_6">
                    <button onclick="saveList('size');">Save List</button>
                </div>
            </div>
        </div>
        <div class="grid_6">
            <h4>Set Style</h4>
            <b>Add Style :</b>
            <select id="allstyles" onchange="addStyle(this.value);">
                <option value="0">Select</option>
                <?php $allstyles = $db->fetchObjectArray("select * from it_styles order by name");
                foreach ($allstyles as $style) {
                ?>
                <option width="40%" value="<?php echo $style->id.":".$style->name; ?>"><?php echo $style->name; ?></option>
                <?php } ?>
            </select>
            <h5>Current display styles (Sequenced)</h5>
           
            <div class="grid_9">
                <select id="styles" name="selectLeft" size="10" width="100%" style="width:200px;">
                </select>
            </div>
            <div class="grid_3">
                <input name="btnTop" type="button" id="btnTop" value="^" onClick="javaScript:moveStyle('up');"><br/>
                <input name="btnDown" type="button" id="btnDown" value="v" onClick="javaScript:moveStyle('down');">
            </div>
            <div class="grid_12">
                <div class="grid_6">
                    <button onclick="removeStyle();">Remove Selected</button>
                </div>
                <div class="grid_6">
                    <button onclick="saveList('style');">Save List</button>
                </div>
            </div>
        </div>
    </div>
   

  </div>


    <?php
    }
}
?>