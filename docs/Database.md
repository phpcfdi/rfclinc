# About the database

This library works with a database, you will need to create the database and setup the configuration file.

### Setup sqlite

```
# command
sqlite3 /home/user/phplinc.db < sql/sqlite/initial.sql
# dsn see http://php.net/manual/es/ref.pdo-sqlite.connection.php
sqlite:///home/user/phplinc.db
``` 


### Setup postgresql

```
# command
psql -d phplinc < sql/postgres/initial.sql
# dsn see http://php.net/manual/es/ref.pdo-pgsql.connection.php
pgsql:host=localhost;dbname=phplinc
``` 


### Setup mysql

```
# command
mysql phplinc < sql/mysql/initial.sql
# dsn see http://php.net/manual/es/ref.pdo-mysql.connection.php
mysql:host=localhost;dbname=phplinc
``` 
