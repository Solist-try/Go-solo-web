-- public.out_there_posts already exists (0001_init.sql) with id, author_id,
-- title, what_did_you_do, expecting, actually_happened, would_do_again,
-- and created_at. Add the names from the simpler shape, including a photograph.
-- Run after 0001_init.sql. Safe to run more than once.

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
