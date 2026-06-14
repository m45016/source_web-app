<template>
  <div class="storageElement __file__" :path="data['path']" :typeFile="generateType()" :title="generateTitle()">
    <div class="wrapperElement __file__" :class="{activeElement: data['isActive']}" :index='index'>
      <div class="imgBlock __file__"><img :src="generateSrc()" alt="file" class="imgElement __file__"></div>
      <div class="nameElement __file__" :class="{editNameElement: data['isEditName']}" :contenteditable="data['isEditName']">{{data['name']}}</div>
    </div>
    <div class="progressZone" v-if="data['isLoading']">
      <progress class="progressLoaded" :value="data['progressLoad']" max="100"></progress><div><span class="progressLoad">{{data['progressLoad']}}</span>%/100%</div>
    </div>
  </div>
</template>

<script>

export default {
  props: ['data', 'index'],
  methods: {
    generateTitle() {
      return `Тип: ${this.data['dataType']['title']}\nРазмер файла: ${this.data['formatedSize']}\nДата загрузки: ${this.data['ctime']}`
    },
    generateSrc() {
      return `assets/img/file${this.data['dataType']['img']}.svg`
    },
    generateType() {
      return `.${this.data['dataType']['type']}`
    }
  }
}

</script>