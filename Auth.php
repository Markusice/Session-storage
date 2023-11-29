<?php

declare(strict_types=1);

class Auth
{
    private IStorage $user_storage;
    private ?array $user = null;

    public function __construct(IStorage $user_storage)
    {
        $this->user_storage = $user_storage;

        if (isset($_SESSION['user'])) {
            $this->user = $_SESSION['user'];
        }
    }

    public function register($data): string
    {
        $user = [
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'roles' => ['user'],
        ];
        return $this->user_storage->add($user);
    }

    public function userExists($username): bool
    {
        $users = $this->user_storage->findOne(['username' => $username]);
        return !is_null($users);
    }

    public function authenticate($username, #[SensitiveParameter] $password): ?array
    {
        $users = $this->user_storage->findMany(function ($user) use ($username, $password) {
            return $user['username'] === $username &&
                password_verify($password, $user['password']);
        });
        return count($users) === 1 ? array_shift($users) : null;
    }

    public function isAuthenticated(): bool
    {
        return !is_null($this->user);
    }

    public function authorize($roles = []): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }
        foreach ($roles as $role) {
            if (in_array($role, $this->user['roles'])) {
                return true;
            }
        }
        return false;
    }

    public function login($user): void
    {
        $this->user = $user;
        $_SESSION['user'] = $user;
    }

    public function logout(): void
    {
        $this->user = null;
        unset($_SESSION['user']);
    }

    public function authenticatedUser(): array
    {
        return $this->user;
    }
}