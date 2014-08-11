<?php

require 'vendor/autoload.php';

$strict = in_array('--strict', $_SERVER['argv']);
$arguments = new \cli\Arguments(compact('strict'));

$arguments->addFlag(array('verbose', 'v'), 'Turn on verbose output');
$arguments->addFlag(array('quiet', 'q'), 'Disable all output');
$arguments->addFlag(array('help', 'h'), 'Show this help screen');

$arguments->parse();
if ($arguments['help']) {
    echo $arguments->getHelpScreen();
    echo "\n\n";
}

$path = end($_SERVER['argv']);

if( strpos($path, '.') === 0 ) {
    $path = dirname(__FILE__).'/'.$path;
}

$path = realpath($path);

// \cli\line('Checking: %s', $path);

$finder = new Symfony\Component\Finder\Finder;
$i = $finder
    ->files()
    ->name('*.tpl')
    ->in($path);

// $spinner = new \cli\notify\Spinner('Checking '.$path);

$errors = false;
foreach( $i as $file ) {
    // $spinner->tick();
    $continue = false;

    $contents = file_get_contents($file->getRealPath());
    if( strpos($contents, 'fetch') !== false ) {
        if( strpos($contents, 'from article') !== false ) {
            $continue = true;
        }
    }

    if( !$continue ) {
        continue;
    }

    // blow it apart
    $lines = explode("\n", $contents);
    $in_fetch = false;
    $in_fetch_article = false;
    $found_status = false;
    $found_created = false;

    $j = 0;
    foreach( $lines as $line ) {
        $j++;
        if( strpos($line, '{% fetch') !== false ) {
            $in_fetch = true;
            $found_status = false;
            $in_fetch_article = false;
        }
        if( $in_fetch && strpos($line, 'from article') !== false ) {
            $in_fetch_article = true;
        }

        if( $in_fetch && $in_fetch_article ) {
            // verify status
            if( strpos($line, 'status') !== false ) {
                $found_status = true;
            }

            if( strpos($line, 'created') !== false ) {
                $found_status = true;
            }
        }

        if( $in_fetch && strpos($line, '%}') !== false ) {
            if( $in_fetch_article && !$found_status ) {
                $errors = true;
                \cli\line('%C%1Missing Status%n: '.$file->getRealPath().':'.$j);
            }

            if( $in_fetch_article && !$found_created ) {
                $errors = true;
                \cli\line('%C%1Using create sorting%n: '.$file->getRealPath().':'.$j);
            }

            $in_fetch = false;
            $in_fetch_article = false;
            $found_status = false;
        }
    }
}

// $spinner->finish();

\cli\line('Scan complete');

\cli\line("");
if( $errors ) {
    \cli\line("%C%1\n\n        Dang. Errors found.\n%n");
} else {
    \cli\line("%C%2\n\n        Yay, it's clean!\n%n");
}
\cli\line("");
// file_put_contents('./analyzer-results.txt', implode("\n", $errors));