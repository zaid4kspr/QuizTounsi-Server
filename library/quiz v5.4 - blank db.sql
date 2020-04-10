-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 31, 2019 at 06:06 AM
-- Server version: 5.6.41-84.1-log
-- PHP Version: 7.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wrteabz6_quiz`
--

-- --------------------------------------------------------

--
-- Table structure for table `authenticate`
--

CREATE TABLE `authenticate` (
  `auth_username` varchar(12) NOT NULL,
  `auth_pass` varchar(50) NOT NULL,
  `role` varchar(32) NOT NULL,
  `app_passcode` varchar(16) NOT NULL,
  `android_key` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `authenticate`
--

INSERT INTO `authenticate` (`auth_username`, `auth_pass`, `role`, `app_passcode`, `android_key`, `created`) VALUES
('admin', '0192023a7bbd73250516f069df18b500', 'admin', '', '', '2019-09-16 12:08:07');

-- --------------------------------------------------------

--
-- Table structure for table `battle_questions`
--

CREATE TABLE `battle_questions` (
  `id` int(11) NOT NULL,
  `match_id` varchar(128) NOT NULL,
  `questions` text CHARACTER SET utf8 NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `battle_statistics`
--

CREATE TABLE `battle_statistics` (
  `id` int(11) NOT NULL,
  `user_id1` int(11) NOT NULL,
  `user_id2` int(11) NOT NULL,
  `is_drawn` tinyint(4) NOT NULL,
  `winner_id` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `language_id` tinyint(4) NOT NULL DEFAULT '0',
  `category_name` varchar(250) CHARACTER SET utf8 NOT NULL,
  `image` longtext CHARACTER SET utf8,
  `row_order` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int(11) NOT NULL,
  `language` varchar(64) CHARACTER SET utf8 NOT NULL,
  `status` tinyint(4) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `monthly_leaderboard`
--

CREATE TABLE `monthly_leaderboard` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(128) NOT NULL,
  `message` varchar(512) NOT NULL,
  `users` varchar(8) NOT NULL DEFAULT 'all',
  `type` varchar(12) NOT NULL,
  `type_id` int(11) NOT NULL,
  `image` varchar(128) NOT NULL,
  `date_sent` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE `question` (
  `id` int(11) NOT NULL,
  `category` int(11) NOT NULL,
  `subcategory` int(11) NOT NULL,
  `language_id` tinyint(4) NOT NULL DEFAULT '0',
  `image` varchar(512) CHARACTER SET utf8 NOT NULL,
  `question` text CHARACTER SET utf8 NOT NULL,
  `optiona` text CHARACTER SET utf8 NOT NULL,
  `optionb` text CHARACTER SET utf8 NOT NULL,
  `optionc` text CHARACTER SET utf8 NOT NULL,
  `optiond` text CHARACTER SET utf8 NOT NULL,
  `optione` text CHARACTER SET utf8,
  `answer` text CHARACTER SET utf8 NOT NULL,
  `level` int(11) NOT NULL,
  `note` text CHARACTER SET utf8 NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `question_reports`
--

CREATE TABLE `question_reports` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `message` varchar(512) CHARACTER SET utf8 NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `type` varchar(32) NOT NULL,
  `message` text CHARACTER SET utf8 NOT NULL,
  `status` int(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `type`, `message`, `status`) VALUES
(1, 'privacy_policy', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce aliquet vulputate tincidunt. Etiam pharetra auctor massa in aliquet. Curabitur a elit ut mauris ullamcorper pulvinar. Phasellus maximus tellus dui, id iaculis lectus fermentum nec. Aliquam odio erat, porttitor vel luctus id, sollicitudin non tortor. Vestibulum neque est, semper vel dui eu, varius aliquam ante. Donec mollis magna sed metus vestibulum consequat. Ut aliquam vulputate ligula, non cursus nibh gravida vitae. Phasellus tellus tellus, accumsan eget tortor laoreet, molestie mollis nisl. Donec molestie semper nibh in efficitur. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Suspendisse dignissim, ex ac iaculis pulvinar, sem nisi scelerisque justo, pulvinar pellentesque ex mi bibendum urna. Quisque ac commodo justo. Integer ut dignissim lectus. Donec a elementum dolor. Vivamus eu nunc vitae mi iaculis imperdiet.</p>\r\n<p>Ut ullamcorper risus leo, sit amet dictum magna consequat id. Cras eros leo, ullamcorper a vehicula sed, suscipit nec mi. Donec facilisis, urna eu placerat condimentum, nisi quam tincidunt ex, ac auctor nisi metus vel tellus. Curabitur aliquam felis ut ex facilisis eleifend. Mauris dapibus consectetur eros, id venenatis risus pretium eget. Proin sit amet egestas odio. Vivamus interdum, enim nec egestas vulputate, purus dui convallis velit, eu elementum massa nibh at nulla. Morbi ullamcorper accumsan ipsum, id pulvinar purus ultrices vel. In vehicula ultrices diam sit amet dapibus. Integer arcu diam, luctus nec urna eu, iaculis tempor arcu. Sed sit amet pulvinar arcu, eget consequat ante. Curabitur nunc ante, venenatis at tellus eu, euismod vulputate lectus. Vivamus finibus arcu nulla.</p>\r\n<p>Proin mollis ullamcorper nibh et viverra. Nullam iaculis leo et erat commodo pretium. Phasellus ut sapien vel dui mattis vulputate. Duis non volutpat elit. Nulla vitae mi metus. Donec euismod vulputate risus, ac maximus erat maximus quis. Nullam molestie eget orci ac accumsan. Proin tortor lectus, ultrices id tortor vel, mollis faucibus enim. Proin augue ante, mollis id libero eget, ultrices auctor augue. Nam lacinia dapibus dui, nec bibendum lacus pharetra sit amet. Maecenas ut diam urna. Sed consectetur ipsum nec tempus facilisis. Proin gravida est lectus, vel sagittis lorem porta non. Maecenas id tempus ex. Integer ullamcorper, lacus sed interdum imperdiet, purus tellus dapibus ipsum, sed auctor dui dolor at lorem.</p>\r\n<p>Sed non placerat erat. Nullam diam purus, cursus vitae sapien et, ultrices molestie eros. Aliquam eleifend sem libero, et facilisis tellus sagittis id. Aliquam faucibus, enim ut fermentum aliquam, arcu nunc mollis justo, in pulvinar ante nisl nec ex. Morbi vel eros non tellus tincidunt sagittis. Sed massa felis, finibus non placerat a, pharetra sit amet massa. Proin ornare magna vitae risus accumsan, vel sagittis nisl finibus. Sed sit amet finibus magna. Proin fringilla risus sit amet velit auctor, sit amet faucibus tellus scelerisque.</p>', 1),
(4, 'about_us', '<p>Welcome to <strong>Quiz Online</strong></p>\r\n<p>Best Android app for online quiz is here. We guarantee you the best quizing experience for your dedicated users.</p>\r\n<p>&nbsp;</p>\r\n<p>Made with &lt;3 by <a href=\"https://wrteam.in\"><strong>WRTeam</strong></a></p>', 1),
(2, 'update_terms', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce aliquet vulputate tincidunt. Etiam pharetra auctor massa in aliquet. Curabitur a elit ut mauris ullamcorper pulvinar. Phasellus maximus tellus dui, id iaculis lectus fermentum nec. Aliquam odio erat, porttitor vel luctus id, sollicitudin non tortor. Vestibulum neque est, semper vel dui eu, varius aliquam ante. Donec mollis magna sed metus vestibulum consequat. Ut aliquam vulputate ligula, non cursus nibh gravida vitae. Phasellus tellus tellus, accumsan eget tortor laoreet, molestie mollis nisl. Donec molestie semper nibh in efficitur. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Suspendisse dignissim, ex ac iaculis pulvinar, sem nisi scelerisque justo, pulvinar pellentesque ex mi bibendum urna. Quisque ac commodo justo. Integer ut dignissim lectus. Donec a elementum dolor. Vivamus eu nunc vitae mi iaculis imperdiet.</p>\r\n<p>Ut ullamcorper risus leo, sit amet dictum magna consequat id. Cras eros leo, ullamcorper a vehicula sed, suscipit nec mi. Donec facilisis, urna eu placerat condimentum, nisi quam tincidunt ex, ac auctor nisi metus vel tellus. Curabitur aliquam felis ut ex facilisis eleifend. Mauris dapibus consectetur eros, id venenatis risus pretium eget. Proin sit amet egestas odio. Vivamus interdum, enim nec egestas vulputate, purus dui convallis velit, eu elementum massa nibh at nulla. Morbi ullamcorper accumsan ipsum, id pulvinar purus ultrices vel. In vehicula ultrices diam sit amet dapibus. Integer arcu diam, luctus nec urna eu, iaculis tempor arcu. Sed sit amet pulvinar arcu, eget consequat ante. Curabitur nunc ante, venenatis at tellus eu, euismod vulputate lectus. Vivamus finibus arcu nulla.</p>\r\n<p>Proin mollis ullamcorper nibh et viverra. Nullam iaculis leo et erat commodo pretium. Phasellus ut sapien vel dui mattis vulputate. Duis non volutpat elit. Nulla vitae mi metus. Donec euismod vulputate risus, ac maximus erat maximus quis. Nullam molestie eget orci ac accumsan. Proin tortor lectus, ultrices id tortor vel, mollis faucibus enim. Proin augue ante, mollis id libero eget, ultrices auctor augue. Nam lacinia dapibus dui, nec bibendum lacus pharetra sit amet. Maecenas ut diam urna. Sed consectetur ipsum nec tempus facilisis. Proin gravida est lectus, vel sagittis lorem porta non. Maecenas id tempus ex. Integer ullamcorper, lacus sed interdum imperdiet, purus tellus dapibus ipsum, sed auctor dui dolor at lorem.</p>\r\n<p>Sed non placerat erat. Nullam diam purus, cursus vitae sapien et, ultrices molestie eros. Aliquam eleifend sem libero, et facilisis tellus sagittis id. Aliquam faucibus, enim ut fermentum aliquam, arcu nunc mollis justo, in pulvinar ante nisl nec ex. Morbi vel eros non tellus tincidunt sagittis. Sed massa felis, finibus non placerat a, pharetra sit amet massa. Proin ornare magna vitae risus accumsan, vel sagittis nisl finibus. Sed sit amet finibus magna. Proin fringilla risus sit amet velit auctor, sit amet faucibus tellus scelerisque.</p>', 1),
(3, 'system_configurations', '{\"system_configurations\":\"1\",\"system_configurations_id\":\"3\",\"system_timezone_gmt\":\"+05:30\",\"system_timezone\":\"Asia/Kolkata\",\"app_link\":\"https://play.google.com/store/apps/details?id=com.wrteam.quiz\",\"more_apps\":\"https://play.google.com/store/apps/developer?id=\",\"language_mode\":\"1\",\"option_e_mode\":\"0\",\"app_version\":\"5.4\",\"shareapp_text\":\"Hello, This is a simple share text. User will be happy to read this.\"}', 1);

-- --------------------------------------------------------

--
-- Table structure for table `subcategory`
--

CREATE TABLE `subcategory` (
  `id` int(11) NOT NULL,
  `maincat_id` int(11) NOT NULL,
  `language_id` tinyint(4) NOT NULL DEFAULT '0',
  `subcategory_name` varchar(250) CHARACTER SET utf8 NOT NULL,
  `image` text CHARACTER SET utf8,
  `status` int(11) NOT NULL DEFAULT '1',
  `row_order` varchar(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_devices`
