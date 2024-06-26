--
-- 	Database Table Creation
--  First drop any existing tables. Any errors are ignored.
--
DROP TABLE Dev CASCADE CONSTRAINTS;
DROP TABLE Producer CASCADE CONSTRAINTS;
DROP TABLE Composer CASCADE CONSTRAINTS;
DROP TABLE ArtDesign CASCADE CONSTRAINTS;
DROP TABLE Programmer CASCADE CONSTRAINTS;
DROP TABLE Publisher CASCADE CONSTRAINTS;
DROP TABLE DevTeam CASCADE CONSTRAINTS;
DROP TABLE AssociatedWith CASCADE CONSTRAINTS;
DROP TABLE Includes CASCADE CONSTRAINTS;
DROP TABLE Platform CASCADE CONSTRAINTS;
DROP TABLE VideoGameMadeBy CASCADE CONSTRAINTS;
DROP TABLE PlayedOn CASCADE CONSTRAINTS;
DROP TABLE ContainsDLC CASCADE CONSTRAINTS;
DROP TABLE Account CASCADE CONSTRAINTS;
DROP TABLE Adds CASCADE CONSTRAINTS;
DROP TABLE MakesReviewReviewing2 CASCADE CONSTRAINTS;
DROP TABLE MakesReviewReviewing1 CASCADE CONSTRAINTS;
--
-- Now, add each table.
--
CREATE TABLE Dev (
	DevName VARCHAR(128),
	Website VARCHAR(128),
	PRIMARY KEY (DevName)
);
CREATE TABLE Producer (
	DevName VARCHAR(128),
	PRIMARY KEY (DevName),
	FOREIGN KEY (DevName) REFERENCES Dev
);
CREATE TABLE Composer (
	DevName VARCHAR(128),
	PRIMARY KEY (DevName),
	FOREIGN KEY (DevName) REFERENCES Dev,
	Genre VARCHAR(128),
	Instrument VARCHAR(128)
);
CREATE TABLE ArtDesign (
	DevName VARCHAR(128),
	PRIMARY KEY (DevName),
	FOREIGN KEY (DevName) REFERENCES Dev,
	ArtistRole VARCHAR(128),
	Style VARCHAR(128)
);
CREATE TABLE Programmer (
	DevName VARCHAR(128),
	PRIMARY KEY (DevName),
	FOREIGN KEY (DevName) REFERENCES Dev,
	Task VARCHAR(128)
);
CREATE TABLE Publisher (
	PublisherName VARCHAR(128),
	Location VARCHAR(128),
	PRIMARY KEY (PublisherName),
	Employees INT
);
CREATE TABLE DevTeam (
	DevTeamName VARCHAR(128),
	Employees INT,
	Location VARCHAR(128),
	PRIMARY KEY (DevTeamName)
);
CREATE TABLE AssociatedWith (
	DevTeamName VARCHAR(128),
	PublisherName VARCHAR(128),
	PRIMARY KEY (DevTeamName, PublisherName),
	FOREIGN KEY (DevTeamName) REFERENCES DevTeam,
	FOREIGN KEY (PublisherName) REFERENCES Publisher
);
CREATE TABLE Includes (
	DevTeamName VARCHAR(128),
	DevName VARCHAR(128),
	PRIMARY KEY (DevTeamName, DevName),
	FOREIGN KEY (DevTeamName) REFERENCES DevTeam,
	FOREIGN KEY (DevName) REFERENCES Dev
);
CREATE TABLE Platform (
	PlatformName VARCHAR(128),
	Type VARCHAR(128),
	PRIMARY KEY (PlatformName)
);
CREATE TABLE VideoGameMadeBy (
	GID INT,
	Name VARCHAR(128),
	ReleaseDate DATE,
	Price FLOAT(24),
	Category VARCHAR(128),
	DevTeamName VARCHAR(128) NOT NULL,
	PRIMARY KEY(GID),
	FOREIGN KEY (DevTeamName) REFERENCES DevTeam
);
CREATE TABLE PlayedOn (
	GID INT,
	PlatformName VARCHAR(128),
	PRIMARY KEY (GID, PlatformName),
	FOREIGN KEY (GID) REFERENCES VideoGameMadeBy ON DELETE CASCADE,
	FOREIGN KEY (PlatformName) REFERENCES Platform ON DELETE CASCADE
);
CREATE TABLE ContainsDLC (
	GID INT,
	DLCName VARCHAR(128),
	Price FLOAT(24),
	ReleaseDate DATE,
	PRIMARY KEY (GID, DLCName),
	FOREIGN KEY (GID) REFERENCES VideoGameMadeBy ON DELETE CASCADE
);
CREATE TABLE Account (
	Username VARCHAR(128),
	Email VARCHAR(128) UNIQUE NOT NULL,
	DisplayName VARCHAR(128),
	CreationDate DATE,
	PRIMARY KEY (Username)
);
CREATE TABLE Adds (
	Username VARCHAR(128),
	GID INT,
	Status VARCHAR(128),
	PRIMARY KEY (Username, GID),
	FOREIGN KEY (Username) REFERENCES Account, 
	FOREIGN KEY (GID) REFERENCES VideoGameMadeBy ON DELETE CASCADE
);
CREATE TABLE MakesReviewReviewing2 (
	Length INT,
	Category VARCHAR(128),
	PRIMARY KEY (Length)
);
CREATE TABLE MakesReviewReviewing1 (
	ReviewID INT,
	ReviewDate DATE,
	Rating INT,
	Length INT NOT NULL,
	Username VARCHAR(128) NOT NULL,
	GID INT NOT NULL,
	PRIMARY KEY (ReviewID),
	FOREIGN KEY (Length) REFERENCES MakesReviewReviewing2,
	FOREIGN KEY (Username) REFERENCES Account,
	FOREIGN KEY (GID) REFERENCES VideoGameMadeBy ON DELETE CASCADE
);
-- done adding all of the tables, now add in some tuples
-- Dev
INSERT INTO Dev(DevName, Website)
VALUES (
		'Ryota Niitsuma',
		'https://www.mobygames.com/person/362265/ryota-niitsuma/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Tomohiko Imanishi',
		'https://www.mobygames.com/person/364171/tomohiko-imanishi/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Azusa Shimada',
		'https://www.mobygames.com/person/1108482/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Davor Hunski',
		'https://www.mobygames.com/person/41188/davor-hunski/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Yohei Shimbori',
		'https://www.mobygames.com/person/509159/yohei-shimbori/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Atsushi Kitajoh',
		'https://www.mobygames.com/person/364191/atsushi-kitajoh/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Takuji Kawano',
		'https://www.mobygames.com/person/98565/takuji-kawano/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Hiromi Sagara',
		'https://www.mobygames.com/person/484529/hiromi-sagara/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Oguchi',
		'https://www.mobygames.com/person/1101660/oguchi/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Yusuke Kozaki',
		'https://www.mobygames.com/person/406971/yusuke-kozaki/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Toru Narihiro',
		'https://www.mobygames.com/person/534705/toru-narihiro/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Hitoshi Yamagami',
		'https://www.mobygames.com/person/50693/hitoshi-yamagami/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Hiroki Morishita',
		'https://www.mobygames.com/person/609076/hiroki-morishita/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Keiichi Okabe',
		'https://www.mobygames.com/person/129821/keiichi-okabe/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Yoshinori Kawamoto',
		'https://www.mobygames.com/person/178444/yoshinori-kawamoto/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Keigo Hoashi',
		'https://www.mobygames.com/person/600583/keigo-hoashi/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Ryo Onishi',
		'https://www.mobygames.com/person/710385/ryo-onishi/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Takashi Wagatsuma',
		'https://www.mobygames.com/person/565395/takashi-wagatsuma/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Misa Yasui',
		'https://www.mobygames.com/person/729004/misa-yasui/'
	);
