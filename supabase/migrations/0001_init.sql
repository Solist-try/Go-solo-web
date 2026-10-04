-- Go Solo foundation schema.
-- Run this in the Supabase SQL editor, or with the Supabase CLI.

create extension if not exists pgcrypto;

create table public.profiles (
  id uuid primary key references auth.users (id) on delete cascade,
  email text not null,
  display_name text,
  bio text not null default '',
  location text,
  avatar_url text,
  intentions text[] not null default '{}',
  onboarding_complete boolean not null default false,
  email_verified boolean not null default false,
  show_location boolean not null default true,
  show_waypoints boolean not null default true,
  notify_replies boolean not null default true,
  notify_waypoints boolean not null default false,
  notify_checkins boolean not null default true,
  created_at timestamptz not null default now()
);

create table public.interests (
  slug text primary key,
  label text not null
);

create table public.user_interests (
  user_id uuid not null references public.profiles (id) on delete cascade,
  interest_slug text not null references public.interests (slug) on delete cascade,
  primary key (user_id, interest_slug)
);

create table public.waypoints (
  id text primary key,
  slug text unique not null,
  name text not null,
  summary text not null,
  description text not null,
  focus text[] not null default '{}'
);

create table public.user_waypoints (
  user_id uuid not null references public.profiles (id) on delete cascade,
  waypoint_id text not null references public.waypoints (id) on delete cascade,
  joined_at timestamptz not null default now(),
  primary key (user_id, waypoint_id)
);

create table public.seeds (
  id text primary key,
  title text not null,
  description text not null,
  prompt text not null,
  category text not null,
  kind text not null,
  timeframe text not null,
  waypoints text[] not null default '{}'
);

create table public.user_seeds (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references public.profiles (id) on delete cascade,
  seed_id text not null references public.seeds (id),
  status text not null default 'active',
  goal text not null default '',
  started_at timestamptz not null default now(),
  check_ins jsonb not null default '[]'::jsonb
);

create table public.same_partnerships (
  id uuid primary key default gen_random_uuid(),
  user_seed_id uuid references public.user_seeds (id) on delete cascade,
  seeker_id uuid not null references public.profiles (id) on delete cascade,
  partner_id uuid references public.profiles (id) on delete set null,
  seed_id text not null references public.seeds (id),
  goal text not null,
  status text not null default 'seeking',
  check_ins jsonb not null default '[]'::jsonb,
  created_at timestamptz not null default now()
);

create table public.skill_offers (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references public.profiles (id) on delete cascade,
  skill text not null,
  description text not null,
  created_at timestamptz not null default now()
);

create table public.skill_requests (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references public.profiles (id) on delete cascade,
  skill text not null,
  description text not null,
  created_at timestamptz not null default now()
);

create table public.skill_connections (
  id uuid primary key default gen_random_uuid(),
  from_user_id uuid not null references public.profiles (id) on delete cascade,
  to_user_id uuid not null references public.profiles (id) on delete cascade,
  offer_id uuid references public.skill_offers (id) on delete set null,
  request_id uuid references public.skill_requests (id) on delete set null,
  note text not null default '',
  created_at timestamptz not null default now()
);

create table public.out_there_posts (
  id uuid primary key default gen_random_uuid(),
  author_id uuid not null references public.profiles (id) on delete cascade,
  title text not null,
  what_did_you_do text not null,
  expecting text not null,
  actually_happened text not null,
  would_do_again text not null,
  seed_id text references public.seeds (id),
  waypoint_id text references public.waypoints (id),
  created_at timestamptz not null default now()
);

create table public.campfire_posts (
  id uuid primary key default gen_random_uuid(),
  author_id uuid not null references public.profiles (id) on delete cascade,
  section text not null,
  kind text not null,
  title text not null,
  body text not null,
  waypoint_id text references public.waypoints (id),
  seed_id text references public.seeds (id),
  out_there_id uuid references public.out_there_posts (id) on delete set null,
  created_at timestamptz not null default now()
);

create table public.comments (
  id uuid primary key default gen_random_uuid(),
  author_id uuid not null references public.profiles (id) on delete cascade,
  target_type text not null,
  target_id uuid not null,
  body text not null,
  created_at timestamptz not null default now()
);

