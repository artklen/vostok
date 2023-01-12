<?php

class Auth extends UniversalSingletoneHelper
{
    public function is_guest(): bool
    {
        return ! isset($_SESSION['auth']);
    }

    public function is_authorized(): bool
    {
        return isset($_SESSION['auth']);
    }

    public function login(string $user_id): void
    {
        $_SESSION['auth'] = $user_id;
    }

    public function logout(): void
    {
        $_SESSION['auth'] = null;
        unset($_SESSION['auth']);
    }

    public function id(): ?string
    {
        return $_SESSION['auth'] ?? null;
    }

    public function user(): User
    {
        if ($this->is_guest()) {
            return d()->User->stub();
        }

        return d()->User->f($this->id());
    }
}