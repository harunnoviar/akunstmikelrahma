<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title><?= @$title ?></title>

   <!-- Google Font: Source Sans Pro -->
   <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
   <!-- Font Awesome -->
   <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/fontawesome-free/css/all.min.css">
   <!-- icheck bootstrap -->
   <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
   <!-- Theme style -->
   <link rel="stylesheet" href="<?= base_url() ?>/assets/dist/css/adminlte.min.css">
   <!-- jQuery -->
   <script src="<?= base_url() ?>/assets/plugins/jquery/jquery.min.js"></script>
   <!-- Bootstrap 4 -->
   <script src="<?= base_url() ?>/assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
   <!-- Validate-->
   <script src="<?= base_url() ?>/assets/plugins/jquery-validation/jquery.validate.js"></script>
   <script src="<?= base_url() ?>/assets/plugins/jquery-validation/additional-methods.min.js"></script>
   <!-- Sweetalert -->
   <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/sweetalert2/sweetalert2.min.css">

</head>

<body class="hold-transition login-page">

   <?= $this->renderSection('content') ?>

   <!-- AdminLTE App -->
   <script src="<?= base_url() ?>/assets/dist/js/adminlte.min.js"></script>
   <!-- Sweatalert -->
   <script src="<?= base_url() ?>/assets/plugins/sweetalert2/sweetalert2.min.js"></script>
   <?= $this->renderSection('pageScripts') ?>
</body>

</html>