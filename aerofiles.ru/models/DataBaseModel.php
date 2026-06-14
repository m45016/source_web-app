<?php

require "{$_SERVER['DOCUMENT_ROOT']}/assets/php/errors/databaseError.php";

class DataBaseModel
{

  private mysqli $mysql; // база данных

  public function __construct(string $domain, string $user, string $password, string $db_name)
  {
    $mysql = new mysqli($domain, $user, $password, $db_name);

    if ($mysql->connect_error) {
      throw new DataBaseError("База данных не найдена");
    }

    $mysql->query('SET time_zone = "+00:00";');

    $this->mysql = $mysql;
  }
  // Регистрация пользователя
  public function createUser(string $login, string $password, string $email):bool
  {

    $sql = "INSERT INTO `user` (`login`,`password`, `email`) VALUES (?,?,?);";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('sss', $login, $password, $email);
    $query->execute();

    if ($query->affected_rows <= 0) {
      throw new DataBaseError($query->error);
    }

    $query->close();
    return true;
  }
  // Получение пользователя по логину
  public function getUser(string $login): object
  {

    $sql = "SELECT `user`.*, DATE_FORMAT(DATE_ADD(`date_payment`, INTERVAL 1 MONTH),'%Y-%m-%d %T') as `tariffValidTo`, CURRENT_TIMESTAMP as `current_date`, `tariff`.`name` as `tariff_name` FROM `user` JOIN `tariff` ON `tariff`.`id_tariff` = `user`.`tariff` WHERE `login` = ?;";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('s', $login);
    $query->execute();

    $result = $query->get_result();

    $query->close();

    return $result;
  }
  // Получение пользователя по ID
  public function getUserById(int $idUser): object
  {

    $sql = "SELECT `user`.*, DATE_FORMAT(DATE_ADD(`date_payment`, INTERVAL 1 MONTH),'%Y-%m-%d %T') as `tariffValidTo`, CURRENT_TIMESTAMP as `current_date`, `tariff`.`name` as `tariff_name` FROM `user` JOIN `tariff` ON `tariff`.`id_tariff` = `user`.`tariff` WHERE `id_user` = ?";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('i', $idUser);
    $query->execute();

    $result = $query->get_result();

    $query->close();

    return $result;
  }
  // Сброс пароля пользователя 
  public function resetPasswordUser(string $email, string $password):int
  {
    $pass_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "UPDATE `user` SET `password` = ? WHERE `email` = ?;";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('ss', $pass_hash, $email);
    $query->execute();
    return $query->affected_rows;
  }

  // Добавление папки в избранное
  public function addSelectedFolder(int $idUser, string $folder, string $path): bool
  {

    $sql = "INSERT INTO `selectedFolder` (`user`, `folder`, `path`) VALUES (?,?,?)";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('iss', $idUser, $folder, $path);
    $query->execute();

    if ($query->affected_rows <= 0) {
      throw new DataBaseError('Невозможно добавить папку для несуществующего пользователя');
    }

    $query->close();
    return true;
  }

  // Получение избранных папок пользователя
  public function getSelectedFolders(int $idUser): object
  {

    $sql = "SELECT `folder`, `path` FROM `selectedFolder` WHERE `user`  = ?";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('i', $idUser);
    $query->execute();

    $result = $query->get_result();
    $query->close();

    return $result;
  }

  // Обновление данных избранной папки
  public function updateSelectedFolder(int $idUser, string $oldName, string $newName, string $oldPath, string $newPath):int
  {

    $sql = "UPDATE `selectedFolder` SET `folder` = ?, `path` = ? WHERE `user` = ? AND `folder` = ? AND `path` = ?";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('ssiss', $newName, $newPath, $idUser, $oldName, $oldPath);
    $query->execute();

    return $query->affected_rows;
  }

