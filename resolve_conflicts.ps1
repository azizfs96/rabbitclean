# حل التعارضات بالاحتفاظ بنسختك المحلية (HEAD)
# Run: powershell -ExecutionPolicy Bypass -File resolve_conflicts.ps1

$conflictedFiles = git diff --name-only --diff-filter=U
foreach ($file in $conflictedFiles) {
    Write-Host "Resolving: $file"
    git checkout --ours $file
    git add $file
}
Write-Host "Done! Now run: git commit -m 'Merge: resolve conflicts - keep local'"
Write-Host "Then: git push"
