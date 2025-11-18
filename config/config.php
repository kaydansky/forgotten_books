<?php

$config['db'] = [
    'driver'    => 'mysql',
    'host'      => getenv('DB_HOST') ?: '127.0.0.1',
    'port'      => (int)(getenv('DB_PORT') ?: 3306),
    'database'  => getenv('DB_NAME') ?: 'forgotten_books',
    'username'  => getenv('DB_USER') ?: 'root',
    'password'  => getenv('DB_PASSWORD') ?: '',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'dsn'       => sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        getenv('DB_HOST') ?: '127.0.0.1',
        (int)(getenv('DB_PORT') ?: 3306),
        getenv('DB_NAME') ?: 'forgotten_books'
    ),
    'options'   => [
        \PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/yandex-ca.pem',
        \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true
    ],
];

const EMAIL_SENDER = [
    'host' => '',
    'username' => '',
    'password' => '',
    'port' => 465,
    'protocol' => 'tls',
    'auto_tls' => true,
    'sendername' => 'Forgotten Books',
    'from' => 'noreply@yourhost'
];

const QUEUE_NAMES = [
    'Upload',
    'Proofreader',
    'Proofing Supervisor',
    'Image Editor',
    'Layout Editor',
    'Layout Supervisor',
    'Blurb Writer',
    'Blurb Editor',
    'Cover Artist',
    'Final Approval',
    'Completed',
    'Removed'
];

const ROLE_QUEUE = [
    1   => 1,
    2   => 2,
    4   => 3,
    8   => 4,
    16  => 5,
    32  => 6,
    64  => 7,
    128 => 8,
    256 => 9
];

const QUEUE_FOLDER = [
    0 => '',
    1 => '/Proof/Workers/|/To Do|/Completed',
    2 => '/Proof/Supervisors/|/In Progress|/In Progress',
    3 => '/Images/|/To Do|/In Progress',
    4 => '/Layout/Workers/|/To Do|/Completed',
    5 => '/Layout/Supervisors/|/In Progress|/In Progress',
    6 => '/Cover/Blurbs/Writers/|/To Do|/Completed',
    7 => '/Cover/Blurbs/Editors/|/In Progress|/In Progress',
    8 => '/Cover/Art/|/To Do|/Completed',
    9 => '/Consolidation||',
    10 => '/Consolidation||',
    11 => '/Consolidation||'
];

const EMAIL_CONTENT = [
    'reset_password_subject' => '"Forgotten Books" password reset',
    'reset_password_body' => 'Click <a href="%URL%">HERE</a> in order to reset your password.<br><br>If the link is not working, copy and past the following address into you browser\'s address bar:<br><br>%URL%<br><br>Best regards,<br>"Forgotten Books" Team',
    'reset_password_alt_body' => 'To reset your password, copy and past the following address into you browser\'s address bar:<br><br>%URL%<br><br>Best regards,<br>"Forgotten Books" Team',
    
    'change_password_subject' => 'Your "Forgotten Books" password has been changed',
    'change_password_body' => 'Your "Forgotten Books" password has been changed.',
    'change_password_alt_body' => 'Your "Forgotten Books" password has been changed.',
    
    'new_user_subject' => 'Join "Forgotten Books" Invitation',
    'new_user_body' => 'You are invited to join "Forgotten Books".<br><br>Click <a href="%URL%">HERE</a> to create your password.<br><br>If the link is not working, copy and past the following address into you browser\'s address bar:<br><br>%URL%<br><br>Best regards,<br>"Forgotten Books" Team',
    'new_user_alt_body' => 'You are invited to join "Forgotten Books".<br><br>To create your password, copy and past the following address into you browser\'s address bar:<br><br>%URL%<br><br>Best regards,<br>"Forgotten Books" Team',
];

const PATH_TEMPLATES = __DIR__ . '/../templates' . DIRECTORY_SEPARATOR;

const PATH_TEMPLATES_CONFIG_FILE = __DIR__ . '/../templates/config/config.php';

const PATH_IMAGES = __DIR__ . '/../public/images' . DIRECTORY_SEPARATOR;
