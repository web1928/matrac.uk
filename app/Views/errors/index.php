<!DOCTYPE html>
<html>

<head>
    <title><?= $code ?> - <?= $message ?></title>
    <style>
        body {
            font-family: sans-serif;
            text-align: center;
            padding: 100px;
        }

        h1 {
            font-size: 50px;
            margin: 0;
        }

        p {
            font-size: 20px;
            color: #666;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <h1><?= $code ?></h1>
    <p><?= $message ?></p>
    <p><a href="<?= url(' /') ?>">Go Home</a></p>
</body>

</html>