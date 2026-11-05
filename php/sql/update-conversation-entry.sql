-- Let a member write a subject, and choose whether their garden can be opened.
-- Paste these statements in the phpMyAdmin SQL tab.
-- Do not type a file name. Paste the statements below.
-- A second paste is safe. It does not remove conversations or members.

ALTER TABLE conversations
  ADD COLUMN IF NOT EXISTS subject VARCHAR(180) NOT NULL DEFAULT '' AFTER context_note;

ALTER TABLE profiles
  ADD COLUMN IF NOT EXISTS garden_visible TINYINT(1) NOT NULL DEFAULT 1;
