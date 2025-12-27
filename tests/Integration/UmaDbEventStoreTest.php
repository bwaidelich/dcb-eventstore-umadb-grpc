<?php
declare(strict_types=1);

namespace Wwwision\DCBEventStoreUmaDbGrpc\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversClass;
use Testcontainers\Container\GenericContainer;
use Testcontainers\Container\StartedTestContainer;
use Testcontainers\Wait\WaitForLog;
use Wwwision\DCBEventStore\Event\Event;
use Wwwision\DCBEventStore\Query\Query;
use Wwwision\DCBEventStore\Tests\Integration\EventStoreTestBase;
use Wwwision\DCBEventStoreUmaDbGrpc\UmaDbEventStore;

#[CoversClass(UmaDbEventStore::class)]
final class UmaDbEventStoreTest extends EventStoreTestBase
{
    private StartedTestContainer|null $testContainer = null;

    public function createEventStore(): UmaDbEventStore
    {
        if ($this->testContainer === null) {
            $this->testContainer = new GenericContainer('umadb/umadb')
                ->withExposedPorts(50051)
                ->withWait((new WaitForLog('UmaDB started'))->withTimeout(120))
                ->start();
        }
        return UmaDbEventStore::create($this->testContainer->getHost() . ':' . $this->testContainer->getMappedPort(50051), clock: $this->getTestClock());
    }

    public function tearDown(): void
    {
        $this->testContainer?->stop();
        $this->testContainer = null;
    }

    private static function getCertificatesPath(): string
    {
        return __DIR__ . '/fixtures/certs';
    }

    public function test_with_api_key(): void
    {
        $apiKey = 'test-secret-key';
        $certDir = self::getCertificatesPath();

        $testContainer = new GenericContainer('umadb/umadb')
            ->withExposedPorts(50051)
            ->withEnvironment([
                'UMADB_API_KEY' => $apiKey,
                'UMADB_TLS_CERT' => '/certs/server-cert.pem',
                'UMADB_TLS_KEY' => '/certs/server-key.pem',
            ])
            ->withMount($certDir, '/certs')
            ->withWait((new WaitForLog('UmaDB started'))->withTimeout(120))
            ->start();

        $eventStore = UmaDbEventStore::create(
            $testContainer->getHost() . ':' . $testContainer->getMappedPort(50051),
            apiKey: $apiKey,
            caPath: $certDir . '/server-cert.pem',
            clock: $this->getTestClock()
        );

        // Append events with apiKey configured
        $eventStore->append(Event::create(type: 'SomeEventType', data: 'test-data', tags: ['test:tag']));

        // Read events back to verify connection works
        $events = $eventStore->read(Query::all());
        $eventArray = iterator_to_array($events);

        self::assertCount(1, $eventArray);
        self::assertSame('SomeEventType', $eventArray[0]->event->type->value);
        self::assertSame('test-data', $eventArray[0]->event->data->value);

        $testContainer->stop();
    }

    public function test_connection_with_tls_credentials(): void
    {
        $certDir = self::getCertificatesPath();

        $testContainer = new GenericContainer('umadb/umadb')
            ->withExposedPorts(50051)
            ->withEnvironment([
                'UMADB_TLS_CERT' => '/certs/server-cert.pem',
                'UMADB_TLS_KEY' => '/certs/server-key.pem',
            ])
            ->withMount($certDir, '/certs')
            ->withWait((new WaitForLog('UmaDB started'))->withTimeout(120))
            ->start();

        // Create event store with TLS enabled (no API key)
        $eventStore = UmaDbEventStore::create(
            $testContainer->getHost() . ':' . $testContainer->getMappedPort(50051),
            caPath: $certDir . '/server-cert.pem',
            clock: $this->getTestClock()
        );

        // Verify we can append and read events
        $eventStore->append(Event::create(
            type: 'TestEventType',
            data: 'tls-test-data',
            tags: ['tls:enabled']
        ));

        $events = $eventStore->read(Query::all());
        $eventArray = iterator_to_array($events);

        self::assertCount(1, $eventArray);
        self::assertSame('TestEventType', $eventArray[0]->event->type->value);

        $testContainer->stop();
    }

    public function test_connection_without_tls(): void
    {
        $testContainer = new GenericContainer('umadb/umadb')
            ->withExposedPorts(50051)
            ->withWait((new WaitForLog('UmaDB started'))->withTimeout(120))
            ->start();

        // Creating without apiKey uses insecure credentials
        $eventStore = UmaDbEventStore::create(
            $testContainer->getHost() . ':' . $testContainer->getMappedPort(50051),
            clock: $this->getTestClock()
        );

        // Verify connection works without TLS
        $eventStore->append(Event::create(
            type: 'InsecureEventType',
            data: 'insecure-data'
        ));

        $events = $eventStore->read(Query::all());
        $eventArray = iterator_to_array($events);

        self::assertCount(1, $eventArray);
        self::assertSame('InsecureEventType', $eventArray[0]->event->type->value);

        $testContainer->stop();
    }
}