<template>
  <Tariff v-for='tariff in tariffs' :tariff='tariff'></Tariff>
</template>

<script>
import Tariff from './tariff.vue';

export default {
  components: { Tariff },
  props: ['tariffs']
}
</script>