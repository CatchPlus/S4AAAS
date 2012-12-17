-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: thdev2.target-imedia.nl
-- Generation Time: Sep 14, 2012 at 02:44 PM
-- Server version: 5.1.61
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `s4aaas`
--

-- --------------------------------------------------------

--
-- Table structure for table `AUTHTOKENS`
--

CREATE TABLE IF NOT EXISTS `AUTHTOKENS` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TOKEN` varchar(32) DEFAULT NULL,
  `USER_ID` int(11) DEFAULT NULL,
  `VALID_UNTIL` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=99 ;

-- --------------------------------------------------------

--
-- Table structure for table `BOOKS`
--

CREATE TABLE IF NOT EXISTS `BOOKS` (
  `ID` int(11) NOT NULL DEFAULT '0',
  `BOOK_DIR` varchar(8) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL COMMENT 'Directory name in s4aaas',
  `COLLECTION_ID` int(11) DEFAULT NULL,
  `MONK_ID` varchar(255) DEFAULT NULL,
  `MONK_DIR` varchar(255) DEFAULT NULL,
  `SHORT_NAME` varchar(255) DEFAULT NULL,
  `LONG_NAME` varchar(255) DEFAULT NULL,
  `HANDLE_URL` varchar(255) DEFAULT NULL,
  `SHEAR` varchar(255) DEFAULT NULL,
  `NAVIS_ID` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `BOOK_DIR` (`BOOK_DIR`),
  UNIQUE KEY `ID` (`ID`),
  UNIQUE KEY `COLLECTION_ID_2` (`COLLECTION_ID`,`MONK_DIR`),
  UNIQUE KEY `COLLECTION_ID_3` (`COLLECTION_ID`,`SHORT_NAME`),
  UNIQUE KEY `COLLECTION_ID_4` (`COLLECTION_ID`,`LONG_NAME`),
  KEY `MONK_ID` (`MONK_ID`),
  KEY `MONK_DIR` (`MONK_DIR`),
  KEY `COLLECTION_ID` (`COLLECTION_ID`),
  KEY `NAVIS_ID` (`NAVIS_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `COLLECTIONS`
--

