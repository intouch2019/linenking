<?php
    $filename = isset($_POST['filename']) ? $_POST['filename'] : "";
    $message = isset($_POST['stacktrace']) ? $_POST['stacktrace'] : "";
    $storeid = isset($_GET['storeid']) ? $_GET['storeid'] : "0";
    if ($filename != "" && $message != "") {
    	file_put_contents("$storeid.$filename", $message . "\n", FILE_APPEND);
	return;
    }

    $myDirectory = opendir(".");
    while($entryName = readdir($myDirectory)) {
        $dirArray[] = $entryName;
    }
    closedir($myDirectory);
    $indexCount = count($dirArray);
    sort($dirArray);
    print("<TABLE border=1 cellpadding=5 cellspacing=0 \n");
    print("<TR><TH>Filename</TH><TH>Filetype</th><th>Filesize</TH></TR>\n");
    for($index=0; $index < $indexCount; $index++) {
        if ((substr("$dirArray[$index]", 0, 1) != ".") 
                && (strrpos("$dirArray[$index]", ".stacktrace") != false)){ 
            print("<TR><TD>");
            print("<a href=\"$dirArray[$index]\">$dirArray[$index]</a>");
            print("</TD><TD>");
            print(filetype($dirArray[$index]));
            print("</TD><TD>");
            print(filesize($dirArray[$index]));
            print("</TD></TR>\n");
        }
    }
    print("</TABLE>\n");
?>
