-- public.campfire_posts already exists (0001_init.sql) with id, author_id,
-- title, body, and created_at. Add the two names from the simpler shape,
-- and keep them aligned with the columns the app already uses.
-- Run after 0001_init.sql. Safe to run more than once.

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
