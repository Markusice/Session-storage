<?php

require_once './utils.php';

session_start([
    'cookie_lifetime' => 86400,
]);

if (isset($_POST['logout'])) {
    session_destroy();
    redirect('index.php');
}
