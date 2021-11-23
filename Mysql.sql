CREATE DATABASE covidTracker

CREATE TABLE countries(
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    Country VARCHAR (64),
    Slug VARCHAR (64),
    ISO2 VArchar (32)
    
);


CREATE TABLE cases(
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    confirmed  INT UNSIGNED,
    active  INT UNSIGNED,
    deaths INT UNSIGNED,
   recovered INT UNSIGNED,
    New_Cases INT UNSIGNED,
   New_Deaths INT UNSIGNED,
    New_Recovered INT UNSIGNED,
    `Date` DATETIME,
    `country_id` INT UNSIGNED,
    CONSTRAINT FOREIGN KEY (country_id) REFERENCES countries(id)
    
);

