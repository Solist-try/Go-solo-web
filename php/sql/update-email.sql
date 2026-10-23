-- Email verification and deletion confirmation.
-- Paste this in the phpMyAdmin SQL tab. It does not read information_schema.
-- It does not delete members, stories, or articles.
-- A second paste is safe. Members already here stay verified.
-- People who join after this paste stay unverified until they open the letter.

CREATE TABLE IF NOT EXISTS schema_updates (
  update_key VARCHAR(80) NOT NULL,
  applied_at DATETIME NOT NULL,
  PRIMARY KEY (update_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verified_at DATETIME NULL;

CREATE TABLE IF NOT EXISTS email_tokens (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  purpose ENUM('verify', 'delete') NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY email_tokens_hash (token_hash),
  KEY email_tokens_user (user_id, purpose, created_at),
  CONSTRAINT email_tokens_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE users AS u
LEFT JOIN schema_updates AS s ON s.update_key = 'email-verify-2026-10-23'
SET u.email_verified_at = u.created_at
WHERE u.email_verified_at IS NULL AND s.update_key IS NULL;

INSERT INTO schema_updates (update_key, applied_at) VALUES ('email-verify-2026-10-23', NOW())
ON DUPLICATE KEY UPDATE applied_at = applied_at;
