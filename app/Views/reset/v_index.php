<?= $this->extend('layout/auth_layout') ?>

<?= $this->section('content') ?>
<div class="login-box">
   <!-- /.login-logo -->
   <div class="card card-cyan shadow shadow-lg">
      <div class="card-header text-center">
         <h3 class="">Atur Ulang Sandi</h3>
      </div>
      <div class="card-body">
         <?php if (session()->getFlashdata('err')) : ?>
            <div class="alert alert-default-danger"><?= session()->getFlashdata('err') ?></div>
         <?php endif; ?>
         <p class=" text-center">Masukkan Email STMIK EL RAHMA</p>
         <form id="form-reset" action="" method="post">
            <?= csrf_field() ?>
            <div class="input-group mb-0">
               <input type="email" name="email" inputmode="email" class="form-control <?= ($validation->hasError('email')) ? 'is-invalid' : '' ?>" placeholder="" value="<?= old('email') ?>">
               <div class="invalid-feedback"> <?= $validation->getError('email'); ?></div>
            </div>
            <small class="form-text text-muted">(Misal: namaemail@stmikelrahma.ac.id)</small>
            <div class="input-group mt-3">
               <div class="g-recaptcha" data-sitekey="<?= $recaptchaSite ?>">
               </div>
            </div>
      </div>
      <div class="card-footer ">
         <a type="button" class="btn btn-warning float-left" href="<?= base_url() ?>"> <i class="fas fa-arrow-circle-left"></i> Batal</a>
         <button type="submit" class="btn btn-info float-right">Lanjut <i class="fas fa-arrow-circle-right"></i></button>
      </div>
      </form>
      <!-- /.card-body -->
   </div>
   <!-- /.card -->
</div>
<!-- /.login-box -->
<script>
   function onSubmit(token) {
      document.getElementById("form-reset").submit();
   }
</script>
<?php $this->endsection() ?>