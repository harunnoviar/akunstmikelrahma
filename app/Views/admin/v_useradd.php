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
                  <h3 class="card-title">Tambah Pengguna</h3>
               </div>
               <!-- /.card-header -->
               <div class="card-body">
                  <div class="mt-2">
                     <?php if (session('error') !== null) { ?>
                        <div class="alert alert-default-warning" role="alert"><?= session('error') ?></div>
                     <?php } ?>
                  </div>
                  <div class="mt-2">
                     <?php if (session('msg') !== null) { ?>
                        <div class="alert alert-default-success" role="alert"><?= session('msg') ?></div>
                     <?php } ?>
                  </div>
                  <form action="" method="POST">
                     <?= csrf_field() ?>
                     <div class="form-group row">
                        <label for="ou" class="col-sm-4 col-form-label">Kategori [OU] :</label>
                        <div class="col-sm-8">
                           <select class="custom-select " id="ou_option" name="ou_option" onchange="this.form.submit()">
                              <?php foreach ($getOu as $ou) { ?>
                                 <?php if ($ou['name'] === $ou_selected) { ?>
                                    <option value="<?= $ou['name']; ?>" selected><?= $ou['name']; ?></option>
                                 <?php } else { ?>
                                    <option value="<?= $ou['name']; ?>"><?= $ou['name']; ?></option>
                                 <?php } ?>
                              <?php } ?>
                           </select>
                        </div>
                     </div>
                  </form>
                  <form action="<?= base_url('admin/user_created') ?>" method="post">
                     <?= csrf_field() ?>
                     <input type="text" name="ou" id="ou" hidden value="<?= $ou_selected ?>">
                     <div class="form-group row">
                        <label for="domain_option" class="col-sm-4 col-form-label">Domain :</label>
                        <div class="col-sm-8">
                           <select class="form-control domain" id="domain_option" name="domain_option">
                              <?php foreach ($getDomain as $d) : ?>
                                 <option id="dom_<?= $d['d_id'] ?>" value="<?= $d['d_id']; ?>"><?= $d['d_id'] . '. ' . $d['dom_name']; ?>
                                 </option>
                              <?php endforeach; ?>
                           </select>
                        </div>
                     </div>
                     <div class="form-group row">
                        <label for="firstname" class="col-sm-4 col-form-label">Nama Depan :</label>
                        <div class="col-sm-8">
                           <input type="text" class="form-control <?= ($validation->hasError('firstname')) ? 'is-invalid' : '' ?>" id="firstname" name="firstname" value="">
                           <div class="invalid-feedback"> <?= $validation->getError('firstname'); ?></div>
                        </div>
                     </div>
                     <div class="form-group row">
                        <label for="lastname" class="col-sm-4 col-form-label">Nama Belakang :</label>
                        <div class="col-sm-8">
                           <input type="text" class="form-control <?= ($validation->hasError('lastname')) ? 'is-invalid' : '' ?>" id="lastname" name="lastname" value="">
                           <div class="invalid-feedback"> <?= $validation->getError('lastname'); ?></div>
                        </div>
                     </div>
                     <?php if ($ou_selected != 'mhs') { ?>
                        <div class="form-group row">
                           <label for="nip" class="col-sm-4 col-form-label">NIP/NITK :</label>
                           <div class="col-sm-8">
                              <input type="text" class="form-control <?= ($validation->hasError('nip')) ? 'is-invalid' : '' ?>" value="" name="nip" id="nip" autocomplete="off">
                              <div class="invalid-feedback"> <?= $validation->getError('nip'); ?></div>
                           </div>
                        </div>
                        <div class="form-group row">
                           <label for="nidn" class="col-sm-4 col-form-label">NIDN :</label>
                           <div class="col-sm-8">
                              <input type="text" class="form-control <?= ($validation->hasError('nidn')) ? 'is-invalid' : '' ?>" value="" name="nidn" id="nidn" autocomplete="off">
                              <div class="invalid-feedback"> <?= $validation->getError('nidn'); ?></div>
                           </div>
                        </div>
                        <?php if (!empty($units)) { ?>
                           <div class="form-group row">
                              <label class="col-sm-4 col-form-label">Satker</label>
                              <div class="col-sm-8">
                                 <select class="form-control" name="unit">
                                    <?php foreach ($units as $u) { ?>
                                       <option value="<?= $u['unit_id'] ?>"><?= $u['unit_id'] ?>. <?= $u['unit_name'] ?> </option>
                                    <?php } ?>
                                 </select>
                              </div>
                           </div>
                        <?php } ?>

                     <?php } ?>

                     <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Group :</label>
                        <div class="col-sm-8">
                           <select class="groups-select strings" name="group[]" data-width="100%" data-live-search="true" multiple required>
                              <?php foreach ($groups as $g) { ?>
                                 <option value="<?= $g->g_id ?>"> <?= $g->g_name ?> </option>
                              <?php } ?>
                           </select>
                        </div>
                     </div>
                     <div class="form-group row">
                        <label for="email" class="col-sm-4 col-form-label">Email</label>
                        <div class="col-sm-8">
                           <div class="input-group">
                              <input type="text" name="email" class="form-control col-lg  <?= ($validation->hasError('password')) ? 'is-invalid' : '' ?>" id="email" autocomplete="off">
                              <label class="input-group-text" id="label_domain" for="email"></label>
                              <input type="text" name="domain_hidden" id="domain_hidden" hidden>
                           </div>
                           <small class="form-text text-muted">Isikan tanpa @domain</small>
                        </div>
                     </div>
                     <div class="form-group row">
                        <label for="password" class="col-sm-4 col-form-label">Password</label>
                        <div class="col-sm-8">
                           <div class="input-group">
                              <input type="text" class="form-control <?= ($validation->hasError('password')) ? 'is-invalid' : '' ?>" value="" name="password" id="password" autocomplete="off">
                              <div class="input-group-append">
                                 <input class="btn btn-outline-secondary" type="button" id="pass-generate" name="pass-generate" onclick="randomString()" value="Generate">
                              </div>
                           </div>
                           <div class="invalid-feedback"> <?= $validation->getError('password'); ?></div>
                        </div>
                     </div>
                     <div class="form-group row">
                        <label for="recoveryemail" class="col-sm-4 col-form-label">Email Pemulihan</label>
                        <div class="col-sm-8">
                           <div class="input-group">
                              <input type="email" class="form-control " value="" name="recoveryemail" id="recoveryemail" autocomplete="off">
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-sm">
                           <div class="form-check mb-2 text-right">
                              <label for=""> <input type="checkbox" class="form-check-input" id="active" name="active"> Aktif</label>
                           </div>
                        </div>
                     </div>

                     <div class="text-right">
                        <button type="submit" class="btn btn-primary"> <i class="fas fa-plus"></i>Tambah</button>
                     </div>
                  </form>
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
      $('.groups-select').selectpicker();

      // untuk replace label domain pada email
      let dom_default = $(".domain option:selected").text();
      let label_domain = $("#label_domain");
      let dom_arr = dom_default.split(" ");
      let domain = dom_arr[1];
      let input_domain = $("#domain_hidden");
      $('input[name="domain_hidden"]').attr('value', domain);
      label_domain.text('@' + domain);
      $(".domain").on('change', function(e) {
         let option_selected = this.options[this.selectedIndex].text;
         let dom_arr = option_selected.split(" ");
         let domain = dom_arr[1];
         $('input[name="domain_hidden"]').attr('value', domain);
         label_domain.text('@' + domain);
         // console.log(domain);
      });

   });

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