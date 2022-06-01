echo "===================================HELLO********************";


CREATE DATABASE IF NOT EXISTS pureeatstest;
USE pureeatstest;

-- create the users for each database
CREATE USER 'arpan'@'%' IDENTIFIED BY 'password';
GRANT CREATE, ALTER, INDEX, LOCK TABLES, REFERENCES, UPDATE, DELETE, DROP, SELECT, INSERT ON `pureeatstest`.* TO 'arpan'@'%';

FLUSH PRIVILEGES;


CREATE TABLE pureeatstest.person (
    PersonID int,
    LastName varchar(255),
    FirstName varchar(255), 
    Address varchar(255),
    City varchar(255)
);

show databases;