<template>
	<h3>Текущий тариф</h3>
    <div class="container">
      <div class="cur_tariff">{{tariffname.toUpperCase()}}</div>
    </div>
    <br>
</template>

<script>

export default{
	props: ['tariffname']
}

</script>