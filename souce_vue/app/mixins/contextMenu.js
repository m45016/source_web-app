export const contextMenu = { // объект контекстного меню
  menu: null,
  groups: { // группы действии
    actionFile: {
      actions: [{ // действия
        action: 'open',
        nameAction: 'Открыть',
        isHidden: false,
        isExistKBD: false, // есть ли привязка к клавишам
        kbd: null
      },
      {
        action: 'rename',
        nameAction: 'Переименовать',
        isHidden: false,
        isExistKBD: false,
        kbd: null
      },
      {
        action: 'copy',
        nameAction: 'Копировать',
        isHidden: false,
        isExistKBD: true,
        kbd: 'Ctrl+C'
      },
      {
        action: 'cut',
        nameAction: 'Вырезать',
        isHidden: false,
        isExistKBD: true,
        kbd: 'Ctrl+X'
      },
      {
        action: 'delete',
        nameAction: 'Удалить',
        isHidden: false,
        isExistKBD: true,
        kbd: 'Delete'
      },
      {
        action: 'addSelectFolder',
        nameAction: 'Добавить в избранное',
        isHidden: false,
        isExistKBD: false,
        kbd: null
      },
      {
        action: 'deleteSelectedFolder',
        nameAction: 'Удалить из избранного',
        isHidden: false,
        isExistKBD: false,
        kbd: null
      },
      {
        action: 'download',
        nameAction: 'Скачать',
        isHidden: false,
        isExistKBD: false,
        kbd: null
      },
      {
        action: 'abortLoading',
        nameAction: 'Отмена',
        isHidden: false,
        isExistKBD: false,
        kbd: null
      },
      {
        action: 'properties',
        nameAction: 'Свойства',
        isHidden: false,
        isExistKBD: false,
        kbd: null
      },
      {
        action: 'noProperties',
        nameAction: 'Свойств нет',
        isHidden: false,
        isExistKBD: false,
        kbd: null
      }
      ],
      isHidden: false
    },
    actionStorage: {
      actions: [{
        action: 'paste',
        nameAction: 'Вставить',
        isHidden: false,
        isExistKBD: true,
        kbd: 'Ctrl+V'
      },
      {
        action: 'createFolder',
        nameAction: 'Создать папку',
        isHidden: false,
        isExistKBD: false,
        kbd: null
      },
      {
        action: 'upload',
        nameAction: 'Загрузить файл',
        isHidden: false,
        isExistKBD: false,
        kbd: null
      }
      ],
      isHidden: false
    }
  },
  isHidden: true,
  showGroup(nameGroup, hideActions = null) { // отображение конкретной группы
    let groups = this.groups;
    let showGrops = [nameGroup];
    for (let group in groups) {
      if (showGrops.includes(group)) {
        groups[group].isHidden = false;
        let actions = groups[group].actions;
        if (hideActions) {
          actions.forEach(action =>
            hideActions.includes(action.action) ? action.isHidden = true : action.isHidden = false
          );
        } else {
          actions.forEach(action => action.isHidden = false);
        }
      } else {
        groups[group].isHidden = true;
      }
    }
  },
  isOutWindow(storageWindow) { // выходит ли контекстное меню за рамки
    let menu = this.menu;
    let positionMenu = menu.getBoundingClientRect();
    let positionStorageWindow = storageWindow.getBoundingClientRect();

    if (positionMenu.x + positionMenu.width > positionStorageWindow.x + positionStorageWindow.width) {
      menu.style.left = `${positionStorageWindow.x + positionStorageWindow.width - positionMenu.width}px`;
    }
    if (positionMenu.y + positionMenu.height > positionStorageWindow.y + positionStorageWindow.height) {
      menu.style.top = `${positionStorageWindow.y + positionStorageWindow.height - positionMenu.height}px`;
    }

  }
};

export const contextmenuMethods = { // методы контекстного меню
  hideContextMenu() { // скрытие контекстного меню
    if (!this.contextmenu.isHidden) {
      this.contextmenu.isHidden = true;
    }
  },
  async openContextMenu(e) { // открытие контекстного меню
    let clickOnElement = e.target.closest('.wrapperElement');
    let clickOnStorage = e.target;

    if (this.contextmenu.isHidden) {
      this.contextmenu.menu.style.left = `${e.clientX}px`;
      this.contextmenu.menu.style.top = `${e.clientY}px`;
      this.contextmenu.isHidden = false;
    } else {
      this.contextmenu.menu.style.left = `${e.clientX}px`;
      this.contextmenu.menu.style.top = `${e.clientY}px`;
    }

    if (clickOnElement !== null) {
      e.preventDefault();
      let hiddenActions = [];
      let indexElement = clickOnElement.getAttribute('index');
      this.setActiveElement(indexElement);
      let activeElements = this.getActiveElements();
      let countLoadingElements = 0;

      for(let i = 0; i<activeElements.length; i++){
        if(activeElements[i].isLoading){
          countLoadingElements++;
        }
      }

      if(activeElements.length > 1 && countLoadingElements<=activeElements.length && countLoadingElements!==0){
        hiddenActions.push('rename', 'open', 'deleteSelectedFolder', 'addSelectFolder', 'download','abortLoading','properties','copy','cut', 'delete')
      }
      else if (activeElements.length > 1) {
        hiddenActions.push('rename', 'open', 'deleteSelectedFolder', 'addSelectFolder', 'download','abortLoading','properties','noProperties');
      }
      else if(activeElements.length === 1 && activeElements[0].isLoading){
        hiddenActions.push('rename','download','copy','cut', 'delete','properties','noProperties');
      }
      else {
        hiddenActions.push('abortLoading','noProperties');
      }

      switch (true) {
        case clickOnElement.classList.contains('__file__'):
          hiddenActions.push('open', 'deleteSelectedFolder', 'addSelectFolder');
          this.contextmenu.showGroup('actionFile', hiddenActions);
          break;
        case clickOnElement.classList.contains('__folder__'):
          let activeElement = this.getFirstActiveElement();
          let pathFolder = activeElement.path + activeElement.name + '/';
          let isSelectedFolder = this.selectedFolders.isSelectedFolder(pathFolder);
          hiddenActions.push('download');
          if (isSelectedFolder) {
            hiddenActions.push('addSelectFolder');
          } else {
            hiddenActions.push('deleteSelectedFolder');
          }
          this.contextmenu.showGroup('actionFile', hiddenActions);
          break;
      }
    } else if (clickOnStorage.classList.contains('storageWindow')) {
      e.preventDefault();
      this.removeIsActiveElements();

      let hiddenActions = [];

      if (this.isEmptyBuffer()) {
        hiddenActions.push('paste');
      }

      this.contextmenu.showGroup('actionStorage', hiddenActions);

    } else {
      this.contextmenu.isHidden = true;
    }
    await this.$nextTick();
    this.contextmenu.isOutWindow(this.$refs.storageWindow);
    await this.$nextTick();
  }
}
