<?= $this->extend('layout/register_layout') ?>

<?= $this->section('content') ?>
<div class="col-lg-6 mt-5">
   <div class="card card-outline card-primary shadow shadow-lg">
      <div class="card-header text-center">
         Register App
      </div>
      <div class="card-body">
         <?php if (isset($validation)) : ?>
            <div class="alert alert-danger"><?= $validation->listErrors() ?></div>
         <?php endif; ?>
         <form action="<?= base_url('appregister') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-group">
               <label for="username">Username</label>
               <input type="text" class="form-control" name="username" id="username" placeholder="Enter Username">
            </div>
            <div class="form-group">
               <label for="firstname">Firstname</label>
               <input type="text" class="form-control" name="firstname" id="firstname" placeholder="Enter firstname">
            </div>
            <div class="form-group">
               <label for="lastname">Lastname</label>
               <input type="text" class="form-control" name="lastname" id="lastname" placeholder="Enter lastname">
            </div>
            <div class="form-group">
               <label for="email">Email address</label>
               <input type="email" class="form-control" name="email" id="email" placeholder="Enter email">
            </div>
            <div class="form-group">
               <label for="password">Password</label>
               <input type="password" class="form-control" name="password  " id="password" placeholder="Enter Password">
            </div>
            <div class="mb-3">
               <label for="confpassword" class="form-label">Confirm Password</label>
               <input type="password" name="confpassword" class="form-control" id="confpassword" placeholder="Enter  Confirm Password">
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
         </form>
      </div>
   </div>
</div>

<?php $this->endsection() ?>