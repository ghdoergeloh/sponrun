# SponRun (Sponsorenlauf-App)

## Beschreibung
SponRun ist eine Anwendung, die Organisationen für einen Sponsorenlauf nutzen können, damit die Läufer sich anmelden können und ihre Sponsoren angeben können. Die Sponsoren können aufgrund dieser Daten kontaktiert werden z.B. für die Mitteilung der Höhe der Spende und für die Quittung.

## Systemanforderungen
- Webserver
- PHP >= 8.0.0
  - OpenSSL PHP Extension
  - PDO PHP Extension
  - Mbstring PHP Extension
  - Tokenizer PHP Extension
- Laravel kompatible Datenbank (<https://laravel.com/docs/database>)

```shell
sudo apt install composer php php-xml php-curl php-mysql
```

## Tools
### Composer
<https://getcomposer.org/>

### npm (für Entwickler)
<https://www.npmjs.com/>

## Installation
In das Installationsverzeichnis wechseln:

```bash
cd $SPONRUN_HOME
```
Projekt auf den Server kopieren:

```bash
git clone -b master https://github.com/ghdoergeloh/sponrun.git .
```
Dem Web-Server Nutzer Schreibrechte für die Verzeichnisse "storage" und "bootstrap/cache" gewähren.

Mit Composer alle PHP-Abhängigkeiten laden:

```bash
composer install --no-dev
```
Die Datei ".env.example" kopieren und in ".env" umbenennen:

```bash
cp .env.example .env
```
Einstellungen für DB,URL,... in der ".env" Datei vornehmen.

App-Key erstellen:

```bash
php artisan key:generate
```
Datenbank erzeugen

Dann das DB-Schema generieren lassen:

```bash
php artisan migrate
```
Abschließend den VirtuellenHost einrichten auf das Verzeichnis "public".

### Admin einrichten
Auf der Webseite registrieren (Der erste User erhält die ID=1)

Dann die Admin-Rolle in die Datenbank einfügen und dem User (mit ID=1) die Rolle zuweisen.

```bash
php artisan db:seed --class=RolesAndFirstAdmin
```


### Für Entwicklung
Alle PHP-Abhängigkeiten für Entwickler laden:

```bash
composer install
```

Mit npm alle JS-Abhängigkeiten laden

```bash
npm install
```

## Update
```bash
cd $SPONRUN_HOME
git fetch origin master
git pull origin master -f
composer install --no-dev
php artisan migrate
```

Abschließend .env mit .env.example vergleichen, ob sich etwas geändert hat.