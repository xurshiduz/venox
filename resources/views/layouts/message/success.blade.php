@if($message = Session::get('success'))
    <div class="alert alert-primary alert-dismissible fade show" role="alert">
        <div class="alert-body">
            {{ $message }}!
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif