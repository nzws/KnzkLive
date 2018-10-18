-- phpMyAdmin SQL Dump
-- version 4.8.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 2018 年 8 月 08 日 11:23
-- サーバのバージョン： 10.2.16-MariaDB
-- PHP Version: 7.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `knzklive`
--
CREATE DATABASE IF NOT EXISTS `knzklive` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `knzklive`;

create table live
(
  id                     int(100) auto_increment
    primary key,
  name                   varchar(100)       not null,
  description            text               null,
  user_id                int(100)           not null,
  slot_id                int(10)            not null,
  created_at             timestamp default current_timestamp()  not null,
  ended_at             timestamp default current_timestamp()  not null,
  is_live                int(2)             not null,
  ip                     varchar(100)       not null,
  token                  varchar(255)       not null,
  privacy_mode           int(5)             null,
  viewers_count          int(100) default 0 null,
  viewers_max            int(100) default 0 null,
  viewers_max_concurrent int(100) default 0 null
);

create table live_slot
(
  id         int(100) auto_increment
    primary key,
  used       int(10)      not null,
  max        int(10)      not null,
  server     varchar(50)  not null,
  server_ip  varchar(100) null,
  is_testing int(2)       not null
);

create table users
(
  id         int(10) auto_increment
    primary key,
  name       varchar(100)                            not null,
  acct       varchar(100)                            not null,
  created_at timestamp default current_timestamp() not null,
  ip         varchar(100)                            not null,
  isLive     int(2)                                  not null,
  liveNow    int(100)                                not null,
  misc       text                                    null
);

-- 2018-10-12 added
ALTER TABLE live ADD is_started int(2) DEFAULT 0 NOT NULL;

-- 2018-10-13 added
CREATE TABLE users_watching
(
    ip varchar(255) NOT NULL primary key,
    watch_id int(100) NOT NULL,
    updated_at timestamp DEFAULT current_timestamp() NOT NULL
);

-- 2018-10-18 added
ALTER TABLE live ADD custom_hashtag varchar(255) NULL;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;