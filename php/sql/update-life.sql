-- Additive update for an existing Go Solo database.
-- Import this once in phpMyAdmin after update-talk.sql.
-- It adds lifecycles, pause choices, life seasons, outcomes, and steward history.
-- It does not drop tables or rewrite stories, members, or reading-room articles.
-- A second import is safe. The script records life-2026-10-08 in schema_updates.

CREATE TABLE IF NOT EXISTS schema_updates (
  update_key VARCHAR(80) NOT NULL,
  applied_at DATETIME NOT NULL,
  PRIMARY KEY (update_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @life_pause_intro := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND COLUMN_NAME = 'pause_introductions'
);
SET @life_sql := IF(@life_pause_intro = 0, 'ALTER TABLE profiles ADD COLUMN pause_introductions TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_pause_seed := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND COLUMN_NAME = 'pause_seed_support'
);
SET @life_sql := IF(@life_pause_seed = 0, 'ALTER TABLE profiles ADD COLUMN pause_seed_support TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_pause_skill := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND COLUMN_NAME = 'pause_skill_interest'
);
SET @life_sql := IF(@life_pause_skill = 0, 'ALTER TABLE profiles ADD COLUMN pause_skill_interest TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_mute := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND COLUMN_NAME = 'mute_notices'
);
SET @life_sql := IF(@life_mute = 0, 'ALTER TABLE profiles ADD COLUMN mute_notices TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_show_trust := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND COLUMN_NAME = 'show_trust'
);
SET @life_sql := IF(@life_show_trust = 0, 'ALTER TABLE profiles ADD COLUMN show_trust TINYINT(1) NOT NULL DEFAULT 1', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_season_public := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND COLUMN_NAME = 'life_season_public'
);
SET @life_sql := IF(@life_season_public = 0, 'ALTER TABLE profiles ADD COLUMN life_season_public TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

CREATE TABLE IF NOT EXISTS life_seasons (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  label VARCHAR(120) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  archived TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY life_seasons_label (label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO life_seasons (label, sort_order) VALUES
  ('Starting over', 10),
  ('Building friendships', 20),
  ('Living independently for the first time', 30),
  ('Growing confidence', 40),
  ('Learning practical independence', 50),
  ('Finding community', 60),
  ('Exploring what comes next', 70),
  ('Prefer not to say', 80)
ON DUPLICATE KEY UPDATE sort_order = sort_order;

SET @life_season_id := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND COLUMN_NAME = 'life_season_id'
);
SET @life_sql := IF(@life_season_id = 0, 'ALTER TABLE profiles ADD COLUMN life_season_id INT UNSIGNED NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_fk := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profiles' AND CONSTRAINT_NAME = 'profiles_season_fk'
);
SET @life_sql := IF(@life_fk = 0, 'ALTER TABLE profiles ADD CONSTRAINT profiles_season_fk FOREIGN KEY (life_season_id) REFERENCES life_seasons (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

ALTER TABLE growing_seeds MODIFY status ENUM('active', 'resting', 'grown', 'archived') NOT NULL DEFAULT 'active';

SET @life_vis := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'growing_seeds' AND COLUMN_NAME = 'grown_visibility'
);
SET @life_sql := IF(@life_vis = 0, 'ALTER TABLE growing_seeds ADD COLUMN grown_visibility ENUM(''garden'', ''private'') NOT NULL DEFAULT ''private''', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_reflect := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'growing_seeds' AND COLUMN_NAME = 'reflection'
);
SET @life_sql := IF(@life_reflect = 0, 'ALTER TABLE growing_seeds ADD COLUMN reflection TEXT NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_seed_at := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'growing_seeds' AND COLUMN_NAME = 'status_at'
);
SET @life_sql := IF(@life_seed_at = 0, 'ALTER TABLE growing_seeds ADD COLUMN status_at DATETIME NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_seed_by := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'growing_seeds' AND COLUMN_NAME = 'status_by'
);
SET @life_sql := IF(@life_seed_by = 0, 'ALTER TABLE growing_seeds ADD COLUMN status_by INT UNSIGNED NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_fk := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'growing_seeds' AND CONSTRAINT_NAME = 'growing_status_by_fk'
);
SET @life_sql := IF(@life_fk = 0, 'ALTER TABLE growing_seeds ADD CONSTRAINT growing_status_by_fk FOREIGN KEY (status_by) REFERENCES users (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_idx := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'growing_seeds' AND INDEX_NAME = 'growing_status'
);
SET @life_sql := IF(@life_idx = 0, 'ALTER TABLE growing_seeds ADD KEY growing_status (status)', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_offer_status := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'skill_offers' AND COLUMN_NAME = 'listing_status'
);
SET @life_sql := IF(@life_offer_status = 0, 'ALTER TABLE skill_offers ADD COLUMN listing_status ENUM(''open'', ''paused'', ''completed'', ''archived'') NOT NULL DEFAULT ''open''', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_offer_at := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'skill_offers' AND COLUMN_NAME = 'status_at'
);
SET @life_sql := IF(@life_offer_at = 0, 'ALTER TABLE skill_offers ADD COLUMN status_at DATETIME NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_offer_by := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'skill_offers' AND COLUMN_NAME = 'status_by'
);
SET @life_sql := IF(@life_offer_by = 0, 'ALTER TABLE skill_offers ADD COLUMN status_by INT UNSIGNED NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

UPDATE skill_offers SET listing_status = 'archived' WHERE archived = 1 AND listing_status = 'open';

SET @life_req_status := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'skill_requests' AND COLUMN_NAME = 'listing_status'
);
SET @life_sql := IF(@life_req_status = 0, 'ALTER TABLE skill_requests ADD COLUMN listing_status ENUM(''open'', ''paused'', ''completed'', ''archived'') NOT NULL DEFAULT ''open''', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_req_at := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'skill_requests' AND COLUMN_NAME = 'status_at'
);
SET @life_sql := IF(@life_req_at = 0, 'ALTER TABLE skill_requests ADD COLUMN status_at DATETIME NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_req_by := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'skill_requests' AND COLUMN_NAME = 'status_by'
);
SET @life_sql := IF(@life_req_by = 0, 'ALTER TABLE skill_requests ADD COLUMN status_by INT UNSIGNED NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

UPDATE skill_requests SET listing_status = 'archived' WHERE archived = 1 AND listing_status = 'open';

SET @life_idx := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'skill_offers' AND INDEX_NAME = 'skill_offers_status'
);
SET @life_sql := IF(@life_idx = 0, 'ALTER TABLE skill_offers ADD KEY skill_offers_status (listing_status)', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_idx := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'skill_requests' AND INDEX_NAME = 'skill_requests_status'
);
SET @life_sql := IF(@life_idx = 0, 'ALTER TABLE skill_requests ADD KEY skill_requests_status (listing_status)', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_fk := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'skill_offers' AND CONSTRAINT_NAME = 'skill_offers_status_by_fk'
);
SET @life_sql := IF(@life_fk = 0, 'ALTER TABLE skill_offers ADD CONSTRAINT skill_offers_status_by_fk FOREIGN KEY (status_by) REFERENCES users (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_fk := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'skill_requests' AND CONSTRAINT_NAME = 'skill_requests_status_by_fk'
);
SET @life_sql := IF(@life_fk = 0, 'ALTER TABLE skill_requests ADD CONSTRAINT skill_requests_status_by_fk FOREIGN KEY (status_by) REFERENCES users (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

CREATE TABLE IF NOT EXISTS skill_parts (
  conversation_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  ended TINYINT(1) NOT NULL DEFAULT 0,
  ended_at DATETIME NULL,
  PRIMARY KEY (conversation_id, user_id),
  KEY skill_parts_user (user_id, ended),
  CONSTRAINT skill_parts_conversation_fk FOREIGN KEY (conversation_id) REFERENCES conversations (id) ON DELETE CASCADE,
  CONSTRAINT skill_parts_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE same_matches MODIFY status ENUM('suggested', 'approved', 'awaiting', 'open', 'closed', 'declined', 'archived') NOT NULL DEFAULT 'suggested';

SET @life_consent_a := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'same_matches' AND COLUMN_NAME = 'consent_a'
);
SET @life_sql := IF(@life_consent_a = 0, 'ALTER TABLE same_matches ADD COLUMN consent_a TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_consent_b := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'same_matches' AND COLUMN_NAME = 'consent_b'
);
SET @life_sql := IF(@life_consent_b = 0, 'ALTER TABLE same_matches ADD COLUMN consent_b TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_feed_a := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'same_matches' AND COLUMN_NAME = 'feedback_a'
);
SET @life_sql := IF(@life_feed_a = 0, 'ALTER TABLE same_matches ADD COLUMN feedback_a VARCHAR(40) NOT NULL DEFAULT ''''', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_feed_b := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'same_matches' AND COLUMN_NAME = 'feedback_b'
);
SET @life_sql := IF(@life_feed_b = 0, 'ALTER TABLE same_matches ADD COLUMN feedback_b VARCHAR(40) NOT NULL DEFAULT ''''', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_closed_by := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'same_matches' AND COLUMN_NAME = 'closed_by'
);
SET @life_sql := IF(@life_closed_by = 0, 'ALTER TABLE same_matches ADD COLUMN closed_by INT UNSIGNED NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_match_at := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'same_matches' AND COLUMN_NAME = 'status_at'
);
SET @life_sql := IF(@life_match_at = 0, 'ALTER TABLE same_matches ADD COLUMN status_at DATETIME NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

UPDATE same_matches SET status = 'open', consent_a = 1, consent_b = 1 WHERE status = 'approved';
UPDATE same_matches SET status = 'awaiting' WHERE status = 'suggested';

SET @life_fk := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'same_matches' AND CONSTRAINT_NAME = 'same_matches_closed_by_fk'
);
SET @life_sql := IF(@life_fk = 0, 'ALTER TABLE same_matches ADD CONSTRAINT same_matches_closed_by_fk FOREIGN KEY (closed_by) REFERENCES users (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_member_closed := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'conversations' AND COLUMN_NAME = 'member_closed'
);
SET @life_sql := IF(@life_member_closed = 0, 'ALTER TABLE conversations ADD COLUMN member_closed TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_talk_by := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'conversations' AND COLUMN_NAME = 'closed_by'
);
SET @life_sql := IF(@life_talk_by = 0, 'ALTER TABLE conversations ADD COLUMN closed_by INT UNSIGNED NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_fk := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'conversations' AND CONSTRAINT_NAME = 'conversations_closed_by_fk'
);
SET @life_sql := IF(@life_fk = 0, 'ALTER TABLE conversations ADD CONSTRAINT conversations_closed_by_fk FOREIGN KEY (closed_by) REFERENCES users (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_idx := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'conversations' AND INDEX_NAME = 'conversations_member_closed'
);
SET @life_sql := IF(@life_idx = 0, 'ALTER TABLE conversations ADD KEY conversations_member_closed (member_closed)', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_archived := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'conversation_participants' AND COLUMN_NAME = 'archived'
);
SET @life_sql := IF(@life_archived = 0, 'ALTER TABLE conversation_participants ADD COLUMN archived TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_muted := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'conversation_participants' AND COLUMN_NAME = 'muted'
);
SET @life_sql := IF(@life_muted = 0, 'ALTER TABLE conversation_participants ADD COLUMN muted TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_served := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'conversation_participants' AND COLUMN_NAME = 'served'
);
SET @life_sql := IF(@life_served = 0, 'ALTER TABLE conversation_participants ADD COLUMN served TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_idx := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'conversation_participants' AND INDEX_NAME = 'conversation_participants_archived'
);
SET @life_sql := IF(@life_idx = 0, 'ALTER TABLE conversation_participants ADD KEY conversation_participants_archived (user_id, archived)', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_cat := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports' AND COLUMN_NAME = 'category'
);
SET @life_sql := IF(@life_cat = 0, 'ALTER TABLE reports ADD COLUMN category VARCHAR(40) NOT NULL DEFAULT ''''', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_resolution := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports' AND COLUMN_NAME = 'resolution'
);
SET @life_sql := IF(@life_resolution = 0, 'ALTER TABLE reports ADD COLUMN resolution TEXT NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_acted := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports' AND COLUMN_NAME = 'acted_by'
);
SET @life_sql := IF(@life_acted = 0, 'ALTER TABLE reports ADD COLUMN acted_by INT UNSIGNED NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_acted_at := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports' AND COLUMN_NAME = 'acted_at'
);
SET @life_sql := IF(@life_acted_at = 0, 'ALTER TABLE reports ADD COLUMN acted_at DATETIME NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

SET @life_fk := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reports' AND CONSTRAINT_NAME = 'reports_acted_by_fk'
);
SET @life_sql := IF(@life_fk = 0, 'ALTER TABLE reports ADD CONSTRAINT reports_acted_by_fk FOREIGN KEY (acted_by) REFERENCES users (id) ON DELETE SET NULL', 'SELECT 1');
PREPARE life_stmt FROM @life_sql;
EXECUTE life_stmt;
DEALLOCATE PREPARE life_stmt;

CREATE TABLE IF NOT EXISTS journey_hides (
  user_id INT UNSIGNED NOT NULL,
  subject_type VARCHAR(40) NOT NULL,
  subject_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (user_id, subject_type, subject_id),
  CONSTRAINT journey_hides_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS outcomes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  subject_type VARCHAR(40) NOT NULL,
  subject_id INT UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  share ENUM('private', 'garden', 'offered') NOT NULL DEFAULT 'private',
  consent ENUM('none', 'offered', 'review', 'permission', 'approved', 'published', 'withdrawn', 'declined') NOT NULL DEFAULT 'none',
  public_body TEXT NULL,
  member_confirmed TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY outcomes_subject (user_id, subject_type, subject_id),
  KEY outcomes_consent (consent, updated_at),
  KEY outcomes_user (user_id),
  CONSTRAINT outcomes_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lifecycle_events (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  subject_type VARCHAR(40) NOT NULL,
  subject_id INT UNSIGNED NOT NULL,
  from_status VARCHAR(40) NOT NULL DEFAULT '',
  to_status VARCHAR(40) NOT NULL DEFAULT '',
  user_id INT UNSIGNED NULL,
  note VARCHAR(255) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY lifecycle_subject (subject_type, subject_id, id),
  KEY lifecycle_user (user_id),
  CONSTRAINT lifecycle_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO schema_updates (update_key, applied_at) VALUES ('life-2026-10-08', NOW())
ON DUPLICATE KEY UPDATE applied_at = applied_at;
