-- Help a member wants, and help a member can offer.
-- A row is one title, kept by the person it belongs to.
-- Run after 0001_init.sql. Safe to run more than once.

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
