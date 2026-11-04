-- Remember when a member last opened each part of the house.
-- Paste these statements in the phpMyAdmin SQL tab.
-- Do not type a file name. Paste the statements below.
-- A second paste is safe. It does not remove members or posts.

CREATE TABLE IF NOT EXISTS section_visits (
  user_id INT UNSIGNED NOT NULL,
  section_key VARCHAR(20) NOT NULL,
  visited_at DATETIME NOT NULL,
  PRIMARY KEY (user_id, section_key),
  CONSTRAINT section_visits_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
