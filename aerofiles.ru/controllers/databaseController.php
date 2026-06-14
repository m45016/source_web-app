<?php

require "{$_SERVER['DOCUMENT_ROOT']}/models/DataBaseModel.php";

class DataBaseController
{

  private DataBaseModel $model; // модель базы данных

  public function __construct(string $domain, string $user, string $password, string $db_name)
  {
    $this->model = new DataBaseModel($domain, $user, $password, $db_name);
  }

  // Регистрация пользователя
  public function regUser(string $login, string $password, string $email): bool
  {
    $pass_hash = password_hash($password, PASSWORD_DEFAULT);

    $this->model->createUser($login, $pass_hash, $email);

    return true;
  }

  // Аутентификация пользователя
  public function authUser(string $login, string $password): array
  {

    $user = $this->model->getUser($login);

    if ($user->num_rows === 0) {
      throw new DataBaseError('Пользовтель не найден');
    }

    $user = $user->fetch_assoc();

    if (!password_verify($password, $user['password'])) {
      throw new DataBaseError('Пароли не совпадают');
    }

    return $user;
  }

  // Получние данных пользователя по ID
  public function getUserById(int $idUser): ?array
  {
    $user = $this->model->getUserById($idUser);

    if ($user->num_rows === 0) {
      return null;
    }

    $user = $user->fetch_assoc();

    return $user;
  }

  // Получние данных пользователя по логину
  public function getUserByLogin(string $login): ?array
  {
    $user =  $this->model->getUser($login);

    if ($user->num_rows === 0) {
      return null;
    }

    $user = $user->fetch_assoc();

    return $user;
  }

  // Сброс пароля пользователя
  public function resetPasswordUser(string $email, string $password): bool
  {

    $isReset = $this->model->resetPasswordUser($email, $password);

    if ($isReset === 0) {
      return false;
    }

    return true;
  }

  // Получение избранных папок пользователя
  public function getSelectedFolders(int $idUser): ?array
  {
    $selectedFolders = $this->model->getSelectedFolders($idUser);

    $folders = [];

    foreach ($selectedFolders as $selectedFolder) {
      array_push($folders, $selectedFolder);
    }

    if(count($folders)===0){
      return null;
    }

    return $folders;
  }

  // Проверка избрана ли папка
  public function isSelectedFolder(int $idUser, string $path): bool
  {
    $selectedFolders = $this->model->getSelectedFolders($idUser);

    foreach ($selectedFolders as $selectedFolder) {
      if (strtolower($selectedFolder['path']) === strtolower($path)) {
        return true;
      }
    }

    return false;
  }

  // Добавление папки в избранное
  public function addSelectedFolder(int $idUser, string $folder, string $path): bool
  {
    $isSelectedFolder = $this->isSelectedFolder($idUser, $path);

    if ($isSelectedFolder) {
      throw new DataBaseError('Папка уже в избранном');
    }

    $isAdd = $this->model->addSelectedFolder($idUser, $folder, $path);

    return $isAdd;
  }

  // Обновление данных избранной папки
  public function updateSelectedFolder(int $idUser, string $oldName, string $newName, string $oldPath, string $newPath): bool
  {
    $isUpdate = $this->model->updateSelectedFolder($idUser, $oldName, $newName, $oldPath, $newPath);

    if ($isUpdate === 0) {
      return false;
    }

    return true;
  }

  // Обновление пути избранных папок
  public function updatePathSelectedFolders(int $idUser, string $oldPath, string $newPath): bool
  {
    $isUpdate = $this->model->updatePathSelectedFolders($idUser, $oldPath, $newPath);

    if ($isUpdate === 0) {
      return false;
    }

    return true;
  }

  // Обновление имени избранной папки по пути
  public function updateNameSelectedFolderForPath(int $idUser, string $oldName, string $newName, string $path): bool
  {
    $isUpdate = $this->model->updateNameSelectedFolderForPath($idUser, $oldName, $newName, $path);

    if ($isUpdate === 0) {
      return false;
    }

    return true;
  }

  // Удаление папки из избранного
  public function deleteSelectedFolder(int $idUser, string $path): bool
  {
    $isDeleted = $this->model->deleteSelectedFolder($idUser, $path);
    if ($isDeleted === 0) {
      return false;
    }

    return true;
  }

