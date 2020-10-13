<?php

return [
    'validation_error' => 'Hiba történt egy vagy több mező feldolgozásában.',
    'errors' => [
        'return' => 'Előző oldal',
        'home' => 'Kezdőoldal',
        '403' => [
            'header' => 'Forbidden',
            'desc' => 'You do not have permission to access this resource on this server.',
        ],
        '404' => [
            'header' => '404',
            'desc' => 'A keresett oldal nem található!',
        ],
        'installing' => [
            'header' => 'Server Installing',
            'desc' => 'The requested server is still completing the install process. Please check back in a few minutes, you should receive an email as soon as this process is completed.',
        ],
        'suspended' => [
            'header' => 'Server Suspended',
            'desc' => 'This server has been suspended and cannot be accessed.',
        ],
        'maintenance' => [
            'header' => 'Karbantarás alatt',
            'title' => '',
            'desc' => 'A szerver karbantartás alatt van ezért átmenetileg nem elérhető.',
        ],
    ],
    'index' => [
        'header' => 'Szervereid',
        'header_sub' => '',
        'list' => 'Szerverek',
    ],
    'api' => [
        'index' => [
            'list' => 'Your Keys',
            'header' => 'Account API',
            'header_sub' => 'Manage access keys that allow you to perform actions against the panel.',
            'create_new' => 'Create New API key',
            'keypair_created' => 'An API key has been successfully generated and is listed below.',
        ],
        'new' => [
            'header' => 'New API Key',
            'header_sub' => 'Create a new account access key.',
            'form_title' => 'Details',
            'descriptive_memo' => [
                'title' => 'Description',
                'description' => 'Enter a brief description of this key that will be useful for reference.',
            ],
            'allowed_ips' => [
                'title' => 'Allowed IPs',
                'description' => 'Enter a line delimited list of IPs that are allowed to access the API using this key. CIDR notation is allowed. Leave blank to allow any IP.',
            ],
        ],
    ],
    'account' => [
        'details_updated' => 'Adataid módosítása sikeresen megtörtént.',
        'invalid_password' => 'A jelszó amit megadtál helytelen.',
        'header' => 'Fiók',
        'header_sub' => '',
        'update_pass' => 'Jelszó módosítása',
        'update_email' => 'E-mail módosítása',
        'current_password' => 'Aktuális jelszó',
        'new_password' => 'Új jelszó',
        'new_password_again' => 'Új jelszó megerőssítése',
        'new_email' => 'Új e-mail cím',
        'first_name' => 'Keresztnév',
        'last_name' => 'Vezetéknév',
        'update_identity' => 'Adatok frissítése',
        'username_help' => 'Felhasználónevednek egyedinek kell lenni ami csak ezeket a karaktereket tartalmazhatja: :requirements.',
        'language' => 'Nyelv',
    ],
    'security' => [
        'session_mgmt_disabled' => 'Your host has not enabled the ability to manage account sessions via this interface.',
        'header' => 'Account Security',
        'header_sub' => 'Control active sessions and 2-Factor Authentication.',
        'sessions' => 'Active Sessions',
        '2fa_header' => '2-Factor Authentication',
        '2fa_token_help' => 'Enter the 2FA Token generated by your app (Google Authenticator, Authy, etc.).',
        'disable_2fa' => 'Disable 2-Factor Authentication',
        '2fa_enabled' => '2-Factor Authentication is enabled on this account and will be required in order to login to the panel. If you would like to disable 2FA, simply enter a valid token below and submit the form.',
        '2fa_disabled' => '2-Factor Authentication is disabled on your account! You should enable 2FA in order to add an extra level of protection on your account.',
        'enable_2fa' => 'Enable 2-Factor Authentication',
        '2fa_qr' => 'Configure 2FA on Your Device',
        '2fa_checkpoint_help' => 'Use the 2FA application on your phone to take a picture of the QR code to the left, or manually enter the code under it. Once you have done so, generate a token and enter it below.',
        '2fa_disable_error' => 'The 2FA token provided was not valid. Protection has not been disabled for this account.',
    ],
];
