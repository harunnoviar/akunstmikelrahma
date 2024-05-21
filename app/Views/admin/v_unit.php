<section class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 responsive">
            <div class="card card-light shadow shadow-lg">
               <div class="card-header">
                  <div class="row">
                     <div class="col-lg-6 ">
                        <div id="message"></div>
                     </div>
                     <div class="col-lg-6 text-right ">
                        <button type="button" id="add_button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addUnitModal"> <i class="fas fa-plus"></i></button>
                     </div>
                  </div>

               </div>
               <div class="card-body">
                  <div class=" table-responsive">
                     <table id="tbl_satker" class="table table-bordered table-striped table-hover">
                        <thead class="">
                           <tr>
                              <th>Id</th>
                              <th>Nama Satker</th>
                              <th>Deskripsi</th>
                              <th>Aksi</th>
                           </tr>
                        </thead>
                     </table>
                  </div>
               </div>
               <!-- /.card-body -->
            </div>
            <!-- /.card -->
         </div>
         <!-- /.col -->
         <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
</section>

<!-- Modal Add -->
<div class="modal fade" id="addUnitModal">
   <div class="modal-dialog ">
      <form method="post" id="add_form">
         <?php csrf_field() ?>
         <div class="modal-content">
            <div class="modal-header ">
               <h4 class="modal-title">Tambah SatKer</h4>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
               <div class="form-group row">
                  <label for="unit_name" class="col-sm-4 col-form-label">Nama SatKer</label>
                  <div class="col-sm-8">
                     <input type="text" name="unit_name" class="form-control" id="unit_name" required>
                  </div>
               </div>
               <div class="form-group row">
                  <label for="unit_desc" class="col-sm-4 col-form-label">Diskripsi</label>
                  <div class="col-sm-8">
                     <input type="text" name="unit_desc" class="form-control" id="unit_desc" required>
                  </div>
               </div>
            </div>
            <div class="modal-footer justify-content-between">
               <button type="button" id="cancel_button" class="btn btn-default" data-dismiss="modal">Tutup</button>
               <button type="submit" id="submit_button" class="btn btn-primary"><i class="fas fa-plus"></i>Tambah</button>
            </div>
         </div>
      </form>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editUnitModal">
   <div class="modal-dialog ">
      <form method="post" id="aksi_form">
         <div class="modal-content">
            <div class="modal-header ">
               <h4 class="modal-title"></h4>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
               <div id="nama_unit"></div>
               <div id="deskripsi"></div>
            </div>
            <div class="modal-footer justify-content-between">
               <input type="hidden" name="action" id="action" value="" />
               <input type="hidden" name="hidden_id" id="hidden_id" value="" />
               <button type="button" id="cancel_button" class="btn btn-default" data-dismiss="modal">Tutup</button>
               <button type="submit" id="submit_button" class="btn btn-primary"></button>
            </div>
         </div>
      </form>
      <!-- /.modal-content -->
   </div>
   <!-- /.modal-dialog -->
</div>


