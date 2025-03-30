# EventMailerBundle

**Ein Kimai2-Plugin zum automatischen E-Mail-Versand bei Aufgaben-Zuweisung.**

## Funktionen

- Sendet eine E-Mail an den Benutzer bei Events - z.B. wenn ihm eine Aufgabe zugewiesen wird
- Berücksichtigt granulare E-Mail-Versand-Regeln:
  - Nur bestimmte Tätigkeiten (Activities)
  - Nur bestimmte Benutzer (User)
- Sofortiger Versand beim Zuweisungsevent (`TaskUpdateEvent`)
- Verwendet Symfony Mailer und Kimai's bestehende Mailkonfiguration (`.env`)
- Konfigurierbar über das Kimai-Backend (System > Einstellungen)

## Voraussetzungen

- **Kimai2 >= 1.13**
- PHP >= 7.4
- Mailversand muss in `.env` korrekt konfiguriert sein (z. B. `MAILER_DSN`)

## Optional: Custom Fields Plugin

Dieses Plugin **benötigt das separate, kostenpflichtige Plugin Kimai Custom Fields Plugins falls man Einstellungen für User und Tätigkeiten verwenden möchte**:

- **Benutzer-Flag:**  
  Wenn die Meta-Definition `mail_for_user` existiert, wird nur dann eine E-Mail versendet, wenn ein Benutzer unter `Benutzereinstellungen` → `mail_for_user = 1` gesetzt ist.
  > Tabelle: `kimai2_user_preferences`

- **Aktivitäts-Flag:**  
  Wenn die Meta-Definition `mail_for_activity` existiert, wird nur dann eine E-Mail versendet, wenn die jeweilige Aktivität unter `Aktivitätseinstellungen` → `mail_for_activity = 1` gesetzt ist.
  > Tabelle: `kimai2_activities_meta`

- **Fallback:**  
  Wenn die Meta-Feld-Definitionen (`kimai2_meta_field_rules`) **nicht existieren**, werden **alle Benutzer und Tätigkeiten** berücksichtigt.

Das Plugin **funktioniert auch ohne Custom Fields Plugin**, aber dann gibt es keine Filterung.
