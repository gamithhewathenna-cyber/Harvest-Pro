-- Tea Estate Management System - MySQL Schema
-- Import this file via cPanel > phpMyAdmin into your database.
SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  phone VARCHAR(40),
  address VARCHAR(255) NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('Owner','Administrator','Estate Manager','Supervisor','Accountant','Viewer') NOT NULL DEFAULT 'Viewer',
  assigned_estate_ids VARCHAR(255) NULL COMMENT 'comma-separated estate ids',
  owner_user_id INT NULL COMMENT 'NULL = tenant root (self); otherwise the Owner this sub-user belongs to',
  is_platform_admin TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'can manage coupons; not a per-tenant role',
  status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  avatar VARCHAR(255) NULL,
  last_login DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_users_role (role),
  INDEX idx_users_owner (owner_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS coupons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL UNIQUE,
  status ENUM('Unused','Used') NOT NULL DEFAULT 'Unused',
  used_by_user_id INT NULL,
  used_for_estate_id INT NULL,
  used_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_coupon_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS estates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_user_id INT NOT NULL,
  name VARCHAR(160) NOT NULL,
  code VARCHAR(40) NOT NULL,
  location VARCHAR(200),
  total_acres DECIMAL(10,2) DEFAULT 0,
  tea_acres DECIMAL(10,2) DEFAULT 0,
  description TEXT,
  manager VARCHAR(120),
  status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_estates_code (code),
  INDEX idx_estates_owner (owner_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_user_id INT NOT NULL,
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
  INDEX idx_sections_estate (estate_id),
  INDEX idx_sections_owner (owner_user_id)
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
  owner_user_id INT NOT NULL,
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
  INDEX idx_emp_estate (estate_id),
  INDEX idx_emp_owner (owner_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS daily_assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_user_id INT NOT NULL,
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
  INDEX idx_asg_emp (employee_id),
  INDEX idx_asg_owner (owner_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS expense_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS expenses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_user_id INT NOT NULL,
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
  INDEX idx_exp_estate (estate_id),
  INDEX idx_exp_owner (owner_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payroll (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_user_id INT NOT NULL,
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
  INDEX idx_pay_emp (employee_id),
  INDEX idx_pay_owner (owner_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS fertilizer_cycles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_user_id INT NOT NULL,
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
  FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
  INDEX idx_fert_owner (owner_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clearing_cycles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_user_id INT NOT NULL,
  estate_id INT NOT NULL,
  section_id INT NULL,
  date_cleared DATE NULL,
  next_due DATE NULL,
  assigned_workers VARCHAR(255),
  cost DECIMAL(12,2) DEFAULT 0,
  notes TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (estate_id) REFERENCES estates(id) ON DELETE CASCADE,
  FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
  INDEX idx_clear_owner (owner_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS service_cycles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_user_id INT NOT NULL,
  service_name VARCHAR(120) NOT NULL,
  description TEXT,
  unit_type VARCHAR(60),
  rate_per_unit DECIMAL(12,2) DEFAULT 0,
  status VARCHAR(40) DEFAULT 'Active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_svc_owner (owner_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reminders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_user_id INT NOT NULL,
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
  INDEX idx_rem_due (due_date),
  INDEX idx_rem_owner (owner_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  owner_user_id INT NOT NULL,
  skey VARCHAR(80) NOT NULL,
  svalue VARCHAR(255),
  PRIMARY KEY (owner_user_id, skey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Platform-level (not per-tenant) tables

CREATE TABLE IF NOT EXISTS platform_settings (
  skey VARCHAR(80) PRIMARY KEY,
  svalue VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS email_settings (
  id TINYINT PRIMARY KEY DEFAULT 1,
  smtp_host VARCHAR(160),
  smtp_port INT DEFAULT 587,
  smtp_user VARCHAR(160),
  smtp_pass VARCHAR(255),
  from_email VARCHAR(160),
  from_name VARCHAR(120),
  encryption ENUM('none','tls','ssl') DEFAULT 'tls'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS support_tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_user_id INT NOT NULL,
  subject VARCHAR(200) NOT NULL,
  status ENUM('Open','Answered','Closed') NOT NULL DEFAULT 'Open',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_ticket_owner (owner_user_id),
  INDEX idx_ticket_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS support_ticket_replies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ticket_id INT NOT NULL,
  user_id INT NOT NULL,
  is_admin_reply TINYINT(1) NOT NULL DEFAULT 0,
  message TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
  INDEX idx_reply_ticket (ticket_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS=1;

-- Seed data
INSERT INTO users (name,email,phone,password_hash,role,is_platform_admin,status)
VALUES ('Estate Owner','admin@estate.local','0000000000','__HASH__','Owner',1,'Active');

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

INSERT INTO settings (owner_user_id,skey,svalue)
SELECT id,'tea_price_per_kg','300' FROM users WHERE email='admin@estate.local';
