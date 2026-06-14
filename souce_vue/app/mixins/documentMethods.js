export const documentMethods = { // методы привязанные к документу
  async keyDown(e) { // отслеживание нажатия клавиш
    if (e.repeat) {
      return 1;
    }
    let activeElements = this.getActiveElements();
    if (e.code === "Enter" && this.renameMode) {
      e.preventDefault();
      this.renameFile();
    } else if (e.code === 'Delete' && activeElements.length !== 0 && !this.renameMode) {
      e.preventDefault();
      let confirmDeleteFiles = await this.modal.confirm('Вы точно хотите удалить выделенные файлы?');
      if (confirmDeleteFiles) {
        let files = activeElements;
        this.deleteFiles(files);
      }
    } else if (e.code === 'KeyC' && activeElements.length !== 0 && e.ctrlKey && !this.renameMode) {
      e.preventDefault();
      let files = activeElements;
      this.copyFiles(files);
    } else if (e.code === 'KeyV' && e.ctrlKey && !this.renameMode) {
      e.preventDefault();
      this.pasteFiles();
    } else if (e.code === 'KeyX' && activeElements.length !== 0 && e.ctrlKey && !this.renameMode) {
      e.preventDefault();
      let files = activeElements;
      this.cutFiles(files);
    }
    else if(e.code === 'KeyA' && e.ctrlKey && !this.renameMode){
      e.preventDefault();
      this.setActiveElementsAll();
    }
    this.hideContextMenu();
  },
  clickOnDocument(e) { // отслеживание кликов на документ
    if (this.renameMode && !e.target.classList.contains('__actionMenu__') && !e.target.classList.contains('editNameElement')) {
      this.renameFile();
    } else if (e.target.hasAttribute('path') && !e.target.classList.contains('__actionMenu__')) {
      let path = e.target.getAttribute('path');
      this.goToPath(path);
    }
    this.hideContextMenu();
  }
}