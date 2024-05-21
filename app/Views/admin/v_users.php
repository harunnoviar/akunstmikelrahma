<section class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 responsive">
            <div class="card card-light shadow shadow-lg">
               <div class="card-header">
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
                  <div class="row">
                     <div class="col-lg-6">
                        <a href="<?= base_url('admin/bulk') ?>" class="btn btn-sm btn-info">Impor<i class="fas fa-file-import"></i></a>
                     </div>
                     <div class="col-lg-6 text-right">
                        <a href="<?= base_url('admin/user_add') ?>" class="btn btn-sm btn-primary"> <i class="fas fa-user-plus"></i></a>
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
                              <!-- <th>Category</th> -->
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
      table = $('#table_user').DataTable({
         'processing': true,
         'serverSide': true,
         'serverMethod': 'post',
         'ajax': {
            'url': '<?= base_url('/admin/users_fetch') ?>'
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
            // {
            //    data: 'category'
            // },
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