create table public.reactions (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references public.profiles (id) on delete cascade,
  target_type text not null,
  target_id uuid not null,
  kind text not null,
  created_at timestamptz not null default now(),
  unique (user_id, target_type, target_id)
);

create table public.notifications (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references public.profiles (id) on delete cascade,
  title text not null,
  body text not null,
  href text,
  read boolean not null default false,
  created_at timestamptz not null default now()
);

create index user_seeds_user_idx on public.user_seeds (user_id);
create index out_there_created_idx on public.out_there_posts (created_at desc);
create index campfire_created_idx on public.campfire_posts (created_at desc);
create index comments_target_idx on public.comments (target_type, target_id);
create index notifications_user_idx on public.notifications (user_id, created_at desc);

alter table public.profiles enable row level security;
alter table public.interests enable row level security;
alter table public.user_interests enable row level security;
alter table public.waypoints enable row level security;
alter table public.user_waypoints enable row level security;
alter table public.seeds enable row level security;
alter table public.user_seeds enable row level security;
alter table public.same_partnerships enable row level security;
alter table public.skill_offers enable row level security;
alter table public.skill_requests enable row level security;
alter table public.skill_connections enable row level security;
alter table public.out_there_posts enable row level security;
alter table public.campfire_posts enable row level security;
alter table public.comments enable row level security;
alter table public.reactions enable row level security;
alter table public.notifications enable row level security;

create policy "interests are public" on public.interests for select to anon, authenticated using (true);
create policy "waypoints are public" on public.waypoints for select to anon, authenticated using (true);
create policy "seeds are public" on public.seeds for select to anon, authenticated using (true);

create policy "members read profiles" on public.profiles for select to authenticated using (true);
create policy "members insert own profile" on public.profiles for insert to authenticated with check (id = auth.uid());
create policy "members update own profile" on public.profiles for update to authenticated using (id = auth.uid()) with check (id = auth.uid());

create policy "members read interests" on public.user_interests for select to authenticated using (true);
create policy "members write own interests" on public.user_interests for insert to authenticated with check (user_id = auth.uid());
create policy "members remove own interests" on public.user_interests for delete to authenticated using (user_id = auth.uid());

create policy "members read waypoint membership" on public.user_waypoints for select to authenticated using (true);
create policy "members join as themselves" on public.user_waypoints for insert to authenticated with check (user_id = auth.uid());
create policy "members leave as themselves" on public.user_waypoints for delete to authenticated using (user_id = auth.uid());

create policy "members read user seeds" on public.user_seeds for select to authenticated using (true);
create policy "members plant their own seeds" on public.user_seeds for insert to authenticated with check (user_id = auth.uid());
create policy "members update their own seeds" on public.user_seeds for update to authenticated using (user_id = auth.uid()) with check (user_id = auth.uid());

create policy "members read partnerships" on public.same_partnerships for select to authenticated using (true);
create policy "members open a partnership" on public.same_partnerships for insert to authenticated with check (seeker_id = auth.uid());
create policy "partners can update a partnership" on public.same_partnerships for update to authenticated using (seeker_id = auth.uid() or partner_id = auth.uid()) with check (seeker_id = auth.uid() or partner_id = auth.uid());

create policy "members read offers" on public.skill_offers for select to authenticated using (true);
create policy "members create offers" on public.skill_offers for insert to authenticated with check (user_id = auth.uid());
create policy "members remove offers" on public.skill_offers for delete to authenticated using (user_id = auth.uid());

create policy "members read requests" on public.skill_requests for select to authenticated using (true);
create policy "members create requests" on public.skill_requests for insert to authenticated with check (user_id = auth.uid());
create policy "members remove requests" on public.skill_requests for delete to authenticated using (user_id = auth.uid());

create policy "members read connections" on public.skill_connections for select to authenticated using (true);
create policy "members create connections" on public.skill_connections for insert to authenticated with check (from_user_id = auth.uid());

create policy "members read stories" on public.out_there_posts for select to authenticated using (true);
create policy "members write stories" on public.out_there_posts for insert to authenticated with check (author_id = auth.uid());
create policy "members edit own stories" on public.out_there_posts for update to authenticated using (author_id = auth.uid()) with check (author_id = auth.uid());

