<?= $this->extend('layout/register_layout') ?>

<?= $this->section('content') ?>
<?php $encrypter = \Config\Services::encrypter(); ?>
<div class="col-lg-6 mt-5">
   <div class="card card-outline card-primary shadow shadow-lg">
      <div class="card-header text-center">
         Aktivasi Akun
      </div>
      <div class="card-body">
         <p class="login-box-msg h1"><?= session()->get('email_u') ?></p>

         <form action="<?= base_url('regstudentsave') ?>" method="post" id="form_regstudent">
            <?= csrf_field() ?>
            <div class="form-group">
               <label for="firstname">Nama Depan</label>
               <input type="text" class="form-control <?= ($validation->hasError('firstname')) ? 'is-invalid' : '' ?>" name="firstname" id="firstname" value="<?= session()->get('firstname_u') ?>">
               <span class="text-danger"><?= $validation->getError('firstname') ?></span>
            </div>
            <div class="form-group">
               <label for="lastname">Nama Belakang</label>
               <input type="text" class="form-control <?= ($validation->hasError('lastname')) ? 'is-invalid' : '' ?>" name="lastname" id="lastname" value="<?= session()->get('lastname_u') ?>">
               <span class="text-danger"><?= $validation->getError('lastname') ?></span>
            </div>
            <div class="form-group">
               <label for="password">Sandi</label>
               <div class="input-group">
                  <input type="password" class="form-control <?= ($validation->hasError('password')) ? 'is-invalid' : '' ?>" name="password" id="password" placeholder="Masukkan Sandi (Min. 8 karakter)">
                  <div class="input-group-text"><i class="fas fa-eye" id="eye_pass"></i></div>
               </div>
               <?php if ($validation->getError('password')) { ?>
                  <span class="text-danger"><?= $validation->getError('password') ?></span>
               <?php } else { ?>
                  <?php if (config('MyConfig')->google['sync']) { ?>
                     <span class="text-primary">Perhatian, sandi google anda akan berubah sesuai isian ini. </span>
                  <?php } ?>
               <?php } ?>
            </div>
            <label for="confpassword" class="form-label">Konfirmasi Sandi</label>
            <div class="form-group input-group">
               <input type="password" name="confpassword" class="form-control <?= ($validation->hasError('confpassword')) ? 'is-invalid' : '' ?>" id="confpassword" placeholder="Masukkan Ulang Sandi">
               <div class="input-group-text"> <i class="fas fa-eye" id="eye_confpass"></i> </div>
               <span class="text-danger"><?= $validation->getError('confpassword') ?></span>
            </div>
            <div class="form-group">
               <label for="recoveryemail">Email Pemulihan/ Cadangan</label>
               <input type="email" class="form-control <?= ($validation->hasError('recoveryemail')) ? 'is-invalid' : '' ?>" name="recoveryemail" id="recoveryemail" placeholder="Masukkan SELAIN email @<?= explode('@', session()->get('email_u'))[1] ?>">
               <span id="recoveryemail_err" class="text-danger"></span>
               <span class="text-danger"><?= $validation->getError('recoveryemail') ?></span>
            </div>
            <?php foreach ($groups as $g) { ?>
               <input name="group" class="input" type="text" value="<?= base64_encode(urlencode($encrypter->encrypt($g->g_id))) ?>" hidden>
            <?php } ?>
            <button type="submit" id="submit_button" class="btn btn-primary"><?= lang('Auth.register') ?></button>
         </form>

      </div>
   </div>
</div>

<script type="text/javascript">
   let domain = 'stmikelrahma.ac.id'
   $(function() {
      $('#form_regstudent').validate({
         rules: {
            firstname: {
               required: true,
            },
            lastname: {
               required: true,
            },
            password: {
               required: true,
               minlength: 8
            },
            confpassword: {
               equalTo: "#password"
            },
            recoveryemail: {
               required: true,
               email: true,
            },
         },
         messages: {
            firstname: {
               required: "Nama Depan tidak boleh kosong",
            },
            lastname: {
               required: "Nama Belakang tidak boleh kosong",
            },
            password: {
               required: "Sandi tidak boleh kosong",
               minlength: "Minimal 8 karakter"
            },
            confpassword: {
               equalTo: "Sandi harus sama",
            },
            recoveryemail: {
               required: "Email tidak boleh kosong",
               email: "Masukkan email yang valid"
            },
         },
         errorElement: 'span',
         errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
         },
         highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid');
         },
         unhighlight: function(element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
         }
      });

      // validasi email tidak boleh stmikelrahma.ac.id
      $('#form_regstudent #recoveryemail').on('keyup', function() {
         if ($(this).val().includes(domain)) {
            $(this).removeClass('is-valid').addClass('is-invalid');
            $('#form_regstudent #recoveryemail_err').text(`Tidak boleh email ${domain}`);
            // alert('salah');
         } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $('#form_regstudent #recoveryemail_err').text('');
         }
      })
   });

   // password show
   let password = $('#password');
   $('#eye_pass').click(function() {
      if (password.prop('type') == 'password') {
         $(this).attr('class', 'fas fa-eye-slash');
         password.attr('type', 'text');
      } else {
         $(this).attr('class', 'fas fa-eye');
         password.attr('type', 'password');
      }
   })
   let confpassword = $('#confpassword');
   $('#eye_confpass').click(function() {
      if (confpassword.prop('type') == 'password') {
         $(this).attr('class', 'fas fa-eye-slash');
         confpassword.attr('type', 'text');
      } else {
         $(this).attr('class', 'fas fa-eye');
         confpassword.attr('type', 'password');
      }
   })

   $('#submit_button').on('click', function(e) {
      e.preventDefault();
      let form = $("#form_regstudent");
      Swal.fire({
         title: 'PERHATIAN',
         // html: `<div class="alert alert-default-warning"> Sandi google akan berubah, apakah anda yakin aktivasi?</div>`,
         html: `<div class="alert alert-default-warning"> Apakah anda yakin aktivasi?</div>`,
         icon: 'warning',
         showCancelButton: true,
         confirmButtonColor: '#3085d6',
         cancelButtonColor: '#d33',
         confirmButtonText: 'Ya!',
         cancelButtonText: " Tidak!",
      }).then((result) => {
         if (result.isConfirmed) {
            form.submit();
         } else {
            Swal.fire("Batal", "", 'error');
         }
      });
   })
</script>

<?php $this->endsection() ?>