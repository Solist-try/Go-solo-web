-- Let a steward opening a contact message mark it read.
-- Paste these statements in the phpMyAdmin SQL tab.
-- Do not type the file name. Paste the statements below.
-- A second paste is safe. It does not delete messages.

ALTER TABLE contact_messages
  MODIFY status ENUM('unread', 'read', 'replied', 'archived') NOT NULL DEFAULT 'unread';
