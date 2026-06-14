<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="CSRF" content="<?= $csrf?>">
  <title>Попленение баланса</title>
  <link rel="stylesheet" href="/assets/css/balance.css">
  <link rel="stylesheet" href="/assets/css/modalWindow.css">
</head>

<body>
  <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/header.php" ?>
  <main>
    <?php if (is_null($message)): ?>
      <div class="balance">
        <h2>Ваш баланс</h2>
        <div><?= $balance ?> руб.</div>
      </div>
      <form id='formBalance' class="form">
        <div class="formBlock">
          <h2>Пополнить баланс</h2>
        </div>
        <div class="formBlock">
          <label>Сумма в рублях</label>
          <input type="number" name='balance' min='10' max='30000' required>
        </div>
        <div class="formBlock">
          <input type="submit" class="btn" value="Пополнить">
        </div>
      </form>
    <?php else: ?>
      <div class='message'>
        <?= $message ?>
      </div>
    <?php endif ?>
  </main>
  <?php require_once "{$_SERVER['DOCUMENT_ROOT']}/assets/php/footer.php" ?>
  <script type="module" src="/assets/js/balance.js"></script>
</body>

</html>