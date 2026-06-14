<template>
  <Folder v-if="!element['isFile']" :data="element" :index="index"></Folder>
  <File v-else :data="element" :index="index"></File>
</template>

<script>
import Folder from './Folder.vue'
import File from './File.vue'

export default {
  components: { Folder, File },
  props: ['element', 'index']
}
</script>