## Avvio del progetto

Dopo aver clonato la repository, assicurarsi di essere sul branch `develop`.

Poi, dalla cartella del backend:

```bash
composer install
cp .env.example .env
```

A questo punto va creato un database locale e va configurata la connessione dentro il file `.env`, inserendo i parametri corretti in base al proprio ambiente.

Dopo la configurazione del database:

```bash
php artisan key:generate
php artisan jwt:secret
php artisan migrate:fresh --seed
php artisan serve
php artisan schedule:work
```

L'API sarà disponibile in locale su `http://localhost:8000/`.

## Note

Per la generazione dei dati di esempio utilizzati nei seeder ho usato Codex come supporto operativo.

In particolare, l'ho usato soprattutto per costruire dati utili a testare la parte relativa ai prestiti in ritardo e alle more, quindi situazioni con date passate, prestiti scaduti e casi realistici da verificare durante lo sviluppo.
