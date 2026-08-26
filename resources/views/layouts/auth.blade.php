
@include('components.auth-header')

<div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth px-0">
            @yield('main')
        </div>
    </div>
</div>


@include('components.auth-footer')
