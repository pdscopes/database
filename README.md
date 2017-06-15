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
> vendor/bin/database migrate:install DB_DSN DB_USER DB_PASS -e
Created migrations table
> vendor/bin/database migrate:upgrade DB_DSN DB_USER DB_PASS -p examples/migrations -e
Migrated UP: v1.0.0-Initial.php
```
or use can use the shortcut:
```bash
> composer install
...
> vendor/bin/database migrate DB_DSN DB_USER DB_PASS -p examples/migrations -e
Created migrations table
Migrated UP: v1.0.0-Initial.php
```


For example, on rollback a migration:
```bash
> vendor/bin/database migrate:rollback DB_DSN DB_USER DB_PASS -p examples/migrations -e
Rolling back batch: 1
Migrated DN: v1.0.0-Initial.php
```

For example, on uninstalling:
```bash
> vendor/bin/database migrate:uninstall DB_DSN DB_USER DB_PASS -p examples/migrations -e
Removed migrations table
```


### Supported Databases
SQL databases currently supported are:

* MySQL
* SQLite

