## Avvio del progetto

Dopo aver clonato la repository, assicurarsi di essere sul branch `develop`.

Poi, dalla cartella del backend:

```bash
composer install
cp .env.example .env
```

A questo punto va configurata la connessione MySQL dentro il file `.env` con questi valori:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=library
DB_USERNAME=root
DB_PASSWORD=
```

Prima di eseguire le migration bisogna collegarsi al server MySQL locale su `127.0.0.1:3306` con il proprio client e creare il database `library`.

Esempio query:

```sql
CREATE DATABASE library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Dopo la creazione del database:

```bash
php artisan key:generate
php artisan jwt:secret
php artisan migrate
php artisan migrate:fresh --seed
php artisan serve
php artisan schedule:work
```

L'API sarà disponibile in locale su `http://localhost:8000/`.

Gli utenti da utilizzare dopo il seed sono definiti in [database/seeders/UserSeeder.php](/Volumes/Archivio macmini/library-api/database/seeders/UserSeeder.php):

- mario.rossi@example.com,Password123
- luigi.verdi@example.com,Password123

## Note

Per la generazione dei dati di esempio utilizzati nei seeder ho usato Codex come supporto operativo.

In particolare, l'ho usato soprattutto per costruire dati utili a testare la parte relativa ai prestiti in ritardo e alle more, quindi situazioni con date passate, prestiti scaduti e casi realistici da verificare durante lo sviluppo.
