CREATE TABLE IF NOT EXISTS Users
(
	id int auto_increment,
	email varchar(255) NOT NULL,
	name varchar(255) NOT NULL,
	password varchar(255) NOT NULL,	
	salt varchar(255) NOT NULL,
	Primary KEY (id)
);

CREATE TABLE IF NOT EXISTS Locations
(
	id int auto_increment,
	street varchar(255),
	zip char(5),
	PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS Categories
(
	id int auto_increment,
	name varchar(255),
	PRIMARY KEY (id),
	UNIQUE KEY (name)
);

CREATE TABLE IF NOT EXISTS Restaurants
(
	id int auto_increment,
	name varchar(255),
	location int,
	FOREIGN KEY (location) REFERENCES Locations(id),
	category int,
	FOREIGN KEY (category) REFERENCES Categories(id),
	delivers tinyint,
	approved tinyint,
	requester int,
	FOREIGN KEY (requester) REFERENCES Users(id),
	approver int,
	FOREIGN KEY (approver) REFERENCES Users(id),
	PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS Orders
(
	id int auto_increment,
	timeArrived datetime,
	timePlaced datetime,
	price double,
	userID int,
	FOREIGN KEY (userID) REFERENCES Users(id),
	restaurant int,
	FOREIGN KEY (restaurant) REFERENCES Restaurants(id),
	location int,
	FOREIGN KEY (location) REFERENCES Locations(id),
	PRIMARY KEY (id)
);