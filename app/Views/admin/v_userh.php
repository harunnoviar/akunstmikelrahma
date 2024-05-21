<section class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 responsive">
            <div class="card card-light shadow shadow-lg">
               <div class="card-header">

               </div>
               <div class="card-body">
                  <div class=" table-responsive">
                     <table id="table_userh" class="table table-bordered table-striped table-hover">
                        <thead class="">
                           <tr>
                              <th>Id</th>
                              <th>Email</th>
                              <th>Nama Depan</th>
                              <th>Nama Belakang</th>
                              <th>Info</th>
                              <th>Delete By</th>
                              <th>Delete At</th>
                              <th>Recovery Email</th>
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

<!-- Tabel histori User Delete -->
<script type="text/javascript">
   let tbl_userh;
   $(document).ready(function() {
      tbl_log = $('#table_userh').DataTable({
         'processing': true,
         'serverSide': true,
         'serverMethod': 'post',
         'ajax': {
            'url': '<?= base_url('/admin/fetchuserh') ?>'
         },
         order: [
            [0, 'desc']
         ],
         'columns': [{
               data: 'id'
            },
            {
               data: 'email'
            },
            {
               data: 'firstname'
            },
            {
               data: 'lastname'
            },
            {
               data: 'info'
            },
            {
               data: 'deleted_by'
            },
            {
               data: 'deleted_at'
            },
            {
               data: 'recoveryemail'
            },
         ]
      });
   });
</script>