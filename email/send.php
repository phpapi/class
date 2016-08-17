<?php

/**
 * Description: Send email api
 * License:
 * User: carey
 * Date: 2016/8/8
 * Time: 16:45
 */

use xlz\SMTP\Email;
require_once(dirname(__DIR__) . '/email/Email.class.php');
require_once(dirname(__DIR__) . '/email/case.php');

class Mail extends TestCase {
    public static function send ( $to_email, $to_name, $title, $message ){
        $mail = new Email(self::SERVER, self::PORT_TLS);
        $mail->setLogin(self::USER, self::PASS); // email, password
        $mail->setProtocol(Email::SSL); // email, password
        $mail->setFrom(self::FROM_EMAIL, self::FROM_NAME); // your name, your email
        $mail->addTo( $to_email,$to_name);
        $mail->setSubject($title. time());
        $mail->setMessage($message, true);
        $mail->send();
        return $mail;
    }
}

var_dump(Mail::send('phpapi@163.com','to_name','title','message'));
