# Autoffiliate - System Architecture & Technical Blueprint

Technical architecture, component interaction contracts, data models, and execution pipelines of the **Autoffiliate** platform.

---

## 🏛️ High-Level System Architecture

```mermaid
graph TD
    subgraph Client_Layer ["Client & Frontend Layer (SPA)"]
        UI["Svelte 5 (Runes: $state, $derived, $effect)"]
        Tailwind["Tailwind CSS v4 + Bits UI"]
        InertiaClient["Inertia.js v3 Client Adapter"]
        Wayfinder["Wayfinder TypeScript Route Bridge"]
        UI --> InertiaClient
        UI --> Wayfinder
        UI --> Tailwind
    end

    subgraph App_Layer ["Application & Backend Layer (Laravel 13)"]
        Nginx["Nginx Web Server / Reverse Proxy"]
        Router["Laravel 13 Router & Fortify 2FA"]
        InertiaMiddleware["Inertia Page Response Middleware"]
        ApiAuthMiddleware["AuthenticateApiToken Middleware"]

        subgraph Controllers ["Controllers & Services"]
            DC["DashboardController<br/>(AI Analytics & Summaries)"]
            AC["AnalyticsController<br/>(Telemetry, Exports & Pruning)"]
            PC["PostController<br/>(Drafts, Captions & Publishing)"]
            WC["WorkflowController<br/>(Studio, Pipelines & Triggers)"]
            SC["SettingsController<br/>(Tokens, Keys & Multi-Page)"]
            API_Auth["AuthController<br/>(Bearer Tokens & API)"]
        end

        Nginx --> Router
        Router --> InertiaMiddleware
        Router --> ApiAuthMiddleware
        InertiaMiddleware --> DC
        InertiaMiddleware --> AC
        InertiaMiddleware --> PC
        InertiaMiddleware --> WC
        InertiaMiddleware --> SC
        ApiAuthMiddleware --> API_Auth
    end

    subgraph Background_Layer ["Background Automation & Queue Worker"]
        Scheduler["Laravel Scheduler<br/>(workflows:run every minute)"]
        JobQueue["Queue Worker<br/>(ExecuteWorkflowRuleJob)"]
        Supervisor["Supervisor / Cron Daemon / Web-Cron"]
        Supervisor --> Scheduler
        Scheduler --> JobQueue
    end

    subgraph Data_Layer ["Persistence & Cache Layer"]
        MySQL[("MariaDB 11 / MySQL 8.0<br/>posts, workflow_rules, ai_usage_logs,<br/>personal_access_tokens, social_accounts,<br/>settings, users")]
        Redis[("Redis 7<br/>Queues, Cache, Sessions")]
    end

    subgraph External_Layer ["External APIs & Integrations"]
        FB["Meta Facebook Graph API v20.0<br/>(Page Feed & Engagement)"]
        AI["AI LLM Engines<br/>(OpenAI, DeepSeek, Gemini, Claude, Groq)"]
        Shopee["Shopee Affiliate Link Resolver"]
        Webhook["n8n Webhook & Telegram Bot"]
    end

    InertiaClient <==>|Inertia JSON / State Bridge| Nginx
    DC --> MySQL
    AC --> MySQL
    PC --> MySQL
    WC --> MySQL
    SC --> MySQL
    API_Auth --> MySQL

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

## 🔄 Core Data & Execution Flows

### 1. Automated Workflow Execution Flow

```mermaid
sequenceDiagram
    autonumber
    participant S as Scheduler (workflows:run)
    participant J as ExecuteWorkflowRuleJob
    participant DB as MariaDB (workflow_rules & posts)
    participant AI as AI Provider (OpenAI/DeepSeek/Gemini)
    participant FB as Meta Graph API v20.0
    participant WH as Outbound Webhook (n8n)

    S->>DB: Query active rules (status = 'active')
    S->>S: Check rule.isDue() in Asia/Manila timezone (with 10-min grace window)
    alt Rule is Due
        S->>J: Dispatch ExecuteWorkflowRuleJob
        J->>AI: Generate dynamic time/weather hook & body
        AI-->>J: Return styled caption, token counts & execution time
        J->>DB: Create Post record (status = 'draft')
        J->>DB: Record AI telemetry in ai_usage_logs
        alt Action includes 'Publish'
            J->>FB: POST /{page_id}/feed with caption & media
            FB-->>J: Return Facebook Post ID
            J->>DB: Update Post (status = 'published', facebook_post_id)
        end
        alt Outbound Webhook configured
            J->>WH: POST payload to n8n / Telegram
        end
        J->>DB: Update rule.last_run = now()
    end
```

### 2. Shopee Deal Creation & AI Caption Pipeline

```mermaid
sequenceDiagram
    autonumber
    participant U as Admin User
    participant P as PostController
    participant S as ShopeeExtractService
    participant AI as AiContentGeneratorService
    participant DB as MariaDB

    U->>P: Submit Shopee URL & Caption Style (e.g. viral_ai)
    P->>S: Extract product title, price & high-res media
    S-->>P: Return canonical info & images
    P->>AI: Generate structured deal post with style
    AI-->>P: Return formatted body, disclosure & token counts
    P->>DB: Insert Post (status = 'draft')
    P->>DB: Log usage in ai_usage_logs (cost & latency)
    P-->>U: Return Draft preview in UI
```

### 3. AI Token Telemetry & Analytics Pipeline

```mermaid
sequenceDiagram
    autonumber
    participant Client as Analytics Dashboard (/analytics)
    participant Controller as AnalyticsController
    participant Model as AiUsageLog
    participant DB as MariaDB

    Client->>Controller: GET /analytics?period=30d&provider=all
    Controller->>Model: getAnalytics(['period' => '30d'])
    Model->>DB: Query aggregated token sums, cost totals & daily series
    DB-->>Model: Raw database aggregations
    Model->>Model: Calculate model price multipliers & breakdown percentages
    Model-->>Controller: Structured telemetry payload
    Controller-->>Client: Render Analytics/Index with KPI cards & charts
```

---

## 🧩 Component Responsibilities

| Component | Class / File | Primary Responsibility |
|---|---|---|
| **AI Generator** | [`AiContentGeneratorService`](../app/Services/AiContentGeneratorService.php) | Connects to OpenAI, DeepSeek, Gemini, Claude, Groq, and fallback template engine; tracks latency. |
| **Telemetry Logger** | [`AiUsageLog`](../app/Models/AiUsageLog.php) | Persists token metrics, applies accurate model pricing rates, and computes analytics breakdowns. |
| **Analytics Controller** | [`AnalyticsController`](../app/Http/Controllers/AnalyticsController.php) | Renders analytics UI, exports CSV/JSON reports, and manages log pruning. |
| **Product Extractor** | [`ShopeeExtractService`](../app/Services/ShopeeExtractService.php) | Unshortens Shopee links, extracts media carousels, and formats prices. |
| **Workflow Engine** | [`WorkflowController`](../app/Http/Controllers/WorkflowController.php) & [`ExecuteWorkflowRuleJob`](../app/Jobs/ExecuteWorkflowRuleJob.php) | Schedules, triggers, and executes autonomous syndication rules. |
| **API Auth Middleware** | [`AuthenticateApiToken`](../app/Http/Middleware/AuthenticateApiToken.php) | Authenticates API requests via Bearer token or X-API-Key header. |
