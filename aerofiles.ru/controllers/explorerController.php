<?php

require "{$_SERVER['DOCUMENT_ROOT']}/models/ExplorerModel.php";


class ExplorerController
{

  private ExplorerModel $explorer;
  private int $numberFolder = 0;
  private string $defaultNameFolder = 'Новая папка';

  public function __construct(string $pathUser, string $pathStorage)
  {
    $this->explorer = new ExplorerModel($pathUser, $pathStorage);
  }

  // Открытие папки
  public function openFolder(string $path): void
  {
    $this->explorer->openFolder($path);
  }

  // Получение текущего пути пользователя
  public function getPathUser(): string
  {
    return $this->explorer->getPathUser();
  }

  // Получение пути к хранилищу пользователя
  public function getPathStorage(): string
  {
    return $this->explorer->getPathStorage();
  }

  // Получение метаданных файла
  public function getStatFile(string $fileName, bool $isFullPath = false): array
  {

    $element = $this->explorer->getStatFile($fileName, $isFullPath);

    if (is_null($element)) {
      throw new ExplorerError('Файл не найден');
    }

    $element['ctime'] = $this->explorer->getDataFromTimeStamp($element['ctime']);
    $element['path'] = $this->getPathUser();
    $element['isActive'] = false;
    $element['isEditName'] = false;
    $element['isLoading'] = false;
    return $element;
  }

  // Создание папки
  public function createFolder(string $nameFolder = 'Новая папка'): array
  {
    $dataFolder = ['data' => null, 'status' => null];

    $isCreated = $this->explorer->createFolder($nameFolder);

    for ($i = 0; $i < 9999; $i++) {

      if ($isCreated) {
        break;
      }

      $this->numberFolder++;
      $nameFolder = "{$this->defaultNameFolder} ({$this->numberFolder})";
      $isCreated = $this->explorer->createFolder($nameFolder);
    }

    if ($this->numberFolder >= 9998) {
      throw new ExplorerError('Превышено количество имен новых папок');
    }

    $folderStat = $this->getStatFile($nameFolder);
    $dataFolder['data'] = $folderStat;
    $dataFolder['status'] = $isCreated;
    return $dataFolder;
  }

  // Создание хранилища пользователя
  public function createStorage(string $nameStorage): bool
  {
    if (!$this->isCorrectNameFile($nameStorage)) {
      throw new ExplorerError('Имя папки не должно содержать символы: / \\ ? * : < > |');
    }
    $isCreated = $this->explorer->createFolder($nameStorage);
    return $isCreated;
  }

  public function isCorrectNameFile(string $nameFile):bool{
    return $this->explorer->isCorrectNameFile($nameFile);
  }

  // Получение файлов из текущего пути пользователя
  public function getFilesFromCurrentPath(): ?array
  {

    $dataFiles = $this->explorer->getFilesFromCurrentPath();

    $pathUser = $this->getPathUser();

    $contentCurrentPath = [
      'elements' => [],
      'length' => 0,
      'path' => $pathUser
    ];

    $countElements = 0;
    $files = [];
    $folders = [];

    foreach ($dataFiles as $file) {
      $element = [];

      $typeFile = $this->explorer->getTypeFile($file->getFilename());

      $element['ctime'] = $this->explorer->getDataFromTimeStamp($file->getCTime());
      $element['path'] = $pathUser;
      $element['isActive'] = false;
      $element['isEditName'] = false;
      $element['isLoading'] = false;

      if ($file->isFile()) {
        $typeFile = $this->explorer->getTypeFile($file->getFilename());

        $element['isFile'] = true;
        $element['name'] = $this->explorer->getShortNameFile($file->getFilename());
        $element['dataType']['type'] = $typeFile;
        $element['dataType']['title'] = $this->explorer->getTitleFile($typeFile);
        $element['dataType']['img'] = strtoupper($this->explorer->getTypeFileForImg($typeFile));
        $element['fullsize'] = $file->getSize();
        $element['formatedSize'] = $this->explorer->shortSizeFile($file->getSize());

        array_push($files, $element);
      } else {

        $element['isFile'] = false;
        $element['name'] = $file->getFilename();
        $element['dataType']['type'] = 'Folder';
        $element['dataType']['title'] = 'Папка';
        $element['size'] = "";

        array_push($folders, $element);
      }
      $countElements++;
    }

    $contentCurrentPath['elements'] = array_merge($folders, $files);

    $contentCurrentPath['length'] = $countElements;

    return $contentCurrentPath;
  }

  // Загрузка файлов
  public function uploadFile(array $file): ?array
  {

    $isUpload = $this->explorer->uploadFile($file);
    if (!$isUpload['isMoved']) {
      return null;
    }

    return $isUpload;
  }

  // Удаление файла
  public function deleteFile(string $filePath, bool $isFullPath = false): array
  {
    $isDeleted = $this->explorer->deleteFile($filePath, $isFullPath);
    if (is_null($isDeleted)) {
      throw new ExplorerError('Файл не найден');
    }
    return $isDeleted;
  }

