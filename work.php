<?php

namespace App\Middleware;

use App\Models\User;

/**
 * Класс-посредник для аутентификации.
 */
class Auth {
    protected ?User $user = null;
    
    /**
     * Создает новый экземпляр класса.
     *
     * @return void
     */
    public function __construct()
    {
        session_start();

        if (isset($_SESSION['user_id'])) {
            $this -> user = new User($_SESSION['user_id']);
        }
    }
    
    /**
     * Метод проверяет, аутентифицирован ли пользователь.
     *
     * @return bool Возвращает true, если пользователь аутентифицирован, иначе false.
     */
    public function check(): bool
    {
        return $this -> user !== null;
    }
    
    /**
     * Метод возвращает экземпляр класса User, представляющий аутентифицированного пользователя.
     *
     * @return ?User Возвращает экземпляр класса User, представляющий аутентифицированного пользователя, или null, если пользователь не аутентифицирован.
     */
    public function user(): ?User
    {
        return $this -> user;
    }
    
    /**
     * Метод аутентифицирует пользователя по электронной почте и паролю.
     *
     * @param string $email Электронная почта пользователя. 
     * @param string $password Пароль пользователя.
     * @return bool Возвращает true, если аутентификация прошла успешно, иначе false.
     */
    public function login(string $email, string $password): bool
    {
        $user = (new User()) -> getByEmail($email);
        if ($user && password_verify($password, $user -> getAttribute('password_hash'))) {
            $_SESSION['user_id'] = $user -> getAttribute('id');
            $this -> user = $user;
            
            return true;
        }

        return false;
    }
    
    /**
     * Метод разлогинивает пользователя.
     *
     * @return void
     */
    public function logout(): void
    {
        unset($_SESSION['user_id']);

        $this -> user = null;
    }
}
