<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>Error 404</title>

   <!-- Google Font: Source Sans Pro -->
   <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
   <!-- Font Awesome Icons -->
   <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/fontawesome-free/css/all.min.css">
   <!-- Theme style -->
   <link rel="stylesheet" href="<?= base_url() ?>/assets/dist/css/adminlte.min.css">
</head>

<body class="hold-transition layout-top-nav">
   <div class="wrapper">



      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
         <!-- Content Header (Page header) -->
         <div class="content-header">
            <div class="container">
               <div class="row mb-2">
                  <div class="col-sm-8">

                  </div><!-- /.col -->
                  <div class="col-sm-8">

                  </div><!-- /.col -->
               </div><!-- /.row -->
            </div><!-- /.container-fluid -->
         </div>
         <!-- /.content-header -->

         <!-- Main content -->
         <div class="content">
            <div class="container">
               <div class="row mt-4">
                  <div class="col-lg-2">
                  </div>

                  <div class="col-lg-8">
                     <div class="card card-warning card-outline shadow shadow-lg">
                        <div class="card-body">
                           <p class="card-text">
                           <div class="error-page">
                              <h2 class="headline text-warning">404</h2>
                              <div class="error-content">
                                 <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Page not found.</h3>
                                 <p>
                                    We could not find the page you were looking for.
                                    Meanwhile, you may <a href="<?= base_url() ?>">return to dashboard</a>.
                                 </p>
                              </div>
                           </div>
                           <!-- /.error-page -->

                           </p>
                        </div>
                     </div><!-- /.card -->
                  </div>

                  <div class="col-lg-2">
                  </div>

               </div>
               <!-- /.row -->
            </div><!-- /.container-fluid -->
         </div>
         <!-- /.content -->
      </div>
      <!-- /.content-wrapper -->

      <!-- Control Sidebar -->
      <aside class="control-sidebar control-sidebar-dark">
         <!-- Control sidebar content goes here -->
      </aside>
      <!-- /.control-sidebar -->

      <!-- Main Footer -->
      <!-- Main Footer -->
      <footer class="main-footer">
         <strong>Copyright &copy; 2023 <a href="https://www.uingusdur.ac.id">UINGUSDUR.AC.ID</a>.</strong>
         All rights reserved.
         <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0
         </div>
      </footer>
   </div>
   <!-- ./wrapper -->

   <!-- REQUIRED SCRIPTS -->

   <!-- jQuery -->
   <script href="<?= base_url() ?>/assets/plugins/jquery/jquery.min.js"></script>
   <!-- Bootstrap 4 -->
   <script href="<?= base_url() ?>/assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
   <!-- AdminLTE App -->
   <script href="<?= base_url() ?>/assets/dist/js/adminlte.min.js"></script>
   <!-- AdminLTE for demo purposes -->
   <script href="<?= base_url() ?>/assets/dist/js/demo.js"></script>
</body>

</html>