<?php

require_once './Auth.php';
require_once './UserStorage.php';

session_start([
    'cookie_lifetime' => 86400,
]);

$auth = new Auth(new UserStorage());

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kezdőlap</title>

    <link rel="stylesheet" href="./dist/output.css">
</head>

<body>
<div class="mt-4 grid justify-center items-center gap-x-4 grid-flow-col selection:bg-[rgba(0,0,0,0.3)]">
    <?php if ($auth->isAuthenticated()): ?>
        <p class="text-lg pointer-events-none bg-sky-700 w-max p-4 rounded-md text-neutral-50">Be vagy
            jelentkezve!</p>

        <form method="post" action="./logout.php">
            <button type="submit" name="logout"
                    class="text-lg rounded-xl w-max bg-red-600 p-4 flex justify-center text-neutral-50">Kijelentkezés
            </button>
        </form>

    <?php else: ?><a href="./login.php"
                     class="text-lg rounded-xl w-max bg-yellow-600 p-4 flex justify-center text-neutral-50">Belépés</a><?php endif; ?>

    <a href="./registration.php"
       class="text-lg rounded-xl bg-yellow-600 w-max p-4 flex justify-center text-neutral-50">Regisztráció</a>
</div>
</body>

</html>
