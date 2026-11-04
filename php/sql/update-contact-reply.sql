-- Let a steward reply to a contact message, and keep a private draft.
-- Paste these statements in the phpMyAdmin SQL tab.
-- Do not type a file name. Paste the statements below.
-- A second paste is safe. It does not delete messages.

ALTER TABLE contact_messages
  ADD COLUMN IF NOT EXISTS subject VARCHAR(160) NOT NULL DEFAULT '' AFTER email;

CREATE TABLE IF NOT EXISTS contact_replies (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  message_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  subject VARCHAR(180) NOT NULL DEFAULT '',
  body MEDIUMTEXT NOT NULL,
  sent_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY contact_replies_message (message_id, sent_at),
  CONSTRAINT contact_replies_message_fk FOREIGN KEY (message_id) REFERENCES contact_messages (id) ON DELETE CASCADE,
  CONSTRAINT contact_replies_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
