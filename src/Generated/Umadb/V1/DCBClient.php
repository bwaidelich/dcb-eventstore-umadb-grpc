<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Umadb\V1;

/**
 */
class DCBClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Umadb\V1\ReadRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\ServerStreamingCall
     */
    public function Read(\Umadb\V1\ReadRequest $argument,
      $metadata = [], $options = []) {
        return $this->_serverStreamRequest('/umadb.v1.DCB/Read',
        $argument,
        ['\Umadb\V1\ReadResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Umadb\V1\AppendRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Umadb\V1\AppendResponse>
     */
    public function Append(\Umadb\V1\AppendRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/umadb.v1.DCB/Append',
        $argument,
        ['\Umadb\V1\AppendResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Umadb\V1\HeadRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Umadb\V1\HeadResponse>
     */
    public function Head(\Umadb\V1\HeadRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/umadb.v1.DCB/Head',
        $argument,
        ['\Umadb\V1\HeadResponse', 'decode'],
        $metadata, $options);
    }

}
