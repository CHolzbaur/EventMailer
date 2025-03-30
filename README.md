# EventMailerBundle

**ATTENTION: This plugin is currently intended solely for learning purposes for my own use. It does not yet function. If I manage to get the plugin running, I will remove this notice.**

**A Kimai2 plugin for the automatic dispatch of emails upon task assignment.**

## Features

- Sends an email to the user on events – e.g., when a task is assigned to them.
- Applies granular email dispatch rules:
  - Only for specific activities
  - Only for specific users
- Immediate dispatch triggered by the assignment event (`TaskUpdateEvent`)
- Utilizes Symfony Mailer and Kimai’s existing mail configuration (from `.env`)
- Configurable via the Kimai backend (System > Settings)

## Requirements

- **Kimai2 >= 1.13**
- PHP >= 7.4
- Email sending must be correctly configured in `.env` (e.g., `MAILER_DSN`)

## Optional: Custom Fields Plugin

This plugin **requires the separate, paid Kimai Custom Fields Plugin if you want to use settings for users and activities**:

- **User Flag:**  
  If the meta definition `mail_for_user` exists, an email is sent only if a user has set `mail_for_user = 1` in their user preferences.  
  > Table: `kimai2_user_preferences`

- **Activity Flag:**  
  If the meta definition `mail_for_activity` exists, an email is sent only if the respective activity has set `mail_for_activity = 1` in its activity settings.  
  > Table: `kimai2_activities_meta`

- **Fallback:**  
  If the meta field definitions (`kimai2_meta_field_rules`) **do not exist**, then **all users and activities** are considered.

The plugin **also works without the Custom Fields Plugin**, but then no filtering is applied.

## Optional: Tasks Plugin

This plugin has most value when used toghether with **the separate, paid Kimai Tasks Plugin**. This will be at least my usage for this plugin.
