# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.6.1...HEAD)

## [v1.6.1](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.6.0...v1.6.1) - 2023-02-14

### Fixed

- Use sidecar.aws_region value in saveToS3 method ([#48](https://github.com/stefanzweifel/sidecar-browsershot/pull/48))

## [v1.6.0](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.5.0...v1.6.0) - 2023-02-04

### Added

- Add Support for Laravel 10 ([#45](https://github.com/stefanzweifel/sidecar-browsershot/pull/45))

## [v1.5.0](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.4.1...v1.5.0) - 2022-12-28

### Added

- Load Emoji Font to support Emoji Characters ([#42](https://github.com/stefanzweifel/sidecar-browsershot/pull/42))

## [v1.4.1](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.4.0...v1.4.1) - 2022-11-25

### Fixed

- Trim base64 PDF Response by overriding base64pdf method ([#38](https://github.com/stefanzweifel/sidecar-browsershot/pull/38))

## [v1.4.0](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.3.0...v1.4.0) - 2022-11-06

### Added

- Add PHP 8.2 Support ([#35](https://github.com/stefanzweifel/sidecar-browsershot/pull/35))

### Fixed

- Fix Typo in browsershot.js ([#31](https://github.com/stefanzweifel/sidecar-browsershot/pull/31))

## [v1.3.0](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.2.1...v1.3.0) - 2022-08-15

### Changed

- Update to Chrome Layer v31 (Supports Node.js 16. Uses Chromium v103) ([#27](https://github.com/stefanzweifel/sidecar-browsershot/pull/27))
- Use GitHub Actions max-parallel setting to run tests ([#29](https://github.com/stefanzweifel/sidecar-browsershot/pull/29))

## [v1.2.1](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.2.0...v1.2.1) - 2022-07-15

### Fixed

- Return Etag value from saveToS3 method ([#24](https://github.com/stefanzweifel/sidecar-browsershot/pull/24))

## [v1.2.0](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.1.0...v1.2.0) - 2022-07-10

### Added

- Add ability to save files directly to S3 ([#14](https://github.com/stefanzweifel/sidecar-browsershot/pull/14), [#21](https://github.com/stefanzweifel/sidecar-browsershot/pull/21)),

## [v1.1.0](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.0.1...v1.1.0) - 2022-05-29

### Added

- Add `sidecar-browsershot` config ([#18](https://github.com/stefanzweifel/sidecar-browsershot/pull/18))
- Add support for Warming ([#18](https://github.com/stefanzweifel/sidecar-browsershot/pull/18))

## [v1.0.1](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.0.0...v1.0.1) - 2022-04-02

## Fixed

- Browsershot 3.52.4 comptability ([#12](https://github.com/stefanzweifel/sidecar-browsershot/pull/12))

## [v1.0.0](https://github.com/stefanzweifel/sidecar-browsershot/compare/v0.2.0...v1.0.0) - 2022-03-06

## Changed

- Official Support for Laravel 9 ([#7](https://github.com/stefanzweifel/sidecar-browsershot/pull/7))

## [v0.2.0](https://github.com/stefanzweifel/sidecar-browsershot/compare/v0.1.0...v0.2.0) - 2022-03-03

## Changed

- Increase maxBuffer to 100 MB ([#8](https://github.com/stefanzweifel/sidecar-browsershot/pull/8))

## Fixed

- Fix Typo in README ([#4](https://github.com/stefanzweifel/sidecar-browsershot/pull/4))

## v0.1.0 - 2022-02-13

- Initial release
