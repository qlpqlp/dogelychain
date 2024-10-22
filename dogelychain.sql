-- Adminer 4.8.1 MySQL 11.1.6-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `coinbase`;
CREATE TABLE `coinbase` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `txid` text DEFAULT NULL,
  `coinbase` varchar(255) DEFAULT NULL,
  `tag` varchar(255) DEFAULT NULL,
  `sequence` varchar(255) DEFAULT NULL,
  `time` varchar(255) DEFAULT NULL,
  `blocktime` varchar(255) DEFAULT NULL,
  `json` longtext DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


DROP TABLE IF EXISTS `ntx`;
CREATE TABLE `ntx` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `block` bigint(20) DEFAULT NULL,
  `tx` bigint(20) DEFAULT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


DROP TABLE IF EXISTS `ntx1`;
CREATE TABLE `ntx1` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `block` bigint(20) DEFAULT 0,
  `tx` bigint(20) DEFAULT 0,
  `date` datetime DEFAULT NULL,
  `size` bigint(20) DEFAULT 0,
  `to` varchar(255) DEFAULT NULL,
  `fees` varchar(255) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


DROP TABLE IF EXISTS `track`;
CREATE TABLE `track` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `txid` text DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `inout` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `from` varchar(255) DEFAULT NULL,
  `to` varchar(255) DEFAULT NULL,
  `amount` decimal(20,8) DEFAULT 0.00000000,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


DROP TABLE IF EXISTS `vout`;
CREATE TABLE `vout` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `txid` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `value` decimal(20,8) DEFAULT 0.00000000,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


-- 2024-10-22 22:06:13
