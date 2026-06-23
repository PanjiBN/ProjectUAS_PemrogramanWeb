<?php

require_once dirname(__FILE__).'/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

class Mail {

    public static function SendMail($to, $name, $subject, $message) {

        //  Inisiasi objek PHPMailer (tanpa parameter, sesuai modul) 
        $mail = new PHPMailer();

        //  Aktifkan mode SMTP 
        $mail->isSMTP();

        // Host SMTP Gmail
        $mail->Host = 'smtp.gmail.com';

        //  Wajib autentikasi SMTP 
        $mail->SMTPAuth = true;

        //  Enkripsi TLS (Port 587) 
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            )
        );

        //  KREDENSIAL GMAIL - SESUAIKAN BAGIAN INI
        $mail->Username = 'bisaapaaja7@gmail.com';   
        $mail->Password = 'tdxf yooe gweu mmbs';  

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

        //  Kirim email 
        if ($mail->send()) {
            return true;
        } else {
            // Kembalikan pesan error dari PHPMailer
            return false;
        }
    }

}
?>
