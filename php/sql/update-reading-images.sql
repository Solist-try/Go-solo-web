-- Let a Reading Room article keep photographs inside the writing.
-- Paste this in the phpMyAdmin SQL tab. It does not read information_schema.
-- It does not delete members, stories, or articles.
-- A second paste is safe.

ALTER TABLE content_images
  MODIFY parent_type ENUM('story', 'campfire', 'waypoint', 'comment', 'conversation', 'reading') NOT NULL;

INSERT INTO schema_updates (update_key, applied_at) VALUES ('reading-images-2026-10-23', NOW())
ON DUPLICATE KEY UPDATE applied_at = applied_at;
