<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Регистрация пользователя в веб-приложение">
  <meta name="CSRF" content="<?= $csrf?>">
  <title>Регистрация AeroFiles</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/form.css">
</head>

<body>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php' ?>
  <main>
    <div class="formContainer">
      <form class="form">
        <div class="formBlock">
          <h2 class="headerForm">Регистрация</h2>
        </div>
        <div class="formBlock">
          <label>Логин</label>
          <input type="text" name='login' minlength="2" required>
        </div>
        <div class="formBlock">
          <label>Пароль</label>
          <input type="text" name='password' pattern="^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^\s])[A-Za-z\d]{8,}$" title="Пароль должен содержать только латинские буквы и цифры.&#010;В пароле должно быть не менее 8 символов.&#010;Обязательно должен содержать хотя бы одну цифру, латинскую большую и маленькую буквы.&#010;Не должен содержать пробелы." required>
        </div>
        <div class="formBlock">
          <label>Подтвердите пароль</label>
          <input type="text" name='rep_password' required>
        </div>
        <div class="formBlock">
          <label>Email</label>
          <input type="email" name='email' required>
        </div>
        <div class="formBlock">
          <input type="submit" class='btn' name='reg_btn' value="Зарегистрироваться">
        </div>
        <div class="formBlock hidden">
          <input type="text" name='reg' value="regForm">
        </div>
      </form>
      <div class='message'></div>
    </div>
  </main>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php' ?>
  <script type="module" src="assets/js/regUser.js"></script>
</body>

</html>