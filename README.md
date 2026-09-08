# UVAS Conflict-Free Timetable System

Online timetable management system for the Department of Statistics and Computer Science, UVAS Ravi Campus Pattoki.

## Current architecture
- Frontend: static HTML/CSS/JavaScript
- Hosting: Netlify
- Repository: GitHub
- Persistent prototype data: browser localStorage
- AI integration: prepared for Gemini through a secure Netlify Function

## Core rules
- 50-minute timetable slots
- Theory sessions use 1 or 2 consecutive slots
- Labs always use 3 consecutive slots (150 minutes)
- Teacher, class, room and lab overlaps are rejected
- Teacher unavailable slots are hard restrictions
- Automatic scheduling never bypasses deterministic validation

## Deployment
The project is designed for direct Netlify deployment from the `main` branch. No build command is required.

For Gemini, set `GEMINI_API_KEY` as a Netlify environment variable. Never put the key in client-side JavaScript.
