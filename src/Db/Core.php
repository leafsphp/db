<?php

declare(strict_types=1);

namespace Leaf\Db;

use Illuminate\Container\Util;

/**
 * Leaf Db [Core]
 * -------------------------
 * Core functionality of leaf db.
 *
 * @author Michael Darko
 * @since 3.0
 * @version 1.0.0
 */
class Core
{
    /**
     * Config for leaf db
     */
    protected $config = [
        'dbtype' => 'mysql',
        'charset' => null,
        'port' => '3306',
        'unixSocket' => null,
        'host' => '127.0.0.1',
        'username' => 'root',
        'password' => '',
        'dbname' => '',
    ];

    /**
     * Db table to peform operations on
     */
    protected $table = null;

    /**
     * Current connection to use for db
     * @var string|null
     */
    protected $currentConnection = null;

    /**
     * List of connected db instances
     */
    protected $connections = [];

    /**
     * Errors caught in leaf db
     */
    protected $errors = [];

    /**
     * Actual query to run
     */
    protected $query;

    /**
     * Full list of params passed into leaf db
     */
    protected $params = [];

    /**
     * Params bound to query
     */
    protected $bindings = [];

    /**
     * Items to hide from query results
     */
    protected $hidden = [];

    /**
     * Items to add to query results
     */
    protected $added = [];

    /**
     * Items which should be unique in db
     */
    protected $uniques = [];

    /**
     * Items to eager load
     */
    protected $eager = [];

    /**
     * Query result
     *
     * @var \PDOStatement
     */
    protected $queryResult;

    /**
     * Initialize leaf db with a database connection
     *
     * @param string|array $host Host Name or full config
     * @param string $dbname Database name
     * @param string $user Database username
     * @param string $password Database password
     * @param string $dbtype Type of database: mysql, postgres, sqlite, ...
     */
    public function __construct(
        $host = '',
        string $dbname = '',
        string $user = 'root',
        string $password = '',
        string $dbtype = 'mysql'
    ) {
        if (class_exists('Leaf\App')) {
            \Leaf\Config::set('db.config', $this->config);
        }

        if ($host !== '') {
            $this->connect($host, $dbname, $user, $password, $dbtype);
        }
    }

