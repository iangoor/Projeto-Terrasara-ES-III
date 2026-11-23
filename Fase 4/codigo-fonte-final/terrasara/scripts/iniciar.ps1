# Inicia o servidor embutido do PHP. Uso (na pasta do projeto):
#   powershell -ExecutionPolicy Bypass -File .\scripts\iniciar.ps1
param([int]$Porta = 8000)

$raiz = Split-Path -Parent $PSScriptRoot
Set-Location $raiz

if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Host "PHP nao encontrado no PATH." -ForegroundColor Red
    exit 1
}
if (-not (Test-Path "config\database.php")) {
    Copy-Item "config\database.example.php" "config\database.php"
    Write-Host "Criado config\database.php. Ajuste os dados do MySQL." -ForegroundColor Yellow
}

$modulos = (& php -m) -join " "
foreach ($m in @("pdo_mysql", "mbstring")) {
    if ($modulos -notmatch $m) {
        Write-Host "AVISO: extensao '$m' nao habilitada no php.ini (extension=$m)." -ForegroundColor Yellow
    }
}

Write-Host "Terrasara em http://localhost:$Porta  (Ctrl+C para parar)" -ForegroundColor Green
& php -S "localhost:$Porta" -t public
