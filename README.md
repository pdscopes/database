# MadeSimple - Database
[![Build Status](https://travis-ci.org/pdscopes/database.svg?branch=master)](https://travis-ci.org/pdscopes/database)

The database package is a abstraction layer between PHP and an SQL database.
The main features of the package are:

1. [Migration control](#migration-control)
2. [Database Seeding](#database-seeding)
3. [Query building](#query-building)
4. [Entities and Relationships](#entities-and-relationships)

## Migration Control
You can control migrations of your database through an easy to use, reliable
command line. Possible actions are to install the migrations table, upgrade,
rollback, uninstall, and refresh. These are all called through `bin/database`.

For example, on first clone of a package using database:
```bash
> composer install
...
> vendor/bin/database migrate:install -e -v
[notice] Migration table created
> vendor/bin/database migrate:upgrade -ep examples/migrations -v
[notice] Migrated file: "/path/to/database/examples/migrations/v1.0.0-ExampleInitial.php"
[notice] Migrated file: "/path/to/database/examples/migrations/v1.0.1-ExampleComment.php"
```
or you can use the shortcut:
```bash
> composer install
...
> vendor/bin/database migrate -e -p examples/migrations -v
[notice] Migration table created
[notice] Migrated file: "/path/to/database/examples/migrations/v1.0.0-ExampleInitial.php"
[notice] Migrated file: "/path/to/database/examples/migrations/v1.0.1-ExampleComment.php"
```


For example, on rollback a migration:
```bash
> vendor/bin/database migrate:rollback -e -v
Rolling back batch: 1
[notice] Rolled back file: "/path/to/database/examples/migrations/v1.0.1-ExampleComment.php"
[notice] Rolled back file: "/path/to/database/examples/migrations/v1.0.0-ExampleInitial.php"
```

For example, on uninstalling:
```bash
> vendor/bin/database migrate:uninstall -e -v
[notice] Migration table removed
```

For example, on refreshing:
```bash
> vendor/bin/database migrate:refresh -e -p examples/migrations/ -s examples/seeds/ -v
[notice] Migration table already installed
[notice] Rolled back file: "/path/to/database/examples/migrations/v1.0.1-ExampleComment.php"
[notice] Rolled back file: "/path/to/database/examples/migrations/v1.0.0-ExampleInitial.php"
[notice] Migrated file: "/path/to/database/examples/migrations/v1.0.0-ExampleInitial.php"
[notice] Migrated file: "/path/to/database/examples/migrations/v1.0.1-ExampleComment.php"
[notice] Seeded file: "/path/to/database/examples/seeds/v1.0.0-ExampleTableSeeder.php"
```

## Database Seeding
You can create seeds for the database that should be used to populate the
database with dummy data. This can be called through `bin/database`.

For example, on seeding that database:
```bash
> vendor/bin/database migrate:seed -e -v -s examples/seeds
[notice] Seeded file: "/path/to/database/examples/seeds/v1.0.0-ExampleTableSeeder.php"
```

## Query Building
### Select
```php
$connection
    ->select()
    ->from('table')
    ->where('column', '=', 'value');
// SELECT * FROM `table` WHERE `columns` = ?
```

### Update
```php
$connection
    ->update()
    ->table('table')
    ->set('column', 'new-value')
    ->where('another-column', '=', 'value');
// UPDATE `table` SET `column`=? WHERE `another-column` = ?
```

### Insert
```php
$connection
    ->insert()
    ->into('table')
    ->columns('column1', 'column2')
    ->values(5, 'value');
// INSERT INTO `table` (`column1`,`column2`) VALUES (?,?)
```

### Delete
```php
$connection
    ->delete()
    ->from('table')
    ->where('column', '=', 'value');
// DELETE FROM `table` WHERE `column` = ?
```

### Statements
```php
$connection->statement(function (CreateTable $create) {
    $create->table('user')->ifNotExists();
    $create->column('id')->integer(10)->primaryKey()->autoIncrement();
    $create->column('uuid')->char(36)->unique()->notNull();
    $create->column('email')->char(255)->unique()->notNull();
    $create->column('password')->char(255)->notNull();
    $create->column('created_at')->timestamp()->notNull()->useCurrent();
    $create->column('updated_at')->timestamp()->notNull()->useCurrent();
    $create
        ->engine('InnoDB')
        ->charset('utf8mb4', 'utf8mb4_general_ci');
});
// CREATE TABLE IF NOT EXISTS `user` (
//   `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
//   `uuid` CHAR(36) NOT NULL UNIQUE,
//   `email` CHAR(255) NOT NULL UNIQUE,
//   `password` CHAR(255) NOT NULL,
//   `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
//   `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
// ) ENGINE=InnoDB,DEFAULT CHARACTER SET=utf8mb4,COLLATE=utf8mb4_general_ci
```

## Entities and Relationships
```php
class User extends \MadeSimple\Database\Entity
{
    use \MadeSimple\Database\Relationship\Relational;

    public function getMap()
    {
        return new \MadeSimple\Database\EntityMap(
            'user', // Table name
            ['id'], // Primary key(s)
            [       // Other columns: database name => property name
                'uuid',
                'email',
                'password',
                'created_at' => 'createdAt',
                'updated_at' => 'updatedAt',
            ]
        );
    }

    /**
     * @return \MadeSimple\Database\Relationship\ToMany
     */
    public function posts()
    {
        return $this->toMany()->has(Post::class, 'p', 'user_id');
    }
}
class Post extends \MadeSimple\Database\Entity
{
    use \MadeSimple\Database\Relationship\Relational;

    public function getMap()
    {
        return new \MadeSimple\Database\EntityMap(
            'post', // Table name
            ['id'], // Primary key(s)
            [       // Other columns: database name => property name
                'uuid',
                'user_id' => 'userId',
                'title',
                'content',
                'created_at' => 'createdAt',
                'updated_at' => 'updatedAt',
            ]
        );
    }

    /**
     * @return \MadeSimple\Database\Relationship\ToOne
     */
    public function user()
    {
        return $this->toOne()->belongsTo(User::class, 'u', 'user_id');
    }
}
```

## Supported Databases
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
