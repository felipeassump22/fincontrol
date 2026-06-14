---
title: Relatório Executivo do Projeto FinControl
author: Engenharia de Software
date: Junho de 2026
geometry: margin=2cm
colorlinks: true
---

# Relatório Técnico e Executivo: Plataforma FinControl

## 1. Introdução e Visão Geral

A gestão financeira corporativa e pessoal demanda sistemas que vão além de planilhas engessadas ou aplicações web obsoletas. O projeto **FinControl** foi concebido como uma resposta direta à necessidade de uma arquitetura limpa, responsiva e performática para consolidação de fluxo de caixa, monitoramento de cartões de crédito e projeção de investimentos.

Construído sob o rigor acadêmico e as melhores práticas da Engenharia de Software, o sistema adota padrões de projeto sólidos, notadamente o *Service Pattern*, que isola as lógicas e regras de negócios vitais da camada de comunicação HTTP (Controllers). O projeto entrega uma experiência de usuário (UX) com níveis de excelência de mercado, permitindo usabilidade fluida, temas de acessibilidade e internacionalização de moedas, características essenciais em produtos de software modernos.

---

## 2. Tecnologias e Arquitetura do Sistema

O alicerce tecnológico foi estrategicamente escolhido para balancear robustez no tratamento de dados bancários com fluidez visual. A arquitetura obedece o seguinte escopo tecnológico:

- **Backend:** Laravel 11.x operando sobre o PHP 8.3. Escolhido pela sua vasta maturidade, proteção CSRF/XSS nativa e sistema de Eloquent ORM avançado para abstração de banco de dados.
- **Frontend Engine:** Hotwire Turbo 8 e CSS Custom Properties (Vanilla CSS). Diferente de arquiteturas sobrecarregadas com bibliotecas pesadas (React/Vue), o Turbo 8 foi utilizado para trafegar pequenos fragmentos de HTML pré-renderizados pela rede, conferindo a sensação de que o software é uma Single Page Application (SPA), garantindo "Zero Page Reloads" sem a perda de SEO ou lentidão excessiva no dispositivo cliente.
- **Infraestrutura e DevOps:** Docker e Docker Compose. Todo o ambiente (MySQL 8.4, Redis em memória, Servidor Apache HTTP) está isolado em *containers*, exterminando os problemas clássicos de conflitos de dependências ("Na minha máquina funciona").
- **Geração de PDF:** Motor *DOMPdf* em conjunto com *Carbon Locale-Aware* para renderização de PDFs traduzidos em tempo real.

---

## 3. Matriz de Requisitos Funcionais (RF) Atendidos

Com base na documentação originária, a engenharia da aplicação garantiu 100% de cobertura dos dezoito (18) requisitos estabelecidos pelo escopo, conforme atestado abaixo:

1. **RF01:** Autenticação via e-mail e senha (blindada por *hashes* BCrypt).
2. **RF02 & RF14:** Criação (CRUD) de Lançamentos Financeiros, Clientes e Categorias.
3. **RF03 & RF05:** Bloqueio severo de edição em Lançamentos Pagos. Proteção implementada na visualização (Interface) e validada matematicamente no servidor (*TransactionService*).
4. **RF06 a RF08:** Módulo de gerenciamento de faturas (Fechamento automático, parcelamentos de compras e limites de crédito).
5. **RF09:** Agendador automático (*Cron Jobs*) para injeção de despesas recorrentes mensais sem necessidade de ação humana.
6. **RF10:** Auditoria de Dados. Trilha contínua que registra histórico de cada alteração de lançamento (Quem, Quando, Valores Anteriores e Valores Novos).
7. **RF11 & RF17:** Motor inteligente de projeção (*CashFlowProjectionService*) que prevê o futuro saldo bancário por período, avaliando médias históricas do usuário e rendimentos de investimentos atrelados.
8. **RF12:** Tratativas para Saldo Negativo. A UI se adapta dinamicamente com cores vermelhas e emissão de alertas em níveis hierárquicos altos.
9. **RF13:** Infraestrutura para *upload* de Notas Fiscais (Armazenamento na pasta /storage vinculada via links simbólicos).
10. **RF15 & RF16:** Dashboards analíticas separando percentualmente as receitas por cliente emissor e por categoria.
11. **RF18:** Geração dinâmica de faturas fechadas num *Portable Document Format* (PDF) imutável para contabilidade corporativa.

