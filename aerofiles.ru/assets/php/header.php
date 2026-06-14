<header>
  <div class="nameApplication">
    <a href="/"><img src="/assets/img/logo.svg" alt="logo" class="logo"></a>AeroFiles
  </div>
  <div class="nav">
    <?php if(!empty($_SESSION['login'])):?>
      <a href="/app.php">Приложение</a>
      <a href="/profile.php">Профиль</a>
      <?php if($_SESSION['isAdmin']):?>
        <a href="/admin">Админ панель</a>
      <?php endif;?>
      <a href="/exit.php">Выход</a>
    <?php else:?>
      <a href="/reg.php">Регистрация</a>
      <a href="/auth.php">Войти</a>
    <?php endif?>
  </div>
</header>