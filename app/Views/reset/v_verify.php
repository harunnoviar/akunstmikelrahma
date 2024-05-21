<?= $this->extend('layout/auth_layout') ?>

<?= $this->section('content') ?>
<div class="login-box">
   <!-- /.login-logo -->
   <div class="card card-cyan shadow shadow-lg">
      <div class="card-header text-center">
         <h3 class="">Verifikasi Email Pemulihan</h3>
      </div>
      <div class="card-body">
         <?php if (session()->getFlashdata('err')) : ?>
            <div class="alert alert-default-danger"><?= session()->getFlashdata('err') ?></div>
         <?php endif; ?>
         <p class=" text-center">Isikan email pemulihan secara lengkap</p>
         <form id="form-verify" action="<?= base_url('reset/viaemail') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="recoveryemail" value="<?= $recoveryEmail ?>">
            <input type="hidden" name="id" value="<?= $id ?>">
            <p class="alert alert-default-info h4"><?= $recoveryEmailMask ?></p>
            <div class="input-group mb-0">
               <input type="email" name="recoveryemail2" inputmode="recoveryemail2" class="form-control <?= ($validation->hasError('recoveryemail2')) ? 'is-invalid' : '' ?>" placeholder="Email Pemulihan" required>
               <div class="invalid-feedback"> <?= $validation->getError('recoveryemail2'); ?></div>
            </div>
      </div>
      <!-- /.card-body -->
      <div class="card-footer ">
         <button type="submit" class="btn btn-info float-right">Lanjut <i class="fas fa-arrow-circle-right"></i></button>
      </div>
   </div>
   </form>
   <!-- /.card -->
</div>
<!-- /.login-box -->
<?php $this->endsection() ?>