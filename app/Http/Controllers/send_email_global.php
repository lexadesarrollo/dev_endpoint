<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;

class send_email_global extends Controller
{
    public static  $empresa, $host, $username, $password, $background, $logo;

    public static function send_email_credentials($data)
    {
        switch (self::$empresa) {
            case 'Hay Tiro':
                self::$host = 'mail.haytiro.mx';
                self::$username = 'ventas@haytiro.mx';
                self::$password = 'Iy0~ZhSl@s%6';
                break;
            case 'Apprisa':

                break;
            case 'SIO':

                break;
            case 'Rgsc':
                break;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = self::$host;
            $mail->SMTPAuth = true;
            $mail->Username = self::$username;
            $mail->Password = self::$password;
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
            $mail->setFrom(self::$username, self::$empresa);
            $mail->addAddress($data['email']);
            $mail->isHTML(true);
            $mail->Subject = 'Credenciales de acceso para ' . self::$empresa . '';
            $mail->Body = ' <body style="background-image: url(https://haytiro.mx/images/textura.webp) !important; background-size: cover; background-repeat: no-repeat;">
                                <center><img style="width:65%; padding-bottom: 1.5em; padding-top: 1.5em" src="https://haytiro.mx/images/logo2.png"></center>
                                <h1 style="text-align:center"><b>Te damos la bienvenida, ' . $data['name_complete'] . '</b></h1><br>
                                <div style="padding: 10px !important; background-color: rgba(255, 255, 255, 0.8); border-radius: 15px; text-align: justify; -webkit-box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                                -moz-box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                                box-shadow: 0 2px 6px rgba(0,0,0,0.2); backdrop-filter: blur(50px) !important">
                                <p style="font-size: 16px; padding: 0 1.5em 0 1.5em">Aquí están tus credenciales de acceso:</p> 
                                <p style="font-size: 16px; padding: 0 1.5em 0 1.5em">Usuario: <b>' . $data['username'] . '</b></p> 
                                <p style="font-size: 16px; padding: 0 1.5em 0 1.5em"> Contraseña: <b>' . $data['password'] . '</b></p> 
                                <p style="font-size: 16px; padding: 0 1.5em 0 1.5em"> No compartas esta información con nadie, nuestros empleados jamás la pedirán.</p>
                                </div>
                                <br>
                                <br> 
                                <center><a style="text-decoration: none; background-color: #c00000; color: #fff; padding: 10px; border-radius: 15px; cursor: pointer; font-size: 15px" href="https://haytiro.mx/"><b>Iniciar Sesión</b></a></center>
                                <br>
                                <br>
                                </body>';

            $mail->send();
            return [
                'status' => true,
                'message' => 'Email send successfully'
            ];
        } catch (Exception $cb) {
            return [
                'status' => false,
                'message' =>  'An error ocurred during send email: ' . $cb    
            ];
        }
    }

    public static function cv($data)
    {
        try {
            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = self::$host;
            $mail->SMTPAuth = true;
            $mail->Username = self::$username;
            $mail->Password = self::$password;
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
            $mail->addAttachment($data['cv']);
            $mail->setFrom(self::$username, self::$empresa);
            $mail->addAddress($data['email']);
            $mail->isHTML(true);
            $mail->Subject = 'CV para candidato abogado de Hay Tiro: ' . $data['nombre'] . '';
            $mail->Body = ' <body style="background-image: url(https://haytiro.mx/images/textura.webp) !important; background-size: cover; background-repeat: no-repeat;">
                                <h1 style="text-align:center"><b>CV del candidato ' . $data['nombre'] . '</b></h1><br>
                                <div style="padding: 10px !important; background-color: rgba(255, 255, 255, 0.8); border-radius: 15px; text-align: justify; -webkit-box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                                -moz-box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                                box-shadow: 0 2px 6px rgba(0,0,0,0.2); backdrop-filter: blur(50px) !important">
                                <p style="font-size: 16px; padding: 0 1.5em 0 1.5em">Estos son sus datos de contacto:</p> 
                                <p style="font-size: 16px; padding: 0 1.5em 0 1.5em">Nombre: <b>' . $data['nombre'] . '</b></p> 
                                <p style="font-size: 16px; padding: 0 1.5em 0 1.5em">Correo: <b>' . $data['email'] . '</b></p> 
                                <p style="font-size: 16px; padding: 0 1.5em 0 1.5em">Teléfono: <b>' . $data['phone'] . '</b></p> 
                                </div>
                            </body>';
            $mail->send();
            return [
                'status' => true,
                'message' => 'Email send successfully'
            ];
        } catch (Exception $cb) {
            return [
                'status' => false,
                'message' =>  'An error ocurred during send email: ' . $cb
            ];
        }
    }
}
