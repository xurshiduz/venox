<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

Route::get('/checkout_today_send_public', 'Backend\CheckoutController@today_send')->name('checkout_today_send_public');

Route::group(
[
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localizationRedirect', 'localeViewPath' ]
], function(){ 

    Route::middleware(['auth:sanctum', 'checkstatus', config('jetstream.auth_session')])->group(function () {
        //Home
        Route::get('/', 'Backend\HomeController@index')->name('home');
        
        Route::get('/inv_print/{id?}', 'Backend\HomeController@inv_index')->name('inv_index');
        Route::get('/inv_excel/{id?}', 'Backend\HomeController@inv_excel')->name('inv_excel');
        Route::get('/del_in', 'Backend\HomeController@del_in')->name('del_in');
        Route::get('/ware_id', 'Backend\HomeController@ware_id')->name('ware_id');
        Route::post('/iscompact', 'Backend\HomeController@iscompact')->name('iscompact');
        Route::get('/dashboard', 'Backend\DashboardController@index')->name('dashboard');
        Route::get('/dashboard_print', 'Backend\DashboardController@print')->name('dashboard_print');
        Route::get('/dashboard_new', 'Backend\DashboardController@dashboard_new')->name('dashboard_new');
        Route::get('/dashboard_month/{type}', 'Backend\DashboardController@dashboard_month')->name('dashboard_month');
        Route::get('/inventory_report', 'Backend\DashboardController@inventory_report')->name('inventory_report');
        Route::post('/inventory_report', 'Backend\DashboardController@inventory_report')->name('inventory_report_post');
        //API
        Route::get('/topclienttwo_api', 'Backend\DashboardController@topclienttwo_api')->name('topclienttwo_api');
        //API Warehouse
        Route::post('/warehouse_blocks_api', 'Backend\WarehouseController@block_api')->name('warehouse_blocks_api');
        Route::post('/warehouse_block_cells_api', 'Backend\WarehouseController@block_cell_api')->name('warehouse_block_cells_api');
        //Display
        Route::get('/display', 'Backend\HomeController@display')->name('display');
        Route::post('/display_send', 'Backend\HomeController@display_send')->name('display_send');
        Route::post('/display_refresh', 'Backend\HomeController@display_refresh')->name('display_refresh');
        
        
        //Filter
        Route::get('/filter_index', 'Backend\FilterController@index')->name('filter_index');
        Route::get('/filter_param', 'Backend\FilterController@filter')->name('filter_param');
        
        Route::get('/kpi_plans', 'Backend\KpiController@plan_index')->name('kpi_plan_index');
        Route::get('/kpi_plan_show/{id}', 'Backend\KpiController@plan_index_show')->name('plan_index_show');
        Route::get('/kpi_plan/form/{id?}', 'Backend\KpiController@form')->name('kpi_form');
        Route::post('/kpi_plan/form/{id?}', 'Backend\KpiController@save');
        Route::get('/kpi_plan_excel/{id}', 'Backend\KpiController@excel')->name('kpi_plan_excel');
        Route::get('/kpi_plan_print/{id}', 'Backend\KpiController@print')->name('kpi_plan_print');
        Route::post('/kpi_plan_filter/{id}', 'Backend\KpiController@postFilter')->name('kpi_plan_filter');
    
        Route::middleware(['role:admin|report|dealer_admin'])->group(function () {
            Route::get('/warehouse_filter', 'Backend\HomeController@warehouse_filter')->name('warehouse_filter');
            Route::post('/warehouse_filter_post', 'Backend\HomeController@warehouse_filter_post')->name('warehouse_filter_post');
            
            Route::get('/top_client_filter', 'Backend\HomeController@top_client_filter')->name('top_client_filter');
            Route::post('/top_client_filter_post', 'Backend\HomeController@top_client_filter_post')->name('top_client_filter_post');
            
            Route::get('/report_top/{id}', 'Backend\HomeController@report_top_all')->name('report_top_all');
            Route::get('/report_category', 'Backend\HomeController@report_category')->name('report_category');
            Route::get('/activitie', 'Backend\HomeController@activitie')->name('activitie');
            Route::get('/chart', 'Backend\HomeController@chart')->name('chart');
            Route::get('/report_unsold', 'Backend\HomeController@report_unsold')->name('report_unsold');
            
            Route::get('/reconciliation_act', 'Backend\HomeController@reconciliation_act')->name('reconciliation_act');
            Route::post('/reconciliation_act_print', 'Backend\HomeController@reconciliation_act_post')->name('reconciliation_act_post');
    
            //Account
            Route::get('/get_change_client_id', 'Backend\CashReceiptController@get_change_client_id')->name('get_change_client_id');  
            Route::get('/cash_receipts', 'Backend\CashReceiptController@index')->name('cash_receipts_index');  
            Route::get('/cash_receipts_his', 'Backend\CashReceiptController@index_his')->name('cash_receipts_index_his');   
            Route::get('/cash_receipts_excel', 'Backend\CashReceiptController@excel')->name('cash_receipts_excel');
            Route::get('/cash_receipt/form/{id?}/{page?}/{checkout?}', 'Backend\CashReceiptController@form')->name('cash_receipt_form');
            Route::post('/cash_receipt/form/{id?}/{page?}/{checkout?}', 'Backend\CashReceiptController@save');
            Route::get('/cash_receipt/{id}/status/{page?}/{checkout?}', 'Backend\CashReceiptController@status')->name('cash_receipt_status');
    
            //Warehouse
            Route::get('/warehouses', 'Backend\WarehouseController@index')->name('warehouses_index');
            Route::post('/warehouses', 'Backend\WarehouseController@search')->name('warehouses_search');
            Route::get('/warehouses_report_print', 'Backend\WarehouseController@report_print')->name('warehouses_report_print');
            Route::get('/warehouses_one_print/{id}', 'Backend\WarehouseController@one_print')->name('warehouses_one_print');
            Route::get('/warehouse/form/{id?}', 'Backend\WarehouseController@form')->name('warehouse_form');
            Route::post('/warehouse/form/{id?}', 'Backend\WarehouseController@save');
            Route::get('/warehouse/{id}/status', 'Backend\WarehouseController@status')->name('warehouse_status');
            //Full warehouse
            Route::get('/warehouse_select/{warehouse?}/{block?}/{row?}/{column?}/{floor?}', 'Backend\WarehouseController@warehouse_select')->name('warehouse_select');
            Route::get('/warehouse_stock_excel/{id}', 'Backend\WarehouseController@warehouse_stock')->name('warehouses_stock_excel');
            Route::get('/warehouse_stock_excel_param/{id}/{take}/{pag}', 'Backend\WarehouseController@warehouse_stock_param')->name('warehouses_stock_excel_param');
            Route::get('/warehouse_stock_excel_input/', 'Backend\WarehouseController@warehouse_stock_input')->name('warehouses_stock_excel_input');
            Route::get('/warehouse_excel_product_list/{id}', 'Backend\WarehouseController@excel_product_list')->name('warehouses_excel_product_list');
            //Inventory
            Route::get('/warehouse_inventory/{id}', 'Backend\WarehouseController@warehouse_inventory')->name('warehouse_inventory');
            //Blocks
            Route::get('/warehouse_blocks/{id}', 'Backend\WarehouseController@warehouse_blocks')->name('warehouse_blocks');
            Route::post('/warehouse_blocks/{id}', 'Backend\WarehouseController@warehouse_blocks_save');
            Route::get('/warehouse_stock_old', 'Backend\WarehouseController@warehouse_stock_old')->name('warehouse_stock_old');
            Route::get('/warehouse_stock_refresh/{id}', 'Backend\WarehouseController@warehouse_stock_refresh')->name('warehouse_stock_refresh');
            //END Warehouse
    
            //Currency
            Route::get('/currencies', 'Backend\CurrencyController@index')->name('currencies_index');    
            Route::get('/currency/form/{id?}', 'Backend\CurrencyController@form')->name('currency_form');
            Route::post('/currency/form/{id?}', 'Backend\CurrencyController@save');
            //TYPE
            Route::get('/currency_type/form/{id?}', 'Backend\CurrencyController@currency_type_form')->name('currency_type_form');
            Route::post('/currency_type/form/{id?}', 'Backend\CurrencyController@currency_type_save');
            //END Currency
    
            //Category
            Route::get('/categories', 'Backend\CategoryController@index')->name('categories_index');
            Route::post('/categories', 'Backend\CategoryController@search')->name('categories_search');
            Route::get('/category/form/{id?}', 'Backend\CategoryController@form')->name('category_form');
            Route::post('/category/form/{id?}', 'Backend\CategoryController@save');
            Route::get('/category/{id}/status', 'Backend\CategoryController@status')->name('category_status');
            Route::get('/category/{id}/report', 'Backend\CategoryController@report')->name('category_report');
            Route::get('/category/import', 'Backend\CategoryController@import_form')->name('category_import');
            Route::post('/category/import', 'Backend\CategoryController@import_save');
            //END Category
            
            //Setting
            Route::get('/settings', 'Backend\SettingController@index')->name('settings_index');
            Route::get('/setting/form/{id?}', 'Backend\SettingController@form')->name('setting_form');
            Route::post('/setting/form/{id?}', 'Backend\SettingController@save');
    
            //Unit
            Route::get('/units', 'Backend\UnitController@index')->name('units_index');
            Route::post('/units', 'Backend\UnitController@search')->name('units_search');
            Route::get('/unit/form/{id?}', 'Backend\UnitController@form')->name('unit_form');
            Route::post('/unit/form/{id?}', 'Backend\UnitController@save');
            Route::get('/unit/{id}/status', 'Backend\UnitController@status')->name('unit_status');
            //END Unit
            
            //CashReceiptTypeController
            Route::get('/cash_receipt_types', 'Backend\CashReceiptTypeController@index')->name('cash_receipt_types_index');
            Route::get('/cash_receipt_type/form/{id?}', 'Backend\CashReceiptTypeController@form')->name('cash_receipt_type_form');
            Route::post('/cash_receipt_type/form/{id?}', 'Backend\CashReceiptTypeController@save');
            Route::get('/cash_receipt_type/{id}/status', 'Backend\CashReceiptTypeController@status')->name('cash_receipt_type_status');
            //END CashReceiptTypeController
            
            //CashReceiptTypeController
            Route::get('/checkout_types', 'Backend\CheckoutTypeController@index')->name('checkout_types_index');
            Route::get('/checkout_type/form/{id?}', 'Backend\CheckoutTypeController@form')->name('checkout_type_form');
            Route::post('/checkout_type/form/{id?}', 'Backend\CheckoutTypeController@save');
            Route::get('/checkout_type/{id}/status', 'Backend\CheckoutTypeController@status')->name('checkout_type_status');
            //END CashReceiptTypeController
    
            //Dealer
            Route::get('/dealers', 'Backend\DealerController@index')->name('dealers_index');
            Route::post('/dealers', 'Backend\DealerController@search')->name('dealers_search');
            Route::get('/dealer/form/{id?}', 'Backend\DealerController@form')->name('dealer_form');
            Route::post('/dealer/form/{id?}', 'Backend\DealerController@save');
            Route::get('/dealer/{id}/status', 'Backend\DealerController@status')->name('dealer_status');
            //END Dealer
        });
    
        //Client
        Route::get('/clients', 'Backend\ClientController@index')->name('clients_index');
        Route::get('/client_search', 'Backend\ClientController@index_search')->name('clients_search');
        Route::get('/client/form/{id?}', 'Backend\ClientController@form')->name('client_form');
        Route::post('/client/form/{id?}', 'Backend\ClientController@save');
        Route::get('/client/{id}/status', 'Backend\ClientController@status')->name('client_status');
        Route::get('/client/import', 'Backend\ClientController@import_form')->name('client_import');
        Route::post('/client/import', 'Backend\ClientController@import_save');
        Route::get('/client_checkouts/{id}', 'Backend\ClientController@checkouts')->name('client_checkouts');
        Route::get('/client_checkins/{id}', 'Backend\ClientController@checkins')->name('client_checkins');
        //Balance
        Route::get('/client_balances', 'Backend\ClientController@balance_index')->name('clients_balance_index');
        Route::get('/client_hisall_balances', 'Backend\ClientController@balance_hisall_index')->name('clients_balance_hisall_index');
        Route::get('/client_history_balance/{id}', 'Backend\ClientController@balance_history_index')->name('clients_balance_history_index');
        Route::get('/client_balance_search', 'Backend\ClientController@index_balance_search')->name('clients_balance_search');
        Route::get('/client_balance/form/{id?}', 'Backend\ClientController@balance_form')->name('client_balance_form');
        Route::post('/client_balance/form/{id?}', 'Backend\ClientController@balance_save')->name('balance_save');
        Route::get('/client_balance/{id}/status', 'Backend\ClientController@balance_status')->name('client_balance_status');
        Route::get('/client_delete/{id}', 'Backend\ClientController@delete')->name('client_delete');
        //API
        Route::post('/api_clients', 'Backend\ClientController@api_clients')->name('api_clients');
        //END Client
    
        //Supplier
        Route::get('/suppliers', 'Backend\SupplierController@index')->name('suppliers_index');
        Route::post('/suppliers', 'Backend\SupplierController@index_search')->name('suppliers_search');
        Route::get('/supplier/form/{id?}', 'Backend\SupplierController@form')->name('supplier_form');
        Route::post('/supplier/form/{id?}', 'Backend\SupplierController@save');
        Route::get('/supplier/{id}/status', 'Backend\SupplierController@status')->name('supplier_status');
        Route::get('/supplier/import', 'Backend\SupplierController@import_form')->name('supplier_import');
        Route::post('/supplier/import', 'Backend\SupplierController@import_save');
        //END Supplier
    
        //Product
        Route::get('/products/{archive?}', 'Backend\ProductController@index')->name('products_index');
        Route::get('/product_stock', 'Backend\ProductController@stock')->name('products_stock');
        Route::get('/product_stock_all', 'Backend\ProductController@stock_all')->name('products_stock_all');
        Route::get('/products_saerch', 'Backend\ProductController@search')->name('products_search');
        Route::get('/products_stock_search', 'Backend\ProductController@stock_search')->name('products_stock_search');
        Route::post('/product_view/{id}', 'Backend\ProductController@index_view')->name('products_view');
        Route::get('/product/form/{id?}/{page?}', 'Backend\ProductController@form')->name('product_form');
        Route::post('/product/form/{id?}/{page?}', 'Backend\ProductController@save');
        Route::get('/product/{id}/status', 'Backend\ProductController@status')->name('product_status');
        Route::get('/product_print/{id}/{hmm}/{vmm}', 'Backend\ProductController@print')->name('product_print');
        Route::get('/product_barcode', 'Backend\ProductController@barcode_form')->name('product_barcode');
        Route::post('/product_barcode', 'Backend\ProductController@barcode_save');
        Route::get('/product_delete/{id}/{page?}', 'Backend\ProductController@delete')->name('product_delete');
        //API
        Route::post('/api_products', 'Backend\ProductController@api_index')->name('products_api');
        Route::get('/products_new_search', 'Backend\ProductController@new_search')->name('products_new_search');
        //
        Route::get('/product_block_view/{id?}/{page?}', 'Backend\ProductController@block_form')->name('product_block_form');
        Route::post('/product_block_view/{id?}/{page?}', 'Backend\ProductController@block_save');
        Route::get('/product_block_delete/{id?}', 'Backend\ProductController@block_delete')->name('product_block_delete');
        //Stock check
        Route::get('/product_stock_check', 'Backend\ProductController@stock_check')->name('product_stock_check');
        //
        Route::get('/product_reports', 'Backend\ProductController@product_report_form')->name('product_report_form');
        Route::get('/product_reports/most-sold-products', 'Backend\ProductController@product_report_save')->name('product_report_save');
        Route::get('/check-barcode', 'Backend\ProductController@checkBarcode')->name('check.barcode');
        //END Product
        
        //Adjdjustment
        Route::get('/stock_adjustments', 'Backend\AdjustmentController@index')->name('stock_adjustment_index');
        Route::post('/stock_adjustment', 'Backend\AdjustmentController@save')->name('stock_adjustment_save');
        //END Adjdjustment
        
        //My Profile
        Route::get('myprofile', 'Backend\UserController@m_form')->name('myprofile_form');
        Route::post('myprofile', 'Backend\UserController@m_save');
        //My Profile
        Route::get('mypassword', 'Backend\UserController@p_form')->name('mypassword_form');
        Route::post('mypassword', 'Backend\UserController@p_save');
        //User Lock and Unlock
        Route::get('/lock_user/{id}', 'Backend\UserController@lock_user')->name('lock_user');
        Route::get('/theme/{id}/status', 'Backend\UserController@theme')->name('theme_user');
        Route::get('/unlock_user/{id}', 'Backend\UserController@unlock_user')->name('unlock_user');
        //User
        Route::get('/users', 'Backend\UserController@index')->name('users_index');
        Route::get('/user_checkouts/{id}', 'Backend\UserController@checkouts')->name('user_checkouts');
        
        Route::get('/user_noactive', 'Backend\UserController@noactive')->name('users_noactive');
        Route::get('/user_roles', 'Backend\UserController@role')->name('users_role');
        Route::get('/user/form/{id?}', 'Backend\UserController@form')->name('user_form');
        Route::post('/user/form/{id?}', 'Backend\UserController@save');
        Route::get('/user/{id}/status', 'Backend\UserController@status')->name('user_status');
        //END User
    
        Route::get('/checkin_sverka', 'Backend\CheckinController@sverka_index')->name('checkins_sverka_index');
        Route::post('/checkin_sverka_excel', 'Backend\CheckinController@sverka_excel')->name('checkins_sverka_excel');
        //Checkin
        Route::get('/checkins', 'Backend\CheckinController@index')->name('checkins_index');
        Route::post('/checkins', 'Backend\CheckinController@search')->name('checkins_search');
        Route::get('/checkin/form/{id?}', 'Backend\CheckinController@form')->name('checkin_form');
        Route::post('/checkin/form/{id?}', 'Backend\CheckinController@save');
        Route::post('/checkin_update', 'Backend\CheckinController@qty')->name('qty_checkin');
        Route::post('/checkin_warehouse_block', 'Backend\CheckinController@warehouse_block')->name('warehouse_block_checkin');
        Route::post('/checkin_price', 'Backend\CheckinController@price')->name('price_checkin');
        Route::post('/checkin_currency', 'Backend\CheckinController@currency')->name('currency_checkin');
        Route::get('/checkin/{id}/print', 'Backend\CheckinController@print')->name('checkin_print');
        Route::get('/checkin/{id}/status', 'Backend\CheckinController@status')->name('checkin_status');
        Route::get('/checkin_select/{id}/delete', 'Backend\CheckinController@delete')->name('checkin_delete');
        Route::get('/checkin_done/{id}/select', 'Backend\CheckinController@done_status')->name('checkin_done_status');
        Route::get('/checkin_cancel/{id}/select', 'Backend\CheckinController@cancel_status')->name('checkin_cancel_status');
        Route::get('/checkin_delete/{id}', 'Backend\CheckinController@delete_checkin')->name('delete_checkin');
        //Excel
        Route::get('/checkin_excel/{id?}', 'Backend\CheckinController@excel')->name('checkin_excel');
        //Add Product
        Route::get('/product/{checkid}/form/{id?}', 'Backend\ProductController@form_check')->name('product_form_check');
        Route::post('/product/{checkid}/form/{id?}', 'Backend\ProductController@save_check');
        //Import excel
        Route::get('/checkin/excel/{id?}', 'Backend\CheckinController@form_excel')->name('checkin_form_excel');
        Route::post('/checkin/excel/{id?}', 'Backend\CheckinController@save_excel');
        Route::post('/date_checkin_change', 'Backend\CheckinController@select_date')->name('date_checkin_change');
        Route::post('/ware_checkout_change', 'Backend\CheckinController@select_warehouse')->name('ware_checkout_change');
        Route::post('/supp_checkout_change', 'Backend\CheckinController@select_supplier')->name('supp_checkout_change');
        Route::post('/cur_checkin_change', 'Backend\CheckinController@change_currency')->name('cur_checkin_change');
        Route::post('/comment_checkin_change', 'Backend\CheckinController@change_comment')->name('comment_checkin_change');
        Route::post('/type_checkin_change', 'Backend\CheckinController@type_change')->name('type_checkin_change');
        
        
        
        Route::post('/checkin/currency-type-change', 'Backend\CheckinController@currency_type_change')->name('checkin_currency_type_change');
        Route::post('/checkin/currency-price-change', 'Backend\CheckinController@currency_price_change')->name('checkin_currency_price_change');
        //END Checkin
    
        //Checkouts
        Route::get('/accounting_month_report', 'Backend\HomeController@accounting_month_report')->name('accounting_month_report');
        //Checkouts
        Route::get('/checkout_exportDebts', 'Backend\CheckoutController@exportDebts')->name('checkout_exportDebts');
        Route::get('/checkout_downloadDebtReport', 'Backend\CheckoutController@downloadDebtReport')->name('checkout_downloadDebtReport');
        Route::get('/checkout_debts_report', 'Backend\CheckoutController@debts_report')->name('checkout_debts_report');
        Route::get('/checkout_all', 'Backend\CheckoutController@all_done_status');
        Route::get('/checkout_refresh_total', 'Backend\CheckoutController@refresh_total');
        Route::get('/checkout_today_send', 'Backend\CheckoutController@today_send')->name('checkout_today_send');
        Route::get('/checkout_yesterday_send', 'Backend\CheckoutController@yesterday_send')->name('checkout_yesterday_send');
        Route::get('/checkout_calculateCost', 'Backend\CheckoutController@calculateCost')->name('checkout_calculateCost');
        
        Route::get('/checkouts/{ctypeAlias?}', 'Backend\CheckoutController@index')->name('checkouts_index');
        Route::get('/checkout_debtors', 'Backend\CheckoutController@debtors')->name('checkout_debtors_index');
        Route::get('/checkout_debtors_excel', 'Backend\CheckoutController@debtors_excel')->name('checkout_debtors_excel');
        Route::get('/index_stiock_del', 'Backend\CheckoutController@index_stiock_del')->name('index_stiock_del');
        Route::post('/checkouts', 'Backend\CheckoutController@search')->name('checkouts_search');
        Route::get('/checkout/form/{id?}/{page?}/{ctypeAlias?}', 'Backend\CheckoutController@form')->name('checkout_form');
        Route::post('/checkout/form/{id?}/{page?}/{ctypeAlias?}', 'Backend\CheckoutController@save');
        Route::post('/checkout_qty', 'Backend\CheckoutController@qty')->name('qty_checkout');
        Route::post('/checkout_bonus', 'Backend\CheckoutController@bonus')->name('bonus_checkout');
        Route::post('/checkout_price', 'Backend\CheckoutController@price')->name('price_checkout');
        Route::post('/checkout_tan_price', 'Backend\CheckoutController@tan_price')->name('tan_price_checkout');
        Route::post('/checkout_price_total', 'Backend\CheckoutController@price_total')->name('price_total_checkout');
        Route::post('/checkout_price_total_detail', 'Backend\CheckoutController@price_total_detail')->name('price_total_detail_checkout');
        Route::post('/checkout_currency', 'Backend\CheckoutController@currency')->name('currency_checkout');
        Route::post('/checkouts_currency', 'Backend\CheckoutController@currencies')->name('currencies_checkout');
        Route::post('/client_checkout_change', 'Backend\CheckoutController@client_change')->name('client_checkout_change');
        Route::post('/type_checkout_change', 'Backend\CheckoutController@select_checkout_type')->name('type_checkout_change');
        Route::post('/date_checkout_change', 'Backend\CheckoutController@select_checkout_date')->name('date_checkout_change');
        Route::post('/checkout_reference_change', 'Backend\CheckoutController@checkout_reference_change')->name('checkout_reference_change');
        Route::get('/checkout/{id}/status', 'Backend\CheckoutController@status')->name('checkout_status');
        Route::get('/checkou_select/{id}/delete', 'Backend\CheckoutController@delete')->name('checkout_delete');
        Route::get('/checkout_done/{id}/select/{page?}/{ctypeAlias?}', 'Backend\CheckoutController@done_status')->name('checkout_done_status');
        Route::get('/checkout_pay/{id}/select', 'Backend\CheckoutController@payment_status')->name('checkout_done_pay');
        Route::post('/checkout_order_pay/{id}/select', 'Backend\CheckoutController@payment_check')->name('checkout_pay');
        Route::get('/checkout_send/{id}/select', 'Backend\CheckoutController@send_status')->name('checkout_done_send');
        Route::get('/checkout_cancel/{id}/select', 'Backend\CheckoutController@cancel_status')->name('checkout_cancel_status');
        Route::get('/checkout/{id}/print/{view}', 'Backend\CheckoutController@print_doc')->name('checkout_print');
        Route::get('/checkout_check/{id}/print', 'Backend\CheckoutController@check')->name('checkout_check');
        Route::get('/checkout_delete/{id}', 'Backend\CheckoutController@delete_checkout')->name('delete_checkout');
        Route::get('/checkout_excel/{id}', 'Backend\CheckoutController@checkout_excel')->name('checkout_excel');
        Route::get('/checkout_excel_null/{id}', 'Backend\CheckoutController@checkout_excel_null')->name('checkout_excel_null');
        Route::post('/checkout_full_price', 'Backend\CheckoutController@full_price')->name('full_price_checkout');
        Route::post('/checkout_one_qty/{id}', 'Backend\CheckoutController@one_qty')->name('one_qty_checkout');
        Route::post('/checkout_send_success', 'Backend\CheckoutController@send_success')->name('checkout_send_success');
        Route::get('/checkout_filter', 'Backend\CheckoutController@checkout_filter')->name('checkout_filter');
        Route::post('/checkout_discount', 'Backend\CheckoutController@checkout_discount')->name('checkout_discount');
        Route::post('/checkout_commission_scheme', 'Backend\CheckoutController@checkout_commission_scheme')->name('checkout_commission_scheme');
        
        Route::get('/checkout_report', 'Backend\CheckoutController@index_report')->name('checkouts_index_report');
        Route::get('/checkout_report_print/{id}', 'Backend\CheckoutController@report_print')->name('checkouts_report_print');
        Route::get('/checkout_report_filter', 'Backend\CheckoutController@report_filter')->name('checkouts_report_filter');
        Route::post('/checkout_report_print_filter', 'Backend\CheckoutController@report_print_filter')->name('checkouts_report_print_filter');
        //AVG
        Route::get('/checkout_report_avg', 'Backend\CheckoutController@report_avg')->name('checkouts_report_avg');
        Route::post('/checkout_report_print_avg', 'Backend\CheckoutController@report_print_avg')->name('checkouts_report_print_avg');
        //AVG
        Route::get('/checkout_in_price', 'Backend\CheckoutController@in_price')->name('checkout_in_price');
        //Kunlik
        Route::get('/checkout_day_filter', 'Backend\CheckoutController@day_filter')->name('checkouts_day_filter');
        Route::post('/checkout_day_print_filter', 'Backend\CheckoutController@day_print_filter')->name('checkouts_day_print_filter');
        //Kunlik
        Route::get('/checkout_month_filter', 'Backend\CheckoutController@month_filter')->name('checkouts_month_filter');
        Route::post('/checkout_month_filter_post', 'Backend\CheckoutController@month_filter_post')->name('checkouts_month_filter_post');
        
        Route::post('/checkout/currency-type-change', 'Backend\CheckoutController@checkout_currency_type_change')->name('checkout_currency_type_change');
        Route::post('/checkout/currency-price-change', 'Backend\CheckoutController@checkout_currency_price_change')->name('checkout_currency_price_change');
        //END Checkouts
    
        //Transfers
        Route::get('/transfers', 'Backend\TransferController@index')->name('transfers_index');
        Route::get('/transfer/form/{id?}', 'Backend\TransferController@form')->name('transfer_form');
        Route::post('/transfer/form/{id?}', 'Backend\TransferController@save');
        Route::get('/transfer/{id}/status', 'Backend\TransferController@status')->name('transfer_status');
        Route::get('/transfer_check/{id}/print', 'Backend\TransferController@check')->name('transfers_check');
        Route::get('/transfer_select/{id}/delete', 'Backend\TransferController@delete')->name('transfers_delete');
        Route::get('/transfer_done/{id}/select/{page?}', 'Backend\TransferController@done_status')->name('transfers_done_status');
        Route::post('/transfer_qty', 'Backend\TransferController@qty')->name('qty_transfer');
        Route::post('/client_transfer_change', 'Backend\TransferController@client_change')->name('client_transfer_change');
        Route::post('/date_transfer_change', 'Backend\TransferController@select_transfer_date')->name('date_transfer_change');
        Route::get('/transfer_delete/{id}', 'Backend\TransferController@delete_transfer')->name('delete_transfer');
        //END Transfers
    
        //Price
        Route::get('/price_lists', 'Backend\PriceController@index')->name('price_lists_index');
        Route::get('/price_list/form/{id?}', 'Backend\PriceController@form')->name('price_list_form');
        Route::post('/price_list/form/{id?}', 'Backend\PriceController@save');
        Route::get('/price_list/{id}/status', 'Backend\PriceController@status')->name('price_list_status');
        //END Price
        
        //Histories
        Route::get('/histories', 'Backend\HomeController@histories')->name('histories');
        //END Histories
        
        //Account
        Route::get('/returns', 'Backend\ReturnController@index')->name('returns_index');    
        Route::get('/return/form/{id?}', 'Backend\ReturnController@form')->name('return_form');
        Route::get('/return_form', 'Backend\ReturnController@save')->name('return_save');
        Route::get('/return/{id}/status', 'Backend\ReturnController@status')->name('return_status');
        Route::post('/return_post/{code}', 'Backend\ReturnController@return_post')->name('return_post');
        
        
        //CashE
        Route::get('/cash_expenditures', 'Backend\CashExController@index')->name('cash_expenditures_index');
        Route::post('/cash_expenditures', 'Backend\CashExController@search')->name('cash_expenditures_search');
        Route::get('/cash_expenditure/form/{id?}', 'Backend\CashExController@form')->name('cash_expenditure_form');
        Route::post('/cash_expenditure/form/{id?}', 'Backend\CashExController@save');
        Route::get('/cash_expenditure/{id}/status', 'Backend\CashExController@status')->name('cash_expenditure_status');
        Route::get('/cash_exp_delete/{id}', 'Backend\CashExController@delete')->name('cash_exp_delete');
        //filtr
        Route::get('/cash_ex_category/{id}', 'Backend\CashExController@category_select')->name('cash_category_select');
        Route::post('/cash_ex_select/', 'Backend\CashExController@date')->name('cash_expenditure_date');
        //END CashE
        
        //DealerTransfer
        Route::get('/dealer_transfers', 'Backend\DealerTransferController@index')->name('dealer_transfers_index');
        Route::post('/dealer_transfers', 'Backend\DealerTransferController@search')->name('dealer_transfers_search');
        Route::get('/dealer_transfer/form/{id?}/{page?}', 'Backend\DealerTransferController@form')->name('dealer_transfer_form');
        Route::post('/dealer_transfer/form/{id?}/{page?}', 'Backend\DealerTransferController@save');
        Route::post('/dealer_transfer_qty', 'Backend\DealerTransferController@qty')->name('qty_dealer_transfer');
        Route::post('/dealer_transfer_price', 'Backend\DealerTransferController@price')->name('price_dealer_transfer');
        Route::post('/dealer_transfer_price_total', 'Backend\DealerTransferController@price_total')->name('price_total_dealer_transfer');
        Route::post('/dealer_transfer_currency', 'Backend\DealerTransferController@currency')->name('currency_dealer_transfer');
        Route::post('/dealer_transfers_currency', 'Backend\DealerTransferController@currencies')->name('currencies_dealer_transfer');
        Route::get('/dealer_transfer/{id}/status', 'Backend\DealerTransferController@status')->name('dealer_transfer_status');
        Route::get('/dealer_transfer_select/{id}/delete', 'Backend\DealerTransferController@delete')->name('dealer_transfer_delete');
        Route::get('/dealer_transfer_done/{id}/select/{page?}', 'Backend\DealerTransferController@done_status')->name('dealer_transfer_done_status');
        Route::get('/dealer_transfer_pay/{id}/select', 'Backend\DealerTransferController@payment_status')->name('dealer_transfer_done_pay');
        Route::post('/dealer_transfer_order_pay/{id}/select', 'Backend\DealerTransferController@payment_check')->name('dealer_transfer_pay');
        Route::get('/dealer_transfer_send/{id}/select', 'Backend\DealerTransferController@send_status')->name('dealer_transfer_done_send');
        Route::get('/dealer_transfer_cancel/{id}/select', 'Backend\DealerTransferController@cancel_status')->name('dealer_transfer_cancel_status');
        Route::get('/dealer_transfer/{id}/print/{view}', 'Backend\DealerTransferController@print_doc')->name('dealer_transfer_print');
        Route::get('/dealer_transfer_check/{id}/print', 'Backend\DealerTransferController@check')->name('dealer_transfer_check');
        Route::get('/dealer_transfer_delete/{id}', 'Backend\DealerTransferController@delete_dealer_transfer')->name('delete_dealer_transfer');
        Route::get('/dealer_transfer_excel/{id}', 'Backend\DealerTransferController@dealer_transfer_excel')->name('dealer_transfer_excel');
        Route::get('/dealer_transfer_excel_null/{id}', 'Backend\DealerTransferController@dealer_transfer_excel_null')->name('dealer_transfer_excel_null');
        
        Route::post('/dealer_transfer_full_price', 'Backend\DealerTransferController@full_price')->name('full_price_dealer_transfer');
         Route::post('/dealer_transfer_one_qty/{id}', 'Backend\DealerTransferController@one_qty')->name('one_qty_dealer_transfer');
         
        Route::post('/dealer_transfer_send_success', 'Backend\DealerTransferController@send_success')->name('dealer_transfer_send_success');
        
        Route::get('/dealer_transfer_filter', 'Backend\DealerTransferController@dealer_transfer_filter')->name('dealer_transfer_filter');
        //END DealerTransfer
        
    });

});
