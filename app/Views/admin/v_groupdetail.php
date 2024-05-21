<section class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 responsive">
            <div class="card card-light shadow shadow-lg">
               <div class="card-header">
                  <div class="row">
                     <div class="col-lg-6">
                        <h2><?= $g_name ?></h2>
                        <input type="text" name="g_id" id="g_id" hidden value="<?= enkrip($g_id) ?>">
                     </div>
                     <div class="col-lg-6 text-right">
                        <a href="<?= base_url('admin/group') ?>" class="btn btn-sm btn-info"> <i class="fas fa-angle-left"></i> Kembali</a>

                     </div>
                  </div>
               </div>
               <div class="card-body">
                  <div class=" table-responsive">
                     <table id="table_user" class="table table-bordered table-striped table-hover">
                        <thead class="">
                           <tr>
                              <th>Id</th>
                              <th>Action</th>
                              <th>Email</th>
                              <th>Fullname</th>
                              <th>Created</th>
                              <th>By</th>
                              <th>Updated</th>
                              <th>By</th>
                              <th>Info</th>
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

<script>
   let table;
   $(document).ready(function() {
      let g_id = $("#g_id").val();
      table = $('#table_user').DataTable({
         'processing': true,
         'serverSide': true,
         'serverMethod': 'post',
         'ajax': {
            'url': '<?= base_url() ?>/admin/grpdetfetch/' + g_id
         },
         order: [
            [0, 'desc']
         ],
         columnDefs: [{
            orderable: false,
            targets: 1
         }],
         "createdRow": function(row, data, dataIndex) {
            if (data.active == "1") {
               $(row).addClass('bg-lime');
            }
         },
         'columns': [{
               data: 'id'
            },
            {
               data: 'action'

            },
            {
               data: 'email'
            },
            {
               data: 'dispname'
            },
            {
               data: 'created_at'
            },
            {
               data: 'created_by'
            },
            {
               data: 'updated_at'
            },
            {
               data: 'updated_by'
            },
            {
               data: 'info'
            },

         ]
      });
   });

   function hapus(email, id) {
      Swal.fire({
         title: 'PERHATIAN',
         html: `Apakah yakin menghapus <strong>${email}? </strong>`,
         icon: 'warning',
         showCancelButton: true,
         confirmButtonColor: '#3085d6',
         cancelButtonColor: '#d33',
         confirmButtonText: 'Ya, Hapus!',
         cancelButtonText: " Tidak!",
      }).then((result) => {
         if (result.isConfirmed) {
            $.ajax({
               url: '/admin/user_del',
               type: "POST",
               data: {
                  id: id,
                  email: email
               },
               cache: false,
               success: function(data) {
                  obj = $.parseJSON(data);
                  Swal.fire("Terhapus", obj.msg, 'success');
                  table.ajax.reload();
               },
               failure: function(data) {
                  table.ajax.reload();
                  Swal.fire("Gagal", "", 'error');
               }
            });
         } else {
            Swal.fire("Batal", "", 'error');
         }
      });
   }
</script>