create policy "members read campfire" on public.campfire_posts for select to authenticated using (true);
create policy "members write campfire" on public.campfire_posts for insert to authenticated with check (author_id = auth.uid());

create policy "members read comments" on public.comments for select to authenticated using (true);
create policy "members write comments" on public.comments for insert to authenticated with check (author_id = auth.uid());

create policy "members read reactions" on public.reactions for select to authenticated using (true);
create policy "members add reactions" on public.reactions for insert to authenticated with check (user_id = auth.uid());
create policy "members change reactions" on public.reactions for update to authenticated using (user_id = auth.uid()) with check (user_id = auth.uid());
create policy "members remove reactions" on public.reactions for delete to authenticated using (user_id = auth.uid());

create policy "members read own notes" on public.notifications for select to authenticated using (user_id = auth.uid());
create policy "members mark own notes" on public.notifications for update to authenticated using (user_id = auth.uid()) with check (user_id = auth.uid());

create or replace function public.handle_new_user()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  insert into public.profiles (id, email, email_verified)
  values (new.id, coalesce(new.email, ''), new.email_confirmed_at is not null)
  on conflict (id) do nothing;
  return new;
end;
$$;

drop trigger if exists on_auth_user_created on auth.users;
create trigger on_auth_user_created
  after insert on auth.users
  for each row execute function public.handle_new_user();

create or replace function public.notify(target uuid, title text, body text, href text)
returns void
language plpgsql
security definer
set search_path = public
as $$
begin
  if auth.uid() is null then
    raise exception 'not authenticated';
  end if;
  insert into public.notifications (user_id, title, body, href)
  values (target, title, body, href);
end;
$$;

create or replace function public.delete_own_account()
returns void
language plpgsql
security definer
set search_path = public
as $$
begin
  delete from auth.users where id = auth.uid();
end;
$$;

revoke all on function public.notify(uuid, text, text, text) from public;
revoke all on function public.delete_own_account() from public;
grant execute on function public.notify(uuid, text, text, text) to authenticated;
grant execute on function public.delete_own_account() to authenticated;

insert into storage.buckets (id, name, public)
values ('avatars', 'avatars', true)
on conflict (id) do nothing;

create policy "avatars are public" on storage.objects
  for select to anon, authenticated
  using (bucket_id = 'avatars');

create policy "members upload their avatar" on storage.objects
  for insert to authenticated
  with check (bucket_id = 'avatars' and auth.uid()::text = (storage.foldername(name))[1]);

create policy "members update their avatar" on storage.objects
  for update to authenticated
  using (bucket_id = 'avatars' and auth.uid()::text = (storage.foldername(name))[1]);

insert into public.interests (slug, label) values
  ('travel', 'Travel'),
  ('creativity', 'Creativity'),
  ('books', 'Books'),
  ('walking', 'Walking'),
  ('home-diy', 'Home & DIY'),
  ('cooking', 'Cooking'),
  ('personal-growth', 'Personal Growth'),
  ('community', 'Community'),
  ('learning', 'Learning'),
  ('nature', 'Nature');

insert into public.waypoints (id, slug, name, summary, description, focus) values
  ('independence-lab', 'independence-lab', 'Independence Lab', 'Routines, home systems, self-reliance, and confidence.', 'A waypoint for the life that happens after the door closes. The small systems, the ordinary courage, and the confidence that comes from trusting your own hands.', array['Routines', 'Home systems', 'Self-reliance', 'Confidence']),
  ('solo-among-others', 'solo-among-others', 'Solo Among Others', 'Friendship, belonging, social confidence, and community.', 'For the stretch between solitude and other people. How to belong without disappearing, and how to be out in the world without waiting for a plus-one.', array['Friendship', 'Belonging', 'Social confidence', 'Community']),
  ('emotional-clarity', 'emotional-clarity', 'Emotional Clarity', 'Reflection, identity, change, and growth.', 'A slower circle. Identity, change, and the sentences that only arrive when you stop performing them. Nothing here needs to be optimized.', array['Reflection', 'Identity', 'Change', 'Growth']);
