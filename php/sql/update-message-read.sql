-- Message read state, and a publication date for Reading Room articles.
-- Paste this in the phpMyAdmin SQL tab.
-- It does not delete members, messages, or articles.
-- A second paste is safe.
-- Published articles receive a publication date only on the first paste.
-- A message stays unread until the other person opens the conversation.

CREATE TABLE IF NOT EXISTS schema_updates (
  update_key VARCHAR(80) NOT NULL,
  applied_at DATETIME NOT NULL,
  PRIMARY KEY (update_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE conversation_messages ADD COLUMN IF NOT EXISTS read_at DATETIME NULL;

ALTER TABLE readings ADD COLUMN IF NOT EXISTS published_at DATETIME NULL;

UPDATE readings AS r
LEFT JOIN schema_updates AS s ON s.update_key = 'reading-published-2026-10-25'
SET r.published_at = r.updated_at
WHERE r.status = 'published' AND r.published_at IS NULL AND s.update_key IS NULL;

INSERT INTO schema_updates (update_key, applied_at) VALUES ('message-read-2026-10-25', NOW())
ON DUPLICATE KEY UPDATE applied_at = applied_at;

INSERT INTO schema_updates (update_key, applied_at) VALUES ('reading-published-2026-10-25', NOW())
ON DUPLICATE KEY UPDATE applied_at = applied_at;
