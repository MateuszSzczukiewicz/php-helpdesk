<?php

declare(strict_types=1);

function testStrictTypes(): array
{
    $results = ['total' => 0, 'with_strict' => 0, 'without_strict' => []];
    $dir = dirname(__DIR__);
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir)
    );
    
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php' && 
            strpos($file->getPathname(), '/vendor/') === false &&
            strpos($file->getPathname(), '/tests/') === false &&
            $file->getFilename() !== '.php-cs-fixer.php') {
            
            $content = file_get_contents($file->getPathname());
            $results['total']++;
            
            if (strpos($content, 'declare(strict_types=1)') !== false) {
                $results['with_strict']++;
            } elseif (strpos($content, '<!DOCTYPE html>') === false) {
                $results['without_strict'][] = $file->getPathname();
            }
        }
    }
    
    return $results;
}

echo "Running Strict Types Coverage Test...\n\n";
$results = testStrictTypes();

echo "Results:\n";
echo "Total PHP files: " . $results['total'] . "\n";
echo "With strict_types: " . $results['with_strict'] . "\n";
$coverage = $results['total'] > 0 ? round(($results['with_strict'] / $results['total']) * 100, 2) : 0;
echo "Coverage: " . $coverage . "%\n";

if (!empty($results['without_strict'])) {
    echo "\nFiles missing strict_types:\n";
    foreach ($results['without_strict'] as $file) {
        echo "  - " . $file . "\n";
    }
    exit(1);
}

echo "\n100% strict types coverage!\n";
exit(0);
