# UVAS Conflict-Free Timetable System

Production-ready timetable management for **University of Veterinary & Animal Sciences, Ravi Campus, Pattoki, Department of Statistics and Computer Science**.

## Architecture
- Static frontend: HTML/CSS/JavaScript
- Cloud database/auth: Supabase
- Hosting/serverless API: Netlify
- Source of truth: GitHub `main`
- AI: Gemini through a Netlify Function only
- Browser localStorage is not the production database

## Core scheduling rules
- Monday–Friday
- 11 periods per day, each exactly 50 minutes
- Theory sessions: 1 or 2 consecutive slots
- Labs: exactly 3 consecutive slots / 150 minutes
- Teacher, section, room and lab overlaps are rejected
- Teacher unavailable slots are hard constraints
- Final/locked entries cannot be changed by the generator
- Manual, automatic and AI timetable actions use the same deterministic validator
- PostgreSQL exclusion constraints provide a second database-level protection against overlaps

## Supabase setup
1. Create a Supabase project.
2. Open SQL Editor.
3. Run `supabase/schema.sql` completely.
4. Run `supabase/production_patch.sql` completely. This adds the Auth-to-faculty link and lets teachers edit only their own preference grid.
5. In Supabase Authentication, create the first administrator account.
6. Promote it to admin:

```sql
update public.profiles
set role='admin', full_name='Timetable Administrator', active=true
where id=(select id from auth.users where email='YOUR_ADMIN_EMAIL');
```

7. For a teacher account, link the Auth user to the faculty record:

```sql
update public.faculty
set user_id=(select id from auth.users where email='teacher@example.com')
where name='Teacher Name';
```

New Auth users start as `teacher`. Promote committee/admin accounts from the `profiles` table.

The seed creates the 10 requested faculty records, four classrooms, two labs, BS Computer Science Semesters I/III/V sections, the requested semester-wise courses, and eligible teacher mappings where the prompt supplied alternatives. Ambiguous courses are not given a single guessed final teacher.

## Netlify environment variables
Set these in Netlify Site configuration:

```env
SUPABASE_URL=
SUPABASE_ANON_KEY=
GEMINI_API_KEY=
```

The anonymous Supabase key is safe for browser use when RLS is correctly enabled. Never expose `SUPABASE_SERVICE_ROLE_KEY` or `GEMINI_API_KEY` to browser code. The service-role key is not required by this project.

## Netlify deployment
Connect this GitHub repository to Netlify and deploy the `main` branch. `netlify.toml` configures the static publish directory and `netlify/functions`.

When Netlify GitHub integration is enabled, commits to `main` can trigger production deployments and pull requests can use deploy previews.

## Gemini assistant
The browser calls `/.netlify/functions/gemini`. Gemini converts natural language into strict JSON actions. It cannot write timetable rows directly. The client resolves the action against current master data and validates any timetable mutation through the deterministic engine.

## Course CSV
Template:

```csv
course_code,course_title,program,semester,credit_hours,theory_hours,lab_hours,teacher
```

The importer supports quoted CSV fields, checks required columns, skips duplicate/unknown rows, and adds the supplied teacher as an eligible assignment when that faculty exists.

## Workflow
1. Configure Supabase and create/promote the first admin.
2. Run both SQL files.
3. Configure Netlify environment variables.
4. Sign in.
5. Review faculty, programs, sections, courses and eligible teachers.
6. Enter faculty preferred/available/unavailable periods.
7. Create a draft timetable version.
8. Generate the timetable.
9. Review unscheduled sessions and alternatives.
10. Manually schedule or lock confirmed entries.
11. Validate, print or export.

## Roles
- **Admin:** full management and role administration through Supabase.
- **Timetable Committee:** timetable/resource/course management, generation, locking and audit actions.
- **Teacher:** view schedules and maintain own preferences; cannot edit the final timetable.

## Production notes
Some official course codes were not supplied in the brief. Seed identifiers such as `PF-I` and `ML-V` are working identifiers only. Replace them with official UVAS codes before institutional publication.

The scheduler is deterministic and preference-aware. Hard constraints always win over preferences or AI requests. The database exclusion constraints protect against race-condition overlaps as a second safety layer.
