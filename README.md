# UVAS Conflict-Free Timetable Management System

For the Department of Statistics & Computer Science, UVAS Ravi Campus Pattoki.

## Implemented
- Dashboard and responsive interface
- Master data for teachers, courses, classes, rooms/labs
- Teacher unavailability management
- Manual allocation and editing
- Deterministic conflict validation
- 50-minute timetable slots and protected breaks
- Theory sessions of 1-2 slots
- Labs fixed at 3 consecutive slots
- Teacher, class, room and lab collision checks
- Automatic timetable generation
- Teacher workload and quality reports
- CSV export and JSON backup/import
- Print-ready timetable
- Dark mode
- Secure Gemini assistant through a Netlify Function
- Supabase-ready relational schema in `supabase/schema.sql`
- Netlify configuration in `netlify.toml`

## Deployment
Connect this GitHub repository to Netlify and deploy the `main` branch. No build command is required.

Set `GEMINI_API_KEY` in Netlify environment variables to enable the AI assistant. Never place the API key in browser JavaScript.

## Data architecture
The current browser application works immediately with localStorage so the deployed prototype requires no database credentials. `supabase/schema.sql` provides the production database foundation for authenticated multi-user persistence.

## Important production step
Before institutional use, connect the UI data layer to Supabase, enable Row Level Security, configure authenticated roles, and migrate the browser data model to the SQL schema. This keeps the current prototype usable while avoiding hardcoded credentials.
