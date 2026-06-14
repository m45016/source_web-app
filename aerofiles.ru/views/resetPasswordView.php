<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Сброс пароля пользователя">
  <meta name="CSRF" content="<?= $csrf?>">
  <title>Сброс пароля AeroFiles</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/form.css">
  <link rel="stylesheet" href="assets/css/token.css">
</head>

<body>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php' ?>
  <main>
    <div class="formContainer">
      <form class="form">
        <div class="formBlock">
          <h2 class="headerForm">Восстановление пароля</h2>
        </div>
        <div class="formBlock">
          <label>Email</label>
          <input type="email" title="Введите email который был введен при регистрации" name='email' required>
        </div>
        <div class="formBlock">
          <input type="submit" class='btn' name='sendEmail_btn' value="Восстановить&#010;пароль">
        </div>
      </form>
      <form class="token hidden">
        <div class="formBlock">
          <h2>Введите код</h2>
        </div>
        <div class="formBlock">
          <div class="inputs">
            <input type="text" class="partToken">
            <input type="text" class="partToken">
            <input type="text" class="partToken">
            <input type="text" class="partToken">
          </div>
        </div>
        <div class="formBlock button">
          <input type="submit" class="btn code" value='Отправить'>
        </div>
        <div class="formBlock desc">
          На ваш email отправлен код для сброса пароля.<br>
        </div>
      </form>
      <form class="form formPasswords hidden">
        <div class="formBlock">
          <h2 class="headerForm">Установка нового пароля</h2>
        </div>
        <div class="formBlock">
          <label>Новый пароль</label>
          <input type="text" name='password' pattern="(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}" title="Пароль должен содержать только латинские буквы и цифры.&#010;В пароле должно быть не менее 8 символов" required>
        </div>
        <div class="formBlock">
          <label>Подтвердите пароль</label>
          <input type="text" name='rep_password' pattern="(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}" title="Пароль должен содержать только латинские буквы и цифры.&#010;В пароле должно быть не менее 8 символов" required>
        </div>
        <div class="formBlock">
          <input type="submit" class='btn' name='resetPassword_btn' value="Установить&#010;пароль">
        </div>
      </form>
      <div class='message'></div>
    </div>
  </main>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php' ?>
  <script type='module' src="assets/js/sendEmail.js"></script>
  <script type="module" src="assets/js/codeResetPassword.js"></script>
  <script type="module" src="assets/js/resetPassword.js"></script>
</body>

</html>