  // Обновление пути избраных папок
  public function updatePathSelectedFolders(int $idUser, string $oldPath, string $newPath):int
  {
    $sql = "UPDATE `selectedFolder` SET `path` = REPLACE(`path`, ?, ?) WHERE `user` = ? AND `path` LIKE ?;";
    
    $likeSQL = "{$oldPath}%";
    $query = $this->mysql->prepare($sql);
    $query->bind_param('ssis', $oldPath, $newPath, $idUser, $likeSQL);
    $query->execute();

    return $query->affected_rows;

  }

  // обновлние имени избранной папки по пути
  public function updateNameSelectedFolderForPath(int $idUser, string $oldName, string $newName, string $path):int
  {
    $sql = "UPDATE `selectedFolder` SET `folder` = ? WHERE `user` = ? AND `path` = ? AND `folder` = ?";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('siss', $newName, $idUser, $path, $oldName);
    $query->execute();

    return $query->affected_rows;
  }

  // Удаление папки из избранного
  public function deleteSelectedFolder(int $idUser, string $path):int
  {
    $sql = "DELETE FROM `selectedFolder` WHERE `user` = ?  AND `path` = ?";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('is', $idUser, $path);
    $query->execute();

    return $query->affected_rows;
  }

  // Добавление размера хранилищу пользователя
  public function addSizeStorage(int $idUser, int $fileSize): bool
  {
    $sql = "UPDATE `user` as `u` JOIN `tariff` as `t` ON `u`.`tariff` = `t`.`id_tariff` SET `u`.`sizeStorage` = `u`.`sizeStorage` + ? WHERE `u`.`id_user` = ? AND `u`.`sizeStorage` + ? <= `t`.`maxSizeStorage`;";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('iii', $fileSize, $idUser, $fileSize);
    $query->execute();

    if ($query->affected_rows <= 0) {
      throw new DataBaseError('Недостаточно места в хранилище :(');
    }

    $query->close();
    return true;
  }

  // Вычитание размера хранилища пользователя
  public function subSizeStorage(int $idUser, int $fileSize): bool
  {
    $sql = "UPDATE `user` SET `sizeStorage` = `sizeStorage` - ? WHERE `id_user` = ? AND `sizeStorage` - ? >= 0;";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('iii', $fileSize, $idUser, $fileSize);
    $query->execute();

    if ($query->affected_rows < 0) {
      throw new DataBaseError('Размер хранилище не может быть отрицательным');
    }

    $query->close();
    return true;
  }

  // Получение данных о хранилище пользователя
  public function getStorageInfo(int $idUser):object
  {
    $sql = "SELECT `t`.`maxSizeStorage` - `u`.`sizeStorage` as `freeSizeStorage`, `t`.`maxSizeStorage`, `u`.`sizeStorage` / `t`.`maxSizeStorage` * 100 as `freeSizeStorageInPercent` FROM `user` as `u` JOIN `tariff` as `t` ON `u`.`tariff` = `t`.`id_tariff` WHERE `id_user` = ?;";
    $query = $this->mysql->prepare($sql);
    $query->bind_param('i', $idUser);
    $query->execute();

    $result = $query->get_result();
    $query->close();

    return $result;
  }

  // Обновление данных пользователя
  public function updateUserData(int $idUser, string $tariff, int $isAdmin):int
  {
    $user = $this->getUserById($idUser);

    if($user->num_rows === 0){
      return 0;
    }
    
    $user = $user->fetch_assoc();

    if($tariff === 'No Change'){
      $tariff = $user['tariff_name'];
    }

    $tariff = $this->getTariff($tariff)['id_tariff'];

    if($isAdmin > 1 || $isAdmin < 0 || $tariff <= 0 || is_null($tariff)){
      return 0;
    }
    
    $sql = 'UPDATE `user` SET `isAdmin` = ?, `tariff` = ? WHERE `id_user` = ?;';

    $query = $this->mysql->prepare($sql);
    $query->bind_param('iii', $isAdmin, $tariff, $idUser);
    $query->execute();

    return $query->affected_rows;

  }

