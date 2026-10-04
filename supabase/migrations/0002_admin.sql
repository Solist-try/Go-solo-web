-- Steward desk. Run after 0001_init.sql.

alter table public.profiles add column if not exists role text not null default 'member';
alter table public.profiles add column if not exists status text not null default 'active';

alter table public.seeds add column if not exists difficulty text not null default 'gentle';
alter table public.seeds add column if not exists featured boolean not null default false;
alter table public.seeds add column if not exists archived boolean not null default false;

alter table public.waypoints add column if not exists colour text not null default 'sage';
alter table public.waypoints add column if not exists icon text not null default '';
alter table public.waypoints add column if not exists featured boolean not null default false;
alter table public.waypoints add column if not exists archived boolean not null default false;

alter table public.out_there_posts add column if not exists hidden boolean not null default false;
alter table public.out_there_posts add column if not exists pinned boolean not null default false;
alter table public.out_there_posts add column if not exists featured boolean not null default false;

alter table public.campfire_posts add column if not exists hidden boolean not null default false;
alter table public.campfire_posts add column if not exists pinned boolean not null default false;
alter table public.campfire_posts add column if not exists featured boolean not null default false;
alter table public.campfire_posts add column if not exists locked boolean not null default false;

create table if not exists public.site_content (
  id text primary key,
  document jsonb not null,
  updated_at timestamptz not null default now()
);

create table if not exists public.admin_desk (
  id text primary key,
  document jsonb not null,
  updated_at timestamptz not null default now()
);

create or replace function public.is_admin()
returns boolean
language sql
stable
security definer
set search_path = public
as $$
  select exists (
    select 1 from public.profiles
    where id = auth.uid() and role = 'admin' and status = 'active'
  );
$$;

alter table public.site_content enable row level security;
alter table public.admin_desk enable row level security;

drop policy if exists "content is readable" on public.site_content;
create policy "content is readable" on public.site_content
  for select to anon, authenticated using (true);

drop policy if exists "admins write content" on public.site_content;
create policy "admins write content" on public.site_content
  for all to authenticated using (public.is_admin()) with check (public.is_admin());

drop policy if exists "admins read desk" on public.admin_desk;
create policy "admins read desk" on public.admin_desk
  for select to authenticated using (public.is_admin());

drop policy if exists "admins write desk" on public.admin_desk;
create policy "admins write desk" on public.admin_desk
  for all to authenticated using (public.is_admin()) with check (public.is_admin());

drop policy if exists "admins update profiles" on public.profiles;
create policy "admins update profiles" on public.profiles
  for update to authenticated using (public.is_admin()) with check (public.is_admin());

drop policy if exists "admins delete profiles" on public.profiles;
create policy "admins delete profiles" on public.profiles
  for delete to authenticated using (public.is_admin());

drop policy if exists "admins update stories" on public.out_there_posts;
create policy "admins update stories" on public.out_there_posts
  for update to authenticated using (public.is_admin()) with check (public.is_admin());

drop policy if exists "admins delete stories" on public.out_there_posts;
create policy "admins delete stories" on public.out_there_posts
  for delete to authenticated using (public.is_admin());

drop policy if exists "admins update campfire" on public.campfire_posts;
create policy "admins update campfire" on public.campfire_posts
  for update to authenticated using (public.is_admin()) with check (public.is_admin());

drop policy if exists "admins delete campfire" on public.campfire_posts;
create policy "admins delete campfire" on public.campfire_posts
  for delete to authenticated using (public.is_admin());

drop policy if exists "admins write seeds" on public.seeds;
create policy "admins write seeds" on public.seeds
  for all to authenticated using (public.is_admin()) with check (public.is_admin());

drop policy if exists "admins write waypoints" on public.waypoints;
create policy "admins write waypoints" on public.waypoints
  for all to authenticated using (public.is_admin()) with check (public.is_admin());
