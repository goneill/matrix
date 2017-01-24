# database structure
DROP DATABASE IF EXISTS matrix; 

CREATE DATABASE IF NOT EXISTS matrix;

USE matrix;

#create table 

CREATE TABLE `Cases` (
	`CaseID` INTEGER NOT NULL AUTO_INCREMENT,
	`CaseName` VARCHAR(255),
	`Caption` VARCHAR (255),
	`JudgeID` INTEGER,
	`Created` DATETIME,
	`Modified` DATETIME,
	CONSTRAINT `PK_Cases` PRIMARY KEY (`CaseID`)

);

CREATE TABLE `Judges` (
	`JudgeID` INTEGER NOT NULL AUTO_INCREMENT,
	`Name` VARCHAR (255),
	`Created` DATETIME,
	`Modified` DATETIME,
	CONSTRAINT `PK_Judges` PRIMARY KEY (`JudgeID`)
);

CREATE TABLE `Phones` (
	`PhoneID` INTEGER NOT NULL AUTO_INCREMENT,
	`CaseID` INTEGER,
	`PhoneNumber` VARCHAR (127),
	`ServiceProviderID` INTEGER,
	`Created` DATETIME,
	`Modified` DATETIME,
	`ShortName` VARCHAR (127),
	`LongName` VARCHAR (255),
	`Icon` VARCHAR (255),
	CONSTRAINT `PK_Phones` PRIMARY KEY (`PhoneID`) 
);

CREATE TABLE `People` (
	`PersonID` INTEGER NOT NULL AUTO_INCREMENT,
	`CaseID` INTEGER,
	`LastName` VARCHAR(63),
	`FirstName` VARCHAR(63),
	`DOB` DATETIME,
	`Notes` TEXT,
	`Created` DATETIME,
	`Modified` DATETIME,
	CONSTRAINT `PK_People` PRIMARY KEY (`PersonID`)
);

CREATE TABLE `PeopleContacts` (
	`PeopleContactID` INTEGER NOT NULL AUTO_INCREMENT,
	`PersonID` INTEGER,
	`CaseID` INTEGER,
	`Address1` VARCHAR (255),
	`Address2` VARCHAR (255),
	`City` VARCHAR (255),
	`State` VARCHAR (7),
	`Zip` VARCHAR (15),
	`Country` VARCHAR (63),
	`Phone` VARCHAR (15), 
	`Email` VARCHAR (127),
	`Fax` VARCHAR (15),
	`Facebook` VARCHAR (127),
	`Twitter` VARCHAR (127),
	`Instagram` VARCHAR (127),
	`Current` BIT DEFAULT 0,
	`Main` BIT DEFAULT 0,
	`Notes` TEXT,
	`Created` DATETIME,
	`Modified` DATETIME,
	CONSTRAINT `PK_PeopleContacts` PRIMARY KEY (`PeopleContactID`)

);

CREATE TABLE `PeopleTypes`(
	`PersonTypeID` INTEGER NOT NULL AUTO_INCREMENT,
	`Name` VARCHAR (255),
	`Created` DATETIME,
	`Modified` DATETIME,
	CONSTRAINT `PK_PeopleTypes` PRIMARY KEY (`PersonTypeID`));

CREATE TABLE `PersonPersonType` (
	`PersonPersonTypeID` INTEGER NOT NULL AUTO_INCREMENT,
	`PersonID` INTEGER,
	`PersonTypeID` INTEGER,
	`CaseID` INTEGER,
	`Created` DATETIME,
	`Modified` DATETIME,
	CONSTRAINT `PK_PersonPersonTypes` PRIMARY KEY (`PersonPersonTypeID`)
);


Create TABLE `ServiceProviders` (
	`ServiceProviderID`	INTEGER NOT NULL AUTO_INCREMENT,
	`Name` VARCHAR (255),
	`Created` DATETIME,
	`Modified` DATETIME,
	CONSTRAINT `PK_ServiceProviders` PRIMARY KEY (`ServiceProviderID`)
);

CREATE TABLE `PhoneCalls` (
	`PhoneCallID` INTEGER NOT NULL AUTO_INCREMENT,
	`CaseID` INTEGER,
	`CallToPhoneID` INTEGER,
	`CallFromPhoneID` INTEGER,
	`DialedDigits` VARCHAR (255),
	`MRNum` VARCHAR (255),
	`StartDate` DATETIME,
	`EndDate` DATETIME,
	`Duration` DATETIME,
	`NEID` INTEGER,
	`REPOLL` INTEGER,
	`FirstCell` INTEGER,
	`LastCell` INTEGER,
	`FirstLatitude` FLOAT(10,6),
	`FirstLongitude` FLOAT(10,6),
	`LastLatitude` FLOAT(10,6),
	`LastLongitude` FLOAT(10,6),
	`FirstCellDirection` VARCHAR (15),
	`LastCellDirection` VARCHAR (15),
	`Pertinent` BIT,
	`Notes` TEXT,
	`Created` DATETIME,
	`Modified` DATETIME,
	CONSTRAINT `PK_PhoneCalls` PRIMARY KEY (`PhoneCallID`)
);

CREATE TABLE `SprintTowers`(
	`SprintTowerID` INTEGER NOT NULL AUTO_INCREMENT,
	`CellNum` INTEGER,
	`CascadeID` VARCHAR (255),
	`Switch` VARCHAR (255),
	`NEID` INTEGER,
	`Repoll` INTEGER,
	`Latitude` FLOAT(10,6),
	`Longitude` FLOAT(10,6),
	`BTSManufacturer` VARCHAR (255),
	`Sector` INTEGER,
	`Azimuth` INTEGER,
	`CDRStatus` Varchar (127),
	`Created` DATETIME,
	`Modified` DATETIME,
	CONSTRAINT `PK_SprintTowers` PRIMARY KEY (`SprintTowerID`)
);
	
CREATE TABLE `PeoplePhones`(
	`PeoplePhoneID`INTEGER NOT NULL AUTO_INCREMENT,
	`PersonID` INTEGER,
	`CaseID` INTEGER,
	`StartUse` DATETIME,
	`EndUse` DATETIME,
	`Created` DATETIME,
	`Modified` DATETIME,
	CONSTRAINT `PK_PeoplePhones` PRIMARY KEY (`PeoplePhoneID`)
);
# ---------------------------------------------------------------------- #
# Foreign key constraints                                                #
# ---------------------------------------------------------------------- #

