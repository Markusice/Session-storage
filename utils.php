<?php

declare(strict_types=1);

function redirect(string $page): void
{
    header("Location: $page");
    exit();
}

function validateRegistration(array $post, array &$data, array &$errors): bool
{
    if ('' !== $username = trim($post['username'])) {
        if (strlen($username) > 32)
            $errors['username'] = 'A név maximum 32 karakter hossszú lehet!';
    } else
        $errors['username'] = 'A név megadása kötelező!';

    if ('' === $password = trim($post['password']))
        $errors['password'] = 'A jelszó megadása kötelező!';
    elseif (strlen($password) < 8)
        $errors['password'] = 'A jelszó legalább 8 karakter hosszú legyen!';

    if (!count($errors)) {
        $data = $post;
        return true;
    }
    return false;
}

function validateLogin(array $post, array &$data, array &$errors): bool
{
    if ('' === trim($post['username']))
        $errors['username'] = 'A név megadása kötelező!';
    if ('' === trim($post['password']))
        $errors['password'] = 'A jelszó megadása kötelező!';

    if (!count($errors)) {
        $data = $post;
        return true;
    }
    return false;
}
