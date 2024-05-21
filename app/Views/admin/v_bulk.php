<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
            </div>
            <!-- left column -->
            <div class="col-md-6">
                <!-- general form elements -->
                <div class="mt-2">
                    <?php if (session('msg') !== null) { ?>
                        <div class="alert alert-default-success alert-dismissible" role="alert"><?= session('msg') ?></div>
                    <?php } ?>
                </div>
                <div class="mt-2">
                    <?php if (session('error') !== null) { ?>
                        <div class="alert alert-default-danger alert-dismissible" role="alert"><?= session('error') ?></div>
                    <?php } ?>
                </div>
                <div class="card card-primary card-tabs shadow shadow-lg">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs">
                            <li class="nav-item"><a class="nav-link <?= ($sub_menu === '' || $sub_menu
                                                                        === 'create') ? 'active' : '' ?>" href="<?= base_url('admin/bulk/create') ?>">Tambah</a></li>
                            <li class="nav-item"><a class="nav-link <?= ($sub_menu
                                                                        === 'delete') ? 'active' : '' ?>" href="<?= base_url('admin/bulk/delete') ?>" href="<?= base_url('admin/bulk/delete') ?>">Hapus</a></li>
                            <li class="nav-item"><a class="nav-link <?= ($sub_menu
                                                                        === 'reset') ? 'active' : '' ?>" href="<?= base_url('admin/bulk/reset') ?>" href="<?= base_url('admin/bulk/reset') ?>">Reset</a></li>
                        </ul>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="<?= ($sub_menu === '' || $sub_menu
                                            === 'create') ? 'active' : '' ?> tab-pane" id="tambah">
                                <p class="alert alert-default-info">
                                    Menu untuk membuat akun secara banyak dari impor file csv (maksimal 1000 row).
                                </p>
                                <p>
                                    Template: <a href="<?= base_url('/admin/bulkfile/create_bulk.csv') ?>">create_bulk.csv</a>
                                </p>
                                <p>
                                <form id="form-create" action="<?= base_url('admin/bulkcreate') ?>" method="post" enctype="multipart/form-data">
                                    <?= csrf_field(); ?>
                                    <div class="form-group col-lg-8">
                                        <label for="import_create">Impor CSV</label>
                                        <div class="input-group ">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input <?= $validation->getError('import_create') ? 'is-invalid' : '' ?>" name="import_create" id="import_create">
                                                <label class="custom-file-label" for="import_create">Pilih </label>
                                            </div>
                                        </div>
                                        <span class=" text-danger"><?= $validation->getError('import_create') ? $validation->getError('import_create') : '' ?></span>
                                    </div>
                                    <!-- <div class="form-group col-lg-8">
                                        <label for="separator">Delimiter</code></label>
                                        <select class="custom-select rounded-0 " name="separator" id="separator">
                                            <option value=",">,</option>
                                            <option value=";">;</option>
                                        </select>
                                    </div> -->
                                    <span class="float-left">
                                        <button id="btn-create" type="submit" class="btn btn-success">Tambah</button>
                                    </span>
                                </form>
                                </p>
                            </div>

                            <div class="<?= ($sub_menu
                                            === 'delete') ? 'active' : '' ?> tab-pane" id="hapus">
                                <p class="alert alert-default-danger">
                                    Menu untuk menghapus akun secara banyak dari impor file csv (maksimal 1000 row).
                                </p>
                                <p>
                                    Template: <a href="<?= base_url('/admin/bulkfile/delete_bulk.csv') ?>">delete_bulk.csv</a>
                                </p>
                                <p>
                                <form id="form-delete" action="<?= base_url('admin/bulkdelete') ?>" method="post" enctype="multipart/form-data">
                                    <?= csrf_field(); ?>
                                    <div class="form-group">
                                        <label for="exampleInputFile">Impor CSV</label>
                                        <div class="input-group col-lg-8">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input <?= $validation->getError('import_delete') ? 'is-invalid' : '' ?>" name="import_delete" id="import_delete">
                                                <label class="custom-file-label" for="import_delete">Pilih </label>
                                            </div>
                                        </div>
                                        <span class=" text-danger"><?= $validation->getError('import_delete') ? $validation->getError('import_delete') : '' ?></span>
                                    </div>
                                    <!-- <div class="form-group col-lg-8">
                                        <label for="separator">Delimiter</code></label>
                                        <select class="custom-select rounded-0 " name="separator" id="separator">
                                            <option value=",">,</option>
                                            <option value=";">;</option>
                                        </select>
                                    </div> -->
                                    <span class="float-left">
                                        <button id="btn-delete" type="submit" class="btn btn-danger">Hapus</button>
                                    </span>
                                </form>
                                </p>
                            </div>

                            <div class="<?= ($sub_menu
                                            === 'reset') ? 'active' : '' ?> tab-pane" id="reset">
                                <p class="alert alert-default-warning">
                                    Menu untuk mengatur ulang sandi akun secara banyak dari impor file csv, apabila field password kosong maka akan diacak oleh sistem (maksimal 1000 row).
                                </p>
                                <p>
                                    Template: <a href="<?= base_url('/admin/bulkfile/reset_bulk.csv') ?>">reset_bulk.csv</a>
                                </p>
                                <p>
                                <form id="form-reset" action="<?= base_url('admin/bulkreset') ?>" method="post" enctype="multipart/form-data">
                                    <?= csrf_field(); ?>
                                    <div class="form-group">
                                        <label for="exampleInputFile">Impor CSV</label>
                                        <div class="input-group col-lg-8">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input <?= $validation->getError('import_reset') ? 'is-invalid' : '' ?>" name="import_reset" id="import_reset">
                                                <label class="custom-file-label" for="import_reset">Pilih </label>
                                            </div>
                                        </div>
                                        <span class=" text-danger"><?= $validation->getError('import_reset')  ?></span>
                                    </div>
                                    <!-- <div class="form-group col-lg-8">
                                        <label for="separator">Delimiter</code></label>
                                        <select class="custom-select rounded-0 " name="separator" id="separator">
                                            <option value=",">,</option>
                                            <option value=";">;</option>
                                        </select>
                                    </div> -->
                                    <span class="float-left">
                                        <button id="btn-reset" type="submit" class="btn btn-warning">Reset</button>
                                    </span>
                                </form>
                                </p>
                            </div>

                        </div>
                        <!-- /.tab-content -->
                    </div>
                    <!-- /.card-body -->

                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {

        // form create
        $('#btn-create').on('click', function(event) {
            event.preventDefault();
            let form = $('#form-create');
            swal.fire({
                title: 'PERHATIAN',
                html: `Apakah yakin impor akun?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya!',
                cancelButtonText: " Tidak!",
            }).then((result) => {
                console.log(result);
                if (result.isConfirmed) {
                    form.submit();
                } else {
                    Swal.fire("Batal", "", 'error');
                }
            })
        });

        // form delete
        $('#btn-delete').on('click', function(event) {
            event.preventDefault();
            let form = $('#form-delete');
            swal.fire({
                title: 'PERHATIAN',
                html: `Apakah yakin hapus akun?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya!',
                cancelButtonText: " Tidak!",
            }).then((result) => {
                console.log(result);
                if (result.isConfirmed) {
                    form.submit();
                } else {
                    Swal.fire("Batal", "", 'error');
                }
            })
        });

        // form reset
        $('#btn-reset').on('click', function(event) {
            event.preventDefault();
            let form = $('#form-reset');
            swal.fire({
                title: 'PERHATIAN',
                html: `Apakah yakin hapus akun?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya!',
                cancelButtonText: " Tidak!",
            }).then((result) => {
                console.log(result);
                if (result.isConfirmed) {
                    form.submit();
                } else {
                    Swal.fire("Batal", "", 'error');
                }
            })
        });
    });
</script>