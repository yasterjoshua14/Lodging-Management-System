<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     *
     * @var array<string, mixed>
     */
    public array $default = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => '',
        'password'     => '',
        'database'     => '',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
        'foundRows'    => false,
        'dateFormat'   => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    //    /**
    //     * Sample database connection for SQLite3.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'database'    => 'database.db',
    //        'DBDriver'    => 'SQLite3',
    //        'DBPrefix'    => '',
    //        'DBDebug'     => true,
    //        'swapPre'     => '',
    //        'failover'    => [],
    //        'foreignKeys' => true,
    //        'busyTimeout' => 1000,
    //        'synchronous' => null,
    //        'dateFormat'  => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for Postgre.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => '',
    //        'hostname'   => 'localhost',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'database'   => 'ci4',
    //        'schema'     => 'public',
    //        'DBDriver'   => 'Postgre',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'utf8',
    //        'swapPre'    => '',
    //        'failover'   => [],
    //        'port'       => 5432,
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for SQLSRV.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => '',
    //        'hostname'   => 'localhost',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'database'   => 'ci4',
    //        'schema'     => 'dbo',
    //        'DBDriver'   => 'SQLSRV',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'utf8',
    //        'swapPre'    => '',
    //        'encrypt'    => false,
    //        'failover'   => [],
    //        'port'       => 1433,
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for OCI8.
    //     *
    //     * You may need the following environment variables:
    //     *   NLS_LANG                = 'AMERICAN_AMERICA.UTF8'
    //     *   NLS_DATE_FORMAT         = 'YYYY-MM-DD HH24:MI:SS'
    //     *   NLS_TIMESTAMP_FORMAT    = 'YYYY-MM-DD HH24:MI:SS'
    //     *   NLS_TIMESTAMP_TZ_FORMAT = 'YYYY-MM-DD HH24:MI:SS'
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => 'localhost:1521/XEPDB1',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'DBDriver'   => 'OCI8',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'AL32UTF8',
    //        'swapPre'    => '',
    //        'failover'   => [],
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    /**
     * This database connection is used when running PHPUnit database tests.
     *
     * @var array<string, mixed>
     */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => '',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => false,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
        'synchronous' => null,
        'dateFormat'  => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->applyDatabaseUrl();
        $this->applyEnvironmentOverrides();

        // Ensure that we always set the database group to 'tests' if
        // we are currently running an automated test suite, so that
        // we don't overwrite live data on accident.
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }

    private function applyDatabaseUrl(): void
    {
        $databaseURL = $this->firstEnvironmentValue('DATABASE_URL', 'database.default.DSN');

        if ($databaseURL === '') {
            return;
        }

        $parts = parse_url($databaseURL);
        if (! is_array($parts)) {
            return;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (str_starts_with($scheme, 'mysql')) {
            $this->default['DBDriver'] = 'MySQLi';
        } elseif (str_starts_with($scheme, 'postgres')) {
            $this->default['DBDriver'] = 'Postgre';
        }

        if (isset($parts['host'])) {
            $this->default['hostname'] = $parts['host'];
        }

        if (isset($parts['user'])) {
            $this->default['username'] = rawurldecode($parts['user']);
        }

        if (isset($parts['pass'])) {
            $this->default['password'] = rawurldecode($parts['pass']);
        }

        if (isset($parts['path'])) {
            $this->default['database'] = ltrim(rawurldecode($parts['path']), '/');
        }

        if (isset($parts['port'])) {
            $this->default['port'] = (int) $parts['port'];
        }
    }

    private function applyEnvironmentOverrides(): void
    {
        $map = [
            'hostname' => ['DATABASE_HOST', 'database.default.hostname'],
            'database' => ['DATABASE_NAME', 'database.default.database'],
            'username' => ['DATABASE_USER', 'database.default.username'],
            'password' => ['DATABASE_PASSWORD', 'database.default.password'],
            'DBDriver' => ['DATABASE_DRIVER', 'database.default.DBDriver'],
            'DBPrefix' => ['DATABASE_PREFIX', 'database.default.DBPrefix'],
            'charset'  => ['DATABASE_CHARSET', 'database.default.charset'],
            'DBCollat' => ['DATABASE_COLLATION', 'database.default.DBCollat'],
        ];

        foreach ($map as $key => $names) {
            $value = $this->firstEnvironmentValue(...$names);
            if ($value !== '') {
                $this->default[$key] = $value;
            }
        }

        $port = $this->firstEnvironmentValue('DATABASE_PORT', 'database.default.port');
        if ($port !== '') {
            $this->default['port'] = (int) $port;
        }

        $debug = $this->firstEnvironmentValue('DATABASE_DEBUG', 'database.default.DBDebug');
        if ($debug !== '') {
            $this->default['DBDebug'] = filter_var($debug, FILTER_VALIDATE_BOOL);
        } elseif (ENVIRONMENT === 'production') {
            $this->default['DBDebug'] = false;
        }

        $ssl = $this->firstEnvironmentValue('DATABASE_SSL', 'database.default.encrypt');
        if ($ssl !== '') {
            $this->default['encrypt'] = filter_var($ssl, FILTER_VALIDATE_BOOL);
        }
    }

    private function firstEnvironmentValue(string ...$names): string
    {
        foreach ($names as $name) {
            $value = getenv($name);
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }

            $value = env($name);
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return '';
    }
}