INSERT INTO Dev(DevName, Website)
VALUES (
		'Takahiro Kitagawa',
		'https://www.mobygames.com/person/1083697/takahiro-kitagawa/'
	);
-- Producers
INSERT INTO Producer(DevName)
VALUES ('Ryota Niitsuma');
INSERT INTO Producer(DevName)
VALUES ('Davor Hunski');
INSERT INTO Producer(DevName)
VALUES ('Yohei Shimbori');
INSERT INTO Producer(DevName)
VALUES ('Toru Narihiro');
INSERT INTO Producer(DevName)
VALUES ('Hitoshi Yamagami');
-- Composer
INSERT INTO Composer(DevName, Genre, Instrument)
VALUES ('Atsushi Kitajoh', 'Jazz', 'Varied');
INSERT INTO Composer(DevName, Genre, Instrument)
VALUES ('Hiroki Morishita', 'Battle', 'Varied');
INSERT INTO Composer(DevName, Genre, Instrument)
VALUES ('Keiichi Okabe', 'Battle', 'Varied');
INSERT INTO Composer(DevName, Genre, Instrument)
VALUES ('Yoshinori Kawamoto', 'Battle', 'Varied');
INSERT INTO Composer(DevName, Genre, Instrument)
VALUES ('Keigo Hoashi', 'Battle', 'Varied');
-- ArtDesign
INSERT INTO ArtDesign(DevName, ArtistRole, Style)
VALUES (
		'Azusa Shimada',
		'Lead 2D character design',
		'anime'
	);
