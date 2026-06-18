# Guida al Deploy — Wine Channel Automation

## Prerequisiti

- n8n installato e raggiungibile da internet (per i webhook Meta)
- SuiteCRM funzionante
- Account Meta Developer con app WhatsApp Business

---

## STEP 1 — Setup Meta WhatsApp Business API

### 1.1 Crea l'app Meta

1. Vai su [developers.facebook.com](https://developers.facebook.com)
2. Clicca **Create App** → tipo: **Business**
3. Nome app: `WineChannel-Automation`
4. Aggiungi il prodotto **WhatsApp**

### 1.2 Configura il numero WhatsApp Business

1. In **WhatsApp → API Setup**, annota:
   - **Phone Number ID** → `WHATSAPP_PHONE_NUMBER_ID`
   - **WhatsApp Business Account ID** → `WHATSAPP_BUSINESS_ACCOUNT_ID`
2. Genera il **Temporary Access Token** (scade in 24h)
   - Per produzione: crea un **System User** con token permanente
   - **App Settings → Advanced → System Users** → crea utente con ruolo Admin
   - Genera token con permessi: `whatsapp_business_messaging`, `whatsapp_business_management`

### 1.3 Configura il Webhook

1. In **WhatsApp → Configuration → Webhook**:
   - **Callback URL**: `https://n8n.tuodominio.it/webhook/whatsapp-inbound`
   - **Verify Token**: il valore di `WHATSAPP_WEBHOOK_VERIFY_TOKEN` nel tuo `.env`
2. Clicca **Verify and Save**
3. Sottoscrivi agli eventi: `messages`, `message_deliveries`, `message_reads`

> **Nota**: Meta richiede che il webhook risponda con il `hub.challenge` per la verifica.
> Il Workflow D gestisce automaticamente sia la verifica (GET) che i messaggi (POST).
> Aggiungi anche un nodo webhook separato per il GET di verifica se necessario.

---

## STEP 2 — Configura le Credenziali in n8n

In n8n → **Settings → Credentials**, crea:

### SuiteCRM Basic Auth
- Tipo: **Basic Auth**
- Nome: `SuiteCRM Basic Auth`
- Username: admin SuiteCRM
- Password: password SuiteCRM

### WhatsApp Bearer Token
- Tipo: **HTTP Header Auth**
- Nome: `WhatsApp Bearer Token`
- Header Name: `Authorization`
- Header Value: `Bearer EAAxxxxxxxx` (il tuo access token Meta)

---

## STEP 3 — Configura le Variabili d'Ambiente in n8n

In n8n → **Settings → Variables**, aggiungi:

| Variabile | Valore |
|-----------|--------|
| `SUITECRM_URL` | `https://crm.tuodominio.it` |
| `WHATSAPP_PHONE_NUMBER_ID` | ID dal portale Meta |
| `LINK_MATERIALI_BASE` | `https://materiali.winechannel.it` |
| `BROADCAST_TIPO_CLIENTE` | `buyer` (modifica prima di ogni campagna) |
| `BROADCAST_TAG_EVENTO` | `Vinitaly2026` |
| `BROADCAST_TEMPLATE_NAME` | `invito_masterclass` |
| `BROADCAST_NOME_EVENTO` | `Vinitaly 2026` |
| `BROADCAST_DATA_EVENTO` | `7-10 Aprile 2026` |

---

## STEP 4 — Configura SuiteCRM

### 4.1 Aggiungi campo personalizzato `whatsapp_number_c`

1. SuiteCRM → **Admin → Studio → Contacts → Fields**
2. **Add Field**: tipo `Phone`, nome `whatsapp_number`, etichetta `WhatsApp`
3. Aggiungi il campo al layout (Detail View e Edit View)

### 4.2 Aggiungi campo `tipo_cliente_c`

1. Tipo: `DropDown`
2. Opzioni: `importatore`, `distributore`, `sommelier`, `privato`, `buyer`

### 4.3 Aggiungi campo `tag_evento_c`

1. Tipo: `Tag` o `Relate` (associa agli eventi)
2. Alternative: usa il campo `Campaigns` per i tag

### 4.4 Installa il Logic Hook (per Workflow A)

1. Copia `suitecrm/logic_hook_whatsapp.php` in `custom/modules/Contacts/`
2. Crea/modifica `custom/modules/Contacts/logic_hooks.php`:

```php
<?php
$hook_array['after_save'][] = array(
    1,
    'Notifica n8n WhatsApp',
    'custom/modules/Contacts/logic_hook_whatsapp.php',
    'ContactWhatsAppHook',
    'after_save'
);
```

3. Admin → **Quick Repair and Rebuild** → **Repair Relationships**

---

## STEP 5 — Sottometti i Template WhatsApp a Meta

1. Vai su **Meta Business Suite → WhatsApp Manager → Message Templates**
2. Clicca **Create Template**
3. Per ogni file in `whatsapp/templates/`:
   - Incolla il contenuto del campo `"text"` nel body
   - Imposta la categoria come indicato nel JSON
   - Attendi l'approvazione (24-48h)
4. I template approvati diventano disponibili con il nome esatto nel campo `"name"`

---

## STEP 6 — Attiva i Workflow in n8n

1. Apri n8n → **Workflows**
2. Attiva in questo ordine:
   - **D — Ricezione Messaggi WhatsApp** (attivalo per primo!)
   - **B — Reminder Pre-Evento WhatsApp**
   - **C — Follow-up Post Evento WhatsApp**
   - **A — Benvenuto Nuovo Contatto WhatsApp**
   - **E — Broadcast Pre-Fiera WhatsApp** (manuale, non serve attivarlo)

---

## STEP 7 — Test

### Test Workflow D (ricezione)
Invia un messaggio WhatsApp al numero di test Meta. Verifica in n8n → Executions che il workflow si attivi.

### Test Workflow A (benvenuto)
Crea un contatto di test in SuiteCRM con un numero WhatsApp. Verifica che arrivi il messaggio.

### Test Workflow B (reminder)
Crea un evento in SuiteCRM con data = domani. Modifica temporaneamente il trigger per testare manualmente.

---

## Note sui Limiti WhatsApp

- **Tier gratuito**: 1.000 conversazioni/mese con utenti unici
- **Limit API**: circa 80 messaggi/secondo (i workflow usano 1/secondo per sicurezza)
- **Template**: necessari per messaggi outbound (a contatti che non hanno scritto nelle ultime 24h)
- **Finestra 24h**: puoi rispondere liberamente a messaggi ricevuti entro 24h
