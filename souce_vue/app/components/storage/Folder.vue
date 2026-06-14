<template>
	<div class="storageElement __folder__" :path="data['path']" :title="generateTitle()">
		<div class="wrapperElement __folder__" :class="{activeElement: data['isActive']}" :index='index'>
			<div class="imgBlock __folder__"><img :src="generateSrc()" alt="folder" class="imgElement __folder__"></div>
			<div class="nameElement __folder__" :class="{editNameElement: data['isEditName']}" :contenteditable="data['isEditName']">{{data['name']}}</div>
		</div>
	</div>
</template>

<script>
export default {
  props: ['data', 'index'],
  methods: {
    generateTitle() {
      return `Тип: ${this.data['dataType']['title']}\nДата создания: ${this.data['ctime']}`
    },
    generateSrc() {
      return `assets/img/${this.data['dataType']['type']}.svg`
    }
  }
}
</script>