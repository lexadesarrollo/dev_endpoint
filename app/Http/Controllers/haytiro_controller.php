<?php

namespace App\Http\Controllers;

use App\Models\haytiro_cart_products;
use App\Models\haytiro_cart_shop;
use App\Models\haytiro_credentials;
use App\Models\haytiro_demand;
use App\Models\haytiro_payment_control;
use App\Models\haytiro_services;
use App\Models\haytiro_services_view;
use App\Models\haytiro_users;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Stripe\StripeClient;

class haytiro_controller extends Controller
{

    private static $views = [
        'key_ad'  => 'adviser_view',
        'key_a'   => 'administrator_view',
        'key_c'   => 'customers_view',
        'key_cs'  => 'customers_services_view',
        'key_sv'  => 'services_view',
        'key_tsv' => 'demand_view',
        'key_pb'  => 'publicity_view',
        'key_ctA' => 'viewAttendedChats',
        'key_cNA' => 'chat_for_attention_view'
    ];

    private static $key;

    public function __construct()
    {
        self::$key = new StripeClient(env('STRIPE_TOKEN'));
    }

    public function ctl_advisor()
    {
    }

    ///-------Funciones crear usuarios----------///
    public function created_users(Request $request)
    {
        $rules = [
            'name_user' => 'required',
            'last_name' => 'required',
            'mother_last_name'  => 'required',
            'cell_phone_number' => 'required|min:11|numeric',
            'email'     => 'required',
            'birthdate' => 'required|date',
            'id_role_credential' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        $customer_validate = haytiro_users::where(['name_user' => $request->name_user])
            ->where(['last_name'  => $request->last_name])
            ->where(['mother_last_name' => $request->mother_last_name])->get();
        if (sizeof($customer_validate) == 0) {
            $email_validate = haytiro_users::where(['email' => $request->email])->get();
            if (sizeof($email_validate) == 0) {
                send_email_global::$empresa = 'Hay Tiro';
                $credentials = credentials_global::created_credentials($request->name_user);
                $name_complete = ucwords(strtolower($request->name_user)) . ' ' . ucwords(strtolower($request->last_name)) . ' ' . ucwords(strtolower($request->mother_last_name));
                $data = [
                    'name_complete' => $name_complete,
                    'email'    => $request->email,
                    'username' => $credentials['user_name'],
                    'password' => $credentials['password']
                ];
                $email = send_email_global::send_email_credentials($data);
                if ($email['status'] == true) {
                    try {
                        $created_user = haytiro_users::insert([
                            'name_user' => ucwords(strtolower($request->name_user)),
                            'last_name' => ucwords(strtolower($request->last_name)),
                            'mother_last_name' => ucwords(strtolower($request->mother_last_name)),
                            'cell_phone_number' => $request->cell_phone_number,
                            'email'  => $request->email,
                            'avatar_user' => 'default/imagen/avatar.png',
                            'id_status_cell_phone_number' => 7,
                            'birthdate' => $request->birthdate
                        ]);
                        if ($created_user) {
                            try {
                                $last_customer_id = haytiro_users::latest('id_user')->first();
                                $id_user = (int)$last_customer_id['id_user'];

                                $created_credentials = haytiro_credentials::insert([
                                    'user_credential' => $credentials['user_name'],
                                    'password_credential' => $credentials['password_token'],
                                    'id_role_credential' => $request->id_role_credential,
                                    'id_user' => $id_user,
                                    'id_type_advisor' => $request->id_type_advisor
                                ]);
                                if ($created_credentials) {
                                    if ($request->id_role_credential == 3) {
                                        $client = self::$key->customers->create([
                                            "name" => $name_complete,
                                            "email" => $request->email,
                                            "phone" => $request->cell_phone_number
                                        ]);
                                        if ($client) {
                                            try {
                                                $last_credential_id = haytiro_credentials::latest('id_credential')->first();
                                                $id_credential = (int)$last_credential_id['id_credential'];
                                                haytiro_payment_control::insert([
                                                    'id_credential' => $id_credential,
                                                    'id_stripe'     => $client->id
                                                ]);
                                                return response()->json([
                                                    'status' => true,
                                                    'message' => 'User created successfully',
                                                ], 200);
                                            } catch (Exception $cb) {
                                                return response()->json([
                                                    'status' => false,
                                                    'message' =>  'An error ocurred during query: ' . $cb
                                                ], 200);
                                            }
                                        } else {
                                            return response()->json([
                                                'status' => false,
                                                'message' =>  'A problem has occurred with stripe'
                                            ], 200);
                                        }
                                    } else {
                                        return response()->json([
                                            'status' => true,
                                            'message' => 'User created successfully',
                                        ], 200);
                                    }
                                } else {
                                    return response()->json([
                                        'status' => false,
                                        'message' =>  'An error ocurred during query created credentials.'
                                    ], 200);
                                }
                            } catch (Exception $cb) {
                                return response()->json([
                                    'status' => false,
                                    'message' =>  'An error ocurred during query: ' . $cb
                                ], 200);
                            }
                        } else {
                            return response()->json([
                                'status' => false,
                                'message' =>  'An error ocurred during query created user.'
                            ], 200);
                        }
                    } catch (Exception $cb) {
                        return response()->json([
                            'status' => false,
                            'message' =>  'An error ocurred during query: ' . $cb
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' =>  'An error occurred, retry later.'
                    ], 200);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Email already registered',
                ], 200);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Currently registered user',
            ], 200);
        }
    }

