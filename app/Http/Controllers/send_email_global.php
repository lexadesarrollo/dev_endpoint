<?php

namespace App\Http\Controllers;

use App\Models\apprisa_tokens;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPMailer\PHPMailer\PHPMailer;

class send_email_global extends Controller
{
    public static  $empresa, $host, $username, $password, $background, $logo, $color, $url, $texto, $message;

    public static function send_email_credentials($data)
    {
        switch (self::$empresa) {
            case 'Hay Tiro':
                self::$host = 'mail.haytiro.mx';
                self::$username = 'ventas@haytiro.mx';
                self::$password = 'Iy0~ZhSl@s%6';
                self::$background = 'https://haytiro.mx/images/textura.webp';
                self::$logo = 'https://haytiro.mx/images/logo2.png';
                self::$color = '#e40f20';
                self::$url = 'https://haytiro.mx/';
                self::$texto = 'Iniciar Sesión';
                self::$message = 'Te damos la bienvenida,';
                break;
            case 'Apprisa':
                self::$host = 'apprisa.com.mx';
                self::$username = 'soporte@apprisa.com.mx';
                self::$password = 'nj!Be_xEAACJ';
                self::$background = 'https://da-pw.mx/storage/Wallpapers/wallpaper_apprisa.jpg';
                self::$logo = 'https://apprisa.com.mx/img/logo.png';
                self::$color = '#ffc100';
                self::$url = 'https://apprisa.com.mx/';
                self::$texto = 'Descargar la aplicación';
                self::$message = 'Te damos la bienvenida,';
                break;
            case 'CensoApp':
                self::$host = 'adminalba.mx';
                self::$username = 'soporte@adminalba.mx';
                self::$password = '4T,1CUNJ^J8Z';
                self::$background = 'https://da-pw.mx/storage/Wallpapers/wallpaper_censo.jpg';
                self::$logo = 'https://adminalba.mx/imgs/logo.png';
                self::$color = '#ead22d';
                self::$url = 'https://adminalba.mx/';
                self::$texto = 'Descargar la aplicación';
                self::$message = 'Te damos la bienvenida,';
                break;
            case 'CensoApp - Recuperación de cuenta':
                self::$host = 'adminalba.mx';
                self::$username = 'soporte@adminalba.mx';
                self::$password = '4T,1CUNJ^J8Z';
                self::$background = 'https://da-pw.mx/storage/Wallpapers/wallpaper_censo.jpg';
                self::$logo = 'https://adminalba.mx/imgs/logo.png';
                self::$color = '#ead22d';
                self::$url = 'https://adminalba.mx/';
                self::$texto = 'Descargar la aplicación';
                self::$message = 'Se ha recuperado tu cuenta,';
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
            $mail->Body = ' <body style="background-image: url(' . self::$background . ') !important; background-size: cover; background-repeat: no-repeat;">
                                <center><img style="width:65%; padding-bottom: 1.5em; padding-top: 1.5em" src="' . self::$logo . '"></center>
                                <h1 style="text-align:center"><b>'.self::$message.' ' . $data['name_complete'] . '</b></h1><br>
                                <div style="padding: 10px !important; background-color: rgba(255, 255, 255, 0.8); border-radius: 15px; text-align: start; -webkit-box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                                -moz-box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                                box-shadow: 0 2px 6px rgba(0,0,0,0.2); backdrop-filter: blur(50px) !important">
                                <p style="font-size: 16px; padding: 0 1.5em 0 1.5em">Aquí están tus credenciales de acceso:</p> 
                                <p style="font-size: 16px; padding: 0 1.5em 0 1.5em">Usuario: <b>' . $data['username'] . '</b></p> 
                                <p style="font-size: 16px; padding: 0 1.5em 0 1.5em"> Contraseña: <b>' . $data['password'] . '</b></p> 
                                <p style="font-size: 16px; padding: 0 1.5em 0 1.5em"> No compartas esta información con nadie, nuestros empleados jamás la pedirán.</p>
                                </div>
                                <br>
                                <br> 
                                <center><a style="text-decoration: none; background-color:' . self::$color . '; color: #fff; padding: 10px; border-radius: 15px; cursor: pointer; font-size: 15px" href="' . self::$url . '"><b>' . self::$texto . '</b></a></center>
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

    public static function twoFA_email($data)
    {
        try {
            if (self::$empresa == "Alba") {
                self::$host = 'adminalba.mx';
                self::$username = 'soporte@adminalba.mx';
                self::$password = '4T,1CUNJ^J8Z';

                $comb = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890{[(-._;,)-]}';
                $pass = array();

                $combLen = strlen($comb) - 1;
                for ($i = 0; $i < 8; $i++) {
                    $n = rand(0, $combLen);
                    $pass[] = $comb[$n];
                }

                $code_user = implode($pass);
                $token = Hash::make($code_user);

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
                $mail->addAddress($data["email"]);
                $mail->isHTML(true);
                $mail->Subject = 'Comprobación de autenticación 2FA Alba';
                $mail->Body = ' <body style="background-size: cover; background-repeat: no-repeat;">
                                <h1 style="text-align:center"><b>Código de seguridad para Alba</b></h1><br>
                                <div style="padding: 10px !important; background-color: rgba(255, 255, 255, 0.8); border-radius: 15px; text-align: justify; -webkit-box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                                -moz-box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                                box-shadow: 0 2px 6px rgba(0,0,0,0.2); backdrop-filter: blur(50px) !important">
                                <p style="font-size: 16px; padding: 0 1.5em 0 1.5em; text-align: center"><b>' . $code_user . '</b></p> 
                                <p style="font-size: 16px; padding: 0 1.5em 0 1.5em;">No comparta este código con nadie.</p>
                                </div>
                            </body>';
                $mail->send();

                $token_exist = apprisa_tokens::where('credential', $data["id"])->first();

                if ($token_exist != null || $token_exist != false) {
                    apprisa_tokens::where('credential', $data["id"])->delete();
                    apprisa_tokens::insert([
                        'credential' => $data["id"],
                        'token' => $token
                    ]);
                } else {
                    apprisa_tokens::insert([
                        'credential' => $data["id"],
                        'token' => $token
                    ]);
                }

                return [
                    'status' => true,
                    'message' => 'Email send successfully'
                ];
            } else {
                return [
                    'status' => False,
                    'message' => 'Resource not found'
                ];
            }
        } catch (Exception $cb) {
            return [
                'status' => false,
                'message' =>  'An error ocurred during send email: ' . $cb
            ];
        }
    }
}
