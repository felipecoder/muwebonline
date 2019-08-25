## MuWebOnline

MuWeb Online is a CMS for MuOnline, created using [Slim Framework 3](http://www.slimframework.com/).

## Installation

Using auto installer

```
Download the repository and access the /install/index.php folder.
```

Using Git

git clone https://github.com/felipecoder/muwebonline.git

Go to the /install/sql folder and restore the mwoinstall.sql database
create the database.php file in /src

```
<?php
putenv('MSSQL_DRIVER=driver');
putenv('MSSQL_HOST=host');
putenv('MSSQL_PORT=port');
putenv('MSSQL_USER=user');
putenv('MSSQL_PASS=pass');
putenv('MSSQL_DBNAME=database');
```
create the app.php file in /src

```
<?php
putenv('DISPLAY_ERRORS=false');
putenv('DEBUG_BAR=false');
putenv('DOMAIN=example.com');
putenv('SITE_LINK=http://example.com/');
putenv('DIR=/');
putenv('DIRADMIN=admin');
putenv('DIRIMG=/uploads/');
putenv('DIRLOGS=logs/');
```
Read [the documentation](https://muwebonline.com/docs/home) for more information.
