<?php

require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/errors/explorerError.php";

class ExplorerModel
{
  private string $pathUser; // путь пользователя
  private string $pathStorage; // путь к хранилищу пользователя
  private array $typesFile = [ // типы файлов
    'txt' => 'Текстовый документ',
    '7z' => 'Архив 7-ZIP',
    'avi' => 'Видео файл',
    'css' => 'Каскадные таблицы стилей',
    'doc' => 'Документ MS Word (bin)',
    'docx' => 'Документ MS Word (XML)',
    'exe' => 'Исполняемый файл Windows',
    'flac' => 'Аудио файл',
    'gif' => 'Растровое изображение с поддержкой анимации',
    'html' => 'Веб-страница',
    'htm' => 'Веб-страница',
    'jpeg' => 'Растровое изображение',
    'jpg' => 'Растровое изображение',
    'js' => 'JavaScript скрипт',
    'mkv' => 'Видео файл',
    'mp3' => 'Аудио файл',
    'mp4' => 'Видео файл',
    'pdf' => 'Универсальный формат документа',
    'php' => 'PHP скрипт',
    'png' => 'Растровое изображение',
    'ppt' => 'Презентация MS PowerPoint (bin)',
    'pptx' => 'Презентация MS PowerPoint (XML)',
    'py' => 'Python скрипт',
    'rar' => 'Архив WinRAR',
    'sql' => 'Инструкции на языке SQL',
    'svg' => 'Векторное изображение',
    'wav' => 'Аудио файл',
    'xls' => 'Книга MS Excel (bin)',
    'xlsx' => 'Книга MS Excel (XML)',
    'zip' => 'Архив ZIP'
  ];
  private array $typeSize = [ // типы размеров данных
    'байт',
    'КБ',
    'МБ',
    'ГБ',
    'TБ',
    'ПБ',
    'ЭБ',
    'ЗБ',
    'ЙБ'
  ];

  private array $forbiddenChars = ['/', '\\', '?', '*', ':', '"', '<', '>', '|']; // запрещенные символы в имени папок

  public function __construct(string $pathUser, string $pathStorage)
  {
    $this->pathUser = $pathUser;
    $this->pathStorage = $pathStorage;
  }

  // Получение текущего пути пользователя
  public function getPathUser(): string
  {
    return $this->pathUser;
  }

  // Получение пути хранилища пользователя
  public function getPathStorage(): string
  {
    return $this->pathStorage;
  }

  // Cоздание папки
  public function createFolder(string $nameFolder, bool $isFullPath = false): bool
  {
    $isCreated = null;

    if ($isFullPath) {
      $nameFolder = $this->sanitizePath($nameFolder);
      if (!$this->isCorrectNameFile(basename($nameFolder)) || is_dir($nameFolder)) {
        return false;
      }
      $isCreated = mkdir($nameFolder);
    } else {
      $path = "{$this->pathStorage}{$this->pathUser}{$nameFolder}";
      $path = $this->sanitizePath($path);
      if (!$this->isCorrectNameFile(basename($nameFolder)) || is_dir($path)) {
        return false;
      }
      $isCreated = mkdir($path);
    }

    return $isCreated;
  }

  // Открытие папки
  public function openFolder(string $path): bool
  {
    $path = $this->sanitizePath($path);

    if (!$this->isCorrectNameFile(basename($path))) {
      throw new ExplorerError('Имя папки не корректное');
    }

    if ($path[strlen($path) - 1] !== '/') {
      $path .= '/';
    }

    if (!is_dir("{$this->pathStorage}{$path}")) {
      throw new ExplorerError('Папка не найдна');
    }
    $this->pathUser = $path;

    return true;
  }

  // Получение корректного пути
  public function sanitizePath(string $path): string
  {
    $oldPath = '';
    while ($oldPath !== $path) {
      $oldPath = $path;
      $path = str_replace(['../', '/../', '//', './', '\\', '/./', '/.', '/..'], '/', $path);
    }
    return $path;
  }

  // Получение файлов из текущего пути пользователя
  public function getFilesFromCurrentPath(): FilesystemIterator
  {
    $path = "{$this->pathStorage}{$this->pathUser}";
    $path = $this->sanitizePath($path);
    $files = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
    return $files;
  }

  // Получение расширения файла
  public function getTypeFile(string $nameFile): ?string
  {
    $posDot = strrpos($nameFile, '.');
    if ($posDot === false) {
      return null;
    }
    $typeFile = substr($nameFile, $posDot + 1);
    return $typeFile;
  }

