<?php

namespace rockunit;

use rock\base\Alias;
use rock\db\common\ConnectionInterface;
use rock\db\Migration;
use rock\helpers\ArrayHelper;
use rock\helpers\Instance;
use rock\sphinx\Connection;

/**
 * Base class for the Sphinx test cases.
 */
class SphinxTestCase extends \PHPUnit_Framework_TestCase
{
    public static $params;
    /**
     * @var array Sphinx connection configuration.
     */
    protected $sphinxConfig = [
        'dsn' => 'mysql:host=127.0.0.1;port=9306;',
        'username' => '',
        'password' => '',
    ];
    /**
     * @var Connection Sphinx connection instance.
     */
    protected $sphinx = 'sphinx';
    /**
     * @var array Database connection configuration.
     */
    protected $dbConfig = [
        'dsn' => 'mysql:host=127.0.0.1;',
        'username' => '',
        'password' => '',
    ];
    /**
     * @var \rock\db\Connection database connection instance.
     */
    protected $connection = 'db';

    protected function setUp()
    {
        parent::setUp();
        $this->up();
    }

    public function up()
    {
        if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('pdo and pdo_mysql extension are required.');
        }
        $config = self::getParam('sphinx');
        if (!empty($config)) {
            $this->sphinxConfig = $config['sphinx'];
            $this->dbConfig = $config['db'];
        }

        // check whether sphinx is running and skip tests if not.
        if (preg_match('/host=([\w\d.]+)/i', $this->sphinxConfig['dsn'], $hm) && preg_match('/port=(\d+)/i', $this->sphinxConfig['dsn'], $pm)) {
            if (!@stream_socket_client($hm[1] . ':' . $pm[1], $errorNumber, $errorDescription, 0.5)) {
                $this->markTestSkipped('No Sphinx searchd running at ' . $hm[1] . ':' . $pm[1] . ' : ' . $errorNumber . ' - ' . $errorDescription);
            }
        }
    }

    protected function tearDown()
    {
        if ($this->sphinx instanceof Connection) {
            $this->sphinx->close();
        }
    }

    /**
     * @param  boolean                $reset whether to clean up the test database
     * @param  boolean                $open  whether to open test database
     * @return \rock\sphinx\Connection
     */
    public function getConnection($reset = false, $open = true)
    {
        if (!$reset && $this->sphinx instanceof Connection) {
            return $this->sphinx;
        }
        $config = ArrayHelper::intersectByKeys($this->sphinxConfig, ['dsn', 'username', 'password', 'attributes']);
        if (!isset($config['class'])) {
            $config['class'] = Connection::className();
        }
        /** @var Connection $connection */
        $connection = Instance::ensure($config);

        if ($open) {
            $connection->open();
        }
        $this->sphinx = $connection;

        return $connection;
    }

    /**
     * Truncates the runtime index.
     * @param string $indexName index name.
     */
    protected function truncateRuntimeIndex($indexName)
    {
        if ($this->sphinx) {
            $this->sphinx->createCommand('TRUNCATE RTINDEX ' . $indexName)->execute();
        }
    }

    /**
     * @param  boolean            $reset whether to clean up the test database
     * @param  boolean            $open  whether to open and populate test database
     * @return \rock\db\Connection
     */
    public function getDbConnection($reset = true, $open = true)
    {
        if (!$reset && $this->connection instanceof \rock\db\Connection) {
            return $this->connection;
        }
        $config = $this->dbConfig;

        $fixture = isset($config['fixture']) ? $config['fixture'] : null;
        $migrations = isset($config['migrations']) ? $config['migrations'] : [];
        $config = ArrayHelper::intersectByKeys($config, ['dsn', 'username', 'password', 'attributes']);
        //$config['class'] = \rock\db\Connection::className();

        try {
            $this->connection = $this->prepareDatabase($config, $fixture, $open, $migrations);
        } catch (\Exception $e) {
            $this->markTestSkipped("Something wrong when preparing database: " . $e->getMessage());
        }
        return $this->connection;
    }


    /**
     * @param $config
     * @param $fixture
     * @param bool $open
     * @param array $migrations
     * @return Connection
     * @throws \rock\helpers\InstanceException
     */
    public function prepareDatabase($config, $fixture, $open = true, array $migrations = [])
    {
        if (!isset($config['class'])) {
            $config['class'] = \rock\db\Connection::className();
        }
        /** @var \rock\db\Connection $connection */
        $connection = Instance::ensure($config);
        if (!$open) {
            return $connection;
        }

        $connection->open();
        if ($fixture !== null) {
            $lines = explode(';', file_get_contents($fixture));
            foreach ($lines as $line) {
                if (trim($line) !== '') {
                    $connection->pdo->exec($line);
                }
            }
        }

        $this->applyMigrations($connection, $migrations);

        return $connection;
    }

    protected function applyMigrations(ConnectionInterface $connection, array $migrations)
    {
        foreach ($migrations as $config) {
            $config = array_merge(['connection' => $connection, 'enableVerbose' => false], $config);
            /** @var Migration $migration */
            $migration = Instance::ensure($config);
            $migration->up();
        }
    }

    /**
     * Returns a test configuration param from /data/config.php
     * @param  string $name    params name
     * @param  mixed  $default default value to use when param is not set.
     * @return mixed  the value of the configuration param
     */
    public static function getParam($name, $default = null)
    {
        if (static::$params === null) {
            static::$params = require(Alias::getAlias('@rockunit/data/config.php'));
        }

        return isset(static::$params[$name]) ? static::$params[$name] : $default;
    }
}