-- Let a member choose whether shared ground can include their garden.
-- Paste these statements in the phpMyAdmin SQL tab.
-- Do not type a file name. Paste the statements below.
-- A second paste is safe. It does not remove gardens or conversations.

ALTER TABLE profiles
  ADD COLUMN IF NOT EXISTS discoverable TINYINT(1) NOT NULL DEFAULT 1;
