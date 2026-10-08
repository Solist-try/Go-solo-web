-- Additive update for an existing Go Solo database.
-- Import this once in phpMyAdmin after update-rooms.sql.
-- It adds waypoint copy fields and a settings timestamp.
-- It does not drop tables or rewrite stories, members, or reading-room articles.
-- A second import is safe. The script records content-2026-10-08 in schema_updates.

CREATE TABLE IF NOT EXISTS schema_updates (
  update_key VARCHAR(80) NOT NULL,
  applied_at DATETIME NOT NULL,
  PRIMARY KEY (update_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @settings_updated := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'settings' AND COLUMN_NAME = 'updated_at'
);
SET @settings_sql := IF(@settings_updated = 0, 'ALTER TABLE settings ADD COLUMN updated_at DATETIME NULL', 'SELECT 1');
PREPARE content_stmt FROM @settings_sql;
EXECUTE content_stmt;
DEALLOCATE PREPARE content_stmt;

SET @intro_line := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'waypoints' AND COLUMN_NAME = 'intro_line'
);
SET @intro_sql := IF(@intro_line = 0, 'ALTER TABLE waypoints ADD COLUMN intro_line VARCHAR(255) NOT NULL DEFAULT '''' AFTER description', 'SELECT 1');
PREPARE content_stmt FROM @intro_sql;
EXECUTE content_stmt;
DEALLOCATE PREPARE content_stmt;

SET @food_intro := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'waypoints' AND COLUMN_NAME = 'food_intro'
);
SET @food_sql := IF(@food_intro = 0, 'ALTER TABLE waypoints ADD COLUMN food_intro TEXT NULL AFTER intro_line', 'SELECT 1');
PREPARE content_stmt FROM @food_sql;
EXECUTE content_stmt;
DEALLOCATE PREPARE content_stmt;

SET @discussion_prompt := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'waypoints' AND COLUMN_NAME = 'discussion_prompt'
);
SET @prompt_sql := IF(@discussion_prompt = 0, 'ALTER TABLE waypoints ADD COLUMN discussion_prompt TEXT NULL AFTER food_intro', 'SELECT 1');
PREPARE content_stmt FROM @prompt_sql;
EXECUTE content_stmt;
DEALLOCATE PREPARE content_stmt;

SET @discussion_cta := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'waypoints' AND COLUMN_NAME = 'discussion_cta'
);
SET @cta_sql := IF(@discussion_cta = 0, 'ALTER TABLE waypoints ADD COLUMN discussion_cta VARCHAR(80) NOT NULL DEFAULT '''' AFTER discussion_prompt', 'SELECT 1');
PREPARE content_stmt FROM @cta_sql;
EXECUTE content_stmt;
DEALLOCATE PREPARE content_stmt;

SET @discussion_empty := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'waypoints' AND COLUMN_NAME = 'discussion_empty'
);
SET @empty_sql := IF(@discussion_empty = 0, 'ALTER TABLE waypoints ADD COLUMN discussion_empty VARCHAR(500) NOT NULL DEFAULT '''' AFTER discussion_cta', 'SELECT 1');
PREPARE content_stmt FROM @empty_sql;
EXECUTE content_stmt;
DEALLOCATE PREPARE content_stmt;

INSERT INTO schema_updates (update_key, applied_at) VALUES ('content-2026-10-08', NOW())
ON DUPLICATE KEY UPDATE applied_at = applied_at;
