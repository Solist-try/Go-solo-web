-- A member can set a conversation request aside without closing it.
-- Paste this in the phpMyAdmin SQL tab while the Go Solo database is selected.
-- Paste the statement below. Do not type the file name.
-- A second paste is safe. It does not delete members or messages.

ALTER TABLE conversation_participants ADD COLUMN IF NOT EXISTS not_now TINYINT(1) NOT NULL DEFAULT 0;
