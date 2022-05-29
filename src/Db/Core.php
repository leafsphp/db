<?php

declare(strict_types=1);

namespace Leaf\Db;

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
     * leaf db connection instance
     */
    protected $connection = null;

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
        if (class_exists('Leaf\App')) app()->config('db', $this->config);

        if ($host !== '') {
            $this->connect($host, $dbname, $user, $password, $dbtype);
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

        // response()

        try {
            $dbtype = $this->config('dbtype');

            $connection = new \PDO(
                $this->dsn(),
                $dbtype === 'sqlite' ? null : $this->config('username'),
                $dbtype === 'sqlite' ? null : $this->config('password'),
                array_merge(
                    $this->config('pdoOptions') ?? [],
                    $pdoOptions
                )
            );

            $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->connection = $connection;

            return $connection;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Connect to database using environment variables
     *
     * @param array $pdoOptions Options for PDO connection
     */
    public function autoConnect(array $pdoOptions = []): \PDO
    {
        return $this->connect(
            [
                'dbtype' => getenv('DB_CONNECTION') ? getenv('DB_CONNECTION') : 'mysql',
                'charset' => getenv('DB_CHARSET'),
                'port' => getenv('DB_PORT') ? getenv('DB_PORT') : '3306',
                'host' => getenv('DB_HOST') ? getenv('DB_HOST') : '127.0.0.1',
                'username' => getenv('DB_USERNAME') ? getenv('DB_USERNAME') : 'root',
                'password' => getenv('DB_PASSWORD') ? getenv('DB_PASSWORD') : '',
                'dbname' => getenv('DB_DATABASE'),
            ],
            '',
            '',
            '',
            '',
            $pdoOptions
        );
    }

    protected function dsn(): string
    {
        $dbtype = $this->config('dbtype');
        $dbname = $this->config('dbname');
        $host = $this->config('host');

        if ($dbtype === 'sqlite') {
            $dsn = "sqlite:$dbname";
        } else {
            $dsn = "$dbtype:host=$host";

            if ($dbname !== '') $dsn .= ";dbname=$dbname";
            if ($this->config('port')) $dsn .= ';port=' . $this->config('port');
            if ($this->config('charset')) $dsn .= ';charset=' . $this->config('charset');
            if ($this->config('unixSocket')) $dsn .= ';unix_socket=' . $this->config('unixSocket');
        }

        return $dsn;
    }

    /**
     * Return the database connection
     *
     * @param \PDO $connection Manual instance of PDO connection
     */
    public function connection(\PDO $connection = null)
    {
        if (!$connection) return $this->connection;
        $this->connection = $connection;
    }

    /**
     * Closes Db connection
     */
    public function close(): void
    {
        $this->connection = null;
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
            if (is_array($name)) {
                $this->config = array_merge($this->config, $name);
                app()->config('db', array_merge(app()->config('db'), $this->config));
            } else {
                return app()->config("db.$name", $value);
            }
        } else {
            if (is_array($name)) {
                $this->config = array_merge($this->config, $name);
            } else {
                if (!$value) {
                    return $this->config[$name];
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
        if ($this->connection === null) {
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
                        trigger_error("$unique not found, Add $unique to your insert or update items or check your spelling.");
                    }

                    if ($this->connection->query("SELECT * FROM {$state['table']} WHERE $unique='{$state['params'][$unique]}'")->fetch(\PDO::FETCH_ASSOC)) {
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
            $this->queryResult = $this->connection->query($state['query']);
        } else {
            $stmt = $this->connection->prepare($state['query']);
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

        $this->execute();
        $result = $this->queryResult->fetch(\PDO::FETCH_ASSOC);

        if (count($added)) {
            $result = array_merge($result, $added);
        }

        if (count($hidden)) {
            foreach ($hidden as $item) {
                unset($result[$item]);
            }
        }

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
        $add = $this->added;
        $hidden = $this->hidden;

        $this->execute();
        $result = $this->queryResult->fetch(\PDO::FETCH_ASSOC);

        if (count($add)) {
            $result = array_merge($result, $add);
        }

        if (count($hidden)) {
            foreach ($hidden as $item) {
                unset($result[$item]);
            }
        }

        return (object) $result;
    }

    /**
     * Fetch the items returned by query
     */
    public function fetchAll($type = 'assoc')
    {
        $added = $this->added;
        $hidden = $this->hidden;

        $this->execute();

        $results = array_map(function ($result) use ($hidden, $added) {
            if (count($hidden)) {
                foreach ($hidden as $item) {
                    unset($result[$item]);
                }
            }

            if (count($added)) {
                $result = array_merge($result, $added);
            }

            return $result;
        }, $this->queryResult->fetchAll(\PDO::FETCH_ASSOC));

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
        $this->table = "";
        $this->query = "";
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
