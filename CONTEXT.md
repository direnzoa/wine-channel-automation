# Wine Channel Automation — Contesto del Progetto

## Cos'è

Sistema di automazione commerciale per un distributore vinicolo (Wine Channel) che integra **WhatsApp Business**, **PostgreSQL** e **n8n** per gestire le comunicazioni con i clienti attorno a eventi e lanci di prodotto.

Il sistema è in evoluzione verso un **Client Manager proprietario** (app Expo + portale web su winechannel.it) che sostituirà AppSheet + Google Sheets + SuiteCRM.

---

## Architettura attuale

```
srv1458539.hstgr.cloud (VPS Hostinger, Ubuntu 24.04, KVM 2)
├── n8n          → automazioni WhatsApp (https://n8n.srv1458539.hstgr.cloud)
├── PostgreSQL   → database clienti/fiere/iscrizioni (porta 5432, interno)
├── PostgREST    → API REST automatica (https://api.srv1458539.hstgr.cloud)
└── Traefik      → reverse proxy + SSL automatico

winechannel.it (WordPress)
└── portale web client manager (da costruire)

Expo App (da costruire)
└── iOS + Android — client manager mobile
```

---

## Stack tecnologico

| Componente | Ruolo | Stato |
|---|---|---|
| **n8n** | Orchestrazione workflow WhatsApp | ✅ operativo |
| **PostgreSQL 16** | Database principale | ✅ operativo |
| **PostgREST** | API REST su PostgreSQL | ✅ operativo |
| **Traefik** | Reverse proxy + SSL | ✅ operativo |
| **Meta WhatsApp Business API** | Canale messaggistica | ⏳ da attivare |
| **Expo (React Native)** | App mobile iOS/Android | 🔨 da costruire |
| **WordPress (winechannel.it)** | Portale web | 🔨 da costruire |

---

## Database PostgreSQL

**Connessione interna n8n:** `postgresql://wcadmin:***@postgres:5432/winechannel`
**API pubblica:** `https://api.srv1458539.hstgr.cloud`

### Tabelle

| Tabella | Descrizione | Record |
|---|---|---|
| `clienti` | Aziende vinicole (~1331 importate da Google Sheet) | 1331 |
| `azioni` | Interazioni commerciali (telefonata/email/appuntamento) | 0 |
| `eventi` | Fiere ed eventi (Vinitaly, QWine, ecc.) | 0 |
| `iscrizioni` | Partecipanti agli eventi | 0 |
| `utenti` | Utenti del sistema (attualmente 4) | 0 |

### Schema clienti (campi principali)
`ragione_sociale`, `nome_azienda`, `tipologia`, `regione`, `fonte_contatto`, `data_primo_contatto`, `data_ultimo_contatto`, `referente`, `email_aziendale`, `email_personale`, `fisso`, `mobile`, `whatsapp`, `produzione_annua_bottiglie`, `evento_servizio_interesse`, `prodotti_acquistati`, `esito_contatto`, `note`

---

## Workflow n8n attivi

### WhatsApp (tutti disattivati — da attivare dopo configurazione Meta)

| ID | Nome | Trigger | Descrizione |
|---|---|---|---|
| A | Benvenuto Nuovo Contatto | Webhook SuiteCRM | Messaggio benvenuto al nuovo contatto |
| B | Reminder Pre-Evento | Scheduler ore 8:00 | Reminder 7gg e 1gg prima dell'evento |
| C | Follow-up Post Evento | Scheduler ore 10:00 | Ringraziamento post-evento con link materiali |
| D | Ricezione Messaggi | Webhook Meta | Riceve messaggi, crea attività/lead, risposta fuori orario |
| E | Broadcast Pre-Fiera | Manuale | Broadcast segmentato con rate limit 1 msg/sec |

### Utility

| Nome | Descrizione |
|---|---|
| Import Clienti Google Sheet → PostgreSQL | Importazione one-time da Google Sheet (già eseguita) |
| Wine Channel - News Manager | Gestione news WordPress via n8n |
| Wine Channel - Newsletter | Newsletter automatica |
| GitHub Auto-Export Workflows | Backup automatico workflow |
| Error Handler | Gestione errori centralizzata |

---

## Cosa serve per attivare WhatsApp

1. Configurare credenziale **WhatsApp Bearer Token** in n8n
2. Impostare variabili d'ambiente in n8n: `SUITECRM_URL`, `WHATSAPP_PHONE_NUMBER_ID`, `LINK_MATERIALI_BASE`
3. Registrare webhook workflow D in Meta Developer Console: `https://n8n.srv1458539.hstgr.cloud/webhook/whatsapp-inbound`
4. Far approvare i 6 template WhatsApp da Meta
5. Attivare i workflow A, B, C, D (E si avvia manualmente)

---

## Template WhatsApp (da approvare Meta)

- `benvenuto_wine_channel` — primo contatto
- `reminder_evento_7gg` — promemoria 7 giorni prima
- `reminder_evento_1gg` — promemoria 1 giorno prima
- `followup_post_evento` — follow-up post-evento
- `fuori_orario` — risposta automatica fuori orario
- `invito_masterclass` — invito masterclass/degustazione

---

## Prossimi passi (roadmap)

1. **Attivare WhatsApp** — configurare credenziali e template Meta
2. **App Expo** — client manager mobile iOS/Android (sostituisce AppSheet)
3. **Portale web** — interfaccia web su winechannel.it
4. **Workflow WhatsApp aggiornati** — collegare ai nuovi endpoint PostgreSQL invece di SuiteCRM
5. **Gestione iscrizioni eventi** — spostare da Gravity Forms all'app

---

## Sviluppo su più macchine

- **Ufficio**: `C:\Users\Admini\Desktop\Claude Code Projects\wine-channel-automation`
- **Notebook**: `C:\Users\diren\Desktop\Claude Code Projects\wine-channel-automation`
- **Repository**: https://github.com/direnzoa/wine-channel-automation.git
- All'inizio di ogni sessione fare `git pull` per sincronizzare le ultime modifiche.

---

## Progetto correlato

Il CRM vero e proprio (Next.js + Prisma) è nel repository separato **wine-channel-crm**:
- Ufficio: `C:\Users\Admini\Desktop\Claude Code Projects\wine-channel-crm`
- Notebook: `C:\Users\diren\Desktop\Claude Code Projects\wine-channel-crm`
- Repository: https://github.com/direnzoa/wine-channel-crm.git

---

## Note operative

- Il progetto è interamente localizzato in **italiano**
- I workflow n8n sono creati tramite MCP direttamente nell'istanza n8n
- I dati clienti provengono dal Google Sheet AppSheet (1331 record importati il 18/06/2026)
- 4 utenti attivi: `direnzoa@gmail.com`, `winechannelit@gmail.com` + altri 2
- Il VPS scade il **2026-07-04** (rinnovare per tempo)
