#!/bin/bash
set -e

# Setup SSH permissions if mounted
if [ -d "$HOME/.ssh" ]; then
    chmod 700 "$HOME/.ssh" 2>/dev/null || true
    chmod 600 "$HOME/.ssh"/* 2>/dev/null || true
    chmod 644 "$HOME/.ssh"/*.pub 2>/dev/null || true
fi

# Start crond in background with root privileges if cron is present
if [ -f /etc/crontabs/root ]; then
    sudo crond -b -l 8
fi


# Execute passed command or default to CMD
exec "$@"