<!-- Tabel category -->
<script type="text/javascript">
   let tbl_satker;
   $(document).ready(function() {
      tbl_satker = $('#tbl_satker').DataTable({
         'processing': true,
         'serverSide': true,
         'serverMethod': 'post',
         'ajax': {
            'url': '<?= base_url('/admin/unitfetch') ?>'
         },
         order: [
            [0, 'asc']
         ],
         'columns': [{
               data: 'unit_id'
            },
            {
               data: 'unit_name'
            },
            {
               data: 'description'
            },
            {
               data: 'action'
            },
         ]
      });
   });

   // saat klik edit
   $(document).on('click', '.edit', function() {
      let id = $(this).data('id');
      $.ajax({
         url: '<?= base_url() ?>/admin/uniteditfetch',
         method: "POST",
         data: {
            id: id
         },
         dataType: 'JSON',
         success: function(data) {
            $('#editUnitModal').modal('show');
            $('#editUnitModal .modal-title').text('Edit SatKer');
            $('#editUnitModal #nama_unit').html('<div class="form-group row"> <label for="unit_name" class="col-sm-4 col-form-label">Nama Unit</label> <div class="col-sm-8"> <input type="text" name="unit_name" class="form-control" id="unit_name" value="' + data.unit_name + '" > <div id="unit_name_err" class="text-danger error"></div></div></div>');
            $('#editUnitModal #deskripsi').html('<div class="form-group row"> <label for="unit_desc" class="col-sm-4 col-form-label">Keterangan</label> <div class="col-sm-8"> <input type="text" name="unit_desc" class="form-control" id="unit_desc" value="' + data.description + '" > <div id="unit_desc_err" class="text-danger error"></div> </div></div>');
            $('#editUnitModal #action').val('edit');
            $('#editUnitModal #hidden_id').val(data.unit_id);
            $('#editUnitModal #submit_button').text('Simpan');

            // deteksi keypress
            $('#editUnitModal #aksi_form :input').on('keyup', function() {
               if ($(this).val().length == 0) {
                  $(this).removeClass('is-valid').addClass('is-invalid');
                  $('#editUnitModal #aksi_form .error').html("");
               } else {
                  $(this).removeClass('is-invalid').addClass('is-valid');
                  $('#editUnitModal #aksi_form .error').html("");
               }
            })
         }
      })

   });

   // saat simpan edit(aksi)
   $('#aksi_form').on('submit', function(event) {
      event.preventDefault();
      $.ajax({
         url: "<?= base_url() ?>/admin/uniteditaction",
         method: "POST",
         data: $(this).serialize(),
         dataType: "JSON",

         success: function(data) {
            if (data.error) {
               $.each(data.error, function(key, value) {
                  $('#editUnitModal #' + key).addClass('is-invalid');
                  $('#editUnitModal #' + key + '_err').html(value);
               });
            } else {
               $('#editUnitModal #submit_button').attr('disabled', false);
               $('#editUnitModal').modal('hide');
               $('#message').html(data.message);
               $('#aksi_form')[0].reset();
               tbl_satker.ajax.reload();
               setTimeout(function() {
                  $('#message').html('');
               }, 5000);
            }
         }
      })
   });

   // form tambah
   $('#add_form').on('submit', function(event) {
      event.preventDefault();
      $.ajax({
         url: "<?= base_url() ?>/admin/unitadd",
         method: "POST",
         data: $(this).serialize(),
         dataType: "JSON",
         success: function(data) {
            $('#addUnitModal').modal('hide');
            $('#message').html(data.message);
            tbl_satker.ajax.reload();
            $('#add_form')[0].reset();
            // $('#add_form').trigger('reset');
            setTimeout(function() {
               $('#message').html('');
            }, 5000);
         }
      })
   });

   // hapus group
   function unit_del(id, name) {
      Swal.fire({
         title: 'PERHATIAN',
         html: `Apakah yakin menghapus unit <strong>${name}? </strong>`,
         icon: 'warning',
         showCancelButton: true,
         confirmButtonColor: '#3085d6',
         cancelButtonColor: '#d33',
         confirmButtonText: 'Ya, Hapus!',
         cancelButtonText: " Tidak!",
      }).then((result) => {
         if (result.isConfirmed) {
            $.ajax({
               url: '/admin/unitdel',
               type: "POST",
               data: {
                  id: id,
               },
               cache: false,
               success: function(data) {
                  obj = $.parseJSON(data);
                  if (obj.msg) {
                     Swal.fire("Terhapus", obj.msg, 'success');
                     tbl_satker.ajax.reload();
                  } else {
                     Swal.fire("Gagal", obj.error, 'error');
                     tbl_satker.ajax.reload();
                  }
               },
               failure: function(data) {
                  tbl_satker.ajax.reload();
                  Swal.fire("Gagal", "", 'error');
               }
            });
         } else {
            Swal.fire("Batal", "", 'error');
         }
      });
   }
</script>