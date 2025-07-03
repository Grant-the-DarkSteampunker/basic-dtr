# basic-dtr
DTR from TEMU


-- Table for employees
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) NOT NULL UNIQUE,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for attendance records
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `log_date` date NOT NULL,
  `time_in` time NOT NULL,
  `time_out` time DEFAULT NULL,
  `status` int(1) NOT NULL COMMENT '0=time_in, 1=time_out',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add some sample employees
INSERT INTO `employees` (`employee_id`, `name`) VALUES
('EMP-001', 'John Doe'),
('EMP-002', 'Jane Smith'),
('EMP-003', 'Peter Jones');
