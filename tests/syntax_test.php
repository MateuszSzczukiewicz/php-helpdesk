<?php

declare(strict_types=1);

function testAllPHPSyntax(): array
{
    $results = ['passed' => 0, 'failed' => 0, 'errors' => []];
    $dir = dirname(__DIR__);
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir)
    );
    
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php' && 
            strpos($file->getPathname(), '/vendor/') === false &&
            strpos($file->getPathname(), '/tests/') === false) {
            
            $output = [];
            $return = 0;
            exec("php -l " . escapeshellarg($file->getPathname()) . " 2>&1", $output, $return);
            
            if ($return === 0) {
                $results['passed']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'file' => $file->getPathname(),
                    'error' => implode("\n", $output)
                ];
            }
        }
    }
    
    return $results;
}

echo "Running PHP Syntax Tests...\n\n";
$results = testAllPHPSyntax();

echo "Results:\n";
echo "Passed: " . $results['passed'] . "\n";
echo "Failed: " . $results['failed'] . "\n";

if ($results['failed'] > 0) {
    echo "\nErrors:\n";
    foreach ($results['errors'] as $error) {
        echo "File: " . $error['file'] . "\n";
        echo "Error: " . $error['error'] . "\n\n";
    }
    exit(1);
}

echo "\nAll tests passed!\n";
exit(0);
