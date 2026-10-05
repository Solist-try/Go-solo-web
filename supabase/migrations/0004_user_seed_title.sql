-- A member's planted seed.
-- public.user_seeds already exists (0001_init.sql) with id, user_id,
-- seed_id, status, goal, started_at, and check_ins. Add the title and
-- created_at from the simpler shape. Existing rows keep their catalog link.
-- Run after 0001_init.sql. Safe to run more than once.

alter table public.user_seeds
  add column if not exists title text;

alter table public.user_seeds
  add column if not exists created_at timestamptz default now();

update public.user_seeds
set created_at = started_at
where created_at is null;

update public.user_seeds as planted
set title = catalog.title
from public.seeds as catalog
where planted.seed_id = catalog.id
  and planted.title is null;

insert into public.seeds (id, title, description, prompt, category, kind, timeframe, waypoints) values
  (
    'learning-spanish',
    'Learning Spanish',
    'A language, begun at the size of one conversation.',
    'Spend the first hour on Spanish. Ask for one small thing you actually need, or trade an hour with someone who already speaks it.',
    'growth',
    'skill-swap',
    'Over time',
    array['solo-among-others']
  ),
  (
    'building-confidence',
    'Building confidence',
    'A stretch you do not have to take alone.',
    'Name one thing you would like to feel more able to do. Find someone to grow it with you.',
    'growth',
    'same',
    'Over time',
    array['emotional-clarity', 'independence-lab']
  ),
  (
    'making-local-friends',
    'Making local friends',
    'A few people nearby, met more than once.',
    'Choose one local place you can return to. Say hello. Go again.',
    'relationships',
    'practice',
    'Over time',
    array['solo-among-others']
  )
on conflict (id) do nothing;
