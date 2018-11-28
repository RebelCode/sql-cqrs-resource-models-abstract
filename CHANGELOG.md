# Change log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [[*next-version*]] - YYYY-MM-DD
### Changed
- When grouping is present in an SQL query, conditions are rendered as `HAVING` clauses instead of `WHERE` clauses.

## [0.2-alpha1] - 2018-06-14
### Added
- SQL SELECT queries can be built with a GROUP BY clause.

## [0.1-alpha4] - 2018-06-11
### Fixed
- Entity field objects were not reduced to their fields when used as column names.

## [0.1-alpha3] - 2018-06-09
### Fixed
- Entity field terms are processed as they should be, before being treated as strings during the building process.

## [0.1-alpha2] - 2018-05-25
### Fixed
- Null values now correctly handled in INSERT and UPDATE query building.

## [0.1-alpha1] - 2018-05-16
Initial version.
