<?php

require_once __DIR__.'/vendor/autoload.php';

\cli\Colors::enable();

function print_err($msg) {
    \cli\err("%C%1\n\n ".$msg." \n\n%n\n\n");
}

$strict = in_array('--strict', $_SERVER['argv']);
$arguments = new \cli\Arguments(compact('strict'));

$arguments->addFlag(array('verbose', 'v'), 'Turn on verbose output');
$arguments->addFlag(array('quiet', 'q'), 'Disable all output');
$arguments->addFlag(array('help', 'h'), 'Show this help screen');

$arguments->addOption(array('host', 's'), array(
    'default'     => 'localhost',
    'description' => 'Database Server'
));
$arguments->addOption(array('user', 'u'), array(
    'default'     => 'root',
    'description' => 'Database User'
));
$arguments->addOption(array('password', 'p'), array(
    'default'     => 'root',
    'description' => 'Database Password'
));
$arguments->addOption(array('database', 'd'), array(
    'default'     => false,
    'description' => 'Database'
));

$arguments->parse();
if ($arguments['help']) {
    echo $arguments->getHelpScreen();
    echo "\n\n";
    exit(1);
}

if( !$arguments['database'] ) {
    print_err("No database defined");
    echo $arguments->getHelpScreen();
    echo "\n\n";
    exit(1);
}

$dbh = new PDO(
    sprintf('mysql:host=%s;dbname=%s', $arguments['host'], $arguments['database']),
    $arguments['user'],
    $arguments['password'],
    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
);

\cli\err("%C%1\n\n WARNING\n\n This is potentially distructive. \n\n%n\n\n");

if( strtolower(\cli\prompt('Did you back up the selected database', 'Yn')) !== 'y' ) {
    \cli\line('May want to do that first');
    exit(1);
}

$author_map = array();

$q = 'select * from gryphon_authors';
$s = $dbh->prepare($q);
$s->execute();

$spinner = new \cli\notify\Spinner('Processing authors');

foreach( $s->fetchAll() as $row ) {
    $spinner->tick();
    if( !array_key_exists($row['name_normalized'], $author_map) ) {
        $author_map[$row['name_normalized']] = $row['uid'];
        continue;
    }

    $orig_id = $author_map[$row['name_normalized']];
    $dupe_id = $row['uid'];

    $q = 'update gryphon_articlesAuthors set author_id = ? where author_id = ?';
    $s = $dbh->prepare($q);
    $s->execute(array($orig_id, $dupe_id));

    $q = 'update gryphon_authorsBlogPosts set author_id = ? where author_id = ?';
    $s = $dbh->prepare($q);
    $s->execute(array($orig_id, $dupe_id));

    $q = 'update gryphon_authorsMedia set author_id = ? where author_id = ?';
    $s = $dbh->prepare($q);
    $s->execute(array($orig_id, $dupe_id));

    $q = 'delete from gryphon_authors where uid = ? limit 1';
    $s = $dbh->prepare($q);
    $s->execute(array($dupe_id));
}

$q = 'select * from gryphon_articlesAuthors';
$s = $dbh->prepare($q);
$s->execute();

$all = array();

foreach ($s->fetchAll() as $r) {
    $spinner->tick();

    $key = $r['article_id'].':'.$r['author_id'];
    if (in_array($key, $all)) {
        $q = 'delete from gryphon_articlesAuthors where article_id = ? and author_id = ? limit 1';
        $s = $dbh->prepare($q);
        $s->execute(array($r['article_id'], $r['author_id']));
    }

    $all[] = $key;

}

$q = 'select * from gryphon_authorsMedia';
$s = $dbh->prepare($q);
$s->execute();

$all = array();

foreach ($s->fetchAll() as $r) {
    $spinner->tick();

    $key = $r['media_id'].':'.$r['author_id'];
    if (in_array($key, $all)) {
        $q = 'delete from gryphon_authorsMedia where media_id = ? and author_id = ? limit 1';
        $s = $dbh->prepare($q);
        $s->execute(array($r['media_id'], $r['author_id']));
    }
    $all[] = $key;

}

$q = 'select * from gryphon_authorsBlogPosts';
$s = $dbh->prepare($q);
$s->execute();

$all = array();

foreach ($s->fetchAll() as $r) {
    $spinner->tick();

    $key = $r['blogPost_id'].':'.$r['author_id'];
    if (in_array($key, $all)) {
        $q = 'delete from gryphon_authorsBlogPosts where blogPost_id = ? and author_id = ? limit 1';
        $s = $dbh->prepare($q);
        $s->execute(array($r['blogPost_id'], $r['author_id']));
    }
    $all[] = $key;

}

$spinner->finish();

$tag_map = array();

$q = 'select * from gryphon_tags';
$s = $dbh->prepare($q);
$s->execute();

$spinner = new \cli\notify\Spinner('Processing tags');

foreach( $s->fetchAll() as $row ) {
    $spinner->tick();
    if( !array_key_exists($row['name_normalized'], $tag_map) ) {
        $tag_map[$row['name_normalized']] = $row['uid'];
        continue;
    }

    $orig_id = $tag_map[$row['name_normalized']];
    $dupe_id = $row['uid'];

    $q = 'update gryphon_articlesTags set tag_id = ? where tag_id = ?';
    $s = $dbh->prepare($q);
    $s->execute(array($orig_id, $dupe_id));

    $q = 'update gryphon_blogPostsTags set tag_id = ? where tag_id = ?';
    $s = $dbh->prepare($q);
    $s->execute(array($orig_id, $dupe_id));

    $q = 'update gryphon_mediaTags set tag_id = ? where tag_id = ?';
    $s = $dbh->prepare($q);
    $s->execute(array($orig_id, $dupe_id));

    $q = 'delete from gryphon_tags where uid = ? limit 1';
    $s = $dbh->prepare($q);
    $s->execute(array($dupe_id));
}

$spinner->finish();

$q = 'select * from gryphon_articlesTags';
$s = $dbh->prepare($q);
$s->execute();

$all = array();

foreach ($s->fetchAll() as $r) {
    $spinner->tick();

    $key = $r['article_id'].':'.$r['tag_id'];
    if (in_array($key, $all)) {
        $q = 'delete from gryphon_articlesTags where article_id = ? and tag_id = ? limit 1';
        $s = $dbh->prepare($q);
        $s->execute(array($r['article_id'], $r['tag_id']));
    }
    $all[] = $key;

}

$q = 'select * from gryphon_mediaTags';
$s = $dbh->prepare($q);
$s->execute();

$all = array();

foreach ($s->fetchAll() as $r) {
    $spinner->tick();

    $key = $r['media_id'].':'.$r['tag_id'];
    if (in_array($key, $all)) {
        $q = 'delete from gryphon_mediaTags where media_id = ? and tag_id = ? limit 1';
        $s = $dbh->prepare($q);
        $s->execute(array($r['media_id'], $r['tag_id']));
    }
    $all[] = $key;

}

$q = 'select * from gryphon_blogPostsTags';
$s = $dbh->prepare($q);
$s->execute();

$all = array();

foreach ($s->fetchAll() as $r) {
    $spinner->tick();

    $key = $r['blogPost_id'].':'.$r['tag_id'];
    if (in_array($key, $all)) {
        $q = 'delete from gryphon_blogPostsTags where blogPost_id = ? and tag_id = ? limit 1';
        $s = $dbh->prepare($q);
        $s->execute(array($r['blogPost_id'], $r['tag_id']));
    }
    $all[] = $key;

}

\cli\line('Done!');