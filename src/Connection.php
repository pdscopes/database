<?php

namespace MadeSimple\Database;

use MadeSimple\Database\Query\QueryBuilder;
use MadeSimple\Database\Statement\StatementBuilder;
use PDO;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Connection
{
    use LoggerAwareTrait;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ConnectorInterface
     */
    public $connector;

    /**
     * @var CompilerInterface
     */
    public $compiler;

    /**
     * @var PDO
     */
    public $pdo;

    /**
     * @var int
     */
    protected $transactions;

    /**
     * Build a new instance of a connection using the specified configuration.
     *
     * @param array                $config
     * @param LoggerInterface|null $logger
     *
     * @return Connection
     */
    public static function factory(array $config, LoggerInterface $logger = null)
    {
        $config = (array) ($config + ['driver' => null]);
        switch ($config['driver']) {
            case 'mysql':
                $connector = new Connector\MySQL($logger);
                $compiler  = new Compiler\MySQL($logger);
                break;

            case 'sqlite':
                $connector = new Connector\SQLite($logger);
                $compiler  = new Compiler\SQLite($logger);
                break;

            default:
                throw new \RuntimeException('Unknown driver: ' . $config['driver']);
        }

        return new Connection($config, $connector, $compiler, $logger);
    }

    /**
     * Connection constructor.
     *
     * @param array                $config
     * @param ConnectorInterface   $connector
     * @param CompilerInterface    $compiler
     * @param LoggerInterface|null $logger
     */
    public function __construct(array $config, ConnectorInterface $connector, CompilerInterface $compiler, LoggerInterface $logger = null)
    {
        $this->setConfig($config);
        $this->setConnector($connector);
        $this->setCompiler($compiler);
        $this->setLogger($logger ?? new NullLogger);

        $this->connect();
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param string     $key
     * @param null|mixed $default
     *
     * @return mixed|null
     */
    public function config($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * @param ConnectorInterface $connector
     */
    public function setConnector(ConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

    /**
     * @param CompilerInterface $compiler
     */
    public function setCompiler(CompilerInterface $compiler)
    {
        $this->compiler = $compiler;
    }

    /**
     * Establishes a connection to the database using the current configuration.
     */
    public function connect()
    {
        $this->pdo = $this->connector->connect($this->config);
    }


    /**
     * @param string $sql
     *
     * @return \PDOStatement
     */
    public function rawQuery($sql)
    {
        return $this->pdo->query($sql);
    }


    /*
     * Create new queries.
     */
    /**
     * @return Query\Select
     */
    public function select()
    {
        return new Query\Select($this, $this->logger);
    }

    /**
     * @return Query\Insert
     */
    public function insert()
    {
        return new Query\Insert($this, $this->logger);
    }

    /**
     * @return Query\Update
     */
    public function update()
    {
        return new Query\Update($this, $this->logger);
    }

    /**
     * @return Query\Delete
     */
    public function delete()
    {
        return new Query\Delete($this, $this->logger);
    }


    /**
     * Requires a Closure with the one QueryBuilder parameter.
     * Calls given Closure with the defined QueryBuilder and
     * returns the result of QueryBuilder::statement();
     *
     * @param \Closure $closure function (QueryBuilder) {}
     * @see QueryBuilder::statement()
     * @return array [PDOStatement, float]
     * @throws \ReflectionException
     */
    public function query(\Closure $closure)
    {
        $reflection = new \ReflectionFunction($closure);
        if ($reflection->getNumberOfParameters() !== 1) {
            throw new \ReflectionException('The closure provided must have as the first parameter a sub class of ' . QueryBuilder::class);
        }
        $reflection = $reflection->getParameters()[0]->getClass();
        if (!$reflection || !$reflection->isSubclassOf(QueryBuilder::class)) {
            $this->logger->critical('Invalid closure signature', ['class' => $reflection->getName()]);
            throw new \ReflectionException('The closure provided must have as the first parameter a sub class of ' . QueryBuilder::class);
        }

        /** @var QueryBuilder $statement */
        $statement = $reflection->newInstance($this, $this->logger);
        $closure($statement);
        return $statement->statement();
    }

    /**
     * Requires a Closure with the one StatementBuilder parameter.
     * Calls given Closure with the defined StatementBuilder and
     * returns the result of StatementBuilder::statement();
     *
     * @param \Closure $closure function (StatementBuilder) {}
     * @see StatementBuilder::statement()
     * @return array [PDOStatement, float]
     * @throws \ReflectionException
     */
    public function statement(\Closure $closure)
    {
        $reflection = new \ReflectionFunction($closure);
        if ($reflection->getNumberOfParameters() !== 1) {
            throw new \ReflectionException('The closure provided must have as the first parameter a sub class of ' . StatementBuilder::class);
        }
        $reflection = $reflection->getParameters()[0]->getClass();
        if (!$reflection || !$reflection->isSubclassOf(StatementBuilder::class)) {
            $this->logger->critical('Invalid closure signature', ['class' => $reflection->getName()]);
            throw new \ReflectionException('The closure provided must have as the first parameter a sub class of ' . StatementBuilder::class);
        }

        /** @var StatementBuilder $statement */
        $statement = $reflection->newInstance($this, $this->logger);
        $closure($statement);
        return $statement->statement();
    }


    /**
     * @return bool
     * @see \PDO::beginTransaction()
     */
    public function beginTransaction()
    {
        if ($this->transactions > 0) {
            $this->transactions++;

            return true;
        }

        if ($this->pdo->beginTransaction()) {
            $this->transactions = 1;

            return true;
        }

        return false;
    }

    /**
     * @return bool
     * @see \PDO::inTransaction()
     */
    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    /**
     * @return bool
     * @see \PDO::rollBack()
     */
    public function rollBack()
    {
        if ($this->transactions < 1) {
            return false;
        }

        if (--$this->transactions == 0) {
            return $this->pdo->rollBack();
        };

        return true;
    }

    /**
     * @return bool
     * @see \PDO::commit()
     */
    public function commit()
    {
        if ($this->transactions < 1) {
            return false;
        }

        if (--$this->transactions == 0) {
            return $this->pdo->commit();
        };

        return true;
    }
}