INSERT INTO ArtDesign(DevName, ArtistRole, Style)
VALUES (
		'Takuji Kawano',
		'Character Concept Artist',
		'anime'
	);
INSERT INTO ArtDesign(DevName, ArtistRole, Style)
VALUES (
		'Hiromi Sagara',
		'Character Concept Artist',
		'anime'
	);
INSERT INTO ArtDesign(DevName, ArtistRole, Style)
VALUES ('Oguchi', 'Character Designer', 'anime');
INSERT INTO ArtDesign(DevName, ArtistRole, Style)
VALUES ('Yusuke Kozaki', 'Character Designer', 'anime');
-- Programmer
INSERT INTO Programmer(DevName, Task)
VALUES ('Tomohiko Imanishi', 'Chief Programmer');
INSERT INTO Programmer(DevName, Task)
VALUES ('Ryo Onishi', 'Lead Programmer');
INSERT INTO Programmer(DevName, Task)
VALUES ('Takashi Wagatsuma', 'Game Programmer');
INSERT INTO Programmer(DevName, Task)
VALUES ('Misa Yasui', 'System Programmer');
INSERT INTO Programmer(DevName, Task)
VALUES ('Takahiro Kitagawa', 'Sound Programmer');
-- Publisher
INSERT INTO Publisher(PublisherName, Location, Employees)
VALUES ('Sega', 'Tokyo, Japan', '3459');
INSERT INTO Publisher(PublisherName, Location, Employees)
VALUES ('Devolver Digital', 'Texas, US', '235');
INSERT INTO Publisher(PublisherName, Location, Employees)
VALUES (
		'Bandai Namco Entertainment',
		'Tokyo, Japan',
		'710'
	);
INSERT INTO Publisher(PublisherName, Location, Employees)
VALUES ('Square Enix', 'Tokyo, Japan', '4712');
INSERT INTO Publisher(PublisherName, Location, Employees)
VALUES ('Annapurna Interactive', 'California, USA', '25');
INSERT INTO Publisher(PublisherName, Location, Employees)
VALUES ('Maddy Makes Games', 'null', '10');
-- DevTeam
INSERT INTO DevTeam(DevTeamName, Employees, Location)
VALUES ('Atlus', '338', 'Tokyo, Japan');
INSERT INTO DevTeam(DevTeamName, Employees, Location)
VALUES ('Croteam', '42', 'Zagreb, Croatia');
INSERT INTO DevTeam(DevTeamName, Employees, Location)
VALUES (
		'Bandai Namco Studios Inc.',
		'1219',
		'Tokyo, Japan'
	);
INSERT INTO DevTeam(DevTeamName, Employees, Location)
VALUES ('Arc System Works', '180', 'Yokohama, Japan');
INSERT INTO DevTeam(DevTeamName, Employees, Location)
VALUES ('Maddy Makes Games', '10', 'null');
-- AssociatedWith
INSERT INTO AssociatedWith(DevTeamName, PublisherName)
VALUES ('Atlus', 'Sega');
INSERT INTO AssociatedWith(DevTeamName, PublisherName)
VALUES ('Croteam', 'Devolver Digital');
INSERT INTO AssociatedWith(DevTeamName, PublisherName)
VALUES (
		'Bandai Namco Studios Inc.',
		'Bandai Namco Entertainment'
	);
