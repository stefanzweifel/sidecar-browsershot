# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.11.0...HEAD)

## [v1.11.0](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.10.0...v1.11.0) - 2023-10-09

### Added

- Add `sidecar-browsershot.timeout` a config to allow the overriding of default timeout value ([#90](https://github.com/stefanzweifel/sidecar-browsershot/pull/90))

## [v1.10.0](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.9.1...v1.10.0) - 2023-09-20

### Added

- Update to Node 18 Runtime and update used Chromium Version ([#85](https://github.com/stefanzweifel/sidecar-browsershot/pull/85))

### Changed

- Bump actions/checkout from 3 to 4 ([#84](https://github.com/stefanzweifel/sidecar-browsershot/pull/84))
- Fix GitHub Actions badges in `README.md` ([#82](https://github.com/stefanzweifel/sidecar-browsershot/pull/82))

## [v1.9.1](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.9.0...v1.9.1) - 2023-07-05

### Changed

- Raise minumum required spatie/browsershot version to v3.57.8.

### Fixed

- Fix for renamed browser.js file in browsershot 3.57.8 ([#78](https://github.com/stefanzweifel/sidecar-browsershot/pull/78))

## [v1.9.0](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.8.1...v1.9.0) - 2023-05-13

### Added

- Add Support for Image Manipulations when using `saveToS3` ([#71](https://github.com/stefanzweifel/sidecar-browsershot/pull/71))

## [v1.8.1](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.8.0...v1.8.1) - 2023-05-01

### Fixed

- Only support x86_64 architecture ([#68](https://github.com/stefanzweifel/sidecar-browsershot/pull/68))

## [v1.8.0](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.7.0...v1.8.0) - 2023-04-28

### Added

- Added storage config setting ([#64](https://github.com/stefanzweifel/sidecar-browsershot/pull/64))

### Changed

- Upgrade to use Pest v2 ([#62](https://github.com/stefanzweifel/sidecar-browsershot/pull/62))

## [v1.7.0](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.6.4...v1.7.0) - 2023-03-29

### Added

- Add `readHtmlFromS3()`-method  ([#60](https://github.com/stefanzweifel/sidecar-browsershot/pull/60))

## [v1.6.4](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.6.3...v1.6.4) - 2023-03-02

### Changed

- Add Support for Sidecar v0.4.0 ([#58](https://github.com/stefanzweifel/sidecar-browsershot/pull/58))

## [v1.6.3](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.6.2...v1.6.3) - 2023-02-27

### Changed

- Cleanup puppeteer_dev_chrome_profile folders in tmp after running Puppeteer  ([#54](https://github.com/stefanzweifel/sidecar-browsershot/pull/54))

### Fixed

- Store Emoji Font in package ([#56](https://github.com/stefanzweifel/sidecar-browsershot/pull/56))

## [v1.6.2](https://github.com/stefanzweifel/sidecar-browsershot/compare/v1.6.1...v1.6.2) - 2023-02-22

### Changed

- Include emoji font in lambda bundle ([#51](https://github.com/stefanzweifel/sidecar-browsershot/pull/51))

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
