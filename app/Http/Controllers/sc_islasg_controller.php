<?php

namespace App\Http\Controllers;

use App\Http\Resources\municiposCollection;
use App\Models\municipiosModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Sensi\Facial\Detector;
use thiagoalessio\TesseractOCR\TesseractOCR;

class sc_islasg_controller extends Controller
{
    /*  Funciones municipios   */
    public function municipios($state)
    {
        $boolean = false;
        $response = ["status" => 0, "msg" => ""];

        try {
            if ($state) {
                $mun = municipiosModel::select('*')->where('state', $state)->get();
                $collection = new municiposCollection($mun); 

                $boolean = true;
                $response["status"] = 200;
                $response["msg"] = "Query completed.";
                return [$collection, $response, $boolean];
            }
        } catch (Exception $e) {
            return response()->json([200, "An error ocurred during query: " . $e]);
        }
    }


    /* Funciones OCR */
    public function ocr(Request $request)
    {
        $response = ["status" => 500, "msg" => "Internal Server Error."];

        if (!$request->missing("b64_Frontal", "b64_Reverso")) {
            if ($request["b64_Frontal"] != "" && $request["b64_Reverso"] != "") {
                try {
                    $data = json_decode($request->getContent());
                    $image_64F = $data->b64_Frontal;
                    $extension = explode('/', explode(':', substr($image_64F, 0, strpos($image_64F, ';')))[1])[1];
                    $replace = substr($image_64F, 0, strpos($image_64F, ',') + 1);
                    $imageF = str_replace($replace, '', $image_64F);
                    $imageF = str_replace(' ', '+', $imageF);
                    $imageNameF = 'Temp_Frontal_' . uniqid() . '.' . $extension;

                    $image_64R = $data->b64_Reverso;
                    $extensionR = explode('/', explode(':', substr($image_64R, 0, strpos($image_64R, ';')))[1])[1];
                    $replaceR = substr($image_64R, 0, strpos($image_64R, ',') + 1);
                    $imageR = str_replace($replaceR, '', $image_64R);
                    $imageR = str_replace(' ', '+', $imageR);
                    $imageNameR = 'Temp_Reverso_' . uniqid() . '.' . $extensionR;

                    Storage::disk('ocr')->put($imageNameF, base64_decode($imageF));

                    $pathF = storage_path('app/public/Temps/' . $imageNameF);

                    Storage::disk('ocr')->put($imageNameR, base64_decode($imageR));

                    $pathR = storage_path('app/public/Temps/' . $imageNameR);

                    if ($pathF && $pathR) {

                        list($width, $height) = getimagesize($pathF);
                        list($widthR, $heightR) = getimagesize($pathR);

                        if ($width < $height) {
                            $imagenF = imagecreatefromjpeg($pathF);
                            $deg = 90;

                            Storage::disk('public')->delete($imageNameF);

                            $deg ? $ineF = imagerotate($imagenF, $deg, 0) : $ineF = imagerotate($imagenF, 0, 0);

                            ob_start();
                            imagejpeg($ineF);
                            $ineF = ob_get_contents();
                            ob_clean();

                            Storage::disk('public')->put($imageNameF, $ineF);
                        }

                        if ($widthR < $heightR) {
                            $imagenR = imagecreatefromjpeg($pathR);
                            $deg = 90;

                            Storage::disk('public')->delete($imageNameR);

                            $deg ? $ineR = imagerotate($imagenR, $deg, 0) : $ineR = imagerotate($imagenR, 0, 0);

                            ob_start();
                            imagejpeg($ineR);
                            $ineR = ob_get_contents();
                            ob_clean();

                            Storage::disk('public')->put($imageNameR, $ineR);
                        }
                    }


                    $front = new TesseractOCR(storage_path('app/public/Temps/' . $imageNameF));
                    $front->lang('spa');
                    $text = $front->run();

                    $Reverse = new TesseractOCR(storage_path('app/public/Temps/' . $imageNameR));
                    $Reverse->lang('spa');
                    $text2 = $Reverse->run();

                    $detector = new Detector();
                    $detectable = $detector->fromFile(storage_path('app/public/Temps/' . $imageNameF));
                    var_dump($detectable->hasFace());
                    if ($detectable->hasFace()) {
                        $resource = $detectable->getFace();
                    } else {
                        $resource = $detectable->getSource();
                    }
                    
                    ob_start();
                    imagejpeg($resource);
                    $resource = ob_get_contents();
                    ob_clean();

                    $faceDetected = 'Temp_FACE_' . uniqid() . '.' . $extensionR;

                    Storage::disk('public')->put($faceDetected, $resource);

                    $text = str_replace("\n", " ", $text);
                    $text = str_replace('-', '', $text);
                    $text = str_replace('——….__', '', $text);
                    $text = explode(" ", $text);

                    $text2 = str_replace("\n", " ", $text2);
                    $text2 = str_replace('-', '', $text2);
                    $text2 = str_replace(' ', '<', $text2);
                    $text2 = explode("<", $text2);

                    Storage::disk('public')->delete($imageNameF);
                    Storage::disk('public')->delete($imageNameR);
                    Storage::disk('public')->delete($faceDetected);
                    
                    $clave = 0;
                    
                    foreach ($text as $key => $clv) {
                        if (stristr($clv, 'c') == true){
                            while (strlen($text[$clave]) < 18) {
                                $clave++;
                            }
                            $claveElector = $text[$clave];
                        }
                        else{
                            if($clave = array_search('CLAVE', $text)){
                                $clave++;
                                while(strlen($text[$clave]) < 18) {
                                    $clave++;
                                }
                                $claveElector = $text[$clave];
                            }
                        }
                        
                        break;
                    }
                
                    if ($curp = array_search("curp", $text) or $curp = array_search("CURP", $text) or $curp = array_search("cuae", $text) or $curp = array_search("Cuae", $text) or $curp = array_search("cURP", $text)) {

                        while (strlen($text[$curp]) < 18) {
                            $curp++;
                        }
                        
                        $tipoDoc = "Instituto Nacional Electoral (INE)";
                        $curpTxt = $text[$curp];
                    }

                    foreach ($text2 as $key => $id) {
                        if (stristr($id, 'IDMEX') == true)
                            break;
                    }
                    
                    if($ocr = array_search($id, $text2)){
                        $ocr++;
                        while(strlen($text2[$ocr]) < 12 or strlen($text2[$ocr]) != 13){
                            $ocr++;
                        }
                        
                        $ocrTxt = $text2[$ocr];
                    }

                    if (strlen($id) >= 15) {
                        $data = array(
                            "Documento" => $tipoDoc,
                            "Clave De Elector" => $claveElector,
                            "CURP" => $curpTxt,
                            "Identificacion De Credencial" => $id,
                            "OCR INE" => $ocrTxt
                        );
                        
                        return [$data];
                    } else {
                        $boolean = false;
                        $response["status"] = 500;
                        $response["msg"] = "No se pudo reconocer el reverso del INE, intente nuevamente.";

                        return [$response, $boolean];
                    }
                } catch (Exception $e) {
                    $boolean = false;
                    $response["status"] = 500;
                    $response["msg"] = "No se pudo reconocer el INE, intente nuevamente.";
                    return [$response, $boolean, $text]; //, $text, $text2, $text[$clave],
                }
            } else {
                $boolean = false;
                $response["status"] = 500;
                $response["msg"] = "Imágenes en base 64 inválidas o faltantes, intente nuevamente.";
                return [$response, $boolean];
            }
        } else {
            $boolean = false;
            $response["status"] = 500;
            $response["msg"] = "Imágenes en base 64 inválidas o faltantes, intente nuevamente.";
            return [$response, $boolean];
        }
    }

}
