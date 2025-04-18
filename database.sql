-- Creating database
CREATE DATABASE IF NOT EXISTS event_planner;
USE event_planner;

-- Users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE events (
    event_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    event_name VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('Active', 'Completed', 'Cancelled') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Guests table
CREATE TABLE guests (
    guest_id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    rsvp_status ENUM('Pending', 'Confirmed', 'Declined') DEFAULT 'Pending',
    number_of_guests INT DEFAULT 1,
    dietary_restrictions TEXT,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);

-- Vendors table
CREATE TABLE vendors (
    vendor_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    contact_person VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Event Vendors
CREATE TABLE event_vendors (
    event_id INT,
    vendor_id INT,
    status ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') DEFAULT 'Pending',
    notes TEXT,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (vendor_id) REFERENCES vendors(vendor_id) ON DELETE CASCADE,
    PRIMARY KEY (event_id, vendor_id)
);

-- Budget Items table
CREATE TABLE budget_items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    category VARCHAR(100),
    description TEXT,
    amount DECIMAL(10,2),
    status ENUM('Pending', 'Paid', 'Cancelled') DEFAULT 'Pending',
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);

-- Timeline Items table
CREATE TABLE timeline_items (
    timeline_id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    task_name VARCHAR(255),
    due_date DATE,
    status ENUM('Pending', 'In Progress', 'Completed') DEFAULT 'Pending',
    notes TEXT,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);

-- Seating Arrangements table
CREATE TABLE seating_arrangements (
    table_id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    table_number INT,
    capacity INT,
    location VARCHAR(255),
    notes TEXT,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);

-- Guest Seating table 
CREATE TABLE guest_seating (
    guest_id INT,
    table_id INT,
    seat_number INT,
    FOREIGN KEY (guest_id) REFERENCES guests(guest_id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES seating_arrangements(table_id) ON DELETE CASCADE,
    PRIMARY KEY (guest_id, table_id)
);

-- Contact Messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('New', 'Read', 'Replied') DEFAULT 'New'
);