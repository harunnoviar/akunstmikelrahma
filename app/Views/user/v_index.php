<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
            </div>
            <!-- left column -->
            <div class="col-md-6">
                <!-- general form elements -->
                <div class="card card-primary shadow shadow-lg">
                    <div class="card-header">
                        <h3 class="card-title">Profil</h3>
                    </div>
                    <div class="text-center mt-2">
                        <h2><?= session()->get('email_u') ?></h2>
                    </div>
                    <!-- /.card-header -->
                    <!-- form start -->

                    <form action="<?= base_url('user/save') ?>" method="post">
                        <?= csrf_field() ?>
                        <?php //dd($validation->getError('password'));
                        ?>
                        <div class="card-body">
                            <div class="mt-2">
                                <?php if (session()->getFlashdata('error')) : ?>
                                    <div class="alert alert-default-danger"><?= session()->getFlashdata('error') ?></div>
                                <?php endif; ?>
                                <?php if (session()->getFlashdata('msg')) { ?>
                                    <div class="alert alert-default-success" role="alert"><?= session()->getFlashdata('msg') ?></div>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <label for="firstname">Nama Depan</label>
                                <input type="text" class="form-control <?= ($validation->hasError('firstname')) ? 'is-invalid' : '' ?>" id="firstname" name="firstname" value="<?= $user['firstname'] ?>" required>
                                <span class="text-danger"><?= $validation->getError('lastname') ?></span>
                            </div>
                            <div class="form-group">
                                <label for="lastname">Nama Belakang</label>
                                <input type="text" class="form-control <?= ($validation->hasError('lastname')) ? 'is-invalid' : '' ?>" id="lastname" name="lastname" value="<?= $user['lastname'] ?>" required>
                                <span class="text-danger"><?= $validation->getError('lastname') ?></span>
                            </div>
                            <div class="form-group">
                                <label for="recoveryemail">Email Pemulihan</label>
                                <input type="email" class="form-control <?= ($validation->hasError('recoveryemail')) ? 'is-invalid' : '' ?>" id="recoveryemail" name="recoveryemail" value="<?= $user['recoveryemail'] ?>" required>
                                <span class="text-danger"><?= $validation->getError('recoveryemail') ?></span>
                            </div>
                            <div class="form-group">
                                <label for="password">Sandi</label>
                                <div class="input-group">
                                    <input type="password" class="form-control <?= ($validation->hasError('password')) ? 'is-invalid' : '' ?>" id="password" name="password" placeholder="Masukkan Sandi">
                                    <div class="input-group-text"><i class="fas fa-eye" id="eye_pass"></i></div>
                                </div>
                                <small id="passwordHelp" class="form-text text-muted">Min. 8 - max. 24 character [a-zA-Z0-9] </small>
                                <?php if ($validation->getError('password')) { ?>
                                    <span class="text-danger"><?= $validation->getError('password') ?></span>
                                <?php } else { ?>
                                    <?php if (config('MyConfig')->google['sync']) { ?>
                                        <span class="text-primary">Perhatian, sandi google anda akan berubah sesuai isian ini. </span>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <label for="confpassword">Ulangi Sandi</label>
                                <div class="input-group">
                                    <input type="password" class="form-control <?= ($validation->hasError('confpassword')) ? 'is-invalid' : '' ?>" id="confpassword" name="confpassword" placeholder="Ulangi Masukkan Sandi">
                                    <div class="input-group-text"> <i class="fas fa-eye" id="eye_confpass"></i> </div>
                                </div>
                                <span class="text-danger"><?= $validation->getError('confpassword') ?></span>
                            </div>
                        </div>
                        <!-- /.card-body -->

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Ubah Profil</button>
                        </div>
                    </form>
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
    <script type="text/javascript">
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
    </script>
</section>