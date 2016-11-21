# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.16)
# Database: matrix
# Generation Time: 2016-11-21 15:01:20 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table Cases
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Cases`;

CREATE TABLE `Cases` (
  `CaseID` int(11) NOT NULL AUTO_INCREMENT,
  `CaseName` varchar(255) DEFAULT NULL,
  `Caption` varchar(255) DEFAULT NULL,
  `JudgeID` int(11) DEFAULT NULL,
  `Created` datetime DEFAULT NULL,
  `Modified` datetime DEFAULT NULL,
  PRIMARY KEY (`CaseID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table Judges
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Judges`;

CREATE TABLE `Judges` (
  `JudgeID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) DEFAULT NULL,
  `Created` datetime DEFAULT NULL,
  `Modified` datetime DEFAULT NULL,
  PRIMARY KEY (`JudgeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table People
# ------------------------------------------------------------

DROP TABLE IF EXISTS `People`;

CREATE TABLE `People` (
  `PersonID` int(11) NOT NULL AUTO_INCREMENT,
  `CaseID` int(11) DEFAULT NULL,
  `LastName` varchar(63) DEFAULT NULL,
  `FirstName` varchar(63) DEFAULT NULL,
  `DOB` datetime DEFAULT NULL,
  `Notes` text,
  `Created` datetime DEFAULT NULL,
  `Modified` datetime DEFAULT NULL,
  PRIMARY KEY (`PersonID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table PeopleContacts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `PeopleContacts`;

CREATE TABLE `PeopleContacts` (
  `PeopleContactID` int(11) NOT NULL AUTO_INCREMENT,
  `PersonID` int(11) DEFAULT NULL,
  `CaseID` int(11) DEFAULT NULL,
  `Address1` varchar(255) DEFAULT NULL,
  `Address2` varchar(255) DEFAULT NULL,
  `City` varchar(255) DEFAULT NULL,
  `State` varchar(7) DEFAULT NULL,
  `Zip` varchar(15) DEFAULT NULL,
  `Country` varchar(63) DEFAULT NULL,
  `Phone` varchar(15) DEFAULT NULL,
  `Email` varchar(127) DEFAULT NULL,
  `Fax` varchar(15) DEFAULT NULL,
  `Facebook` varchar(127) DEFAULT NULL,
  `Twitter` varchar(127) DEFAULT NULL,
  `Instagram` varchar(127) DEFAULT NULL,
  `Current` bit(1) DEFAULT b'0',
  `Main` bit(1) DEFAULT b'0',
  `Notes` text,
  `Created` datetime DEFAULT NULL,
  `Modified` datetime DEFAULT NULL,
  PRIMARY KEY (`PeopleContactID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table PeoplePhones
# ------------------------------------------------------------

DROP TABLE IF EXISTS `PeoplePhones`;

CREATE TABLE `PeoplePhones` (
  `PeoplePhoneID` int(11) NOT NULL AUTO_INCREMENT,
  `PersonID` int(11) DEFAULT NULL,
  `CaseID` int(11) DEFAULT NULL,
  `StartUse` datetime DEFAULT NULL,
  `EndUse` datetime DEFAULT NULL,
  `Created` datetime DEFAULT NULL,
  `Modified` datetime DEFAULT NULL,
  PRIMARY KEY (`PeoplePhoneID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table PeopleTypes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `PeopleTypes`;

CREATE TABLE `PeopleTypes` (
  `PersonTypeID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) DEFAULT NULL,
  `Created` datetime DEFAULT NULL,
  `Modified` datetime DEFAULT NULL,
  PRIMARY KEY (`PersonTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table PersonPersonType
# ------------------------------------------------------------

DROP TABLE IF EXISTS `PersonPersonType`;

CREATE TABLE `PersonPersonType` (
  `PersonPersonTypeID` int(11) NOT NULL AUTO_INCREMENT,
  `PersonID` int(11) DEFAULT NULL,
  `PersonTypeID` int(11) DEFAULT NULL,
  `CaseID` int(11) DEFAULT NULL,
  `Created` datetime DEFAULT NULL,
  `Modified` datetime DEFAULT NULL,
  PRIMARY KEY (`PersonPersonTypeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table PhoneCalls
# ------------------------------------------------------------

DROP TABLE IF EXISTS `PhoneCalls`;

CREATE TABLE `PhoneCalls` (
  `PhoneCallID` int(11) NOT NULL AUTO_INCREMENT,
  `CaseID` int(11) DEFAULT NULL,
  `CallToPhoneID` int(11) DEFAULT NULL,
  `CallFromPhoneID` int(11) DEFAULT NULL,
  `DialedDigits` varchar(255) DEFAULT NULL,
  `MRNum` varchar(255) DEFAULT NULL,
  `StartDate` datetime DEFAULT NULL,
  `EndDate` datetime DEFAULT NULL,
  `Duration` varchar(255) DEFAULT NULL,
  `NEID` int(11) DEFAULT NULL,
  `REPOLL` int(11) DEFAULT NULL,
  `FirstCell` int(11) DEFAULT NULL,
  `LastCell` int(11) DEFAULT NULL,
  `FirstLatitude` float(10,6) DEFAULT NULL,
  `FirstLongitude` float(10,6) DEFAULT NULL,
  `LastLatitude` float(10,6) DEFAULT NULL,
  `LastLongitude` float(10,6) DEFAULT NULL,
  `FirstCellDirection` varchar(15) DEFAULT NULL,
  `LastCellDirection` varchar(15) DEFAULT NULL,
  `Pertinent` bit(1) DEFAULT NULL,
  `Notes` text,
  `Source` varchar(255) DEFAULT NULL,
  `ServiceProviderID` int(11) DEFAULT NULL,
  `Created` datetime DEFAULT NULL,
  `Modified` datetime DEFAULT NULL,
  PRIMARY KEY (`PhoneCallID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table Phones
# ------------------------------------------------------------

DROP TABLE IF EXISTS `Phones`;

CREATE TABLE `Phones` (
  `PhoneID` int(11) NOT NULL AUTO_INCREMENT,
  `CaseID` int(11) DEFAULT NULL,
  `PhoneNumber` varchar(127) DEFAULT NULL,
  `ServiceProviderID` int(11) DEFAULT NULL,
  `Created` datetime DEFAULT NULL,
  `Modified` datetime DEFAULT NULL,
  `ShortName` varchar(127) DEFAULT NULL,
  `LongName` varchar(255) DEFAULT NULL,
  `Icon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`PhoneID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table ServiceProviders
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ServiceProviders`;

CREATE TABLE `ServiceProviders` (
  `ServiceProviderID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) DEFAULT NULL,
  `Created` datetime DEFAULT NULL,
  `Modified` datetime DEFAULT NULL,
  PRIMARY KEY (`ServiceProviderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table SprintTowers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `SprintTowers`;

CREATE TABLE `SprintTowers` (
  `SprintTowerID` int(11) NOT NULL AUTO_INCREMENT,
  `CellNum` int(11) DEFAULT NULL,
  `CascadeID` varchar(255) DEFAULT NULL,
  `Switch` varchar(255) DEFAULT NULL,
  `NEID` int(11) DEFAULT NULL,
  `Repoll` int(11) DEFAULT NULL,
  `Latitude` float(10,6) DEFAULT NULL,
  `Longitude` float(10,6) DEFAULT NULL,
  `BTSManufacturer` varchar(255) DEFAULT NULL,
  `Sector` int(11) DEFAULT NULL,
  `Azimuth` int(11) DEFAULT NULL,
  `CDRStatus` varchar(127) DEFAULT NULL,
  `Created` datetime DEFAULT NULL,
  `Modified` datetime DEFAULT NULL,
  PRIMARY KEY (`SprintTowerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table VerizonSwitchElementMap
# ------------------------------------------------------------

DROP TABLE IF EXISTS `VerizonSwitchElementMap`;

CREATE TABLE `VerizonSwitchElementMap` (
  `VerizonSwitchElementMapID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `NetworkElementName` varchar(255) DEFAULT NULL,
  `SwitchName` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`VerizonSwitchElementMapID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table VerizonTowerFiles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `VerizonTowerFiles`;

CREATE TABLE `VerizonTowerFiles` (
  `VerizonTowerFileID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `FileName` varchar(255) DEFAULT NULL,
  `Created` datetime DEFAULT NULL,
  `Modified` datetime DEFAULT NULL,
  PRIMARY KEY (`VerizonTowerFileID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table VerizonTowers
# ------------------------------------------------------------

DROP TABLE IF EXISTS `VerizonTowers`;

CREATE TABLE `VerizonTowers` (
  `VerizonTowerID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `MarketSID` varchar(255) DEFAULT NULL,
  `SwitchNumber` varchar(11) DEFAULT NULL,
  `SwitchName` varchar(255) DEFAULT NULL,
  `CellNumber` varchar(255) DEFAULT NULL,
  `Latitude` float(10,6) DEFAULT NULL,
  `Longitude` float(10,6) DEFAULT NULL,
  `StreetAddress` varchar(255) DEFAULT NULL,
  `City` varchar(255) DEFAULT NULL,
  `State` varchar(255) DEFAULT NULL,
  `ZIP` varchar(255) DEFAULT NULL,
  `Sector` varchar(7) DEFAULT NULL,
  `Technology` varchar(255) DEFAULT NULL,
  `Azimuth` varchar(255) DEFAULT NULL,
  `SourceID` int(11) DEFAULT NULL,
  `Created` datetime DEFAULT NULL,
  `Modified` datetime DEFAULT NULL,
  PRIMARY KEY (`VerizonTowerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
