-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Июн 14 2026 г., 07:35
-- Версия сервера: 5.6.51-log
-- Версия PHP: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `aerofiles_db`
--

DROP DATABASE IF EXISTS `aerofiles_db`;
CREATE DATABASE IF NOT EXISTS `aerofiles_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `aerofiles_db`;

-- --------------------------------------------------------

--
-- Структура таблицы `selectedFolder`
--

DROP TABLE IF EXISTS `selectedFolder`;
CREATE TABLE IF NOT EXISTS `selectedFolder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `folder` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `tariff`
--

DROP TABLE IF EXISTS `tariff`;
CREATE TABLE IF NOT EXISTS `tariff` (
  `id_tariff` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` smallint(5) NOT NULL,
  `maxSizeStorageText` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `maxSizeStorage` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_tariff`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `tariff`
--

INSERT INTO `tariff` (`id_tariff`, `name`, `price`, `maxSizeStorageText`, `maxSizeStorage`) VALUES
(1, 'test', 0, '5 Мб', 5242880),
(2, 'free', 0, '10 Гб', 10737418240),
(3, 'standart', 350, '100 Гб', 107374182400),
(4, 'premium', 750, '500 Гб', 536870912000),
(5, 'vip', 1250, '1 Тб', 1099511627776);

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isAdmin` tinyint(1) NOT NULL,
  `sizeStorage` bigint(20) NOT NULL DEFAULT '0',
  `isActive` tinyint(1) NOT NULL DEFAULT '0',
  `tariff` int(11) NOT NULL DEFAULT '2',
  `date_payment` datetime DEFAULT CURRENT_TIMESTAMP,
  `balance` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `login` (`login`),
  UNIQUE KEY `email` (`email`),
  KEY `tariff` (`tariff`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `user`
--

INSERT INTO `user` (`id_user`, `login`, `password`, `email`, `isAdmin`, `sizeStorage`, `isActive`, `tariff`, `date_payment`, `balance`) VALUES
(1, 'Maxim', '$2y$10$sw5FpEwFbwR5hQMedSrzK..IN.3JUvs6x937nDAVaCAXTyM6EfKdK', 'maxim0004k@gmail.com', 0, 0, 1, 5, '2026-06-04 13:22:15', 870),
(2, 'Admin1', '$2y$10$kXQT9q1LNj.8PyOhnI8PEetZUM/yDPAU/0rAfel8splTzYubs5pD2', 'Admin@Panel1.com', 1, 0, 1, 2, '2026-05-27 01:28:16', 420);

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `selectedFolder`
--
ALTER TABLE `selectedFolder`
  ADD CONSTRAINT `selectedfolder_ibfk_1` FOREIGN KEY (`user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`tariff`) REFERENCES `tariff` (`id_tariff`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
