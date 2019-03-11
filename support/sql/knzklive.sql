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

-- 2018-10-19 added
CREATE TABLE comment
(
  id int(255) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  user_id varchar(255) NOT NULL,
  content text NOT NULL,
  created_at timestamp DEFAULT current_timestamp() NOT NULL,
  live_id int(255) NOT NULL,
  is_deleted int(3)
);

-- 2018-10-20 added
CREATE TABLE mastodon_auth
(
  domain varchar(255) PRIMARY KEY NOT NULL,
  client_id varchar(255) NOT NULL,
  client_secret varchar(255) NOT NULL
);

-- 2018-12-25 added
-- auto-generated definition
create table prop_vote
(
  id       int auto_increment,
  live_id  int                not null,
  title    varchar(255)       not null,
  v1       varchar(255)       not null,
  v2       varchar(255)       not null,
  v3       varchar(255)       null,
  v4       varchar(255)       null,
  v1_count int(255) default 0 not null,
  v2_count int(255) default 0 not null,
  v3_count int(255) default 0 not null,
  v4_count int(255) default 0 not null,
  is_ended int(5) default 0   not null,
  constraint prop_vote_id_uindex
  unique (id)
);

create index prop_vote_live_id_index
  on prop_vote (live_id);

alter table prop_vote
  add primary key (id);

-- 2019-01-12 added
ALTER TABLE users ADD twitter_id varchar(100) NULL;
ALTER TABLE users CHANGE isLive is_broadcaster int(2) NOT NULL DEFAULT 0;
ALTER TABLE users CHANGE liveNow live_current_id int(100) NOT NULL DEFAULT 0;

-- 2019-01-13
ALTER TABLE live ADD comment_count int(100) DEFAULT 0 NOT NULL;
ALTER TABLE live
  MODIFY COLUMN comment_count int(100) NOT NULL DEFAULT 0 AFTER viewers_max_concurrent;

-- 2019-01-16
ALTER TABLE users MODIFY name varchar(255) NOT NULL;

-- 2019/01/19
alter table users
	add point_count int(255) default 0 not null;

create table point_log
(
  id bigint auto_increment primary key,
  created_at timestamp default current_timestamp not null,
  user_id int(255) not null,
  type varchar(100) not null,
  data text null,
  point int(255) default 0 not null
);

create unique index point_log_id_uindex
  on point_log (id);

create index point_log_user_id_index
  on point_log (user_id);

alter table users
	add point_count_today_toot int(255) default 0 not null;

create table point_ticket
(
  id varchar(100) not null,
  point int(255) not null,
  user_id int(255) not null,
  created_at timestamp default current_timestamp not null,
  comment text null,
  used_by int(255) null
);

create unique index point_ticket_id_uindex
  on point_ticket (id);

alter table point_ticket
  add constraint point_ticket_pk
    primary key (id);

create unique index users_id_uindex
  on users (id);

create index users_point_count_today_toot_index
  on users (point_count_today_toot);

-- 2019/01/23
alter table live
  add point_count int(255) default 0 not null after comment_count;

-- 2019/01/25
ALTER TABLE users ADD opener_token varchar(255) NULL;
CREATE UNIQUE INDEX users_opener_token_uindex ON users (opener_token);

-- 2019/01/27
alter table live
  add is_sensitive int(5) default 0 not null;

-- 2019-01-31
ALTER TABLE users_watching ADD watching_now int(5) DEFAULT 1 NOT NULL;
CREATE UNIQUE INDEX users_watching_ip_watch_id_uindex ON users_watching (ip, watch_id);
ALTER TABLE users_watching DROP PRIMARY KEY;

-- 2019-01-31
ALTER TABLE users_watching ADD user_id int(255) NULL;

-- 2019-02-01
ALTER TABLE live ADD misc text DEFAULT '{}' NULL;
ALTER TABLE live DROP is_sensitive;
ALTER TABLE live ALTER COLUMN is_live SET DEFAULT 1;
UPDATE `live` SET misc = '{"is_sensitive":false,"able_item":true,"able_comment":true}' WHERE misc = '{}';

-- 2019-02-02
ALTER TABLE users ADD ngwords longtext DEFAULT '[]' NOT NULL;
CREATE TABLE users_blocking
(
  live_user_id int(255) NOT NULL,
  target_user_id int(255) NOT NULL,
  created_by int(255) NOT NULL,
  misc text,
  created_at timestamp DEFAULT current_timestamp() NOT NULL,
  is_permanent int(5) DEFAULT 0 NOT NULL,
  is_blocking_watch int(5) DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX users_blocking_live_user_id_target_user_id_uindex ON users_blocking (live_user_id, target_user_id);
CREATE INDEX users_blocking_live_user_id_index ON users_blocking (live_user_id);

-- 2019-02-06
create table comment_delete
(
  id varchar(255) not null,
  live_id int(255) not null,
  created_at timestamp default current_timestamp() not null,
  created_by int(255) not null
);

create unique index comment_delete_id_uindex
  on comment_delete (id);

create index comment_delete_live_id_index
  on comment_delete (live_id);

alter table comment_delete
  add constraint comment_delete_pk
    primary key (id);

-- 2019-02-08
alter table users change is_broadcaster broadcaster_id varchar(255) null;

UPDATE `users` SET broadcaster_id = acct WHERE broadcaster_id = '1';
UPDATE `users` SET broadcaster_id = null WHERE broadcaster_id = '0';
create unique index users_broadcaster_id_uindex
  on users (broadcaster_id);

-- 2019/02/15
create table donate
(
  id bigint auto_increment,
  live_id int(255) not null,
  user_id int(255) not null,
  amount int(255) not null,
  currency varchar(10) not null,
  created_at timestamp default current_timestamp() not null,
  ended_at timestamp not null,
  color varchar(100) not null,
  primary key (id)
);

create unique index donate_id_uindex
  on donate (id);

-- 2019/02/16
alter table donate modify amount float not null;

-- 2019/02/19
alter table users
  add donation_desc text null;

-- 2019/02/25
alter table users_blocking add target_user_acct varchar(255) not null after live_user_id;
alter table users_blocking add constraint users_blocking_live_user_id_target_user_acct_uindex unique (live_user_id, target_user_acct);

drop index users_blocking_live_user_id_target_user_id_uindex on users_blocking;
alter table users_blocking drop column target_user_id;

-- 2019/03/09
alter table users_watching
	add created_at timestamp default current_timestamp not null;

-- 2019/03/11
create table items
(
	id bigint auto_increment,
	type varchar(10) not null,
	user_id int(255) not null,
	name varchar(255) not null,
	point int(255) default 0 not null,
	file_name varchar(255) not null,
	created_at timestamp default current_timestamp() not null,
	able_item int(2) null,
	able_comment int(2) null,
	primary key (id)
);

create unique index items_id_uindex
	on items (id);

create index items_user_id_type_index
	on items (user_id, type);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
