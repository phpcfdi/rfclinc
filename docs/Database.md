# About the database

This library works with a database, you will need to create the database and setup the configuration file.

### Setup sqlite

```
# command
sqlite3 /home/user/phplinc.db < sql/sqlite/initial.sql
# dns
sqlite:///home/user/phplinc.db
``` 


### Setup postgresql

```
# command
psql -d phplinc < sql/postgres/initial.sql
# dns
pgsql:dbname=phplinc
``` 


### Setup mysql

```
# command
mysql phplinc < sql/postgres/initial.sql
# dns
mysql:dbname=phplinc
``` 
