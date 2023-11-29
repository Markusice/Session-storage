<?php

declare(strict_types=1);

require_once './Auth.php';
require_once './UserStorage.php';
require_once './utils.php';

session_start([
    'cookie_lifetime' => 86400,
]);

$auth = new Auth(new UserStorage());

if ($auth->isAuthenticated())
    redirect('index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [];
    $errors = [];

    if (validateLogin($_POST, $data, $errors)) {
        if (!$loggedInUser = $auth->authenticate($data['username'], $data['password']))
            $errors['global'] = 'Hibás jelszó megadva!';
        else {
            $auth->login($loggedInUser);
            redirect('index.php');
        }
    }
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title>Belépési oldal</title>

    <link rel="stylesheet" href="./dist/output.css">
</head>

<body>
<div class="wrapper p-4">
    <?php if (isset($errors['global'])): ?>
        <p><span class="error"><?= $errors['global'] ?></span></p>
    <?php endif; ?>

    <form action="" method="post">
        <div class="form-items grid gap-y-3">
            <div class="grid gap-y-2">
                <label for="username">Felhasználónév:</label>
                <input type="text" name="username" id="username" value="<?= $_POST['username'] ?? '' ?>"
                       autocomplete="username"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-60 p-2.5">
                <?php if (isset($errors['username'])) : ?>
                    <span class="error"><?= $errors['username'] ?></span>
                <?php endif; ?>
            </div>
            <div class="grid gap-y-2">
                <label for="password">Jelszó:</label>
                <input type="password" name="password" id="password"
                       autocomplete="current-password"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-60 p-2.5">
                <?php if (isset($errors['password'])) : ?>
                    <span class="error"><?= $errors['password'] ?></span>
                <?php endif; ?>
            </div>
            <div class="grid grid-cols-[repeat(2,max-content)] gap-2">
                <button type="submit" class="rounded-xl w-28 bg-yellow-600 py-3 flex justify-center text-neutral-50">
                    Belépés
                </button>
                <a href="./index.php" class="rounded-xl w-28 bg-yellow-600 py-3 flex justify-center text-neutral-50">Kezdőlap
                </a>
            </div>
        </div>
    </form>
</div>
</body>

</html>
