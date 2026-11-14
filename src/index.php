<?php

declare(strict_types=1);

$status = 'LAMP environment is running.';

?><!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AWS EC2 LAMP Environment</title>
</head>
<body>
    <h1><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></h1>
    <p>Apache and PHP are responding normally.</p>
</body>
</html>

