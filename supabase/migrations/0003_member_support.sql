-- Member support for Go Solo.
-- Profiles, seeds, SAME, Campfire, and Out There.
-- The tables already exist in 0001_init.sql. This adds the missing columns
-- and the two new tables a profile needs. Safe to run more than once.
-- Run after 0001_init.sql and 0002_admin.sql.

-- Preferred contact frequency and style live on the profile.
alter table public.profiles
  add column if not exists contact_frequency text;

alter table public.profiles
  add column if not exists contact_style text;

alter table public.profiles
  add column if not exists help_plant_note text not null default '';

-- A planted seed can be a catalog seed, or a personal "Seeds I'm Growing" row.
alter table public.user_seeds
  add column if not exists title text;

alter table public.user_seeds
  add column if not exists created_at timestamptz default now();

alter table public.user_seeds
  add column if not exists looking_for_support boolean not null default false;

alter table public.user_seeds
  alter column seed_id drop not null;

update public.user_seeds
set created_at = started_at
where created_at is null;

update public.user_seeds as planted
set title = catalog.title
from public.seeds as catalog
where planted.seed_id = catalog.id
  and planted.title is null;

drop policy if exists "members remove their own seeds" on public.user_seeds;
create policy "members remove their own seeds"
  on public.user_seeds
  for delete
  to authenticated
  using (user_id = auth.uid());

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

-- Seeds I'd like help growing, and seeds I'm happy to help plant.
create table if not exists public.seed_help_requests (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references public.profiles (id) on delete cascade,
  title text
);

create table if not exists public.seed_help_offers (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references public.profiles (id) on delete cascade,
  title text
);

create index if not exists seed_help_requests_user_idx on public.seed_help_requests (user_id);
create index if not exists seed_help_offers_user_idx on public.seed_help_offers (user_id);

alter table public.seed_help_requests enable row level security;
alter table public.seed_help_offers enable row level security;

drop policy if exists "members read seed help requests" on public.seed_help_requests;
create policy "members read seed help requests"
  on public.seed_help_requests for select to authenticated using (true);
drop policy if exists "members ask for their own help" on public.seed_help_requests;
create policy "members ask for their own help"
  on public.seed_help_requests for insert to authenticated with check (user_id = auth.uid());
drop policy if exists "members remove their own help requests" on public.seed_help_requests;
create policy "members remove their own help requests"
  on public.seed_help_requests for delete to authenticated using (user_id = auth.uid());

drop policy if exists "members read seed help offers" on public.seed_help_offers;
create policy "members read seed help offers"
  on public.seed_help_offers for select to authenticated using (true);
drop policy if exists "members offer their own help" on public.seed_help_offers;
create policy "members offer their own help"
  on public.seed_help_offers for insert to authenticated with check (user_id = auth.uid());
drop policy if exists "members remove their own help offers" on public.seed_help_offers;
create policy "members remove their own help offers"
  on public.seed_help_offers for delete to authenticated using (user_id = auth.uid());

-- Support preferences. One row per kind of support.
create table if not exists public.same_preferences (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references public.profiles (id) on delete cascade,
  support_type text,
  frequency text,
  notes text,
  created_at timestamptz default now()
);

create index if not exists same_preferences_user_idx on public.same_preferences (user_id);

alter table public.same_preferences enable row level security;

drop policy if exists "members read same preferences" on public.same_preferences;
create policy "members read same preferences"
  on public.same_preferences for select to authenticated using (true);
drop policy if exists "members write their own same preferences" on public.same_preferences;
create policy "members write their own same preferences"
  on public.same_preferences for insert to authenticated with check (user_id = auth.uid());
drop policy if exists "members remove their own same preferences" on public.same_preferences;
create policy "members remove their own same preferences"
  on public.same_preferences for delete to authenticated using (user_id = auth.uid());

-- Members can say they are open. A steward makes the match.
drop policy if exists "members open a partnership" on public.same_partnerships;
create policy "members open a partnership"
  on public.same_partnerships
  for insert
  to authenticated
  with check (seeker_id = auth.uid() and partner_id is null and status = 'seeking');

drop policy if exists "admins suggest same matches" on public.same_partnerships;
create policy "admins suggest same matches"
  on public.same_partnerships
  for insert
  to authenticated
  with check (public.is_admin());

create or replace function public.guard_same_partnership()
returns trigger
language plpgsql
as $$
begin
  if public.is_admin() then
    return new;
  end if;

  if tg_op = 'INSERT' then
    if new.seeker_id is distinct from auth.uid()
      or new.partner_id is not null
      or new.status is distinct from 'seeking' then
      raise exception 'A steward makes SAME matches.';
    end if;
    return new;
  end if;

  if new.seeker_id is distinct from old.seeker_id
    or new.partner_id is distinct from old.partner_id
    or new.seed_id is distinct from old.seed_id
    or new.status is distinct from old.status
    or new.user_seed_id is distinct from old.user_seed_id then
    raise exception 'A steward makes SAME matches.';
  end if;

  return new;
end;
$$;

drop trigger if exists guard_same_partnership on public.same_partnerships;
create trigger guard_same_partnership
  before insert or update on public.same_partnerships
  for each row execute function public.guard_same_partnership();

-- Campfire keeps author_id and body, and accepts user_id and content.
alter table public.campfire_posts
  add column if not exists user_id uuid references public.profiles (id) on delete cascade;

alter table public.campfire_posts
  add column if not exists content text;

alter table public.campfire_posts alter column section set default 'general';
alter table public.campfire_posts alter column kind set default 'thought';
alter table public.campfire_posts alter column title set default '';
alter table public.campfire_posts alter column body set default '';

