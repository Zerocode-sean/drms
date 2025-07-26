# PowerShell script to fix logo paths
$projectDir = Get-Location
$files = Get-ChildItem -Path "$projectDir\src\frontend\assets\*.php" -File

$replacements = @{
    '../images/logo.png' = '/src/frontend/images/logo.png'
    'href="../images/logo.png"' = 'href="/src/frontend/images/logo.png"'
    'src="../images/logo.png"' = 'src="/src/frontend/images/logo.png"'
}

$fixedFiles = @()
$totalReplacements = 0

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    foreach ($search in $replacements.Keys) {
        $replace = $replacements[$search]
        $beforeCount = ($content -split [regex]::Escape($search)).Count - 1
        $content = $content.Replace($search, $replace)
        $afterCount = ($content -split [regex]::Escape($replace)).Count - 1
        $totalReplacements += $beforeCount
    }
    
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        $fixedFiles += $file.Name
    }
}

Write-Host "Logo Path Fix Results:"
Write-Host "Files processed: $($files.Count)"
Write-Host "Files fixed: $($fixedFiles.Count)"
Write-Host "Total replacements: $totalReplacements"
Write-Host "Fixed files: $($fixedFiles -join ', ')"
