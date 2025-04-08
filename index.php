<?php

require_once __DIR__ . '/../../autoload.php';

use App\Middleware\Auth;

$auth = new Auth();

if (!$auth -> check()) {
    header('Location: /');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['errors'] = [];
    switch ($_GET['type']) {
        case 'personal':
            if (empty($_POST['lastname'])) {
                $_SESSION['errors']['lastname'][] = 'Фамилия обязательна к заполнению';
            }
            if (empty($_POST['firstname'])) {
                $_SESSION['errors']['firstname'][] = 'Имя обязательно к заполнению';
            }
            if (empty($_POST['email'])) {
                $_SESSION['errors']['email'][] = 'Почта обязательна к заполнению';
            }

            if (empty($_SESSION['errors'])) {
                $auth -> user() -> fillAndSave([
                    'lastname' => $_POST['lastname'],
                    'firstname' => $_POST['firstname'],
                    'patronymic' => $_POST['patronymic'],
                    'email' => $_POST['email'],
                ]);
            }
        break;
        case 'password':
            if (empty($_POST['password'])) {
                $_SESSION['errors']['password'][] = 'Пароль обязателен к заполнению';
            }
            if (empty($_POST['password_confirmation'])) {
                $_SESSION['errors']['password_confirmation'][] = 'Подтверждение пароля обязательно к заполнению';
            }
            if ($_POST['password'] !== $_POST['password_confirmation']) {
                $_SESSION['errors']['password_confirmation'][] = 'Пароли не совпадают';
            }

            if (empty($_SESSION['errors'])) {
                $auth -> user() -> fillAndSave([
                    'password' => $_POST['password'],
                ]);
            }
        break;
    }
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
                <li><a class="block px-4 py-2 bg-cyan-700 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account">Кабинет</a></li>
                <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account/logout.php">Выйти</a></li>
            <?php } else { ?>
                <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account/login.php">Войти</a></li>
                <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account/register.php">Регистрация</a></li>
            <?php } ?>
        </ul>
    </header>
    <main class="mt-10 my-8 md:m-0 mx-8">
        <h1 class="text-3xl font-bold my-10">Личный кабинет</h1>
        <h2 class="text-2xl font-bold my-8">Данные о вас</h2>
        <form method="post" action="/account?type=personal">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="mb-5">
                    <label for="lastname" class="block mb-2 text-sm font-medium text-gray-900">Фамилия<span class="text-red-600">*</span></label>
                    <input value="<?php print($auth -> user() -> getAttribute('lastname')); ?>" type="text" id="lastname" name="lastname" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Иванов" required />
                    <?php if (isset($_SESSION['errors']['lastname'])) { ?>
                        <p class="text-red-500 text-sm italic">
                            <?php foreach ($_SESSION['errors']['lastname'] as $error) { ?>
                                <span><?php echo $error; ?></span> 
                            <?php } ?>
                        </p>
                    <?php } ?>
                </div>
                <div class="mb-5">
                    <label for="firstname" class="block mb-2 text-sm font-medium text-gray-900">Имя<span class="text-red-600">*</span></label>
                    <input value="<?php print($auth -> user() -> getAttribute('firstname')); ?>" type="text" id="firstname" name="firstname" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Иван" required />
                    <?php if (isset($_SESSION['errors']['firstname'])) { ?>
                        <p class="text-red-500 text-sm italic">
                            <?php foreach ($_SESSION['errors']['firstname'] as $error) { ?>
                                <span><?php echo $error; ?></span> 
                            <?php } ?>
                        </p>
                    <?php } ?>
                </div>
                <div class="mb-5">
                    <label for="patronymic" class="block mb-2 text-sm font-medium text-gray-900">Отчество (при наличии)</label>
                    <input value="<?php print($auth -> user() -> getAttribute('patronymic')); ?>" type="text" id="patronymic" name="patronymic" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Иванович" />
                    <?php if (isset($_SESSION['errors']['patronymic'])) { ?>
                        <p class="text-red-500 text-sm italic">
                            <?php foreach ($_SESSION['errors']['patronymic'] as $error) { ?>
                                <span><?php echo $error; ?></span> 
                            <?php } ?>
                        </p>
                    <?php } ?>
                </div>
                <div class="mb-5">
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Почта<span class="text-red-600">*</span></label>
                    <input value="<?php print($auth -> user() -> getAttribute('email')); ?>" type="email" id="email" name="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="ivan@ivanov.ru" required />
                    <?php if (isset($_SESSION['errors']['email'])) { ?>
                        <p class="text-red-500 text-sm italic">
                            <?php foreach ($_SESSION['errors']['email'] as $error) { ?>
                                <span><?php echo $error; ?></span> 
                            <?php } ?>
                        </p>
                    <?php } ?>
                </div>
            </div>
            <button type="submit" class="text-white bg-cyan-600 hover:bg-cyan-800 focus:ring-4 focus:outline-none focus:ring-cyan-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center cursor-pointer">Изменить данные</button>
        </form>
        <h2 class="text-2xl font-bold my-8">Пароль</h2>
        <form method="post" action="/account?type=password">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="mb-5">
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Пароль<span class="text-red-600">*</span></label>
                        <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" type="password" name="password" id="password" placeholder="Тс-с-с! Тут что-то хитрое..." required>
                        <?php if (isset($_SESSION['errors']['password'])) { ?>isset
                            <p class="text-red-500 text-sm italic">
                                <?php foreach ($_SESSION['errors']['password'] as $error) { ?>
                                    <span><?php echo $error; ?></span> 
                                <?php } ?>
                            </p>
                        <?php } ?>
                    </div>
                    <div class="mb-5">
                        <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900">Повторите пароль<span class="text-red-600">*</span></label>
                        <input class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-cyan-600 focus:border-cyan-600 block w-full p-2.5" type="password" name="password_confirmation" id="password_confirmation" placeholder="Тс-с-с! Тут что-то хитрое... опять..." required>
                        <?php if (isset($_SESSION['errors']['password_confirmation'])) { ?>
                            <p class="text-red-500 text-sm italic">
                                <?php foreach ($_SESSION['errors']['password_confirmation'] as $error) { ?>
                                    <span><?php echo $error; ?></span> 
                                <?php } ?>
                            </p>
                        <?php } ?>
                    </div>
            </div>
            <button type="submit" class="text-white bg-cyan-600 hover:bg-cyan-800 focus:ring-4 focus:outline-none focus:ring-cyan-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center cursor-pointer">Изменить пароль</button>
        </form>
        <hr class="my-4 border-gray-300">
        <h2 class="text-2xl font-bold my-8">Посты</h2>
        <ul class="list-disc ms-5">
            <li><a class="text-blue-500 hover:underline" href="/account/post.php">Создать новый пост!</a></li>
            <?php foreach ($auth -> user() -> posts() as $post) { ?>
                <li><?php print($post['heading']); ?>. <a class="text-blue-500 hover:underline" href="/post.php?id=<?php print($post['id']); ?>">Просмотреть</a>. <a class="text-blue-500 hover:underline" href="/account/post.php?id=<?php print($post['id']); ?>">Редактировать</a>. <a class="text-blue-500 hover:underline" href="/account/post.php?id=<?php print($post['id']); ?>&method=delete">Удалить</a>.</li>
            <?php } ?>
        </ul>
    </main>
</body>
</html>

<?php unset($_SESSION['errors']); ?>