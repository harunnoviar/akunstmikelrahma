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
                    <!-- /.card-header -->
                    <!-- form start -->

                    <form action="<?= base_url('admin/save') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="card-body">
                            <div class="mt-2">
                                <?php if (session('msg') !== null) { ?>
                                    <div class="alert alert-success" role="alert"><?= session('msg') ?></div>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <label for="firstname">Nama Depan</label>
                                <input type="text" class="form-control <?= ($validation->hasError('firstname')) ? 'is-invalid' : '' ?>" id="firstname" name="firstname" value="<?= $user['firstname'] ?>" required>
                                <div class="invalid-feedback"> <?= $validation->getError('firstname'); ?></div>
                            </div>
                            <div class="form-group">
                                <label for="lastname">Nama Belakang</label>
                                <input type="text" class="form-control <?= ($validation->hasError('lastname')) ? 'is-invalid' : '' ?>" id="lastname" name="lastname" value="<?= $user['lastname'] ?>" required>
                                <div class="invalid-feedback"> <?= $validation->getError('lastname'); ?></div>
                            </div>
                            <div class="form-group">
                                <label for="password">Sandi</label>
                                <input type="password" class="form-control <?= ($validation->hasError('password')) ? 'is-invalid' : '' ?>" id="password" name="password" placeholder="Masukkan Sandi">
                                <small id="passwordHelp" class="form-text text-muted">Min. 8 - max. 24 character [a-zA-Z0-9] </small>
                                <div class="invalid-feedback"> <?= $validation->getError('password'); ?></div>
                            </div>
                            <div class="form-group">
                                <label for="confpassword">Ulangi Sandi</label>
                                <input type="password" class="form-control <?= ($validation->hasError('confpassword')) ? 'is-invalid' : '' ?>" id="confpassword" name="confpassword" placeholder="Ulangi Masukkan Sandi">
                                <div class="invalid-feedback"> <?= $validation->getError('confpassword'); ?></div>
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
</section>