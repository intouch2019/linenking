<?php

// example on using PHPMailer with GMAIL

include("class.phpmailer.php");
include("class.smtp.php"); // note, this is optional - gets called from main class if not already loaded

class EmailHelper {

    public function send($toArray, $subject, $body) {
        if (config_disable_email == true){ 
            return;
            exit;
            
        }
           
        $mail = new PHPMailer();

        $mail->IsSMTP();
        $mail->SMTPAuth = true;                  // enable SMTP authentication
        $mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
        $mail->Host = "smtp.gmail.com";      // sets GMAIL as the SMTP server
        $mail->Port = 465;                   // set the SMTP port

        $mail->Username = "intouchemailtester@gmail.com";  // GMAIL username
        $mail->Password = "intouch25";           // GMAIL password

        $mail->From = "intouchemailtester@gmail.com";
        $mail->FromName = "InTouch Email Tester";
        $mail->Subject = $subject;
        $mail->WordWrap = 70; // set word wrap

        $mail->MsgHTML($body);

        $mail->AddReplyTo("intouchemailtester@gmail.com", "Intouch Admin");

        foreach ($toArray as $to) {
            $mail->AddAddress($to);
        }

        $mail->IsHTML(true); // send as HTML

        if (!$mail->Send()) {
            return $mail->ErrorInfo;
        } else {
            return 0;
        }
    }

}
