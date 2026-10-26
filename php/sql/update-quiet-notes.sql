-- Remembers that a closing reminder was already shown.
-- Paste this in the phpMyAdmin SQL tab while the Go Solo database is selected.
-- Paste the statement below. Do not type the file name.
-- A second paste is safe. It does not delete members or messages.

CREATE TABLE IF NOT EXISTS quiet_notes (
  user_id INT UNSIGNED NOT NULL,
  note_key VARCHAR(40) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (user_id, note_key),
  CONSTRAINT quiet_notes_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
