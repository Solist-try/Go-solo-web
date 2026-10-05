-- How a member would like SAME to feel.
-- One row per kind of support. Frequency and notes travel with that choice.
-- Run after 0001_init.sql. Safe to run more than once.

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
