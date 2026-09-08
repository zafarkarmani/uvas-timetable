create table if not exists teachers(id uuid primary key default gen_random_uuid(),name text not null,max_weekly_slots int default 12,active boolean default true);
create table if not exists classes(id uuid primary key default gen_random_uuid(),name text not null,active boolean default true);
create table if not exists rooms(id uuid primary key default gen_random_uuid(),name text not null,kind text not null check(kind in ('Theory','Lab')),active boolean default true);
create table if not exists courses(id uuid primary key default gen_random_uuid(),name text not null,teacher_id uuid references teachers(id),type text not null check(type in ('Theory','Lab')),sessions_per_week int default 1,active boolean default true);
create table if not exists teacher_unavailability(id uuid primary key default gen_random_uuid(),teacher_id uuid references teachers(id) on delete cascade,day text not null,slot int not null check(slot between 0 and 7));
create table if not exists allocations(id uuid primary key default gen_random_uuid(),day text not null,start_slot int not null check(start_slot between 0 and 7),duration int not null check(duration between 1 and 3),course_id uuid references courses(id),teacher_id uuid references teachers(id),class_id uuid references classes(id),room_id uuid references rooms(id),type text not null check(type in ('Theory','Lab')),created_at timestamptz default now());
create index if not exists allocations_day_slot_idx on allocations(day,start_slot);
create index if not exists allocations_teacher_idx on allocations(teacher_id);
create index if not exists allocations_class_idx on allocations(class_id);
create index if not exists allocations_room_idx on allocations(room_id);
-- Enable RLS before production use. Create authenticated role policies appropriate to the department's workflow.