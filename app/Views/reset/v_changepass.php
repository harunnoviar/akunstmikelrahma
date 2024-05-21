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
         <?php if (session()->getFlashdata('msg')) : ?>
            <div class="alert alert-default-success"><?= session()->getFlashdata('msg') ?></div>
         <?php endif; ?>
         <div class=" text-center alert alert-default-light"><?= dekrip($email) ?></div>
         <form id="form-setpass" action="<?= base_url('reset/setpass') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $email ?>">
            <div class="form-group row">
               <label for="newpassword" class="col-sm-4 col-form-label">Sandi :</label>
               <div class="col-sm-8">
                  <div class="input-group">
                     <input type="password" class="form-control <?= ($validation->hasError('newpassword')) ? 'is-invalid' : '' ?>" id="newpassword" name="newpassword" value="">
                     <div class="input-group-text"><i class="fas fa-eye" id="eye_pass"></i></div>
                  </div>
                  <div class="invalid-feedback"> <?= $validation->getError('newpassword'); ?></div>
                  <small class="form-text text-muted">Min. 8 - max. 24 character [a-zA-Z0-9]</small>
               </div>
            </div>
            <div class="form-group row">
               <label for="newpassword2" class="col-sm-4 col-form-label">Ulang Sandi :</label>
               <div class="col-sm-8">
                  <div class="input-group">
                     <input type="password" class="form-control <?= ($validation->hasError('newpassword2')) ? 'is-invalid' : '' ?>" id="newpassword2" name="newpassword2" value="">
                     <div class="input-group-text"> <i class="fas fa-eye" id="eye_confpass"></i> </div>
                  </div>
                  <div class="invalid-feedback"> <?= $validation->getError('newpassword2'); ?></div>
               </div>
            </div>
            <div class="form-group row">
               <label for="token" class="col-sm-4 col-form-label">Token :</label>
               <div class="col-sm-8">
                  <input type="text" class="form-control <?= ($validation->hasError('token')) ? 'is-invalid' : '' ?>" id="token" name="token" value="">
                  <div class="invalid-feedback"> <?= $validation->getError('token'); ?></div>
               </div>
            </div>
      </div>
      <!-- /.card-body -->
      <div class="card-footer ">
         <button type="submit" class="btn btn-info float-right">Proses <i class="fas fa-arrow-circle-right"></i></button>
      </div>
      </form>
   </div>
   <!-- /.card -->
</div>
<!-- /.login-box -->

<script type="text/javascript">
   // password show
   let password = $('#newpassword');
   $('#eye_pass').click(function() {
      if (password.prop('type') == 'password') {
         $(this).attr('class', 'fas fa-eye-slash');
         password.attr('type', 'text');
      } else {
         $(this).attr('class', 'fas fa-eye');
         password.attr('type', 'password');
      }
   })
   let confpassword = $('#newpassword2');
   $('#eye_confpass').click(function() {
      if (confpassword.prop('type') == 'password') {
         $(this).attr('class', 'fas fa-eye-slash');
         confpassword.attr('type', 'text');
      } else {
         $(this).attr('class', 'fas fa-eye');
         confpassword.attr('type', 'password');
      }
   })
</script>
<?php $this->endsection() ?>