<template>
  <div class="menu __menuTag__" :class="{hidden: menu['isHidden']}" :ref="(el)=>menu['menu']=el">
    <div class="contextMenu __menuTag__">
      <ContextMenuActionGroup v-for="(group, nameGroup) in menu['groups']" :group="group" :nameGroup="nameGroup"></ContextMenuActionGroup>
    </div>
  </div>
</template>

<script>

import ContextMenuActionGroup from './ContextMenuActionGroup.vue'

export default {
  components: { ContextMenuActionGroup },
  props: ['menu']
}

</script>