--

CREATE TABLE `tbl_devices` (
  `id` int(10) NOT NULL,
  `token` varchar(1024) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fcm_key`
--

CREATE TABLE `tbl_fcm_key` (
  `id` int(11) NOT NULL,
  `fcm_key` text CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_image`
--

CREATE TABLE `tbl_image` (
  `id` int(11) NOT NULL,
  `image` text CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mobile` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `profile` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `fcm_id` varchar(1024) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coins` int(11) NOT NULL DEFAULT '0',
  `refer_code` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `friends_code` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip_address` char(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `status` int(10) UNSIGNED DEFAULT '0',
  `date_registered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_statistics`
--

CREATE TABLE `users_statistics` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `questions_answered` int(11) NOT NULL,
  `correct_answers` int(11) NOT NULL,
  `strong_category` int(11) NOT NULL,
  `ratio1` double NOT NULL,
  `weak_category` int(11) NOT NULL,
  `ratio2` double NOT NULL,
  `best_position` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `authenticate`
--
ALTER TABLE `authenticate`
  ADD PRIMARY KEY (`auth_username`),
  ADD UNIQUE KEY `auth_username` (`auth_username`);

--
-- Indexes for table `battle_questions`
--
ALTER TABLE `battle_questions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `match_id` (`match_id`);

--
-- Indexes for table `battle_statistics`
--
ALTER TABLE `battle_statistics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `monthly_leaderboard`
--
ALTER TABLE `monthly_leaderboard`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`date_created`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`),
  ADD KEY `subcategory` (`subcategory`) USING BTREE;

--
-- Indexes for table `question_reports`
--
ALTER TABLE `question_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subcategory`
--
ALTER TABLE `subcategory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_devices`
--
ALTER TABLE `tbl_devices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_fcm_key`
--
ALTER TABLE `tbl_fcm_key`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_image`
--
ALTER TABLE `tbl_image`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`,`mobile`);

--
-- Indexes for table `users_statistics`
--
ALTER TABLE `users_statistics`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `battle_questions`
--
ALTER TABLE `battle_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `battle_statistics`
--
ALTER TABLE `battle_statistics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `monthly_leaderboard`
--
ALTER TABLE `monthly_leaderboard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question`
--
ALTER TABLE `question`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_reports`
--
ALTER TABLE `question_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subcategory`
--
ALTER TABLE `subcategory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_devices`
--
ALTER TABLE `tbl_devices`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_fcm_key`
--
ALTER TABLE `tbl_fcm_key`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_image`
--
ALTER TABLE `tbl_image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_statistics`
--
ALTER TABLE `users_statistics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