  // Получение имени изображения файла по типу 
  public function getTypeFileForImg(string $typeFile): string
  {
    if (!in_array($typeFile, array_keys($this->typesFile), true)) {
      return "WithoutFormat";
    }
    return $typeFile;
  }

  // Получение заголовка файла
  public function getTitleFile(string $typeFile): string
  {
    if (!in_array($typeFile, array_keys($this->typesFile), true)) {
      return "{$typeFile} файл";
    }
    return $this->typesFile[$typeFile];
  }

  // Получение имения файла без расширения
  public function getShortNameFile(string $nameFile): string
  {
    $posDot = strrpos($nameFile, '.');
    if ($posDot === false) {
      return $nameFile;
    }
    $nameFile = substr($nameFile, 0, $posDot);
    return $nameFile;
  }

  // Получение даты из timestamp
  public function getDataFromTimestamp(int $timestamp): string
  {
    return date("Y-m-d H:i:s", $timestamp);
  }

  // Урезание дробного числа до чисел после точки
  public function truncNumber(float $number, int $pos = 2): float
  {
    $number = (string) $number;
    $divPath = substr($number, strrpos($number, '.'));
    if ($divPath === $number) {
      return (float) $number;
    }
    $divPath = substr($divPath, 0, $pos + 1);
    $number = substr($number, 0, strrpos($number, '.'));

    $result = $number . $divPath;
    return (float) $result;
  }

  // Получение сокращенного размера файла
  public function shortSizeFile(int $bytes): ?string
  {
    if ($bytes < 0) {
      return null;
    }

    $countLoops = 0;
    $size = $bytes;
    $countTypeSizes = count($this->typeSize) - 1;

    while ($size >= 1024) {
      $size = $size / 1024;
      $countLoops++;
      if ($countLoops === $countTypeSizes) {
        break;
      }
    }

    $typeSize = $this->typeSize[$countLoops];
    $size = $this->truncNumber($size);

    return "{$size} {$typeSize}";
  }

  // Получение метаданных о файле или папке 
  public function getStatFile(string $nameFile, bool $isFullPath = false): ?array
  {
    $pathFile = null;

    if (!$isFullPath) {
      $pathFile = "{$this->pathStorage}{$this->pathUser}{$nameFile}";
      if (!$this->isCorrectNameFile(basename($pathFile))) {
        throw new ExplorerError('Имя файла не корректное');
      }
    } else {
      $nameFile = $this->sanitizePath($nameFile);
      $pathFile = $nameFile;
      $nameFile = basename($nameFile);
      if (!$this->isCorrectNameFile($nameFile)) {
        throw new ExplorerError('Имя файла не корректное');
      }
    }

    $pathFile = $this->sanitizePath($pathFile);

    if (!is_file($pathFile) && !is_dir($pathFile)) {
      return null;
    }

    $statFile = stat($pathFile);

    if (!is_file($pathFile)) {
      return [
        'dataType' => ['type' => 'Folder', 'title' => 'Папка'],
        'name' => $nameFile,
        'isFile' => false,
        'ctime' => $statFile['ctime']
      ];
    }

    $typeFile = $this->getTypeFile($nameFile);
    $titleFile = $this->getTitleFile($typeFile);
    $typeForImg = $this->getTypeFileForImg($typeFile);
    $shortFileName = $this->getShortNameFile($nameFile);
    $formatedSize = $this->shortSizeFile($statFile['size']);

    return [
      'dataType' => ['type' => $typeFile, 'title' => $titleFile, 'img' => strtoupper($typeForImg)],
      'name' => $shortFileName,
      'isFile' => true,
      'formatedSize' => $formatedSize,
      'ctime' => $statFile['ctime'],
      'fullsize' => $statFile['size']
    ];
  }

  // Загрузка файлов
  public function uploadFile(array $file): array
  {
    $message = ['isMoved' => false, 'nameFile' => null];
    $tmpfile = $file['tmp_name'];
    $fileName = [substr($file['name'], 0, strrpos($file['name'], '.')), substr($file['name'], strrpos($file['name'], '.'))];

    $nameFile = "{$fileName[0]}{$fileName[1]}";
    $pathFile = "{$this->pathStorage}{$this->pathUser}{$nameFile}";

    $pathFile = $this->sanitizePath($pathFile);

    if (is_file($pathFile)) {
      for ($i = 0; $i < 9999; $i++) {
        $nameFile = "{$fileName[0]} ({$i}){$fileName[1]}";
        $pathFile = "{$this->pathStorage}{$this->pathUser}{$nameFile}";
        if (!is_file($pathFile)) {
          break;
        }
        if ($i >= 9998) {
          throw new ExplorerError('Превышено количество имен загруженных файлов');
        }
      }
    }

    $isMoved = move_uploaded_file($tmpfile, $pathFile);

    if (!$isMoved) {
      return $message;
    }

    $message['isMoved'] = true;
    $message['nameFile'] = $nameFile;
    return $message;
  }

