# UVAS Conflict-Free Timetable System

Production-ready timetable management for **University of Veterinary & Animal Sciences, Ravi Campus, Pattoki, Department of Statistics and Computer Science**.

## Architecture
- Static frontend: HTML/CSS/JavaScript
- Cloud database/auth: Supabase
- Hosting/serverless API: Netlify
- Source of truth: GitHub `main`
- AI: Gemini through a Netlify Function only
- Browser localStorage is not used as the production database

## Core scheduling rules
- Monday–Friday
- 11 periods per day, each exactly 50 minutes
- Theory sessions: 1 or 2 consecutive slots
- Labs: exactly 3 consecutive slots / 150 minutes
- Teacher, section, room and lab overlaps are rejected
- Teacher unavailable slots are hard constraints
- Final/locked entries cannot be changed by the generator
- Manual, automatic and AI timetable actions use the same browser-side deterministic validator
- PostgreSQL exclusion constraints additionally prevent overlapping teacher, section, room and lab records at database level

## Supabase setup
1. Create a Supabase project.
2. Open SQL Editor.
3. Run `supabase/schema.sql` completely.
4. In Supabase Authentication, create the first administrator account.
5. After the user is created, run:

```sql
update public.profiles
set role='admin', full_name='Timetable Administrator', active=true
where id=(select id from auth.users where email='YOUR_ADMIN_EMAIL');
```

6. Add additional users through Supabase Auth. New users start as `teacher`; an admin can promote the appropriate account to `committee` or `admin` in the `profiles` table.

The SQL seed creates the 10 faculty records, six resources, BS Computer Science Semesters I/III/V sections, the requested semester-wise courses, and eligible teacher mappings for courses where the prompt provided multiple possible teachers. It does not make a final teacher assignment when eligibility is ambiguous.

## Netlify environment variables
Set these in Netlify Site configuration:

```env
SUPABASE_URL=
SUPABASE_ANON_KEY=
GEMINI_API_KEY=
```

`SUPABASE_ANON_KEY` is intentionally exposed to the browser through the small runtime configuration function and is protected by Supabase RLS. **Never expose `SUPABASE_SERVICE_ROLE_KEY` or `GEMINI_API_KEY` to browser code.** The service-role key is not required by this application.

## Netlify deployment
Connect this GitHub repository to Netlify and deploy the `main` branch. `netlify.toml` points Netlify to the static site and `netlify/functions`.

Every commit to `main` can trigger a production deployment when GitHub integration is enabled in Netlify. Pull-request deploy previews can be enabled in Netlify.

## Gemini assistant
The browser calls `/.netlify/functions/gemini`. The function converts natural-language requests into strict JSON actions. It never receives permission to write directly to the database. The client resolves the action against current master data and sends any timetable mutation through the same hard-constraint validator.

Required secret:

```env
GEMINI_API_KEY=
```

## Course CSV template
Use:

```csv
course_code,course_title,program,semester,credit_hours,theory_hours,lab_hours,teacher
```

The importer supports quoted CSV fields, validates required columns, skips duplicate/unknown records, and creates eligible teacher assignments when the teacher exists.

## Initial workflow
1. Configure Supabase and create the first admin.
2. Configure Netlify environment variables.
3. Open the deployed application and sign in.
4. Review faculty, programs, sections, courses and eligible teachers.
5. Enter teacher preferences: available, preferred, or unavailable.
6. Create a draft timetable version.
7. Generate a timetable.
8. Review unscheduled sessions and suggested alternatives.
9. Manually correct or lock confirmed entries.
10. Validate, print or export the timetable.

## Roles
- **Admin:** full management and user/role administration through Supabase.
- **Timetable Committee:** timetable/resource/course management, generation, locking and audit actions.
- **Teacher:** view schedules and maintain availability/preferences; cannot edit the final timetable.

## Production notes
Academic course codes and some teacher mappings supplied in the project brief were incomplete or ambiguous. Seed codes such as `PF-I` and `ML-V` are stable working identifiers, not claims about official UVAS catalog codes. Replace them with official codes before publication.

The scheduler is deterministic and preference-aware, but it is intentionally maintainable rather than a black-box optimizer. Hard constraints always win. The database exclusion constraints provide a second safety layer against race-condition overlaps.
