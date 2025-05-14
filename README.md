# Kicktastic
Kicktastic is a CMS-based web application designed to manage local soccer tournaments. It enables team managers to register teams and players, allows league administrators to create schedules and manage the tournament, and engages viewers with match updates and live news.

## Target Users
•	- Local Soccer League Organizers (LeagueAdmin)
•	- Team Managers/Captains (TeamManager)
•	- Players
•	- Fans/Viewers

# Site Architecture and Data Flow
•	Team Registration:
•	TeamManager registers account → logs in → creates team → adds players → waits for schedule
•	Schedule Generation:
•	LeagueAdmin monitors team registrations → when full, triggers auto-scheduler → publishes matches
•	News Feed:
•	Homepage loads soccer news via NewsAPI and caches/display on frontend
