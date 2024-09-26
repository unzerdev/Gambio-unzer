<?php
$t_language_text_section_content_array = [

    //module configuration
    'MODULE_PAYMENT_UNZER_TEXT_TITLE' => 'Unzer Payments',
    'MODULE_PAYMENT_UNZER_TEXT_DESC' => 'Ermöglicht die Zahlung mit den Unzer Zahlungsarten.',
    'MODULE_PAYMENT_UNZER_STATUS_TITLE' => 'Zahlungsmodul aktivieren',
    'MODULE_PAYMENT_UNZER_STATUS_DESC' => 'Möchten Sie Zahlungen mit Unzer akzeptieren?',
    'MODULE_PAYMENT_UNZER_SORT_ORDER_TITLE' => 'Anzeigereihenfolge',
    'MODULE_PAYMENT_UNZER_SORT_ORDER_DESC' => 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.',
    'MODULE_PAYMENT_UNZER_ALLOWED_TITLE' => 'Erlaubte Zonen',
    'MODULE_PAYMENT_UNZER_ALLOWED_DESC' => 'Geben Sie einzeln die Zonen an, welche für dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))',
    'MODULE_PAYMENT_UNZER_ZONE_TITLE' => 'Zahlungszone',
    'MODULE_PAYMENT_UNZER_ZONE_DESC' => 'Wenn eine Zone ausgewählt ist, gilt die Zahlungsmethode nur für diese Zone.',
    'MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_AUTHORIZED_TITLE' => 'Bestellstatus nach Autorisierung',
    'MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_AUTHORIZED_DESC' => '',
    'MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_CAPTURED_TITLE' => 'Bestellstatus nach Zahlungseinzug',
    'MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_CAPTURED_DESC' => '',
    'MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_CHARGEBACK_TITLE' => 'Bestellstatus nach Chargeback',
    'MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_CHARGEBACK_DESC' => '',
    'MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_REFUNDED_TITLE' => 'Bestellstatus nach Erstattung',
    'MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_REFUNDED_DESC' => '',
    'MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_TRIGGER_REFUND_TITLE' => 'Bestellstatus zum Auslösen einer Erstattung',
    'MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_TRIGGER_REFUND_DESC' => '',
    'MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_TRIGGER_CAPTURE_TITLE' => 'Bestellstatus zum Auslösen eines Zahlungseinzugs',
    'MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_TRIGGER_CAPTURE_DESC' => '',
    'MODULE_PAYMENT_UNZER_PUBLIC_KEY_TITLE' => 'Public Key',
    'MODULE_PAYMENT_UNZER_PUBLIC_KEY_DESC' => '',
    'MODULE_PAYMENT_UNZER_PRIVATE_KEY_TITLE' => 'Private Key',
    'MODULE_PAYMENT_UNZER_PRIVATE_KEY_DESC' => '',
    'MODULE_PAYMENT_UNZER_TEXT_INFO' => '',
    'MODULE_PAYMENT_UNZER_CONFIGURE_METHODS' => 'Zahlungsarten konfigurieren',
    'MODULE_PAYMENT_UNZER_CONFIGURE_WEBHOOKS' => 'Webhooks konfigurieren',

    //payment methods
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_EPS'=>'EPS',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_IDEAL'=>'iDEAL',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_ALIPAY'=>'Alipay',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_WECHATPAY'=>'WeChat Pay',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_PREPAYMENT'=>'Vorkasse',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_APPLEPAY'=>'Apple Pay',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_SEPA-DIRECT-DEBIT-SECURED'=>'SEPA Lastschrift gesichert',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_GOOGLEPAY'=>'Google Pay',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_PIS'=>'PIS',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_PAYPAL'=>'PayPal',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_TWINT'=>'TWINT',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_KLARNA'=>'Klarna',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_SEPA-DIRECT-DEBIT'=>'SEPA Lastschrift',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_POST-FINANCE-CARD'=>'Post Finance Card',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_SOFORT'=>'Sofort',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_CARD'=>'Kreditkarte',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_PRZELEWY24'=>'Przelewy24',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_POST-FINANCE-EFINANCE'=>'Post Finance eFinance',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_INVOICE-SECURED'=>'Rechnungskauf',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_INVOICE'=>'Rechnung',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_INSTALLMENT-SECURED'=>'Ratenzahlung',
    'MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_BANCONTACT'=>'Bancontact',

    //payment method configuration page
    'UNZER_CONFIGURATION_TITLE' => 'Unzer Zahlungsarten-Konfiguration',
    'UNZER_CONFIGURATION_INTRO' => 'Hier können Sie die Unzer Zahlungsarten konfigurieren.',
    'UNZER_OPTION_LABEL_LOG_LEVEL_DEBUG' => 'Debug (alles loggen)',
    'UNZER_OPTION_LABEL_LOG_LEVEL_ERROR' => 'Fehler',
    'UNZER_OPTION_LABEL_AUTHORIZE' => 'Autorisierung/Reservierung',
    'UNZER_OPTION_LABEL_CAPTURE' => 'sofortiger Zahlungseinzug',
    'UNZER_ORDER_STATUS_UNCHANGED' => 'Status unverändert lassen',
    'UNZER_CONFIGURATION_SAVE'=>'Speichern',

    //webhook admin page
    'UNZER_WEBHOOKS_TITLE'=>'Unzer Webhooks',
    'UNZER_WEBHOOKS_INTRO'=>'Hier können Sie die Unzer Webhooks konfigurieren.',
    'UNZER_WEBHOOKS_DELETE'=>'Löschen',
    'UNZER_WEBHOOKS_REGISTER'=>'Webhook jetzt registrieren',
    'UNZER_WEBHOOKS_REGISTERED'=>'Webhook registriert ✅',
    'UNZER_WEBHOOKS_NOT_REGISTERED'=>'Webhook nicht registriert. Bitte registrieren Sie den Webhook, indem Sie auf diesen Button klicken:',

    //transaction
    'UNZER_DO_REFUND'=>'Betrag erstatten',
    'UNZER_DO_CAPTURE'=>'Betrag einziehen',
    'UNZER_DO_CANCEL'=>'Zahlung abbrechen',
    'UNZER_TRANSACTIONS'=>'Transaktionen',
    'UNZER_OVERVIEW'=>'Übersicht',
    'UNZER_AMOUNT_REMAINING'=>'Betrag ausstehend',
    'UNZER_AMOUNT_CAPTURED'=>'Betrag eingezogen',
    'UNZER_AMOUNT_REFUNDED'=>'Betrag erstattet',

    //payment instructions
    'UNZER_PAYMENT_INSTRUCTIONS'=>'Bitte überweisen Sie den Betrag auf das folgende Konto:',
    'UNZER_ACCOUNT_HOLDER'=>'Kontoinhaber',
    'UNZER_ACCOUNT_IBAN'=>'IBAN',
    'UNZER_ACCOUNT_BIC'=>'BIC',
    'UNZER_ACCOUNT_DESCRIPTOR'=>'Verwendungszweck',


    'UNZER_GENERIC_ERROR_MESSAGE'=>'Die Zahlung wurde abgebrochen. Bitte versuchen Sie es erneut.',



];