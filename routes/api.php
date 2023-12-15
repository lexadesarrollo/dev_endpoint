<?php

use App\Http\Controllers\apprisa_controller;
use App\Http\Controllers\auth_sio;
use App\Http\Controllers\censo_controller;
use App\Http\Controllers\haytiro_controller;
use App\Http\Controllers\sc_islasg_controller;
use App\Http\Controllers\sio_controller;
use Illuminate\Support\Facades\Route;

Route::post('auth/register', [auth_sio::class, 'create']);
Route::post('auth/login', [auth_sio::class, 'login']);
Route::post('auth/censo/login', [censo_controller::class, 'login_users_censo']);
Route::post('אָטענטאַקייט/שליסלען', [auth_sio::class, 'סימען']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('sio')->group(function () {
        //Rutas status
        Route::get('/status_sio', [sio_controller::class, 'ctl_status_all']);
        Route::get('/details_status_sio/{id_status}', [sio_controller::class, 'detail_status']);
        Route::post('/create_status_sio', [sio_controller::class, 'created_status']);
        Route::put('/updated_status_sio', [sio_controller::class, 'updated_status']);

        //Rutas tipo de archivos
        Route::get('/types_files_sio', [sio_controller::class, 'ctl_type_file']);
        Route::post('/details_type_files_sio', [sio_controller::class, 'detail_type_file']);
        Route::post('/created_type_files_sio', [sio_controller::class, 'created_type_file']);
        Route::put('/updated_status_type_file_sio', [sio_controller::class, 'updated_status_type_file']);

        //Rutas bancos
        Route::get('/view_banks_sio', [sio_controller::class, 'view_banks']);
        Route::post('/details_banks_sio', [sio_controller::class, 'detail_bank']);
        Route::post('/create_bank_sio', [sio_controller::class, 'created_banks']);
        Route::put('/updated_bank_sio', [sio_controller::class, 'updated_bank']);
        Route::put('/updated_status_bank_sio', [sio_controller::class, 'updated_status_banks']);

        //Rutas cuentas origen
        Route::get('/view_origin_account_sio', [sio_controller::class, 'ctl_account_origin']);
        Route::post('/create_source_account_sio', [sio_controller::class, 'create_account_origin']);
        Route::post('/details_origin_account_sio', [sio_controller::class, 'detail_origin_account']);
        Route::put('/updated_status_origin_account_sio', [sio_controller::class, 'updated_status_origin_account']);
        Route::put('/updated_origin_account_sio', [sio_controller::class, 'updated_origin_account']);

        //Rutas empleaos
        Route::post('/create_employees_sio', [sio_controller::class, 'created_employees']);

        //Rutas recibos 
        Route::get('/receipts_sio', [sio_controller::class, 'ctl_receipts']);
        Route::get('/receipts_complete_sio', [sio_controller::class, 'view_receipts_complete']);
        Route::get('/receipts_incomplete_sio', [sio_controller::class, 'view_receipts_incomplete']);

        //Rutas socios comanditarios
        Route::get('/partners_sio', [sio_controller::class, 'ctl_partners']);
        Route::get('/doc_partners_global_sio', [sio_controller::class, 'ctl_doc_partners_global']);
        Route::get('/partners_general_sio', [sio_controller::class, 'ctl_partners_general']);
        Route::post('/details_partners_sio', [sio_controller::class, 'detail_partners']);


        //Rutas tipo de usuarios
        Route::get('/roles_sio', [sio_controller::class, 'ctl_roles']);
        Route::post('/create_role_sio', [sio_controller::class, 'created_role']);
        Route::put('/updated_descrip_role_sio', [sio_controller::class, 'updated_descrip_role']);

        //Rutas compañias
        Route::get('/cia_sio', [sio_controller::class, 'ctl_cia']);
        Route::post('/create_cia_sio', [sio_controller::class, 'create_cia']);
        Route::post('/details_cia_sio', [sio_controller::class, 'detail_cia']);
        Route::put('/updated_status_cia_sio', [sio_controller::class, 'updated_status_cia']);
        Route::put('/updated_cia_sio', [sio_controller::class, 'updated_cia']);

         //Rutas estados de cuenta
         Route::get('/states_account_sio', [sio_controller::class, 'ct_states_accounts']);

         //Rutas municipios
         Route::get('/municipality_sio', [sio_controller::class, 'ctl_municipality']);
    });

    Route::prefix('apprisa')->group(function () {
        Route::post('/crearUsuario/', [apprisa_controller::class, 'createUser']);
        Route::get('/restaurante/', [apprisa_controller::class, 'getRestaurante']);
        Route::post('/createStatus/', [apprisa_controller::class, 'createStatus']);
        Route::get('/usuario/', [apprisa_controller::class, 'getUser']);
        Route::post('/crearGeocerca/', [apprisa_controller::class, 'createGeofence']);
        Route::get('/verGeocercas/', [apprisa_controller::class, 'viewGeofences']);
        Route::get('/verTGeocercas/', [apprisa_controller::class, 'viewAllGeofences']);
        Route::post('/crearZona/', [apprisa_controller::class, 'createZone']);
        Route::post('/disableGeofence/', [apprisa_controller::class, 'update']);
    });
    Route::prefix('sc_islasg')->group(function () {
        Route::get('/ocrImage/', [sc_islasg_controller::class, 'ocr']);
        Route::get('/municipios/{state}', [sc_islasg_controller::class, 'municipios']);
    });

    Route::prefix('censo')->group(function () {
        Route::get('/status', [censo_controller::class, 'ctl_status']);
        Route::get('/role', [censo_controller::class, 'ctl_role']);
        Route::get('/view_role', [censo_controller::class, 'view_ctl_role']);
        Route::get('/types_business', [censo_controller::class, 'ctl_types_business']);
        Route::get('/view_types_business', [censo_controller::class, 'view_ctl_types_business']);
        Route::get('/registered_businesses', [censo_controller::class, 'tbl_registered_businesses']);
        Route::get('/user_commissions', [censo_controller::class, 'tbl_user_commissions']);
        Route::post('/create_users', [censo_controller::class, 'create_users']);
        Route::post('/device_info_users', [censo_controller::class, 'create_devices_user']);
        Route::post('/create_bussines', [censo_controller::class, 'create_bussnines']);
    });


    Route::prefix('haytiro')->group(function () {
        Route::post('/created_users_haytiro', [haytiro_controller::class, 'created_users']);
        Route::get('/views_general_haytiro/{key_views}', [haytiro_controller::class, 'views_general']);
        Route::get('/view_customer_services/{customer}', [haytiro_controller::class, 'view_customer_services']);
        Route::get('/view_cart_services_customer_haytiro/{id_credential}', [haytiro_controller::class, 'cart_services_customer']);
        Route::get('/details_customers_haytiro/{id_credential}', [haytiro_controller::class, 'details_customer_view']);
        Route::get('/details_adviser_haytiro/{id_credential}', [haytiro_controller::class, 'details_adviser_view']);
        Route::get('/details_chats_attended_haytiro/{id_chat}', [haytiro_controller::class, 'details_chats_attended_view']);
        Route::get('/details_chats_haytiro/{id_chat}', [haytiro_controller::class, 'details_chats_view']);
        Route::get('/deleted_services_cart_haytiro/{id_product}', [haytiro_controller::class, 'deleted_services_cart']);
        Route::post('/payment_method_haytiro', [haytiro_controller::class, 'payment_method']);
        Route::post('/payment_intent', [haytiro_controller::class, 'payment']);
        Route::post('/show_payment_method_haytiro', [haytiro_controller::class, 'show_payment_method']);
        Route::post('/customer_products_haytiro', [haytiro_controller::class, 'customer_products']);
        Route::post('/create_service_haytiro', [haytiro_controller::class, 'create_services']);
        Route::post('/create_demand_haytiro', [haytiro_controller::class, 'create_demand']);
        Route::post('/send_cv_haytiro', [haytiro_controller::class, 'sendCv']);
    });
});
