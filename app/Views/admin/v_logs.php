<section class="content">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12 responsive">
            <div class="card card-light shadow shadow-lg">
               <div class="card-header">
                  <div class="text-right mb-2">
                     <button type="button" id="refresh-on" name="refresh-on" class="badge badge-warning refresh-on" title="Refresh Off"></i>Refresh Off</button>
                  </div>
               </div>
               <div class="card-body">
                  <div class=" table-responsive">
                     <table id="table_log" class="table table-bordered table-striped table-hover">
                        <thead class="">
                           <tr>
                              <th>Tanggal</th>
                              <th>Id</th>
                              <th>Pesan</th>
                              <th>IP</th>
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

<!-- table log -->
<script type="text/javascript">
   let tbl_log;
   $(document).ready(function() {
      tbl_log = $('#table_log').DataTable({
         'processing': true,
         'serverSide': true,
         'serverMethod': 'post',
         'ajax': {
            'url': '<?= base_url('/admin/logfetch') ?>'
         },
         order: [
            [0, 'desc']
         ],
         'columns': [{
               data: 'created_at'
            },
            {
               data: 'user'
            },
            {
               data: 'message'
            },
            {
               data: 'ip'
            },
         ]
      });

      autorefresh = setInterval(tbl_log.ajax.reload, 5000);
      $(document).on('click', '.refresh-off', function() {
         $('.refresh-off').text('Refresh On').attr('class', 'badge badge-success refresh-on').attr('title', 'Refresh Off');
         autorefresh = setInterval(tbl_log.ajax.reload, 5000);
      });
      $(document).on('click', '.refresh-on', function() {
         $('.refresh-on').text('Refresh Off').attr('class', 'badge badge-warning refresh-off').attr('title', 'Refresh On');
         clearInterval(autorefresh);
      });
   });
</script>