<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Umadb;

use Grpc\BaseStub;
use Grpc\ServerStreamingCall;

/**
 * UmaDB service
 */
final class UmaDBServiceClient extends BaseStub
{

    /**
     * @param array<mixed> $opts channel options
     */
    public function __construct(string $hostname, array $opts, \Grpc\Channel|null $channel = null) // @phpstan-ignore class.notFound
    {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * Read events from the store
     * @param array<mixed> $metadata metadata
     * @param array<mixed> $options call options
     */
    public function read(
        ReadRequestProto $readRequest,
        array $metadata = [],
        array $options = []
    ): ServerStreamingCall {
        return $this->_serverStreamRequest(
            '/umadb.UmaDBService/Read',
            $readRequest,
            ['\Umadb\ReadResponseProto', 'decode'], // @phpstan-ignore argument.type
            $metadata,
            $options
        );
    }

    /**
     * Append events to the store
     * @param array<mixed> $metadata metadata
     * @param array<mixed> $options call options
     * @return \Grpc\UnaryCall<AppendResponseProto>
     */
    public function append(
        AppendRequestProto $appendRequest,
        array $metadata = [],
        array $options = []
    ): \Grpc\UnaryCall {
        return $this->_simpleRequest(
            '/umadb.UmaDBService/Append',
            $appendRequest,
            ['\Umadb\AppendResponseProto', 'decode'], // @phpstan-ignore argument.type
            $metadata,
            $options
        );
    }

    /**
     * Get the current head position of the event store
     * @param array<mixed> $metadata metadata
     * @param array<mixed> $options call options
     * @return \Grpc\UnaryCall<HeadResponseProto>
     */
    public function head(
        HeadRequestProto $headRequest,
        array $metadata = [],
        array $options = []
    ): \Grpc\UnaryCall {
        return $this->_simpleRequest(
            '/umadb.UmaDBService/Head',
            $headRequest,
            ['\Umadb\HeadResponseProto', 'decode'], // @phpstan-ignore argument.type
            $metadata,
            $options
        );
    }
}
