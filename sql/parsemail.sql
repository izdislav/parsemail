-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2015 at 12:00 AM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `parsemail`
--
CREATE DATABASE IF NOT EXISTS `parsemail` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `parsemail`;

-- --------------------------------------------------------

--
-- Table structure for table `attachment`
--

DROP TABLE IF EXISTS `attachment`;
CREATE TABLE `attachment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `emailmessage_id` bigint(20) unsigned NOT NULL,
  `name` varchar(30) NOT NULL,
  `type` varchar(30) NOT NULL,
  `size` int(11) NOT NULL,
  `content` mediumblob NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `emailmessage`
--

DROP TABLE IF EXISTS `emailmessage`;
CREATE TABLE `emailmessage` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `body` longtext,
  `body_html` longtext NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `header`
--

DROP TABLE IF EXISTS `header`;
CREATE TABLE `header` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `emailmessage_id` bigint(20) unsigned NOT NULL,
  `headername_id` bigint(20) unsigned NOT NULL,
  `header_value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `emailmessage_id` (`emailmessage_id`),
  KEY `headername_id` (`headername_id`),
  KEY `emailmessage_id_headername_id` (`emailmessage_id`,`headername_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `headername`
--

DROP TABLE IF EXISTS `headername`;
CREATE TABLE `headername` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `header_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `header_name` (`header_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_email` (`user_email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `useremail`
--

DROP TABLE IF EXISTS `useremail`;
CREATE TABLE `useremail` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `emailmessage_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
