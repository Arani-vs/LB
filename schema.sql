SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

DROP TABLE IF EXISTS `issued_books`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `category`;
DROP TABLE IF EXISTS `authors`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `member_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `address` varchar(250) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'Member',
  `status` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`member_id`, `name`, `email`, `password`, `mobile`, `address`, `role`, `status`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$fR74n4aUgzF9h/SHsJQCXe0mTBY48dgYpvkR1XU4rOzxDfaZu0c0W', '1148458757', 'Library Head Office', 'Admin', 1),
(4, 'user', 'user@gmail.com', '$2y$10$vBcRYhcBhR.kFQgCuSTVQeXYzdyjjjuaMuhYsht4uQKXiNmWW7chW', '2147483644', 'XYZ Coloney, PQR Nagar , Jaipur', 'Member', 1);

CREATE TABLE `admins` (
  `adminID` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  PRIMARY KEY (`adminID`),
  CONSTRAINT `fk_admin_user` FOREIGN KEY (`member_id`) REFERENCES `users` (`member_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `admins` (`adminID`, `member_id`) VALUES
(1, 1);

CREATE TABLE `authors` (
  `author_id` int(11) NOT NULL AUTO_INCREMENT,
  `author_name` varchar(250) NOT NULL,
  PRIMARY KEY (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `authors` (`author_id`, `author_name`) VALUES
(102, 'M D Guptaa'),
(103, 'Chetan Bhagat'),
(104, 'Munshi Prem Chand');

CREATE TABLE `category` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(100) NOT NULL,
  PRIMARY KEY (`cat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `category` (`cat_id`, `cat_name`) VALUES
(1, 'Computer Science Engineering '),
(2, 'Novel'),
(4, 'Motivational'),
(5, 'Story');

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL AUTO_INCREMENT,
  `book_name` varchar(250) NOT NULL,
  `author_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `book_no` int(11) NOT NULL,
  `book_price` int(11) NOT NULL,
  `copies` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`book_id`),
  UNIQUE KEY `uq_book_no` (`book_no`),
  CONSTRAINT `fk_books_author` FOREIGN KEY (`author_id`) REFERENCES `authors` (`author_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_books_category` FOREIGN KEY (`cat_id`) REFERENCES `category` (`cat_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `books` (`book_id`, `book_name`, `author_id`, `cat_id`, `book_no`, `book_price`, `copies`) VALUES
(1, 'Software engineering', 102, 1, 4518, 270, 5),
(2, 'Data structure', 102, 2, 6541, 300, 3);

CREATE TABLE `issued_books` (
  `s_no` int(11) NOT NULL AUTO_INCREMENT,
  `book_no` int(11) NOT NULL,
  `book_name` varchar(200) NOT NULL,
  `book_author` varchar(200) NOT NULL,
  `member_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `issue_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expected_return_date` date DEFAULT NULL,
  `fine_amount` int(11) NOT NULL DEFAULT 0,
  `fine_status` varchar(20) NOT NULL DEFAULT 'Settled',
  PRIMARY KEY (`s_no`),
  CONSTRAINT `fk_issued_book_no` FOREIGN KEY (`book_no`) REFERENCES `books` (`book_no`) ON DELETE CASCADE,
  CONSTRAINT `fk_issued_member` FOREIGN KEY (`member_id`) REFERENCES `users` (`member_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `issued_books` (`s_no`, `book_no`, `book_name`, `book_author`, `member_id`, `status`, `issue_date`, `expected_return_date`) VALUES
(1, 6541, 'Data structure', 'M D Guptaa', 4, 1, '2023-01-01 10:00:00', '2023-01-15');

SET FOREIGN_KEY_CHECKS = 1;
