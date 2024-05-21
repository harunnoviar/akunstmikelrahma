<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title><?= @($title) ?></title>

   <!-- Google Font: Source Sans Pro -->
   <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
   <!-- Font Awesome -->
   <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/fontawesome-free/css/all.min.css">
   <!-- icheck bootstrap -->
   <link rel="stylesheet" href="<?= base_url() ?>/assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
   <!-- jQuery -->
   <script src="<?= base_url() ?>/assets/plugins/jquery/jquery.min.js"></script>
   <!-- Theme style -->
   <link rel="stylesheet" href="<?= base_url() ?>/assets/dist/css/adminlte.min.css">
   <link rel="stylesheet" href="<?= base_url() ?>/assets/dist/css/styleku.css">
</head>

<body class="hold-transition login-page">

   <?= $this->renderSection('content') ?>

   <!-- Bootstrap 4 -->
   <script src="<?= base_url() ?>/assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
   <!-- AdminLTE App -->
   <script src="<?= base_url() ?>/assets/dist/js/adminlte.min.js"></script>
   <!-- Recaptcha google -->
   <script src='https://www.google.com/recaptcha/api.js'></script>
   <!-- <script src="https://www.google.com/recaptcha/api.js?render=6LfX0CUTAAAAACey7zusgA9CN7Zt7lp1hbUcU5lQ"></script> -->
   <?= $this->renderSection('pageScripts') ?>
</body>

</html>