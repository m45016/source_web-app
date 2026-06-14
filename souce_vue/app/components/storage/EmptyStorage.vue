<template>
	<div class='text-center'>Переместите файлы или создайте папку</div>
	<div>
		<button class="btn" @click="createFolder()">Создать папку</button>
	</div>
</template>

<script>

export default{
	emits: ['create-folder'],
	methods: {
		createFolder() {
			this.$emit('create-folder');
		}
	}
}

</script>