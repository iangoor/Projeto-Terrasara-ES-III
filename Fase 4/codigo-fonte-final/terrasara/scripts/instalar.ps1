# Cria o banco, as tabelas e o administrador. Uso (na pasta do projeto):
#   powershell -ExecutionPolicy Bypass -File .\scripts\instalar.ps1            (sem dados de exemplo)
#   powershell -ExecutionPolicy Bypass -File .\scripts\instalar.ps1 -Demo      (com dados de exemplo)
#   powershell -ExecutionPolicy Bypass -File .\scripts\instalar.ps1 -Migrar    (banco da versao anterior)
param([switch]$Demo, [switch]$Migrar, [switch]$Recriar)

$raiz = Split-Path -Parent $PSScriptRoot
Set-Location $raiz

if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Host "PHP nao encontrado no PATH. Adicione a pasta do php.exe ao PATH ou rode com o caminho completo." -ForegroundColor Red
    exit 1
}
if (-not (Test-Path "config\database.php")) {
    Copy-Item "config\database.example.php" "config\database.php"
    Write-Host "Criado config\database.php. Ajuste usuario/senha/porta do MySQL e rode novamente." -ForegroundColor Yellow
    exit 1
}

$args_php = @("scripts/instalar_banco.php")
if ($Demo)    { $args_php += "--demo" }
if ($Migrar)  { $args_php += "--migrar" }
if ($Recriar) { $args_php += "--recriar" }
& php @args_php
exit $LASTEXITCODE
