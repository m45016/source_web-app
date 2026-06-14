<template>
  <div class="selectedFolder" :path='selectedFolder.path'>{{selectedFolder.folder}}</div>
</template>

<script>
export default {
  props: ['selectedFolder', 'index']
}
</script>