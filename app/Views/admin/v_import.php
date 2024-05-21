<?= $this->extend('layout/page_layout') ?>

<?= $this->section('content') ?>
<section class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 responsive">
            <div class="card card-light shadow shadow-lg">
               <div class="card-header">
                  <!-- <h3>Impor dari CSV</h3> -->
               </div>
               <div class="card-body">
                  <?php if (isset($path)) { ?>
                     <form action="<?= base_url('admin/impcsvact') ?>" id="upload_csv" method="post" enctype="multipart/form-data">
                        <?php csrf_field() ?>
                        <div class="col-lg-4">
                           <div class="form-group">
                              <div class="mt-2">
                                 <div class="alert alert-warning">
                                    <p>Lokasi Unggah: <?= $path ?></p>
                                    <p>Nama File: <?= $filename ?></p>
                                    <input type="text" name="filename" value="<?= $filename ?>" hidden>
                                 </div>
                              </div>
                              <div class="mt-2">
                                 <button type="submit" name="import" id="import" class="btn btn-info">Impor</button>
                              </div>
                           </div>
                        </div>
                     </form>
                  <?php  } else { ?>
                     <form action="" id="upload_csv" method="post" enctype="multipart/form-data">
                        <?php csrf_field() ?>
                        <div class="col-lg-4">
                           <div class="form-group">
                              <div class="input-group">
                                 <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="csv_file" name="csv_file" accept=".csv">
                                    <label class="custom-file-label" for="csv_file">Pilih</label>
                                 </div>
                              </div>
                              <div class="mt-2">
                                 <input type="submit" name="upload" id="upload" class="btn btn-info" value="Unggah">
                              </div>
                           </div>
                        </div>
                     </form>
                  <?php } ?>

                  <div id="csv_file_data"></div>
               </div>
            </div>
         </div>
      </div>
   </div>
</section>
<?php $this->endSection() ?>