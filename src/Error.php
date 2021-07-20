<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>

    <style>
        body {
            height: 100%;
            background: #fff;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #4e7d50;
            font-weight: 300;
        }

        h1 {
            font-weight: lighter;
            letter-spacing: 0.8;
            font-size: 2.3rem;
            margin-top: 0;
            margin-bottom: 0;
            color: #4e7d50;
        }

        .wrap {
            margin-top: 20%;
            background: #fff;
            text-align: center;
        }

        p {
            margin-top: 1.5rem;
        }
    </style>
</head>

<body>
    <div class="wrap">
        <h1><?= $code ?> | <?= $message ?></h1>
    </div>
</body>

</html>