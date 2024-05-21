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
                        <button type="button" id="create_button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#createGroupModal"> <i class="fas fa-plus"></i></button>
                     </div>
                  </div>

               </div>
               <div class="card-body">
                  <div class=" table-responsive">
                     <table id="tbl_grp" class="table table-bordered table-striped table-hover">
                        <thead class="">
                           <tr>
                              <th>Id</th>
                              <th>Nama Group</th>
                              <th>Base Group DN</th>
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

<!-- Modal Create -->
<div class="modal fade" id="createGroupModal">
   <div class="modal-dialog ">
      <form method="post" id="create_form">
         <?php csrf_field() ?>
         <div class="modal-content">
            <div class="modal-header ">
               <h4 class="modal-title">Tambah Group</h4>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
               <div class="form-group row">
                  <label for="g_name" class="col-sm-4 col-form-label">Nama Group</label>
                  <div class="col-sm-8">
                     <input type="text" name="g_name" class="form-control" id="g_name" required>
                  </div>
               </div>
               <div class="form-group row">
                  <label for="base_group_dn" class="col-sm-4 col-form-label">Base Group DN</label>
                  <div class="col-sm-8">
                     <input type="text" name="base_group_dn" class="form-control" id="base_group_dn" required value="ou=groups,dc=stmikelrahma,dc=ac,dc=id">
                  </div>
               </div>
               <div class="form-group row">
                  <label for="g_desc" class="col-sm-4 col-form-label">Diskripsi</label>
                  <div class="col-sm-8">
                     <input type="text" name="g_desc" class="form-control" id="g_desc" required>
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
<div class="modal fade" id="editGroupModal">
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
               <div id="nama_group"></div>
               <div id="nama_group_dn"></div>
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
   let tbl_grp;
   $(document).ready(function() {
      tbl_grp = $('#tbl_grp').DataTable({
         'processing': true,
         'serverSide': true,
         'serverMethod': 'post',
         'ajax': {
            'url': '<?= base_url('/admin/grpfetch') ?>'
         },
         order: [
            [0, 'asc']
         ],
         'columns': [{
               data: 'g_id'
            },
            {
               data: 'g_name'
            },
            {
               data: 'base_group_dn'
            },
            {
               data: 'g_desc'
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
         url: '<?= base_url() ?>/admin/grpeditfetch',
         method: "POST",
         data: {
            id: id
         },
         dataType: 'JSON',
         success: function(data) {
            $('#editGroupModal').modal('show');
            $('#editGroupModal .modal-title').text('Edit Group');
            let i = '';
            let z = '';
            let selection;

            $('#editGroupModal #nama_group').html('<div class="form-group row "> <label for="g_name" class="col-sm-4 col-form-label">Nama Group</label> <div class="col-sm-8"> <input type="text" name="g_name" class="form-control" id="g_name" value="' + data.g_name + '" > <div id="g_name_err" class="text-danger error"></div></div></div>');
            $('#editGroupModal #nama_group_dn').html('<div class="form-group row"> <label for="base_group_dn" class="col-sm-4 col-form-label">Nama Group</label> <div class="col-sm-8"> <input type="text" name="base_group_dn" class="form-control" id="base_group_dn" value="' + data.base_group_dn + '" readonly="readonly" > <div id="base_group_dn_err" class="text-danger"></div></div></div>');
            $('#editGroupModal #deskripsi').html('<div class="form-group row "> <label for="g_desc" class="col-sm-4 col-form-label">Keterangan</label> <div class="col-sm-8"> <input type="text" name="g_desc" class="form-control " id="g_desc" value="' + data.g_desc + '" > <div id="g_desc_err" class="text-danger error"></div> </div></div>');
            $('#editGroupModal #domain').html('<div class="form-group row"><label for="domain" class="col-sm-4 col-form-label">Domain</label> <div class="col-sm-8"><select class="custom-select" id="domain" name="domain"> ' + i + '</select></div></div>');
            $('#editGroupModal #action').val('edit');
            $('#editGroupModal #hidden_id').val(data.g_id);
            $('#editGroupModal #submit_button').text('Simpan');

            // deteksi keypress
            $('#editGroupModal #aksi_form :input').on('keyup', function() {
               if ($(this).val().length == 0) {
                  $(this).removeClass('is-valid').addClass('is-invalid');
                  $('#editGroupModal #aksi_form .error').html("");
               } else {
                  $(this).removeClass('is-invalid').addClass('is-valid');
                  $('#editGroupModal #aksi_form .error').html("");
               }
            })
         }
      })

   });

   // saat simpan edit(aksi)
   $('#aksi_form').on('submit', function(event) {
      event.preventDefault();
      $.ajax({
         url: "<?= base_url() ?>/admin/grpeditaction",
         method: "POST",
         data: $(this).serialize(),
         dataType: "JSON",
         // beforeSend: function() {
         //    $('#aksi_form #submit_button').text('wait...');
         //    $('#aksi_form #submit_button').attr('disabled', 'disabled');
         // },

         success: function(data) {
            // console.log(data);
            if (data.error) {
               // $(":input").removeClass('is-invalid');
               // $('#editGroupModal .error').removeClass('text-danger').html('');
               $.each(data.error, function(key, value) {
                  $('#editGroupModal #' + key).addClass('is-invalid');
                  $('#editGroupModal #' + key + '_err').html(value);
               });
            } else {
               $('#editGroupModal #submit_button').attr('disabled', false);
               $('#editGroupModal').modal('hide');
               $('#message').html(data.message);
               $('#aksi_form')[0].reset();
               tbl_grp.ajax.reload();
               setTimeout(function() {
                  $('#message').html('');
               }, 5000);
            }
         }
      })
   });

   // form tambah
   $('#create_form').on('submit', function(event) {
      event.preventDefault();
      $.ajax({
         url: "<?= base_url() ?>/admin/grpcreate",
         method: "POST",
         data: $(this).serialize(),
         dataType: "JSON",
         success: function(data) {
            $('#createGroupModal').modal('hide');
            $('#message').html(data.message);
            tbl_grp.ajax.reload();
            $('#create_form').get(0).reset();
            setTimeout(function() {
               $('#message').html('');
            }, 5000);
         }
      })
   });

   // hapus group
   function grp_del(id, name) {
      Swal.fire({
         title: 'PERHATIAN',
         html: `Apakah yakin menghapus group <strong>${name}? </strong>`,
         icon: 'warning',
         showCancelButton: true,
         confirmButtonColor: '#3085d6',
         cancelButtonColor: '#d33',
         confirmButtonText: 'Ya, Hapus!',
         cancelButtonText: " Tidak!",
      }).then((result) => {
         if (result.isConfirmed) {
            $.ajax({
               url: '/admin/grpdel',
               type: "POST",
               data: {
                  id: id,
               },
               cache: false,
               success: function(data) {
                  obj = $.parseJSON(data);
                  if (obj.msg) {
                     Swal.fire("Terhapus", obj.msg, 'success');
                     tbl_grp.ajax.reload();
                  } else {
                     Swal.fire("Gagal", obj.error, 'error');
                     tbl_grp.ajax.reload();
                  }
               },
               failure: function(data) {
                  tbl_grp.ajax.reload();
                  Swal.fire("Gagal", "", 'error');
               }
            });
         } else {
            Swal.fire("Batal", "", 'error');
         }
      });
   }
</script>