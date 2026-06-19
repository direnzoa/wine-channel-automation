# Wine Channel — Roadmap di Progetto

## Visione generale

Sostituire l'ecosistema AppSheet + Google Sheets + SuiteCRM con una suite proprietaria composta da:
- **CRM web** (`crm.winechannel.it`) — già in produzione
- **Automazioni WhatsApp** (n8n sul VPS) — workflow pronti, da attivare
- **App mobile** (Expo iOS/Android) — da costruire
- **Portale clienti** (`winechannel.it`) — da costruire

---

## Fase 1 — CRM Web ✅ In corso / quasi completato

**Repository**: `wine-channel-crm` → https://github.com/direnzoa/wine-channel-crm

### Completato
- [x] Setup Next.js 15 + Prisma + PostgreSQL su VPS Hostinger
- [x] Autenticazione con NextAuth v5 (utenti attivi/disattivi)
- [x] Importazione 1331 clienti da AppSheet/Google Sheet
- [x] Importazione 1030 referenti
- [x] Importazione 224 azioni storiche (202 azioni + 22 appuntamenti)
- [x] CRUD clienti con scheda completa (anagrafica, contatti, referenti, dettagli commerciali)
- [x] Storico azioni per cliente (aggiungi, modifica, elimina)
- [x] Pagina Azioni con filtri, ricerca, modifica inline (drawer)
- [x] Pagina Eventi con CRUD e iscrizioni
- [x] Impostazioni: gestione utenti + voci selezionabili configurabili
- [x] Filtri avanzati clienti (Regione, Esito, Tipologia, Fonte, Evento)
- [x] Export clienti CSV / XLSX con selezione righe e "seleziona tutti"
- [x] Tipo di azione configurabile da Impostazioni (non più hardcoded)
- [x] Layout scheda cliente ottimizzato (storico azioni a destra, sempre visibile)

### Da fare
- [ ] Deploy su `crm.winechannel.it` (dominio + SSL + variabili produzione)
- [ ] Gestione allegati per cliente
- [ ] Dashboard con KPI commerciali (grafici, trend)
- [ ] Notifiche / promemoria interni

---

## Fase 2 — Automazioni WhatsApp ⏳ Workflow pronti, da attivare

**Repository**: `wine-channel-automation` → https://github.com/direnzoa/wine-channel-automation

I 5 workflow sono già creati in n8n e collegati a SuiteCRM. Vanno aggiornati per usare il nuovo CRM PostgreSQL.

### Da fare
- [ ] Configurare credenziale **WhatsApp Bearer Token** in n8n
- [ ] Creare **System User** Meta con token permanente (non scade in 24h)
- [ ] Registrare webhook D in Meta Developer Console: `https://n8n.srv1458539.hstgr.cloud/webhook/whatsapp-inbound`
- [ ] Sottomettere e far approvare i 6 template WhatsApp a Meta (24-48h)
- [ ] Aggiornare i workflow A/B/C/D: sostituire le chiamate SuiteCRM con chiamate al nuovo CRM PostgreSQL (via PostgREST o API Next.js)
- [ ] Impostare variabili d'ambiente in n8n (`WHATSAPP_PHONE_NUMBER_ID`, ecc.)
- [ ] Attivare workflow nell'ordine: D → B → C → A (E è manuale)
- [ ] Test end-to-end con numero di test Meta

### Workflow esistenti
| ID | Nome | Trigger | Stato |
|---|---|---|---|
| A | Benvenuto Nuovo Contatto WhatsApp | Webhook CRM | ⏳ da aggiornare |
| B | Reminder Pre-Evento WhatsApp | Scheduler ore 8:00 | ⏳ da aggiornare |
| C | Follow-up Post Evento WhatsApp | Scheduler ore 10:00 | ⏳ da aggiornare |
| D | Ricezione Messaggi WhatsApp | Webhook Meta | ⏳ da aggiornare |
| E | Broadcast Pre-Fiera WhatsApp | Manuale | ⏳ da aggiornare |

---

## Fase 3 — App Mobile 🔨 Da costruire

App **Expo** (React Native) per iOS e Android — versione mobile del CRM per uso in trasferta/fiera.

### Funzionalità previste
- Login con le stesse credenziali del CRM web
- Lista clienti con ricerca e filtri
- Scheda cliente con storico azioni
- Aggiunta rapida azione (tap sul cliente → "Chiama" / "Email" / "Appuntamento")
- Gestione iscrizioni eventi (check-in in fiera)
- Notifiche push per reminder

### Stack previsto
- Expo + React Native
- Stesse API del CRM web (Next.js API routes)
- Autenticazione JWT condivisa con il CRM

---

## Fase 4 — Portale Web Clienti 🔨 Da costruire

Portale su `winechannel.it` (WordPress o standalone) per i clienti finali.

### Funzionalità previste
- Area riservata per ogni cliente (login)
- Visualizzazione eventi disponibili e iscrizione online
- Download materiali (schede tecniche, listini)
- Storico ordini / acquisti

---

## Note infrastruttura

- **VPS Hostinger** scade il **2026-07-04** — rinnovare prima di quella data
- **Database**: PostgreSQL 16, container Docker `n8n-postgres-1`, DB `winechannel`
- **Dev locale CRM**: attivare SSH tunnel `ssh -L 5432:localhost:5432 root@187.124.26.15 -N` prima di `npm run dev`
- I workflow n8n vanno backuppati (usare il workflow "GitHub Auto-Export Workflows" già presente)
