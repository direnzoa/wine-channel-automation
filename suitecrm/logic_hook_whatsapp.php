<?php
/**
 * SuiteCRM Logic Hook — Notifica n8n quando viene creato/modificato un Contatto con WhatsApp
 *
 * Installazione:
 * 1. Copia questo file in: custom/modules/Contacts/
 * 2. Aggiungi al file custom/modules/Contacts/logic_hooks.php:
 *    $hook_array['after_save'][] = array(1, 'Notifica n8n WhatsApp', 'custom/modules/Contacts/logic_hook_whatsapp.php', 'ContactWhatsAppHook', 'after_save');
 * 3. Esegui Quick Repair and Rebuild da Admin
 */

class ContactWhatsAppHook
{
    private string $n8nWebhookUrl;

    public function __construct()
    {
        // URL webhook n8n — imposta in config.php o variabile d'ambiente
        $this->n8nWebhookUrl = getenv('N8N_WEBHOOK_URL_NUOVO_CONTATTO')
            ?: 'https://n8n.tuodominio.it/webhook/suitecrm-nuovo-contatto';
    }

    public function after_save(string $moduleName, string $event, SugarBean $bean): void
    {
        // Invia webhook solo se il contatto ha un numero WhatsApp
        if (empty($bean->whatsapp_number_c)) {
            return;
        }

        // Invia solo per nuovi contatti (non aggiornamenti)
        if (!$bean->is_new_record()) {
            return;
        }

        $payload = [
            'id'               => $bean->id,
            'first_name'       => $bean->first_name,
            'last_name'        => $bean->last_name,
            'email'            => $bean->email1,
            'phone_mobile'     => $bean->phone_mobile,
            'whatsapp_number_c' => $bean->whatsapp_number_c,
            'tipo_cliente_c'   => $bean->tipo_cliente_c ?? '',
            'tag_evento_c'     => $bean->tag_evento_c ?? '',
            'created_at'       => $bean->date_entered,
        ];

        $this->sendWebhook($payload);
    }

    private function sendWebhook(array $payload): void
    {
        $ch = curl_init($this->n8nWebhookUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            $GLOBALS['log']->error("ContactWhatsAppHook: webhook fallito (HTTP {$httpCode}): {$response}");
        }
    }
}