    ///-------------------------Vistas------------------------------/////

    public function views_general($key_views)
    {
        try {
            $key_view = $key_views;
            $views_global = DB::connection('HayTiro')->select('select * from ' . self::$views[$key_view]);
            return response()->json([
                'status' => true,
                'data' =>  $views_global
            ], 200);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    public function view_customer_services($customer)
    {
        try {
            $services_customer = DB::connection('HayTiro')->table(self::$views['key_cs'])->where('id_credential', $customer)->get();
            return response()->json([
                'status' => true,
                'data' =>  $services_customer
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $th
            ], 200);
        }
    }

    ////---------- Actualización de clientes y/o asesores.------------//

    public function status_customer(Request $request)
    {
        $rules = [
            'customer' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        try {
            $cliente = haytiro_credentials::where('id_credential', $request->customer)->first();
            $status = haytiro_users::where('id_user', $cliente->id_user)->first();
            switch ($status->id_status_user) {
                case 1:
                    haytiro_users::where('id_user', $cliente->id_user)->update([
                        "id_status_user" => 3
                    ]);

                    if ($cliente->id_role_credential == 2) {
                        $mensaje = 'El asesor ' . $status->name_user . ' ha sido suspendido.';
                    } else {
                        $mensaje = 'El cliente ' . $status->name_user . ' ha sido suspendido.';
                    }

                    return response()->json([
                        'status' => true,
                        'message' => $mensaje,
                    ], 200);
                    break;
                case 3:
                    haytiro_users::where('id_user', $cliente->id_user)->update([
                        "id_status_user" => 1
                    ]);

                    if ($cliente->id_role_credential == 2) {
                        $mensaje = 'El asesor ' . $status->name_user . ' ha sido habilitado.';
                    } else {
                        $mensaje = 'El cliente ' . $status->name_user . ' ha sido habilitado.';
                    }

                    return response()->json([
                        'status' => true,
                        'message' => $mensaje,
                    ], 200);
                    break;
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => 'An error ocurred, try again: ' . $th,
            ], 200);
        }
    }

    public function advisor_detail(Request $request)
    {
        $rules = [
            'advisor' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        try {
            $advisor = DB::connection('HayTiro')->table(self::$views['key_ad'])->where('id_user', $request->advisor)->get();
            return response()->json([
                'status' => true,
                'data' =>  $advisor
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => 'An error ocurred, try again: ' . $th,
            ], 200);
        }
    }

    public function update_adviser(Request $request)
    {
        $rules = [
            'id' => 'required',
            'name' => 'required',
            'last_name' => 'required',
            'mother_last_name' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'birthdate' => 'required',
            'type' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        try {
            haytiro_users::where('id_user', $request->id)->update([
                'name_user'         => $request->name,
                'last_name'         => $request->last_name,
                'mother_last_name'  => $request->mother_last_name,
                'cell_phone_number' => $request->phone,
                'email'             => $request->email,
                'birthdate '        => $request->birthdate
            ]);
            haytiro_credentials::where('id_user', $request->id)->update([
                'id_type_advisor'   => $request->type
            ]);
            return response()->json([
                'status' => true,
                'message' => 'El asesor ' . $request->name . ' se ha actualizado.',
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => 'An error ocurred, try again: ' . $th,
            ], 200);
        }
    }

    ////----------Funciones globales------------//



    public static function created_cart_shop($data)
    {
        $last_credential_id = haytiro_credentials::latest('id_credential')->first();
        $id_credential = (int)$last_credential_id['id_credential'];
        try {
            haytiro_cart_shop::insert([
                'id_credential' => $id_credential,
                'id_status_cart' => 1
            ]);
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }


    public function cart_services_customer($id_credential)
    {
        if (!$id_credential) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            $cart_services = DB::connection('HayTiro')->select('exec cart_services_customer ?', [$id_credential]);
            if ($cart_services == false) {
                return response()->json([
                    'status' => false,
                    'message' => 'No results found',
                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'data' => $cart_services
                ], 200);
            }
        }
    }


    public function payment_method(Request $request)
    {
        $rules = [
            'id_credential' => 'required',
            'token'   => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        $cliente = haytiro_payment_control::where('id_credential', $request->id_user)->first();

        try {
            $card = self::$key->customers->createSource(
                strval($cliente->id_stripe),
                [
                    "source" => strval($request->token)
                ]
            );
            return response()->json([
                'status' => true,
                'message' =>  'Created payment method',
                'data'    => $card
            ], 200);
        } catch (Exception) {
            return response()->json([
                'status' => false,
                'message' =>  'Without results'
            ], 200);
        }
    }

    public function show_payment_method(Request $request)
    {
        $rules = [
            'id_credential' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        try {
            $cliente = haytiro_payment_control::where('id_credential', $request->id_credential)->first();
            $method = self::$key->customers->allSources(
                $cliente->id_stripe,
                [
                    "object" => "card"
                ]
            );
            return response()->json([
                'status' => true,
                'data'   => $method
            ], 200);
        } catch (Exception) {
            return response()->json([
                'status' => false,
                'message' =>  'Without results'
            ], 200);
        }
    }


    public function deleted_payment_method(Request $request)
    {
        $rules = [
            'id_credential' => 'required',
            'card'          => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        try {
            $cliente = haytiro_payment_control::where('id_credential', $request->id_credential)->first();
            self::$key->customers->deleteSource(
                $cliente->id_stripe,
                $request->card,
            );
            return response()->json([
                'status' => true,
                'message' =>  'Deleted payment method'
            ], 200);
        } catch (Exception) {
            return response()->json([
                'status' => false,
                'message' =>  'Without results'
            ], 200);
        }
    }

    private function calculateOrderAmount($id)
    {
        $cart = haytiro_cart_shop::where('id_credential', $id)->first();
        $carrito = DB::connection('HayTiro')->table('CartProductsServices')->where('id_cart_shop', $cart->id_cart_shop)->get();

        $i = 0;
        $productos = array();
        while ($i < sizeof($carrito)) {
            $productos[$i] = $carrito[$i]->subtotal;

            $i++;
        }
        $total = array_sum($productos);

        return $total . "00";
    }

    public function payment(Request $data)
    {
        $rules = [
            'id_credential' => 'required',
        ];
        $validator = Validator::make($data->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }

        try {
            $cliente = haytiro_payment_control::where('id_credential', $data->id_credential)->first();
            $credencial = haytiro_credentials::where('id_credential', $data->id_credential)->first();
            $usuario = haytiro_users::where('id_user', $credencial->id_user)->first();

            if ($cliente->id_payment != "" || $cliente->id_payment != null) {
                $paymentIntent = self::$key->paymentIntents->retrieve(
                    $cliente->id_payment
                );

                $secretKey = [
                    'clientSecret' => $paymentIntent->client_secret
                ];
            } else {
                $paymentIntent = self::$key->paymentIntents->create([
                    'customer' => strval($cliente->id_stripe),
                    'amount' => $this->calculateOrderAmount($data->id_credential),
                    'currency' => 'mxn',
                    'description' => 'Servicio de consulta jurídica de Hay Tiro.',
                    'receipt_email' => $usuario->email
                ]);

                haytiro_payment_control::where('id_credential', $data->id_credential)
                    ->update([
                        "id_payment" => $paymentIntent->id
                    ]);

                $secretKey = [
                    'clientSecret' => $paymentIntent->client_secret
                ];
            }

            return  $secretKey;
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred during request: " . $th
            ], 200);
        }
    }


    public function customer_products(Request $request)
    {
        $rules = [
            'id_services'   => 'required',
            'id_credential' => 'required'
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }

        try {
            $services = haytiro_services_view::where('id_status_service', 1)
                ->where('id_services', $request->id_services)->first();

            $carrito = haytiro_cart_shop::where('id_credential', $request->id_credential)->first();
            $cartClient = $carrito->id_cart_shop;

            $products_customer = haytiro_cart_products::where('id_cart_shop', $cartClient)
                ->where('id_services', $request->id_services)->get();
            if (sizeof($products_customer) == 0) {

                $paymentIntent = haytiro_payment_control::where('id_credential', $request->id_credential)->first();

                if ($paymentIntent->id_payment != "" || $paymentIntent->id_payment != null) {
                    $productos = haytiro_cart_products::where('id_cart_shop', $cartClient)->get();

                    $a = 0;
                    $productosExistentes = array();
                    while ($a < sizeof($productos)) {
                        $productosExistentes[$a] = $productos[$a]->subtotal;

                        $a++;
                    }

                    $subtotal = array_sum($productosExistentes);

                    $total = $subtotal + $services->price_service;

                    self::$key->paymentIntents->update(
                        $paymentIntent->id_payment,
                        [
                            'amount' => $total . "00",
                            'currency' => 'mxn'
                        ]
                    );
                }

                haytiro_cart_products::insert([
                    "id_services" => $request->id_services,
                    "amount" => 1,
                    "subtotal" => $services->price_service,
                    "id_cart_shop" => $cartClient
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Se ha agreado "' . $services->service_name . '" a su carrito.'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Este servicio ya está en tu carrito.'
                ], 200);
            }
        } catch (Exception $cb) {
            return response()->json([
                'status' => false,
                'message' =>  'An error ocurred during query: ' . $cb
            ], 200);
        }
    }

    ////----------Funciones servicios ------------//

    public function create_services(Request $data)
    {
        $rules = [
            'image_service'         => 'required',
            'service_name'          => 'required',
            'descrip_service'       => 'required',
            'price_service'         => 'required',
            'id_credential'         => 'required',
            'id_payment_modalities' => 'required',
            'id_payment_types'      => 'required',
            'id_type_services'      => 'required'
        ];

        $validator = Validator::make($data->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }

        try {
            haytiro_services::insert([
                'image_service'   => $data->image_service,
                'service_name'    => ucwords(strtolower($data->service_name)),
                'descrip_service' => $data->descrip_service,
                'price_service'   => $data->price_service,
                'id_credential'   => $data->id_credential,
                'id_payment_modalities' => $data->id_payment_modalities,
                'id_payment_types' => $data->id_payment_types,
                'id_type_services' => $data->id_type_services
            ]);

            return response()->json([
                'status' => true,
                'messsage' => "Service created."
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again." . $th
            ], 200);
        }
    }

    public function create_demand(Request $data)
    {
        $rules = [
            'demand_name'     => 'required',
            'id_type_advisor' => 'required'
        ];

        $validator = Validator::make($data->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }

        try {
            haytiro_demand::insert([
                'demand_name' => $data->demand_name,
                'id_advisor'  => $data->id_type_advisor
            ]);

            return response()->json([
                'status' => true,
                'message' => "Demand created."
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again."
            ], 200);
        }
    }



    public function details_chats_attended_view($id_chat)
    {
        if (!$id_chat) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            $details_chats_attended = DB::connection('HayTiro')->table('viewAttendedChats')->where('id_chat', $id_chat)->get();
            if ($details_chats_attended == false) {
                return response()->json([
                    'status' => false,
                    'message' => 'No results found',
                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'data' => $details_chats_attended
                ], 200);
            }
        }
    }

    public function details_chats_view($id_chat)
    {
        if (!$id_chat) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            $details_chats = DB::connection('HayTiro')->table('viewChats')->where('id_chat', $id_chat)->get();
            if ($details_chats == false) {
                return response()->json([
                    'status' => false,
                    'message' => 'No results found',
                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'data' => $details_chats
                ], 200);
            }
        }
    }

    public function deleted_services_cart($id_cart_product)
    {
        if (!$id_cart_product) {
            return response()->json([
                'status' => false,
                'message' => 'No results found',
            ], 200);
        } else {
            $cart_products = haytiro_cart_products::where('id_cart_products', $id_cart_product)->first();

            if ($cart_products == false) {
                return response()->json([
                    'status' => false,
                    'message' => 'No results found',
                ], 200);
            } else {

                $id_cart = $cart_products->id_cart_shop;
                $cart = haytiro_cart_shop::where('id_cart_shop', $id_cart)->first();

                haytiro_cart_products::where('id_cart_products', $id_cart_product)->delete();
                $cart_updated = haytiro_cart_products::where('id_cart_shop', $id_cart)->get();
                $payment = haytiro_payment_control::where('id_credential', $cart->id_credential)->first();

                if (sizeof($cart_updated) == 0 || $cart_updated == false) {
                    self::$key->paymentIntents->cancel($payment->id_payment);
                    haytiro_payment_control::where('id_credential', $cart->id_credential)->update([
                        "id_payment" => ""
                    ]);
                } else {
                    $a = 0;
                    $productosExistentes = array();
                    while ($a < sizeof($cart_updated)) {
                        $productosExistentes[$a] = $cart_updated[$a]->subtotal;
                        $a++;
                    }

                    $total = array_sum($productosExistentes);

                    self::$key->paymentIntents->update(
                        $payment->id_payment,
                        [
                            'amount' => $total . "00",
                            'currency' => 'mxn'
                        ]
                    );
                }

                return response()->json([
                    'status' => true,
                    'data' => 'Products deleted successfully'
                ], 200);
            }
        }
    }

    public function sendCv(Request $data)
    {
        $rules = [
            'name_post'     => 'required',
            'phone_post'    => 'required',
            'email_post'    => 'required',
            'cv_post'       => 'required'
        ];

        $validator = Validator::make($data->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        } else {
            $datos = [
                'nombre' => $data->name_post,
                'email'    => $data->email_post,
                'phone' => $data->phone_post,
                'cv' => $data->cv_post
            ];
            $email = send_email_global::cv($datos);

            if ($email['status'] == true) {
                return response()->json([
                    'status' => true,
                    'message' => 'CV send successfuly',
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'An error ocurred during the process.',
                ], 200);
            }
        }
    }

    ////---------- Actualización de servicios, tipos de servicio.------------//

    public function status_service(Request $request)
    {
        $rules = [
            "service" => "required"
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        try {
            $service = haytiro_services::where('id_services', $request->service)->first();
            switch ($service->id_status_service) {
                case 1:
                    haytiro_services::where('id_services', $request->service)->update([
                        "id_status_service" => 2
                    ]);
                    return response()->json([
                        'status' => true,
                        'message' => "Este servicio a sido deshabilitado"
                    ], 200);

                    break;

                case 2:
                    haytiro_services::where('id_services', $request->service)->update([
                        "id_status_service" => 1
                    ]);
                    return response()->json([
                        'status' => true,
                        'message' => "Este servicio a sido habilitado"
                    ], 200);

                    break;
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => "An error ocurred, try again: " . $th
            ], 200);
        }
    }

    public function service_detail(Request $request)
    {
        $rules = [
            'service' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        try {
            $service = DB::connection('HayTiro')->table(self::$views['key_sv'])->where('id_services', $request->service)->get();
            return response()->json([
                'status' => true,
                'data' =>  $service
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => 'An error ocurred, try again: ' . $th,
            ], 200);
        }
    }

    public function update_service(Request $request)
    {
        $rules = [
            'id_services' => 'required',
            'service_name' => 'required',
            'descrip_service' => 'required',
            'price_service' => 'required',
            'id_payment_modalities' => 'required',
            'id_type_services' => 'required',
            'id_payment_types' => 'required',
        ];
        $validator = Validator::make($request->input(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 400);
        }
        try {
            haytiro_services::where('id_services', $request->id_services)->update([
                'service_name'          => $request->service_name,
                'descrip_service'       => $request->descrip_service,
                'price_service'         => $request->price_service,
                'id_payment_modalities' => $request->id_payment_modalities,
                'id_type_services'      => $request->id_type_services,
                'id_payment_types '     => $request->id_payment_types
            ]);
            return response()->json([
                'status' => true,
                'message' => 'El servicio ' . $request->service_name . ' se ha actualizado.',
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => false,
                'message' => 'An error ocurred, try again: ' . $th,
            ], 200);
        }
    }
    
}