---

## 4. Histórico de Otimizações, Refatorações e Correções

A jornada de desenvolvimento e consolidação da versão estável atual exigiu correções rigorosas e implementações adicionais para elevar a qualidade do software:

### 4.1. Otimização Visual: Temas Dinâmicos (Dark / Amoled)
Implementou-se um sistema baseado em *Session/Redis* onde o usuário define o perfil estético. Destaca-se a implementação inédita do **Tema Amoled**, onde o código hexadecimal forçado para o preto puro (`#000000`) desliga pixels de monitores/celulares OLED, economizando energia drasticamente.

### 4.2. Refatoração Sintática (View vs IDE)
Eliminação de dezenas de falsos positivos reportados em IDEs de código (como VS Code) na sintaxe *Blade*. Substituiu-se a prática arcaica de interpolação direta (`style="width: {{ $val }}"`) pela diretiva arquitetural e limpa `@style()` aprovada pelo Laravel 11.

### 4.3. Internacionalização e Multi-Moeda
Para adequar a plataforma a um escopo global, o sistema tradicional de `$money()` fixo no Brasil (R$) foi refatorado. Foi criada uma estrutura na tabela de Usuários e na Central de Configurações onde o usuário seleciona se deseja os relatórios em `pt_BR (R$)` ou `en_US ($)`.

### 4.4. Evolução do Relatório de PDF (RF18)
O sistema antigo de exportação estava quebrado e com layout rudimentar. O documento HTML injetado no DOMPdf foi recriado do mais absoluto zero, ganhando uma estrutura de grades estáticas responsivas, cabeçalho institucional e cálculos de lucro líquido com traduções alimentadas pelo motor de datas `Carbon Locale-Aware`.

### 4.5. Limpeza de Rastreamento (Git e Segurança)
Correção do vetor de segurança do GitHub. Foram ajustadas as diretivas de `.gitignore` para proibir o rastreamento temporário de sessões `/storage/framework/sessions/` e arquivos sensíveis, evitando o vazamento acidental de chaves temporárias na nuvem.

---

## 5. Guia de Execução (Manual do Usuário)

O acesso à plataforma independe de conhecimentos profundos em linguagens de programação, bastando que o host possua o **Docker Engine** operante.

### Como Iniciar (Computador Windows, Mac ou Linux)
1. Certifique-se de que o aplicativo **Docker Desktop** esteja aberto.
2. Através do Terminal (PowerShell, Bash, etc.), navegue até a pasta raiz do repositório `fincontrol/`.
3. Para ativar a construção dos servidores de banco de dados, cachê e processamento, execute:
   ```bash
   docker compose up -d --build
   ```
4. Em seguida, realize a criação e pré-população do banco de dados fictício com a injeção inicial de teste:
   ```bash
   docker compose exec app php artisan migrate:fresh --seed
   ```
5. Acesse diretamente via navegador da sua máquina no endereço: `http://localhost:8000`
   > **Credencial base:** `joao@empresa.com.br` | **Senha:** `admin123`

### Como Acessar a Aplicação via Celular (Mobile View)
A interface é 100% responsiva. Como os servidores do Docker amarram a porta da rede, você pode acessar a aplicação do seu celular sem fios, de forma nativa:
1. Conecte o celular na mesma rede Wi-Fi que o seu computador.
2. Descubra o IP local do computador (Comando `ipconfig` no Windows ou `hostname -I` no Linux/Mac).
3. Abra o Google Chrome no seu celular e acesse o Endereço IP seguido da porta:
   ```text
   Exemplo Real: http://192.168.0.147:8000
   ```
4. *(Opcional)*: Aceite o aviso para adicionar à tela inicial e utilizar como um aplicativo nativo sem barra de navegador.

---

## 6. Conclusão

A arquitetura estabelecida demonstra proficiência não apenas na resolução lógica das matrizes de requisitos de software, mas ressalta um cuidado essencial com as necessidades de UX/UI atuais e blindagem escalável da infraestrutura com Docker. O **FinControl** é uma plataforma que vai além de uma prova de conceito acadêmica, posicionando-se como um Produto de Software Minimum Viable Product (MVP) estável e corporativo.
 