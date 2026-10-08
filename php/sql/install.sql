-- Go Solo installation for MySQL or MariaDB.
-- Import this once in phpMyAdmin.
-- Importing it again drops the Go Solo tables and starts over.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS private_notes;
DROP TABLE IF EXISTS activity;
DROP TABLE IF EXISTS notices;
DROP TABLE IF EXISTS readings;
DROP TABLE IF EXISTS reading_categories;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS moderator_notes;
DROP TABLE IF EXISTS warnings;
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS campfire_posts;
DROP TABLE IF EXISTS stories;
DROP TABLE IF EXISTS skill_links;
DROP TABLE IF EXISTS skill_requests;
DROP TABLE IF EXISTS skill_offers;
DROP TABLE IF EXISTS same_matches;
DROP TABLE IF EXISTS same_requests;
DROP TABLE IF EXISTS support_preferences;
DROP TABLE IF EXISTS help_offers;
DROP TABLE IF EXISTS help_requests;
DROP TABLE IF EXISTS growing_seeds;
DROP TABLE IF EXISTS planted_seeds;
DROP TABLE IF EXISTS seeds;
DROP TABLE IF EXISTS waypoint_members;
DROP TABLE IF EXISTS waypoints;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS profiles;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('member', 'moderator', 'admin') NOT NULL DEFAULT 'member',
  status ENUM('active', 'suspended', 'banned') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL,
  last_seen_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY users_email (email),
  KEY users_status (status),
  KEY users_created (created_at),
  KEY users_seen (last_seen_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE profiles (
  user_id INT UNSIGNED NOT NULL,
  display_name VARCHAR(80) NOT NULL DEFAULT '',
  bio TEXT NOT NULL,
  location VARCHAR(120) NOT NULL DEFAULT '',
  avatar_path VARCHAR(255) NOT NULL DEFAULT '',
  contact_frequency VARCHAR(40) NOT NULL DEFAULT '',
  check_in_style VARCHAR(40) NOT NULL DEFAULT '',
  show_location TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (user_id),
  CONSTRAINT profiles_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY resets_user (user_id),
  KEY resets_token (token_hash),
  CONSTRAINT resets_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
  setting_key VARCHAR(80) NOT NULL,
  setting_value MEDIUMTEXT NOT NULL,
  PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE waypoints (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(80) NOT NULL,
  title VARCHAR(160) NOT NULL,
  description TEXT NOT NULL,
  cover_path VARCHAR(255) NOT NULL DEFAULT '',
  archived TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY waypoints_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE waypoint_members (
  waypoint_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (waypoint_id, user_id),
  CONSTRAINT waypoint_members_waypoint_fk FOREIGN KEY (waypoint_id) REFERENCES waypoints (id) ON DELETE CASCADE,
  CONSTRAINT waypoint_members_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE seeds (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(80) NOT NULL,
  title VARCHAR(160) NOT NULL,
  description TEXT NOT NULL,
  prompt VARCHAR(255) NOT NULL DEFAULT '',
  kind ENUM('practice', 'same', 'skill-swap') NOT NULL DEFAULT 'practice',
  category VARCHAR(80) NOT NULL DEFAULT '',
  archived TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY seeds_slug (slug),
  KEY seeds_kind (kind)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE planted_seeds (
  user_id INT UNSIGNED NOT NULL,
  seed_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (user_id, seed_id),
  CONSTRAINT planted_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT planted_seed_fk FOREIGN KEY (seed_id) REFERENCES seeds (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE growing_seeds (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(120) NOT NULL,
  status ENUM('active', 'resting') NOT NULL DEFAULT 'active',
  looking_for_support TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY growing_user (user_id),
  CONSTRAINT growing_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE help_requests (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(120) NOT NULL,
  PRIMARY KEY (id),
  KEY help_requests_user (user_id),
  CONSTRAINT help_requests_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE help_offers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(120) NOT NULL,
  PRIMARY KEY (id),
  KEY help_offers_user (user_id),
  CONSTRAINT help_offers_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_preferences (
  user_id INT UNSIGNED NOT NULL,
  preference VARCHAR(40) NOT NULL,
  PRIMARY KEY (user_id, preference),
  CONSTRAINT support_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE same_requests (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  seed_id INT UNSIGNED NULL,
  note TEXT NOT NULL,
  status ENUM('open', 'archived') NOT NULL DEFAULT 'open',
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY same_requests_user (user_id),
  KEY same_requests_status (status),
  CONSTRAINT same_requests_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT same_requests_seed_fk FOREIGN KEY (seed_id) REFERENCES seeds (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE same_matches (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_a_id INT UNSIGNED NOT NULL,
  user_b_id INT UNSIGNED NOT NULL,
  seed_id INT UNSIGNED NULL,
  note TEXT NOT NULL,
  status ENUM('suggested', 'approved', 'archived') NOT NULL DEFAULT 'suggested',
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY same_matches_a (user_a_id),
  KEY same_matches_b (user_b_id),
  KEY same_matches_status (status),
  CONSTRAINT same_matches_a_fk FOREIGN KEY (user_a_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT same_matches_b_fk FOREIGN KEY (user_b_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT same_matches_seed_fk FOREIGN KEY (seed_id) REFERENCES seeds (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE skill_offers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(120) NOT NULL,
  detail TEXT NOT NULL,
  archived TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY skill_offers_user (user_id),
  CONSTRAINT skill_offers_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE skill_requests (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(120) NOT NULL,
  detail TEXT NOT NULL,
  archived TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY skill_requests_user (user_id),
  CONSTRAINT skill_requests_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE skill_links (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  from_user_id INT UNSIGNED NOT NULL,
  to_user_id INT UNSIGNED NOT NULL,
  offer_id INT UNSIGNED NULL,
  request_id INT UNSIGNED NULL,
  note TEXT NOT NULL,
  archived TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY skill_links_from (from_user_id),
  KEY skill_links_to (to_user_id),
  CONSTRAINT skill_links_from_fk FOREIGN KEY (from_user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT skill_links_to_fk FOREIGN KEY (to_user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT skill_links_offer_fk FOREIGN KEY (offer_id) REFERENCES skill_offers (id) ON DELETE SET NULL,
  CONSTRAINT skill_links_request_fk FOREIGN KEY (request_id) REFERENCES skill_requests (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE stories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  what_i_did TEXT NOT NULL,
  expectations TEXT NOT NULL,
  what_happened TEXT NOT NULL,
  would_do_again TEXT NOT NULL,
  body TEXT NULL,
  image_path VARCHAR(255) NOT NULL DEFAULT '',
  hidden TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY stories_user (user_id),
  KEY stories_hidden (hidden, created_at),
  CONSTRAINT stories_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE campfire_posts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(160) NOT NULL,
  body TEXT NOT NULL,
  hidden TINYINT(1) NOT NULL DEFAULT 0,
  locked TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY campfire_user (user_id),
  KEY campfire_hidden (hidden, created_at),
  CONSTRAINT campfire_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  target_type ENUM('story', 'campfire', 'waypoint') NOT NULL,
  target_id INT UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  hidden TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY comments_target (target_type, target_id),
  KEY comments_user (user_id),
  CONSTRAINT comments_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reports (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  reporter_id INT UNSIGNED NOT NULL,
  target_type ENUM('story', 'campfire', 'comment', 'profile', 'waypoint') NOT NULL,
  target_id INT UNSIGNED NOT NULL,
  reason TEXT NOT NULL,
  status ENUM('pending', 'reviewed', 'dismissed') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY reports_status (status),
  KEY reports_target (target_type, target_id),
  CONSTRAINT reports_reporter_fk FOREIGN KEY (reporter_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE warnings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  steward_id INT UNSIGNED NOT NULL,
  note TEXT NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY warnings_user (user_id),
  CONSTRAINT warnings_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT warnings_steward_fk FOREIGN KEY (steward_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE moderator_notes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  steward_id INT UNSIGNED NOT NULL,
  note TEXT NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY moderator_notes_user (user_id),
  CONSTRAINT moderator_notes_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT moderator_notes_steward_fk FOREIGN KEY (steward_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_messages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(80) NOT NULL,
  email VARCHAR(190) NOT NULL,
  body TEXT NOT NULL,
  status ENUM('unread', 'replied', 'archived') NOT NULL DEFAULT 'unread',
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY contact_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reading_categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(80) NOT NULL,
  title VARCHAR(120) NOT NULL,
  line VARCHAR(255) NOT NULL DEFAULT '',
  image_path VARCHAR(255) NOT NULL DEFAULT '',
  image_alt VARCHAR(255) NOT NULL DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY reading_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE readings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id INT UNSIGNED NOT NULL,
  slug VARCHAR(120) NOT NULL,
  title VARCHAR(180) NOT NULL,
  standfirst VARCHAR(255) NOT NULL DEFAULT '',
  body MEDIUMTEXT NOT NULL,
  image_path VARCHAR(255) NOT NULL DEFAULT '',
  status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY readings_slug (slug),
  KEY readings_category (category_id),
  KEY readings_status (status),
  CONSTRAINT readings_category_fk FOREIGN KEY (category_id) REFERENCES reading_categories (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE waypoint_posts (
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

CREATE TABLE waypoint_readings (
  waypoint_id INT UNSIGNED NOT NULL,
  reading_id INT UNSIGNED NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (waypoint_id, reading_id),
  KEY waypoint_readings_reading (reading_id, sort_order),
  CONSTRAINT waypoint_readings_waypoint_fk FOREIGN KEY (waypoint_id) REFERENCES waypoints (id) ON DELETE CASCADE,
  CONSTRAINT waypoint_readings_reading_fk FOREIGN KEY (reading_id) REFERENCES readings (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE content_images (
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

CREATE TABLE schema_updates (
  update_key VARCHAR(80) NOT NULL,
  applied_at DATETIME NOT NULL,
  PRIMARY KEY (update_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notices (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  body VARCHAR(255) NOT NULL,
  href VARCHAR(255) NOT NULL DEFAULT '',
  read_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY notices_user (user_id, read_at),
  CONSTRAINT notices_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  summary VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY activity_user (user_id, created_at),
  CONSTRAINT activity_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE private_notes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  context_type ENUM('same', 'skill') NOT NULL,
  context_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY private_notes_context (context_type, context_id),
  KEY private_notes_user (user_id),
  CONSTRAINT private_notes_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Starting steward. Password is documented in INSTALL.md. Change it after the first login.
INSERT INTO users (id, email, password_hash, role, status, created_at)
VALUES (
  1,
  'marge@gosolo.co.network',
  '$2b$12$NGEzlCCO9lUab3EfMjj9o.I.wTn428gLIQfNiHHW1okVtBsB0dPjq',
  'admin',
  'active',
  NOW()
);

INSERT INTO profiles (user_id, display_name, bio, location, avatar_path, contact_frequency, check_in_style, show_location)
VALUES (
  1,
  'Marge Aliaga',
  'I built Go Solo because a lot of rooms still expect you to arrive as a pair.',
  '',
  '',
  '',
  '',
  1
);

INSERT INTO waypoints (slug, title, description, cover_path, archived, created_at) VALUES
(
  'independence-lab',
  'Independence Lab',
  'For people learning the practical work of a household and a week of their own. Repairs, money, meals, and the next small step.',
  '/assets/images/reading/reading-independent-living.jpg',
  0,
  NOW()
),
(
  'solo-among-others',
  'Solo Among Others',
  'For people who are around other people and still feel the gap. Friendship, rooms built for pairs, and staying connected while living alone.',
  '/assets/images/reading/reading-connection.jpg',
  0,
  NOW()
),
(
  'emotional-clarity',
  'Emotional Clarity',
  'For people sorting what they feel while life is changing. Grief, starting over, and the quieter work of knowing what you want.',
  '/assets/images/freedom-at-home.jpg',
  0,
  NOW()
);

INSERT INTO seeds (slug, title, description, prompt, kind, category, archived, created_at) VALUES
(
  'same',
  'SAME',
  'Looking for someone on a similar path? A steward can introduce you. There is no rush.',
  'What would you like company for on this stretch?',
  'same',
  'SAME',
  0,
  NOW()
),
(
  'skill-swap',
  'Skill Swap',
  'Learn something. Teach something. A crochet lesson, a bit of Spanish, a garden, a budget, writing, a small repair.',
  'What can you teach, and what would you like to learn?',
  'skill-swap',
  'Skill Swap',
  0,
  NOW()
),
(
  'learning-spanish',
  'Learning Spanish',
  'A language learned in ordinary weeks. A lesson, a conversation, a page.',
  'Learning Spanish',
  'skill-swap',
  'Skill Swap',
  0,
  NOW()
),
(
  'building-confidence',
  'Building confidence',
  'One thing you have been postponing. A class, a conversation, a first try. A steward can suggest someone in a similar stretch.',
  'Building confidence',
  'same',
  'SAME',
  0,
  NOW()
),
(
  'making-local-friends',
  'Making local friends',
  'Repeated contact with people who live near you. A class, a walk, a second invitation.',
  'Making local friends',
  'practice',
  'Connection',
  0,
  NOW()
);

INSERT INTO settings (setting_key, setting_value) VALUES
  ('site_title', 'Go Solo'),
  ('tagline', 'A calm place to try something small, with other people nearby.'),
  ('logo_text', 'Go Solo'),
  ('footer_line', 'Go Solo © 2026'),
  ('founder_name', 'Marge Aliaga'),
  ('founder_email', 'marge@gosolo.co.network'),
  ('hero_title', 'Hello, vagabond'),
('hero_subhead', 'Your life doesn''t have to wait for a partner.'),
('hero_support', 'A calm community for people living on their own. A meal, a class, a trip, a chair with someone in a similar part of life.'),
('hero_primary', 'Join Go Solo'),
('hero_secondary', 'Find Your Way'),
  ('hero_philosophy', 'Go solo, not alone'),
  ('hero_image', '/assets/images/hero-city-walk.jpg'),
  ('hero_image_alt', 'A person walking home along a rainy street with groceries.'),
  ('freedom_heading', 'Freedom is a choice, not a circumstance.'),
  ('freedom_body', 'A quiet morning at home can be part of it. So can having someone to ask.'),
  ('freedom_image', '/assets/images/freedom-at-home.jpg'),
  ('freedom_image_alt', 'A person pouring tea at a wooden table in a small apartment.'),
  ('how_title', 'How Go Solo works'),
  ('how_steps', '[{"name":"Find a Seed","body":"A seed is a tiny future, not a task. Plant something small.","href":"/seeds"},{"name":"Go Out There","body":"Try it in ordinary life. Take the trip. A Tuesday counts.","href":"/out-there"},{"name":"Return to Campfire","body":"Talk about a question, an ordinary day, or something you are figuring out.","href":"/campfire"},{"name":"Visit a Waypoint","body":"Sit with people in a similar part of life. Take your time.","href":"/waypoints"}]'),
  ('home_seeds_title', 'Seeds'),
  ('home_seeds_line', 'Tiny futures you can plant now.'),
  ('about_heading', 'Why Go Solo exists'),
  ('about_intro', 'I kept noticing the same thing.

A lot of life is still set up for couples, families, and people who already have a circle. I was moving through it on my own. Sometimes by choice. Sometimes by circumstance.'),
  ('about_founder_heading', 'How this started'),
  ('about_founder_body', 'I''ve always been a friendly person. I haven''t always been well connected.

People assumed I had someone built in. Someone to bring along, someone to call, someone to help me decide, or someone to nudge me when life got stuck. Sometimes I did. Often I didn''t.

One experience stayed with me. I was turned away from a bar because I arrived alone. It was a small moment, and it made something obvious: a lot of rooms are still built for people who show up as a pair.

I hadn''t given up on company. I wanted to keep going when nobody was free to come with me. That is how Go Solo started.'),
  ('about_belief_heading', 'Freedom is a choice, not a circumstance'),
  ('about_belief_body', 'I postponed a lot while I waited for the right person, the right timing, or a bit more confidence.

Life is not equally easy for everyone. People arrive starting over, living alone, managing an illness, grieving, or new in a city.

A life can keep moving before all of that lines up. That doesn''t mean doing everything alone. Support is welcome, and the choice stays yours.'),
  ('about_hello_heading', 'Say hello'),
  ('about_hello_body', 'If you have a question, an idea, or simply want to say hello, I would love to hear from you.'),
  ('about_close_heading', 'Pull up a chair'),
  ('about_close_body', 'You''re welcome here. Take your time.'),
  ('contact_headline', 'Contact'),
  ('contact_card_title', 'How to reach me'),
  ('contact_body', 'Hi there. If you have a question, idea, concern, or story, I''d love to hear from you.'),
  ('seeds_title', 'Seeds'),
  ('seeds_line', 'Tiny futures you can plant now'),
  ('seeds_support', 'Some seeds grow through support.'),
  ('seeds_accountability', 'Some through accountability.'),
  ('seeds_learning', 'Some through learning.'),
  ('seeds_small', 'All begin with something small.'),
  ('out_there_empty', 'The walk, the class, the conversation, the first solo trip. This is where members share what happened next.'),
  ('out_there_image', '/assets/images/reading/reading-out-there.jpg'),
  ('out_there_image_alt', 'A person looking out a train window, with a bag on the seat.'),
  ('campfire_waiting', 'The campfire is waiting for its first conversation.'),
  ('campfire_empty', 'Pull up a chair. Talk about something you''re wondering about, something that happened, or something you''re still working through.'),
  ('campfire_image', '/assets/images/campfire-table.jpg'),
  ('campfire_image_alt', 'Three people talking over coffee at a small wooden table.'),
  ('nav_about', 'About'),
  ('nav_seeds', 'Seeds'),
  ('nav_out_there', 'Out There'),
  ('nav_campfire', 'Campfire'),
  ('nav_waypoints', 'Waypoints'),
  ('nav_reading', 'Reading Room'),
  ('nav_contact', 'Contact'),
  ('nav_join', 'Join'),
  ('nav_login', 'Log In'),
  ('color_background', '#f7f5f2'),
  ('color_ink', '#1a1a1a'),
  ('color_soft', '#6b6b6b'),
  ('color_sage', '#dce5de'),
  ('color_clay', '#eed9d2'),
  ('color_gold', '#f3ead7'),
  ('color_mist', '#eceeef'),
  ('color_card', '#eceeef'),
  ('email_reset_subject', 'A way back into Go Solo'),
  ('email_reset_body', 'Hello {name},

Here is a link to choose a new password. It lasts for two hours.

{link}

If you did not ask for this, you can ignore it.');

INSERT INTO reading_categories (id, slug, title, line, image_path, image_alt, sort_order) VALUES (1, 'living-well', 'Living Well', 'A sustainable everyday life. The kitchen, the week, and a home that is actually used.', '/assets/images/reading/reading-living-well.jpg', 'A person cooking a simple meal beside unpacked groceries and a cup of tea.', 1);
INSERT INTO reading_categories (id, slug, title, line, image_path, image_alt, sort_order) VALUES (2, 'connection', 'Connection', 'Everyday human contact. A conversation, a meal, someone you see again.', '/assets/images/reading/reading-connection.jpg', 'Two people talking over coffee in a café.', 2);
INSERT INTO reading_categories (id, slug, title, line, image_path, image_alt, sort_order) VALUES (3, 'starting-over', 'Starting Over', 'Transitions and new beginnings. An unfinished room after a move, a marriage, or a life that no longer fits.', '/assets/images/reading/reading-starting-over.jpg', 'Half-unpacked boxes, keys on a windowsill, and a suitcase beside a bed.', 3);
INSERT INTO reading_categories (id, slug, title, line, image_path, image_alt, sort_order) VALUES (4, 'independent-living', 'Independent Living', 'Learning how to navigate a life on your own. Repairs, money, meals, and the next practical step.', '/assets/images/reading/reading-independent-living.jpg', 'A person fixing a wooden shelf in an apartment.', 4);
INSERT INTO reading_categories (id, slug, title, line, image_path, image_alt, sort_order) VALUES (5, 'out-there', 'Out There', 'Trips, tables, and the city you already live in.', '/assets/images/reading/reading-out-there.jpg', 'A person looking out a train window, with a bag on the seat.', 5);
INSERT INTO reading_categories (id, slug, title, line, image_path, image_alt, sort_order) VALUES (6, 'reflections', 'Reflections', 'Why a life gets postponed, and what makes it feel larger.', '', '', 6);

INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (1, 1, 'creating-a-weekly-reset', 'Creating A Weekly Reset', 'A short sequence that makes the next seven days visible.', 'A weekly reset is not a new personality. It is a repeating hour that answers three questions: what must happen, what will I eat, and what in the home is in the way?

Keep it small enough to finish. Look at the calendar. Choose five dinners. Clear one surface. If the hour runs long, the ritual will not survive a busy Sunday.

Do it in the same order each week. The order is the point. You stop deciding how to begin, and you start the week already knowing the shape of it.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (2, 1, 'cooking-for-one-without-waste', 'Cooking For One Without Waste', 'A kitchen that feeds one person without throwing half of it away.', 'Cooking for one fails when every recipe assumes four plates. Buy ingredients that can cross meals: a roast chicken becomes soup, herbs go into eggs, rice becomes tomorrow''s lunch.

Plan five dinners, not a personality overhaul. Repeat two of them. A repeated meal is not a failure of imagination. It is how a single household stays fed.

Keep a short list of meals you will actually cook on a tired night. Those are the meals that stop the expensive default. The interesting recipe can wait for a night when you have the hour.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (3, 4, 'managing-a-household-alone', 'Managing A Household Alone', 'Every task in the home has one name on it. Yours.', 'Living alone means there is no one else to notice the bin, the bill, or the dripping tap. That is not a character flaw. It is a staffing problem with a staff of one.

Split the house into a few repeating jobs rather than a constant sense of being behind. Bins. Laundry. Food. Bills. One repair. Put each job on a day you can actually keep.

When something breaks, write down the next physical step: the part, the person to call, the afternoon it will happen. A household stays manageable when problems have a next action, not only a mood.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (4, 4, 'building-routines-that-stick', 'Building Routines That Stick', 'A routine survives when it is attached to something you already do.', 'Most routines fail because they ask for a new life at 6 a.m. Attach the new action to a hinge you already have: the kettle, the walk to the door, the Sunday evening.

Make the first version almost too small. A morning routine can be water, a window, and the bag by the door. If you miss a day, begin again the next day. A routine is a return, not a streak.

Judge it after two weeks, not two mornings. The question is whether the week is kinder, not whether you felt inspired.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (5, 1, 'making-a-home-feel-like-yours', 'Making A Home Feel Like Yours', 'The room can tell the truth about the life that happens in it.', 'A home feels borrowed when it is arranged for a person you are waiting to become, or for guests who rarely come. Start with the chair you actually sit in and the light you use at night.

Change one corner before you change the whole flat. A lamp, a table at the right height, a shelf that holds the things you use. The rest of the room can catch up.

You do not need a style. You need a place that supports cooking, resting, working, and having one other person over. If a room does none of those, move one piece of furniture until it does.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (6, 4, 'organising-your-finances', 'Organising Your Finances', 'A plain picture of money is a form of self-reliance.', 'Begin with one month, not a lifetime plan. Write what comes in. Write the bills that must leave. What remains is the money you can choose with.

Name the accounts by their job: rent and bills, food, the rest. Automatic payments for the non-negotiables remove a weekly decision. The remaining money is easier to see when the essentials have already gone.

Review it once a month, on a date you will remember. The review is not a verdict on your character. It is how a person living alone stays ahead of a surprise.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (7, 1, 'managing-decision-fatigue', 'Managing Decision Fatigue', 'A life of your own contains a surprising number of small decisions.', 'When you live alone, you choose the meal, the plan, the repair, and whether to go out. By evening, even a good choice can feel heavy. That weight is decision fatigue, and it is ordinary.

Remove repeat decisions. A default breakfast. A weekly meal list. A Sunday look at the calendar. The point is not efficiency for its own sake. It is leaving enough attention for the choices that matter.

When you are tired, choose the option you already prepared. A decided meal and a decided evening are a kindness you can give your future self.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (8, 2, 'how-adults-make-friends', 'How Adults Make Friends', 'Adult friendship is mostly repeated contact with the same people.', 'Friendship after school rarely arrives as a revelation. It arrives because you were in the same room often enough to become familiar. A class, a volunteer shift, a walking group, a neighbour you keep greeting.

One meeting is an introduction. The second meeting is where a person becomes specific. Invite someone again before you decide whether you are friends. Familiarity comes before closeness.

You can be the one who suggests the next time. Adults are often waiting for someone else to do that. A simple invitation is not neediness. It is how a social life gets built.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (9, 2, 'reaching-out-without-feeling-awkward', 'Reaching Out Without Feeling Awkward', 'A specific note is easier to answer than a perfect one.', 'Awkwardness grows in the gap between wanting to write and waiting for the right wording. Send a shorter message. Name a memory, ask one question, or offer one time to meet.

You do not need a reason as large as a birthday. ''I thought of you when I walked past that bakery'' is a complete reason. People are glad to be remembered in ordinary weeks.

If they are slow to reply, leave the door open and go on with your week. A message is an invitation, not a test you can fail in public.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (10, 2, 'building-community-slowly', 'Building Community Slowly', 'A community is a set of places you return to.', 'Community is not a crowd you join once. It is the cafe that knows your order, the class where people start to save you a seat, the neighbour you can ask for a tool.

Pick one place and go back. The third visit is when faces become names. The sixth is when someone asks how you are and waits for the answer.

You can belong in more than one small circle. A walking group and a neighbour and one old friend can be a whole social life. It does not have to look like a full calendar.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (11, 2, 'creating-meaningful-friendships', 'Creating Meaningful Friendships', 'Meaning shows up when you tell the truth in small amounts.', 'A meaningful friendship is not a performance of having an interesting life. It is two people who know something true about each other''s weeks.

Share one real thing: a repair you are avoiding, a parent you miss, a meal you cooked, a plan you are nervous about. Then ask something that lets them do the same.

Keep a rhythm. A monthly walk will do more than a dramatic reunion every two years. Meaning accumulates in the conversations you actually have.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (12, 2, 'staying-connected-while-living-alone', 'Staying Connected While Living Alone', 'Solitude and contact can share a week.', 'Living alone does not require disappearing. It does require choosing contact, because nobody else is already in the kitchen.

Decide, in advance, how you will stay in touch. One call on a weekday. One meal with someone. One message that is more than a reaction to a photo. Put them on the calendar the way you would put a bill.

Connection can also be light. A neighbour, a regular class, a voice note. You do not have to host a dinner to remain a person among people.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (13, 3, 'life-after-divorce', 'Life After Divorce', 'A household of one has practical work and a grief of its own.', 'After a divorce, the practical list and the emotional one arrive together. Keys, money, furniture, and the evening that used to have another person in it. Both lists are real. Handle one item from each, not the whole future.

Rebuild the week before you rebuild an identity. Who cooks. Who the emergency contact is. Which evening has another human voice in it. A routine is a form of care while the larger story is still settling.

You do not have to narrate the marriage in order to have a Tuesday. Tell a few trusted people the truth. With everyone else, you can simply be a person who lives here now.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (14, 3, 'moving-to-a-new-city', 'Moving To A New City', 'A new city becomes yours through repeated errands, not a perfect first month.', 'The first weeks are administration: a bed, a shop, a doctor, a route to work or to the station. Do those before you judge whether you belong. Belonging is slow because the city does not know you yet.

Learn one neighbourhood on foot. Find a grocery, a place to sit, and a walk you can repeat. Familiarity is the first form of home.

Say yes to one recurring room: a class, a volunteer shift, a language exchange. One room, visited often, will introduce you to the city faster than a list of sights.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (15, 3, 'relocating-abroad', 'Relocating Abroad', 'Another country asks for paperwork, patience, and a few people who know your name.', 'Start with the systems that keep you safe: registration, banking, a doctor, a way to get home at night. Adventure can wait until the ordinary machinery works.

Learn the phrases that buy food, ask for help, and apologise. Use them badly. A life abroad gets larger each time you complete an errand in the local language.

Find one person who will notice if you go quiet. A colleague, a neighbour, a class. Independence in a new country still needs a human thread back to the world.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (16, 3, 'starting-again-in-midlife', 'Starting Again In Midlife', 'A later beginning can be practical. It does not have to look like a reinvention.', 'Midlife beginnings are often quieter than the story suggests. A flat of your own. A skill you can use. A friendship that is not inherited from an old life. These are substantial.

Keep what still fits. A new chapter does not require throwing out every habit, friend, and object. Sort them. Some are ballast. Some are the reason the next year will work.

Give the new life a weekly shape before you give it a meaning. Work, food, movement, one person. Meaning tends to arrive after the week has a floor.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (17, 3, 'rebuilding-a-social-circle', 'Rebuilding A Social Circle', 'A circle is rebuilt one repeated person at a time.', 'After a move, a divorce, or a long retreat, the old circle may be far away or finished. Start with two kinds of people: someone from before who is still glad to hear from you, and someone new you can see in person.

Be specific. ''We should get together sometime'' dissolves. ''Thursday at the place near the station'' can become a friendship.

Expect it to be uneven. Some people will not write back. One person who does, and who you see again, is the beginning of a circle. You do not need twelve.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (18, 5, 'first-solo-trip', 'First Solo Trip', 'A first trip alone can be one night and a plan for dinner.', 'You do not need a month abroad to learn that you can travel alone. One night in a town you can get home from is a complete first trip. Book the bed. Know how you will eat. Leave a note with someone about where you are.

Build a loose shape for the day: a walk, one place you want to see, and a meal. Leave the rest unscheduled. Solo travel feels larger when you are not performing an itinerary.

The awkward parts are usually smaller than the weeks of waiting. A table for one. A train seat. An evening in a room that is not yours. You can do each of them once, and then you know the way.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (19, 5, 'restaurant-for-one', 'Restaurant For One', 'A meal out can be the plan, not a pause until someone else is free.', 'Choose a place where one person is ordinary: a counter, a small table, a lunch hour. Sit where you can see the room if that helps, or the window if you would rather not.

Order the thing you want. Bring a book if you like having a companion object. You can also just eat. A meal does not need a second conversation to be finished.

Stay for the course you ordered. Leaving early teaches your nerves that you were right to be uneasy. Staying teaches them that a table for one is a normal piece of furniture.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (20, 5, 'how-to-try-new-experiences', 'How To Try New Experiences', 'A new experience needs a date, a size, and a way home.', 'Pick something you can finish in one outing. A workshop, a museum, a class, a neighbourhood. If it requires a new personality, it will stay on the list.

Decide the practical edges before you go: when it starts, what it costs, how you get back. Courage is easier when the logistics are already handled.

Afterwards, tell the truth. What you expected. What happened. Whether you would do it again. That record is how a life of trying becomes knowledge, not a pile of almosts.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (21, 5, 'exploring-your-own-city', 'Exploring Your Own City', 'The city you live in still has streets you have never used.', 'Choose a neighbourhood you only pass through. Walk it for an hour. Find one place you could return to: a bakery, a gallery, a bench, a shop.

Treat it as travel. Leave the usual route. Eat something there. The point is to become a visitor in a place that is already yours.

Repeat one walk until it is familiar, then pick the next. A city opens by accumulation. You do not have to see all of it to stop living in a corridor between home and work.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (22, 5, 'travelling-without-waiting-for-company', 'Travelling Without Waiting For Company', 'Company can join a trip you have already begun.', 'Waiting for matching calendars can postpone a trip for years. Decide the smallest version you would still be glad you took, and book that. A weekend counts.

Tell one person the dates, so someone knows you have gone. Then plan the trip for the person who is actually going: you. Meals, walks, and a pace you like.

If a friend wants to come next time, there can be a next time because you know the way. The first trip does not have to be the shared one.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (23, 6, 'why-we-wait', 'Why We Wait', 'Waiting often looks like practicality. It is frequently a habit.', 'People wait for the right person, the right timing, a matching calendar, or a feeling of readiness. Each reason can be true for a particular plan. Together they can postpone a whole life.

Ask of the thing you are delaying: what, exactly, is missing? If the answer is company, consider the version you can do alone. If the answer is money or skill, name the next practical step. If the answer is a feeling, the feeling may arrive after you begin.

Waiting has a cost that does not show up on a calendar. It shows up as a list of days you meant to live. Beginning with one small action gives that list somewhere to go.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (24, 6, 'the-myth-of-the-right-time', 'The Myth Of The Right Time', 'The right time is often the week you stop arranging around it.', 'There is a useful right time: when the rent is paid, when you are well enough, when the train exists. There is also a mythical right time, in which every condition is comfortable and someone is free to come with you.

The mythical version never quite arrives. A free evening, a small budget, and a plan that fits in one day are enough for most beginnings.

Put the action on a real date. A plan without a date is still a wish. A date turns it into something you can do, postpone on purpose, or learn from.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (25, 6, 'independence-versus-isolation', 'Independence Versus Isolation', 'A life of your own can still have people in it.', 'Independence is the ability to keep a home, a week, and a set of choices. Isolation is the absence of contact. They are often spoken of as the same thing. They are not.

You can cook for yourself and still have a friend for coffee. You can travel alone and still belong to a room you return to. The skill is knowing which parts of life you want to hold, and which parts you want to share.

If the days have become only tasks and no voices, that is worth tending. A message, a class, a neighbour. Independence stays healthy when it includes a way back to other people.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (26, 6, 'permission-to-begin', 'Permission To Begin', 'You do not need a witness before you are allowed to try.', 'Permission is a habit dressed up as a practical concern. Who will I go with? What will people think? Is this the sort of thing a person does alone?

Most of the time, the practical answer is ordinary. A class takes one. A kitchen works for one. A budget can be reviewed on a Sunday without an audience.

You can still want company. Wanting it is different from waiting for it. Begin. If someone wants to come next time, there will be a next time because you already know the way.', '', 'published', NOW(), NOW());
INSERT INTO readings (id, category_id, slug, title, standfirst, body, image_path, status, created_at, updated_at) VALUES (27, 6, 'what-makes-a-life-feel-bigger', 'What Makes A Life Feel Bigger', 'A larger life is not the same as a louder one.', 'A life feels bigger when it contains both reach and ground. A trip you took. A kitchen that works. A friend you called. A week you can see coming.

Excitement is one kind of growth. Capability is another. The person who finally keeps a household routine has grown, just as the person who boards a train alone has grown.

Ask of this month: what would make my actual days feel more intentional, more connected, more capable? The answer might be Poland. It might be Tuesday. Both count.', '', 'published', NOW(), NOW());

INSERT INTO schema_updates (update_key, applied_at) VALUES ('rooms-2026-10-08', NOW());