INSERT INTO AssociatedWith(DevTeamName, PublisherName)
VALUES ('Arc System Works', 'Bandai Namco Entertainment');
INSERT INTO AssociatedWith(DevTeamName, PublisherName)
VALUES ('Maddy Makes Games', 'Maddy Makes Games');
-- Includes
INSERT INTO Includes(DevTeamName, DevName)
VALUES ('Atlus', 'Ryota Niitsuma');
INSERT INTO Includes(DevTeamName, DevName)
VALUES ('Atlus', 'Tomohiko Imanishi');
INSERT INTO Includes(DevTeamName, DevName)
VALUES ('Atlus', 'Azusa Shimada');
INSERT INTO Includes(DevTeamName, DevName)
VALUES ('Croteam', 'Davor Hunski');
INSERT INTO Includes(DevTeamName, DevName)
VALUES ('Bandai Namco Studios Inc.', 'Yohei Shimbori');
-- Platform
INSERT INTO Platform(PlatformName, Type)
VALUES ('Steam', 'PC');
INSERT INTO Platform(PlatformName, Type)
VALUES ('Nintendo Switch', 'Console');
INSERT INTO Platform(PlatformName, Type)
VALUES ('Play Store', 'Mobile');
INSERT INTO Platform(PlatformName, Type)
VALUES ('3DS', 'Portable');
INSERT INTO Platform(PlatformName, Type)
VALUES ('Playstation 5', 'Console');
-- VideoGameMadeBy
INSERT INTO VideoGameMadeBy(
		GID,
		Name,
		ReleaseDate,
		Price,
		Category,
		DevTeamName
	)
VALUES (
		'216878',
		'Persona 3: Reload',
		TO_DATE('2024-02-02', 'yyyy/mm/dd'),
		'69.99',
		'RPG',
		'Atlus'
	);
INSERT INTO VideoGameMadeBy(
		GID,
		Name,
		ReleaseDate,
		Price,
		Category,
		DevTeamName
	)
VALUES (
		'1382330',
		'Persona 5 Strikers',
		TO_DATE('2021-02-22', 'yyyy/mm/dd'),
		'79.99',
		'RPG',
		'Atlus'
	);
INSERT INTO VideoGameMadeBy(
		GID,
		Name,
		ReleaseDate,
		Price,
		Category,
		DevTeamName
	)
VALUES (
		'257510',
		'The Talos Principle',
		TO_DATE('2014-12-04', 'yyyy/mm/dd'),
		'36.99',
		'Puzzle',
		'Croteam'
	);
INSERT INTO VideoGameMadeBy(
		GID,
		Name,
		ReleaseDate,
		Price,
		Category,
		DevTeamName
	)
VALUES (
		'1778820',
		'TEKKEN 8',
		TO_DATE('2024-01-25', 'yyyy/mm/dd'),
		'93.49',
		'Fighting',
		'Bandai Namco Studios Inc.'
	);
INSERT INTO VideoGameMadeBy(
		GID,
		Name,
		ReleaseDate,
		Price,
		Category,
		DevTeamName
	)
VALUES (
		'101945',
		'Dragon Ball',
		TO_DATE('2018-01-26', 'yyyy/mm/dd'),
		'59.99',
		'Fighting',
		'Arc System Works'
	);
INSERT INTO VideoGameMadeBy(
		GID,
		Name,
		ReleaseDate,
		Price,
		Category,
		DevTeamName
	)
VALUES (
		'504230',
		'Celeste',
		TO_DATE('2018-01-25', 'yyyy/mm/dd'),
		'25.99',
		'Platformer',
		'Maddy Makes Games'
	);
