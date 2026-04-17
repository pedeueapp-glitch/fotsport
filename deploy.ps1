# Fazer o script mudar para a pasta onde ele esta localizado
Set-Location $PSScriptRoot

# Configuracoes do Deploy
$VpsUser = "root"
$VpsIP = "82.25.68.234"
$VpsPath = "/root/fotsport"

Write-Host "-------------------------------------------" -ForegroundColor Cyan
Write-Host "   INICIANDO DEPLOY - FOTSPORT" -ForegroundColor Cyan
Write-Host "-------------------------------------------" -ForegroundColor Cyan

# 1. Build Local das Assets
Write-Host "[1/4] Gerando build local..." -ForegroundColor Yellow
npm install
npm run build

if ($LASTEXITCODE -ne 0) {
    Write-Host "Erro no build. Abortando." -ForegroundColor Red
    exit
}

# 2. Criar pacote e enviar
Write-Host "[2/4] Enviando pacote para a VPS..." -ForegroundColor Yellow
if (Test-Path "deploy.tar.gz") { Remove-Item "deploy.tar.gz" }
# INCLUINDO A PASTA docker NO TAR
tar -czf deploy.tar.gz docker public/build Dockerfile docker-compose.yml .env app resources routes config database bootstrap
scp deploy.tar.gz "${VpsUser}@${VpsIP}:${VpsPath}/"

# 3. Extrair e Reconstruir na VPS
Write-Host "[3/4] Reconstruindo containers na VPS..." -ForegroundColor Yellow
$Cmd1 = "cd " + $VpsPath
$Cmd2 = "rm -rf app resources routes public/build"
$Cmd3 = "tar -xzf deploy.tar.gz"
$Cmd4 = "rm deploy.tar.gz"
$Cmd5 = "docker compose down"
$Cmd6 = "docker compose build --no-cache app"
$Cmd7 = "docker compose up -d"
$RemoteRebuild = $Cmd1 + "; " + $Cmd2 + "; " + $Cmd3 + "; " + $Cmd4 + "; " + $Cmd5 + "; " + $Cmd6 + "; " + $Cmd7
ssh ${VpsUser}@${VpsIP} "$RemoteRebuild"

# 4. Forcar permissoes e migracao
Write-Host "[4/4] Forcando permissoes e migracoes..." -ForegroundColor Yellow
$FixCommand = "docker exec fotsport-app sh -c 'chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache && php artisan migrate --force'"
ssh ${VpsUser}@${VpsIP} "$FixCommand"

# Limpeza local
if (Test-Path "deploy.tar.gz") { Remove-Item "deploy.tar.gz" }

Write-Host "-------------------------------------------" -ForegroundColor Green
Write-Host "   DEPLOY CONCLUIDO COM SUCESSO" -ForegroundColor Green
Write-Host "-------------------------------------------" -ForegroundColor Green
