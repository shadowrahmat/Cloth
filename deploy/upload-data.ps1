<#
  Copy the shop's database and uploaded images from this PC to the VPS.
  Run from the project folder AFTER provision.sh has finished on the server:

    powershell -ExecutionPolicy Bypass -File deploy\upload-data.ps1 -Server 203.0.113.10

  It will ask for the server's SSH password (twice: once to copy, once to import)
  unless you use an SSH key.

  WARNING: this REPLACES the live database with your local one. Run it once, to go
  live. After real customers start ordering on the live site, don't run it again.
#>
param(
    [Parameter(Mandatory = $true)][string]$Server,
    [string]$User = 'root',
    [string]$LocalDb = 'cloth',
    [int]$Port = 3306,
    [string]$MysqlBin = 'D:\xampp\mysql\bin'
)

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

$mysql = Join-Path $MysqlBin 'mysql.exe'
$mysqldump = Join-Path $MysqlBin 'mysqldump.exe'
foreach ($exe in $mysql, $mysqldump) {
    if (-not (Test-Path $exe)) { throw "Not found: $exe  (pass -MysqlBin with your MySQL bin folder)" }
}

$work = Join-Path $env:TEMP "cloth-deploy-$(Get-Date -Format yyyyMMddHHmmss)"
New-Item -ItemType Directory -Path $work | Out-Null
$dump = Join-Path $work 'cloth-db.sql'
$images = Join-Path $work 'cloth-storage.tar.gz'

Write-Host '==> Listing shop tables (WordPress wp_* tables are skipped)' -ForegroundColor Green
# Connection flags are explicit: a stray my.ini on this PC points clients at port 3308.
$conn = @('--protocol=TCP', '--host=127.0.0.1', "--port=$Port", '--user=root')
$tables = & $mysql @conn --batch --skip-column-names -e "SHOW TABLES" $LocalDb |
    Where-Object { $_ -and $_ -notlike 'wp_*' }
Write-Host "    $($tables.Count) tables"

Write-Host '==> Dumping database' -ForegroundColor Green
& $mysqldump @conn --single-transaction --no-tablespaces `
    --default-character-set=utf8mb4 --skip-comments "--result-file=$dump" $LocalDb @tables
if ($LASTEXITCODE -ne 0) { throw 'mysqldump failed' }
Write-Host ("    {0:N1} MB" -f ((Get-Item $dump).Length / 1MB))

Write-Host '==> Packing uploaded images' -ForegroundColor Green
tar.exe -czf $images --exclude=.gitignore -C storage/app/public .
if ($LASTEXITCODE -ne 0) { throw 'tar failed' }
Write-Host ("    {0:N1} MB" -f ((Get-Item $images).Length / 1MB))

Write-Host "==> Uploading to $User@$Server" -ForegroundColor Green
scp $dump $images "${User}@${Server}:/tmp/"
if ($LASTEXITCODE -ne 0) { throw 'scp failed' }

Write-Host '==> Importing on the server' -ForegroundColor Green
ssh "${User}@${Server}" "bash /var/www/cloth/deploy/import-data.sh /tmp/cloth-db.sql /tmp/cloth-storage.tar.gz"
if ($LASTEXITCODE -ne 0) { throw 'import failed' }

Remove-Item -Recurse -Force $work
Write-Host "`nDone. Open http://$Server" -ForegroundColor Green
Write-Host 'Save the new admin password printed above - the old one (password) no longer works.' -ForegroundColor Yellow
