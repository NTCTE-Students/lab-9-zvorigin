<?php

require_once __DIR__ . '/../autoload.php';

use App\Config\Database;
use App\Middleware\Auth;
use App\Models\User;

$auth = new Auth();
$user = new User((int) $_GET['id']);


if (!$user -> getAttribute('id')) {
    header('Location: /');
    exit();
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Блог</title>
    <link rel="stylesheet" href="/assets/css/style.css" />
</head>
<body class="max-w-6xl xl:mx-auto md:mx-10">
    <header class="flex md:my-5 flex-col md:flex-row justify-between">
        <ul class="flex md:space-x-2 flex-col md:flex-row">
            <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/">Главная</a></li>
            <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/feed.php">Лента</a></li>
        </ul>
        <ul class="flex md:space-x-2 flex-col md:flex-row">
            <?php if ($auth -> check()) { ?>
                <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account">Кабинет</a></li>
                <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account/logout.php">Выйти</a></li>
            <?php } else { ?>
                <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account/login.php">Войти</a></li>
                <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account/register.php">Регистрация</a></li>
            <?php } ?>
        </ul>
    </header>
    <main>
            <section class="my-8 md:m-0 mx-8">
                <h1 class="text-3xl font-bold my-8">
                    <a class="hover:underline" href="/blog.php?id=<?php print($user -> getAttribute('id')); ?>">Блог пользователя - <?php print("{$user -> getAttribute('lastname')} {$user -> getAttribute('firstname')}"); ?></a>
                </h1>
                <div class="grid grid-cols-1 md:grid-cols-3 md:gap-4 gap-y-2">
                    <?php
                        $dbase = new Database();
                        $posts = $dbase -> read('posts', ['id', 'heading', 'SUBSTRING(body, 1, 50) AS body'], [['user_id', '=', $user -> getAttribute('id')]]);
                        foreach($posts as $post) { ?>
                            <div class="max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                                <a href="#">
                                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900"><?php print($post['heading']); ?></h5>
                                </a>
                                <p class="mb-3 font-normal text-gray-700"><?php print($post['body']); ?>...</p>
                                <a href="/post.php?id=<?php print($post['id']); ?>" class="inline-flex items-center px-3 py-2 text-sm font-medium textr-center text-white bg-cyan-700 rounded-lg hover:bg-cyan-800 focus:ring-4 focus:outline-none focus-within:ring-cyan-300">
                                    Подробнее
                                    <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                                    </svg>
                                </a>
                            </div>
                        <?php } ?>
                </div>
            </section>
    </main>
</body>
</html>