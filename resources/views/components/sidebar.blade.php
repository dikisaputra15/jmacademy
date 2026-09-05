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
            <li class="nav-item {{ request()->routeIs('student-registrations.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('student-registrations.index') }}">
              <i class="icon-grid-2 menu-icon"></i>
              <span class="menu-title">Student Register</span>
            </a>
          </li>
          <li class="nav-item {{ request()->routeIs('paid-schedules.*', 'trial-schedules.*') ? 'active' : '' }}">
                <a class="nav-link {{ request()->routeIs('paid-schedules.*', 'trial-schedules.*') ? '' : 'collapsed' }}" data-toggle="collapse" data-target="#teacher-schedules" href="#teacher-schedules" role="button" aria-expanded="{{ request()->routeIs('paid-schedules.*', 'trial-schedules.*') ? 'true' : 'false' }}" aria-controls="teacher-schedules">
                    <i class="icon-grid-2 menu-icon"></i>
                    <span class="menu-title">Teacher Schedules</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('paid-schedules.*', 'trial-schedules.*') ? 'show' : '' }}" id="teacher-schedules">
                    <ul class="nav flex-column sub-menu">
                    <li class="nav-item {{ request()->routeIs('paid-schedules.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('paid-schedules.index') }}">Paid Schedules</a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('trial-schedules.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('trial-schedules.index') }}">Trial Schedules</a>
                    </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="">
                <i class="icon-grid-2 menu-icon"></i>
                <span class="menu-title">Gaji Guru</span>
                </a>
            </li>
          @endrole

          @role('guru')
          <li class="nav-item {{ request()->routeIs('teacher-salaries.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('teacher-salaries.index') }}">
              <i class="icon-contract menu-icon"></i>
              <span class="menu-title">My Salary</span>
            </a>
          </li>
          <li class="nav-item {{ request()->routeIs('teacher-courses.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('teacher-courses.index') }}">
              <i class="icon-contract menu-icon"></i>
              <span class="menu-title">Course</span>
            </a>
          </li>
           <li class="nav-item {{ request()->routeIs('teacher-histories.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('teacher-histories.index') }}">
              <i class="icon-contract menu-icon"></i>
              <span class="menu-title">Histories</span>
            </a>
          </li>
           <li class="nav-item {{ request()->routeIs('parent-reports.*') ? 'active' : '' }}">
             <a class="nav-link" href="{{ route('parent-reports.index') }}">
              <i class="icon-contract menu-icon"></i>
              <span class="menu-title">Parent Reports</span>
            </a>
          </li>
           <li class="nav-item {{ request()->routeIs('pending-reports.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('pending-reports.index') }}">
              <i class="icon-contract menu-icon"></i>
              <span class="menu-title">Pending Reports</span>
            </a>
          </li>

          <li class="nav-item {{ request()->routeIs('teacher-paid-schedules.*', 'teacher-trial-schedules.*') ? 'active' : '' }}">
            <a class="nav-link {{ request()->routeIs('teacher-paid-schedules.*', 'teacher-trial-schedules.*') ? '' : 'collapsed' }}" data-toggle="collapse" data-target="#teacher-own-schedules" href="#teacher-own-schedules" role="button" aria-expanded="{{ request()->routeIs('teacher-paid-schedules.*', 'teacher-trial-schedules.*') ? 'true' : 'false' }}" aria-controls="teacher-own-schedules">
                <i class="icon-grid-2 menu-icon"></i>
                <span class="menu-title">Schedules</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse {{ request()->routeIs('teacher-paid-schedules.*', 'teacher-trial-schedules.*') ? 'show' : '' }}" id="teacher-own-schedules">
                <ul class="nav flex-column sub-menu">
                <li class="nav-item {{ request()->routeIs('teacher-paid-schedules.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('teacher-paid-schedules.index') }}">Paid Schedules</a>
                </li>
                <li class="nav-item {{ request()->routeIs('teacher-trial-schedules.*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('teacher-trial-schedules.index') }}">Trial Schedules</a>
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

          <li class="nav-item {{ request()->routeIs('student-classes.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('student-classes.index') }}">
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

          <li class="nav-item {{ request()->routeIs('student-reports.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('student-reports.index') }}">
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
