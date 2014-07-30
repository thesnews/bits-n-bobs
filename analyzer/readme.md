Analyzer
==========

The start of something useful... possibly.

Maybe.

Anyway, the idea is that it will analyze Twig templates looking for common code smells. Right now it only looks for missing status checks in FETCH tags.

## Install

You're going to need composer, if you don't already have it

```
$] cd analyzer
$] curl -sS https://getcomposer.org/installer | php
$] php composer.phar install
```

## Run

After installing the dependencies, simply run `analyzer.php` with the path to your twig files. The script will search through the path looking for `.tpl` files and write results to `analyzer-results.txt` file in your current directory

`analyzer.php` will accept both absolute and relative paths

```
$] php analyzer.php /path/to/files
$] php analyzer.php ../path/to/files
```
