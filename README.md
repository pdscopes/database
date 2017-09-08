# MadeSimple - Database
The database package is a abstraction layer between PHP and an SQL database.
The main features of the package are:

1. Migration control
2. Query building
3. Entity management
4. Entity relationships

### Migration Control
You can control migrations of your database through an easy to use, reliable
command line. Possible actions are to install the migrations table, upgrade,
rollback, and uninstall. These are all called through `bin/database`.

For example, on first clone of a package using database:
```bash
> composer install
...
> vendor/bin/database migrate:install -e
Created migrations table
> vendor/bin/database migrate:upgrade -p examples/migrations -e
Migrated UP: v1.0.0-Initial.php
```


For example, on rollback a migration:
```bash
> vendor/bin/database migrate:rollback -p examples/migrations -e
Rolling back batch: 1
Migrated DN: v1.0.0-Initial.php
```

For example, on uninstalling:
```bash
> vendor/bin/database migrate:uninstall -p examples/migrations -e
Removed migrations table
```


### Supported Databases
SQL databases currently supported are:

* MySQL
* SQLite


# External Documentation
Links to documentation for external dependencies:
* [PHP Docs](http://php.net/)
* [Logging PSR-3](http://www.php-fig.org/psr/psr-3/)

Links to documentation for development only external dependencies:
* [monolog/monolog](https://github.com/Seldaek/monolog)
* [symfony/console](http://symfony.com/doc/current/components/console.html)
* [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv)