-- PlayedOn
INSERT INTO PlayedOn(GID, PlatformName)
VALUES ('216878', 'Steam');
INSERT INTO PlayedOn(GID, PlatformName)
VALUES ('1382330', 'Nintendo Switch');
INSERT INTO PlayedOn(GID, PlatformName)
VALUES ('257510', 'Steam');
INSERT INTO PlayedOn(GID, PlatformName)
VALUES ('1778820', 'Steam');
INSERT INTO PlayedOn(GID, PlatformName)
VALUES ('101945', 'Steam');
INSERT INTO PlayedOn(GID, PlatformName)
VALUES ('504230', 'Nintendo Switch');
-- ContainsDLC
INSERT INTO ContainsDLC(GID, DLCName, Price, ReleaseDate)
VALUES (
		'101945',
		'Dragon Ball FighterZ: Broly',
		'4.99',
		TO_DATE('2018-03-28', 'yyyy/mm/dd')
	);
INSERT INTO ContainsDLC(GID, DLCName, Price, ReleaseDate)
VALUES (
		'101945',
		'Dragon Ball FighterZ: Android 21 (Lab Coat)',
		'4.99',
		TO_DATE('2022-02-23', 'yyyy/mm/dd')
	);
INSERT INTO ContainsDLC(GID, DLCName, Price, ReleaseDate)
VALUES (
		'101945',
		'Dragon Ball FighterZ: Vegito (SSGSS)',
		'4.99',
		TO_DATE('2018-05-31', 'yyyy/mm/dd')
	);
INSERT INTO ContainsDLC(GID, DLCName, Price, ReleaseDate)
VALUES (
		'101945',
		'Dragon Ball FighterZ: Bardock',
		'4.99',
		TO_DATE('2018-03-28', 'yyyy/mm/dd')
	);
INSERT INTO ContainsDLC(GID, DLCName, Price, ReleaseDate)
VALUES (
		'101945',
		'Dragon Ball FighterZ: Goku (Ultra Instinct)',
		'4.99',
		TO_DATE('2020-05-21', 'yyyy/mm/dd')
	);
-- Account
INSERT INTO Account(Username, Email, DisplayName, CreationDate)
VALUES (
		'dkos',
		'dkos1884 @email.com',
		'Salvation',
		TO_DATE('2023-02-14', 'yyyy/mm/dd')
	);
INSERT INTO Account(Username, Email, DisplayName, CreationDate)
VALUES (
		'popcornman',
		'ilovemovies @office.ca',
		'SilentWatcher',
		TO_DATE('2023-01-08', 'yyyy/mm/dd')
	);
INSERT INTO Account(Username, Email, DisplayName, CreationDate)
VALUES (
		'dreamindream',
		'inception @inception.com',
		'LucidDreamer',
		TO_DATE('2010-07-08', 'yyyy/mm/dd')
	);
INSERT INTO Account(Username, Email, DisplayName, CreationDate)
VALUES (
		'heronboy',
		'ghilblilover @live.com',
		'Heron',
		TO_DATE('2023-12-08', 'yyyy/mm/dd')
	);
INSERT INTO Account(Username, Email, DisplayName, CreationDate)
VALUES (
		'mobius',
		'ahoy @gmail.com',
		'Polybius',
		TO_DATE('2017-09-08', 'yyyy/mm/dd')
	);
-- Adds
INSERT INTO Adds(Username, GID, Status)
VALUES ('dkos', '216878', 'playing');
INSERT INTO Adds(Username, GID, Status)
VALUES ('popcornman', '1382330', 'complete');
INSERT INTO Adds(Username, GID, Status)
VALUES ('dreamindream', '257510', 'backlog');
INSERT INTO Adds(Username, GID, Status)
VALUES ('dreamindream', '216878', 'backlog');
INSERT INTO Adds(Username, GID, Status)
VALUES ('heronboy', '216878', 'complete');
INSERT INTO Adds(Username, GID, Status)
VALUES ('heronboy', '1778820', 'playing');
INSERT INTO Adds(Username, GID, Status)
VALUES ('heronboy', '101945', 'playing');
INSERT INTO Adds(Username, GID, Status)
VALUES ('mobius', '101945', 'playing');
INSERT INTO Adds(Username, GID, Status)
VALUES ('mobius', '216878', 'complete');
INSERT INTO Adds(Username, GID, Status)
VALUES ('mobius', '257510', 'backlog');
INSERT INTO Adds(Username, GID, Status)
VALUES ('mobius', '504230', 'backlog');
INSERT INTO Adds(Username, GID, Status)
VALUES ('mobius', '1382330', 'backlog');
INSERT INTO Adds(Username, GID, Status)
VALUES ('mobius', '1778820', 'backlog');
-- MakesReviewReviewing2
INSERT INTO MakesReviewReviewing2(Category, Length)
VALUES ('Short', '0');
INSERT INTO MakesReviewReviewing2(Category, Length)
VALUES ('Medium', '300');
INSERT INTO MakesReviewReviewing2(Category, Length)
VALUES ('Medium', '200');
INSERT INTO MakesReviewReviewing2(Category, Length)
VALUES ('Short', '50');
INSERT INTO MakesReviewReviewing2(Category, Length)
VALUES ('Long', '1000');
INSERT INTO MakesReviewReviewing2(Category, Length)
VALUES ('Medium', '400');
-- MakesReviewReviewing1
INSERT INTO MakesReviewReviewing1(
		ReviewID,
		ReviewDate,
		Rating,
		Length,
		Username,
		GID
	)
