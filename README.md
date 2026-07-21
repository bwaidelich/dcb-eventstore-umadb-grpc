# Dynamic Consistency Boundary Event Store - UmaDB adapter

[UmaDB](https://github.com/pyeventsourcing/umadb) adapter for the [Dynamic Consistency Boundary implementation](https://github.com/bwaidelich/dcb-eventstore).

## Usage

> [!IMPORTANT]
> This adapter targets the UmaDB gRPC API as of **UmaDB `v0.6.6`** and requires a UmaDB server running that version or newer.
> Since `v0.6.6`, event metadata is stored in UmaDB's native `metadata` field, so event stores written with older versions of this adapter are not backwards-compatible and need to be re-created or migrated.

Install via [composer](https://getcomposer.org):

```shell
composer require wwwision/dcb-eventstore-umadb-grpc
```

### Create instance

```php
use Wwwision\DCBEventStoreUmaDbGrpc\UmaDbEventStore;

$eventStore = UmaDbEventStore::create('127.0.0.1:50051');
```

#### Specify api key / CA certificate

UmaDB can be used with [TLS and API Key](https://umadb.io/cli.html):

```php
use Wwwision\DCBEventStoreUmaDbGrpc\UmaDbEventStore;

$eventStore = UmaDbEventStore::create(
  hostname: '127.0.0.1:50051',
  apiKey: 'secret',
  caPath: '/path/to/certificate.pem',
);
```

See [wwwision/dcb-eventstore](https://github.com/bwaidelich/dcb-eventstore) for more details and usage examples

> [!NOTE]  
> This package communicates with UmaDB via gRPC and requires the [gRPC PHP extension](https://github.com/grpc/grpc/tree/master/src/php) (`ext-grpc`) to be installed
> See [wwwision/dcb-eventstore-umadb](https://github.com/bwaidelich/dcb-eventstore-umadb) for a version that uses the custom [UmaDB PHP extension](https://github.com/bwaidelich/umadb-php) instead


## Contribution

Contributions in the form of [issues](https://github.com/bwaidelich/dcb-eventstore-umadb/issues) or [pull requests](https://github.com/bwaidelich/dcb-eventstore-umadb/pulls) are highly appreciated

## License

See [LICENSE](./LICENSE)