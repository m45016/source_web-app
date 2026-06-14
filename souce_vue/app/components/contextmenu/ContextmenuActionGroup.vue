<template>
  <div class="groupActions __menuTag__" :class="{hidden: group.isHidden}" :nameGroup="nameGroup">
    <ContextMenuAction v-for="action in group['actions']" :action='action'></ContextMenuAction>
  </div>
</template>

<script>

import ContextMenuAction from './ContextMenuAction.vue'

export default {
  components: { ContextMenuAction },
  props: ['group', 'nameGroup']
}

</script>