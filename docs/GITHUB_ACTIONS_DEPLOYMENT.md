# Automated CI/CD Deployment to Hostinger via GitHub Actions

This guide explains how to configure GitHub Secrets to enable automatic, zero-downtime deployments to **Hostinger** every time you push code to GitHub.

---

## 🔑 Required GitHub Secrets

In your GitHub repository, go to **Settings ➔ Secrets and variables ➔ Actions** and click **New repository secret**.

Add the following secrets:

| Secret Name | Description | Example (Shared / Cloud hPanel) | Example (Hostinger VPS) |
|---|---|---|---|
| `HOSTINGER_SSH_HOST` | Server IP or Hostname | `185.199.108.153` or `ssh.yourdomain.com` | `185.199.108.153` |
| `HOSTINGER_SSH_USER` | SSH Username | `u123456789` | `root` or `ubuntu` |
| `HOSTINGER_SSH_PORT` | SSH Port | `65002` *(Standard Hostinger hPanel port)* | `22` *(Standard VPS port)* |
| `HOSTINGER_SSH_KEY` | Private SSH Key *(or use PASSWORD below)* | Paste private key (`-----BEGIN OPENSSH PRIVATE KEY-----...`) | Paste private key |
| `HOSTINGER_SSH_PASSWORD` | SSH Password *(Alternative to Key)* | Your SSH password | Your VPS root password |
| `HOSTINGER_APP_PATH` | Absolute path to your app directory | `/home/u123456789/domains/yourdomain.com/app` | `/var/www/autoffiliate` |

---

## 🛠️ How to Find Your Hostinger SSH Details

### If using Hostinger Shared / Cloud Hosting (hPanel):
1. Log in to **Hostinger hPanel**.
2. Go to **Websites ➔ Dashboard ➔ Advanced ➔ SSH Access**.
3. Enable SSH Access if not already active.
4. Note down:
   - **SSH IP:** (e.g. `185.x.x.x` $\rightarrow$ `HOSTINGER_SSH_HOST`)
   - **SSH Port:** (usually `65002` $\rightarrow$ `HOSTINGER_SSH_PORT`)
   - **SSH Username:** (e.g. `u123456789` $\rightarrow$ `HOSTINGER_SSH_USER`)
   - **SSH Password:** (click "Change Password" to set a secure password if needed $\rightarrow$ `HOSTINGER_SSH_PASSWORD`)

### If using Hostinger VPS:
1. Go to **VPS ➔ Manage ➔ Overview**.
2. Note down your **IP Address** (`HOSTINGER_SSH_HOST`), **Port 22** (`HOSTINGER_SSH_PORT`), and **root** / user password.

---

## ⚡ How the Automated Pipeline Works

Whenever you push to the `main` branch:

```mermaid
sequenceDiagram
    autonumber
    participant Git as GitHub (Push to main)
    participant CI as GitHub Actions Runner (Node 20)
    participant Hostinger as Hostinger Server (PHP & MariaDB)

    Git->>CI: Trigger CD - Deploy to Hostinger via SSH
    CI->>CI: Checkout code & compile assets (npm ci && npm run build)
    CI->>Hostinger: Connect via SSH (Port 65002 / 22)
    Note over Hostinger: Server never needs Node.js!
    Hostinger->>Hostinger: Put app in maintenance mode (php artisan down)
    Hostinger->>Hostinger: Pull latest git changes (git pull origin main)
    Hostinger->>Hostinger: Install Composer packages (composer install --no-dev)
    Hostinger->>Hostinger: Run database migrations (php artisan migrate --force)
    Hostinger->>Hostinger: Optimize config, routes, and views cache
    Hostinger->>Hostinger: Restart background workers & exit maintenance mode (php artisan up)
    Hostinger-->>CI: Deployment Successful!
```

---

## 🚀 Manual Deployment Trigger

You can also trigger a deployment manually at any time:
1. Go to your GitHub repository.
2. Click the **Actions** tab.
3. Select **"CD - Deploy to Hostinger via SSH"** in the left sidebar.
4. Click **Run workflow ➔ Branch: main ➔ Run workflow**.
