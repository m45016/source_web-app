<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/assets/css/admin.css">
  <link rel="stylesheet" href="/assets/css/modalWindow.css">
  <meta name="CSRF" content="<?= $csrf?>">
  <title>Админ панель AeroFiles</title>
</head>

<body>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/header.php' ?>
  <main>
    <h1>Админ панель</h1>
    <div class="p-3">Активные пользователи: <?= $activeUsers?></div>
    <form id="form">
      <label for="login">Поиск пользователя</label>
      <input type="text" id="login" name='login' placeholder="<?= htmlspecialchars($user['data']['login']) ?>" value="<?= $user['data']['login'] ?>">
      <input type="submit" id="findUser" class='btn' value="Поиск">
    </form>
    <div class='container'>
      <?php if (is_null($user['error'])): ?>
        <div class="user">
          <div class="rowData">ID: <span class='idUser'><?= $user['data']['id_user'] ?></span></div>
          <div class="rowData">Имя пользователя: <span class='nameUser'><?= htmlspecialchars($user['data']['login']) ?></span></div>
          <div class="rowData">Активен: <span><?= $user['data']['isActive'] ?></span></div>
          <div class="rowData">Email: <span class='EmailUser'><?= htmlspecialchars($user['data']['email']) ?></span></div>
          <div class="rowData">Права администратора: <span class='isAdmin'><?= $user['data']['isAdminText'] ?></span></div>
          <div class="rowData">Баланс: <span><?= $user['data']['balance'] ?> руб.</span></div>
          <div class="rowData">Тариф: <span><?= strtoupper($user['data']['tariff_name']) ?></span></div>
          <div class="rowData">Дата оплаты: <span class='date'><?= $user['data']['date_payment']?$user['data']['date_payment']:'Не оплачен' ?></span></div>
          <div class="rowData">Действителен до: <span class='date'><?= $user['data']['tariffValidTo']?$user['data']['tariffValidTo']:'Не оплачен' ?></span></div>
          <div class="rowData">Тариф действителен: <span><?= $user['data']['isPaymentTariff'] ?></span></div>
          <div class="rowData">Сводбодное место в хранилище: <span class='freeStorage'><?= $user['data']['freeSize'] ?></span></div>
          <div class="rowData">Занятое место в хранилище: <span class='sizeStorage'><?= $user['data']['sizeStorage'] ?></span></div>
          <div class="rowData">Максимальный размер хранилища: <span class='maxStorage'><?= $user['data']['maxSizeStorage'] ?></span></div>
          <div class="actionUser">
            <div>Сделать админом: <span><input type="checkbox" class="setAdmin" <?= $user['data']['isAdminCheckBox'] ?>></span></div>
            <div>Установить тариф: <select class='setMaxStorage'>
                <option value="No Change">No Change</option>
                <?php foreach($user['data']['tariffs'] as $tariff):?>
                  <option value="<?= $tariff['name']?>"><?= strtoupper($tariff['name'])?></option>
                <?php endforeach;?>
              </select>
            </div>
            <div><button class='setChange'>Изменить данные</button></div>
          </div>
        </div>
      <?php else: ?>
        <div class="text-center"><?= $user['error'] ?></div>
      <?php endif; ?>
    </div>
  </main>
  <?php require $_SERVER['DOCUMENT_ROOT'] . '/assets/php/footer.php' ?>
  <script type='module' src="/assets/js/admin.js"></script>
</body>

</html>