VALUES (
		'2341',
		TO_DATE('2023-02-18', 'yyyy/mm/dd'),
		'2',
		'300',
		'dkos',
		'216878'
	);
INSERT INTO MakesReviewReviewing1(
		ReviewID,
		ReviewDate,
		Rating,
		Length,
		Username,
		GID
	)
VALUES (
		'8766',
		TO_DATE('2021-11-28', 'yyyy/mm/dd'),
		'2',
		'400',
		'dreamindream',
		'216878'
	);
INSERT INTO MakesReviewReviewing1(
		ReviewID,
		ReviewDate,
		Rating,
		Length,
		Username,
		GID
	)
VALUES (
		'8767',
		TO_DATE('2024-09-02', 'yyyy/mm/dd'),
		'5',
		'50',
		'heronboy',
		'216878'
	);
INSERT INTO MakesReviewReviewing1(
		ReviewID,
		ReviewDate,
		Rating,
		Length,
		Username,
		GID
	)
VALUES (
		'1234',
		TO_DATE('2023-09-05', 'yyyy/mm/dd'),
		'4',
		'200',
		'popcornman',
		'1382330'
	);
INSERT INTO MakesReviewReviewing1(
		ReviewID,
		ReviewDate,
		Rating,
		Length,
		Username,
		GID
	)
VALUES (
		'5756',
		TO_DATE('2019-05-27', 'yyyy/mm/dd'),
		'5',
		'50',
		'dreamindream',
		'257510'
	);
INSERT INTO MakesReviewReviewing1(
		ReviewID,
		ReviewDate,
		Rating,
		Length,
		Username,
		GID
	)
VALUES (
		'5757',
		TO_DATE('2018-08-10', 'yyyy/mm/dd'),
		'2',
		'50',
		'mobius',
		'257510'
	);
INSERT INTO MakesReviewReviewing1(
		ReviewID,
		ReviewDate,
		Rating,
		Length,
		Username,
		GID
	)
VALUES (
		'1304',
		TO_DATE('2020-08-13', 'yyyy/mm/dd'),
		'4',
		'1000',
		'heronboy',
		'1778820'
	);
INSERT INTO MakesReviewReviewing1(
		ReviewID,
		ReviewDate,
		Rating,
		Length,
		Username,
		GID
	)
VALUES (
		'1305',
		TO_DATE('2019-09-12', 'yyyy/mm/dd'),
		'5',
		'1000',
		'mobius',
		'1778820'
	);
INSERT INTO MakesReviewReviewing1(
		ReviewID,
		ReviewDate,
		Rating,
		Length,
		Username,
		GID
	)
VALUES (
		'8765',
		TO_DATE('2021-10-14', 'yyyy/mm/dd'),
		'5',
		'400',
		'mobius',
		'101945'
	);
INSERT INTO MakesReviewReviewing1(
		ReviewID,
		ReviewDate,
		Rating,
		Length,
		Username,
		GID
	)
VALUES (
		'8764',
		TO_DATE('2022-07-20', 'yyyy/mm/dd'),
		'3',
		'200',
		'heronboy',
		'101945'
	);