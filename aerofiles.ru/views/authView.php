<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Аутентификация в веб-приложение">
  <meta name="CSRF" content="<?= $csrf?>">
  <title>Аутентификация AeroFiles</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/form.css">
</head>

<body>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php' ?>
  <main>
    <div class="formContainer">
      <form class="form">
        <div class="formBlock">
          <h2 class="headerForm">Вход в аккаунт</h2>
        </div>
        <div class="formBlock">
          <label>Логин</label>
          <input type="text" name='login' required>
        </div>
        <div class="formBlock">
          <label>Пароль</label>
          <input type="text" name='password' required>
        </div>
        <div class="formBlock">
          <input type="submit" class="btn" name='auth_btn' value="Войти">
        </div>
        <div class="formBlock hidden">
          <input type="text" name='auth' value="authForm">
        </div>
        <div class="formBlock">
          <a href="resetPassword.php">Забыли пароль?</a>
        </div>
      </form>
      <div class='message'></div>
    </div>
  </main>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php' ?>
  <script type="module" src="assets/js/authUser.js"></script>
</body>

</html>