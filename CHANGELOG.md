# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.0] - 19-08-2025

### Added

- Added support for Nova 5.0 (thanks to [@tmannherz](https://github.com/tmannherz))
- Automatically add min amount of rows when minRows is set (thanks to [@nurmuhammet-ali](https://github.com/nurmuhammet-ali))

### Changed

- Updated packages

## [2.2.3] - 15-02-2024

### Changed

- Allow specifying field custom width using `->withMeta(['nsrWidth' => '60px'])`
- Updated packages

## [2.2.2] - 17-10-2023

### Changed

- Currently disabled any logic regarding to FormData update. Needs more thorough testing for it to be stable.

## [2.2.1] - 20-09-2023

### Added

- `json()` method to field that allows sending data in JSON format instead FormData.

### Fixed

- Validation error display when one or more rows were deleted. [Issue description](https://github.com/outl1ne/nova-simple-repeatable/issues/52)
- Fixed dark mode recognition
- Fixed UI misalignment issues

## [2.2.0] - 03-07-2023

### Added

- Return type annotations for some methods that are now required by Nova.

### Changed

- Format that data is sent to Nova is now FormData instead of JSON.

### Fixed

- Fixed sorting of locales present for field inside repeater.
- Fixed unique attribute names to match validation errors returned by Nova.
- Fixed asterisk display for required fields.

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
