<?php
declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) 2020 Juan Pablo Ramirez and Nicolas Masson
 * @link      https://webrider.de/
 * @since     2.2.0
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace CakephpTestMigrator;


use Cake\Datasource\ConnectionManager;

final class TestConnectionManager
{
    /**
     * @var bool
     */
    private static $aliasConnectionIsLoaded = false;

    /**
     * @return void
     */
    public static function aliasConnections()
    {
        if (!self::$aliasConnectionIsLoaded) {
            (new self())->_aliasConnections();
            self::$aliasConnectionIsLoaded = true;
        }
    }

    /**
     * Unset the phinx migration tables from an array of tables.
     *
     * @param  string[] $tables
     * @return array
     */
    public static function unsetMigrationTables(array $tables): array
    {
        $endsWithPhinxlog = function (string $string) {
            $needle = 'phinxlog';
            return substr($string, -strlen($needle)) === $needle;
        };

        foreach ($tables as $i => $table) {
            if ($endsWithPhinxlog($table)) {
                unset($tables[$i]);
            }
        }

        return array_values($tables);
    }

    /**
     * Add aliases for all non test prefixed connections.
     *
     * This allows models to use the test connections without
     * a pile of configuration work.
     *
     * @return void
     */
    protected function _aliasConnections()
    {
        $connections = ConnectionManager::configured();
        ConnectionManager::alias('test', 'default');
        $map = [];
        foreach ($connections as $connection) {
            if ($connection === 'test' || $connection === 'default') {
                continue;
            }
            if (isset($map[$connection])) {
                continue;
            }
            if (strpos($connection, 'test_') === 0) {
                $map[$connection] = substr($connection, 5);
            } else {
                $map['test_' . $connection] = $connection;
            }
        }
        foreach ($map as $testConnection => $normal) {
            ConnectionManager::alias($testConnection, $normal);
        }
    }
}
