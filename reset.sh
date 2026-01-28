#!/bin/bash
echo "Stopping and removing containers + volumes..."
docker compose -f docker-compose.yml down -v

echo "Starting fresh..."
docker compose -f docker-compose.yml up -d

echo "Reset complete!"
