#!/bin/bash
set -e

# Ensure .env exists
if [ ! -f .env ]; then
  echo "📄 Copying .env.example to .env..."
  cp .env.example .env
fi

echo "🔧 Building and starting containers (first boot)..."
docker compose up --build -d

echo "⏳ Waiting for Laravel container to become healthy..."

while [ "$(docker inspect --format='{{.State.Health.Status}}' task-manager-app-1 2>/dev/null)" != "healthy" ]; do
  sleep 1
done

echo "🔁 Restarting containers to reload environment with correct APP_KEY..."
sleep 1
docker compose down
docker compose up -d

echo "✅ Application ready for use! 🚀"