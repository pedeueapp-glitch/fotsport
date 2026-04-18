# Fotsport - Plataforma de Fotos Esportivas

Este projeto utiliza Laravel 10, Docker e Inteligência Artificial para reconhecimento facial.

## 🚀 Guia de Instalação Manual (VPS Limpa - Ubuntu 22.04+)

Siga estes passos para configurar sua VPS (IP: 82.25.68.234) do zero, sem Easypanel.

### 1. Preparação e Instalação do Docker
Atualize o sistema e instale o Docker:
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install git curl unzip tar nginx -y

# Instalar Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
```

### 2. Configuração do Domínio (DNS)
No painel da Hostinger, aponte seu domínio para o IP da VPS:
- **Registro A**: fotsport.com -> 82.25.68.234
- **Registro A**: www -> 82.25.68.234

### 3. Configuração do Nginx (Proxy Reverso)
O Nginx vai receber o acesso na porta 80/443 e mandar para o Docker na porta 8000.

Crie o arquivo de configuração:
```bash
sudo nano /etc/nginx/sites-available/fotsport
```

Cole o conteúdo abaixo:
```nginx
server {
    listen 80;
    server_name fotsport.com www.fotsport.com;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Ative o site e reinicie o Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/fotsport /etc/nginx/sites-enabled/
sudo systemctl restart nginx
```

### 4. Instalação do Certificado SSL (HTTPS)
Use o Certbot para gerar o certificado automático:
```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d fotsport.com -d www.fotsport.com
```
*Escolha a opção de redirecionar (Opção 2).*

---

## 🛠️ Como realizar o Deploy (Local -> VPS)

1. **Configure seu .env local**:
   - `APP_URL=https://fotsport.com`
   - `DB_PASSWORD=secret`

2. **Crie a pasta na VPS**:
   ```bash
   mkdir -p /root/fotsport
   ```

3. **Execute o Deploy no Windows**:
   ```powershell
   .\deploy.ps1
   ```

---

## 🔑 Acesso sem Senha (SSH)
Para evitar digitar senha no deploy:
1. `ssh-keygen` no PowerShell do Windows (Enter em tudo).
2. `cat ~/.ssh/id_rsa.pub | ssh root@82.25.68.234 "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys"`
