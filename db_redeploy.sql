CREATE DATABASE IF NOT EXISTS demo; 

USE demo;

CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	username VARCHAR(50) NOT NULL,
	password VARCHAR(255) NOT NULL,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	count INT DEFAULT 0,
	email VARCHAR(100) NOT NULL UNIQUE KEY,
	email_verification_link VARCHAR(255) NOT NULL,
	email_verified_at TIMESTAMP,
	first_name VARCHAR(20),         
	last_name VARCHAR(20),                               
	dob DATE,                                     
	ans VARCHAR(255),                 
	ques INT,                         
	tfaen INT,                         
	tfa VARCHAR(255)              
);

CREATE TABLE IF NOT EXISTS all_login_attempts (
	username VARCHAR(50) NOT NULL,
	password VARCHAR(255) NOT NULL,
	attempt_date DATETIME,
	ip VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS login_attempts LIKE all_login_attempts;

CREATE TABLE IF NOT EXISTS password_reset_temp (
	email VARCHAR(250),
	keyTO VARCHAR(255),
	expD DATETIME
);

