<div class="theme-setting-wrapper">

</div>
      <!-- partial -->
      <!-- partial:partials/_sidebar.html -->
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
          <li class="nav-item">
            <a class="nav-link" href="{{ route('home') }}">
              <i class="icon-grid menu-icon"></i>
              <span class="menu-title">Overview</span>
            </a>
          </li>
          @role('admin')
          <li class="nav-item">
            <a class="nav-link" href="{{ route('users.index') }}">
              <i class="icon-head menu-icon"></i>
              <span class="menu-title">Management User</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('course-categories.index') }}">
              <i class="icon-grid-2 menu-icon"></i>
              <span class="menu-title">Category Course</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('courses.index') }}">
              <i class="icon-grid-2 menu-icon"></i>
              <span class="menu-title">Course</span>
            </a>
          </li>
         <li class="nav-item">
            <a class="nav-link" href="">
              <i class="icon-grid-2 menu-icon"></i>
              <span class="menu-title">Student Register</span>
            </a>
          </li>
          @endrole

          @role('guru')
          <li class="nav-item">
            <a class="nav-link" href="">
              <i class="icon-contract menu-icon"></i>
              <span class="menu-title">My Salary</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="">
              <i class="icon-contract menu-icon"></i>
              <span class="menu-title">Course</span>
            </a>
          </li>
           <li class="nav-item">
            <a class="nav-link" href="">
              <i class="icon-contract menu-icon"></i>
              <span class="menu-title">Permit Request</span>
            </a>
          </li>
           <li class="nav-item">
            <a class="nav-link" href="">
              <i class="icon-contract menu-icon"></i>
              <span class="menu-title">Histories</span>
            </a>
          </li>
           <li class="nav-item">
            <a class="nav-link" href="">
              <i class="icon-contract menu-icon"></i>
              <span class="menu-title">Parent Reports</span>
            </a>
          </li>
           <li class="nav-item">
            <a class="nav-link" href="">
              <i class="icon-contract menu-icon"></i>
              <span class="menu-title">Pending Reports</span>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#schedules" aria-expanded="false" aria-controls="schedules">
                <i class="icon-grid-2 menu-icon"></i>
                <span class="menu-title">Schedules</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="schedules">
                <ul class="nav flex-column sub-menu">
                <li class="nav-item">
                    <a class="nav-link" href="">Paid Schedules</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="">Trial Schedules</a>
                </li>
                </ul>
            </div>
        </li>

          @endrole

          @role('student')

           <li class="nav-item {{ request()->routeIs('student-courses.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('student-courses.index') }}">
              <i class="icon-grid-2 menu-icon"></i>
              <span class="menu-title">All Courses</span>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="">
              <i class="icon-grid-2 menu-icon"></i>
              <span class="menu-title">My Classes</span>
            </a>
          </li>

        <li class="nav-item {{ request()->routeIs('student-transactions.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('student-transactions.index') }}">
              <i class="icon-grid-2 menu-icon"></i>
              <span class="menu-title">My Transactions</span>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="">
              <i class="icon-grid-2 menu-icon"></i>
              <span class="menu-title">Report Study</span>
            </a>
          </li>
          @endrole

          <li class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('profile.edit') }}">
              <i class="icon-head menu-icon"></i>
              <span class="menu-title">Profile</span>
            </a>
          </li>
        </ul>
      </nav>
