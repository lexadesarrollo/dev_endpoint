<?php

use App\Http\Controllers\apprisa_controller;
use App\Http\Controllers\auth_sio;
use App\Http\Controllers\censo_controller;
use App\Http\Controllers\censo_controller_v2;
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
        Route::put('/updated_all_receipts_sio', [sio_controller::class, 'updated_receipts_all']);
        Route::put('/update_receipts_cia_sio', [sio_controller::class, 'update_receipts_cia']);
        Route::post('/detail_receipts_sio', [sio_controller::class, 'detail_receipts']);
        Route::put('/cancel_receipts_sio', [sio_controller::class, 'cancel_receipts']);

        //Rutas socios comanditarios
        Route::get('/partners_sio', [sio_controller::class, 'ctl_partners']);
        Route::get('/doc_partners_global_sio', [sio_controller::class, 'ctl_doc_partners_global']);
        Route::get('/partners_general_sio', [sio_controller::class, 'ctl_partners_general']);
        Route::post('/details_partners_sio', [sio_controller::class, 'detail_partners']);
        Route::post('/created_partners_sio', [sio_controller::class, 'created_partners']);
        Route::put('/updated_partners_sio', [sio_controller::class, 'updated_partners']);
        Route::put('/updated_status_partners_sio', [sio_controller::class, 'updated_status_partners']);


        //Rutas tipo de usuarios
        Route::get('/roles_sio', [sio_controller::class, 'ctl_roles']);
        Route::post('/create_role_sio', [sio_controller::class, 'created_role']);
        Route::put('/updated_descrip_role_sio', [sio_controller::class, 'updated_descrip_role']);

        //Rutas compañias
        Route::get('/cia_sio', [sio_controller::class, 'ctl_cia']);
        Route::post('/create_cia_sio', [sio_controller::class, 'create_cia']);
        Route::post('/details_cia_sio', [sio_controller::class, 'detail_cia']);
        Route::put('/updated_status_cia_sio', [sio_controller::class, 'updated_status_cia']);
        Route::put('/updated_cia_sio', [sio_controller::class, 'updated_cia_details']);

        //Rutas estados de cuenta
        Route::get('/states_account_sio', [sio_controller::class, 'ct_states_accounts']);

        //Rutas municipios
        Route::post('/municipality_sio', [sio_controller::class, 'ctl_municipality']);
    });

    Route::prefix('apprisa')->group(function () {
        Route::post('/create_user_apprisa', [apprisa_controller::class, 'create_user']);
        Route::put('/status_user_apprisa', [apprisa_controller::class, 'status_user']);
        Route::post('/code_2fa_apprisa', [apprisa_controller::class, 'TwoFA_auth_code']);
        Route::post('/autorize_TwoFA_apprisa', [apprisa_controller::class, 'autorize_TwoFA']);
        Route::get('/all_admin_apprisa', [apprisa_controller::class, 'getAllAdmins']);
        Route::get('/all_geofences_apprisa', [apprisa_controller::class, 'getAllGeofences']);
        Route::get('/active_geofences_apprisa', [apprisa_controller::class, 'getActiveGeofences']);
        Route::post('/create_geofence_apprisa', [apprisa_controller::class, 'create_geofence']);
        Route::put('/status_geofence_apprisa', [apprisa_controller::class, 'status_geofence']);
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
        Route::put('/status_customer_haytiro', [haytiro_controller::class, 'status_customer']);
        Route::post('/adviser_details_haytiro', [haytiro_controller::class, 'advisor_detail']);
        Route::put('/update_adviser_haytiro', [haytiro_controller::class, 'update_adviser']);
        Route::put('/status_service_haytiro', [haytiro_controller::class, 'status_service']);
        Route::post('/service_detail_haytiro', [haytiro_controller::class, 'service_detail']);
        Route::put('/update_service_haytiro', [haytiro_controller::class, 'update_service']);
    });
});

