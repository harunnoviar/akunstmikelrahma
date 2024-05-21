<aside class="main-sidebar sidebar-dark-primary elevation-4">
   <!-- Brand Logo -->
   <div class="text-center">
      <a href="<?= base_url('user') ?>" class="brand-link">
         <img src="<?= base_url() ?>/assets/dist/img/logo_stmikelrahma.png" alt="AdminLTE Logo" class="center" style="opacity: .8;width:40%">
      </a>
   </div>
   <!-- Sidebar -->
   <div class="sidebar">
      <?php session()->get('role') === '1' ? $path = 'admin' : $path = 'user' ?>
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
         <div class="image">
            <img src="<?= base_url() ?>/assets/dist/img/avatar.png" class="img-circle elevation-2" alt="User Image">
         </div>
         <div class="info">
            <a href="<?= base_url($path) ?>" class="d-block"><?= session()->get('firstname') . ' ' . session()->get('lastname') ?></a>
         </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
         <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->

            <li class="nav-item">
               <a href="<?= base_url($path) ?>" class="nav-link <?= @$a_menu === 'profil' ? 'active' : '' ?>">
                  <i class="nav-icon fas fa-id-card"></i>
                  <p> Profil</p>
               </a>
            </li>
            <?php if (session()->get('role') === '1') { ?>
               <li class="nav-item">
                  <a href="<?= base_url($path . '/user_add') ?>" class="nav-link <?= @$a_menu === 'user_add' ? 'active' : '' ?>">
                     <i class="nav-icon fas fa-user-plus"></i>
                     <p>Tambah Akun</p>
                  </a>
               </li>
               <li class="nav-item">
                  <a href="<?= base_url($path . '/users') ?>" class="nav-link <?= @$a_menu === 'users' ? 'active' : '' ?>">
                     <i class="nav-icon fas fa-users"></i>
                     <p>Akun</p>
                  </a>
               </li>
               <li class="nav-item">
                  <a href="<?= base_url($path . '/userh') ?>" class="nav-link <?= @$a_menu === 'userh' ? 'active' : '' ?>">
                     <i class="nav-icon fas fa-users-slash"></i>
                     <p>Riwayat Akun</p>
                  </a>
               </li>
               <li class="nav-item">
                  <a href="<?= base_url($path . '/dom') ?>" class="nav-link <?= @$a_menu === 'dom' ? 'active' : '' ?>">
                     <i class="nav-icon fas fa-users-cog"></i>
                     <p>Domain</p>
                  </a>
               </li>
               <li class="nav-item">
                  <a href="<?= base_url($path . '/category') ?>" class="nav-link <?= @$a_menu === 'ctg' ? 'active' : '' ?>">
                     <i class="nav-icon fas fa-users-cog"></i>
                     <p>Kategori [OU]</p>
                  </a>
               </li>
               <li class="nav-item">
                  <a href="<?= base_url($path . '/group') ?>" class="nav-link <?= @$a_menu === 'grp' ? 'active' : '' ?>">
                     <i class="nav-icon fas fa-object-group"></i>
                     <p>Group</p>
                  </a>
               </li>
               <!-- <li class="nav-item">
                  <a href="<?= base_url($path . '/unit') ?>" class="nav-link <?= @$a_menu === 'unit' ? 'active' : '' ?>">
                     <i class="nav-icon fas fa-building"></i>
                     <p>SatKer</p>
                  </a>
               </li> -->
               <li class="nav-item">
                  <a href="<?= base_url($path . '/logs') ?>" class="nav-link <?= @$a_menu === 'logs' ? 'active' : '' ?>">
                     <i class="nav-icon fas fa-history"></i>
                     <p>Log</p>
                  </a>
               </li>

            <?php } ?>
            <?php
            if (session()->get('role') === '1') {
               $url_logout = base_url('applogout');
            } elseif (session()->get('sso')) {
               $url_logout = base_url('auth/ssologout');
            } else {
               $url_logout = base_url('logout');
            }
            ?>
            <li class="nav-item">
               <a href="<?= $url_logout ?>" class="nav-link">
                  <i class="nav-icon fas fa-sign-out-alt"></i>
                  <p>Keluar <?= session()->get('sso') ? '<div class="badge badge-success">SSO</div>' : '' ?> </p>
               </a>
            </li>
         </ul>
      </nav>
      <!-- /.sidebar-menu -->
   </div>
   <!-- /.sidebar -->
</aside>