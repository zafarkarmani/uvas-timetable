-- Run after schema.sql.
alter table public.faculty add column if not exists user_id uuid references auth.users(id) on delete set null;
create unique index if not exists faculty_user_id_uidx on public.faculty(user_id) where user_id is not null;

drop policy if exists faculty_preferences_write on public.faculty_preferences;
create policy faculty_preferences_write on public.faculty_preferences for all to authenticated using (public.is_admin_or_committee() or exists(select 1 from public.faculty f where f.id=faculty_preferences.faculty_id and f.user_id=auth.uid())) with check (public.is_admin_or_committee() or exists(select 1 from public.faculty f where f.id=faculty_preferences.faculty_id and f.user_id=auth.uid()));

drop policy if exists faculty_preferences_read on public.faculty_preferences;
create policy faculty_preferences_read on public.faculty_preferences for select to authenticated using (true);

-- After creating each teacher's Supabase Auth account, link it to the matching faculty record:
-- update public.faculty set user_id=(select id from auth.users where email='teacher@example.com') where name='Teacher Name';
