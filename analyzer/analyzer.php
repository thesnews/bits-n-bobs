<?php

// find article.created|timeSince

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
    ->exclude('attic')
    ->in($path);

// $spinner = new \cli\notify\Spinner('Checking '.$path);

$is_clean = true;
foreach( $i as $file ) {
    $errors = array();

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
    $in_fetch_media = false;
    $in_fetch_post = false;

    $found_status               = false;
    $found_created              = false;
    $found_pub_after_weight     = false;

    $j = 0;
    foreach( $lines as $line ) {
        $j++;

        if( strpos($line, '.created|timeSince') !== false ) {
            $is_clean = false;
            $errors[] = array(
                'message'   => "Displaying created time, should be using published time",
                'line'      => $j
            );
        }

        if( strpos($line, '{% fetch') !== false ) {
            $in_fetch = true;
            $found_status = false;
            $in_fetch_article = false;
        }
        if( $in_fetch && strpos($line, 'from article') !== false ) {
            $in_fetch_article = true;
        }
        if( $in_fetch && strpos($line, 'from media') !== false ) {
            $in_fetch_media = true;
        }
        if( $in_fetch && strpos($line, 'from blogPost') !== false ) {
            $in_fetch_post = true;
        }

        if( $in_fetch && ($in_fetch_article || $in_fetch_media || $in_fetch_post) ) {
            // verify status
            if( strpos($line, 'status') !== false ) {
                $found_status = true;
            }

            if( strpos($line, 'created') !== false ) {
                $found_created = true;
            }

            $i1 = strpos($line, 'weight');
            $i2 = strpos($line, 'published');
            if( $i1 !== false && $i2 !== false && $i1 > $i2 ) {
                $found_pub_after_weight = true;
            }
        }

        if( $in_fetch && strpos($line, '%}') !== false ) {
            if( $in_fetch_article || $in_fetch_media || $in_fetch_post ) {

                if( !$found_status ) {
                    $is_clean = false;
                    $errors[] = array(
                        'message'   => "Missing 'status = 1' filter",
                        'line'      => $j
                    );
                }

                if( $found_created ) {
                    $is_clean = false;
                    $errors[] = array(
                        'message'   =>  "Using create time sorting, should be using published time",
                        'line'      => $j
                    );
                }

                if( $found_pub_after_weight ) {
                    $is_clean = false;
                    $errors[] = array(
                        'message'   => "Weight order sorting should be before date order sorting",
                        'line'      => $j
                    );
                }
            }

            $in_fetch = false;
            $in_fetch_article = false;
            $in_fetch_media = false;
            $in_fetch_post = false;
            $found_status = false;
            $found_created = false;
            $found_pub_after_weight = false;
        }
    }

    if( count($errors) ) {
        \cli\line("");
        \cli\line("\33[1;31m".$file->getRealPath()."%n");
        foreach( $errors as $error ) {
            \cli\line(sprintf("\33[0;33mLine %s: %s%%n", $error['line'], $error['message']));
        }
        \cli\line("");
    }
}

// $spinner->finish();

\cli\line('Scan complete');

\cli\line("");
if( !$is_clean ) {
    \cli\line("\33[1;31mDang. Errors found.%n");
} else {
    \cli\line("\33[0;32mYay, it's clean!%n");
}
\cli\line("");
// file_put_contents('./analyzer-results.txt', implode("\n", $errors));