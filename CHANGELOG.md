# Migration Manager Changelog

## 3.0.18 - 2019-02-27

### Fixed

- fixed JSON encoding error with environment variables, switched to NOWDOC vs HEREDOC
- fixed migration file name bug when name contained accented characters
- fixed color field content migration bug that resulted in empty field values
-

## 3.0.17 - 2019-01-30

### Fixed

- fixed issue creating migrations for Matrix fields
- updated support for SuperTable and Neo 3.1 releases
- removed Routes from migration options for Craft 3.1 + (database routes not supported in Craft 3.1, moved to project config)

## 3.0.16 - 2019-01-28 [CRITICAL]

### Fixed

- fixed errors caused by Uids introduced in Craft 3.1 which caused Migration Manager to fail. This updated is needed for Migration Manager to run in 3.1 installations
- fixed deprecation error on js include
- fixed user permissions being applied after migration
- fixed global set migrations being run consecutively

## 3.0.15 - 2018-11-27

### Added

- support for Neo fields (structure and content migrations)

### Fixed

- fixed error logging issue
- fixed source error for Entry and Asset fields
- updates for Supertable content migrations

## 3.0.14 - 2018-10-26

### Fixed

- fixed index conflict when importing global sets
- fixed missing site group id for newly created site groups

## 3.0.13 - 2018-10-10

### Fixed

- fixed asset transforms failing validation error
- fixed deprecation errors for content migrations

### Added

- user permission to allow content migrations for non admin users

## 3.0.12 - 2018-9-28

### Fixed

- corrected UTF 8 encoding for content migrations
- fix Matrix block issue

## 3.0.11 - 2018-07-06

### Fixed

- Fixed entry dates for content migrations
- Fixed invalid volume error when exporting asset fields
- Fixed null item error for custom field types

## 3.0.10 - 2018-05-31

### Fixed

- Fixed null field error for empty content migrations

## 3.0.9 - 2018-05-25

### Fixed

- Fixed json decoding that resulted in null migration error

## 3.0.8 - 2018-05-10

### Fixed

- Fixed a template issue when migrations run with 'backupOnUpdate' set to 'false'

## 3.0.7 - 2018-05-03

### Fixed

- Retrieve default site handle instead of using 'default'
- Better error reporting for Entry errors

## 3.0.6 - 2018-05-02

### Fixed

- Fixed query table prefix error when retrieving field groups

## 3.0.5 - 2018-04-26

### Fixed

- Fixed volume folder references in Asset and Redactor field settings

## 3.0.4 - 2018-04-26

### Fixed

- Fixed escaping for backslashes in settings

## 3.0.3 - 2018-04-25

### Fixed

- Deprecation errors in templates
- Null value when creating Asset Volume migration

## 3.0.2 - 2018-04-23

### Fixed

- Exporting of Redactor field
- SuperTable field export no longer throws errors
- Removed unnecessary asset bundle for sidebar
- Field migrations for Matrix and SuperTable fixed to prevent orphaned data

## 3.0.1 - 2018-04-20

### Fixed

- Edition check for user group permissions

## 3.0.0 - 2018-04-19

### Added

- Initial release for Craft 3
