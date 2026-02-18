<?php

declare(strict_types=1);

function testSecurityFeatures(): array
{
    $results = [
        'csrf_protection' => 0,
        'prepared_statements' => 0,
        'htmlspecialchars' => 0,
        'password_hash' => 0,
        'require_auth' => 0,
        'issues' => []
    ];
    
    $dir = dirname(__DIR__);
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir)
    );
    
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php' && 
            strpos($file->getPathname(), '/vendor/') === false &&
            strpos($file->getPathname(), '/tests/') === false) {
            
            $content = file_get_contents($file->getPathname());
            
            if (strpos($content, 'requireCSRFToken') !== false) {
                $results['csrf_protection']++;
            }
            
            if (strpos($content, '->prepare(') !== false) {
                $results['prepared_statements']++;
            }
            
            if (strpos($content, 'htmlspecialchars') !== false) {
                $results['htmlspecialchars']++;
            }
            
            if (strpos($content, 'password_hash') !== false) {
                $results['password_hash']++;
            }
            
            if (strpos($content, 'requireAuth') !== false || strpos($content, 'requireAdmin') !== false) {
                $results['require_auth']++;
            }
            
            if (preg_match('/\$_(GET|POST|REQUEST)\[.*\].*echo/i', $content)) {
                $results['issues'][] = $file->getPathname() . ': Potential XSS - echoing user input';
            }
            
            if (preg_match('/mysql_query|mysqli_query.*\$_/i', $content)) {
                $results['issues'][] = $file->getPathname() . ': Potential SQL Injection - unsafe query';
            }
        }
    }
    
    return $results;
}

echo "Running Security Features Test...\n\n";
$results = testSecurityFeatures();

echo "Security Features Found:\n";
echo "CSRF Protection: " . $results['csrf_protection'] . " files\n";
echo "Prepared Statements: " . $results['prepared_statements'] . " files\n";
echo "XSS Protection: " . $results['htmlspecialchars'] . " files\n";
echo "Password Hashing: " . $results['password_hash'] . " files\n";
echo "Authentication: " . $results['require_auth'] . " files\n";

if (!empty($results['issues'])) {
    echo "\nSecurity Issues Found:\n";
    foreach ($results['issues'] as $issue) {
        echo "  - " . $issue . "\n";
    }
    exit(1);
}

echo "\nNo security issues detected!\n";
exit(0);
