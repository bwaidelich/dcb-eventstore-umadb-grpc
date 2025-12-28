<?php

declare(strict_types=1);

namespace Wwwision\DCBEventStoreUmaDbGrpc;

use DateTimeImmutable;
use Grpc\ChannelCredentials;
use Psr\Clock\ClockInterface;
use RuntimeException;
use Umadb\V1\AppendCondition as UmadbAppendCondition;
use Umadb\V1\AppendRequest;
use Umadb\V1\DCBClient;
use Umadb\V1\Event as UmadbEvent;
use Umadb\V1\QueryItem;
use Umadb\V1\Query as UmadbQuery;
use Umadb\V1\ReadRequest;
use Umadb\V1\ReadResponse;
use Umadb\V1\SequencedEvent as UmadbSequencedEvent;
use Webmozart\Assert\Assert;
use Wwwision\DCBEventStore\AppendCondition\AppendCondition;
use Wwwision\DCBEventStore\Event\Event;
use Wwwision\DCBEventStore\Event\Events;
use Wwwision\DCBEventStore\Event\Tags;
use Wwwision\DCBEventStore\EventStore;
use Wwwision\DCBEventStore\Exceptions\ConditionalAppendFailed;
use Wwwision\DCBEventStore\Query\Query;
use Wwwision\DCBEventStore\ReadOptions;
use Wwwision\DCBEventStore\SequencedEvent\SequencedEvent;
use Wwwision\DCBEventStore\SequencedEvent\SequencedEvents;
use Wwwision\DCBEventStore\SequencedEvent\SequencePosition;

final readonly class UmaDbEventStore implements EventStore
{

    public function __construct(
        private DCBClient $client,
        private ClockInterface $clock,
        private string|null $apiKey = null,
    ) {
    }

    /**
     * @param string $hostname UmaDB host name, including port. E.g. `127.0.0.1:50051`
     * @param string|null $apiKey Optional API KEY, see https://umadb.io/cli.html
     * @param string|null $caPath Optional path to CA certificate, see https://umadb.io/cli.html
     * @param ClockInterface|null $clock Optional custom clock for event timestamps
     */
    public static function create(
        string $hostname,
        string|null $apiKey = null,
        string|null $caPath = null,
        ClockInterface|null $clock = null,
    ): self {
        if ($caPath !== null) {
            if (!file_exists($caPath)) {
                throw new RuntimeException(sprintf('CA certificate file not found: %s', $caPath));
            }
            $caCert = file_get_contents($caPath);
            if ($caCert === false) {
                throw new RuntimeException(sprintf('Failed to read CA certificate file: %s', $caPath));
            }
            $credentials = ChannelCredentials::createSsl($caCert);
        } else {
            $credentials = ChannelCredentials::createInsecure();
        }

        $client = new DCBClient($hostname, ['credentials' => $credentials]);
        if ($clock === null) {
            $clock = new class implements ClockInterface {
                public function now(): DateTimeImmutable
                {
                    return new DateTimeImmutable();
                }
            };
        }
        return new self($client, $clock, $apiKey);
    }

    public function read(Query $query, ?ReadOptions $options = null): SequencedEvents
    {
        $request = new ReadRequest();

        $queryProto = $this->convertQuery($query);
        $request->setQuery($queryProto);

        if ($options !== null) {
            if ($options->from !== null) {
                $request->setStart($options->from->value);
            }
            if ($options->backwards) {
                $request->setBackwards(true);
            }
            if ($options->limit) {
                $request->setLimit($options->limit);
            }
        }
        /** @var ReadResponse[] $responses */
        $responses = $this->client->Read($request, $this->getCallMetadata())->responses();
        return SequencedEvents::create(static function () use ($responses) {
            foreach ($responses as $response) {
                foreach ($response->getEvents() as $sequencedEventProto) {
                    yield self::convertEvent($sequencedEventProto);
                }
            }
        });
    }

    public function append(Event|Events $events, ?AppendCondition $condition = null): void
    {
        $request = new AppendRequest();
        if ($events instanceof Event) {
            $events = Events::fromArray([$events]);
        }
        $eventProtos = [];
        foreach ($events as $event) {
            $data = [
                'payload' => $event->data->value,
                'metadata' => $event->metadata->value,
                'recordedAt' => $this->clock->now()->format(DATE_ATOM),
            ];
            $eventProto = new UmadbEvent();
            $eventProto->setEventType($event->type->value);
            $eventProto->setData(json_encode($data, JSON_THROW_ON_ERROR),);
            $eventProto->setTags($event->tags->toStrings());
            $eventProtos[] = $eventProto;
        }
        $request->setEvents($eventProtos);
        if ($condition !== null) {
            $conditionProto = new UmadbAppendCondition();
            if ($condition->after !== null) {
                $conditionProto->setAfter($condition->after->value);
            }
            if ($condition->failIfEventsMatch->hasItems()) {
                $conditionProto->setFailIfEventsMatch($this->convertQuery($condition->failIfEventsMatch));
            }
            $request->setCondition($conditionProto);
        }

        [$_, $status] = $this->client->Append($request, $this->getCallMetadata())->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            if ($condition !== null) {
                if ($condition->after !== null) {
                    throw ConditionalAppendFailed::becauseMatchingEventsExistAfterSequencePosition($condition->after);
                }
                throw ConditionalAppendFailed::becauseMatchingEventsExist();
            }
            throw new RuntimeException(sprintf('Append failed (code: %d): %s', $status->code, $status->details));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getCallMetadata(): array
    {
        if ($this->apiKey === null) {
            return [];
        }
        return ['authorization' => ['Bearer ' . $this->apiKey]];
    }

    private function convertQuery(Query $query): UmadbQuery
    {
        $queryProto = new UmadbQuery();
        $queryItemProtos = [];
        foreach ($query as $item) {
            $queryItemProto = new QueryItem();
            if ($item->eventTypes !== null) {
                $types = [];
                foreach ($item->eventTypes as $eventType) {
                    $types[] = $eventType->value;
                }
                $queryItemProto->setTypes($types);
            }
            if ($item->tags !== null) {
                $queryItemProto->setTags($item->tags->toStrings());
            }
            $queryItemProtos[] = $queryItemProto;
        }
        $queryProto->setItems($queryItemProtos);
        return $queryProto;
    }

    private static function convertEvent(UmadbSequencedEvent $sequencedEventProto): SequencedEvent
    {
        $umaDbEvent = $sequencedEventProto->getEvent();
        if ($umaDbEvent === null) {
            throw new RuntimeException('Failed to read sequenced event');
        }
        $data = json_decode($umaDbEvent->getData(), true, 512, JSON_THROW_ON_ERROR);
        Assert::isArray($data);
        Assert::string($data['recordedAt']);
        Assert::string($data['payload']);
        Assert::isMap($data['metadata']);
        $recordedAt = DateTimeImmutable::createFromFormat(DATE_ATOM, $data['recordedAt']);
        Assert::isInstanceOf($recordedAt, DateTimeImmutable::class);
        return new SequencedEvent(
            SequencePosition::fromInteger((int) $sequencedEventProto->getPosition()),
            $recordedAt,
            Event::create(
                type: $umaDbEvent->getEventType(),
                data: $data['payload'],
                tags: Tags::fromArray(iterator_to_array($umaDbEvent->getTags())),
                metadata: $data['metadata'],
            ),
        );
    }
}
