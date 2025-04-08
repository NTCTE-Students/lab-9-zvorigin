<?php

// spl_autoload_register - регистрирует функцию автозагрузки классов
spl_autoload_register(function ($class) {
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    /**
     * Создаем из имени namespace'а абсолютный путь до файла.
     * Например, при вызове namespace'а с именем "App\Models\User"
     * будет получен путь /path/to/server/app/Models/User.php
     */
    $full_path = __DIR__ . "/{$path}.php";

    if (file_exists($full_path)) {
        require_once $full_path;
    }
});