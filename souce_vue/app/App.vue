<template>
  <aside class="asideMenu">
    <div class='sizeStorageContainer'>
      <div class='textProgress'>{{freeSize}} свободно из {{maxSize}}</div>
      <progress class='progressSizeStorage' :value="freeSizeInPercent" max='100'></progress>
    </div>
    <div class="asideHeader">Избранное</div>
    <div class="selectedFolders">
      <componentSelectedFolders v-for="(selectedFolder, index) in selectedFolders.folders" :selected-folder="selectedFolder" :index="index"></componentSelectedFolders>
      <componentEmptyFolders v-if="selectedFolders.isEmptyFolders"></componentEmptyFolders>
    </div>
  </aside>
  <div class="containerMain">
    <div class="containerWindow">
      <div class='pathContainer'><div class="scrollPathContainer"><span action='goToFolder' path='/'>root</span><span class='path' v-html='path'></span></div></div>
      <div class="storageWindow" ref="storageWindow" :class="{withoutFiles: isEmptyStorage}" @click='clickOnStorage($event)' @dblclick='dblClickOnStorage($event)' @dragover="($e)=>$e.preventDefault()" @drop="uploadFileFromEvent($event)">
        <componentStorageElement v-for="(element, index) in elements" :element='element' :index='index'></componentStorageElement>
        <componentEmptyStorage v-if="isEmptyStorage" @create-folder="createFolder"></componentEmptyStorage>
      </div>
    </div>
  </div>
  <componentContextMenu :menu="contextmenu"></componentContextMenu>
</template>

<script>

import {explorerMethods} from './mixins/explorerMethods.js'
import {selectedFolders, selectedFoldersMethods} from './mixins/selectedFolders.js'
import {contextMenu, contextmenuMethods} from './mixins/contextmenu.js'
import {documentMethods} from './mixins/documentMethods.js'
import API from './mixins/apiModule.js';
import { modal } from './mixins/modalWindow.js';
import { validJSON } from './mixins/jsonSchema.js';
import { Datetime } from './mixins/datetimeModule.js';

// компоненты
import componentEmptyStorage from './components/storage/EmptyStorage.vue'
import componentStorageElement from './components/storage/StorageElement.vue'
import componentContextMenu from './components/contextmenu/Contextmenu.vue'
import componentEmptyFolders from './components/selectedFolders/EmptyFolders.vue'
import componentSelectedFolders from './components/selectedFolders/SelectedFolders.vue'


export default {
  components: {
    componentEmptyStorage,
    componentStorageElement,
    componentContextMenu,
    componentEmptyFolders,
    componentSelectedFolders
  },
  
  data() {
    return {
      isEmptyStorage: true,
      path: '/',
      elements: [],
      freeSize: 'None',
      freeSizeInPercent: '0',
      maxSize: 'None',
      countFiles: 0,
      progressNumber: 1,
      pathUser: '/',
      buffer: null,
      renameMode: false,
      forbiddenChars: ['/', '\\', '?', '*', ':', '"', '<', '>', '|'],
      editElement: null,
      editName: null,
      contextmenu: { ...contextMenu },
      selectedFolders: { ...selectedFolders },
      API,
      modal,
      validJSON
    }
  },
  
  async created() { // получение данных с сервера

    try {
      let csrf = document.getElementsByName('CSRF')[0].content;
      let json ={csrf};
      json = JSON.stringify(json);
      console.log('Получение данных приложения');
      let response = await this.API.send('app', 'openRoot', json);

      this.isEmptyStorage = response.emptyStorage;
      this.elements = response.elements.map(elem=>{
        let datetime = new Datetime();
        datetime.setDate(elem.ctime);
        elem.ctime = datetime.getDate();
        return elem;
      });
      this.freeSize = response.storageInfo.freeSizeStorage;
      this.freeSizeInPercent = response.storageInfo.freeSizeStorageInPercent;
      this.maxSize = response.storageInfo.maxSizeStorage;
      this.pathUser = response.pathUser;
      this.countFiles = response.countFiles;
      if (response.selectedFolders !== null) {
        this.selectedFolders.folders = response.selectedFolders;
        this.selectedFolders.isEmptyFolders = false;
      }
      console.log('Данные получены');
    } catch (e) {
      console.error(e);
      await this.modal.alert(`Ошибка: ${e.message}`);
    }
  },
  
  mounted() {
    document.addEventListener('contextmenu', this.openContextMenu)
    document.addEventListener('click', this.clickOnDocument)
    window.addEventListener('resize', this.hideContextMenu)
    document.addEventListener('keydown', this.keyDown)
  },
  
  beforeUnmount() {
    document.removeEventListener('contextmenu', this.openContextMenu)
    document.removeEventListener('click', this.clickOnDocument)
    window.removeEventListener('resize', this.hideContextMenu)
    document.removeEventListener('keydown', this.keyDown)
  },
  
  provide() {
    return {
      'MENU': {
        contextmenu: this.contextmenu,
        createFolder: this.createFolder,
        addSelectFolder: this.addSelectFolder,
        getFirstActiveElement: this.getFirstActiveElement,
        deleteSelectedFolder: this.deleteSelectedFolder,
        setModeRenameFile: this.setModeRenameFile,
        deleteFiles: this.deleteFiles,
        getActiveElements: this.getActiveElements,
        openFolder: this.openFolder,
        downloadFiles: this.downloadFiles,
        copyFiles: this.copyFiles,
        cutFiles: this.cutFiles,
        pasteFiles: this.pasteFiles,
        abortLoading: this.abortLoading,
        createInputFile: this.createInputFile,
        uploadFileFromInput: this.uploadFileFromInput,
        modal: this.modal
      }
    }
  },
  
  methods: {
    ...explorerMethods,
    ...selectedFoldersMethods,
    ...contextmenuMethods,
    ...documentMethods
  }
}
</script>