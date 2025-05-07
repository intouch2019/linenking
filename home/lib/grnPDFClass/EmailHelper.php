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
    //$mail->Username   = "cottonkingwebmaster@gmail.com";  // GMAIL username
    #$mail->Password   = "Cottonking@2012";            // GMAIL password(old- cottonking2012)
    //$mail->Password   = "hqsehyhriufycvci";            // GMAIL password(old- cottonking2012) //App Password
    
    $mail->Username   = "webmaster@kinglifestyle.com";  // GMAIL username
    //$mail->Password   = "Cottonking@321";            // GMAIL password
    $mail->Password   = "bkaampfnvsknzjdq";            // App password

    $mail->From       = "webmaster@kinglifestyle.com";
    $mail->FromName   = "LinenKing Webmaster";
    $mail->Subject    = $subject;
    $mail->WordWrap   = 50; // set word wrap

    $mail->MsgHTML($body);

    $mail->AddReplyTo("webmaster@kinglifestyle.com","LinenKing Webmaster");
    
    //adding attachement(s)
    if($attachments){
        foreach($attachments as $attachment){
          $mail->AddAttachment($attachment);
        }
    }

    foreach($toArray as $to) {
      $mail->AddBCC($to);
    }
   
    //optional
    if($ccArray){
        foreach($ccArray as $cc){
         $mail->AddBCC($cc);
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
