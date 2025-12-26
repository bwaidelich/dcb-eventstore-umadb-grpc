# Dynamic Consistency Boundary Event Store - UmaDB adapter

[UmaDB](https://github.com/pyeventsourcing/umadb) adapter for the [Dynamic Consistency Boundary implementation](https://github.com/bwaidelich/dcb-eventstore).

## Usage

Install via [composer](https://getcomposer.org):

```shell
composer require wwwision/dcb-eventstore-umadb-grpc
```

### Create instance

```php
use Wwwision\DCBEventStoreUmaDb\UmaDbEventStore;

$eventStore = UmaDbEventStore::create('http://127.0.0.1:50051');
```

See [wwwision/dcb-eventstore](https://github.com/bwaidelich/dcb-eventstore) for more details and usage examples

> [!NOTE]  
> This package requires the custom [UmaDB PHP extension](https://github.com/bwaidelich/umadb-php) to be installed
> See [wwwision/dcb-eventstore-umadb-grpc](https://github.com/bwaidelich/dcb-eventstore-umadb-grpc) for a version that uses gRPC


## Contribution

Contributions in the form of [issues](https://github.com/bwaidelich/dcb-eventstore-umadb/issues) or [pull requests](https://github.com/bwaidelich/dcb-eventstore-umadb/pulls) are highly appreciated

## License

See [LICENSE](./LICENSE)