<div align="center">

<img src="public/fincontrol.png" alt="FinControl Logo" width="240">

# 🏛️ FinControl

**Sua vida financeira, finalmente sob controle.**

*Do fluxo de caixa à projeção do futuro — tudo em um painel elegante.*

<br>

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Redis](https://img.shields.io/badge/Redis-Cache-DC382D?style=for-the-badge&logo=redis&logoColor=white)](https://redis.io)
[![Turbo 8](https://img.shields.io/badge/Turbo-8.x-333333?style=for-the-badge&logo=hotwire&logoColor=white)](https://hotwired.dev)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)

<br>

[![GitHub Stars](https://img.shields.io/github/stars/GatoSemOrelha/fincontrol-engenharia?style=social)](https://github.com/GatoSemOrelha/fincontrol-engenharia/stargazers)
[![GitHub Forks](https://img.shields.io/github/forks/GatoSemOrelha/fincontrol-engenharia?style=social)](https://github.com/GatoSemOrelha/fincontrol-engenharia/network)
[![Status](https://img.shields.io/badge/Status-🚀_Produção-brightgreen?style=flat-square)](#)
[![Licença](https://img.shields.io/badge/Licença-MIT-blue?style=flat-square)](LICENSE)

---

**[✨ O que é?](#-o-que-é-o-fincontrol) · [🚀 Como rodar](#-como-rodar-o-projeto-passo-a-passo) · [👥 Usuários](#-usuários-perfis-e-permissões) · [⚙️ Funcionalidades](#%EF%B8%8F-tudo-que-o-fincontrol-faz) · [🏗️ Arquitetura](#%EF%B8%8F-como-o-projeto-foi-construído) · [📱 Mobile](#-acessando-pelo-celular)**

</div>

---

## ✨ O que é o FinControl?

O **FinControl** surgiu de um problema real: ferramentas financeiras são ou **simples demais** (planilhas do Excel) ou **complexas demais** (ERPs corporativos caros e feios). A proposta aqui é o meio-termo perfeito.

É uma plataforma web completa que:

- 📊 **Consolida** todas as suas contas bancárias num único painel
- 🔮 **Projeta** seu fluxo de caixa para os próximos 12 meses usando médias históricas
- 💳 **Monitora** o limite de múltiplos cartões de crédito em tempo real
- ⚙️ **Automatiza** despesas recorrentes (aluguel, assinaturas, salários) sem você precisar fazer nada
- 📈 **Acompanha** o rendimento dos seus investimentos (CDB, Tesouro Direto)
- 📄 **Exporta** relatórios gerenciais profissionais em PDF
- 🕵️ **Registra** um log completo de tudo que foi alterado no sistema



---

## 🚀 Como Rodar o Projeto (Passo a Passo)

Antes de qualquer coisa: **você não precisa instalar PHP, MySQL, Composer nem nada disso.** Toda a magia acontece dentro do **Docker** — uma ferramenta que cria "caixinhas" virtuais no seu computador onde cada servidor roda isolado. Legal, né?

### 🐳 Passo 0 — Instalar o Docker Desktop

Se você ainda não tem o Docker instalado, esse é o único pré-requisito:

1. Acesse 👉 **[https://www.docker.com/products/docker-desktop/](https://www.docker.com/products/docker-desktop/)**
2. Baixe a versão para o seu sistema operacional (Windows, Mac ou Linux)
3. Instale como qualquer outro programa
4. Abra o **Docker Desktop** e espere o ícone da baleia 🐳 aparecer na barra de tarefas/menu bar
5. Quando o Docker mostrar **"Engine running"**, está pronto!

> [!IMPORTANT]
> O Docker Desktop precisa estar **aberto e rodando** para que os próximos passos funcionem. Antes de continuar, confira se o ícone da baleia 🐳 está ativo na sua barra de tarefas.

---

### 📂 Passo 1 — Baixar o Projeto

Abra o terminal do seu sistema operacional:
- **Windows:** Pressione `Windows + R`, digite `cmd` e aperte Enter
- **Mac:** Pressione `Cmd + Espaço`, digite `Terminal` e aperte Enter
- **Linux:** Use o atalho `Ctrl + Alt + T`

Agora cole os comandos abaixo e aperte Enter:

```bash
# Baixa o código do projeto para a sua máquina
git clone https://github.com/GatoSemOrelha/fincontrol-engenharia.git

# Entra na pasta do projeto
cd fincontrol-engenharia
```

> [!TIP]
> **Não tem o Git instalado?** Sem problema! Na página do GitHub, clique no botão verde **"Code"** e depois em **"Download ZIP"**. Descompacte o arquivo e abra o terminal dentro dessa pasta.

---

### ⚡ Passo 2 — Subir os Servidores

Ainda no terminal, dentro da pasta do projeto, rode:

```bash
docker compose up -d --build
```

Esse único comando vai:
- 📦 Baixar as imagens do PHP 8.3, MySQL 8.4, Redis e phpMyAdmin
- 🔧 Instalar todas as dependências do PHP (via Composer) automaticamente
- 🚀 Iniciar 4 servidores em segundo plano (App, Banco, Cache, Admin)

> [!WARNING]
> **A primeira execução pode demorar entre 3 e 10 minutos**, pois o Docker precisa baixar as imagens oficiais dos servidores da internet (~500MB). Nas próximas vezes que você rodar, será instantâneo porque as imagens ficam salvas no seu computador. ☕ Vai lá pegar um café!

Quando terminar, você vai ver algo parecido com isso no terminal:
```
✔ Container fincontrol-db      Started
✔ Container fincontrol-redis   Started  
✔ Container fincontrol-app     Started
✔ Container fincontrol-admin   Started
```

---

### 🗄️ Passo 3 — Criar e Popular o Banco de Dados

Agora precisamos criar as tabelas no banco e colocar alguns dados de exemplo pra você testar. Rode:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

Esse comando cria **14 tabelas** e insere automaticamente:
- 👤 3 usuários de teste (admin e usuários comuns)
- 🏦 Contas bancárias com saldos de demonstração
- 💳 Cartões de crédito com faturas e parcelas
- 📊 Histórico de transações do mês atual
- 💰 Investimentos e despesas recorrentes

---

### 🎉 Passo 4 — Acesse e Explore!

| O que acessar | Endereço | Login |
|:---|:---|:---|
| 🏛️ **FinControl App** | [http://localhost:8000](http://localhost:8000) | `joao@empresa.com.br` / `admin123` |
| 🛠️ **Gerenciador do Banco** | [http://localhost:8080](http://localhost:8080) | Usuário: `root` — Senha: `rootpass123` |

> [!TIP]
> **3 usuários disponíveis para teste após o seed:**
> | 👤 Usuário | 📧 E-mail | 🔑 Senha | 🎭 Perfil | ✅ Status |
> |:---|:---|:---|:---|:---:|
> | João Admin | `joao@empresa.com.br` | `admin123` | Administrador | Ativo |
> | Maria Viewer | `maria@empresa.com.br` | `viewer123` | Visualizador | Ativo |
> | Carlos Silva | `carlos@empresa.com.br` | `viewer123` | Visualizador | **Inativo** 🔒 |

---

### 🛑 Como Parar os Servidores

Quando terminar de usar, basta rodar:

```bash
docker compose down
```

Isso desliga todos os 4 servidores. Seus dados ficam salvos no banco. Da próxima vez que quiser usar, rode apenas:

```bash
docker compose up -d
```
*(Sem o `--build`, pois já foi construído antes — inicia em segundos!)*

---

## 👥 Usuários, Perfis e Permissões

O sistema possui um controle de acesso por **papéis (Roles)**, onde cada usuário tem um perfil que define o que ele pode ou não fazer.

### 🎭 Perfis Disponíveis

| Perfil | Descrição |
|:---|:---|
| 👑 **Administrador** | Acesso total ao sistema. Pode criar, editar, excluir e marcar lançamentos como pagos. |
| 👁️ **Visualizador** | Pode visualizar dados, relatórios e projeções, mas **não pode criar, editar nem excluir** lançamentos. |

---

### 🔐 Mapa de Permissões por Ação

| Ação | 👑 Administrador | 👁️ Visualizador |
|:---|:---:|:---:|
| Visualizar lançamentos | ✅ | ✅ |
| Visualizar relatórios e projeções | ✅ | ✅ |
| Criar novo lançamento | ✅ | ❌ |
| Editar lançamento pendente | ✅ | ❌ |
| Editar lançamento **pago** | ❌ | ❌ |
| Excluir lançamento | ✅ | ❌ |
| Marcar lançamento como pago | ✅ | ❌ |
| Gerenciar contas bancárias | ✅ | ❌ |
| Gerenciar cartões de crédito | ✅ | ❌ |
| Cadastrar categorias e clientes | ✅ | ❌ |
| Exportar PDF | ✅ | ✅ |

> [!CAUTION]
> **Regra de ouro do sistema:** mesmo o Administrador **não consegue editar um lançamento marcado como PAGO**. Essa trava é implementada diretamente no `TransactionService` no servidor — não é apenas um botão desativado na tela. Mesmo que alguém tente via Postman ou manipulação de URL, a requisição é rejeitada com `403 Forbidden`.

---

### 👤 Usuários de Demonstração (Gerados pelo Seed)

| # | 👤 Nome | 📧 E-mail | 🔑 Senha | 🎭 Perfil | Status |
|:---:|:---|:---|:---|:---|:---:|
| 1 | João Admin | `joao@empresa.com.br` | `admin123` | 👑 Administrador | 🟢 Ativo |
| 2 | Maria Viewer | `maria@empresa.com.br` | `viewer123` | 👁️ Visualizador | 🟢 Ativo |
| 3 | Carlos Silva | `carlos@empresa.com.br` | `viewer123` | 👁️ Visualizador | 🔴 Inativo |

> [!NOTE]
> O usuário **Carlos Silva** está marcado como `is_active = false` no banco. Isso significa que ele existe mas **não consegue fazer login** — serve para demonstrar a desativação de usuários sem precisar excluí-los do sistema.

---

## ⚙️ Tudo que o FinControl Faz

<details>
<summary><b>🔐 Autenticação e Segurança</b> — clique para expandir</summary>
<br>

| Funcionalidade | Como foi feito |
|:---|:---|
| Login por e-mail e senha | Senhas protegidas com `Bcrypt` — o mesmo padrão do GitHub e do Nubank |
| Proteção de rotas | Middleware `auth` bloqueia qualquer tentativa de acesso sem login, redirecionando para a tela de login |
| Anti-fraude em lançamentos | Lançamentos marcados como **"Pagos"** ficam congelados. Nem via URL manipulada ou Postman é possível alterá-los |
| Proteção CSRF | Tokens anti-falsificação em todos os formulários (padrão Laravel) |

</details>

<details>
<summary><b>💰 Contas Bancárias e Lançamentos</b> — clique para expandir</summary>
<br>

| Funcionalidade | Como foi feito |
|:---|:---|
| Múltiplas contas bancárias | Cada conta tem seu saldo individual, atualizado atomicamente a cada lançamento |
| Lançamentos (receitas/despesas) | CRUD completo com categoria, cliente, conta, status e anexo de nota fiscal |
| Alerta de saldo negativo | A interface inteira muda de cor quando uma conta fica abaixo de R$ 0,00 |
| Anexo de comprovantes | Upload de NFs e comprovantes por lançamento, com download avulso |
| Atualização atômica de saldo | O `BankAccountService` usa operações atômicas (`increment/decrement`) para evitar erros em múltiplos acessos simultâneos |

</details>

<details>
<summary><b>💳 Cartões de Crédito e Faturas</b> — clique para expandir</summary>
<br>

| Funcionalidade | Como foi feito |
|:---|:---|
| Múltiplos cartões | Cada cartão tem seu limite, data de fechamento e fatura mensal independente |
| Parcelamento inteligente | O `InstallmentService` divide o valor em parcelas e usa um algoritmo de absorção de centavos para garantir que a soma seja sempre exata (sem R$ 0,01 de diferença por arredondamento) |
| Limite disponível em tempo real | Calcula o uso atual somando todas as faturas abertas e parcelas pendentes |

</details>

<details>
<summary><b>🔮 Projeção de Fluxo de Caixa</b> — clique para expandir</summary>
<br>

| Funcionalidade | Como foi feito |
|:---|:---|
| Projeção algorítmica | O `CashFlowProjectionService` calcula a **média histórica** dos últimos 3 meses e projeta o saldo estimado para 1, 3, 6 ou 12 meses à frente |
| Filtro por conta e período | Permite analisar a projeção de uma conta específica num intervalo personalizado |
| Rentabilidade de investimentos | Calcula o rendimento de CDBs e Tesouro Direto com base na taxa e data de início |

</details>

<details>
<summary><b>⚙️ Automação de Despesas Recorrentes</b> — clique para expandir</summary>
<br>

Você configura uma vez: "todo dia 1° debitar R$ 2.500,00 de aluguel da conta Bradesco". O sistema faz o resto sozinho, todo mês, sem você precisar fazer nada.

Tecnicamente, o `RecurringExpenseService` é chamado por um **CRON Job** agendado pelo Laravel Scheduler todo primeiro dia do mês.

</details>

<details>
<summary><b>📊 Relatórios e Analytics</b> — clique para expandir</summary>
<br>

| Funcionalidade | Como foi feito |
|:---|:---|
| Receitas por categoria | Mostra percentualmente de qual categoria vem o maior faturamento |
| Receitas por cliente | Identifica seus clientes mais rentáveis em gráfico de barras |
| Relatório mensal em PDF | Exporta um relatório gerencial profissional com resumo financeiro, totalizadores e tabela de lançamentos |

</details>

<details>
<summary><b>🕵️ Auditoria Total do Sistema</b> — clique para expandir</summary>
<br>

Cada mutação de dados no sistema gera automaticamente um registro em `audit_logs` com:
- 👤 **Quem** fez a alteração
- 📋 **O que** foi alterado (qual campo)
- 🕐 **Quando** (timestamp preciso)
- 🔴 **Valor anterior** (em JSON)
- 🟢 **Novo valor** (em JSON)

</details>

---

## 🏗️ Como o Projeto Foi Construído

### O Problema que o Service Pattern resolve

Imagine que você faz um lançamento de R$ 500,00. O que acontece nos bastidores?

1. Valida se os dados estão corretos
2. Cria o registro do lançamento no banco
3. Debita R$ 500,00 da conta bancária
4. Registra no log de auditoria
5. Se for de cartão, atualiza o limite disponível

Colocar tudo isso dentro de um `Controller` seria uma bagunça impossível de testar e manter. O **Service Pattern** isola cada responsabilidade:

```mermaid
flowchart LR
    A["📥 HTTP Request"] --> B["🎮 Controller\n(só valida e direciona)"]
    B --> C["🧠 TransactionService\n(orquestra a operação)"]
    C --> D["🏦 BankAccountService\n(ajusta o saldo)"]
    C --> E["🕵️ AuditService\n(grava o log)"]
    C --> F["💳 CreditCardService\n(atualiza limite)"]
    D & E & F --> G[("🗄️ MySQL")]
    B --> H["📤 HTTP Response\n(redireciona com sucesso)"]
```

### Visão Completa da Infraestrutura

```mermaid
graph TD
    subgraph Usuário
        B[🖥️ Navegador]
        M[📱 Celular / PWA]
    end

    subgraph Docker["🐳 Docker (4 containers)"]
        A["⚡ App Container\nPHP 8.3 + Apache + Laravel 11"]
        D[("🗄️ MySQL 8.4\nBanco de Dados")]
        R[("⚡ Redis\nCache & Sessões")]
        P["🛠️ phpMyAdmin\nAdmin do Banco"]
    end

    B -->|"HTTP via Turbo 8"| A
    M -->|"HTTP / PWA"| A
    A --> D
    A --> R
    P --> D
```

### Os 9 Services da Aplicação

| Service | O que ele faz |
|:---|:---|
| `TransactionService` | O coração do sistema — gerencia todo o ciclo de vida de um lançamento |
| `BankAccountService` | Ajusta saldos com operações atômicas para evitar inconsistências |
| `InstallmentService` | Cria parcelas com algoritmo que garante soma matematicamente exata |
| `CreditCardService` | Calcula em tempo real quanto limite foi usado em cada cartão |
| `CashFlowProjectionService` | Lê o histórico e projeta o futuro financeiro do usuário |
| `RecurringExpenseService` | Injeta despesas recorrentes mensalmente de forma automática |
| `MonthlyReportService` | Gera o PDF do relatório gerencial com DOMPdf |
| `ReportService` | Agrega dados por categoria e cliente para os gráficos |
| `AuditService` | Registra silenciosamente cada alteração feita no sistema |

---

## 🗄️ Banco de Dados

O sistema possui **14 tabelas** organizadas em módulos lógicos:

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string email
        string currency
    }
    bank_accounts {
        bigint id PK
        bigint user_id FK
        string name
        decimal current_balance
    }
    transactions {
        bigint id PK
        bigint bank_account_id FK
        bigint category_id FK
        bigint client_id FK
        decimal amount
        string type
        string status
        date due_date
    }
    credit_cards {
        bigint id PK
        bigint user_id FK
        string name
        decimal credit_limit
        int closing_day
    }
    invoices {
        bigint id PK
        bigint credit_card_id FK
        int month
        int year
        string status
    }
    installments {
        bigint id PK
        bigint invoice_id FK
        decimal amount
        int installment_number
    }
    investments {
        bigint id PK
        bigint user_id FK
        string type
        decimal invested_amount
        decimal rate
    }
    recurring_expenses {
        bigint id PK
        bigint user_id FK
        decimal amount
        int day_of_month
    }
    audit_logs {
        bigint id PK
        bigint user_id FK
        string entity_type
        json old_values
        json new_values
    }

    users ||--o{ bank_accounts : "tem"
    users ||--o{ transactions : "faz"
    users ||--o{ credit_cards : "possui"
    users ||--o{ investments : "faz"
    users ||--o{ recurring_expenses : "configura"
    bank_accounts ||--o{ transactions : "recebe"
    credit_cards ||--o{ invoices : "gera"
    invoices ||--o{ installments : "tem"
```

---

## 💻 Stack Completo

| O quê | Tecnologia | Por que essa escolha |
|:---|:---:|:---|
| Linguagem backend | **PHP 8.3** | JIT Compiler, tipos fortes e enorme ecossistema financeiro |
| Framework | **Laravel 11** | Roteamento, ORM, Agendamento e Segurança já incluídos |
| Banco de dados | **MySQL 8.4** | Conformidade ACID — fundamental para operações financeiras |
| Cache | **Redis** | Sessões e preferências de tema com latência de milissegundos |
| Frontend | **Hotwire Turbo 8** | Navegação instantânea tipo SPA sem precisar de React ou Vue |
| Estilização | **CSS3 puro** | Sistema de variáveis CSS para 3 temas (Light, Dark, AMOLED) |
| PDF | **DOMPdf** | Geração de relatórios HTML → PDF no servidor |
| Infra | **Docker Compose** | 4 containers orquestrados num só comando |

---

## 📱 Acessando pelo Celular

O FinControl é um **Web App Responsivo** — funciona no celular sem instalar nada.

**Como acessar:**

1. ✅ Docker rodando no seu computador
2. 📶 Celular e computador na **mesma rede Wi-Fi**
3. 🔍 Descubra o IP do seu computador:
   - **Windows:** `ipconfig` no CMD → procure "Endereço IPv4"
   - **Mac:** Preferências de Sistema → Rede
   - **Linux:** `hostname -I` no terminal
4. 📱 No navegador do celular, acesse: `http://SEU_IP:8000`

> [!TIP]
> **Transforme em app nativo!**
> - **iPhone (Safari):** Botão de compartilhar → "Adicionar à Tela de Início" 📲
> - **Android (Chrome):** Menu (⋮) → "Adicionar à tela inicial" 📲
>
> O FinControl vai abrir sem barra de endereço, igualzinho a um aplicativo instalado da loja!

---

## 📁 Estrutura de Arquivos

```
fincontrol/
│
├── 📂 app/
│   ├── 📂 Console/Commands/       # Comando para gerar recorrências mensais
│   ├── 📂 Http/Controllers/       # 12 Controllers (um por tela do sistema)
│   ├── 📂 Models/                 # 13 Models Eloquent (tabelas do banco)
│   └── 📂 Services/               # 9 Services com toda a lógica de negócio
│
├── 📂 database/
│   ├── 📂 migrations/             # 14 Migrations (estrutura do banco)
│   └── 📂 seeders/                # Dados de demonstração gerados automaticamente
│
├── 📂 resources/
│   ├── 📂 lang/                   # Traduções PT-BR e EN
│   └── 📂 views/                  # Templates Blade (HTML da aplicação)
│       ├── layouts/               # Sidebar, Topbar e sistema de temas
│       ├── dashboard/             # Painel principal
│       ├── transactions/          # Tela de lançamentos
│       ├── bank-accounts/         # Contas bancárias
│       ├── credit-cards/          # Cartões de crédito
│       ├── reports/               # Relatórios e projeções
│       └── reports/pdf/           # Template do PDF exportável
│
├── 📂 public/
│   ├── 📂 css/                    # Design System (3 temas: Light, Dark, AMOLED)
│   └── 📂 storage/                # Notas fiscais e comprovantes enviados
│
├── 📂 routes/
│   └── web.php                    # Todas as rotas da aplicação
│
└── 🐳 docker-compose.yml          # Configuração dos 4 containers Docker
```

---

---

<div align="center">

---

### Feito com 💙 e muito ☕

*Construído com obsessão por qualidade.*

[![GitHub Stars](https://img.shields.io/github/stars/GatoSemOrelha/fincontrol-engenharia?style=social)](https://github.com/GatoSemOrelha/fincontrol-engenharia/stargazers)
[![GitHub Forks](https://img.shields.io/github/forks/GatoSemOrelha/fincontrol-engenharia?style=social)](https://github.com/GatoSemOrelha/fincontrol-engenharia/network)

</div>
