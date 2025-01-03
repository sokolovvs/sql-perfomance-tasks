<?php

require_once __DIR__ . '/../vendor/autoload.php';

ini_set('memory_limit', '2G');

use Faker\Factory;

$operatorQty = 200;
$callQty = 10_000_000;
$taskQty = 10_000;
$batchSize = 10_000;


function generateOperatorData(int $id, \Faker\Generator $faker)
{
    return [
        $id,
        '\'' . $faker->name() . '\''
    ];
}

function generateTaskData(int $id, \Faker\Generator $faker)
{
    return [
        $id,
        '\'' . $faker->company() . '\'',
    ];
}

function generateCallData(int $id, \Faker\Generator $faker, int $operatorQty, int $taskQty)
{
    return [
        $id,
        $faker->numberBetween(1, $operatorQty),
        $faker->numberBetween(1, $taskQty),
        $faker->numberBetween(5, 3600),
        '\'' . $faker->dateTimeInInterval('-1 year 6 months')->format('Y-m-d H:i:s') . '\'',
    ];
}

function batchInsert(PDO $pdo, string $table, array $columns, array $dataBatch)
{
    if (empty($dataBatch)) {
        return;
    }

    $pdo->beginTransaction();

    try {
        $dataBatch = array_map(function (array $row) {
            return '(' . implode(',', $row) . '), ';
        }, $dataBatch);
        $dataBatch = array_reduce($dataBatch, function ($carry, $item) {
            $carry .= $item;
            return $carry;
        });
        $dataBatch = rtrim($dataBatch, ", ");


        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES %s',
            $table,
            implode(', ', $columns),
            $dataBatch
        );


        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $pdo->commit();

        echo "Inserted batch into DB", PHP_EOL;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Failed to insert data into PostgreSQL: " . $e->getMessage() . PHP_EOL;
    }
}

$dbName = getenv('DB_NAME');
$dbUser = getenv('DB_USER');
$dbPassword = getenv('DB_PASSWORD');

$mysql = new PDO(
    "mysql:host=mariadb;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]
);

$pgsql = new PDO(
    "pgsql:host=pgsql;dbname=$dbName;", $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]
);

$faker = Factory::create('ru_RU');

$operators = [];
for ($i = 1; $i <= $operatorQty; $i++) {
    $operators[] = generateOperatorData($i, $faker);
    if ($i % $batchSize === 0) {
        batchInsert($mysql, 'operators', ['id', 'name'], $operators);
        batchInsert($pgsql, 'operators', ['id', 'name'], $operators);
        $operators = [];

        gc_collect_cycles();
    }
}
batchInsert($mysql, 'operators', ['id', 'name'], $operators);
batchInsert($pgsql, 'operators', ['id', 'name'], $operators);

$faker = Factory::create('ru_RU');

$tasks = [];
for ($i = 1; $i <= $taskQty; $i++) {
    $tasks[] = generateTaskData($i, $faker);
    if ($i % $batchSize === 0) {
        batchInsert($mysql, 'tasks', ['id', 'name'], $tasks);
        batchInsert($pgsql, 'tasks', ['id', 'name'], $tasks);
        $tasks = [];
    }

    gc_collect_cycles();
}
batchInsert($mysql, 'tasks', ['id', 'name'], $tasks);
batchInsert($pgsql, 'tasks', ['id', 'name'], $tasks);


$faker = Factory::create();

$calls = [];
$columns = ['id', 'operator_id', 'task_id', 'duration', 'started_at'];
for ($i = 1; $i <= $callQty; $i++) {
    $calls[] = generateCallData($i, $faker, $operatorQty, $taskQty);
    if ($i % $batchSize === 0) {
        batchInsert($mysql, 'calls', $columns, $calls);
        batchInsert($pgsql, 'calls', $columns, $calls);
        $calls = [];
    }

    gc_collect_cycles();
}
batchInsert($mysql, 'calls', $columns, $calls);
batchInsert($pgsql, 'calls', $columns, $calls);

