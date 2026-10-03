-- Libraray database. Import into phpMyAdmin (it creates app_db if missing).
CREATE DATABASE IF NOT EXISTS `app_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `app_db`;

-- Members (your prof's table; kept as-is)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `author` varchar(150) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `copies` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `isbn` (`isbn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `borrowings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `borrowed_at` date NOT NULL,
  `due_date` date NOT NULL,
  `returned_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `books` (`title`, `author`, `isbn`, `copies`) VALUES
('Noli Me Tangere', 'Jose Rizal', '9780140445152', 2),
('El Filibusterismo', 'Jose Rizal', '9780824805227', 1),
('Introduction to Algorithms', 'Thomas H. Cormen', '9780262046305', 3),
('Computer Networking: A Top-Down Approach', 'James Kurose', '9780136681557', 2);

-- Sample borrowers and records (safe to delete)
INSERT IGNORE INTO `users` (`name`, `email`) VALUES
('Juan Cruz', 'juan.cruz@example.com'),
('Ana Santos', 'ana.santos@example.com');

INSERT INTO `borrowings` (`book_id`, `user_id`, `borrowed_at`, `due_date`, `returned_at`)
SELECT b.id, u.id, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), NULL
FROM books b, users u WHERE b.title='Introduction to Algorithms' AND u.email='juan.cruz@example.com'
AND NOT EXISTS (SELECT 1 FROM borrowings);

INSERT INTO `borrowings` (`book_id`, `user_id`, `borrowed_at`, `due_date`, `returned_at`)
SELECT b.id, u.id, DATE_SUB(CURDATE(), INTERVAL 1 DAY), DATE_ADD(CURDATE(), INTERVAL 6 DAY), NOW()
FROM books b, users u WHERE b.title='Noli Me Tangere' AND u.email='ana.santos@example.com'
AND (SELECT COUNT(*) FROM borrowings) = 1;
