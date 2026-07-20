$ErrorActionPreference = 'Stop'

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$dbName = if ($args.Count -gt 0) { $args[0] } else { 'test.db' }

switch -Regex ($dbName) {
    '\.db$|\.sqlite3$' {
        break
    }
    '\.' {
        Write-Error "Extension non valide pour $dbName"
    }
    default {
        $dbName = "$dbName.db"
    }
}

$dbPath = Join-Path $scriptDir $dbName

if (-not (Test-Path -LiteralPath $scriptDir)) {
    New-Item -ItemType Directory -Path $scriptDir -Force | Out-Null
}

if (Get-Command php -ErrorAction SilentlyContinue) {
    if (-not (Test-Path -LiteralPath $dbPath)) {
        # Use PHP to create an empty SQLite file
        $phpPath = $dbPath -replace '\\', '/'
        & php -r "file_put_contents('$phpPath', '');" | Out-Null
    }
} elseif (-not (Test-Path -LiteralPath $dbPath)) {
    New-Item -ItemType File -Path $dbPath -Force | Out-Null
}

Write-Host "SQLite file ready: $dbPath"