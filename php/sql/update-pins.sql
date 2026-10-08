-- Additive update for an existing Go Solo database.
-- Import this once in phpMyAdmin after the earlier updates.
-- It lets a steward pin one Campfire post, one discussion in each Waypoint,
-- and one Out There story, and feature Reading Room articles in an order.
-- It does not drop tables or rewrite stories, members, or articles.
-- A second import is safe. The script records pins-2026-10-08 in schema_updates.

CREATE TABLE IF NOT EXISTS schema_updates (
  update_key VARCHAR(80) NOT NULL,
  applied_at DATETIME NOT NULL,
  PRIMARY KEY (update_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @pin_campfire := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'campfire_posts' AND COLUMN_NAME = 'pinned'
);
SET @pin_sql := IF(@pin_campfire = 0, 'ALTER TABLE campfire_posts ADD COLUMN pinned TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN pinned_at DATETIME NULL, ADD KEY campfire_pinned (pinned, pinned_at)', 'SELECT 1');
PREPARE pin_stmt FROM @pin_sql;
EXECUTE pin_stmt;
DEALLOCATE PREPARE pin_stmt;

SET @pin_story := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stories' AND COLUMN_NAME = 'pinned'
);
SET @pin_sql := IF(@pin_story = 0, 'ALTER TABLE stories ADD COLUMN pinned TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN pinned_at DATETIME NULL, ADD KEY stories_pinned (pinned, pinned_at)', 'SELECT 1');
PREPARE pin_stmt FROM @pin_sql;
EXECUTE pin_stmt;
DEALLOCATE PREPARE pin_stmt;

SET @pin_posts := (
  SELECT COUNT(*) FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'waypoint_posts'
);
SET @pin_way := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'waypoint_posts' AND COLUMN_NAME = 'pinned_at'
);
SET @pin_sql := IF(@pin_posts = 1 AND @pin_way = 0, 'ALTER TABLE waypoint_posts ADD COLUMN pinned_at DATETIME NULL', 'SELECT 1');
PREPARE pin_stmt FROM @pin_sql;
EXECUTE pin_stmt;
DEALLOCATE PREPARE pin_stmt;

SET @pin_sql := IF(@pin_posts = 1, 'UPDATE waypoint_posts AS older JOIN (SELECT waypoint_id, MAX(id) AS keep_id FROM waypoint_posts WHERE pinned = 1 GROUP BY waypoint_id) AS kept ON kept.waypoint_id = older.waypoint_id SET older.pinned = 0 WHERE older.pinned = 1 AND older.id <> kept.keep_id', 'SELECT 1');
PREPARE pin_stmt FROM @pin_sql;
EXECUTE pin_stmt;
DEALLOCATE PREPARE pin_stmt;

SET @pin_sql := IF(@pin_posts = 1, 'UPDATE waypoint_posts SET pinned_at = created_at WHERE pinned = 1 AND pinned_at IS NULL', 'SELECT 1');
PREPARE pin_stmt FROM @pin_sql;
EXECUTE pin_stmt;
DEALLOCATE PREPARE pin_stmt;

SET @pin_featured := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'readings' AND COLUMN_NAME = 'featured'
);
SET @pin_sql := IF(@pin_featured = 0, 'ALTER TABLE readings ADD COLUMN featured TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN featured_order INT NOT NULL DEFAULT 0, ADD KEY readings_featured (featured, featured_order)', 'SELECT 1');
PREPARE pin_stmt FROM @pin_sql;
EXECUTE pin_stmt;
DEALLOCATE PREPARE pin_stmt;

CREATE TABLE IF NOT EXISTS pin_hides (
  user_id INT UNSIGNED NOT NULL,
  subject_type ENUM('campfire', 'waypoint', 'story') NOT NULL,
  subject_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (user_id, subject_type, subject_id),
  KEY pin_hides_subject (subject_type, subject_id),
  CONSTRAINT pin_hides_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO schema_updates (update_key, applied_at) VALUES ('pins-2026-10-08', NOW())
ON DUPLICATE KEY UPDATE applied_at = applied_at;
