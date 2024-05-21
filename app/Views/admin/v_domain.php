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
                        <button type="button" id="create_button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#createModal"> <i class="fas fa-user-plus"></i></button>
                     </div>
                  </div>

               </div>
               <div class="card-body">
                  <div class=" table-responsive">
                     <table id="table_ctg" class="table table-bordered table-striped table-hover">
                        <thead class="">
                           <tr>
                              <th>Id</th>
                              <th>Nama Domain</th>
                              <th>Keterangan</th>
                              <th>Created At</th>
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

<div class="modal fade" id="createModal">
   <div class="modal-dialog ">
      <form method="post" id="create_form">
         <?php csrf_field() ?>
         <div class="modal-content">
            <div class="modal-header ">
               <h4 class="modal-title">Tambah Domain Email</h4>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
               <div class="form-group row">
                  <label for="name" class="col-sm-4 col-form-label">Nama Domain</label>
                  <div class="col-sm-8">
                     <input type="text" name="name" class="form-control" id="name" required placeholder="contoh: xxx.stmikelrahma.ac.id">
                  </div>
               </div>
               <div class="form-group row">
                  <label for="description" class="col-sm-4 col-form-label">Keterangan</label>
                  <div class="col-sm-8">
                     <input type="text" name="description" class="form-control" id="description" required>
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
<!-- /.modal -->

<div class="modal fade" id="editModal">
   <div class="modal-dialog ">
      <form method="post" id="aksi_form">
         <div class="modal-content">
            <div class="modal-header ">
               <h4 class="modal-title">Large Modal</h4>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
               <div id="nama_domain"></div>
               <div id="keterangan"></div>
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
<!-- /.modal -->



<!-- Tabel category -->
<script type="text/javascript">
   let tbl_ctg;
   $(document).ready(function() {
      tbl_ctg = $('#table_ctg').DataTable({
         'processing': true,
         'serverSide': true,
         'serverMethod': 'post',
         'ajax': {
            'url': '<?= base_url('/admin/domfetch') ?>'
         },
         order: [
            [0, 'asc']
         ],
         'columns': [{
               data: 'id'
            },
            {
               data: 'name'
            },
            {
               data: 'description'
            },
            {
               data: 'created_at'
            },
            {
               data: 'action'
            },
         ]
      });
   });

   // saat klik edit
   $(document).on('click', '.edit', function() {
      var id = $(this).data('id');
      $.ajax({
         url: '<?= base_url() ?>/admin/domeditfetch',
         method: "POST",
         data: {
            id: id
         },
         dataType: 'JSON',
         success: function(data) {
            $('#editModal #submit_button').attr('disabled', false);
            $('#editModal').modal('show');
            $('#editModal .modal-title').text('Edit Domain');
            let dom = data.domain;
            let i = '';
            let z = '';
            let selection;
            let disabled;
            data.protected === 't' ? readonly = 'readonly' : '';
            // console.log(data);

            $.each(dom, function(k, v) {
               // console.log(dom[k].name);
               selection = (dom[k].id === data.d_id) ? 'selected' : '';
               z = '<option value="' + dom[k].id + '" ' + selection + '>' + dom[k].name + '</option>';
               i = i + z;
            });

            $('#editModal #nama_domain').html('<div class="form-group row"> <label for="name" class="col-sm-4 col-form-label">Nama Domain</label> <div class="col-sm-8"> <input type="text" name="name" class="form-control" id="name" value="' + data.name + '" ' + readonly + '> </div></div>');
            $('#editModal #keterangan').html('<div class="form-group row"> <label for="description" class="col-sm-4 col-form-label">Keterangan</label> <div class="col-sm-8"> <input type="text" name="description" class="form-control" id="description" value="' + data.description + '"> </div></div>');
            $('#editModal #action').val('edit');
            $('#editModal #hidden_id').val(data.id);

            $('#editModal #submit_button').text('Simpan');
         }
      })
   });

   // saat simpan edit(aksi)
   $('#aksi_form').on('submit', function(event) {
      event.preventDefault();
      $.ajax({
         url: "<?= base_url() ?>/admin/domeditaction",
         method: "POST",
         data: $(this).serialize(),
         dataType: "JSON",
         beforeSend: function() {
            $('#aksi_form #submit_button').text('wait...');
            $('#aksi_form #submit_button').attr('disabled', 'disabled');
         },

         success: function(data) {
            $('#editModal #submit_button').attr('disabled', false);
            $('#editModal').modal('hide');
            $('#message').html(data.message);
            tbl_ctg.ajax.reload(null, false);
            setTimeout(function() {
               $('#message').html('');
            }, 5000);
         }
      })
   });

   // form tambah
   $('#create_form').on('submit', function(event) {
      event.preventDefault();
      $.ajax({
         url: "<?= base_url() ?>/admin/domcreate",
         method: "POST",
         data: $(this).serialize(),
         dataType: "JSON",
         success: function(data) {
            $('#createModal').modal('hide');
            $('#message').html(data.message);
            $('#create_form').get(0).reset();
            tbl_ctg.ajax.reload(null, false);
            setTimeout(function() {
               $('#message').html('');
            }, 5000);
         }
      })
   });

   // hapus kategori
   function ctg_del(id) {
      Swal.fire({
         title: 'PERHATIAN',
         html: `Apakah yakin menghapus <strong>${id}? </strong>`,
         icon: 'warning',
         showCancelButton: true,
         confirmButtonColor: '#3085d6',
         cancelButtonColor: '#d33',
         confirmButtonText: 'Ya, Hapus!',
         cancelButtonText: " Tidak!",
      }).then((result) => {
         if (result.isConfirmed) {
            $.ajax({
               url: '/admin/domdel',
               type: "POST",
               data: {
                  id: id,
               },
               cache: false,
               success: function(data) {
                  obj = $.parseJSON(data);
                  if (obj.msg) {
                     Swal.fire("Terhapus", obj.msg, 'success');
                     tbl_ctg.ajax.reload(null, false);
                  } else {
                     Swal.fire("Gagal", obj.error, 'error');
                     tbl_ctg.ajax.reload(null, false);
                  }
               },
               failure: function(data) {
                  tbl_ctg.ajax.reload(null, false);
                  Swal.fire("Gagal", "", 'error');
               }
            });
         } else {
            Swal.fire("Batal", "", 'error');
         }
      });
   }
</script>