  // Удаление файла
  public function deleteFile(string $filePath, bool $isFullPath = false): ?array
  {
    $path = null;

    if ($isFullPath) {
      $path = $filePath;
    } else {
      $path = "{$this->pathStorage}{$this->pathUser}{$filePath}";
    }

    $path = $this->sanitizePath($path);

    if (!$this->isCorrectNameFile(basename($path)) || !is_file($path)) {
      return null;
    }

    $data = [
      'isDeleted' => false,
      'sizeFile' => filesize($path)
    ];

    $isDeleted = unlink($path);

    $data['isDeleted'] = $isDeleted;

    return $data;
  }

  // Проверка на пустоту папки
  public function isEmptyFolder(string $path): ?bool
  {
    $path = $this->sanitizePath($path);

    if (!$this->isCorrectNameFile(basename($path)) || !is_dir($path)) {
      return null;
    }

    $files = scandir($path);
    $files = array_diff($files, ['.', '..']);

    if (count($files) === 0) {
      return true;
    }

    return false;
  }

  // Очистка папки
  public function clearFolder(string $path): ?array
  {
    $path = $this->sanitizePath($path);

    if (!$this->isCorrectNameFile(basename($path)) || !is_dir($path)) {
      return null;
    }

    if ($path[strlen($path) - 1] === '/') {
      $path = substr($path, 0, strrpos($path, '/'));
    }

    $data = [
      'isDeleted' => false,
      'sizeFile' => 0,
      'folders' => []
    ];

    $files = scandir($path);
    $files = array_diff($files, ['.', '..']);

    foreach ($files as $file) {
      $pathFile = "{$path}/{$file}";

      if (is_dir($pathFile)) {
        if (!$this->isEmptyFolder($pathFile)) {
          $clearFolder = $this->clearFolder($pathFile);
          $data['sizeFile'] += $clearFolder['sizeFile'];
          $data['folders'] = array_merge($data['folders'], $clearFolder['folders']);
        }
        rmdir($pathFile);
        array_push($data['folders'], str_replace($this->pathStorage, '', $pathFile) . '/');
      } else {
        $data['sizeFile'] += filesize($pathFile);
        unlink($pathFile);
      }
    }

    $data['isDeleted'] = true;

    return $data;
  }

  // Удаление папки
  public function deleteFolder(string $folderName, bool $isFullPath = false): ?array
  {
    $folderName = $this->sanitizePath($folderName);

    $path = null;
    $data = [
      'isDeleted' =>false,
      'sizeFile' => 0,
      'folders' => []
    ];

    if ($isFullPath) {
      $path = $folderName;
    } else {
      $path = "{$this->pathStorage}{$this->pathUser}{$folderName}";
    }

    if (!$this->isCorrectNameFile(basename($path)) || !is_dir($path)) {
      return null;
    }

    if ($this->isEmptyFolder($path)) {
      rmdir($path);
    } else {
      $dataDeletedFolder = $this->clearFolder($path);

      $data['sizeFile'] = $dataDeletedFolder['sizeFile'];
      $data['folders'] = $dataDeletedFolder['folders'];
      rmdir($path);
    }

    array_push($data['folders'], str_replace($this->pathStorage, '', $path) . '/');

    $data['isDeleted'] = true;

    return $data;
  }

  // Проверка корректное ли имя файла
  public function isCorrectNameFile(string $nameFile): bool
  {
    if (strlen($nameFile) === array_count_values(mb_str_split($nameFile))['.']) {
      return false;
    }

    $forbiddenChars = $this->forbiddenChars;
    foreach ($forbiddenChars as $char) {
      if (strpos($nameFile, $char) !== false) {
        return false;
      }
    }

    return true;
  }

