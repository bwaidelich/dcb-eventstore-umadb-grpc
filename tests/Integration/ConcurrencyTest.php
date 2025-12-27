<?php

declare(strict_types=1);

namespace Wwwision\DCBEventStoreUmaDbGrpc\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversNothing;
use RuntimeException;
use Testcontainers\Container\GenericContainer;
use Testcontainers\Container\StartedTestContainer;
use Testcontainers\Wait\WaitForLog;
use Wwwision\DCBEventStore\EventStore;
use Wwwision\DCBEventStore\Query\Query;
use Wwwision\DCBEventStore\Tests\Integration\EventStoreConcurrencyTestBase;
use Wwwision\DCBEventStoreUmaDbGrpc\UmaDbEventStore;

require_once __DIR__ . '/../../vendor/autoload.php';

#[CoversNothing]
final class ConcurrencyTest extends EventStoreConcurrencyTestBase
{

    private static UmaDbEventStore|null $eventStore = null;
    private static StartedTestContainer|null $testContainer = null;

    public static function prepare(): void
    {
        $eventStore = self::createEventStore();
        if ($eventStore->read(Query::all())->first() !== null) {
            throw new RuntimeException('The event store must not contain any events before preforming consistency tests');
        }
    }

    public static function cleanup(): void
    {
        self::$testContainer?->stop();
        self::$testContainer = null;
        putenv('DCB_TEST_UMADB_URL');
    }

    protected static function createEventStore(): EventStore
    {
        if (self::$eventStore === null) {
            $umaDBUrl = getenv('DCB_TEST_UMADB_URL');
            if (!is_string($umaDBUrl)) {
                self::$testContainer = new GenericContainer('umadb/umadb')
                    ->withExposedPorts(50051)
                    ->withWait((new WaitForLog('UmaDB started'))->withTimeout(120))
                    ->start();
                $umaDBUrl = 'http://' . self::$testContainer->getHost() . ':' . self::$testContainer->getMappedPort(50051);
                putenv('DCB_TEST_UMADB_URL=' . $umaDBUrl);
            }
            self::$eventStore = UmaDbEventStore::create($umaDBUrl);
        }
        return self::$eventStore;
    }

}