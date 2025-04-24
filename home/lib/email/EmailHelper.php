<?php

// example on using PHPMailer with GMAIL

include("class.phpmailer.php");
include("class.smtp.php"); // note, this is optional - gets called from main class if not already loaded

class EmailHelper {

public function send($toArray, $subject, $body,$attachments=false , $ccArray=false) {
$mail             = new PHPMailer();

$mail->IsSMTP();
$mail->SMTPAuth   = true;                  // enable SMTP authentication
$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
$mail->Port       = 465;                   // set the SMTP port

#$mail->Username   = "cottonking.portal@gmail.com";  // GMAIL username
#$mail->Password   = "cottonking2012";            // GMAIL password
//$mail->Username   = "cottonkingwebmaster@gmail.com";  // GMAIL username
//$mail->Password   = "hqsehyhriufycvci";            // GMAIL password
    
 $mail->Username   = "cottonkingwebmaster2@gmail.com";  // GMAIL username
 //$mail->Password   = "Cottonking@321";            // GMAIL password
 $mail->Password   = "ukrogttrmptbubla";            // App password --gifiudlssrcmihlz

$mail->From       = "cottonkingwebmaster2@gmail.com";
$mail->FromName   = "Linenking  Webmaster";
$mail->Subject    = $subject;
$mail->WordWrap   = 50; // set word wrap

$mail->MsgHTML($body);

$mail->AddReplyTo("cottonkingwebmaster2@gmail.com","Linenking Webmaster");

//adding attachement(s)
if($attachments){
    foreach($attachments as $attachment){
      $mail->AddAttachment($attachment);
    }
}

foreach($toArray as $to) {
$mail->AddAddress($to);
}

//optional
    if($ccArray){
        foreach($ccArray as $cc){
         $mail->AddCC($cc);
        }        
    }
    
    
$mail->IsHTML(true); // send as HTML

if(!$mail->Send()) {
  return $mail->ErrorInfo;
} else {
  return 0;
}
}

}