    /**
     * Connect to database immediately
     *
     * @param string|array $host Host Name or full config
     * @param string $dbname Database name
     * @param string $user Database username
     * @param string $password Database password
     * @param string $dbtype Type of database: mysql, postgres, sqlite, ...
     * @param array $pdoOptions Options for PDO connection
     */
    public function connectSync(
        $host = '127.0.0.1',
        string $dbname = '',
        string $user = 'root',
        string $password = '',
        string $dbtype = 'mysql',
        array $pdoOptions = []
    ): \PDO {
        if (is_array($host)) {
            $this->config($host);
        } else {
            $this->config([
                'host' => $host,
                'dbname' => $dbname,
                'username' => $user,
                'password' => $password,
                'dbtype' => $dbtype,
            ]);
        }

        try {
            $dbtype = $this->config('dbtype');

            $connection = new \PDO(
                $this->config('dbUrl') ?? $this->dsn(),
                $dbtype === 'sqlite' ? null : $this->config('username'),
                $dbtype === 'sqlite' ? null : $this->config('password'),
                array_merge(
                    $this->config('pdoOptions') ?? [],
                    $pdoOptions
                )
            );

            $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            return $connection;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Connect to database
     *
     * @param string|array $host Host Name or full config
     * @param string $dbname Database name
     * @param string $user Database username
     * @param string $password Database password
     * @param string $dbtype Type of database: mysql, postgres, sqlite, ...
     * @param array $pdoOptions Options for PDO connection
     */
    public function connect(
        $host = '127.0.0.1',
        string $dbname = '',
        string $user = 'root',
        string $password = '',
        string $dbtype = 'mysql',
        array $pdoOptions = []
    ): Core {
        if (!is_array($host)) {
            $this->config([
                'deferred' => [
                    'host' => $host,
                    'dbname' => $dbname,
                    'username' => $user,
                    'password' => $password,
                    'dbtype' => $dbtype,
                    'pdoOptions' => $pdoOptions,
                ],
            ]);
        } else {
            $this->config([
                'deferred' => $host,
            ]);
        }

        return $this;
    }

    /**
     * Add a list of database connections
     *
     * @param array $connections List of database connections
     * @return Core
     */
    public function addConnections(array $connections, ?string $default = null): Core
    {
        $this->config([
            'connections' => array_merge($this->config('connections') ?? [], $connections),
        ]);

        if ($default) {
            $this->config(['deferred' => $connections[$default]]);
        }

        return $this;
    }

    /**
     * Alias for connect
     *
     * @param string|array $host Host Name or full config
     * @param string $dbname Database name
     * @param string $user Database username
     * @param string $password Database password
     * @param string $dbtype Type of database: mysql, postgres, sqlite, ...
     * @param array $pdoOptions Options for PDO connection
     */
    public function load(
        $host = '127.0.0.1',
        string $dbname = '',
        string $user = 'root',
        string $password = '',
        string $dbtype = 'mysql',
        array $pdoOptions = []
    ): Core {
        return $this->connect($host, $dbname, $user, $password, $dbtype, $pdoOptions);
    }

    /**
     * Connect to database using environment variables
     *
     * @param array $pdoOptions Options for PDO connection
     */
    public function autoConnect(array $pdoOptions = []): Core
    {
        return $this->connect(
            [
                'dbtype' => $this->env('DB_CONNECTION') ?: 'mysql',
                'charset' => $this->env('DB_CHARSET'),
                'port' => $this->env('DB_PORT') ?: '3306',
                'host' => $this->env('DB_HOST') ?: '127.0.0.1',
                'username' => $this->env('DB_USERNAME') ?: 'root',
                'password' => $this->env('DB_PASSWORD') ?: '',
                'dbname' => $this->env('DB_DATABASE'),
            ],
            '',
            '',
            '',
            '',
            $pdoOptions
        );
    }

    /**
     * Returns the value of the environment variable by using Leaf's `_env` primarily.
     * @param string $name
     * @return string|bool
     */
    private function env(string $name): string|bool
    {
        // If `_env` function of Leaf is defined, use it.
        if (function_exists('_env')) {
            return _env($name, false);
        }

        // Return the value if found, otherwise false like getenv().
        return $_ENV[$name] ?? false;
    }

    protected function dsn(): string
    {
        $dbtype = $this->config('dbtype');
        $dbname = $this->config('dbname');
        $host = $this->config('host');

        if ($dbtype === 'sqlite') {
            $dsn = "sqlite:$dbname";
        } elseif ($dbtype === 'sqlsrv') {
            $dsn = $dbtype . ':Server=' . $this->config('host');
            if ($this->config('port')) {
                $dsn .= ',' . $this->config('port');
            }
            $dsn .= ';Database=' . $this->config('dbname');
        } else {
            $dsn = "$dbtype:host=$host";

            if ($dbname !== '') {
                $dsn .= ";dbname=$dbname";
            }
            if ($this->config('port')) {
                $dsn .= ';port=' . $this->config('port');
            }
            if ($this->config('charset')) {
                if ($dbtype === 'pgsql') {
                    $dsn .= ';options=\'--client_encoding=' . $this->config('charset') . '\'';
                } else {
                    $dsn .= ';charset=' . $this->config('charset');
                }
            }
            if ($this->config('unixSocket')) {
                $dsn .= ';unix_socket=' . $this->config('unixSocket');
            }
        }

        return $dsn;
    }

    /**
     * Set the current connection to use for queries
     * @param string|null $connection The name of the connection to use
     * @return Core
     */
    public function use(?string $connection = null)
    {
        $this->currentConnection = $connection;
        return $this;
    }


    /**
     * Return the database connection
     *
     * @param \PDO|string|null $connection Manual instance of PDO connection
     */
    public function connection($connection = null)
    {
        if (is_object($connection)) {
            return $this->connections['default'] = $connection;
        }

        if (is_string($connection)) {
            if (!($this->connections[$connection] ?? false)) {
                $this->connections[$connection] = $this->connectSync($this->config('connections')[$connection]);
            }

            return $this->connections[$connection];
        }

        if (!($this->connection['default'] ?? false) && $this->config('deferred')) {
            $this->connections['default'] = $this->connectSync($this->config('deferred'));
        }

        return $this->connections['default'];
    }

    /**
     * Closes Db connection
     */
    public function close(): void
    {
        $this->connections[$this->currentConnection ?? 'default'] = null;
    }

    /**
     * Returns the ID of the last inserted row or sequence value
     *
     * @param string|null $name Name of the sequence object from which the ID should be returned.
     */
    public function lastInsertId($name = null)
    {
        return $this->connection($this->currentConnection)->lastInsertId($name);
    }

    /**
     * Set the current db table for operations
     *
     * @param string $table Table to perform database operations on
     */
    public function table(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Configure leaf db - syncs with leaf config
     */
    public function config($name, $value = null)
    {
        if (class_exists('Leaf\App') && function_exists('app')) {
            if (!$value && is_string($name)) {
                return $this->config[$name] ?? null;
            }

            $this->config = array_merge($this->config, $name);

            if (!is_array($name)) {
                $this->config[$name] = $value;
            }

            \Leaf\Config::set('db.config', $this->config);
        } else {
            if (is_array($name)) {
                $this->config = array_merge($this->config, $name);
            } else {
                if (!$value) {
                    return $this->config[$name] ?? null;
                } else {
                    $this->config[$name] = $value;
                }
            }
        }
    }

    /**
     * Manually create a database query
     *
     * @param string $sql Full db query
     */
    public function query(string $sql): self
    {
        $this->query = $sql;

        return $this;
    }

    /**
     * Bind parameters to a query
     *
     * @param array|string $data The data to bind to string
     */
    public function bind(...$bindings): self
    {
        $this->bindings = $bindings;

        return $this;
    }

    /**
     * Execute a generated query
     */
    public function execute()
    {
        if ($this->connection($this->currentConnection) === null) {
            trigger_error('Initialise your database first with connect()');
        }

        $state = $this->copyState();
        $this->clearState();

        if (count($state['uniques'])) {
            $IS_UPDATE = is_int(strpos($state['query'], 'UPDATE '));
            $IS_INSERT = is_int(strpos($state['query'], 'INSERT INTO '));

            if ($IS_UPDATE || $IS_INSERT) {
                foreach ($state['uniques'] as $unique) {
                    if (!isset($state['params'][$unique])) {
                        // trigger_error("$unique not found, Add $unique to your insert or update items or check your spelling.");
                        continue;
                    }

                    if ($this->connection($this->currentConnection)->query("SELECT * FROM {$state['table']} WHERE $unique='{$state['params'][$unique]}'")->fetch(\PDO::FETCH_ASSOC)) {
                        $this->errors[$unique] = "$unique already exists";
                    }
                }

                if (count($this->errors)) {
                    Builder::$bindings = [];

                    return null;
                }
            }
        }

        if (count($state['bindings']) === 0) {
            $this->queryResult = $this->connection($this->currentConnection)->query($state['query']);
        } else {
            $stmt = $this->connection($this->currentConnection)->prepare($state['query']);
            $stmt->execute($state['bindings']);

            $this->queryResult = $stmt;
        }

        Builder::$bindings = [];

        return $this->queryResult;
    }

    /**
     * Get raw result of last query
     *
     * @return \PDOStatement
     */
    public function result()
    {
        $this->execute();

        return $this->queryResult;
    }

    /**
     * Fetch column from results
     */
    public function column()
    {
        $this->execute();

        return $this->queryResult->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Get the current count of objects in query
     */
    public function count(): int
    {
        $this->execute();

        return $this->queryResult->rowCount();
    }

    /**
     * Alias for fetchAssoc
     */
    public function assoc()
    {
        return $this->fetchAssoc();
    }

    /**
     * Fetch the items returned by query
     */
    public function fetchAssoc()
    {
        $added = $this->added;
        $hidden = $this->hidden;
        $currentTable = $this->table;

        $hiddenEagerFields = [];

        $this->execute();

        $result = $this->queryResult->fetch(\PDO::FETCH_ASSOC);

        if (count($added)) {
            $result = array_merge($result, $added);
        }

        if (count($hidden)) {
            foreach ($hidden as $item) {
                if (isset($result[$item])) {
                    unset($result[$item]);
                } else if (strpos($item, '.') !== false) {
                    $hiddenEagerFields[] = explode('.', $item);
                }
            }

            $this->hidden = [];
        }

        if (count($this->eager)) {
            foreach ($this->eager as $item) {
                $keyName = Utils::basicSingularize($item['table']);

                if (class_exists('Leaf\Auth\Config') && \Leaf\Auth\Config::get('db.table') === $item['table']) {
                    $hiddenEagerFields = array_merge($hiddenEagerFields, \Leaf\Auth\Config::get('hidden'));
                }

                if ($result[$item['foreignKey']] ?? false) {
                    $result[$keyName] = $this
                        ->connection()
                        ->query("SELECT * FROM {$item['table']} WHERE id = {$result[$item['foreignKey']]}")
                        ->fetch(\PDO::FETCH_ASSOC);
                } else {
                    $keyName = $item['table'];
                    $item['foreignKey'] = Utils::basicSingularize($currentTable) . '_id';

                    $result[$keyName] = $this
                        ->connection()
                        ->query("SELECT * FROM {$item['table']} WHERE {$item['foreignKey']} = {$result['id']}")
                        ->fetchAll(\PDO::FETCH_ASSOC);
                }

                if (count($hiddenEagerFields)) {
                    foreach ($hiddenEagerFields as $field) {
                        if (is_array($field) && $field[0] === $keyName) {
                            $field = $field[1];
                        }

                        if ($field === 'field.id' && class_exists('Leaf\Auth\Config')) {
                            $field = \Leaf\Auth\Config::get('id.key');
                        }

                        if ($field === 'field.password' && class_exists('Leaf\Auth\Config')) {
                            $field = \Leaf\Auth\Config::get('password.key');
                        }

                        unset($result[$keyName][$field]);
                    }
                }
            }

            $this->eager = [];
        }

        $currentTable = null;

        return $result;
    }

    /**
     * Alias for fetchObj
     */
    public function obj()
    {
        return $this->fetchObj();
    }

    /**
     * Fetch the items returned by query
     */
    public function fetchObj()
    {
        return (object) $this->fetchAssoc();
    }

    /**
     * Fetch the items returned by query
     */
    public function fetchAll($type = 'assoc')
    {
        $added = $this->added;
        $hidden = $this->hidden;
        $currentTable = $this->table;

        $eagerForeignKeys = [];
        $hiddenEagerFields = [];

        $this->execute();

        $results = array_map(function ($result) use ($hidden, $added, $currentTable, &$eagerForeignKeys, &$hiddenEagerFields) {
            if (count($this->eager)) {
                foreach ($this->eager as $item) {
                    if (!in_array($result[$item['foreignKey']] ?? $result['id'], $eagerForeignKeys)) {
                        $eagerForeignKeys[] = $result[$item['foreignKey']] ?? $result['id'];
                    }

                    $item['foreignKey'] ??= Utils::basicSingularize($currentTable) . '_id';
                }
            }

            if (count($hidden)) {
                foreach ($hidden as $item) {
                    if (isset($result[$item])) {
                        unset($result[$item]);
                    } else if (strpos($item, '.') !== false) {
                        $hiddenEagerFields[] = explode('.', $item);
                    }
                }
            }

            if (count($added)) {
                $result = array_merge($result, $added);
            }

            return $result;
        }, $this->queryResult->fetchAll(\PDO::FETCH_ASSOC));

        if (count($eagerForeignKeys)) {
            foreach ($this->eager as $item) {
                $keyName = Utils::basicSingularize($item['table']);

                if (class_exists('Leaf\Auth\Config') && \Leaf\Auth\Config::get('db.table') === $item['table']) {
                    $hiddenEagerFields = array_merge($hiddenEagerFields, \Leaf\Auth\Config::get('hidden'));
                }

                if ($results[0][$item['foreignKey']] ?? false) {
                    $eagerResults = $this
                        ->connection()
                        ->query("SELECT * FROM {$item['table']} WHERE id IN (" . implode(',', $eagerForeignKeys) . ")")
                        ->fetchAll(\PDO::FETCH_ASSOC);
                } else {
                    $keyName = $item['table'];
                    $item['foreignKey'] = Utils::basicSingularize($currentTable) . '_id';

                    $eagerResults = $this
                        ->connection()
                        ->query("SELECT * FROM {$item['table']} WHERE {$item['foreignKey']} IN (" . implode(',', $eagerForeignKeys) . ")")
                        ->fetchAll(\PDO::FETCH_ASSOC);
                }

                foreach ($results as $key => $result) {
                    $results[$key][$keyName] = isset($result[$item['foreignKey']])
                        ? $eagerResults[array_search($result[$item['foreignKey']], array_column($eagerResults, 'id'))]
                        : array_values(array_filter($eagerResults, function ($eagerResult) use ($item, $result) {
                            return $eagerResult[$item['foreignKey']] === $result['id'];
                        }) ?? []);

                    if (count($hiddenEagerFields)) {
                        foreach ($hiddenEagerFields as $field) {
                            if (is_array($field) && $field[0] === $keyName) {
                                $field = $field[1];
                            }

                            if ($field === 'field.id' && class_exists('Leaf\Auth\Config')) {
                                $field = \Leaf\Auth\Config::get('id.key');
                            }

                            if ($field === 'field.password' && class_exists('Leaf\Auth\Config')) {
                                $field = \Leaf\Auth\Config::get('password.key');
                            }

                            unset($results[$key][$keyName][$field]);
                        }
                    }
                }
            }

            $this->eager = [];
        }

        $currentTable = null;

        if ($type == 'obj' || $type == 'object') {
            $results = (object) $results;
        }

        return $results;
    }

    /**
     * Alias for fetchAll
     */
    public function all($type = 'assoc')
    {
        return $this->fetchAll($type);
    }

    /**
     * Alias for fetchAll
     */
    public function get($type = 'assoc')
    {
        return $this->fetchAll($type);
    }

    /**
     * Copy internal state
     */
    protected function copyState()
    {
        return [
            'table' => $this->table,
            'query' => $this->query,
            'bindings' => $this->bindings,
            'uniques' => $this->uniques,
            'hidden' => $this->hidden,
            'added' => $this->added,
            'params' => $this->params,
        ];
    }

    /**
     * Prepare leaf db to handle next query
     */
    protected function clearState()
    {
        $this->table = '';
        $this->query = '';
        $this->bindings = [];
        $this->uniques = [];
        $this->hidden = [];
        $this->added = [];
        $this->params = [];
    }

    /**
     * Get the current snapshot of leaf db internals
     */
    public function debug()
    {
        return [
            'query' => $this->query,
            'queryResult' => $this->queryResult,
            'config' => $this->config,
            'connection' => $this->connection,
            'bindings' => $this->bindings,
            'hidden' => $this->hidden,
            'added' => $this->added,
            'uniques' => $this->uniques,
            'errors' => $this->errors,
        ];
    }

    /**
     * Return caught errors if any
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
