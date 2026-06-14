<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="CSRF" content="<?= $csrf?>">
  <title>Тарифы</title>
  <link rel="stylesheet" href="/assets/css/tariffs.css">
  <link rel="stylesheet" href="/assets/css/modalWindow.css">
</head>

<body>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php' ?>
  <main id='app'></main>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php' ?>
  <script type="module" src='/assets/js/tariff.js'></script>
</body>

</html>