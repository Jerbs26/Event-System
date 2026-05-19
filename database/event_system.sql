-- Event Management System Database
-- Created for event-system project

CREATE DATABASE IF NOT EXISTS event_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE event_system;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Events table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    time TIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    capacity INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Registrations table
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_registration (user_id, event_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed admin account (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Administrator', 'admin@eventsystem.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Seed sample events
INSERT INTO events (title, description, date, time, location, capacity) VALUES
('Tech Summit 2025', 'Annual technology conference featuring industry leaders and innovators discussing the future of AI, cloud computing, and digital transformation.', '2025-08-15', '09:00:00', 'Manila Grand Ballroom, BGC Taguig', 500),
('Web Development Bootcamp', 'Intensive 2-day hands-on workshop covering modern web technologies including React, Node.js, and cloud deployment strategies.', '2025-07-20', '08:00:00', 'SM Aura, Taguig City', 80),
('Digital Marketing Workshop', 'Learn proven strategies for SEO, social media marketing, content creation, and analytics to grow your online presence.', '2025-07-28', '13:00:00', 'Bonifacio High Street, BGC', 120),
('Startup Pitch Night', 'An evening where aspiring entrepreneurs pitch their ideas to a panel of seasoned investors and mentors.', '2025-08-05', '18:00:00', 'The Collective, Poblacion Makati', 200),
('UI/UX Design Conference', 'Explore the latest trends in user interface and user experience design with workshops and talks from world-class designers.', '2025-09-10', '09:30:00', 'Shangri-La The Fort, BGC', 300);
