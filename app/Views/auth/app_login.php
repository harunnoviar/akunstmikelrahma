<?= $this->extend('layout/auth_layout') ?>

<?= $this->section('content') ?>
<div class="login-box">
   <!-- /.login-logo -->
   <div class="card card-outline card-secondary shadow shadow-lg">
      <div class="card-header text-center">
         <strong>
            <h2>Admin Login</h2>
         </strong>
      </div>
      <div class="card-body">
         <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-default-danger"><?= session()->getFlashdata('error') ?></div>
         <?php endif; ?>
         <?php if (session()->getFlashdata('msg')) : ?>
            <div class="alert alert-default-success"><?= session()->getFlashdata('msg') ?></div>
         <?php endif; ?>
         <form action="<?= base_url('applogin') ?>" method="post">
            <?= csrf_field() ?>
            <div class="input-group mb-3">
               <input type="text" name="username" inputmode="username" class="form-control" placeholder="Enter Username" value="<?= old('username') ?>" required>
               <div class="input-group-append">
                  <div class="input-group-text">
                     <span class="fas fa-user"></span>
                  </div>
               </div>
            </div>
            <div class="input-group mb-3">
               <input type="password" name="password" inputmode="text" class="form-control" autocomplete="current-password" placeholder="Enter Password" required>
               <div class="input-group-append">
                  <div class="input-group-text">
                     <span class="fas fa-lock"></span>
                  </div>
               </div>
            </div>
            <div class="input-group mt-3">
               <div class="g-recaptcha" data-sitekey="<?= $recaptchaSite ?>">
               </div>
            </div>
            <div class="row mt-2">
               <button type="submit" class="btn btn-primary btn-block">Masuk</button>
            </div>
            <div class="row mt-3">
               <p>Masuk sebagai pengguna? <a href="<?= base_url() ?>/auth">disini</a></p>

            </div>
         </form>




      </div>
      <!-- /.card-body -->
   </div>
   <!-- /.card -->
</div>
<!-- /.login-box -->

<?php $this->endsection() ?>