  // Переименование файла
  public function renameFile(string $oldName, string $newName): ?bool
  {

    if (!$this->isCorrectNameFile($newName)) {
      throw new ExplorerError('Имя файла не корректное');
    }

    if ($newName[strlen($newName) - 1] === '.') {
      while ($newName[strlen($newName) - 1] === '.') {
        $newName = substr($newName, 0, strrpos($newName, '.'));
      }
    }

    $oldPath = "{$this->pathStorage}{$this->pathUser}{$oldName}";
    $newPath = "{$this->pathStorage}{$this->pathUser}{$newName}";

    $oldPath = $this->sanitizePath($oldPath);
    $newPath = $this->sanitizePath($newPath);

    if ((!is_dir($oldPath) && !is_file($oldPath))) {
      return false;
    }

    $filesDir = scandir("{$this->pathStorage}{$this->pathUser}");

    for ($i = 0; $i < count($filesDir); $i++) {
      if ($oldName === $filesDir[$i]) {
        $filesDir[$i] = $newName;
      }
    }

    $existsFiles = [];

    for ($i = 0; $i < count($filesDir); $i++) {
      if ($filesDir[$i] === $newName) {
        array_push($existsFiles, $filesDir[$i]);
      }
    }

    if ((is_file($newPath) || is_dir($newPath)) && count($existsFiles) > 1) {
      throw new ExplorerError('Файл с таким именем уже существует');
    }

    $isRenamed = rename($oldPath, $newPath);

    if (!$isRenamed) {
      return false;
    }

    return true;
  }

  // Скачивание файла
  public function downloadFile(string $path): void
  {
    $path = $this->sanitizePath($path);

    if (!$this->isCorrectNameFile(basename($path))) {
      throw new ExplorerError('Имя файла не корректное');
    }

    $baseFileName = basename($path);
    header('Content-Type: application/octet-stream');
    header("Content-Disposition: attachemt; filename={$baseFileName}");
    readfile($path);
  }

  // Генерация токена для скачивания файла
  public function genToken(int $length): string
  {
    return bin2hex(random_bytes($length));
  }

  // Копирование файла
  public function copyFile(string $oldPath, string $newPath): ?array
  {

    $oldPath = $this->sanitizePath($oldPath);
    $newPath = $this->sanitizePath($newPath);

    if (!$this->isCorrectNameFile(basename($oldPath)) || !$this->isCorrectNameFile(basename($newPath)) || !is_file($oldPath)) {
      return null;
    }

    $data = [
      'isCopy' => false,
      'sizeFile' => filesize($oldPath)
    ];

    $isCopy = copy($oldPath, $newPath);

    if (!$isCopy) {
      return $data;
    }

    $data['isCopy'] = true;
    return $data;
  }

  // Копирование папки
  public function copyFolder(string $oldPath, string $newPath): ?array
  {

    $oldPath = $this->sanitizePath($oldPath);
    $newPath = $this->sanitizePath($newPath);

    if (!$this->isCorrectNameFile(basename($oldPath)) || !$this->isCorrectNameFile(basename($newPath)) || !is_dir($oldPath)) {
      return null;
    }

    $data = [
      'isCopy' => false,
      'sizeFile' => 0
    ];

    $files = array_diff(scandir($oldPath), ['.', '..']);

    if (!is_dir($newPath)) {
      $this->createFolder($newPath, true);
    }

    foreach ($files as $file) {

      $oldPathFolder = "{$oldPath}{$file}";
      $newPathFolder = "{$newPath}{$file}";

      if (is_dir($oldPathFolder)) {
        $isCreated = $this->createFolder($newPathFolder, true);

        if (!$isCreated) {
          return $data;
        }

        $oldPathFolder .= '/';
        $newPathFolder .= '/';

        $isCopy = $this->copyFolder($oldPathFolder, $newPathFolder);

        if (is_null($isCopy) || !$isCopy['isCopy']) {
          return $data;
        }
        $data['sizeFile'] += $isCopy['sizeFile'];
      } else {
        $isCopy = $this->copyFile($oldPathFolder, $newPathFolder);

        if (is_null($isCopy) || !$isCopy['isCopy']) {
          return $data;
        }

        $data['sizeFile'] += $isCopy['sizeFile'];
      }
    }
    $data['isCopy'] = true;
    return $data;
  }

  // Получение размера папки
  public function getSizeFolder(string $path): ?int
  {
    $path = $this->sanitizePath($path);

    if (!$this->isCorrectNameFile(basename($path)) || !is_dir($path)) {
      return null;
    }

    $sizeDir = 0;
    $files = array_diff(scandir($path), ['.', '..']);

    foreach ($files as $file) {
      $newPath = "{$path}/{$file}";
      if (is_dir($newPath)) {
        $sizeDir += $this->getSizeFolder($newPath);
      } else {
        $sizeDir += filesize($newPath);
      }
    }
    return $sizeDir;
  }
}
