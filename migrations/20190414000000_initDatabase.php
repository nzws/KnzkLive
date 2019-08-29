<?php

use Phpmig\Migration\Migration;

class InitDatabase extends Migration {
    /**
     * Do the migration
     */
    public function up() {
        $sql = <<< SQL
--
-- テーブルの構造 `comment`
--

CREATE TABLE `comment` (
                           `id` bigint(255) NOT NULL,
                           `user_id` varchar(255) NOT NULL,
                           `content` text NOT NULL,
                           `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                           `live_id` int(255) NOT NULL,
                           `is_deleted` int(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブルの構造 `comment_delete`
--

CREATE TABLE `comment_delete` (
                                  `id` varchar(255) NOT NULL,
                                  `live_id` int(255) NOT NULL,
                                  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                                  `created_by` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブルの構造 `donate`
--

CREATE TABLE `donate` (
                          `id` bigint(20) NOT NULL,
                          `live_id` int(255) NOT NULL,
                          `user_id` int(255) NOT NULL,
                          `amount` float NOT NULL,
                          `currency` varchar(10) NOT NULL,
                          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                          `ended_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                          `color` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブルの構造 `items`
--

CREATE TABLE `items` (
                         `id` bigint(20) NOT NULL,
                         `type` varchar(10) NOT NULL,
                         `user_id` int(255) NOT NULL,
                         `name` varchar(255) NOT NULL,
                         `point` int(255) NOT NULL DEFAULT 0,
                         `file_name` varchar(255) NOT NULL,
                         `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                         `able_item` int(2) DEFAULT NULL,
                         `able_comment` int(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブルの構造 `live`
--

CREATE TABLE `live` (
                        `id` int(100) NOT NULL,
                        `name` varchar(100) NOT NULL,
                        `description` text DEFAULT NULL,
                        `user_id` int(100) NOT NULL,
                        `slot_id` int(10) NOT NULL,
                        `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                        `ended_at` timestamp NOT NULL DEFAULT current_timestamp(),
                        `is_live` int(2) NOT NULL DEFAULT 1,
                        `ip` varchar(100) NOT NULL,
                        `token` varchar(255) NOT NULL,
                        `privacy_mode` int(5) DEFAULT NULL,
                        `viewers_count` int(100) DEFAULT 0,
                        `viewers_max` int(100) DEFAULT 0,
                        `viewers_max_concurrent` int(100) DEFAULT 0,
                        `comment_count` int(100) NOT NULL DEFAULT 0,
                        `point_count` int(255) NOT NULL DEFAULT 0,
                        `is_started` int(2) NOT NULL DEFAULT 0,
                        `custom_hashtag` varchar(255) DEFAULT NULL,
                        `misc` text DEFAULT '{}'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブルの構造 `live_slot`
--

CREATE TABLE `live_slot` (
                             `id` int(100) NOT NULL,
                             `used` int(10) NOT NULL,
                             `max` int(10) NOT NULL,
                             `server` varchar(50) NOT NULL,
                             `server_ip` varchar(100) DEFAULT NULL,
                             `is_testing` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブルの構造 `mastodon_auth`
--

CREATE TABLE `mastodon_auth` (
                                 `domain` varchar(255) NOT NULL,
                                 `client_id` varchar(255) NOT NULL,
                                 `client_secret` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブルの構造 `point_log`
--

CREATE TABLE `point_log` (
                             `id` bigint(20) NOT NULL,
                             `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                             `user_id` int(255) NOT NULL,
                             `type` varchar(100) NOT NULL,
                             `data` text DEFAULT NULL,
                             `point` int(255) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブルの構造 `point_ticket`
--

CREATE TABLE `point_ticket` (
                                `id` varchar(100) NOT NULL,
                                `point` int(255) NOT NULL,
                                `user_id` int(255) NOT NULL,
                                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                                `comment` text DEFAULT NULL,
                                `used_by` int(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブルの構造 `prop_vote`
--

CREATE TABLE `prop_vote` (
                             `id` int(11) NOT NULL,
                             `live_id` int(11) NOT NULL,
                             `title` varchar(255) NOT NULL,
                             `v1` varchar(255) NOT NULL,
                             `v2` varchar(255) NOT NULL,
                             `v3` varchar(255) DEFAULT NULL,
                             `v4` varchar(255) DEFAULT NULL,
                             `v1_count` int(255) NOT NULL DEFAULT 0,
                             `v2_count` int(255) NOT NULL DEFAULT 0,
                             `v3_count` int(255) NOT NULL DEFAULT 0,
                             `v4_count` int(255) NOT NULL DEFAULT 0,
                             `is_ended` int(5) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブルの構造 `users`
--

CREATE TABLE `users` (
                         `id` int(10) NOT NULL,
                         `name` varchar(100) NOT NULL,
                         `acct` varchar(100) NOT NULL,
                         `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                         `ip` varchar(100) NOT NULL,
                         `broadcaster_id` varchar(255) DEFAULT NULL,
                         `live_current_id` int(100) NOT NULL DEFAULT 0,
                         `misc` text DEFAULT NULL,
                         `twitter_id` varchar(100) DEFAULT NULL,
                         `point_count` int(255) NOT NULL DEFAULT 100,
                         `point_count_today_toot` int(255) NOT NULL DEFAULT 0,
                         `opener_token` varchar(255) DEFAULT NULL,
                         `ngwords` longtext NOT NULL DEFAULT '[]',
                         `donation_desc` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブルの構造 `users_blocking`
--

CREATE TABLE `users_blocking` (
                                  `live_user_id` int(255) NOT NULL,
                                  `target_user_acct` varchar(255) NOT NULL,
                                  `created_by` int(255) NOT NULL,
                                  `misc` text DEFAULT NULL,
                                  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                                  `is_permanent` int(5) NOT NULL DEFAULT 0,
                                  `is_blocking_watch` int(5) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- テーブルの構造 `users_watching`
--

CREATE TABLE `users_watching` (
                                  `ip` varchar(255) NOT NULL,
                                  `watch_id` int(100) NOT NULL,
                                  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
                                  `watching_now` int(5) NOT NULL DEFAULT 1,
                                  `user_id` int(255) DEFAULT NULL,
                                  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comment_delete`
--
ALTER TABLE `comment_delete`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `comment_delete_id_uindex` (`id`),
    ADD KEY `comment_delete_live_id_index` (`live_id`);

--
-- Indexes for table `donate`
--
ALTER TABLE `donate`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `donate_id_uindex` (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `items_id_uindex` (`id`),
    ADD UNIQUE KEY `items_type_user_id_name_uindex` (`type`,`user_id`,`name`),
    ADD KEY `items_user_id_type_index` (`user_id`,`type`);

--
-- Indexes for table `live`
--
ALTER TABLE `live`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `live_slot`
--
ALTER TABLE `live_slot`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mastodon_auth`
--
ALTER TABLE `mastodon_auth`
    ADD PRIMARY KEY (`domain`);

--
-- Indexes for table `point_log`
--
ALTER TABLE `point_log`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `point_log_id_uindex` (`id`),
    ADD KEY `point_log_user_id_index` (`user_id`);

--
-- Indexes for table `point_ticket`
--
ALTER TABLE `point_ticket`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `point_ticket_id_uindex` (`id`);

--
-- Indexes for table `prop_vote`
--
ALTER TABLE `prop_vote`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `prop_vote_id_uindex` (`id`),
    ADD KEY `prop_vote_live_id_index` (`live_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `users_id_uindex` (`id`),
    ADD UNIQUE KEY `users_opener_token_uindex` (`opener_token`),
    ADD UNIQUE KEY `users_broadcaster_id_uindex` (`broadcaster_id`),
    ADD KEY `users_point_count_today_toot_index` (`point_count_today_toot`);

--
-- Indexes for table `users_blocking`
--
ALTER TABLE `users_blocking`
    ADD UNIQUE KEY `users_blocking_live_user_id_target_user_acct_uindex` (`live_user_id`,`target_user_acct`),
    ADD KEY `users_blocking_live_user_id_index` (`live_user_id`);

--
-- Indexes for table `users_watching`
--
ALTER TABLE `users_watching`
    ADD UNIQUE KEY `users_watching_ip_watch_id_uindex` (`ip`,`watch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
    MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `donate`
--
ALTER TABLE `donate`
    MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
    MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `live`
--
ALTER TABLE `live`
    MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `live_slot`
--
ALTER TABLE `live_slot`
    MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `point_log`
--
ALTER TABLE `point_log`
    MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prop_vote`
--
ALTER TABLE `prop_vote`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
    MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
SQL;
        $container = $this->getContainer();
        $container['db']->query($sql);
    }
}
