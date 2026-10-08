-- Additive update for an existing Go Solo database.
-- Import this once in phpMyAdmin. It does not drop tables or rewrite stories.
-- A second import is safe. The script records itself in schema_updates as rooms-2026-10-08.
-- Do not import install.sql again. That file starts the database over.

CREATE TABLE IF NOT EXISTS schema_updates (
  update_key VARCHAR(80) NOT NULL,
  applied_at DATETIME NOT NULL,
  PRIMARY KEY (update_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @stories_body := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stories' AND COLUMN_NAME = 'body'
);
SET @stories_sql := IF(@stories_body = 0, 'ALTER TABLE stories ADD COLUMN body TEXT NULL', 'SELECT 1');
PREPARE rooms_stmt FROM @stories_sql;
EXECUTE rooms_stmt;
DEALLOCATE PREPARE rooms_stmt;

ALTER TABLE comments MODIFY target_type ENUM('story', 'campfire', 'waypoint') NOT NULL;
ALTER TABLE reports MODIFY target_type ENUM('story', 'campfire', 'comment', 'profile', 'waypoint') NOT NULL;

CREATE TABLE IF NOT EXISTS waypoint_posts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  waypoint_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  body TEXT NOT NULL,
  pinned TINYINT(1) NOT NULL DEFAULT 0,
  hidden TINYINT(1) NOT NULL DEFAULT 0,
  locked TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY waypoint_posts_room (waypoint_id, pinned, created_at),
  KEY waypoint_posts_user (user_id),
  CONSTRAINT waypoint_posts_waypoint_fk FOREIGN KEY (waypoint_id) REFERENCES waypoints (id) ON DELETE CASCADE,
  CONSTRAINT waypoint_posts_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS waypoint_readings (
  waypoint_id INT UNSIGNED NOT NULL,
  reading_id INT UNSIGNED NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (waypoint_id, reading_id),
  KEY waypoint_readings_reading (reading_id, sort_order),
  CONSTRAINT waypoint_readings_waypoint_fk FOREIGN KEY (waypoint_id) REFERENCES waypoints (id) ON DELETE CASCADE,
  CONSTRAINT waypoint_readings_reading_fk FOREIGN KEY (reading_id) REFERENCES readings (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS content_images (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  parent_type ENUM('story', 'campfire', 'waypoint', 'comment') NOT NULL,
  parent_id INT UNSIGNED NOT NULL,
  path VARCHAR(255) NOT NULL,
  alt VARCHAR(255) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY content_images_parent (parent_type, parent_id),
  KEY content_images_user (user_id),
  CONSTRAINT content_images_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO schema_updates (update_key, applied_at) VALUES ('rooms-2026-10-08', NOW());
