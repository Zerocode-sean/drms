<?php
// Script to fix logo paths in all PHP files
// This will replace relative logo paths with absolute paths

$projectDir = __DIR__;
$files = glob($projectDir . '/src/frontend/assets/*.php');

$replacements = [
    '../images/logo.png' => '/src/frontend/images/logo.png',
    'href="../images/logo.png"' => 'href="/src/frontend/images/logo.png"',
    'src="../images/logo.png"' => 'src="/src/frontend/images/logo.png"'
];

$fixedFiles = [];
$totalReplacements = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    foreach ($replacements as $search => $replace) {
        $count = 0;
        $content = str_replace($search, $replace, $content, $count);
        $totalReplacements += $count;
    }
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $fixedFiles[] = basename($file);
    }
}

echo "Logo Path Fix Results:\n";
echo "Files processed: " . count($files) . "\n";
echo "Files fixed: " . count($fixedFiles) . "\n";
echo "Total replacements: " . $totalReplacements . "\n";
echo "Fixed files: " . implode(', ', $fixedFiles) . "\n";
?>
