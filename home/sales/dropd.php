<html>
<head>
<title>Plotting Routes</title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/ajax.js"></script>
<script type="text/javascript" src="js/ajax-dynamic-list.js"></script>
<script type="text/javascript">
	var gCurrPageNo = 1;
	var gSearch = false;

	function checkEnter(e) {
		if(e.keyCode==13 || e.keyCode==9) { search(); return false; }
		return true;
	}
</script>
<style type="text/css">
	/* Big box with list of options */
	#ajax_listOfOptions{
		position:absolute;	/* Never change this one */
		width:175px;	/* Width of box */
		height:250px;	/* Height of box */
		overflow:auto;	/* Scrolling features */
		border:1px solid #317082;	/* Dark green border */
		background-color:#FFF;	/* White background color */
		text-align:left;
		font-size:0.9em;
		z-index:100;
	}
	#ajax_listOfOptions div{	/* General rule for both .optionDiv and .optionDivSelected */
		margin:1px;		
		padding:1px;
		cursor:pointer;
		font-size:0.9em;
	}
	#ajax_listOfOptions .optionDiv{	/* Div for each item in list */
		
	}
	#ajax_listOfOptions .optionDivSelected{ /* Selected item in the list */
		background-color:#317082;
		color:#FFF;
	}
	#ajax_listOfOptions_iframe{
		background-color:#F00;
		position:absolute;
		z-index:5;
	}
	
	form{
		display:inline;
	}
	</style>
</head>
<body>
<div class="content">
<form name="plotRoute" method="post" action="#" onsubmit="search();return false;">
	<!--  <div class="leftcontent"><input type="text" id="station" name="station"></div>-->
	<div  style="font-size: 14">
	<a href="javascript:{}" onclick="showAll()">Show All Routes</a><br />
	Show routes passing through <textarea rows=1 style="height:22px;" id="station" name="station" onkeypress="return checkEnter(event)" onkeyup="ajax_showOptions(this,'getStationsByLetters',event)"></textarea>
	Station Name will appear as you enter text. <input type="hidden" id="station_hidden" name="station_ID"><!-- THE ID OF the station will be inserted into this hidden input -->
	</div>
</form>
</div>
</body>
</html>
