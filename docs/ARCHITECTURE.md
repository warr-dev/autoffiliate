# Autoffiliate - System Architecture & Design

This document details the architectural design, component interactions, data models, and execution pipelines of the **Autoffiliate** platform.

---

## 🏛️ High-Level System Architecture

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
        Nginx["Nginx Web Server"]
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

    InertiaClient <==>|Inertia JSON / SSR| Nginx
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

## 🔄 Core Data & Execution Flows

### 1. Automated Workflow Execution Flow

```mermaid
sequenceDiagram
    autonumber
    participant S as Scheduler (workflows:run)
    participant J as ExecuteWorkflowRuleJob
    participant DB as MariaDB (workflow_rules & posts)
    participant AI as AI Provider (OpenAI/DeepSeek)
    participant FB as Meta Graph API v20.0
    participant WH as Outbound Webhook (n8n)

    S->>DB: Query active rules (status = 'active')
    S->>S: Check rule.isDue() in Asia/Manila timezone
    alt Rule is Due
        S->>J: Dispatch ExecuteWorkflowRuleJob
        J->>AI: Generate dynamic time/weather hook & body
        AI-->>J: Return styled caption & token counts
        J->>DB: Record post draft & log AI usage (ai_usage_logs)
        alt Action includes Publish
            J->>FB: POST /v20.0/{page_id}/feed (caption + affiliate_url)
            FB-->>J: Return post_id (live URL)
            J->>DB: Update post status to 'published' + facebook_post_id
        end
        opt Outbound Webhook Configured
            J->>WH: POST post.published payload
        end
        J->>DB: Update rule.last_run = now()
    end
```

---

### 2. Facebook Permanent Page Token Exchange Flow

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
    Backend->>DB: Store / Update SocialAccount record (token, page_id, name)
    Backend-->>User: Auto-populate and show active status (Never Expires ♾️)
```

---

### 3. Caption & Compliance Formatting Pipeline

```mermaid
flowchart TD
    A["Raw Product Deal / Prompt Context"] --> B{"Style Selected"}
    B -->|viral| C1["🔥 SUPER SALE ALERT! Hook"]
    B -->|taglish| C2["Sobrang sulit nito mga besh! Hook"]
    B -->|specs| C3["📌 Product Specs & Highlights"]
    B -->|standard| C4["✨ Great deal showcase"]

    C1 --> D["Assemble Post Body & Selling Points"]
    C2 --> D
    C3 --> D
    C4 --> D

    D --> E{"Contains Affiliate Link?"}
    E -->|Yes| F["Append Affiliate Disclosure<br/>'Affiliate link. Price and availability may change anytime.'"]
    E -->|No| G["Skip Disclosure Notice"]

    F --> H["Append Trailing Hashtags at VERY END<br/>#TechSulitDeals #ShopeePH #automated"]
    G --> H

    H --> I["Final Publication Message"]
```

---

## 🗄️ Database Entity-Relationship Diagram

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

## 🛡️ Security Architecture

1. **Credential Segregation**: AI API keys, Meta App Secrets, and webhook secrets are stored securely in the database `settings` table and never exposed in frontend Inertia props.
2. **UI Masking**: Sensitive fields in the Settings page (`Settings/Index.svelte`) are masked (`••••••••`) by default with client-side toggle controls.
3. **Session & Auth Protection**: Built on Laravel Fortify with 2FA support, rate-limiting on authentication endpoints, and strict CSRF token validation.
4. **Permanent Token Isolation**: Page Access Tokens are tied to specific Page IDs with explicit minimal scopes (`pages_manage_posts`, `pages_read_engagement`).
