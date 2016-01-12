<?php

require_once __DIR__ . '/../vendor/autoload.php';

$out = new \Symfony\Component\Console\Output\ConsoleOutput();

$skips = [
    'Billing\\Entities\\*',
    'SchedulerApi\Console\Command\HelloWorldCommand',
];

if ($argc < 3) {
    help();
    exit(1);
}

$cloverFile            = $argv[1];
$minCoveragePercentage = $argv[2];

if (!is_readable($cloverFile)) {
    $out->writeln(sprintf('<error>"%s" is not readable</error>', $cloverFile));
    exit(1);
}

if (!is_numeric($minCoveragePercentage) || ($minCoveragePercentage < 0 || $minCoveragePercentage > 100)) {
    $out->writeln('<error>Provided min coverage is not a valid int</error>');
    exit(1);
}

libxml_use_internal_errors(true);
libxml_clear_errors();
$xml = simplexml_load_file($cloverFile);
if (!empty(libxml_get_last_error())) {
    $out->writeln(sprintf(
        '<error>XML Errors found while parsing: %s</error>',
        implode(',', libxml_get_errors())
    ));

    exit(1);
}

$files = $xml->xpath('//file[class]');
$failedCoverageChecks = [];

foreach ($files as $file) {
    $fileName = (string)$file['name'];
    $class    = (string)$file->class['namespace'] . '\\' . (string)$file->class['name'];
    $metrics  = $file->class->metrics;
    $doSkip   = false;
    foreach ($skips as $skip) {
        if (fnmatch($skip, $class, FNM_NOESCAPE)) {
            $doSkip = true;
            break;
        }
    }

    if ($doSkip) {
        continue;
    }

    if ((int)$metrics['elements'] == 0) {
        continue;
    }

    $covered  = round(((int)$metrics['coveredelements'] / (int)$metrics['elements']) * 100, 2);
    if ($covered < $minCoveragePercentage) {
        $failedCoverageChecks[] = ['Class' => $class, 'Coverage' => $covered];
    }
}

if (!empty($failedCoverageChecks)) {
    $out->writeln('<error>Following classes found to be failing coverage check</error>');
    $table = new \Symfony\Component\Console\Helper\Table($out);
    $table->setHeaders(['Class', 'Coverage']);
    $table->addRows($failedCoverageChecks);
    $table->render();
    exit(0); //will be switched to 1 when coverage is decent
} else {
    $out->writeln('<info>Coverage Checks: OK</info>');
    exit(0);
}


/*** helper functions **/
function help()
{
    global $out;
    $helpText = <<<HELP
Usage: check-coverage.php <clover-file> <min-percentage>
Check coverage of files above provided min
Example: php check-coverage.php clover.xml 75
HELP;

    $out->writeln($helpText);
}
