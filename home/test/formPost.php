<html>
<head>
<script type="text/javascript">
function validateForm(theForm)
{

var x = theForm.fname.value;
alert(x);
return false;
}
</script>
</head>

<body>
<form name="myForm" action="#" onsubmit="return validateForm(this);" method="post">
    <div id="abc">
First name: <input type="text" name="fname">
<input type="submit" value="Submit">
    </div>
</form>
</body>

</html>

