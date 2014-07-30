Duplicahater
==========

Removes and reassigns duplicate tags and authors.

## Install

You're going to need composer, if you don't already have it

```
$] cd duplicahater
$] curl -sS https://getcomposer.org/installer | php
$] php composer.phar install
```

## Run

Duplicahater has a number of command options:

Option | Description
------ | -----------
--host, -s | Database host
--user, -u | Database user
--password, -p | Database password
--database, -d | Database to de-dupe

**Make sure you backup the database first**

### Example

```
$] php duplicahater.php --host my.awesome.database.url.com --user username --password itsasecret --database some_database
```
