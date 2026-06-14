import { Datetime } from "./datetimeModule";

export const explorerMethods = { // методы проводника (хранилища)
  async createFolder() { // создание папки

    let csrf = document.getElementsByName('CSRF')[0].content;
    let json = { csrf };
    json = JSON.stringify(json);

    try {
      console.log('Создание папки');
      let response = await this.API.send('app', 'createFolder',json);

      let datetime = new Datetime();
      datetime.setDate(response.ctime);
      response.ctime = datetime.getDate();

      this.elements.push(response);
      this.countFiles++;
      if (this.isEmptyStorage) {
        this.isEmptyStorage = false;
      }

      console.log('Папка создана');

    } catch (e) {
      console.error(e);
      await this.modal.alert(`Ошибка создания папки: ${e.message}`);
      if (!this.isEmptyStorage && this.countFiles === 0) {
        this.isEmptyStorage = true;
      }
    }
  },
  finishLoading(progressNumber) { // завершение загрузки файла
    let elements = this.elements;
    for (let element of elements) {
      if (element?.progressNumber === progressNumber) {
        element.isLoading = false;
        break;
      }
    }
  },
  async updateProgressLoad(progressNumber, value, xhr) { // обновлние загрузки файла
    let elements = this.elements;
    for (let i = 0; i < elements.length; i++) {
      if (elements[i]?.progressNumber === progressNumber && elements[i]?.isLoading) {
        elements[i].progressLoad = value;
        break;
      }
      else if (elements[i]?.progressNumber === progressNumber && !elements[i]?.isLoading) {
        xhr.abort();
        elements.splice(i, 1);
        if (elements.length === 0) {
          this.isEmptyStorage = true;
        }
        await this.$nextTick();
      }
    }
  },
  updateDataElement(progressNumber, data) { // обновление метаданных о файле
    let elements = this.elements;
    let datetime = new Datetime();
    for (let element of elements) {
      if (element?.progressNumber === progressNumber) {
        element.dataType.title = data.dataType.title;
        element.formatedSize = data.formatedSize;
        datetime.setDate(data.ctime);
        element.ctime = datetime.getDate();
        element.dataType.type = data.dataType.type;
        element.dataType.img = data.dataType.img;
        element.path = data.path;
        element.name = data.name;
        element.isActive = data.isActive;
        element.isEditName = data.isEditName;
        element.fullsize = data.fullsize;
        break;
      }
    }
  },
  async uploadFiles(files) { // загрузка файлов

    const url = 'api/app/uploadFiles.php';
    let filesLength = files.length;

    if (this.isEmptyStorage) {
      this.isEmptyStorage = false;
      this.countFiles = 0;
    }

    for (let i = 0; i < filesLength; i++) {

      let form = new FormData();
      let fileName = files[i].name;
      let shortFileName = fileName.substr(0, fileName.lastIndexOf('.'));
      let progressNumber = this.progressNumber++;
      let element = {
        ctime: 'None',
        dataType: {
          img: 'WITHOUTFORMAT',
          title: 'None',
          type: 'None'
        },
        formatedSize: "0",
        fullsize: '0',
        isFile: true,
        name: shortFileName,
        isLoading: true,
        progressLoad: '0',
        progressNumber: progressNumber
      };

      console.log(`Загрузка файла ${fileName}`);

      this.elements.push(element);

      let csrf = document.getElementsByName('CSRF')[0].content;

      form.append('file', files[i]);
      form.append('csrf', csrf);

      let xhr = new XMLHttpRequest();

      xhr.open("POST", url);

      xhr.upload.onprogress = (e) => {

        let loaded = ((e.loaded / e.total) * 100).toFixed(2);
        this.updateProgressLoad(progressNumber, loaded, xhr);

      };

      xhr.onerror = async () => {
        console.error('Ошибка загрузки: Нет интернет соединения!');
        await this.modal.alert('Ошибка загрузки: Нет интернет соединения!');
        this.finishLoading(progressNumber);
        for (let i = 0; i < this.elements.length; i++) {
          if (this.elements[i].progressNumber === progressNumber) {
            this.elements.splice(i, 1);
            if (this.elements.length === 0) {
              this.isEmptyStorage = true;
            }
            this.$nextTick();
          }
        }
      }

      xhr.onload = async (e) => {
        try {

          if (e.target.status !== 200) {
            throw new Error(`${e.target.status} ${e.target.statusText}`);
          }

          let response = JSON.parse(e.target.response);

          if (!this.validJSON(response)) {
            throw new Error('Получена не корректная структура данных');
          }

          if (typeof response.error === 'string') {
            throw new Error(response.error);
          }

          let data = response['data'];

          this.updateProgressLoad(progressNumber, 100, xhr);

          this.updateDataElement(progressNumber, data);

          this.freeSize = data['freeSize'];
          this.freeSizeInPercent = data['freeSizeInPercent'];

          setTimeout(() => {
            this.finishLoading(progressNumber);
          }, 500);

          this.countFiles++;
          xhr = null;
          console.log(`Файл ${fileName} загружен`);
        } catch (e) {
          this.modal.alert(`Ошибка загрузки: ${e.message}!`);
          console.error(e);
          this.finishLoading(progressNumber);
          for (let i = 0; i < this.elements.length; i++) {
            if (this.elements[i].progressNumber === progressNumber) {
              this.elements.splice(i, 1);
              if (this.elements.length === 0) {
                this.isEmptyStorage = true;
              }
              this.$nextTick();
            }
          }
        }

      }
      xhr.send(form);
    }
  },
  clickOnStorage(e) { // отслеживание кликов в пределах окна хранилища
    let clickOnElement = e.target.closest('.wrapperElement');
    let clickOnStorage = e.target;

    if (clickOnElement !== null && e.ctrlKey) {
      let indexElement = clickOnElement.getAttribute('index');
      if (!this.isActiveElement(indexElement)) {
        this.setActiveElement(indexElement);
      }
      else {
        this.removeActiveElement(indexElement);
      }

    }
    else if (clickOnElement !== null) {
      let indexElement = clickOnElement.getAttribute('index');
      this.removeIsActiveElements();
      this.setActiveElement(indexElement);
    }
    else if (clickOnStorage.classList.contains('storageWindow')) {
      this.removeIsActiveElements();
      if (!this.contextmenu.isHidden) {
        this.contextmenu.isHidden = true;
      }
    }
  },
  dblClickOnStorage(e) { // отслеживание двойных кликов в пределах окна хранилища
    let activeElements = this.getActiveElements();
    if (e.target.classList.contains('nameElement') && activeElements.length === 1) {
      let file = this.getFirstActiveElement();
      this.setModeRenameFile(file);
    } else if (e.target.classList.contains('__folder__') && activeElements.length === 1) {
      let folder = this.getFirstActiveElement();
      this.openFolder(folder);
    }
  },
  getActiveElements() { // получение выбранных элементов
    let elements = this.elements;
    let activeElements = [];
    for (let element of elements) {
      if (element.isActive) {
        activeElements.push(element);
      }
    }

    return activeElements;
  },
  getFirstActiveElement() { // получение первого выбранного элемента
    let elements = this.elements;
    for (let element of elements) {
      if (element.isActive) {
        return element;
      }
    }
    return null;
  },
  setActiveElement(index) { // установить элемент как выбранный
    this.elements[index].isActive = true;
  },
  removeActiveElement(index) { // установить элемент как не выбранный
    this.elements[index].isActive = false;
  },
  isActiveElement(index) { // проверка выбранного элемента
    return this.elements[index].isActive;
  },
  removeIsActiveElements() { // установить все элементы как не выбранные
    this.elements.forEach(elem => elem.isActive === true ? elem.isActive = false : undefined);
  },
  setActiveElementsAll() { // установить все элементы как выбранные
    this.elements.forEach(elem => elem.isActive === false ? elem.isActive = true : undefined);
  },
  openFolder(folder) { // открыть папку
    let nameFolder = `${this.pathUser}${folder.name}/`;
    this.updateWindow(nameFolder);
  },
  goToPath(path) { // перейти по пути
    this.updateWindow(path);
  },
  async updateWindow(path) { // обновить окно хранилища
    let pathSelFolder = path;

    console.log(`Обновление окна хранилища: ${path}`);
    let csrf = document.getElementsByName('CSRF')[0].content;

    let json = {
      path,
      csrf
    };

    json = JSON.stringify(json);

    try {

      let response = await this.API.send('app', 'openFolder', json);

      if (response.isSelected) {
        this.selectedFolders.deleteSelectedFolderForPath(pathSelFolder);
        return 1;
      } else if (!response.isSelected && !response.isExists) {
        this.updateWindow('/');
        return 1;
      }

      this.isEmptyStorage = response.emptyStorage;
      this.elements = response.elements.map(elem => {
        let datetime = new Datetime();
        datetime.setDate(elem.ctime);
        elem.ctime = datetime.getDate();
        return elem;
      });
      this.pathUser = response.pathUser;
      this.countFiles = response.countFiles;

      let path = this.pathUser.split('/').reduce((fullPath, folder) => {

        if (folder === '') {
          fullPath.path += '/';
          fullPath.elements.push('');
          return fullPath;
        }
        fullPath.path += `${folder}/`;

        let span = `<span action='goToFolder' path='${fullPath.path}'>${folder}</span>`;
        fullPath.elements.push(span);
        return fullPath;

      }, {
        elements: [],
        path: ''
      });

      this.path = path.elements.join('/');
      console.log('Окно обновлено');
    } catch (e) {
      console.error(e);
      await this.modal.alert(`Ошибка открытия папки: ${e.message}`);
    }
  },
  async pasteFiles() { // вставить файлы

    if (this.isEmptyBuffer()) {
      await this.modal.alert('Буффер обмена пустой');
      return 1;
    }

    let pasteFiles = this.buffer;
    pasteFiles.newPath = this.pathUser;
    let files = pasteFiles.files;

    let action = null;

    switch (pasteFiles.mode) {
      case 'copy':
        action = 'copyFiles';
        break;
      case 'cut':
        action = 'cutFiles';
        break;
      default:
        return 1;
    }

    for (let i = 0; i < files.length; i++) {
      let file = files[i];
      let csrf = document.getElementsByName('CSRF')[0].content;
      
      let json = {
        oldPath: pasteFiles.oldPath,
        newPath: pasteFiles.newPath,
        fileName: file[0],
        fileType: file[1],
        csrf
      };

      json = JSON.stringify(json);
      console.log(`Вставка файла ${file[0]}.${file[1]}; Режим вставки: ${pasteFiles.mode}; Старый путь: ${pasteFiles.oldPath}; Новый путь: ${pasteFiles.newPath}`);
      this.API.send('app', action, json)
        .then(response => {

          if (!response.status) {
            throw new Error('Ошибка: Не удалось вставить файл');
          }

          if (this.isEmptyStorage) {
            this.isEmptyStorage = false;
            this.countFiles = 0;
          }

          let pathUser = this.pathUser;
          let dataFile = response.file;
          let file = null;

          this.countFiles++;

          if (pasteFiles.mode === 'copy') {
            this.freeSize = response['freeSize'];
            this.freeSizeInPercent = response['freeSizePercent'];
          } else if (pasteFiles.mode === 'cut') {
            this.buffer = null;
          }

          if (response.selectedFolders !== null && pasteFiles.mode === 'cut') {
            this.selectedFolders.folders = response.selectedFolders;
          }

          let datetime = new Datetime();
          datetime.setDate(dataFile['ctime']);
          dataFile['ctime'] = datetime.getDate();

          file = {
            path: pathUser,
            dataType: {
              type: dataFile['dataType']['type'],
              title: dataFile['dataType']['title'],
              img: dataFile['dataType']['img'] ?? null
            },
            formatedSize: dataFile['formatedSize'],
            ctime: dataFile['ctime'],
            name: dataFile['name'],
            isFile: dataFile['isFile'],
            isActive: dataFile['isActive'],
            isEditName: dataFile['isEditName'],
            isLoading: dataFile['isLoading'],
            fullsize: dataFile['fullsize'] ?? ""
          };
          this.elements.push(file);
          console.log('Вставка файла успешна');
        }).catch(e => {
          console.error(e);
          this.modal.alert(`Ошибка вставки: ${e.message}`);
        });
    }
  },
  cutFiles(files) { // вырезать файлы

    let cutFiles = {
      oldPath: null,
      newPath: null,
      files: null,
      mode: 'cut'
    };

    cutFiles.oldPath = this.pathUser;
    let nameFiles = files.map(elem => [elem.name, elem.isFile ? `.${elem.dataType.type}` : '']);
    cutFiles.files = nameFiles;
    this.buffer = cutFiles;
  },
  copyFiles(files) { // копировать файлы

    let copyFiles = {
      oldPath: null,
      newPath: null,
      files: null,
      mode: 'copy'
    };

    copyFiles.oldPath = this.pathUser;
    let nameFiles = files.map(elem => [elem.name, elem.isFile ? `.${elem.dataType.type}` : '']);
    copyFiles.files = nameFiles;
    this.buffer = copyFiles;
  },
  createHiddenForm(inputs) { // создать скрытую форму для скичивания файла

    let form = document.createElement('form');
    form.classList.add('hidden');
    let countInputs = Object.values(inputs).length;
    inputs = Object.entries(inputs);

    for (let i = 0; i < countInputs; i++) {
      let input = document.createElement('input');
      input.type = 'text';
      input.name = inputs[i][0];
      input.value = inputs[i][1];
      form.append(input);
    }

    let inputSub = document.createElement('input');
    inputSub.type = 'submit';
    form.append(inputSub);
    return form;

  },
  async downloadFiles(files) { // скачать файл
    let fileNames = files.map(elem => `${elem.name}.${elem.dataType.type}`);
    let url = 'api/app/downloadFile.php';

    for (let i = 0; i < fileNames.length; i++) {

      let fileName = fileNames[i];
      let csrf = document.getElementsByName('CSRF')[0].content;

      let json = {
        fileName,
        csrf
      };

      json = JSON.stringify(json);

      try {
        console.log(`Скачивание файла: ${fileName}`);
        let response = await this.API.send('app', 'downloadFile', json);

        let token = response;
        let csrf = document.getElementsByName('CSRF')[0].content;

        let dataInput = {
          'fileName': fileName,
          't': token,
          'csrf': csrf
        };

        let form = this.createHiddenForm(dataInput);
        form.method = 'POST';
        form.action = url;
        document.body.append(form);
        form.querySelector('[type="submit"]').click();
        form.remove();
      } catch (e) {
        console.error(e);
        await this.modal.alert(`Ошибка скачивания: ${e.message}`);
      }
    }
  },
  async deleteFiles(files) { // удалить файл
    let allElements = this.elements;
    let fileNames = files.map(elem => `${elem.name}${elem.isFile ? `.${elem.dataType.type}` : ''}`);
    let isFiles = files.map(elem => elem.isFile);
    let pathFiles = files.map(elem => `${elem.path + elem.name + '/'}`);

    for (let i = 0; i < files.length; i++) {

      let fileName = fileNames[i];
      let isFile = isFiles[i];
      let filePath = pathFiles[i];
      let csrf = document.getElementsByName('CSRF')[0].content;

      let json = {
        fileName,
        isFile,
        csrf
      }

      json = JSON.stringify(json);

      this.API.send('app', 'deleteFile', json)
        .then(response => {

          console.log(`Удаление файла: ${fileName}`);

          for (let i in allElements) {
            if (allElements[i].path + allElements[i].name + '/' === filePath) {
              allElements.splice(i, 1);
              break;
            }
          }

          if (response['folders']) {
            response['folders'].forEach(function (elem) {
              return this.selectedFolders.deleteSelectedFolderForPath(elem)
            }, {
              selectedFolders: this.selectedFolders
            });
          }

          this.countFiles--;
          if (this.countFiles === 0) {
            this.isEmptyStorage = true;
          }
          this.freeSize = response['freeSize'];
          this.freeSizeInPercent = response['freeSizePercent'];
          console.log(`Файл ${fileName} удален`);
        }).catch(async (e) => {
          console.error(e);
          this.modal.alert(`Ошибка удаления: ${e.message}`);
        });
    }
  },
  async renameFile() { // переименовать файл

    if (this.editElement === null && this.editName === null) {
      return 1;
    }

    let newName = this.editElement.innerText.trim();
    let oldName = this.editName;
    let oldFullName = null;
    let newFullName = null;
    let element = this.elements[this.editElement.parentNode.getAttribute('index')];
    let isFile = true;
    let oldPath = null;
    element.name = newName;

    await this.$nextTick();

    if (!element.isFile) {
      oldPath = element.path + oldName + '/';
      isFile = false;
    }

    if (newName.length > 200 || newName === '<br>' || newName === "\n" || newName === "") {
      await this.modal.alert('Имя файла должно быть от 1 до 200 символов.');
      console.error('Имя файла должно быть от 1 до 200 символов.');
      return 1;
    } else if (this.forbiddenChars.map(elem => newName.includes(elem)).includes(true)) {
      await this.modal.alert('Имя файла не должно содержать следующих символов:\n? \\ / * : " < > |');
      console.error('Имя файла не должно содержать следующих символов:\n? \\ / * : " < > |');
      return 1;
    } else if (this.isExistFile(newName)) {
      element.name = oldName;
      this.editElement.focus();
      await this.modal.alert("Такой файл уже существует. Придумайте другое название.");
      console.error("Такой файл уже существует. Придумайте другое название.");
      return 1;
    }

    element.isEditName = false;

    await this.$nextTick();

    if (oldName === newName) {
      this.renameMode = false;
      this.editElement = null;
      this.editName = null;
      return 1;
    }

    if (isFile) {
      let typeFile = `.${element.dataType.type}`;
      oldFullName = `${oldName}${typeFile}`;
      newFullName = `${newName}${typeFile}`;
    }
    else {
      oldFullName = oldName;
      newFullName = newName;
    }

    let isSelectedFolder = this.selectedFolders.isSelectedFolder(oldPath);
    let csrf = document.getElementsByName('CSRF')[0].content;
    let json = {
      oldFullName,
      newFullName,
      isFile,
      isSelectedFolder,
      csrf
    }

    json = JSON.stringify(json);

    try {

      let response = await this.API.send('app', 'renameFile', json);

      console.log(`Переименование файла: Старое имя: ${oldFullName}; Новое имя: ${newFullName}`);

      if (!response.isRenamed) {
        await this.modal.alert('Такой файл уже существует.');
        console.error('Такой файл уже существует.');
        this.editName = oldName;
        this.editElement.innerText = oldName
        element.isEditName = true;
        return 1
      }
      else if (isSelectedFolder && response.isSelectedFolder !== null) {
        this.selectedFolders.updateNameFolder(response.isSelectedFolder.oldName, response.isSelectedFolder.oldPath, response.isSelectedFolder.newName, response.isSelectedFolder.newPath);
      }

      if (response.selectedFolders !== null) {
        this.selectedFolders.folders = response.selectedFolders;
      }

      element.name = newName;
      this.editElement = null;
      this.editName = null;
      this.renameMode = false;
      console.log('Файл переименован');
    } catch (e) {
      console.error(e);
      this.renameMode = false;
      await this.modal.alert(`Ошибка переименования: ${e.message}`);
    }
  },
  async setModeRenameFile(file) { // установть режим редактирования имени файла

    if (!file.isLoading) {
      this.renameMode = true;
      file.isEditName = true;
      this.editName = file.name;
      await this.$nextTick();
      let element = document.querySelector('.editNameElement');
      this.editElement = element;
      element.focus();
    }

  },
  isExistFile(nameFile) { // проверка существует ли файл
    let elements = this.elements;
    let countElements = 0;
    for (let element of elements) {
      if (element.name.toLocaleLowerCase() === nameFile.toLocaleLowerCase()) {
        countElements++;
      }
    }

    if (countElements > 1) {
      return true;
    }
    return false;
  },
  isEmptyBuffer() { // проверка на пустоту буффера обмена
    return this.buffer === null ? true : false;
  },
  abortLoading(file) { // отмена загрузки файла
    file.isLoading = false;
  },
  createInputFile() { // создание поля загрузки файлов
    let input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('class', 'hidden');
    document.body.insertAdjacentElement('beforeend', input);
    return input;
  },
  uploadFileFromInput(input) { // загрузка файлов через поле 
    input.removeEventListener('change', this.uploadFileFromInput.bind(null, input));
    input.remove();
    this.uploadFiles(input.files);
  },
  async uploadFileFromEvent(e) { // загрузка файлов через событие drag'n drop
    e.preventDefault();
    let files = e.dataTransfer.files;
    let items = e.dataTransfer.items;
    for (let item of items) {
      let entry = item.webkitGetAsEntry();
      if (entry.isDirectory) {
        console.error('Загружать папки нельзя, только файлы');
        await this.modal.alert('Загружать папки нельзя, только файлы');
        return 1;
      }
    }
    this.uploadFiles(files);
  }
}