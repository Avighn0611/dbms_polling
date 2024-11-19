// RUN following queries in MYSQL CLI

CREATE DATABASE IF NOT EXISTS Avighn;

USE Avighn;

CREATE TABLE users(
    firstname varchar(20),
    lastname varchar(20),
    age int,
    gender varchar(10),
    username varchar(10) unique,
    password varchar(10) unique,
    state varchar(10),
    city varchar(10),
    country varchar(10),
    phoneno bigint,
    email varchar(20),
    primary key(phoneno,email)
);

CREATE TABLE polls (
    pollID INT AUTO_INCREMENT PRIMARY KEY,  
    title VARCHAR(255) NOT NULL,
    createdby VARCHAR(50),                 
    startdate DATETIME,
    enddate DATETIME,
    type ENUM('vote','delete''edit'),
    FOREIGN KEY (createdby) REFERENCES users(username) 
);

CREATE TABLE questions (
    questionID INT AUTO_INCREMENT PRIMARY KEY, 
    questiontext TEXT NOT NULL,                 
    pollID INT,                               
    FOREIGN KEY (pollID) REFERENCES polls(pollID) 
    ON DELETE CASCADE                           
    ON UPDATE CASCADE                          
);

CREATE TABLE options (
    optionID INT AUTO_INCREMENT PRIMARY KEY,  
    optiontext TEXT NOT NULL,                 
    questionID INT,                           
    FOREIGN KEY (questionID) REFERENCES questions(questionID)  
    ON DELETE CASCADE                         
    ON UPDATE CASCADE                         
);

CREATE TABLE votes (
    voteID INT AUTO_INCREMENT PRIMARY KEY,        
    voter VARCHAR(10),                             
    optionID INT,                                  
    votedate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,   
    FOREIGN KEY (voter) REFERENCES users(username) 
    ON DELETE CASCADE                              
    ON UPDATE CASCADE,                             
    FOREIGN KEY (optionID) REFERENCES options(optionID)
    ON DELETE CASCADE                              
    ON UPDATE CASCADE                              
);

CREATE TABLE user_activity (
    activity_id INT AUTO_INCREMENT PRIMARY KEY, 
    pollID INT,                                
    username VARCHAR(10),                     
    action_type ENUM('login', 'logout'), 
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pollID) REFERENCES polls(pollID) ON DELETE SET NULL, 
    FOREIGN KEY (username) REFERENCES users(username) ON DELETE CASCADE 
);