  // Добавление размера хранилищу пользователя
  public function addSizeStorage(int $idUser, int $fileSize): bool
  {

    $user = $this->getUserById($idUser);

    if (is_null($user)) {
      throw new DataBaseError('Пользователь не найден');
    }

    $isUpdate = $this->model->addSizeStorage($idUser, $fileSize);

    return $isUpdate;
  }

  // Вычитание размера хранилища пользователя
  public function subSizeStorage(int $idUser, int $fileSize): bool
  {

    $user = $this->getUserById($idUser);

    if (is_null($user)) {
      throw new DataBaseError('Пользователь не найден');
    }

    $isUpdate = $this->model->subSizeStorage($idUser, $fileSize);
    return $isUpdate;
  }

  // Получение данных о хранилище пользователя
  public function getStorageInfo(int $idUser): array
  {

    $user = $this->getUserById($idUser);

    if (is_null($user)) {
      throw new DataBaseError('Пользователь не найден');
    }

    $storageInfo = $this->model->getStorageInfo($idUser);

    return $storageInfo->fetch_assoc();
  }

  // Получение данных о хранилище пользователя по логину
  public function getStorageInfoByLogin(string $login): array
  {

    $user = $this->getUserByLogin($login);

    if (is_null($user)) {
      throw new DataBaseError('Пользователь не найден');
    }

    $storageInfo = $this->model->getStorageInfo($user['id_user']);

    return $storageInfo->fetch_assoc();
  }

  // Обновление данных пользователя
  public function updateUserData(int $idUser, string $tariff, int $isAdmin): bool
  {
    $isUpdate = $this->model->updateUserData($idUser, $tariff, $isAdmin);

    if ($isUpdate === 0) {
      return false;
    }

    return true;
  }

  public function getActiveUsers(): int{
    return $this->model->getActiveUsers();
  }

  public function setActiveUser(int $idUser, bool $isActive):bool{
    if($isActive){
      $isActive = 1;
    }
    else{
      $isActive = 0;
    }
    
    $isUpdate = $this->model->setAcviteUser($idUser, $isActive);
    return $isUpdate;
  }

  public function getAllTariffs(){
    return $this->model->getAllTariffs();
  }

  public function getLimitTariffs(int $from, int $to=10):?object{
    return $this->model->getLimitTariffs($from, $to);
  }

  public function getTariff(string $nameTariff):?array{
    return $this->model->getTariff($nameTariff);
  }

  public function setTariff(int $idUser, string $nameTariff):bool{

    $tariff = $this->model->getTariff($nameTariff);

    if(is_null($tariff)){
      throw new DataBaseError('Тариф не найден');
    }

    $idTariff = $tariff['id_tariff'];

    return $this->model->setTariff($idUser, $idTariff);
  }

  public function setTariffByLogin(string $login, string $nameTariff):bool{

    $tariff = $this->model->getTariff($nameTariff);

    if(is_null($tariff)){
      throw new DataBaseError('Тариф не найден');
    }

    $idTariff = $tariff['id_tariff'];

    return $this->model->setTariffByLogin($login, $idTariff);
  }

  public function updateDatePaymentUser(int $idUser):?string{

    $date = new DateTime();
    $date = $date->format('Y-m-d H:i:s');

    $isUpdate = $this->model->updateDatePaymentUser($idUser, $date);

    if(!$isUpdate){
      return null;
    }
    
    return $date;

  }

  public function dropDatePayment(int $idUser):bool{
    return $this->model->dropDatePayment($idUser);
  }

  public function paymentTariff(int $idUser, string $nameTariff):bool{
    return $this->model->paymentTariff($idUser, $nameTariff);
  }

  public function addBalance(int $idUser, int $balance):array{

    $currBalance = $this->getUserById($idUser)['balance'];

    $isUpdate = $this->model->addBalance($idUser, $balance);
    
    if(!$isUpdate){
      return ['balance'=>$currBalance,'isSuccess'=>false];
    }

    return ['balance'=>$currBalance + $balance, 'isSuccess'=>true];
  }

  public function isExistUser(string $email):bool{
    return $this->model->isExistUser($email);
  }

  // Закрытие БД
  public function close()
  {
    $this->model->close();
  }
}
