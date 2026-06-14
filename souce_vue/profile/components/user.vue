<template>
  <h1>Мой профиль</h1>
  <div class="container">
    <div class="user">
      <div class="rowData">Имя пользователя: <span>{{user['login']}}</span></div>
      <div class="rowData">Email: <span>{{user['email']}}</span></div>
      <div class="rowData">Свободное место в хранилище: <span>{{user['freeSize']}}</span></div>
      <div class="rowData">Занятое место в хранилище: <span>{{user['sizeStorage']}}</span></div>
      <div class="rowData">Максимальный размер хранилища: <span>{{user['maxSizeStorage']}}</span></div>
      <div class="rowData">Баланс: <span>{{user['balance']}} руб.</span></div>
      <div class="rowData">Тариф: <span>{{user['tariff_name'].toUpperCase()}}</span></div>
      <div class="rowData">Дата оплаты: <span>{{user['date_payment']?user['date_payment']:'Не оплачен'}}</span></div>
      <div class="rowData">Действителен до: <span>{{user['tariffValidTo']?user['tariffValidTo']:'Не оплачен'}}</span></div>
      <div class="rowData">Тариф действителен: <span>{{user['isPaymentTariff']}}</span></div>
      <div class="actionUser">
        <div><a href="balance.php" class='button'>Пополнить баланс</a></div>
        <div><a href="tariff.php" class='button'>Изменить тариф</a></div>
        <div><a class='button' @click='paymentTariff'>Оплатить тариф</a></div>
      </div>
    </div>
  </div>
</template>

<script>

export default{
  props: ['user'],
  inject: ['APP'],
  methods: {
    paymentTariff() {
      this.APP.paymentTariff();
    }
  }
}

</script>