update public.campfire_posts
set user_id = author_id
where user_id is null;

update public.campfire_posts
set content = body
where content is null;

create or replace function public.sync_campfire_post()
returns trigger
language plpgsql
as $$
begin
  if new.user_id is null then
    new.user_id := new.author_id;
  end if;
  if new.author_id is null then
    new.author_id := new.user_id;
  end if;

  if tg_op = 'UPDATE' then
    if new.user_id is distinct from old.user_id and new.author_id is not distinct from old.author_id then
      new.author_id := new.user_id;
    elsif new.author_id is distinct from old.author_id then
      new.user_id := new.author_id;
    end if;
    if new.content is distinct from old.content and new.body is not distinct from old.body then
      new.body := coalesce(new.content, '');
    elsif new.body is distinct from old.body then
      new.content := new.body;
    end if;
  else
    if new.content is null then
      new.content := new.body;
    end if;
    if new.body is null or new.body = '' then
      new.body := coalesce(new.content, '');
    end if;
    if new.content is null then
      new.content := new.body;
    end if;
  end if;

  return new;
end;
$$;

drop trigger if exists sync_campfire_post on public.campfire_posts;
create trigger sync_campfire_post
  before insert or update on public.campfire_posts
  for each row execute function public.sync_campfire_post();

drop policy if exists "members write campfire" on public.campfire_posts;
create policy "members write campfire"
  on public.campfire_posts
  for insert
  to authenticated
  with check (author_id = auth.uid() or user_id = auth.uid());

-- Out There keeps the original columns, and accepts the shorter names.
alter table public.out_there_posts
  add column if not exists user_id uuid references public.profiles (id) on delete cascade;

alter table public.out_there_posts
  add column if not exists what_i_did text;

alter table public.out_there_posts
  add column if not exists expectations text;

alter table public.out_there_posts
  add column if not exists what_happened text;

alter table public.out_there_posts
  add column if not exists image_url text;

alter table public.out_there_posts alter column title set default '';
alter table public.out_there_posts alter column what_did_you_do set default '';
alter table public.out_there_posts alter column expecting set default '';
alter table public.out_there_posts alter column actually_happened set default '';
alter table public.out_there_posts alter column would_do_again set default 'yes';

update public.out_there_posts
set
  user_id = author_id,
  what_i_did = what_did_you_do,
  expectations = expecting,
  what_happened = actually_happened
where user_id is null
   or what_i_did is null
   or expectations is null
   or what_happened is null;

create or replace function public.sync_out_there_post()
returns trigger
language plpgsql
as $$
begin
  if new.user_id is null then
    new.user_id := new.author_id;
  end if;
  if new.author_id is null then
    new.author_id := new.user_id;
  end if;

  if tg_op = 'UPDATE' then
    if new.user_id is distinct from old.user_id and new.author_id is not distinct from old.author_id then
      new.author_id := new.user_id;
    elsif new.author_id is distinct from old.author_id then
      new.user_id := new.author_id;
    end if;

    if new.what_i_did is distinct from old.what_i_did and new.what_did_you_do is not distinct from old.what_did_you_do then
      new.what_did_you_do := coalesce(new.what_i_did, '');
    elsif new.what_did_you_do is distinct from old.what_did_you_do then
      new.what_i_did := new.what_did_you_do;
    end if;

    if new.expectations is distinct from old.expectations and new.expecting is not distinct from old.expecting then
      new.expecting := coalesce(new.expectations, '');
    elsif new.expecting is distinct from old.expecting then
      new.expectations := new.expecting;
    end if;

    if new.what_happened is distinct from old.what_happened and new.actually_happened is not distinct from old.actually_happened then
      new.actually_happened := coalesce(new.what_happened, '');
    elsif new.actually_happened is distinct from old.actually_happened then
      new.what_happened := new.actually_happened;
    end if;
  else
    if new.what_i_did is null then
      new.what_i_did := new.what_did_you_do;
    end if;
    if new.what_did_you_do is null or new.what_did_you_do = '' then
      new.what_did_you_do := coalesce(new.what_i_did, '');
    end if;
    if new.what_i_did is null then
      new.what_i_did := new.what_did_you_do;
    end if;

    if new.expectations is null then
      new.expectations := new.expecting;
    end if;
    if new.expecting is null or new.expecting = '' then
      new.expecting := coalesce(new.expectations, '');
    end if;
    if new.expectations is null then
      new.expectations := new.expecting;
    end if;

    if new.what_happened is null then
      new.what_happened := new.actually_happened;
    end if;
    if new.actually_happened is null or new.actually_happened = '' then
      new.actually_happened := coalesce(new.what_happened, '');
    end if;
    if new.what_happened is null then
      new.what_happened := new.actually_happened;
    end if;
  end if;

  return new;
end;
$$;

drop trigger if exists sync_out_there_post on public.out_there_posts;
create trigger sync_out_there_post
  before insert or update on public.out_there_posts
  for each row execute function public.sync_out_there_post();

drop policy if exists "members write stories" on public.out_there_posts;
create policy "members write stories"
  on public.out_there_posts
  for insert
  to authenticated
  with check (author_id = auth.uid() or user_id = auth.uid());

drop policy if exists "members edit own stories" on public.out_there_posts;
create policy "members edit own stories"
  on public.out_there_posts
  for update
  to authenticated
  using (author_id = auth.uid() or user_id = auth.uid())
  with check (author_id = auth.uid() or user_id = auth.uid());
