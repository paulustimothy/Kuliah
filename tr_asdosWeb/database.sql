CREATE DATABASE IF NOT EXISTS pet_care CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE pet_care;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pet_name VARCHAR(100) NOT NULL,
    pet_type VARCHAR(60) NOT NULL,
    service_type ENUM('mandi', 'grooming', 'penitipan') NOT NULL,
    status ENUM('dititip', 'grooming', 'selesai') NOT NULL DEFAULT 'dititip',
    start_date DATE NOT NULL,
    notes TEXT,
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (full_name, email, password, role)
SELECT 'Administrator', 'admin@local.test', '$2y$10$fqizGnOQ2mJ5Gm39BP50M.zQlgyC9HCHTJYZiwEYL1b7uo80GWj2S', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@local.test');

