# Autoffiliate — Master Operations, Architecture & Deployment Documentation

Comprehensive guide and architectural blueprint for the **Autoffiliate** platform — an AI-powered affiliate marketing studio and automated social media syndication system built with **Laravel 13**, **Svelte 5 (Runes)**, **Inertia.js v3**, **Tailwind CSS v4**, and **Vite 8**.

---

## 📑 Table of Contents

1. [Executive Overview & Tech Stack](#-executive-overview--tech-stack)
2. [System Architecture & Data Flows](#-system-architecture--data-flows)
3. [Background Automation & Scheduling](#-background-automation--scheduling)
4. [Facebook Graph API & Multi-Page Management](#-facebook-graph-api--multi-page-management)
5. [AI Token Tracking & Cost Analytics](#-ai-token-tracking--cost-analytics)
6. [CI/CD Pipeline: Build with Node ➔ Deploy via SSH](#-cicd-pipeline-build-with-node--deploy-via-ssh)
7. [Hostinger Deployment Guide](#-hostinger-deployment-guide)
8. [CLI Tooling & Quality Assurance](#-cli-tooling--quality-assurance)
9. [Database Entity-Relationship Model](#-database-entity-relationship-model)
10. [Security & Credential Management](#-security--credential-management)

---

## ⚡ Executive Overview & Tech Stack

Autoffiliate streamlines automated deal hunting, dynamic AI content generation, and multi-channel publication (Facebook Pages, Webhooks, Telegram).

```text
┌────────────────────────────────────────────────────────────────────────┐
│                          AUTOFFILIATE STUDIO                           │
├──────────────────────────┬─────────────────────────────────────────────┤
│ Frontend Framework       │ Svelte 5 (Runes: $state, $derived, $effect) │
│ State & Navigation       │ Inertia.js v3 (Svelte adapter) + Wayfinder  │
│ UI & Design System       │ Tailwind CSS v4, Bits UI, Lucide Svelte     │
│ Backend Framework        │ Laravel 13 (PHP 8.3+) with Fortify (2FA)    │
│ Background Queue & Cache │ Redis 7 + Laravel Queued Jobs               │
│ Database Persistence     │ MariaDB 11 / MySQL 8.0                      │
│ AI Intelligence Engines  │ OpenAI (GPT-4o-mini), DeepSeek, Gemini      │
│ Social Media Publishing  │ Meta Facebook Graph API v20.0               │
│ Task Automation Daemon   │ Supervisor / Laravel Scheduler              │
└──────────────────────────┴─────────────────────────────────────────────┘
```

---

## 🏛️ System Architecture & Data Flows

### High-Level Component Architecture

```mermaid
graph TD
    subgraph Client_Layer ["Client & Frontend Layer (SPA)"]
        UI["Svelte 5 (Runes) + Tailwind CSS v4"]
        InertiaClient["Inertia.js v3 Client Adapter"]
        Wayfinder["Wayfinder TypeScript Route Bridge"]
        UI --> InertiaClient
        UI --> Wayfinder
    end

    subgraph App_Layer ["Application & Backend Layer (Laravel 13)"]
        Nginx["Nginx Web Server / Gateway"]
        Router["Laravel Router & Fortify Auth"]
        InertiaMiddleware["Inertia Page Response Middleware"]

        subgraph Controllers ["Controllers & Services"]
            DC["DashboardController<br/>(AI Analytics & Summaries)"]
            PC["PostController<br/>(Drafts, Captions & Publishing)"]
            WC["WorkflowController<br/>(Studio, Pipelines & Triggers)"]
            SC["SettingsController<br/>(Tokens, Keys & Multi-Page)"]
        end

        Nginx --> Router
        Router --> InertiaMiddleware
        InertiaMiddleware --> DC
        InertiaMiddleware --> PC
        InertiaMiddleware --> WC
        InertiaMiddleware --> SC
    end

    subgraph Background_Layer ["Background Automation & Queue Worker"]
        Scheduler["Laravel Scheduler<br/>(workflows:run every minute)"]
        JobQueue["Queue Worker<br/>(ExecuteWorkflowRuleJob)"]
        Supervisor["Supervisor / Cron Daemon"]
        Supervisor --> Scheduler
        Scheduler --> JobQueue
    end

    subgraph Data_Layer ["Persistence & Cache Layer"]
        MySQL[("MariaDB 11 / MySQL 8.0<br/>posts, workflow_rules, ai_usage_logs,<br/>social_accounts, settings, users")]
        Redis[("Redis 7<br/>Queues, Cache, Sessions")]
    end

    subgraph External_Layer ["External APIs & Integrations"]
        FB["Meta Facebook Graph API v20.0<br/>(Page Feed & Engagement)"]
        AI["AI LLM Engines<br/>(OpenAI, DeepSeek, Gemini)"]
        Shopee["Shopee Affiliate Link Resolver"]
        Webhook["n8n Webhook & Telegram Bot"]
    end

    InertiaClient <==>|Inertia JSON / State Bridge| Nginx
    DC --> MySQL
    PC --> MySQL
    WC --> MySQL
    SC --> MySQL

    DC --> Redis
    JobQueue --> MySQL
    JobQueue --> Redis

    PC --> FB
    PC --> AI
    PC --> Webhook

    JobQueue --> FB
    JobQueue --> AI
    JobQueue --> Webhook
    WC --> Shopee
```

---

## ⚙️ Background Automation & Scheduling

Automated publishing executes seamlessly 24/7 in the background without keeping browser tabs open:

1. **Artisan Scheduler Command:** `php artisan workflows:run`
   - Evaluates active rules in Philippine Time (`Asia/Manila`, `UTC+8`).
   - Checks matching weekdays, weekends, specific days (`Mon`, `Tue`), and time slots (`08:00 AM`, `13:00`).
   - Enforces a 2-minute debounce window via `last_run` timestamp.
2. **Queued Asynchronous Job:** [`ExecuteWorkflowRuleJob`](../app/Jobs/ExecuteWorkflowRuleJob.php)
   - Evaluates dynamic context (time-of-day greetings, weather, occasions).
   - Generates AI captions and logs token usage in `ai_usage_logs`.
   - Creates post records and publishes to the Meta Graph API.
   - Dispatches outbound webhooks (e.g. n8n).
3. **Scheduler Registration:** Scheduled to run every minute in [`routes/console.php`](../routes/console.php):
   ```php
   Schedule::command('workflows:run')->everyMinute();
   ```

---

## 🔑 Facebook Graph API & Multi-Page Management

### Permanent Page Access Token Exchange Flow

To eliminate the need for monthly token refreshes, Autoffiliate features a **1-Click Permanent Token Generator**:

```mermaid
sequenceDiagram
    autonumber
    participant User as Admin User (Settings UI)
    participant Backend as SettingsController
    participant Meta as Meta Graph API v20.0
    participant DB as MariaDB (social_accounts)

    User->>Backend: Provide App ID, App Secret & Short-Lived User Token (EAAB...)
    Backend->>Meta: GET /oauth/access_token (grant_type=fb_exchange_token)
    Meta-->>Backend: Return 60-day Long-Lived User Token
    Backend->>Meta: GET /me/accounts?access_token={60_day_token}
    Meta-->>Backend: Return Managed Pages list with Permanent Page Tokens
    Backend->>DB: Store / Update SocialAccount record (Never Expires ♾️)
    Backend-->>User: Connected & Ready for 24/7 Publishing
```

### Caption & Tag Formatting Pipeline
All generated posts adhere to strict compliance guidelines:

```text
┌─────────────────────────────────────────────────────────────┐
│ [Dynamic Hook / Time Greeting / Selling Points Body]        │
│                                                             │
│ Affiliate link. Price and availability may change anytime.  │
│ (Condition: Only appended if affiliate URL is present)      │
│                                                             │
│ #TechSulitDeals #ShopeePH #automated (Trailing Tags at End) │
└─────────────────────────────────────────────────────────────┘
```

---

## 🤖 AI Token Tracking & Cost Analytics

Every AI generation is tracked in the database `ai_usage_logs` table:

- **Metrics Tracked:** `post_id`, `provider` (OpenAI, DeepSeek, Gemini), `model`, `style`, `prompt_tokens`, `completion_tokens`, `total_tokens`, `estimated_cost`.
- **Live Dashboard Stats:**
  - Total AI Generations
  - Cumulative Token Count (Prompt vs Completion)
  - Estimated Total Spend ($ USD)
  - Usage Breakdown by Provider & Tone Preset
  - Real-Time AI Generation Feed

---

## 🚀 CI/CD Pipeline: Build with Node ➔ Deploy via SSH

The automated pipeline ([`.github/workflows/ci-cd.yml`](../.github/workflows/ci-cd.yml)) compiles frontend assets with Node 20 on GitHub's cloud runners and deploys to Hostinger over SSH.

### Required GitHub Secrets / Variables

Configure in **Settings ➔ Secrets and variables ➔ Actions**:

| Name | Type | Example | Description |
|---|---|---|---|
| `HOSTINGER_SSH_HOST` | Secret / Variable | `185.199.108.153` | Server IP or Hostname |
| `HOSTINGER_SSH_USER` | Secret / Variable | `u123456789` | SSH Username (`root` on VPS) |
| `HOSTINGER_SSH_PORT` | Secret / Variable | `65002` | SSH Port (`65002` on hPanel, `22` on VPS) |
| `HOSTINGER_SSH_PASSWORD` | **Secret** | `YourPassword` | SSH Password (or use SSH Key) |
| `HOSTINGER_SSH_KEY` | **Secret** | `-----BEGIN OPENSSH...` | Private SSH Key (optional) |
| `HOSTINGER_APP_PATH` | Secret / Variable | `/home/u123456789/domains/yourdomain.com/app` | Absolute application directory path |

### Pipeline Workflow Execution

```mermaid
sequenceDiagram
    autonumber
    participant Dev as Push to main
    participant CI as GitHub Runner (Node 20)
    participant Server as Hostinger Server (PHP 8.3 & MySQL)

    Dev->>CI: Trigger Build & Deploy Pipeline
    Note over CI: 1. Compiles frontend assets (npm ci && npm run build)
    Note over CI: 2. Runs linters, type checks & 53 PHPUnit tests
    CI->>Server: Connect over SSH (Port 65002 / 22)
    Note over Server: Server does NOT need Node.js!
    Server->>Server: 3. php artisan down (Maintenance mode)
    Server->>Server: 4. git pull origin main (Pulls code + pre-built assets)
    Server->>Server: 5. composer install --no-dev --optimize-autoloader
    Server->>Server: 6. php artisan migrate --force
    Server->>Server: 7. Cache config, routes, views & wayfinder
    Server->>Server: 8. php artisan up (Exit maintenance mode)
    Server-->>CI: ✅ Deployment Complete!
```

---

## 🌐 Hostinger Deployment Guide

### Option 1: Hostinger Shared / Cloud Hosting (hPanel)

1. **PHP Configuration:** In hPanel, navigate to **Advanced ➔ PHP Configuration** and select **PHP 8.3** with `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, `zip`, `bcmath`.
2. **Document Root:** Set web root directory to `/public_html/public` (or symlink `public` to `public_html`).
3. **Database:** Create a MySQL database and user in **Databases ➔ Management**.
4. **Deploy via Git / SSH:**
   ```bash
   ssh -p 65002 u123456789@YOUR_SERVER_IP
   cd ~/domains/yourdomain.com
   git clone https://github.com/warr-dev/autoffiliate.git app
   cd app
   composer install --no-dev --optimize-autoloader
   cp .env.example .env
   # Configure DB details in .env
   php artisan key:generate
   php artisan migrate --seed --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
5. **hPanel Cron Job Setup:**
   - Go to **Advanced ➔ Cron Jobs** ➔ **Custom**.
   - Schedule: `* * * * *` (Every Minute).
   - Command:
     ```bash
     cd /home/u123456789/domains/yourdomain.com/app && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
     ```

### Option 2: Hostinger VPS (Ubuntu 22.04 / 24.04)

1. **Install Packages:**
   ```bash
   sudo apt update && sudo apt install -y nginx php8.3-fpm php8.3-mysql php8.3-curl php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath php8.3-redis mariadb-server redis-server supervisor certbot python3-certbot-nginx
   ```
2. **Supervisor Daemon:**
   Configure `/etc/supervisor/conf.d/autoffiliate-scheduler.conf`:
   ```ini
   [program:autoffiliate-scheduler]
   process_name=%(program_name)s
   command=/usr/bin/php /var/www/autoffiliate/artisan schedule:work
   autostart=true
   autorestart=true
   user=www-data
   redirect_stderr=true
   stdout_logfile=/var/www/autoffiliate/storage/logs/scheduler-supervisor.log
   ```
3. **1-Click Local Deploy Command:**
   ```bash
   npm run deploy
   # or
   composer run deploy
   ```

---

## 🛠️ CLI Tooling & Quality Assurance

Autoffiliate includes automated test and code-quality commands:

| Command | Purpose |
|---|---|
| `composer run dev` | Runs PHP server, Vite, and background Scheduler concurrently |
| `composer run ci:check` | Executes ESLint, Prettier, Svelte-Check, Pint, PHPStan, and Pest |
| `composer run test` | Clears config and executes Pest / PHPUnit test suite |
| `composer run lint` | Auto-fixes PHP code style via Laravel Pint |
| `npm run lint` | Auto-fixes JavaScript/Svelte via ESLint |
| `npm run format` | Auto-formats codebase via Prettier |
| `npm run types:check` | Validates TypeScript & Svelte compiler types |
| `npm run deploy` | Compiles assets and deploys to remote Hostinger via SSH |
| `php artisan workflows:run` | Evaluates and executes scheduled workflow rules immediately |
| `php artisan make:user` | Creates administrative users via interactive CLI |

---

## 🗄️ Database Entity-Relationship Model

```mermaid
erDiagram
    USERS ||--o{ POSTS : creates
    USERS {
        bigint id PK
        string name
        string email UK
        timestamp email_verified_at
        string password
        text two_factor_secret
        timestamp created_at
    }

    POSTS ||--o{ AI_USAGE_LOGS : generates
    POSTS {
        string id PK
        string product_title
        text affiliate_url
        text caption
        text tags
        string status
        json media_files
        string facebook_post_id
        string facebook_post_url
        timestamp created_at
    }

    WORKFLOW_RULES {
        string id PK
        string name
        string category
        string frequency
        json times
        json days
        string target_page
        json workflow_actions
        json action_contexts
        text general_context
        text weather_context
        text occasion_context
        json tones
        json personas
        string status
        timestamp last_run
        timestamp created_at
    }

    SOCIAL_ACCOUNTS {
        bigint id PK
        string platform
        string account_id
        string name
        text access_token
        boolean is_enabled
        json extra_config
        timestamp token_expires_at
        timestamp created_at
    }

    AI_USAGE_LOGS {
        bigint id PK
        string post_id
        string provider
        string model
        string style
        integer prompt_tokens
        integer completion_tokens
        integer total_tokens
        decimal estimated_cost
        timestamp created_at
    }

    SETTINGS {
        bigint id PK
        string key UK
        text value
        timestamp created_at
    }
```

---

## 🛡️ Security & Credential Management

1. **Database Key Storage:** API keys and access tokens are stored in the database `settings` and `social_accounts` tables — never hardcoded in client templates.
2. **Frontend Masking:** Secrets in the Settings interface are masked by default (`••••••••`) with client-side visibility toggles.
3. **Authentication & 2FA:** Fortify authentication with brute-force rate-limiting, CSRF token validation, and Passkey / Two-Factor Authentication support.
