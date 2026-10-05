-- How a member likes to be reached.
-- public.profiles already exists (0001_init.sql) with id, display_name,
-- bio, location, avatar_url, and created_at. Add the two fields that were missing.
-- Run after 0001_init.sql. Safe to run more than once.

alter table public.profiles
  add column if not exists contact_frequency text;

alter table public.profiles
  add column if not exists contact_style text;
