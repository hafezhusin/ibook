$oldLine = " * Unauthorized copying, modification, distribution, or use of this software,"
$newBlock = " * Pembangun : Mohd Hafez bin Husin (Unit Aplikasi Gunasama)`n *`n * Unauthorized copying, modification, distribution, or use of this software,"

$files = Get-ChildItem -Path "C:\laragon\www\ibook\app" -Recurse -Filter "*.php"
$patched = 0

foreach ($file in $files) {
    $content = [System.IO.File]::ReadAllText($file.FullName, [System.Text.Encoding]::UTF8)
    if ($content -notmatch [regex]::Escape($oldLine)) { continue }
    $newContent = $content.Replace($oldLine, $newBlock)
    [System.IO.File]::WriteAllText($file.FullName, $newContent, [System.Text.UTF8Encoding]::new($false))
    $patched++
    Write-Host "OK $($file.Name)"
}

Write-Host "`nSelesai: $patched fail dikemaskini."