Route::prefix('censoApp-v2')->group(function () {
    //Rutas de catalogos
    //-----------------
    //      Status
    //-----------------
    Route::get('/ctl_status_censo',        [censo_controller_v2::class, 'ctl_status']);
    Route::post('/created_status_censo', [censo_controller_v2::class, 'created_status']);
    Route::put('/updated_status_censo', [censo_controller_v2::class, 'updated_status']);
    //-----------------
    //      Role
    //-----------------
    Route::get('/ctl_role_censo',          [censo_controller_v2::class, 'ctl_role']);
    Route::post('/created_role_censo', [censo_controller_v2::class, 'created_role']);
    Route::post('/detail_role_censo', [censo_controller_v2::class, 'detail_role']);
    Route::put('/updated_role_censo', [censo_controller_v2::class, 'updated_role']);
    Route::put('/updated_status_role_censo', [censo_controller_v2::class, 'updated_status_role']);
    //-----------------
    //      Company
    //-----------------
    Route::get('/ctl_company_censo',       [censo_controller_v2::class, 'ctl_company']);
    Route::post('/created_company_censo', [censo_controller_v2::class, 'created_company']);
    Route::post('/detail_company_censo', [censo_controller_v2::class, 'detail_company']);
    Route::put('/updated_status_company_censo', [censo_controller_v2::class, 'updated_status_company']);
    Route::put('/updated_company_censo', [censo_controller_v2::class, 'updated_company']);
    //-----------------
    //      Lada
    //-----------------
    Route::get('/ctl_lada_censo',          [censo_controller_v2::class, 'ctl_lada']);
    Route::post('/created_lada_censo', [censo_controller_v2::class, 'created_lada']);
    Route::post('/detail_lada_censo', [censo_controller_v2::class, 'detail_lada']);
    Route::put('/updated_status_lada_censo', [censo_controller_v2::class, 'updated_status_lada']);
    Route::put('/updated_lada_censo', [censo_controller_v2::class, 'updated_lada']);
    //-----------------
    //      Type Business
    //-----------------
    Route::get('/ctl_type_business_censo', [censo_controller_v2::class, 'ctl_type_business']);
    Route::post('/created_type_business_censo', [censo_controller_v2::class, 'created_type_business']);
    Route::post('/detail_type_business_censo', [censo_controller_v2::class, 'detail_type_business']);
    Route::put('/updated_status_type_business_censo', [censo_controller_v2::class, 'updated_status_type_business']);
    Route::put('/updated_type_business_censo', [censo_controller_v2::class, 'updated_type_business']);
    //-----------------
    //      State
    //-----------------
    Route::get('/ctl_state_censo',         [censo_controller_v2::class, 'ctl_state']);
    Route::post('/created_state_censo', [censo_controller_v2::class, 'created_state']);
    Route::post('/detail_state_censo', [censo_controller_v2::class, 'detail_state']);
    Route::put('/updated_status_state_censo', [censo_controller_v2::class, 'updated_status_state']);
    Route::put('/updated_state_censo', [censo_controller_v2::class, 'updated_state']);
    //-----------------
    //      Municipality
    //-----------------
    Route::get('/ctl_municipality_censo',  [censo_controller_v2::class, 'ctl_municipality']);
    Route::post('/created_municipality_censo', [censo_controller_v2::class, 'created_municipality']);
    Route::post('/detail_municipality_censo', [censo_controller_v2::class, 'detail_municipality']);
    Route::put('/updated_municipality_censo', [censo_controller_v2::class, 'updated_municipality']);
    Route::put('/updated_status_municipality_censo', [censo_controller_v2::class, 'updated_status_municipality']);
    //-----------------
    //      Roads
    //-----------------
    Route::get('/ctl_roads_censo',         [censo_controller_v2::class, 'ctl_roads']);
    Route::post('/created_roads_censo', [censo_controller_v2::class, 'created_roads']);
    Route::post('/detail_roads_censo', [censo_controller_v2::class, 'detail_roads']);
    Route::put('/updated_roads_censo', [censo_controller_v2::class, 'updated_roads']);
    Route::put('/updated_status_roads_censo', [censo_controller_v2::class, 'updated_status_roads']);
    //-----------------
    //      Settlements
    //-----------------
    Route::get('/ctl_settlements_censo',   [censo_controller_v2::class, 'ctl_settlements']);
    Route::post('/created_settlements_censo', [censo_controller_v2::class, 'created_settlements']);
    Route::post('/detail_settlements_censo', [censo_controller_v2::class, 'detail_settlements']);
    Route::put('/updated_settlements_censo', [censo_controller_v2::class, 'updated_settlements']);
    Route::put('/updated_status_settlements_censo', [censo_controller_v2::class, 'updated_status_settlements']);
    //Rutas de tablas
    //-----------------
    //      Users
    //-----------------
    Route::get('/tbl_users_censo',                 [censo_controller_v2::class, 'tbl_users']);
    Route::post('/created_users_censo', [censo_controller_v2::class, 'created_users']);
    Route::post('/detail_users_censo', [censo_controller_v2::class, 'detail_users']);
    Route::put('/updated_user_censo', [censo_controller_v2::class, 'updated_user']);
    Route::put('/updated_status_user_censo', [censo_controller_v2::class, 'updated_status_user']);
    //-----------------
    //      Credentials
    //-----------------
    Route::get('/tbl_credentials_censo',   [censo_controller_v2::class, 'tbl_credentials']);
    Route::put('/recover_password_censo',   [censo_controller_v2::class, 'recover_password']);
    Route::put('/updated_status_credentials_censo', [censo_controller_v2::class, 'updated_status_credentials']);
    Route::post('/detail_credentials_censo', [censo_controller_v2::class, 'detail_credentials']);
    //-----------------
    //      Device User
    //-----------------
    Route::get('/tbl_device_user_censo',   [censo_controller_v2::class, 'tbl_device_user']);
    Route::post('/created_device_user_censo',   [censo_controller_v2::class, 'created_device_user']);
    Route::put('/updated_device_user_censo', [censo_controller_v2::class, 'updated_device_user']);
    Route::post('/detail_device_user_censo', [censo_controller_v2::class, 'detail_device_user']);
    //-----------------
    //      Registered Businesses
    //-----------------
    Route::get('/tbl_registered_businesses_censo', [censo_controller_v2::class, 'tbl_registered_businesses']);
    //-----------------
    //      Commissions
    //-----------------
    Route::get('/tbl_commissions_censo',   [censo_controller_v2::class, 'tbl_commissions']);
});
