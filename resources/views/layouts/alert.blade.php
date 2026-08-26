@if ($message = Session::get('success'))
    <div class="alert alert-success alert-dismissible show fade">
        <div class="alert-body">
            <button class="btn btn-success btn-icon-split">
                <span class="icon text-white-50">
                    <i class="fas fa-check"></i>
                </span>
                <span class="text">X</span>
            </button>
            <p>{{ $message }}</p>
        </div>
    </div>
@endif
