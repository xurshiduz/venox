<div class="nk-sidebar nk-sidebar-fixed is-dark {{ Auth::user()->iscompact == 1 ? 'is-compact' : null }}" data-content="sidebarMenu">
    <div class="nk-sidebar-element nk-sidebar-head">
        <div class="nk-menu-trigger">
            <a href="#" class="nk-nav-toggle nk-quick-nav-icon d-xl-none" data-target="sidebarMenu"><em class="icon ni ni-arrow-left"></em></a>
            <a href="#" id="iscompact" class="nk-nav-compact nk-quick-nav-icon d-none d-xl-inline-flex" data-target="sidebarMenu"><em class="icon ni ni-menu"></em></a>
        </div>
        <div class="nk-sidebar-brand">
            <a href="{{ route('home') }}" class="logo-link nk-sidebar-logo">
                <img style="margin-left: 0.925rem;" class="logo-light logo-img" src="/venox_logo_white.png" srcset="/venox_logo_white.png 2x" alt="logo">
                <img class="logo-dark logo-img" src="/venox_logo_white.png" srcset="/venox_logo_white.png 2x" alt="logo-dark">
            </a>
        </div>
    </div><!-- .nk-sidebar-element -->
    <div class="nk-sidebar-element nk-sidebar-body">
        <div class="nk-sidebar-content">
            <div class="nk-sidebar-menu" data-simplebar>
                <ul class="nk-menu">
                    <li class="nk-menu-item {{ Request::routeIs('home') ? 'active' : '' }}">
                        <a href="{{ route('home') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-home-alt"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.home') }}</span>
                        </a>
                    </li>

                    @hasanyrole('admin')
                    <li class="nk-menu-item {{ Request::routeIs('dashboard_new') ? 'active' : '' }}">
                        <a href="{{ route('dashboard_new') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-growth"></em></span>
                            <span class="nk-menu-text">Мониторинг</span>
                        </a>
                    </li>
                    @endhasanyrole
                    
                    @hasanyrole('admin|sale|report')
                    <li class="nk-menu-item {{ Request::routeIs('product_barcode') ? 'active' : '' }}">
                        <a href="{{ route('product_barcode') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-scan"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.product_barcode') }}</span>
                        </a>
                    </li>
                    @endhasanyrole

                    @hasanyrole('admin|cashier|report')
                    <li class="nk-menu-item has-sub {{ Request::routeIs('checkouts_day_filter') || Request::routeIs('cash_receipts_index') || Request::routeIs('currencies_index') ? 'active' : '' }}">
                        <a href="#" class="nk-menu-link nk-menu-toggle">
                            <span class="nk-menu-icon"><em class="icon ni ni-coins"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.cash_receipts_all') }}</span>
                        </a>
                        <ul class="nk-menu-sub">
                            <li class="nk-menu-item {{ Request::routeIs('accounting_month_report') ? 'active' : '' }}">
                                <a href="{{ route('accounting_month_report') }}" class="nk-menu-link"><span class="nk-menu-text">Ойлик хисобот (P&L)</span></a>
                            </li>
                            <li class="nk-menu-item {{ Request::routeIs('checkouts_day_filter') ? 'active' : '' }}">
                                <a href="{{ route('checkouts_day_filter') }}" class="nk-menu-link"><span class="nk-menu-text">Фильтр по продажам</span></a>
                            </li>
                            <li class="nk-menu-item {{ Request::routeIs('checkins_sverka_index') ? 'active' : '' }}">
                                <a href="{{ route('checkins_sverka_index') }}" class="nk-menu-link"><span class="nk-menu-text">Сверка закупок</span></a>
                            </li>
                            <li class="nk-menu-item {{ Request::routeIs('cash_receipts_index') ? 'active' : '' }}">
                                <a href="{{ route('cash_receipts_index') }}" class="nk-menu-link"><span class="nk-menu-text">{{ trans('backend.menu.cash_receipts_index') }}</span></a>
                            </li>
                            @if(App\Models\CurrencyType::where('status', 1)->where('id', '!=', 1)->count())
                            <li class="nk-menu-item {{ Request::routeIs('currencies_index') ? 'active' : '' }}">
                                <a href="{{ route('currencies_index') }}" class="nk-menu-link"><span class="nk-menu-text">{{ trans('backend.menu.currencies_index') }}</span></a>
                            </li>
                            @endif
                            
                            <li class="nk-menu-item {{ Request::routeIs('checkouts_month_filter') ? 'active' : '' }}">
                                <a href="{{ route('checkouts_month_filter') }}" class="nk-menu-link"><span class="nk-menu-text">Ойлик мижозга сотувлар буйича</span></a>
                            </li>
                            
                        </ul>
                    </li>
                    @endhasanyrole
                    
                    @hasanyrole('admin|cashier')
                    <li class="nk-menu-item {{ Request::routeIs('cash_expenditures_index') || Request::routeIs('cash_expenditure_form') ? 'active' : '' }}">
                        <a href="{{ route('cash_expenditures_index') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-coins"></em></span>
                            <span class="nk-menu-text">Затраты</span>
                        </a>
                    </li>
                    @endhasanyrole
                    
                    @hasanyrole('admin|cashier|report')
                    <li class="nk-menu-item {{ Request::routeIs('reconciliation_act') ? 'active' : '' }}">
                        <a href="{{ route('reconciliation_act') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-tile-thumb"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.reconciliation_act') }}</span>
                        </a>
                    </li>
                    @endhasanyrole

                    @hasanyrole('admin|arrival')
                    <li class="nk-menu-item {{ Request::routeIs('checkins_index') || Request::routeIs('checkin_form') ? 'active' : '' }}">
                        <a href="{{ route('checkins_index') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-download"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.checkins_index') }}</span>
                        </a>
                    </li>
                    @endhasanyrole

                    @hasanyrole('admin|sale|dealer_admin')
                    <li class="nk-menu-item {{ Request::routeIs('checkouts_index') || Request::routeIs('checkout_form') ? 'active' : '' }}">
                        <a href="{{ route('checkouts_index') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-upload"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.checkouts_index') }}</span>
                        </a>
                    </li>
                    @endhasanyrole

                    @hasanyrole('admin22|sale22|dealer_admin22')
                    <li class="nk-menu-item {{ Request::routeIs('checkout_debtors_index') || Request::routeIs('checkout_debtors_form') ? 'active' : '' }}">
                        <a href="{{ route('checkout_debtors_index') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-user-list"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.debtors') }}</span>
                        </a>
                    </li>
                    @endhasanyrole

                    @hasanyrole('admin|sale|dealer_admin')
                    <li class="nk-menu-item {{ Request::routeIs('checkout_debts_report') ? 'active' : '' }}">
                        <a href="{{ route('checkout_debts_report') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-user-list"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.debtors') }} ({{ trans('backend.menu.reconciliation_act') }})</span>
                        </a>
                    </li>
                    @endhasanyrole
                    
                    @hasanyrole('admin22|sale22|cashier22')
                    <li class="nk-menu-item {{ Request::routeIs('transfers_index') || Request::routeIs('transfers_form') ? 'active' : '' }}">
                        <a href="{{ route('transfers_index') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-tranx"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.prod_ware_transfers') }}</span>
                        </a>
                    </li>
                    @endhasanyrole
                    
                    
                    @hasanyrole('admin2|report2|tan_report2')
                    <li class="nk-menu-item {{ Request::routeIs('products_stock') ? 'active' : '' }}">
                        <a href="{{ route('products_stock') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-bag"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.products_stock') }}</span>
                        </a>
                    </li>
                    @endhasanyrole

                    @hasanyrole('admin|sale22|cashier22')
                    <li class="nk-menu-item {{ Request::routeIs('returns_index') ? 'active' : '' }}">
                        <a href="{{ route('returns_index') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-undo"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.returns_index') }}</span>
                        </a>
                    </li>
                    @endhasanyrole
                    
                    @hasanyrole('admin|sale|cashier|report')
                    <li class="nk-menu-item {{ Request::routeIs('filter_index') || Request::routeIs('filter_param') ? 'active' : '' }}">
                        <a href="{{ route('filter_index') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-filter"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.table.filter') }} <!--<span class="badge rounded-pill bg-outline-success">new</span>--></span>
                        </a>
                    </li>
                    @endhasanyrole
                    

                    @hasanyrole('admin|sale|report|cashier|arrival')
                    <li class="nk-menu-item {{ Request::routeIs('products_index') || Request::routeIs('product_form') || Request::routeIs('product_import') ? 'active' : '' }}">
                        <a href="{{ route('products_index') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-bag"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.products_index') }}</span>
                        </a>
                    </li>
                    @endhasanyrole

                    @hasanyrole('admina|salea|reporta')
                    <li class="nk-menu-item {{ Request::routeIs('price_lists_index') || Request::routeIs('price_list_form') ? 'active' : '' }}">
                        <a href="{{ route('price_lists_index') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-file-text"></em></span>
                            <span class="nk-menu-text">Прайс-лист</span>
                        </a>
                    </li>
                    @endhasanyrole

                    @hasanyrole('admin|dealer_admin')
                    <li class="nk-menu-item {{ Request::routeIs('product_report_form') || Request::routeIs('product_report_save') ? 'active' : '' }}">
                        <a href="{{ route('product_report_form') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-growth"></em></span>
                            <span class="nk-menu-text">Топ продукты ({{ trans('backend.table.filter') }}) </span>
                        </a>
                    </li>
                    @endhasanyrole

                    @hasanyrole('admin|dealer_admin')
                    <li class="nk-menu-item {{ Request::routeIs('top_client_filter') || Request::routeIs('top_client_filter_post') ? 'active' : '' }}">
                        <a href="{{ route('top_client_filter') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-growth"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.top_client') }}</span>
                        </a>
                    </li>
                    @endhasanyrole

                    

                    @hasanyrole('admin|sale|cashier|dealer_admin')
                    <li class="nk-menu-item {{ Request::routeIs('clients_index') || Request::routeIs('client_form') || Request::routeIs('client_import') ? 'active' : '' }}">
                        <a href="{{ route('clients_index') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-user-list"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.clients_index') }}</span>
                        </a>
                    </li>
                    @endhasanyrole

                    @hasanyrole('admin222|arrival2222')
                    <li class="nk-menu-item {{ Request::routeIs('suppliers_index') || Request::routeIs('supplier_form') || Request::routeIs('supplier_import') ? 'active' : '' }}">
                        <a href="{{ route('suppliers_index') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-user-c"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.suppliers_index') }}</span>
                        </a>
                    </li>
                    @endhasanyrole

                    @hasanyrole('admin|arrival|dealer_admin')
                    <li class="nk-menu-item {{ Request::routeIs('warehouses_index') || Request::routeIs('warehouse_form') || Request::routeIs('category_import') ? 'active' : '' }}">
                        <a href="{{ route('warehouses_index') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-building"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.warehouses_index') }}</span>
                        </a>
                    </li>
                    @endhasanyrole

                    @hasanyrole('admin|dealer_admin')
                    <li class="nk-menu-item {{ Request::routeIs('users_index') || Request::routeIs('user_form') ? 'active' : '' }}">
                        <a href="{{ route('users_index') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-user-check"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.users_index') }}</span>
                        </a>
                    </li>
                    @endhasanyrole
                    
                    @hasanyrole('admin')
                    <li class="nk-menu-item has-sub">
                        <a href="#" class="nk-menu-link nk-menu-toggle">
                            <span class="nk-menu-icon"><em class="icon ni ni-setting"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.settings_index') }}</span>
                        </a>
                        <ul class="nk-menu-sub">
                            <li class="nk-menu-item {{ Request::routeIs('checkout_types_index') ? 'active' : '' }}">
                                <a href="{{ route('checkout_types_index') }}" class="nk-menu-link"><span class="nk-menu-text">{{ trans('backend.index.type_checkout') }}</span></a>
                            </li>
                            <li class="nk-menu-item {{ Request::routeIs('cash_receipt_types_index') ? 'active' : '' }}">
                                <a href="{{ route('cash_receipt_types_index') }}" class="nk-menu-link"><span class="nk-menu-text">{{ trans('backend.input.type_pay') }}</span></a>
                            </li>
                            <li class="nk-menu-item {{ Request::routeIs('settings_index') ? 'active' : '' }}">
                                <a href="{{ route('settings_index') }}" class="nk-menu-link"><span class="nk-menu-text">{{ trans('backend.menu.settings_index') }}</span></a>
                            </li>
                            <li class="nk-menu-item {{ Request::routeIs('units_index') ? 'active' : '' }}">
                                <a href="{{ route('units_index') }}" class="nk-menu-link"><span class="nk-menu-text">{{ trans('backend.menu.units_index') }}</span></a>
                            </li>
                            <li class="nk-menu-item {{ Request::routeIs('categories_index') ? 'active' : '' }}">
                                <a href="{{ route('categories_index') }}" class="nk-menu-link"><span class="nk-menu-text">{{ trans('backend.menu.categories_index') }}</span></a>
                            </li>
                            <li class="nk-menu-item {{ Request::routeIs('histories') ? 'active' : '' }}">
                                <a href="{{ route('histories') }}" class="nk-menu-link"><span class="nk-menu-text">{{ trans('backend.menu.histories') }}</span></a>
                            </li>
                        </ul>
                    </li>
                    
                    @endhasanyrole

                    @hasanyrole('admin|report')
                    <li class="nk-menu-item has-sub">
                        <a href="#" class="nk-menu-link nk-menu-toggle">
                            <span class="nk-menu-icon"><em class="icon ni ni-bar-chart-alt"></em></span>
                            <span class="nk-menu-text">{{ trans('backend.menu.report') }}</span>
                        </a>
                        <ul class="nk-menu-sub">
                            @php
                                $date = Carbon\Carbon::now();
                                $date_old = Carbon\Carbon::now()->subMonth();
                            @endphp
                            
                            <li class="nk-menu-item">
                                <a href="{{ route('warehouse_filter') }}" class="nk-menu-link"><span class="nk-menu-text">Фильтр</span></a>
                            </li>
                                
                            <li class="nk-menu-item">
                                <a href="{{ route('report_top_all', ['id' => 'all']) }}" class="nk-menu-link"><span class="nk-menu-text">Топ 100 товаров за все время</span></a>
                            </li>
                            <li class="nk-menu-item">
                                <a href="{{ route('report_top_all', ['id' => 'last_month']) }}" class="nk-menu-link"><span class="nk-menu-text">Топ 100 товаров за {{ $date_old->locale('ru')->getTranslatedMonthName('MMMM YYYY') }}</span></a>
                            </li>
                            <li class="nk-menu-item">
                                
                                <a href="{{ route('report_top_all', ['id' => 'quarter']) }}" class="nk-menu-link"><span class="nk-menu-text">Топ 100 товаров за {{ $date->locale('ru')->getTranslatedMonthName('MMMM YYYY') }}</span></a>
                            </li>
                            <!--<li class="nk-menu-item">
                                <a href="{{ route('report_category') }}" class="nk-menu-link"><span class="nk-menu-text">Отчет по категориям</span></a>
                            </li>-->
                            
                            <li class="nk-menu-item">
                                <a href="{{ route('report_unsold') }}" class="nk-menu-link"><span class="nk-menu-text">Непродаваемые товары</span></a>
                            </li>
                        </ul>
                    </li>
                    @endhasanyrole

                    @hasanyrole('admin')
                    <!-- <li class="nk-menu-heading">
                        <h6 class="overline-title text-primary-alt">Misc</h6>
                    </li>
                    
                    <li class="nk-menu-item">
                        <a href="{{ route('activitie') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-text-rich"></em></span>
                            <span class="nk-menu-text">Activities</span>
                        </a>
                    </li> -->
                    @endhasanyrole

                    
                </ul><!-- .nk-menu -->
            </div><!-- .nk-sidebar-menu -->
        </div><!-- .nk-sidebar-content -->
    </div><!-- .nk-sidebar-element -->
</div>