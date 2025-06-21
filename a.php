<?php return array (
    'aliyunsms' =>
        array (
            'region_id' => 'cn-hangzhou',
            'access_key' => NULL,
            'access_secret' => NULL,
            'sign_name' => NULL,
        ),
    'app' =>
        array (
            'wallet_api' => '',
            'socket_io_port' => '2000',
            'http_worker_port' => '2001',
            'text_worker_port' => '2003',
            'web_server_port' => '2002',
            'worker_push_url' => '127.0.0.1',
            'demo_mode' => '0',
            'btc' => '',
            'bch' => '',
            'eth' => '',
            'trx' => '',
            'usdt_omni' => '',
            'usdt_erc20' => '',
            'usdt_trc20' => '',
            'ltc' => '',
            'name' => 'angela',
            'env' => 'local',
            'debug' => true,
            'url' => 'http://localhost',
            'timezone' => 'America/New_York',
            'locale' => 'zh',
            0 => 'en',
            1 => 'hk',
            'fallback_locale' => 'en',
            'key' => 'base64:/2levZvt1OgyotQ9Su0ZyXwRb0AX2OFeIoGB0Fn9BVE=',
            'cipher' => 'AES-256-CBC',
            'log' => 'single',
            'log_level' => 'debug',
            'providers' =>
                array (
                    0 => 'Illuminate\\Auth\\AuthServiceProvider',
                    1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
                    2 => 'Illuminate\\Bus\\BusServiceProvider',
                    3 => 'Illuminate\\Cache\\CacheServiceProvider',
                    4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
                    5 => 'Illuminate\\Cookie\\CookieServiceProvider',
                    6 => 'Illuminate\\Database\\DatabaseServiceProvider',
                    7 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
                    8 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
                    9 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
                    10 => 'Illuminate\\Hashing\\HashServiceProvider',
                    11 => 'Illuminate\\Mail\\MailServiceProvider',
                    12 => 'Illuminate\\Notifications\\NotificationServiceProvider',
                    13 => 'Illuminate\\Pagination\\PaginationServiceProvider',
                    14 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
                    15 => 'Illuminate\\Queue\\QueueServiceProvider',
                    16 => 'Illuminate\\Redis\\RedisServiceProvider',
                    17 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
                    18 => 'Illuminate\\Session\\SessionServiceProvider',
                    19 => 'Illuminate\\Translation\\TranslationServiceProvider',
                    20 => 'Illuminate\\Validation\\ValidationServiceProvider',
                    21 => 'Illuminate\\View\\ViewServiceProvider',
                    22 => 'App\\Providers\\AppServiceProvider',
                    23 => 'App\\Providers\\AuthServiceProvider',
                    24 => 'App\\Providers\\EventServiceProvider',
                    25 => 'App\\Providers\\RouteServiceProvider',
                ),
            'aliases' =>
                array (
                    'App' => 'Illuminate\\Support\\Facades\\App',
                    'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
                    'Auth' => 'Illuminate\\Support\\Facades\\Auth',
                    'Blade' => 'Illuminate\\Support\\Facades\\Blade',
                    'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast',
                    'Bus' => 'Illuminate\\Support\\Facades\\Bus',
                    'Cache' => 'Illuminate\\Support\\Facades\\Cache',
                    'Config' => 'Illuminate\\Support\\Facades\\Config',
                    'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
                    'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
                    'DB' => 'Illuminate\\Support\\Facades\\DB',
                    'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
                    'Event' => 'Illuminate\\Support\\Facades\\Event',
                    'File' => 'Illuminate\\Support\\Facades\\File',
                    'Gate' => 'Illuminate\\Support\\Facades\\Gate',
                    'Hash' => 'Illuminate\\Support\\Facades\\Hash',
                    'Lang' => 'Illuminate\\Support\\Facades\\Lang',
                    'Log' => 'Illuminate\\Support\\Facades\\Log',
                    'Mail' => 'Illuminate\\Support\\Facades\\Mail',
                    'Notification' => 'Illuminate\\Support\\Facades\\Notification',
                    'Password' => 'Illuminate\\Support\\Facades\\Password',
                    'Queue' => 'Illuminate\\Support\\Facades\\Queue',
                    'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
                    'Redis' => 'Illuminate\\Support\\Facades\\Redis',
                    'Request' => 'Illuminate\\Support\\Facades\\Request',
                    'Response' => 'Illuminate\\Support\\Facades\\Response',
                    'Route' => 'Illuminate\\Support\\Facades\\Route',
                    'Schema' => 'Illuminate\\Support\\Facades\\Schema',
                    'Session' => 'Illuminate\\Support\\Facades\\Session',
                    'Storage' => 'Illuminate\\Support\\Facades\\Storage',
                    'URL' => 'Illuminate\\Support\\Facades\\URL',
                    'Validator' => 'Illuminate\\Support\\Facades\\Validator',
                    'View' => 'Illuminate\\Support\\Facades\\View',
                ),
        ),
    'auth' =>
        array (
            'defaults' =>
                array (
                    'guard' => 'web',
                    'passwords' => 'users',
                ),
            'guards' =>
                array (
                    'web' =>
                        array (
                            'driver' => 'session',
                            'provider' => 'users',
                        ),
                    'api' =>
                        array (
                            'driver' => 'token',
                            'provider' => 'users',
                        ),
                ),
            'providers' =>
                array (
                    'users' =>
                        array (
                            'driver' => 'eloquent',
                            'model' => 'App\\User',
                        ),
                ),
            'passwords' =>
                array (
                    'users' =>
                        array (
                            'provider' => 'users',
                            'table' => 'password_resets',
                            'expire' => 60,
                        ),
                ),
        ),
    'broadcasting' =>
        array (
            'default' => 'log',
            'connections' =>
                array (
                    'pusher' =>
                        array (
                            'driver' => 'pusher',
                            'key' => '',
                            'secret' => '',
                            'app_id' => '',
                            'options' =>
                                array (
                                ),
                        ),
                    'redis' =>
                        array (
                            'driver' => 'redis',
                            'connection' => 'default',
                        ),
                    'log' =>
                        array (
                            'driver' => 'log',
                        ),
                    'null' =>
                        array (
                            'driver' => 'null',
                        ),
                ),
        ),
    'cache' =>
        array (
            'default' => 'redis',
            'stores' =>
                array (
                    'apc' =>
                        array (
                            'driver' => 'apc',
                        ),
                    'array' =>
                        array (
                            'driver' => 'array',
                        ),
                    'database' =>
                        array (
                            'driver' => 'database',
                            'table' => 'cache',
                            'connection' => NULL,
                        ),
                    'file' =>
                        array (
                            'driver' => 'file',
                            'path' => '/www/wwwroot/okx866.top/storage/framework/cache/data',
                        ),
                    'memcached' =>
                        array (
                            'driver' => 'memcached',
                            'persistent_id' => NULL,
                            'sasl' =>
                                array (
                                    0 => NULL,
                                    1 => NULL,
                                ),
                            'options' =>
                                array (
                                ),
                            'servers' =>
                                array (
                                    0 =>
                                        array (
                                            'host' => '127.0.0.1',
                                            'port' => 11211,
                                            'weight' => 100,
                                        ),
                                ),
                        ),
                    'redis' =>
                        array (
                            'driver' => 'redis',
                            'connection' => 'default',
                        ),
                ),
            'prefix' => 'laravel',
        ),
    'database' =>
        array (
            'default' => 'mysql',
            'connections' =>
                array (
                    'sqlite' =>
                        array (
                            'driver' => 'sqlite',
                            'database' => 'okx866_top',
                            'prefix' => '',
                        ),
                    'mysql' =>
                        array (
                            'driver' => 'mysql',
                            'host' => '127.0.0.1',
                            'port' => '3306',
                            'database' => 'okx866_top',
                            'username' => 'okx866_top',
                            'password' => 'AmZXiPab3wkbbbeE',
                            'unix_socket' => '',
                            'charset' => 'utf8mb4',
                            'collation' => 'utf8mb4_unicode_ci',
                            'prefix' => '',
                            'strict' => false,
                            'engine' => NULL,
                        ),
                    'pgsql' =>
                        array (
                            'driver' => 'pgsql',
                            'host' => '127.0.0.1',
                            'port' => '3306',
                            'database' => 'okx866_top',
                            'username' => 'okx866_top',
                            'password' => 'AmZXiPab3wkbbbeE',
                            'charset' => 'utf8',
                            'prefix' => '',
                            'schema' => 'public',
                            'sslmode' => 'prefer',
                        ),
                    'sqlsrv' =>
                        array (
                            'driver' => 'sqlsrv',
                            'host' => '127.0.0.1',
                            'port' => '3306',
                            'database' => 'okx866_top',
                            'username' => 'okx866_top',
                            'password' => 'AmZXiPab3wkbbbeE',
                            'charset' => 'utf8',
                            'prefix' => '',
                        ),
                ),
            'migrations' => 'migrations',
            'redis' =>
                array (
                    'client' => 'predis',
                    'default' =>
                        array (
                            'host' => '127.0.0.1',
                            'password' => '',
                            'port' => '6379',
                            'database' => 0,
                        ),
                ),
        ),
    'elasticsearch' =>
        array (
            'hosts' =>
                array (
                    0 => 'http://127.0.0.1:9300',
                ),
        ),
    'filesystems' =>
        array (
            'default' => 'local',
            'cloud' => 's3',
            'disks' =>
                array (
                    'local' =>
                        array (
                            'driver' => 'local',
                            'root' => '/www/wwwroot/okx866.top/storage/app',
                        ),
                    'public' =>
                        array (
                            'driver' => 'local',
                            'root' => '/www/wwwroot/okx866.top/storage/app/public',
                            'url' => 'http://localhost/storage',
                            'visibility' => 'public',
                        ),
                    's3' =>
                        array (
                            'driver' => 's3',
                            'key' => NULL,
                            'secret' => NULL,
                            'region' => NULL,
                            'bucket' => NULL,
                        ),
                ),
        ),
    'mail' =>
        array (
            'driver' => 'smtp',
            'host' => 'smtp.mailtrap.io',
            'port' => '2525',
            'from' =>
                array (
                    'address' => 'hello@example.com',
                    'name' => 'Example',
                ),
            'encryption' => NULL,
            'username' => NULL,
            'password' => NULL,
            'sendmail' => '/usr/sbin/sendmail -bs',
            'markdown' =>
                array (
                    'theme' => 'default',
                    'paths' =>
                        array (
                            0 => '/www/wwwroot/okx866.top/resources/views/vendor/mail',
                        ),
                ),
        ),
    'phpmail' =>
        array (
            'host' => 'smtp.mailtrap.io',
            'port' => '2525',
            'username' => NULL,
            'password' => NULL,
        ),
    'queue' =>
        array (
            'default' => 'sync',
            'connections' =>
                array (
                    'sync' =>
                        array (
                            'driver' => 'sync',
                        ),
                    'database' =>
                        array (
                            'driver' => 'database',
                            'table' => 'jobs',
                            'queue' => 'default',
                            'retry_after' => 90,
                        ),
                    'beanstalkd' =>
                        array (
                            'driver' => 'beanstalkd',
                            'host' => 'localhost',
                            'queue' => 'default',
                            'retry_after' => 90,
                        ),
                    'sqs' =>
                        array (
                            'driver' => 'sqs',
                            'key' => 'your-public-key',
                            'secret' => 'your-secret-key',
                            'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
                            'queue' => 'your-queue-name',
                            'region' => 'us-east-1',
                        ),
                    'redis' =>
                        array (
                            'driver' => 'redis',
                            'connection' => 'default',
                            'queue' => 'default',
                            'retry_after' => 90,
                        ),
                ),
            'failed' =>
                array (
                    'database' => 'mysql',
                    'table' => 'failed_jobs',
                ),
        ),
    'services' =>
        array (
            'mailgun' =>
                array (
                    'domain' => NULL,
                    'secret' => NULL,
                ),
            'ses' =>
                array (
                    'key' => NULL,
                    'secret' => NULL,
                    'region' => 'us-east-1',
                ),
            'sparkpost' =>
                array (
                    'secret' => NULL,
                ),
            'stripe' =>
                array (
                    'model' => 'App\\User',
                    'key' => NULL,
                    'secret' => NULL,
                ),
        ),
    'session' =>
        array (
            'driver' => 'redis',
            'lifetime' => 120,
            'expire_on_close' => false,
            'encrypt' => false,
            'files' => '/www/wwwroot/okx866.top/storage/framework/sessions',
            'connection' => NULL,
            'table' => 'sessions',
            'store' => NULL,
            'lottery' =>
                array (
                    0 => 2,
                    1 => 100,
                ),
            'cookie' => 'angela_session',
            'path' => '/',
            'domain' => NULL,
            'secure' => false,
            'http_only' => true,
            'same_site' => NULL,
        ),
    'view' =>
        array (
            'paths' =>
                array (
                    0 => '/www/wwwroot/okx866.top/resources/views',
                ),
            'compiled' => '/www/wwwroot/okx866.top/storage/framework/views',
        ),
    'websocket' =>
        array (
            'client' =>
                array (
                    'callback_class' => 'App\\Utils\\Workerman\\WorkerCallback',
                    'process_num' => 9,
                ),
        ),
    'trustedproxy' =>
        array (
            'proxies' =>
                array (
                    0 => '192.168.1.10',
                ),
            'headers' =>
                array (
                    1 => 'FORWARDED',
                    2 => 'X_FORWARDED_FOR',
                    4 => 'X_FORWARDED_HOST',
                    8 => 'X_FORWARDED_PROTO',
                    16 => 'X_FORWARDED_PORT',
                ),
        ),
    'excel' =>
        array (
            'cache' =>
                array (
                    'enable' => true,
                    'driver' => 'memory',
                    'settings' =>
                        array (
                            'memoryCacheSize' => '32MB',
                            'cacheTime' => 600,
                        ),
                    'memcache' =>
                        array (
                            'host' => 'localhost',
                            'port' => 11211,
                        ),
                    'dir' => '/www/wwwroot/okx866.top/storage/cache',
                ),
            'properties' =>
                array (
                    'creator' => 'Maatwebsite',
                    'lastModifiedBy' => 'Maatwebsite',
                    'title' => 'Spreadsheet',
                    'description' => 'Default spreadsheet export',
                    'subject' => 'Spreadsheet export',
                    'keywords' => 'maatwebsite, excel, export',
                    'category' => 'Excel',
                    'manager' => 'Maatwebsite',
                    'company' => 'Maatwebsite',
                ),
            'sheets' =>
                array (
                    'pageSetup' =>
                        array (
                            'orientation' => 'portrait',
                            'paperSize' => '9',
                            'scale' => '100',
                            'fitToPage' => false,
                            'fitToHeight' => true,
                            'fitToWidth' => true,
                            'columnsToRepeatAtLeft' =>
                                array (
                                    0 => '',
                                    1 => '',
                                ),
                            'rowsToRepeatAtTop' =>
                                array (
                                    0 => 0,
                                    1 => 0,
                                ),
                            'horizontalCentered' => false,
                            'verticalCentered' => false,
                            'printArea' => NULL,
                            'firstPageNumber' => NULL,
                        ),
                ),
            'creator' => 'Maatwebsite',
            'csv' =>
                array (
                    'delimiter' => ',',
                    'enclosure' => '"',
                    'line_ending' => '
',
                    'use_bom' => false,
                ),
            'export' =>
                array (
                    'autosize' => true,
                    'autosize-method' => 'approx',
                    'generate_heading_by_indices' => true,
                    'merged_cell_alignment' => 'left',
                    'calculate' => false,
                    'includeCharts' => false,
                    'sheets' =>
                        array (
                            'page_margin' => false,
                            'nullValue' => NULL,
                            'startCell' => 'A1',
                            'strictNullComparison' => false,
                        ),
                    'store' =>
                        array (
                            'path' => '/www/wwwroot/okx866.top/storage/exports',
                            'returnInfo' => false,
                        ),
                    'pdf' =>
                        array (
                            'driver' => 'DomPDF',
                            'drivers' =>
                                array (
                                    'DomPDF' =>
                                        array (
                                            'path' => '/www/wwwroot/okx866.top/vendor/dompdf/dompdf/',
                                        ),
                                    'tcPDF' =>
                                        array (
                                            'path' => '/www/wwwroot/okx866.top/vendor/tecnick.com/tcpdf/',
                                        ),
                                    'mPDF' =>
                                        array (
                                            'path' => '/www/wwwroot/okx866.top/vendor/mpdf/mpdf/',
                                        ),
                                ),
                        ),
                ),
            'filters' =>
                array (
                    'registered' =>
                        array (
                            'chunk' => 'Maatwebsite\\Excel\\Filters\\ChunkReadFilter',
                        ),
                    'enabled' =>
                        array (
                        ),
                ),
            'import' =>
                array (
                    'heading' => 'slugged',
                    'startRow' => 1,
                    'separator' => '_',
                    'slug_whitelist' => '._',
                    'includeCharts' => false,
                    'to_ascii' => true,
                    'encoding' =>
                        array (
                            'input' => 'UTF-8',
                            'output' => 'UTF-8',
                        ),
                    'calculate' => true,
                    'ignoreEmpty' => false,
                    'force_sheets_collection' => false,
                    'dates' =>
                        array (
                            'enabled' => true,
                            'format' => false,
                            'columns' =>
                                array (
                                ),
                        ),
                    'sheets' =>
                        array (
                            'test' =>
                                array (
                                    'firstname' => 'A2',
                                ),
                        ),
                ),
            'views' =>
                array (
                    'styles' =>
                        array (
                            'th' =>
                                array (
                                    'font' =>
                                        array (
                                            'bold' => true,
                                            'size' => 12,
                                        ),
                                ),
                            'strong' =>
                                array (
                                    'font' =>
                                        array (
                                            'bold' => true,
                                            'size' => 12,
                                        ),
                                ),
                            'b' =>
                                array (
                                    'font' =>
                                        array (
                                            'bold' => true,
                                            'size' => 12,
                                        ),
                                ),
                            'i' =>
                                array (
                                    'font' =>
                                        array (
                                            'italic' => true,
                                            'size' => 12,
                                        ),
                                ),
                            'h1' =>
                                array (
                                    'font' =>
                                        array (
                                            'bold' => true,
                                            'size' => 24,
                                        ),
                                ),
                            'h2' =>
                                array (
                                    'font' =>
                                        array (
                                            'bold' => true,
                                            'size' => 18,
                                        ),
                                ),
                            'h3' =>
                                array (
                                    'font' =>
                                        array (
                                            'bold' => true,
                                            'size' => 13.5,
                                        ),
                                ),
                            'h4' =>
                                array (
                                    'font' =>
                                        array (
                                            'bold' => true,
                                            'size' => 12,
                                        ),
                                ),
                            'h5' =>
                                array (
                                    'font' =>
                                        array (
                                            'bold' => true,
                                            'size' => 10,
                                        ),
                                ),
                            'h6' =>
                                array (
                                    'font' =>
                                        array (
                                            'bold' => true,
                                            'size' => 7.5,
                                        ),
                                ),
                            'a' =>
                                array (
                                    'font' =>
                                        array (
                                            'underline' => true,
                                            'color' =>
                                                array (
                                                    'argb' => 'FF0000FF',
                                                ),
                                        ),
                                ),
                            'hr' =>
                                array (
                                    'borders' =>
                                        array (
                                            'bottom' =>
                                                array (
                                                    'style' => 'thin',
                                                    'color' =>
                                                        array (
                                                            0 => 'FF000000',
                                                        ),
                                                ),
                                        ),
                                ),
                        ),
                ),
        ),
    'ide-helper' =>
        array (
            'filename' => '_ide_helper',
            'format' => 'php',
            'meta_filename' => '.phpstorm.meta.php',
            'include_fluent' => false,
            'include_factory_builders' => false,
            'write_model_magic_where' => true,
            'write_model_relation_count_properties' => true,
            'write_eloquent_model_mixins' => false,
            'include_helpers' => false,
            'helper_files' =>
                array (
                    0 => '/www/wwwroot/okx866.top/vendor/laravel/framework/src/Illuminate/Support/helpers.php',
                ),
            'model_locations' =>
                array (
                    0 => 'app',
                ),
            'ignored_models' =>
                array (
                ),
            'extra' =>
                array (
                    'Eloquent' =>
                        array (
                            0 => 'Illuminate\\Database\\Eloquent\\Builder',
                            1 => 'Illuminate\\Database\\Query\\Builder',
                        ),
                    'Session' =>
                        array (
                            0 => 'Illuminate\\Session\\Store',
                        ),
                ),
            'magic' =>
                array (
                ),
            'interfaces' =>
                array (
                ),
            'custom_db_types' =>
                array (
                ),
            'model_camel_case_properties' => false,
            'type_overrides' =>
                array (
                    'integer' => 'int',
                    'boolean' => 'bool',
                ),
            'include_class_docblocks' => false,
        ),
    'tinker' =>
        array (
            'commands' =>
                array (
                ),
            'dont_alias' =>
                array (
                    0 => 'App\\Nova',
                ),
        ),
);
