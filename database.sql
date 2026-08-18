-- Tea Estate Management System - MySQL Schema
-- Import this file via cPanel > phpMyAdmin into your database.
SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  phone VARCHAR(40),
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('Owner','Administrator','Estate Manager','Supervisor','Accountant','Viewer') NOT NULL DEFAULT 'Viewer',
  assigned_estate_id INT NULL,
  status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  avatar VARCHAR(255) NULL,
  last_login DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_users_role (role),
  INDEX idx_users_estate (assigned_estate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS estates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  code VARCHAR(40) NOT NULL,
  location VARCHAR(200),
  total_acres DECIMAL(10,2) DEFAULT 0,
  tea_acres DECIMAL(10,2) DEFAULT 0,
  description TEXT,
  manager VARCHAR(120),
  status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_estates_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  estate_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  code VARCHAR(40),
  acres DECIMAL(10,2) DEFAULT 0,
  clone VARCHAR(120),
  num_plants INT DEFAULT 0,
  planted_date DATE NULL,
  status VARCHAR(40) DEFAULT 'Active',
  notes TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (estate_id) REFERENCES estates(id) ON DELETE CASCADE,
  INDEX idx_sections_estate (estate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tea_clones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  code VARCHAR(40),
  description TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS assignment_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  is_plucking TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS employees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  emp_code VARCHAR(40) NOT NULL,
  full_name VARCHAR(160) NOT NULL,
  nic VARCHAR(40),
  phone VARCHAR(40),
  address VARCHAR(255),
  gender ENUM('Male','Female','Other') DEFAULT 'Male',
  dob DATE NULL,
  joining_date DATE NULL,
  employment_type VARCHAR(60),
  job_role VARCHAR(80),
  estate_id INT NULL,
  section_id INT NULL,
  daily_rate DECIMAL(10,2) DEFAULT 0,
  kg_rate DECIMAL(10,2) DEFAULT 0,
  overtime_rate DECIMAL(10,2) DEFAULT 0,
  bank_details VARCHAR(255),
  emergency_contact VARCHAR(120),
  status ENUM('Active','Inactive','On Leave','Terminated') DEFAULT 'Active',
  notes TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_emp_code (emp_code),
  FOREIGN KEY (estate_id) REFERENCES estates(id) ON DELETE SET NULL,
  FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
  INDEX idx_emp_estate (estate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS daily_assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  work_date DATE NOT NULL,
  estate_id INT NOT NULL,
  section_id INT NULL,
  employee_id INT NOT NULL,
  assignment_type_id INT NULL,
  assignment_type VARCHAR(80),
  start_time TIME NULL,
  end_time TIME NULL,
  kg DECIMAL(10,2) DEFAULT 0,
  rate DECIMAL(10,2) DEFAULT 0,
  allowance DECIMAL(10,2) DEFAULT 0,
  deduction DECIMAL(10,2) DEFAULT 0,
  cost DECIMAL(12,2) DEFAULT 0,
  supervisor VARCHAR(120),
  status VARCHAR(40) DEFAULT 'Recorded',
  notes TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (estate_id) REFERENCES estates(id) ON DELETE CASCADE,
  FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  INDEX idx_asg_date (work_date),
  INDEX idx_asg_estate (estate_id),
  INDEX idx_asg_section (section_id),
  INDEX idx_asg_emp (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS expense_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS expenses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  expense_date DATE NOT NULL,
  estate_id INT NOT NULL,
  section_id INT NULL,
  category_id INT NULL,
  category VARCHAR(80),
  supplier VARCHAR(120),
  description VARCHAR(255),
  quantity DECIMAL(10,2) DEFAULT 0,
  amount DECIMAL(12,2) DEFAULT 0,
  payment_method VARCHAR(60),
  reference VARCHAR(80),
  status ENUM('Draft','Pending','Approved','Rejected','Paid') DEFAULT 'Pending',
  entered_by VARCHAR(120),
  approved_by VARCHAR(120),
  notes TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (estate_id) REFERENCES estates(id) ON DELETE CASCADE,
  FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
  INDEX idx_exp_date (expense_date),
  INDEX idx_exp_estate (estate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payroll (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT NOT NULL,
  estate_id INT NULL,
  period_from DATE NOT NULL,
  period_to DATE NOT NULL,
  basic DECIMAL(12,2) DEFAULT 0,
  plucking_pay DECIMAL(12,2) DEFAULT 0,
  assignment_pay DECIMAL(12,2) DEFAULT 0,
  overtime DECIMAL(12,2) DEFAULT 0,
  allowances DECIMAL(12,2) DEFAULT 0,
  bonuses DECIMAL(12,2) DEFAULT 0,
  deductions DECIMAL(12,2) DEFAULT 0,
  advances DECIMAL(12,2) DEFAULT 0,
  gross DECIMAL(12,2) DEFAULT 0,
  net DECIMAL(12,2) DEFAULT 0,
  status ENUM('Draft','Calculated','Approved','Paid') DEFAULT 'Calculated',
  approved_by VARCHAR(120),
  approved_date DATE NULL,
  paid_date DATE NULL,
  payment_method VARCHAR(60),
  reference VARCHAR(80),
  notes TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  INDEX idx_pay_emp (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS fertilizer_cycles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  estate_id INT NOT NULL,
  section_id INT NULL,
  fertilizer_type VARCHAR(120),
  date_applied DATE NULL,
  next_due DATE NULL,
  quantity DECIMAL(10,2) DEFAULT 0,
  cost DECIMAL(12,2) DEFAULT 0,
  supplier VARCHAR(120),
  applied_by VARCHAR(120),
  notes TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (estate_id) REFERENCES estates(id) ON DELETE CASCADE,
  FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clearing_cycles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  estate_id INT NOT NULL,
  section_id INT NULL,
  date_cleared DATE NULL,
  next_due DATE NULL,
  assigned_workers VARCHAR(255),
  cost DECIMAL(12,2) DEFAULT 0,
  notes TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (estate_id) REFERENCES estates(id) ON DELETE CASCADE,
  FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS service_cycles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  service_name VARCHAR(120) NOT NULL,
  asset VARCHAR(120),
  estate_id INT NULL,
  section_id INT NULL,
  last_service_date DATE NULL,
  next_service_date DATE NULL,
  frequency VARCHAR(60),
  cost DECIMAL(12,2) DEFAULT 0,
  supplier VARCHAR(120),
  notes TEXT,
  status VARCHAR(40) DEFAULT 'Active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (estate_id) REFERENCES estates(id) ON DELETE SET NULL,
  FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reminders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  description TEXT,
  type VARCHAR(60),
  estate_id INT NULL,
  section_id INT NULL,
  due_date DATE NULL,
  priority ENUM('Low','Medium','High','Critical') DEFAULT 'Medium',
  assigned_user VARCHAR(120),
  status ENUM('Open','Completed') DEFAULT 'Open',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (estate_id) REFERENCES estates(id) ON DELETE SET NULL,
  FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
  INDEX idx_rem_due (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  skey VARCHAR(80) PRIMARY KEY,
  svalue VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS=1;

-- Seed data
INSERT INTO users (name,email,phone,password_hash,role,status)
VALUES ('Estate Owner','admin@estate.local','0000000000','__HASH__','Owner','Active');

INSERT INTO assignment_types (name,is_plucking) VALUES
('Tea Plucking',1),('Clearing',0),('Fertilizing',0),('Pruning',0),
('Weeding',0),('Spraying',0),('Planting',0),('Maintenance',0),
('Transport',0),('Other',0);

INSERT INTO expense_categories (name) VALUES
('Labour'),('Fertilizer'),('Clearing'),('Transport'),('Fuel'),
('Machinery'),('Maintenance'),('Chemicals'),('Equipment'),('Other');

INSERT INTO tea_clones (name,code,description) VALUES
('TRI 2026','TRI2026','High yield clone'),
('TRI 4049','TRI4049','Drought tolerant clone');

INSERT INTO settings (skey,svalue) VALUES ('tea_price_per_kg','300');