CREATE TABLE IF NOT EXISTS `COLLECTIONS` (
  `ID` int(11) NOT NULL DEFAULT '0',
  `INSTITUTION_ID` int(11) DEFAULT NULL,
  `MONK_ID` varchar(255) DEFAULT NULL,
  `SHORT_NAME` varchar(255) DEFAULT NULL,
  `LONG_NAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `INSTITUTION_ID` (`INSTITUTION_ID`,`MONK_ID`),
  UNIQUE KEY `INSTITUTION_ID_2` (`INSTITUTION_ID`,`SHORT_NAME`),
  UNIQUE KEY `INSTITUTION_ID_3` (`INSTITUTION_ID`,`LONG_NAME`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `CUTOUTLINES`
--

CREATE TABLE IF NOT EXISTS `CUTOUTLINES` (
  `CUTOUTTOKEN_ID` int(11) NOT NULL,
  `LINE_ID` int(11) NOT NULL,
  `Y1` int(11) NOT NULL,
  `Y2` int(11) NOT NULL,
  PRIMARY KEY (`CUTOUTTOKEN_ID`,`LINE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `CUTOUTTOKENS`
--

CREATE TABLE IF NOT EXISTS `CUTOUTTOKENS` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IPADDRESS` varchar(40) NOT NULL,
  `TOKEN` varchar(32) NOT NULL,
  `ORIG_WIDTH` int(11) NOT NULL,
  `ANGLE` int(11) NOT NULL,
  `X1` int(11) NOT NULL,
  `Y1` int(11) NOT NULL,
  `X2` int(11) NOT NULL,
  `Y2` int(11) NOT NULL,
  `VALID_UNTIL` datetime NOT NULL,
  `STATUS` varchar(16) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `TOKEN` (`TOKEN`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `IMAGELOOKUP`
--

CREATE TABLE IF NOT EXISTS `IMAGELOOKUP` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IMAGE_ID` varchar(16) NOT NULL COMMENT 'Obfuscated image id',
  `TYPE` varchar(8) NOT NULL COMMENT 'PAGE or LINE',
  `OBJECT_ID` int(11) NOT NULL COMMENT 'id of the page/line',
  `VALID_UNTIL` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `OBJECT_ID` (`OBJECT_ID`,`VALID_UNTIL`),
  KEY `OBJECT_ID_2` (`OBJECT_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8897 ;

-- --------------------------------------------------------

--
-- Table structure for table `INSTITUTIONS`
--

CREATE TABLE IF NOT EXISTS `INSTITUTIONS` (
  `ID` int(11) NOT NULL DEFAULT '0',
  `MONK_ID` varchar(255) DEFAULT NULL,
  `SHORT_NAME` varchar(255) DEFAULT NULL,
  `LONG_NAME` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`),
  UNIQUE KEY `MONK_ID` (`MONK_ID`),
  UNIQUE KEY `SHORT_NAME` (`SHORT_NAME`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `LABELS`
--

CREATE TABLE IF NOT EXISTS `LABELS` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `LINE_ID` int(11) DEFAULT NULL,
  `BYUSER_ID` int(11) DEFAULT NULL,
  `TIMESTAMP` datetime DEFAULT NULL,
  `X` int(11) DEFAULT NULL,
  `Y` int(11) DEFAULT NULL,
  `WIDTH` int(11) DEFAULT NULL,
  `HEIGHT` int(11) DEFAULT NULL,
  `ROI` varchar(255) NOT NULL,
  `LABEL_TEXT` varchar(255) DEFAULT NULL,
  `STATUS` varchar(255) NOT NULL DEFAULT 'OPEN',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`),
  KEY `ID_2` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=32 ;

-- --------------------------------------------------------

--
-- Table structure for table `LINES`
--

CREATE TABLE IF NOT EXISTS `LINES` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PAGE_ID` int(11) DEFAULT NULL,
  `LINE_NO` int(11) DEFAULT NULL,
  `Y_TOP` int(11) DEFAULT NULL,
  `Y_BOT` int(11) DEFAULT NULL,
  `TRANSCRIPT` varchar(255) DEFAULT NULL,
  `IMAGE_RENDERED` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `PAGE_ID` (`PAGE_ID`),
  KEY `LINE_NO` (`LINE_NO`),
  KEY `IMAGE_RENDERED` (`IMAGE_RENDERED`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=569049 ;

-- --------------------------------------------------------

--
-- Table structure for table `PAGELOCKS`
--

CREATE TABLE IF NOT EXISTS `PAGELOCKS` (
  `PAGE_ID` int(11) NOT NULL,
  `USER_ID` int(11) NOT NULL,
  `LOCKED_UNTIL` datetime DEFAULT NULL,
  PRIMARY KEY (`PAGE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `PAGES`
--

CREATE TABLE IF NOT EXISTS `PAGES` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `BOOK_ID` int(11) DEFAULT NULL,
  `NAVIS_ID` varchar(255) DEFAULT NULL,
  `PAGE_NO` int(11) NOT NULL,
  `ORIG_WIDTH` int(11) NOT NULL,
  `TRANSCRIPT` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `BOOK_ID` (`BOOK_ID`,`PAGE_NO`),
  UNIQUE KEY `NAVIS_ID` (`NAVIS_ID`),
  KEY `PAGE_NO` (`PAGE_NO`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15985 ;

-- --------------------------------------------------------

--
-- Table structure for table `USERBOOK`
--

CREATE TABLE IF NOT EXISTS `USERBOOK` (
  `USER_ID` int(11) DEFAULT NULL,
  `BOOK_ID` int(11) DEFAULT NULL,
  `PERMISSIONS` int(11) DEFAULT NULL,
  `PAGE_FROM` int(11) DEFAULT NULL,
  `PAGE_TO` int(11) DEFAULT NULL,
  `BYUSER_ID` int(11) DEFAULT NULL,
  `TIMESTAMP` datetime DEFAULT NULL,
  `DELETED` varchar(3) DEFAULT NULL,
  UNIQUE KEY `USER_ID` (`USER_ID`,`BOOK_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `USERCOL`
--

CREATE TABLE IF NOT EXISTS `USERCOL` (
  `USER_ID` int(11) DEFAULT NULL,
  `COLLECTION_ID` int(11) DEFAULT NULL,
  `PERMISSIONS` int(11) DEFAULT NULL,
  `BYUSER_ID` int(11) DEFAULT NULL,
  `TIMESTAMP` datetime DEFAULT NULL,
  `DELETED` varchar(3) DEFAULT NULL,
  UNIQUE KEY `USER_ID` (`USER_ID`),
  UNIQUE KEY `COLLECTION_ID` (`COLLECTION_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `USERINST`
--

CREATE TABLE IF NOT EXISTS `USERINST` (
  `USER_ID` int(11) DEFAULT NULL,
  `INSTITUTION_ID` int(11) DEFAULT NULL,
  `PERMISSIONS` int(11) DEFAULT NULL,
  `BYUSER_ID` int(11) DEFAULT NULL,
  `TIMESTAMP` datetime DEFAULT NULL,
  `DELETED` varchar(3) DEFAULT NULL,
  UNIQUE KEY `USER_ID` (`USER_ID`,`INSTITUTION_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `USERS`
--

CREATE TABLE IF NOT EXISTS `USERS` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MONK_ID` varchar(255) DEFAULT NULL,
  `PASSWORD` varchar(255) DEFAULT NULL,
  `PERMISSIONS` int(11) DEFAULT NULL,
  `BYUSER_ID` int(11) DEFAULT NULL,
  `TIMESTAMP` datetime DEFAULT NULL,
  `DISABLED` varchar(3) DEFAULT NULL,
  `DELETED` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ID` (`ID`),
  UNIQUE KEY `MONK_ID` (`MONK_ID`),
  KEY `ID_2` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
