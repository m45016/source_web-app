<template>
  <div class="tariff">
    <div class="nameTariff">{{tariff['name'].toUpperCase()}}</div>
    <div class="priceTariff">Цена: {{tariff['price'] == 0 ? 'Бесплатно' : tariff['price'] + ' руб. в месяц'}}</div>
    <div>Максимальный размер хранилища: {{tariff['maxSizeStorageText']}}</div>
    <div><button class="setTariff btn" @click='setTariff($event)'>Выбрать тариф</button></div>
  </div>
</template>

<script>

export default {
  props: ['tariff'],
  inject: ['APP'],
  methods: {
    setTariff(event) {
      this.APP.setTariff(event);
    }
  }
}
</script>