
<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$kernel = new App\Kernel(
    $_SERVER['APP_ENV'],
    (bool) $_SERVER['APP_DEBUG']
);
$kernel->boot();

$connection = $kernel->getContainer()
    ->get('doctrine')
    ->getConnection();

$rows = $connection->fetchAllAssociative(
    'SELECT id, roles, calendar_show FROM user'
);

$connection->beginTransaction();

try {
    foreach ($rows as $row) {
        $updates = [];

        foreach (['roles', 'calendar_show'] as $column) {
            $value = $row[$column];

            if ($value === null) {
                continue;
            }

            // Already valid JSON? Leave it unchanged.
            if (json_validate($value)) {
                continue;
            }

            if ($value === 'N;') {
                $updates[$column] = null;
                continue;
            }

            $decoded = @unserialize($value, [
                'allowed_classes' => false,
            ]);

            if (!is_array($decoded)) {
                throw new RuntimeException(
                    sprintf(
                        'Invalid serialized array: user %s, column %s',
                        $row['id'],
                        $column
                    )
                );
            }

            $updates[$column] = json_encode($decoded, JSON_THROW_ON_ERROR);
        }

        if ($updates !== []) {
            $connection->update('user', $updates, [
                'id' => $row['id'],
            ]);

            echo sprintf("Updated user %s\n", $row['id']);
        }
    }

    $connection->commit();
    echo "Migration completed.\n";
} catch (Throwable $e) {
    $connection->rollBack();
    throw $e;
} finally {
    $kernel->shutdown();
}
