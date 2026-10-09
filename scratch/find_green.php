<?php
$dir = dirname(__DIR__);
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

foreach ($files as $file) {
    if ($file->isFile() && preg_match('/\.(css|php|html|js)$/', $file->getFilename())) {
        $content = file_get_contents($file->getPathname());
        if (stripos($content, '315b51') !== false || stripos($content, '284e44') !== false || stripos($content, '#2e5') !== false || stripos($content, '315B') !== false) {
            echo "Match in: " . $file->getPathname() . "\n";
        }
    }
}
