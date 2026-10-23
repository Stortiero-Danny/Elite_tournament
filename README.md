# Elite Tournament 🎮

**Elite Tournament** è una piattaforma web per la gestione di tornei di videogiochi, realizzata come progetto personale per mettere in pratica lo sviluppo web full stack, la progettazione di database relazionali e la gestione di utenti e competizioni.

🌐 **[Visita la demo online](https://elite-tournament-tan.vercel.app/)**

## Funzionalità principali

- **Account e profili:** registrazione, accesso, gestione del profilo e ruoli utente.
- **Tornei:** creazione e gestione delle competizioni, iscrizione dei partecipanti e consultazione delle informazioni.
- **Squadre:** creazione e gestione dei team, inviti e partecipazione ai tornei a squadre.
- **Check-in:** conferma della presenza dei partecipanti prima dell'avvio dei tornei.
- **Incontri:** generazione e visualizzazione del tabellone, dei match e dei risultati.
- **Interazioni tra utenti:** amicizie e notifiche relative alle attività della piattaforma.
- **Amministrazione:** funzioni riservate alla gestione di utenti, giochi e tornei.

> **Nota sulla demo:** alcune funzionalità, tra cui il caricamento e la modifica delle immagini, sono temporaneamente sospese nella versione online.

## Tecnologie utilizzate

| Area | Tecnologie |
| --- | --- |
| Frontend | HTML, CSS, JavaScript |
| Backend | PHP |
| Database | MySQL |
| Sviluppo locale | XAMPP |
| Hosting applicazione | Vercel |
| Hosting database | Aiven |
| Versionamento | Git e GitHub |

## Come funziona

L'applicazione usa **PHP** per gestire la logica lato server, l'autenticazione, le richieste dei form e le operazioni sul database. **MySQL** conserva le informazioni relative a utenti, giochi, squadre, tornei, iscrizioni, notifiche e incontri. L'interfaccia è realizzata con **HTML, CSS e JavaScript**.

Il progetto è stato sviluppato inizialmente in ambiente locale con XAMPP e successivamente adattato per una demo online con applicazione su Vercel e database su Aiven.

## Prova il progetto

➡️ **[Apri Elite Tournament](https://elite-tournament-tan.vercel.app/)**

La demo permette di esplorare l'interfaccia e le funzionalità attualmente disponibili. Alcune operazioni richiedono un account; le funzionalità amministrative sono destinate ai ruoli autorizzati.

## Esecuzione in locale

Il progetto è pensato per un ambiente con **PHP e MySQL**, ad esempio XAMPP.

1. Clona il repository nella cartella servita dal web server (ad esempio `htdocs`):

   ```bash
   git clone https://github.com/Stortiero-Danny/Elite_tournament.git
   ```

2. Avvia Apache e MySQL.
3. Crea il database MySQL e importa lo schema SQL del progetto, se disponibile nel repository.
4. Configura le credenziali del database tramite le variabili d'ambiente previste dall'applicazione (`DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`, `DB_PORT`) e gli eventuali requisiti SSL della connessione.
5. Apri il progetto dal browser tramite l'indirizzo locale del server.

> Le credenziali e le informazioni riservate **non devono essere caricate nel repository**. La configurazione per l'ambiente locale può richiedere adattamenti rispetto alla distribuzione online.

## Obiettivi del progetto

Questo progetto mi ha permesso di approfondire:

- lo sviluppo di un'applicazione web con funzionalità frontend e backend;
- la modellazione di un database relazionale e l'utilizzo di query SQL;
- le operazioni CRUD e l'impiego di prepared statement;
- l'organizzazione di flussi applicativi articolati, come iscrizioni, check-in e gestione dei match;
- il versionamento con Git e la pubblicazione di un'applicazione web.

## Stato del progetto

**Demo online disponibile.** Il progetto è in evoluzione: alcune funzionalità possono essere aggiornate, migliorate o temporaneamente disabilitate.

---

**Progetto personale a scopo formativo e dimostrativo.**
