# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-07-21

### Changed

- Updated the bundled UmaDB gRPC proto to **v0.6.6** and regenerated the PHP client classes.
- Event metadata is now stored in UmaDB's native `metadata` field instead of being packed
  into a JSON blob in `data`. The payload is stored raw in `data`, and the recording
  timestamp is kept under a reserved `umadb:recordedAt` metadata key.

### Added

- Generated client support for the new `Subscribe` and `GetTrackingInfo` RPCs and their
  message types (`SubscribeRequest`/`SubscribeResponse`, `TrackingInfo`,
  `TrackingRequest`/`TrackingResponse`, `MetadataEntry`). These are available on the
  generated `DCBClient` but not yet wired into the event store adapter.

### BREAKING

- The on-disk storage format changed. Event stores written with earlier versions of this
  adapter (JSON blob in `data`) are not backwards-compatible and require a fresh store or a
  data migration.
- Requires a UmaDB server running **v0.6.6** or newer.

## [1.0.0]

- Initial release.
