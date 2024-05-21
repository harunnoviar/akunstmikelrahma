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
                        <h3 class="card-title">Edit Pengguna</h3>
                    </div>
                    <!-- /.card-header -->
                    <!-- form start -->

                    <form action="<?= base_url('admin/user_edit') ?>" method="post" class="form-horizontal">
                        <?= csrf_field() ?>
                        <div class="card-body">
                            <div class="mt-2">
                                <?php if (session('msg') !== null) { ?>
                                    <div class="alert alert-default-success" role="alert"><?= session('msg') ?></div>
                                <?php } ?>
                                <?php if (session('error') !== null) { ?>
                                    <div class="alert alert-default-danger" role="alert"><?= session('error') ?></div>
                                <?php } ?>
                            </div>
                            <div class="form-group row">
                                <label for="domain" class="col-sm-4 col-form-label">Domain</label>
                                <div class="col-sm-8">
                                    <div class="form-control bg-light">
                                        <?= $user['dom_name'] ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="email" class="col-sm-4 col-form-label">Email</label>
                                <div class="col-sm-8">
                                    <div class="form-control bg-light">
                                        <?= $user['u_email']  ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="ou_option" class="col-sm-4 col-form-label">Kategori [OU]</label>
                                <div class="col-sm-8">
                                    <select class="custom-select ou_select" name="ou_option" id="ou_option" onchange="changeOu()">
                                        <?php foreach ($ou_all as $o) { ?>
                                            <option value="<?= enkrip($o->id) ?>" <?= $o->id === $user['ou'] ? "selected" : "" ?>><?= $o->name  ?> </option>
                                        <?php } ?>
                                    </select>
                                    <!-- <div class="form-control bg-light">
                                        <?= $user['ou_name']  ?>
                                    </div> -->
                                </div>
                            </div>
                            <input type="text" id="user_id" name="id" value="<?= enkrip($user['u_id']) ?>" hidden>
                            <div class="form-group row">
                                <label for="firstname" class="col-sm-4 col-form-label">Nama Depan</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control <?= ($validation->hasError('firstname')) ? 'is-invalid' : '' ?>" id="firstname" name="firstname" value="<?= $user['firstname'] ?>" autofocus>
                                    <div class="invalid-feedback"> <?= $validation->getError('firstname'); ?></div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="lastname" class="col-sm-4 col-form-label">Nama Belakang</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control <?= ($validation->hasError('lastname')) ? 'is-invalid' : '' ?>" id="lastname" name="lastname" value="<?= $user['lastname'] ?>">
                                    <div class="invalid-feedback"> <?= $validation->getError('lastname'); ?></div>
                                </div>
                            </div>
                            <?php if ($user['ou_name'] != 'mhs') { ?>
                                <div class="form-group row">
                                    <label for="nip" class="col-sm-4 col-form-label">NIP/NITK</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control <?= ($validation->hasError('nip')) ? 'is-invalid' : '' ?>" id="nip" name="nip" value="<?= $user['nip'] ?>">
                                        <div class="invalid-feedback"> <?= $validation->getError('nip'); ?></div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="nidn" class="col-sm-4 col-form-label">NIDN</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control <?= ($validation->hasError('nidn')) ? 'is-invalid' : '' ?>" id="nidn" name="nidn" value="<?= $user['nidn'] ?>">
                                        <div class="invalid-feedback"> <?= $validation->getError('nidn'); ?></div>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="row">
                                <div class="col-sm-4"></div>
                                <div class="col-sm-5">
                                    <div class="form-check mb-2 text-right">
                                        <input type="checkbox" class="form-check-input" id="active" name="active" <?= $user['active'] === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="active">Aktif</label>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-check mb-2 text-right">
                                        <input type="checkbox" class="form-check-input" id="forbid" name="forbid" <?= $user['forbid'] === 't' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="forbid">Forbidden</label>
                                    </div>
                                </div>
                            </div>
                            <div class="aaa"></div>

                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Goup</label>
                                <div class="col-sm-8">
                                    <select class="bootstrap-select strings" name="group[]" data-width="100%" data-live-search="true" multiple required>
                                        <div class="ch_group">
                                            <?php foreach ($groups as $g) { ?>
                                                <option class="opt_group" value="<?= $g->g_id ?>"> <?= $g->g_name ?> </option>
                                            <?php } ?>
                                        </div>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="recoveryemail" class="col-sm-4 col-form-label">Email Pemulihan</label>
                                <div class="col-sm-8">
                                    <input type="email" class="form-control <?= ($validation->hasError('recoveryemail')) ? 'is-invalid' : '' ?>" id="recoveryemail" name="recoveryemail" value="<?= $user['recoveryemail'] ?>">
                                    <div class="invalid-feedback"> <?= $validation->getError('recoveryemail'); ?></div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="password" class="col-sm-4 col-form-label">Sandi</label>
                                <div class="col-sm-8 ">
                                    <div class="input-group">
                                        <input type="text" class="form-control <?= ($validation->hasError('password')) ? 'is-invalid' : '' ?>" id="password" name="password" placeholder="Masukkan Sandi">
                                        <div class="input-group-append">
                                            <input class="btn btn-outline-secondary" type="button" id="pass-generate" name="pass-generate" onclick="randomString()" value="Generate">
                                        </div>
                                        <div class="invalid-feedback"> <?= $validation->getError('password'); ?></div>
                                    </div>
                                    <small id="passwordHelp" class="form-text text-muted">Min. 8 - max. 24 character [a-zA-Z0-9] </small>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Ubah</button>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </form>
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
</section>
<script>
    $(document).ready(function() {
        $('.bootstrap-select').selectpicker();
        let user_id = $('#user_id').val();
        $.ajax({
            url: "<?php base_url() ?>/admin/grpuseridfetch",
            method: "POST",
            data: {
                user_id: user_id
            },
            cache: false,
            success: function(data) {
                console.log(data);
                var val1 = data.replace("[", "");
                var val2 = val1.replace("]", "");
                var values = val2;
                // console.log($(".strings option[value=3]").text());
                $.each(values.split(","), function(i, e) {
                    $(".strings option[value=" + e + "]").prop("selected", true).trigger('change');
                    $(".strings").selectpicker('refresh');
                });
            }

        });

    });

    // saat memilih ou lain
    function changeOu() {
        let ou_select = $(".ou_select").val();
        $('.bootstrap-select').selectpicker();
        $.ajax({
            url: "<?php base_url() ?>/admin/grpoufetch",
            method: "POST",
            data: {
                ou_id: ou_select
            },
            cache: false,
            success: function(data) {
                let obj = JSON.parse(data);
                let select_group = $('.bootstrap-select .strings');
                let opt_list = '';
                select_group.empty();

                $.each(obj, function(i, v) {
                    opt_list += '<option id="' + v.g_id + '" class="opt_group" value="' + v.g_id + '">' + v.g_name + '</option>';
                })
                select_group.html(opt_list);
                $(".strings").selectpicker('refresh');
            }

        });
    }

    function randomString() {
        var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
        var string_length = 8;
        var randomstring = '';
        for (var i = 0; i < string_length; i++) {
            var rnum = Math.floor(Math.random() * chars.length);
            randomstring += chars.substring(rnum, rnum + 1);
        }
        $('#password').val(randomstring);
    }
</script>