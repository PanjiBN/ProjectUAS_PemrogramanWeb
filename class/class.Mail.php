<?php

require_once dirname(__FILE__).'/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Mail {
    private static $lastError = '';

    public static function SendEticketMail($to, $name, $subject, $message) {
        return self::sendUsingAccount(
            'ferdinusantaraprima@gmail.com',
            'odjylfpuyszrgyte',
            $to,
            $name,
            $subject,
            $message
        );
    }

    private static function sendUsingAccount($username, $password, $to, $name, $subject, $message) {
        self::$lastError = '';

        //  Inisiasi objek PHPMailer dari Composer
        $mail = new PHPMailer(true);

        try {
            //  Aktifkan mode SMTP 
            $mail->isSMTP();

            // Host SMTP Gmail
            $mail->Host = 'smtp.gmail.com';

            //  Wajib autentikasi SMTP 
            $mail->SMTPAuth = true;

            //  Enkripsi TLS (Port 587) 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                )
            );

            //  KREDENSIAL GMAIL
            $mail->Username = $username;
            $mail->Password = $password;

            //  Identitas pengirim 
            $mail->setFrom($mail->Username, 'FutsalHub');

            //  Penerima 
            $mail->addAddress($to, $name);

            //  Format email HTML 
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            //  Subjek dan isi pesan 
            $mail->Subject = $subject;
            $mail->Body    = $message;

            // Versi plain text sebagai fallback 
            $mail->AltBody = strip_tags($message);

            return $mail->send();
        } catch (PHPMailerException $e) {
            self::$lastError = $mail->ErrorInfo ?: $e->getMessage();
            return false;
        }
    }

    public static function GetLastError() {
        return self::$lastError;
    }

}
?>
