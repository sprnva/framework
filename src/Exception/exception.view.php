<?php

use App\Core\App;
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel='icon' href='<?= public_url('/favicon.ico') ?>' type='image/ico' />
    <title>
        <?= "ERROR | " . App::get('config')['app']['name']; ?>
    </title>

    <link rel="stylesheet" href="<?= public_url('/assets/sprnva/css/bootstrap.min.css') ?>">

    <style>
        body {
            background-color: #eef1f4;
            color: #00096f;
        }
    </style>

    <script src="<?= public_url('/assets/sprnva/js/jquery-3.6.0.min.js') ?>"></script>
    <script src="<?= public_url('/assets/sprnva/js/popper.min.js') ?>"></script>
    <script src="<?= public_url('/assets/sprnva/js/bootstrap.min.js') ?>"></script>
</head>
<div class="container">
    <div class="row justify-content-md-center">
        <div class="col-md-8">
            <div class="card" style="margin-top: 10%;background-color: #fff; border: 0px; border-radius: 3px; box-shadow: 0 4px 5px 0 rgba(0,0,0,0.2);padding: 10px;">
                <div class="card-body d-flex flex-column">

                    <p class="mt-2 mb-0" style="font-size: 18px;font-weight: 500;"><?= $message ?></p>
                    <small class="text-muted"><?= $exeption ?></small>
                    <small class="text-muted mt-4">Sprnva blast</small>

                </div>
            </div>
        </div>
    </div>
</div>

</body>

</html>