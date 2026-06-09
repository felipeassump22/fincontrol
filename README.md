# FinControl — Sistema de Gestão Financeira Empresarial

Sistema financeiro web completo desenvolvido com **Laravel 11** + **PHP 8.3** + **MySQL 8.4**, seguindo arquitetura MVC com camada de serviços.

**Inclui ambiente virtual Docker** — rode com um único comando, sem instalar nada.

---

## 📋 Índice

1. [Início rápido (Docker)](#-início-rápido-ambiente-virtual-docker)
2. [Instalação manual (sem Docker)](#-instalação-manual-sem-docker)
3. [Como ligar o sistema](#-como-ligar-o-sistema)
4. [Usuários e senhas](#-usuários-e-senhas)
5. [Funcionalidades](#-funcionalidades)
6. [Estrutura do projeto](#-estrutura-do-projeto)
7. [Automações](#-automações-scheduler)
8. [Problemas comuns](#-problemas-comuns)

---

## 🐳 Início Rápido (Ambiente Virtual Docker)

> **Pré-requisito:** Instale o [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows/Mac/Linux).

### Subir o sistema (1 comando)

```bash
docker compose up -d --build
```

Aguarde 1-2 minutos na primeira vez. O sistema faz tudo automaticamente:
- ✅ Instala PHP 8.3 + extensões
- ✅ Instala e configura MySQL 8.4
- ✅ Instala dependências (Composer)
- ✅ Cria o banco de dados `fincontrol`
- ✅ Executa as 13 migrations
- ✅ Popula com dados iniciais (seeders)
- ✅ Gera a chave da aplicação
- ✅ Configura o Apache + storage link

### Acessar

| Serviço | URL | Descrição |
|---------|-----|-----------|
| **FinControl** | http://localhost:8000 | Sistema principal |
| **phpMyAdmin** | http://localhost:8080 | Gerenciador visual do banco |

### Login rápido

| E-mail | Senha | Perfil |
|--------|-------|--------|
| `joao@empresa.com.br` | `admin123` | **Administrador** |
| `maria@empresa.com.br` | `viewer123` | Visualizador |

### Desligar

```bash
docker compose down
```

### Desligar e apagar todos os dados

```bash
docker compose down -v
```

### Ver logs em tempo real

```bash
docker compose logs -f app
```

### Acessar terminal do container

```bash
docker compose exec app bash
```

### Credenciais do banco (phpMyAdmin / MySQL)

| Campo | Valor |
|-------|-------|
| Host | `db` (dentro do Docker) ou `localhost:3307` (fora) |
| Banco | `fincontrol` |
| Usuário | `fincontrol` |
| Senha | `fincontrol123` |
| Root password | `rootpass123` |

---

## 🔧 Instalação Manual (sem Docker)

Se preferir instalar na máquina local sem Docker:

### Requisitos

| Software | Versão mínima | Verificar com |
|----------|---------------|---------------|
| PHP | 8.2+ | `php -v` |
| Composer | 2.x | `composer -V` |
| MySQL | 8.0+ | `mysql --version` |

**Extensões PHP necessárias:** `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `gd`, `zip`

### Passo a passo

```bash
# 1. Instalar dependências PHP
composer install

# 2. Copiar o arquivo de ambiente
cp .env.example .env        # Linux/Mac
Copy-Item .env.example .env # Windows PowerShell

# 3. Gerar chave da aplicação
php artisan key:generate

# 4. Configurar o banco no .env
#    Edite DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 5. Criar o banco de dados
mysql -u root -p -e "CREATE DATABASE fincontrol CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 6. Executar migrations (criar 13 tabelas)
php artisan migrate

# 7. Popular com dados iniciais
php artisan db:seed

# 8. Criar link simbólico para uploads
php artisan storage:link

# 9. Iniciar o servidor
php artisan serve
```

Acesse: http://127.0.0.1:8000

---

## ▶️ Como ligar o sistema

### Opção 1: Docker (recomendado)

```bash
# Ligar
docker compose up -d

# Desligar
docker compose down
```

### Opção 2: Manual (sem Docker)

**Passo 1 — Iniciar o MySQL:**

```powershell
# Windows (se instalado como serviço)
net start MySQL84

# Linux/Mac
sudo systemctl start mysql
```

**Passo 2 — Iniciar o Laravel:**

```bash
php artisan serve
```

**Passo 3 — Acessar:** http://127.0.0.1:8000

**Desligar:** pressione `Ctrl+C` no terminal.

---

## 👤 Usuários e senhas

O sistema vem com 3 usuários pré-cadastrados:

| # | Nome | E-mail | Senha | Perfil | Status |
|---|------|--------|-------|--------|--------|
| 1 | **João Admin** | `joao@empresa.com.br` | `admin123` | **Administrador** | ✅ Ativo |
| 2 | Maria Viewer | `maria@empresa.com.br` | `viewer123` | Visualizador | ✅ Ativo |
| 3 | Carlos Silva | `carlos@empresa.com.br` | `viewer123` | Visualizador | ❌ Inativo |

> **Dica:** Use `joao@empresa.com.br` / `admin123` para acesso completo.

### Diferenças entre perfis

| Permissão | Administrador | Visualizador |
|-----------|:---:|:---:|
| Visualizar dashboard e relatórios | ✅ | ✅ |
| Criar/editar/excluir lançamentos | ✅ | ❌ |
| Gerenciar contas bancárias | ✅ | ❌ |
| Gerenciar cartões de crédito | ✅ | ❌ |
| Gerenciar categorias | ✅ | ❌ |
| Gerenciar usuários | ✅ | ❌ |
| Fechar relatório mensal | ✅ | ❌ |
| Exportar PDF | ✅ | ✅ |

---

## ✅ Funcionalidades

### Requisitos Funcionais Implementados

| Código | Funcionalidade | Descrição |
|--------|---------------|-----------|
| RF01 | **Autenticação e autorização** | Login por e-mail/senha, 2 perfis (Admin/Viewer), middleware CheckRole |
| RF02 | **Registrar entradas e saídas** | CRUD completo de lançamentos com filtros por tipo, status, conta, categoria |
| RF03 | **Bloquear edição de pagos** | Lançamentos com status "Pago" não podem ser editados (Policy) |
| RF04 | **Alerta de saldo negativo** | Verificação AJAX antes de criar despesa, alertas visuais no dashboard |
| RF05 | **Vincular nota fiscal** | Upload de PDF/JPG/PNG (max 5MB) vinculado ao lançamento |
| RF06 | **Total da fatura do cartão** | Soma automática das transações pendentes do cartão |
| RF07 | **Compras parceladas** | Criar compra com N parcelas, gera N transações + invoice |
| RF08 | **Pagar fatura do cartão** | Marca todas as parcelas pendentes como pagas |
| RF09 | **Despesas fixas recorrentes** | Recriação automática no dia 1 de cada mês via scheduler |
| RF10 | **Receitas por cliente/categoria** | Relatórios com totais e percentuais |
| RF11 | **Projeção de fluxo de caixa** | Projeção de 6 meses usando média histórica + rendimentos CDB |
| RF12 | **Relatório mensal em PDF** | Geração via DomPDF, fechamento imutável |

### Páginas do sistema

| Página | URL | Descrição |
|--------|-----|-----------|
| Login | `/login` | Tela de autenticação |
| Dashboard | `/dashboard` | Métricas, alertas de saldo, gráficos |
| Lançamentos | `/transactions` | CRUD, filtros, marcar como pago, NF |
| Contas bancárias | `/bank-accounts` | Cards com saldos, criar/editar |
| Categorias | `/categories` | Tabela com totais mensais |
| Cartões de crédito | `/credit-cards` | Fatura aberta, pagar fatura |
| Investimentos | `/investments` | Portfolio com rendimentos |
| Clientes | `/clients` | Cadastro de clientes |
| Parcelamentos | `/invoices` | Compras parceladas |
| Despesas fixas | `/recurring-expenses` | Templates de despesas recorrentes |
| Relatório mensal | `/reports` | Métricas consolidadas, fechar mês |
| Fluxo de caixa | `/reports/cash-flow` | Projeção 6 meses |
| Auditoria | `/audit` | Log de alterações |
| Usuários | `/users` | Gerenciamento de usuários (admin) |

---

## 📁 Estrutura do projeto

```
fincontrol/
├── app/
│   ├── Console/Commands/          # Comandos artisan (RF09, RF12)
│   ├── Enums/                     # TransactionType, TransactionStatus, etc.
│   ├── Http/
│   │   ├── Controllers/           # 12 controllers
│   │   ├── Middleware/            # CheckRole
│   │   └── Requests/             # Form Requests
│   ├── Jobs/                      # PayCreditCardInvoice, GenerateMonthlyReportPdf
│   ├── Models/                    # 13 Eloquent models
│   ├── Policies/                  # Autorização por perfil
│   ├── Services/                  # 9 serviços de regra de negócio
│   └── Traits/                    # Auditable (log automático)
├── config/                        # Configurações Laravel
├── database/
│   ├── migrations/                # 13 migrations
│   └── seeders/                   # Dados iniciais
├── docker/                        # Configurações Docker
│   ├── entrypoint.sh             # Script de inicialização automática
│   └── php.ini                   # Config PHP customizada
├── public/
│   ├── css/app.css               # Design system completo
│   └── index.php                 # Entry point
├── resources/views/               # 15 Blade views + layout
├── routes/web.php                # Todas as rotas
├── storage/                       # Uploads, cache, logs
├── .dockerignore                  # Arquivos ignorados no build
├── .env.example                   # Template de configuração
├── composer.json                  # Dependências PHP
├── docker-compose.yml             # Ambiente virtual (3 serviços)
├── Dockerfile                     # Imagem PHP+Apache+Laravel
└── README.md                      # Este arquivo
```

---

## ⏰ Automações (Scheduler)

| Comando | Frequência | Descrição |
|---------|-----------|-----------|
| `php artisan expenses:recreate` | Dia 1/mês às 00:05 | Recria despesas fixas como lançamentos pendentes |
| `php artisan reports:close` | Dia 5/mês às 00:10 | Fecha relatório do mês anterior e gera PDF |

### Executar manualmente

```bash
# Docker
docker compose exec app php artisan expenses:recreate --month=6 --year=2025
docker compose exec app php artisan reports:close --month=5 --year=2025

# Sem Docker
php artisan expenses:recreate --month=6 --year=2025
php artisan reports:close --month=5 --year=2025
```

### Configurar no servidor (produção)

```bash
* * * * * cd /caminho/do/fincontrol && php artisan schedule:run >> /dev/null 2>&1
```

---

## ❓ Problemas comuns

### Docker: "port already in use"

Outra aplicação está usando a porta 8000 ou 3307. Altere no `docker-compose.yml`:
```yaml
ports:
  - "9000:80"    # Mude 8000 para outra porta
```

### Docker: container reiniciando em loop

Verifique os logs:
```bash
docker compose logs app
```

### "Connection refused" (sem Docker)

O MySQL não está rodando. Inicie-o:
```powershell
net start MySQL84     # Windows
sudo systemctl start mysql  # Linux
```

### "Access denied for user 'root'"

Verifique a senha no `.env`:
```
DB_PASSWORD=sua_senha_aqui
```

### "Class not found"

Regenere o autoload:
```bash
composer dump-autoload
```

### "The page has expired" (erro 419)

Limpe o cache:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Docker: resetar tudo do zero

```bash
docker compose down -v
docker compose up -d --build
```

---

## 📄 Licença

Projeto acadêmico — Engenharia de Software.
