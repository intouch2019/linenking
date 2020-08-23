<html>
<head>
<link href="../uploadify/uploadify.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../fluid960gs/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../uploadify/swfobject.js"></script>
<script type="text/javascript" src="../uploadify/jquery.uploadify.v2.1.4.min.js"></script>
<script type="text/javascript">


//function addimage(abc)
//{
  //      alert(abc);
$(function() {
        $("#imageUpload").uploadify({
		'uploader'       : '../uploadify/uploadify.swf',
		'script'         : 'load.php',
		'cancelImg'      : '../uploadify/cancel.png',
		'folder'         : 'images',
		'auto'           : true,
		'multi'          : false,
		'queueSizeLimit' : 1,
                'buttonText'     : 'Select Image',
		'fileDesc'	 : 'jpg, gif, png',
		'fileExt'        : '*.jpg;*.gif;*.png',
                'removeCompleted': false,
		'sizeLimit'      : '512000',//max size bytes - 500kb
		'checkScript'    : '../uploadify/check.php', //if we take this out, it will never replace files, otherwise asks if we want to replace
		'onError'        : function (event,ID,fileObj,errorObj) {
                            alert(errorObj.type + ' Error: ' + errorObj.info); }
                //'onAllComplete'  : function() {
                                    //alert("image added !");
                                        //$('#switch-effect').unbind('change');
                                        //$('#toggle-slideshow').unbind('click');
                                        //galleries[0].slideshow.stop();
                                        //start();
                                   // }
	});
});
//}
</script>
</head>

<body>
    <div id="image">
<input type="file" id="imageUpload" name="image">
    </div>
 <a href="javascript:$('#imageUpload').uploadifyUpload();"><input type="button" value=" Add Files "/></a> 
    <div id="imagebutton"
    
</body>
</html>