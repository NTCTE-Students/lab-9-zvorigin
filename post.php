<?php

require_once __DIR__ . '/../../autoload.php';

use App\Middleware\Auth;
use App\Models\Post;

$auth = new Auth();

if (!$auth -> check()) {
    header('Location: /');
    exit();
}

$post = new Post((int) $_GET['id'] ?? null);

if ($post -> getAttribute('id') !== null && $auth -> user() -> getAttribute('id') !== $post -> getAttribute('user_id')) {
    header('Location: /');
    exit();
} elseif (isset($_GET['method']) && isset($_GET['id']) && $_GET['method'] == 'delete') {
    $post -> delete();
    header('Location: /account');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['errors'] = [];

    if (empty($_POST['heading'])) {
        $_SESSION['errors']['heading'][] = 'Заголовок поста обязателен к заполнению';
    }
    if (empty($_POST['body'])) {
        $_SESSION['errors']['body'][] = 'Основной текст поста обязателен к заполнению';
    }

    if (empty($_SESSION['errors'])) {
        if ($post -> getAttribute('id') === null) {
            $post -> createAndSet([
                'heading' => $_POST['heading'],
                'body' => $_POST['body'],
                'user_id' => $auth -> user() -> getAttribute('id'),
            ]);
            
            header("Location: /account/post.php?id={$post -> getAttribute('id')}");
            exit();
        } else {
            $post -> fillAndSave([
                'heading' => $_POST['heading'],
                'body' => $_POST['body'],
            ]);
        }
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
                <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account">Кабинет</a></li>
                <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account/logout.php">Выйти</a></li>
            <?php } else { ?>
                <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account/login.php">Войти</a></li>
                <li><a class="block px-4 py-2 bg-cyan-600 text-cyan-50 hover:bg-cyan-900 md:rounded-xl" href="/account/register.php">Регистрация</a></li>
            <?php } ?>
        </ul>
    </header>
    <main class="mt-10 my-8 md:m-0 mx-8">
        <h1 class="text-3xl font-bold my-10"><?php print($post -> getAttribute('id') !== null ? 'Редактировать' : 'Создать'); ?> пост</h1>
        <form method="post">
            <div class="mb-5">
                <label for="heading" class="block mb-2 text-sm font-medium text-gray-900">Заголовок<span class="text-red-600">*</span></label>
                <input value="<?php print($post -> getAttribute('heading')); ?>" type="text" id="heading" name="heading" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="На сколько быстро студенты справятся с копипастой? Разъясняем..." required />
                <?php if (isset($_SESSION['errors']['heading'])) { ?>
                    <p class="text-red-500 text-sm italic">
                        <?php foreach ($_SESSION['errors']['heading'] as $error) { ?>
                            <span><?php echo $error; ?></span> 
                        <?php } ?>
                    </p>
                <?php } ?>
            </div>
            <div class="mb-5">
                <label for="body" class="block mb-2 text-sm font-medium text-gray-900">Основной текст<span class="text-red-600">*</span></label>
                <textarea id="body" name="body" rows="10" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="Студенты решили, что они умные и решили самостоятельно что-то выполнить!"><?php print($post -> getAttribute('body')); ?></textarea>
                <?php if (isset($_SESSION['errors']['body'])) { ?>
                    <p class="text-red-500 text-sm italic">
                        <?php foreach ($_SESSION['errors']['body'] as $error) { ?>
                            <span><?php echo $error; ?></span> 
                        <?php } ?>
                    </p>
                <?php } ?>
            </div>
            <button type="submit" class="text-white bg-cyan-600 hover:bg-cyan-800 focus:ring-4 focus:outline-none focus:ring-cyan-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center cursor-pointer"><?php print($post -> getAttribute('id') !== null ? 'Редактировать' : 'Создать'); ?> пост</button>
        </form>
    </main>
</body>
</html>

<?php unset($_SESSION['errors']); ?>