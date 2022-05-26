# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.4.2] - 26-05-2022

### Added

- Ability to customize "add row" button text (thanks to [@monaye](https://github.com/monaye))

### Changed

- Updated packages

## [1.4.1] - 2021-10-01

### Changed

- Fixed form view styles leaking to detail view
- Updated packages

## [1.4.0] - 2021-08-17

### Changed

- Improved `nova-translatable` support (changing locale won't change whole pages locale anymore)
- Updated packages

## [1.3.7] - 2021-07-15

### Changed

- Style fixes
- Updated packages

## [1.3.6] - 2021-06-22

### Changed

- Improved support for `nova-translatable` inside `nova-flexible-content`
- Updated packages

## [1.3.5] - 2021-05-26

### Changed

- Improved support for `nova-translatable`

## [1.3.4] - 2021-05-26

### Changed

- Improved support for `nova-translatable`
- Updated packages

## [1.3.3] - 2021-05-14

### Changed

- Fixed validation inside `nova-flexible-content`

## [1.3.2] - 2021-05-12

### Changed

- Fixed `nova-translatable` rules not being transformed when creating errors
- Updated packages

## [1.3.1] - 2021-05-11

### Changed

- Fixed issues with error displaying
- Fixed a rare UI issue with `nova-flexible-content`
- Updated packages

## [1.3.0] - 2021-05-07

### Changed

- Hide header row with titles when there's no rows added yet
- Fix underlying fields that are supposed to return an array or an object returning a string instead
- Fixed issue with validation crashing in some cases
- Fixed fields returning `formData[i]` arrays not being saved
- Render detail fields on detail view (let's see how well this goes, might rollback later)
- Updated packages

## [1.2.5] - 2021-03-16

### Changed

- Fixed nova-translatable support broken since [1.2.4].

## [1.2.4] - 2021-02-19

### Changed

- Published translations for faster localization (thanks to [@eimantaaas](https://github.com/eimantaaas))
- Hide table header for hidden fields (thanks to [@thefilip](https://github.com/thefilip))
- Updated packages

## [1.2.3] - 2021-02-08

### Changed

- Added `Collection` import to `SimpleRepeatable`

## [1.2.2] - 2021-02-08

### Changed

- Fixed `resolveUsing` not being used when resolving the value
- Updated packages

## [1.2.1] - 2021-01-28

### Changed

- Small styling fixes

## [1.2.0] - 2021-01-28

### Added

- Added `minRows` config option [Artexis10](https://github.com/Artexis10)
- Initializes the minimum amount of rows defined by user [Artexis10](https://github.com/Artexis10)
- Added [nova-translatable](https://github.com/optimistdigital/nova-translatable) support

### Changed

- Reworked `SimpleRepeatable` to resolve each row field's values.

## [1.1.2] - 2021-01-18

### Changed

- Fixed `maxRows` not working
- Fixed field width (again)
- Updated packages

## [1.1.1] - 2021-01-07

### Changed

- Hide from Index view by default
- Fixed detail field rendering a table even when there's no values
- Updated packages

## [1.1.0] - 2021-01-06

### Added

- Validation support
- Detail field

## [1.0.3] - 2021-01-04

### Changed

- Fixed field width (again)

## [1.0.2] - 2021-01-04

### Changed

- Fixed field width

## [1.0.1] - 2021-01-04

### Changed

- Fixed cases where the field label was not removed from some fields
- Updated packages

## [1.0.0] - 2020-12-18

Initial release.
