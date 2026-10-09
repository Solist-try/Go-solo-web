-- Additive update for an existing Go Solo database.
-- Import this once in phpMyAdmin after the earlier updates.
-- It gives a SAME or skill introduction a temporary private chair
-- after both people agree, and records whether each person wants to keep it.
-- It does not drop tables or rewrite members, stories, or conversations.
-- A second import is safe. The script records path-2026-10-09 in schema_updates.
-- It does not read information_schema.

CREATE TABLE IF NOT EXISTS schema_updates (
  update_key VARCHAR(80) NOT NULL,
  applied_at DATETIME NOT NULL,
  PRIMARY KEY (update_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE conversations
  ADD COLUMN IF NOT EXISTS expires_at DATETIME NULL;

ALTER TABLE conversation_participants
  ADD COLUMN IF NOT EXISTS keep_chair TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE skill_links
  ADD COLUMN IF NOT EXISTS consent_from TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS consent_to TINYINT(1) NOT NULL DEFAULT 1;

INSERT INTO schema_updates (update_key, applied_at) VALUES ('path-2026-10-09', NOW())
ON DUPLICATE KEY UPDATE applied_at = applied_at;
