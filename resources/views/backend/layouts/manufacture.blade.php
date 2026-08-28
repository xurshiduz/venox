<style type="text/css">
    * {
  margin: 0;
  padding: 0;
}
#chart-container {
  position: relative;
  height: 50vh;
  overflow: hidden;
  padding-bottom: 20px;
}

#chart-container-new {
  position: relative;
  height: 50vh;
  overflow: hidden;
  padding-top: 20px;
}

#chart-container-year {
  position: relative;
  height: 50vh;
  overflow: hidden;
  padding-top: 20px;
}
</style>

<div class="row g-gs" style="padding-top: 20px;">
    <div class="col-md-4">
        <div class="card card-bordered card-full">
            <div class="card-inner">
                <div class="card-title-group align-start mb-0">
                    <div class="card-title">
                        <h6 class="subtitle">{{ trans('backend.table.produced_all') }}</h6>
                    </div>
                </div>
                <div class="card-amount">
                    <span class="amount"> 0 <span class="currency currency-usd">{{ trans('backend.table.name_kg') }}</span>
                    </span>
                </div>
                <div class="invest-data">
                    <div class="invest-data-amount g-2">
                        <div class="invest-data-history">
                            <div class="title">{{ trans('backend.table.today') }}</div>
                            <div class="amount">0 <span class="currency currency-usd">{{ trans('backend.table.name_kg') }}</span></div>
                        </div>
                        <div class="invest-data-history">
                            <div class="title">{{ trans('backend.table.yesterday') }}</div>
                            <div class="amount">0 <span class="currency currency-usd">{{ trans('backend.table.name_kg') }}</span></div>
                        </div>
                        <div class="invest-data-history">
                            <div class="title">{{ trans('backend.table.this_month') }}</div>
                            <div class="amount">0 <span class="currency currency-usd">{{ trans('backend.table.name_kg') }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card card-bordered card-full">
            <div class="card-inner">
                <div class="card-title-group align-start mb-0">
                    <div class="card-title">
                        <h6 class="subtitle">{{ trans('backend.menu.manufacturer_transfers') }}</h6>
                    </div>
                </div>
                <div class="card-amount">
                    <span class="amount"> 0 <span class="currency currency-usd">{{ trans('backend.table.name_kg') }}</span>
                    </span>
                </div>
                <div class="invest-data">
                    <div class="invest-data-amount g-2">
                        <div class="invest-data-history">
                            <div class="title">{{ trans('backend.table.today') }}</div>
                            <div class="amount">0 <span class="currency currency-usd">{{ trans('backend.table.name_kg') }}</span></div>
                        </div>
                        <div class="invest-data-history">
                            <div class="title">{{ trans('backend.table.yesterday') }}</div>
                            <div class="amount">0 <span class="currency currency-usd">{{ trans('backend.table.name_kg') }}</span></div>
                        </div>
                        <div class="invest-data-history">
                            <div class="title">{{ trans('backend.table.this_month') }}</div>
                            <div class="amount">0 <span class="currency currency-usd">{{ trans('backend.table.name_kg') }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card card-bordered card-full">
            <div class="card-inner">
                <div class="card-title-group align-start mb-0">
                    <div class="card-title">
                        <h6 class="subtitle">{{ trans('backend.menu.manufacturer_purchases') }}</h6>
                    </div>
                </div>
                <div class="card-amount">
                    <span class="amount"> 28933.31 <span class="currency currency-usd">{{ trans('backend.table.name_kg') }}</span>
                    </span>
                </div>
                <div class="invest-data">
                    <div class="invest-data-amount g-2">
                        <div class="invest-data-history">
                            <div class="title">{{ trans('backend.table.today') }}</div>
                            <div class="amount">0 <span class="currency currency-usd">{{ trans('backend.table.name_kg') }}</span></div>
                        </div>
                        <div class="invest-data-history">
                            <div class="title">{{ trans('backend.table.yesterday') }}</div>
                            <div class="amount">0 <span class="currency currency-usd">{{ trans('backend.table.name_kg') }}</span></div>
                        </div>
                        <div class="invest-data-history">
                            <div class="title">{{ trans('backend.table.this_month') }}</div>
                            <div class="amount">28933.31 <span class="currency currency-usd">{{ trans('backend.table.name_kg') }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>