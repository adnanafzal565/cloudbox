<?php

namespace App\Http\Controllers;

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use DB;

abstract class Controller
{
    protected $token_secret = "laravel-authentication-token-secret";
    protected $admin_token_secret = "laravel-authentication-admin-token-secret";
    protected $premium_message = "This feature is available in premium version only.";

    protected function send_mail($to, $to_name, $subject, $body)
    {
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        $settings = DB::table("settings")->get();
        if (count($settings) <= 0)
        {
            return "SMTP configurations not set.";
        }

        $settings_obj = new \stdClass();
        foreach ($settings as $setting)
        {
            $settings_obj->{$setting->key} = $setting->value;
        }

        try
        {
            //Server settings
            $mail->SMTPDebug = 0; // SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $settings_obj->smtp_host;                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $settings_obj->smtp_username;                     //SMTP username
            $mail->Password   = $settings_obj->smtp_password;                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = $settings_obj->smtp_port;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom($settings_obj->smtp_from, $settings_obj->smtp_from_name);
            $mail->addAddress($to, $to_name);     //Add a recipient

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $body;

            $mail->send();
            return "";
            // echo 'Message has been sent';
        }
        catch (Exception $e)
        {
            return $mail->ErrorInfo;
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