  // Удаление папки
  public function deleteFolder(string $folderPath, bool $isFullPath = false): array
  {

    $isDeleted = $this->explorer->deleteFolder($folderPath, $isFullPath);

    if (is_null($isDeleted)) {
      throw new ExplorerError('Папка не найдена');
    }

    return $isDeleted;
  }

  // Переименование файла
  public function renameFile(string $oldName, string $newName): bool
  {

    if ($oldName === $newName) {
      throw new ExplorerError('Имя файлов одинаковые');
    }

    $isRenamed = $this->explorer->renameFile($oldName, $newName);
    if (is_null($isRenamed) || !$isRenamed) {
      return false;
    }

    return true;
  }

  // Генерация токена
  public function genToken(): string
  {
    $lenToken = random_int(10, 20);
    $token = $this->explorer->genToken($lenToken);

    return $token;
  }

  // Скачивание файла
  public function downloadFile(string $fileName)
  {

    $filePath = "{$this->getPathStorage()}{$this->getPathUser()}{$fileName}";

    if (!is_file($filePath)) {
      throw new ExplorerError('Файл не найден');
    }

    $this->explorer->downloadFile($filePath);
  }

  // Проверка вложенного копирования
  public function isSelfCopy(string $oldPath, string $newPath): bool
  {
    $selfCopy = str_replace($oldPath, '', $newPath);

    return $selfCopy !== $newPath && $oldPath !== $newPath ? true : false;
  }

  // Копирование файла
  public function copyFile(string $oldPath, string $newPath, array $file): array
  {

    $oldPath = $this->explorer->sanitizePath($oldPath);
    $newPath = $this->explorer->sanitizePath($newPath);

    if (!$this->isCorrectNameFile($file[0])) {
      throw new ExplorerError('Не корректное имя файла');
    }

    $data = ['file' => [], 'status' => false, 'sizeFile' => 0];

    $oldPathUserFile = "{$oldPath}{$file[0]}{$file[1]}";
    $oldPathFile = "{$this->getPathStorage()}{$oldPathUserFile}";

    if (!file_exists($oldPathFile)) {
      throw new ExplorerError("Файл не найден");
    }
    $newPathUserFile = "{$newPath}{$file[0]}{$file[1]}";
    $newPathFile = "{$this->getPathStorage()}{$newPathUserFile}";

    // echo $oldPathFile, " ", $newPathFile, '<br>';

    if (is_dir($oldPathFile)) {

      $isSelfCopy = $this->isSelfCopy($oldPathFile, $newPathFile);

      if ($isSelfCopy) {
        throw new ExplorerError('Вложенное копирование папки');
      }

      if (is_dir($newPathFile)) {
        for ($i = 1; $i < 9999; $i++) {
          $newPathUserFile = "{$newPath}{$file[0]} Копия({$i}){$file[1]}";
          $newPathFile = "{$this->getPathStorage()}{$newPathUserFile}";
          if (!is_dir($newPathFile)) {
            break;
          } else if ($i >= 9998) {
            throw new ExplorerError('Превышено количество имен папки');
          }
        }
      }

      $oldPathFile .= '/';
      $newPathFile .= '/';

      $isCopy = $this->explorer->copyFolder($oldPathFile, $newPathFile);

      if (!$isCopy['isCopy']) {
        throw new ExplorerError('Папка скопирована не полностью');
      }

      $data['sizeFile'] = $isCopy['sizeFile'];
      $data['oldPathFile'] = $oldPathUserFile . '/';
      $data['newPathFile'] = $newPathUserFile . '/';
    } else if (is_file($oldPathFile)) {

      if (is_file($newPathFile)) {
        for ($i = 1; $i < 9999; $i++) {
          $newPathFile = "{$this->getPathStorage()}{$newPath}{$file[0]} Копия({$i}){$file[1]}";
          if (!is_file($newPathFile)) {
            break;
          } else if ($i >= 9998) {
            throw new ExplorerError('Превышено количество имен файла');
          }
        }
      }

      $isCopy = $this->explorer->copyFile($oldPathFile, $newPathFile);

      if (!$isCopy['isCopy']) {
        throw new ExplorerError('Файл не скопирован');
      }

      $data['sizeFile'] = $isCopy['sizeFile'];
    }

    $statFile = $this->getStatFile($newPathFile, true);

    // echo $newPathFile, '<br>';

    $data['file'] = $statFile;

    $data['status'] = true;

    return $data;
  }

  // Получение сокращенного размера файла
  public function shortSizeFile(int $bytes): string
  {
    $shortSizeFile = $this->explorer->shortSizeFile($bytes);
    if (is_null($shortSizeFile)) {
      throw new ExplorerError('Размер файла не может быть отрицательным');
    }
    return $shortSizeFile;
  }

  // Получение размера файла или папки
  public function getSizeFile(string $path): int
  {
    $path = "{$this->getPathStorage()}{$path}";

    if(!file_exists($path)){
      throw new ExplorerError('Файл не найден');
    }

    if (is_file($path)) {
      return filesize($path);
    } else {
      return $this->explorer->getSizeFolder($path);
    }
  }
  public function sanitizePath(string $path):string{
    return $this->explorer->sanitizePath($path);
  }
}
