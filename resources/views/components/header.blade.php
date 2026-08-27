<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <h5>JM Academy</h5>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
          <span class="icon-menu"></span>
        </button>

        <ul class="navbar-nav navbar-nav-right">

          <li class="nav-item nav-profile">
            <a class="nav-link" href="#">
              <img src="{{ asset('images/faces/face28.jpg') }}" alt="profile"/>
            </a>
          </li>

          <li class="nav-item mx-1">
                <a class="btn btn-success" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit()">
                Logout
              </a>

               <form id="logout-form" action="{{route('logout')}}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>

        </ul>

      </div>
</nav>
