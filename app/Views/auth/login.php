<?= $this->extend('layout/auth_layout') ?>

<?= $this->section('content') ?>
<div class="login-box">
   <!-- /.login-logo -->
   <div class="card card-outline card-maroon shadow shadow-lg">
      <div class="card-header text-center">
         <a href="<?= base_url('auth') ?>" class="h1"><b><?= lang('Auth.headTitle') ?></b> id</a>
      </div>
      <div class="card-body">
         <p class="login-box-msg"><?= lang('Auth.loginTitle') ?></p>
         <?php if (session('error') !== null) : ?>
            <div class="alert alert-default-danger" role="alert"><?= session('error') ?></div>
         <?php elseif (session('errors') !== null) : ?>
            <div class="alert alert-default-danger" role="alert">
               <?php if (is_array(session('errors'))) : ?>
                  <?php foreach (session('errors') as $error) : ?>
                     <?= $error ?>
                     <br>
                  <?php endforeach ?>
               <?php else : ?>
                  <?= session('errors') ?>
               <?php endif ?>
            </div>
         <?php endif ?>

         <?php if (session('msg') !== null) : ?>
            <div class="alert alert-default-success" role="alert"><?= session('msg') ?></div>
         <?php endif ?>
         <?php if (session('msg2') !== null) : ?>
            <div class="alert alert-default-success" role="alert"><?= session('msg2') ?></div>
         <?php endif ?>

         <form action="<?= base_url('auth') ?>" method="post">
            <?= csrf_field() ?>
            <div class="input-group mb-3">
               <input type="email" name="email" inputmode="email" class="form-control" placeholder="<?= lang('Auth.email') ?>" value="<?= old('email') ?>" required>
               <div class="input-group-append">
                  <div class="input-group-text">
                     <span class="fas fa-envelope"></span>
                  </div>
               </div>
            </div>
            <div class="input-group mb-3">
               <input type="password" name="password" inputmode="text" class="form-control" autocomplete="current-password" placeholder="<?= lang('Auth.password') ?>" required>
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
               <!-- /.col -->
               <div class="col-4 mb-1">
                  <button type="submit" class="btn btn-primary btn-block"><?= lang('Auth.login') ?></button>
               </div>
               <!-- /.col -->
            </div>
         </form>
         <?php if (config('Auth')->allowSso) : ?>
            <div class="row mt-2">
               <!-- /.col -->
               <div class="col-4 mb-1">
                  <a href="<?= base_url('auth/ssologin') ?>" class="btn btn-success btn-block"><?= lang('Auth.sso') ?></a>
               </div>
               <!-- /.col -->
            </div>
         <?php endif ?>

         <?php if (config('Auth')->allowRegistration) : ?>
            <p class="m-1"><?= lang('Auth.needAccount') ?> <a href="<?= base_url('register') ?>"><?= lang('Auth.register') ?></a></p>
         <?php endif ?>
         <?php if (config('Auth')->allowReset) : ?>
            <p class="m-1"><?= lang('Auth.forgotPassword') ?> <a href="<?= base_url('reset') ?>"><?= lang('Auth.reset') ?></a></p>
         <?php endif ?>

      </div>
      <!-- /.card-body -->
   </div>
   <!-- /.card -->
</div>
<!-- /.login-box -->

<?php $this->endsection() ?>