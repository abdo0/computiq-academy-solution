<?php

if (env('APP_ENV') == 'local') {
    $MYSQL_PATH = "D:\Ali\laragon\bin\mysql\mysql-5.7.44-winx64\bin\mysql.exe";
    $MYSQLDUMP_PATH = "D:\Ali\laragon\bin\mysql\mysql-5.7.44-winx64\bin\mysqldump.exe";
} else {
    $MYSQL_PATH = 'mysql';
    $MYSQLDUMP_PATH = 'mysqldump';
}

return [
    /*
    |--------------------------------------------------------------------------
    | Custom Backup Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the paths to your database command-line tools.
    | These paths are used by the custom backup and restore functionality.
    | On Windows, you should provide the full path to the executable,
    | for example: 'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe'
    |
    */

    'mysql' => [
        'mysqldump_path' => $MYSQLDUMP_PATH,
        'mysql_path' => $MYSQL_PATH,
    ],

    'pgsql' => [
        'pg_dump_path' => env('PG_DUMP_PATH', 'pg_dump'),
        'psql_path' => env('PGSQL_PATH', 'psql'),
    ],

    'sqlite' => [
        'sqlite3_path' => env('SQLITE3_PATH', 'sqlite3'),
    ],
];