  public function getActiveUsers(): int{
    $sql = 'SELECT * FROM `user` WHERE isActive = 1';

    $query = $this->mysql->query($sql);

    return $query->num_rows;
  }

  public function setAcviteUser(int $idUser, int $isActive):bool{
    $sql = 'UPDATE `user` SET `isActive` = ? WHERE `id_user` = ?;';

    $query = $this->mysql->prepare($sql);
    $query->bind_param('ii', $isActive, $idUser);
    $query->execute();

    if($query->affected_rows === 0){
      return false;
    }

    return true;
  }

  public function getAllTariffs(){
    $sql = "SELECT * FROM `tariff`";
    $query = $this->mysql->query($sql);
    return $query;
  }

  public function getTariff(string $nameTariff):?array{
    $sql = "SELECT * FROM `tariff` WHERE `name`=?";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('s',$nameTariff);
    $query->execute();
    $result = $query->get_result();

    if($result->num_rows === 0){
      return null;
    }

    return $result->fetch_assoc();
  }

  public function getLimitTariffs(int $from, int $to=10):?object{
    $sql = "SELECT * FROM `tariff` LIMIT ?,?";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('ii',$from, $to);
    $query->execute();
    $result = $query->get_result();

    if($result->num_rows === 0){
      return null;
    }

    return $result;
  }

  public function setTariff(int $idUser, int $idTariff):bool{
    $sql = "UPDATE `user` SET `tariff` = ? WHERE `id_user` = ?;";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('ii',$idTariff,$idUser);
    $query->execute();
    
    if($query->affected_rows <=0){
      return false;
    }

    return true;

  }

  public function setTariffByLogin(string $login, int $idTariff):bool{
    $sql = "UPDATE `user` SET `tariff` = ? WHERE `login` = ?;";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('is',$idTariff,$login);
    $query->execute();
    
    if($query->affected_rows <=0){
      return false;
    }

    return true;

  }

  public function updateDatePaymentUser(int $idUser, string $date):bool{
    $sql = "UPDATE `user` SET `date_payment` = ? WHERE `id_user` = ?;";
    
    $query = $this->mysql->prepare($sql);
    $query->bind_param('si',$date,$idUser);
    $query->execute();

    if($query->affected_rows <= 0){
      return false;
    }

    return true;

  }

  public function dropDatePayment(int $idUser):bool{

    $sql = "UPDATE `user` SET `date_payment` = null WHERE `id_user` = ?";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('i',$idUser);
    $query->execute();

    if($query->affected_rows<=0){
      return false;
    }

    return true;

  }

  public function paymentTariff(int $idUser, string $nameTariff):bool{

    $priceTariff = $this->getTariff($nameTariff)['price'];
    
    $sql = "UPDATE `user` SET `balance` = `balance` - ? WHERE `id_user` = ? AND `balance` - ? > 0";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('iii', $priceTariff, $idUser, $priceTariff);
    $query->execute();

    if($query->affected_rows <= 0){
      return false;
    }

    return true;
  }

  public function addBalance(int $idUser, int $balance):bool{
    $sql = "UPDATE `user` SET `balance` = `balance` + ? WHERE `id_user` = ? AND `balance` + ? < 30000";

    $query = $this->mysql->prepare($sql);
    $query->bind_param('iii',$balance,$idUser,$balance);
    $query->execute();

    if($query->affected_rows <= 0){
      return false;
    }

    return true;

  }

  public function isExistUser(string $email):bool{

    $sql = "SELECT `email` FROM `user` WHERE `email` = ?;";
    
    $query = $this->mysql->prepare($sql);
    $query->bind_param('s',$email);
    $query->execute();
    $result = $query->get_result();
    
    if($result->num_rows === 0){
      return false;
    }

    return true;

  }


  // Закрытие БД
  public function close():void
  {
    $this->mysql->close();
  }
}
