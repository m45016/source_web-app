<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="keywords" content="облачное хранилище, облако, хранилище файлов, aerofiles">
  <meta name="description" content="Главная страница сайта aerofiles.ru">
  <title>AeroFiles</title>
  <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php'?>
  <main>
    <h2>Облачное хранилище, как на вашем компьютере</h2>
    <div class="text">Удобный проводник, drag'n drop загрузка и 10 Гб бесплано.</div>
    <a href='<?=  $link?>' class='btn'>Начать бесплатно</a>
    <h2>Почему выбирают AeroFiles</h2>
    <div class="features">
      <div class="feature">
        <div class="icon">📁</div>
        <div class="featureHeader">Как проводник ОС</div>
        <div class="featureText">Знакомый интерфейс: папки, файлы, контекстное меню &mdash; работайте так, как привыкли на своем компьютере.</div>
      </div>
      <div class="feature">
        <div class="icon">🖱️</div>
        <div class="featureHeader">Drag'n Drop</div>
        <div class="featureText">Просто перетащите файлы в окно браузера &mdash; они мгновенно загрузятся в облако.</div>
      </div>
      <div class="feature">
        <div class="icon">🆓</div>
        <div class="featureHeader">10 ГБ бесплатно</div>
        <div class="featureText">Никаких карт и обязательств. Начните использовать сразу после регистрации.</div>
      </div>
    </div>
  </main>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php'?>
</body>
</html>