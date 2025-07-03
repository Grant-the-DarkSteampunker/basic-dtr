# basic-dtr
DTR from TEMU


-- Drop the old tables if they exist
DROP TABLE IF EXISTS `attendance`;
DROP TABLE IF EXISTS `employees`;

-- Create a new 'employees' table using name as the primary key
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL UNIQUE,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create a new 'attendance' table
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `log_date` date NOT NULL,
  `time_in` time NOT NULL,
  `time_out` time DEFAULT NULL,
  `status` int(1) NOT NULL COMMENT '0=time_in, 1=time_out',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert the new list of names
INSERT INTO `employees` (`name`) VALUES
('Sample Dude'),
