-- Additive update for an existing Go Solo database.
-- Import this once in phpMyAdmin after update-rooms.sql.
-- It adds private conversations and a profile preference.
-- It does not drop tables or rewrite stories, members, or reading-room articles.
-- A second import is safe. The script records talk-2026-10-08 in schema_updates.

CREATE TABLE IF NOT EXISTS schema_updates (
  update_key VARCHAR(80) NOT NULL,
  applied_at DATETIME NOT NULL,
  PRIMARY KEY (update_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @talk_open := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND COLUMN_NAME = 'conversations_open'
);
SET @talk_open_sql := IF(@talk_open = 0, 'ALTER TABLE profiles ADD COLUMN conversations_open TINYINT(1) NOT NULL DEFAULT 1', 'SELECT 1');
PREPARE talk_stmt FROM @talk_open_sql;
EXECUTE talk_stmt;
DEALLOCATE PREPARE talk_stmt;

SET @talk_held := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND COLUMN_NAME = 'conversations_held'
);
SET @talk_held_sql := IF(@talk_held = 0, 'ALTER TABLE profiles ADD COLUMN conversations_held TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE talk_stmt FROM @talk_held_sql;
EXECUTE talk_stmt;
DEALLOCATE PREPARE talk_stmt;

CREATE TABLE IF NOT EXISTS conversations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  context_type ENUM('seed', 'skill', 'introduction', 'waypoint', 'campfire', 'story', 'member') NOT NULL,
  context_kind VARCHAR(20) NOT NULL DEFAULT '',
  context_id INT UNSIGNED NOT NULL,
  context_label VARCHAR(160) NOT NULL DEFAULT '',
  context_note TEXT NOT NULL,
  opened_by INT UNSIGNED NOT NULL,
  status ENUM('requested', 'open', 'declined') NOT NULL DEFAULT 'open',
  closed TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY conversations_context (context_type, context_id),
  KEY conversations_opened (opened_by),
  CONSTRAINT conversations_opened_fk FOREIGN KEY (opened_by) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS conversation_participants (
  conversation_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (conversation_id, user_id),
  KEY conversation_participants_user (user_id),
  CONSTRAINT conversation_participants_conversation_fk FOREIGN KEY (conversation_id) REFERENCES conversations (id) ON DELETE CASCADE,
  CONSTRAINT conversation_participants_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS conversation_messages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  conversation_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY conversation_messages_room (conversation_id, id),
  CONSTRAINT conversation_messages_conversation_fk FOREIGN KEY (conversation_id) REFERENCES conversations (id) ON DELETE CASCADE,
  CONSTRAINT conversation_messages_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE reports MODIFY target_type ENUM('story', 'campfire', 'comment', 'profile', 'waypoint', 'conversation') NOT NULL;
ALTER TABLE content_images MODIFY parent_type ENUM('story', 'campfire', 'waypoint', 'comment', 'conversation') NOT NULL;

ALTER TABLE conversations MODIFY context_type ENUM('seed', 'skill', 'introduction', 'waypoint', 'campfire', 'story', 'member') NOT NULL;

SET @talk_status := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'conversations' AND COLUMN_NAME = 'status'
);
SET @talk_status_sql := IF(@talk_status = 0, 'ALTER TABLE conversations ADD COLUMN status ENUM(''requested'', ''open'', ''declined'') NOT NULL DEFAULT ''open'' AFTER opened_by', 'SELECT 1');
PREPARE talk_stmt FROM @talk_status_sql;
EXECUTE talk_stmt;
DEALLOCATE PREPARE talk_stmt;

SET @talk_pref := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND COLUMN_NAME = 'conversations_pref'
);
SET @talk_pref_sql := IF(@talk_pref = 0, 'ALTER TABLE profiles ADD COLUMN conversations_pref ENUM(''anyone'', ''context'', ''none'') NOT NULL DEFAULT ''context'' AFTER show_location', 'SELECT 1');
PREPARE talk_stmt FROM @talk_pref_sql;
EXECUTE talk_stmt;
DEALLOCATE PREPARE talk_stmt;

SET @talk_choice := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND COLUMN_NAME = 'conversations_choice_made'
);
SET @talk_choice_sql := IF(@talk_choice = 0, 'ALTER TABLE profiles ADD COLUMN conversations_choice_made TINYINT(1) NOT NULL DEFAULT 0 AFTER conversations_pref', 'SELECT 1');
PREPARE talk_stmt FROM @talk_choice_sql;
EXECUTE talk_stmt;
DEALLOCATE PREPARE talk_stmt;

UPDATE profiles SET conversations_pref = 'context' WHERE conversations_choice_made = 0;

CREATE TABLE IF NOT EXISTS member_blocks (
  user_id INT UNSIGNED NOT NULL,
  blocked_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (user_id, blocked_id),
  KEY member_blocks_blocked (blocked_id),
  CONSTRAINT member_blocks_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT member_blocks_blocked_fk FOREIGN KEY (blocked_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO schema_updates (update_key, applied_at) VALUES ('talk-2026-10-08', NOW())
ON DUPLICATE KEY UPDATE applied_at = applied_at;

INSERT INTO schema_updates (update_key, applied_at) VALUES ('talk-choice-2026-10-08', NOW())
ON DUPLICATE KEY UPDATE applied_at = applied_at;
