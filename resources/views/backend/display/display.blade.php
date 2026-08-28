@extends('layouts.pos')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block">
                    <div class="card">
                        <div class="card-inner">
                            <div class="row">
                                <div class="col-lg-5">
                                    <div id="carouselExampleSlidesOnly" class="carousel slide" data-bs-ride="carousel">
                                      <div class="carousel-inner">
                                        @if($item->image)
                                        <div class="carousel-item active">
                                            <img src="/upload/product_image/{{ $item->image }}" class="d-block w-100">
                                        </div>
                                        @if($item->image_2)
                                        <div class="carousel-item">
                                          <img src="/upload/product_image/{{ $item->image_2 }}" class="d-block w-100">
                                        </div>
                                        @endif
                                        @if($item->image_3)
                                        <div class="carousel-item">
                                          <img src="/upload/product_image/{{ $item->image_3 }}" class="d-block w-100">
                                        </div>
                                        @endif
                                        @else
                                        <div class="carousel-item active">
                                            <img src="/upload/no_photo.jpg" class="d-block w-100">
                                        </div>
                                        @endif
                                      </div>
                                    </div>
                                </div><!-- .col -->
                                <div class="col-lg-7">
                                    <div class="product-info me-xxl-5">
                                        <h4 class="product-price text-primary" style="font-size: 34px" >{{ $item->barcode }}</h4>
                                        <h2 class="product-title">{{ $item->name }}</h2>
                                            <ul class="d-flex g-3 gx-5">
                                                <li>
                                                    <div class="fs-14px text-muted" style="font-size: 22px">Склад</div>
                                                    @foreach(App\Models\WarehouseBlockProduct::where('product_id', $item->id)->get() as $bid)
                                                    <div style="font-size: 22px" class="fs-16px fw-bold text-secondary">{{ $bid->wareid ? $bid->wareid->num_code : null}}</div>
                                                    @endforeach
                                                </li>
                                                <li>
                                                    <div class="fs-14px text-muted" style="font-size: 22px">Ячейка</div>
                                                    @foreach(App\Models\WarehouseBlockProduct::where('product_id', $item->id)->get() as $bid)
                                                        <div style="font-size: 22px" class="fs-16px fw-bold text-secondary">
                                                            {{ $bid->wareblockid ? $bid->wareblockid->row : null }}-блок {{ $bid->wareblockcellid ? $bid->wareblockcellid->cell . '-' . $bid->wareblockcellid->cell_number  : null}}
                                                        </div>
                                                    @endforeach
                                                </li>
                                            </ul>
                                    </div><!-- .product-info -->
                                </div><!-- .col -->
                            </div><!-- .row -->
                        </div>
                    </div>
                </div><!-- .nk-block -->
                <div class="row">
                @php($wares = App\Models\Warehouse::whereIn('id', [1,2,3,4])->where('status', 1)->where('dealer_id', 1)->get())
                @foreach($wares as $ware)
                <div class="col-md-3 mt-3">
                     <div class="card card-bordered product-card">
                         <div class="product-thumb">
                             <ul class="product-badges">
                                 <li>@if($item->stockid()->where('warehouse_id', $ware->id)->sum('stock')) <span class="badge bg-success">Есть</span> @else <span class="badge bg-danger">Нет</span> @endif</li>
                             </ul>
                         </div>
                         <div class="text-center">
                             <ul class="product-tags">
                                        <li style="font-size: 22px">{{ $ware->name }}</li>
                             </ul>
                             <h3 class="product-title">{{ $ware->num_code }}</h3>
                             <div class="product-price text-primary h5" style="font-size: 28px">{{ $item->stockid()->where('warehouse_id', $ware->id)->sum('stock') }}</div>
                         </div>
                     </div>
                 </div>   
                @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

var timeOutId = 0;

var ajaxFn = function () {
    var datacid = {{ $item->barcode }};

    $.ajax({
        type: "POST",
        url: '{{ route("display_refresh") }}',
        dataType: 'JSON',
        data: { datacid: datacid },
        success: function(data) {
            if(data.barcode != datacid){
                location.reload()
            }
            timeOutId = setTimeout(ajaxFn, 5000);
        },
        error: function(ajaxContext) {
            alert(ajaxContext.responseText)
        }
    });
}
ajaxFn();

</script>
@endsection