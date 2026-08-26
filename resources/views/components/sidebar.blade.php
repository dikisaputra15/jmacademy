<div class="theme-setting-wrapper">

</div>
      <!-- partial -->
      <!-- partial:partials/_sidebar.html -->
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
          <li class="nav-item">
            <a class="nav-link" href="{{ Route('home') }}">
              <i class="icon-grid menu-icon"></i>
              <span class="menu-title">Dashboard</span>
            </a>
          </li>
          <?php if(auth()->user()->roles == 'admin'){ ?>
          <li class="nav-item">
            <a class="nav-link" href="{{ Route('user.index') }}">
              <i class="icon-head menu-icon"></i>
              <span class="menu-title">Data Pelanggan</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ Route('admin.allpesan') }}">
              <i class="icon-columns menu-icon"></i>
              <span class="menu-title">Data Pesanan</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ Route('pembayaran.verify') }}">
              <i class="icon-bar-graph menu-icon"></i>
              <span class="menu-title">Data Pembayaran</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ Route('produk.index') }}">
              <i class="icon-grid-2 menu-icon"></i>
              <span class="menu-title">Data Produk</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('laporan.index') }}">
              <i class="icon-contract menu-icon"></i>
              <span class="menu-title">Laporan</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ Route('lokasi.index') }}">
              <i class="icon-grid-2 menu-icon"></i>
              <span class="menu-title">Lokasi</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ Route('metodepembayaran.index') }}">
              <i class="icon-grid-2 menu-icon"></i>
              <span class="menu-title">Metode Pembayaran</span>
            </a>
          </li>
          <?php } ?>

          <?php if(auth()->user()->roles == 'pimpinan'){ ?>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('permintaanstok.index') }}">
              <i class="icon-grid-2 menu-icon"></i>
              <span class="menu-title">Data Produk</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('laporan.index') }}">
              <i class="icon-contract menu-icon"></i>
              <span class="menu-title">Laporan</span>
            </a>
          </li>
          <?php } ?>

          <?php if(auth()->user()->roles == 'kurir'){ ?>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('pengantaran.index') }}">
              <i class="icon-grid-2 menu-icon"></i>
              <span class="menu-title">Daftar Pengantaran</span>
            </a>
          </li>
          <?php } ?>

          <?php if(auth()->user()->roles == 'pelanggan'){ ?>
        <li class="nav-item">
            <a class="nav-link" href="{{ url('/keranjang/allproduk') }}">
              <i class="icon-grid-2 menu-icon"></i>
              <span class="menu-title">Produk</span>
            </a>
          </li>

           <li class="nav-item">
            <a class="nav-link" href="{{ Route('pembayaran.all') }}">
              <i class="icon-grid-2 menu-icon"></i>
              <span class="menu-title">Pembayaran</span>
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="{{ route('pesanan.status') }}">
              <i class="icon-grid-2 menu-icon"></i>
              <span class="menu-title">Status Pemesanan</span>
            </a>
          </li>
          <?php } ?>
        </ul>
      </nav>
