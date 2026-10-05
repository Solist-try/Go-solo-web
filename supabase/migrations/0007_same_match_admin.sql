-- A steward can join two members in a SAME partnership.
-- Members can still say they are open. They cannot pair themselves.
-- Run after 0002_admin.sql. Safe to run more than once.

drop policy if exists "admins suggest same matches" on public.same_partnerships;
create policy "admins suggest same matches"
  on public.same_partnerships
  for insert
  to authenticated
  with check (public.is_admin());
