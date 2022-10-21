# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.0] - 21-07-2022

### Added

- Simple repeatable field now supports dependsOn. (thanks to [@mlopezsti](https://github.com/mlopezsti))

**NB! This does not include the fields inside SimpleRepeatable, only the SimpleRepeatable field itself.**

## [2.0.2] - 19-06-2022

### Changed

- Removed throwing "Not found" exception when resourceId is missing from fill request
  - Temporary solution to bypass validation support for `outl1ne/nova-page-manager`

## [2.0.1] - 09-06-2022

### Changed

- Fixed case where deleting a row would always delete the first item
- Fixed locale switching not working when starting with no rows
- Updated packages

## [2.0.0] - 26-05-2022

### Added

- Nova 4.0 support (huge thanks to [@emilianotisato](https://github.com/emilianotisato))

### Changed

- Renamed namespace from OptimistDigital to Outl1ne
- Dropped Nova 3.X support
- Dropped PHP 7.X support
