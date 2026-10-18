-- One optional featured Reading Room piece on a waypoint.
-- Paste this in the phpMyAdmin SQL tab. It does not read information_schema.
-- It does not delete members, stories, or articles.
-- A second paste is safe.

ALTER TABLE waypoints ADD COLUMN IF NOT EXISTS featured_reading_id INT UNSIGNED NULL;

INSERT INTO schema_updates (update_key, applied_at) VALUES ('feature-2026-10-18', NOW())
ON DUPLICATE KEY UPDATE applied_at = applied_at;
