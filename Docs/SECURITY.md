# Security

## Vorfall vom 2026-05-08

Eine Produktions-Konfigurationsdatei (`.env.production`) war versehentlich
in einem Commit Anfang April 2026 in das öffentliche Repository
`cheffe17/SJJTimer` aufgenommen worden. Sie blieb über einen Zeitraum von
ca. 5 Wochen öffentlich zugänglich, bevor der Vorfall bemerkt und
behoben wurde.

### Betroffene Secrets

- `APP_KEY` (Laravel Application Key)
- `MAIL_PASSWORD` (SMTP-Passwort für `info@ssjtimer.com`)
- Server-Pfade und Username (weniger kritisch, aber preisgegeben)

Da im Chatverlauf zur Server-Bereitstellung zusätzlich das **SSH-Passwort**
im Klartext geteilt worden war und dieses identisch mit dem Mail-Passwort
war, wurde es ebenfalls als kompromittiert betrachtet.

## Durchgeführte Maßnahmen

### Rotationen
- SSH-Passwort des Hosting-Accounts wurde im Hosting-Provider Panel neu gesetzt.
- Mail-Passwort für `info@ssjtimer.com` wurde im Hosting-Provider Panel neu gesetzt.
- `APP_KEY` wurde auf dem Server per `php artisan key:generate` neu erzeugt.

### Repo-Cleanup
- `.env.production` wurde aus dem Working Tree entfernt
  (`git rm --cached`).
- `.gitignore` wurde um `.env.*` (mit Ausnahme `!.env.example`) erweitert.
- Die gesamte Git-History wurde mit `git filter-repo` neu geschrieben,
  sodass `.env.production` aus allen Commits entfernt wurde.
- Alle Branches wurden per `git push --force --all` auf GitHub
  überschrieben.

### Server-Sync
- Der Server (`~/domains/ssjtimer.com/public_html`) wurde manuell auf die
  neue History gebracht (`git fetch` + `git reset --hard origin/main`).
- Konfig-Caches (`config:cache`, `route:cache`, `view:cache`) wurden mit
  dem neuen `APP_KEY` neu gebaut.

### Resterisiko
GitHub kann verwaiste Commits nach einem Force-Push noch einige Zeit über
den direkten Hash erreichbar halten. Der eigentliche Schutz ist daher die
oben genannte Passwort- und Key-Rotation, nicht das Entfernen aus der
History.

## Workflow ab jetzt

### Was gehört ins Repo
- `.env.example` — Template ohne echte Werte, nur Variablen-Namen und
  Platzhalter. Diese Datei ist explizit über `.gitignore` whitelisted.
- Keinerlei andere `.env`-Varianten.

### Was gehört NICHT ins Repo
- `.env`, `.env.local`, `.env.production`, `.env.staging` o.ä. mit
  echten Werten.
- API-Keys, Tokens, Passwörter, Private Keys in irgendeiner Form.
- Backup-Dateien wie `.env.backup`, `*.sql`, `database/database.sqlite`
  mit Echtdaten.

### Wo liegen die echten Werte
- Die produktive `.env` lebt **ausschließlich auf dem Server** unter
  `~/domains/ssjtimer.com/public_html/.env`.
- Lokale Entwicklungs-Werte stehen in einer lokalen `.env` (auch
  ignoriert).
- Änderungen am Set der benötigten Variablen müssen in `.env.example`
  nachgezogen werden, damit ein Setup auf einem neuen Rechner möglich
  bleibt.

### Vor jedem Commit
- `git status` prüfen, dass keine `.env*`-Datei (außer
  `.env.example`) staged ist.
- Niemals Secrets in Commit-Messages, Code-Kommentare oder Test-Fixtures
  schreiben.

### Wenn nochmal was leakt
1. **Zuerst rotieren**, dann erst aufräumen — die History-Bereinigung
   ist Kosmetik gegenüber der Annahme "Wert ist öffentlich".
2. Betroffene Secrets im jeweiligen Anbieter-Panel neu setzen
   (Hosting-Provider Panel, GitHub, Mail-Provider, etc.).
3. History via `git filter-repo` säubern und force-pushen.
4. Server auf die neue History resetten und Caches neu bauen.
5. Diesen Vorfall hier in `Docs/SECURITY.md` ergänzen.
