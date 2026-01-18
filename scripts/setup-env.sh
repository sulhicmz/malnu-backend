#!/bin/bash
# Environment Setup Script for Malnu Backend
# This script helps developers set up their .env file from .env.example
# and generates secure random secrets for production-ready configuration.

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Malnu Backend Environment Setup${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if .env already exists
if [ -f ".env" ]; then
    echo -e "${YELLOW}Warning: .env file already exists.${NC}"
    read -p "Do you want to overwrite it? (yes/no): " -r
    if [[ ! $REPLY =~ ^yes$ ]]; then
        echo -e "${GREEN}Setup cancelled.${NC}"
        exit 0
    fi
    echo -e "${YELLOW}Backing up existing .env to .env.backup...${NC}"
    cp .env .env.backup
fi

# Check if .env.example exists
if [ ! -f ".env.example" ]; then
    echo -e "${RED}Error: .env.example not found!${NC}"
    exit 1
fi

echo -e "${GREEN}Step 1: Generating secure secrets...${NC}"

# Generate APP_KEY
APP_KEY=$(openssl rand -base64 32)
echo -e "  ${GREEN}✓${NC} APP_KEY generated"

# Generate JWT_SECRET
JWT_SECRET=$(openssl rand -hex 32)
echo -e "  ${GREEN}✓${NC} JWT_SECRET generated"

# Copy .env.example to .env
echo ""
echo -e "${GREEN}Step 2: Creating .env file from template...${NC}"
cp .env.example .env

# Update .env with generated secrets
if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    sed -i '' "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" .env
    sed -i '' "s|^JWT_SECRET=.*|JWT_SECRET=$JWT_SECRET|" .env
else
    # Linux
    sed -i "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" .env
    sed -i "s|^JWT_SECRET=.*|JWT_SECRET=$JWT_SECRET|" .env
fi

echo -e "  ${GREEN}✓${NC} Secrets injected into .env"

echo ""
echo -e "${GREEN}Step 3: Reviewing configuration...${NC}"

# Check for required empty fields that need user input
EMPTY_FIELDS=""
if grep -q "^DB_PASSWORD=malnu_password_change_in_production" .env; then
    EMPTY_FIELDS="${EMPTY_FIELDS}DB_PASSWORD "
fi

if grep -q "^MAIL_USERNAME=your-username" .env; then
    EMPTY_FIELDS="${EMPTY_FIELDS}MAIL_USERNAME "
fi

if grep -q "^MAIL_PASSWORD=your-password" .env; then
    EMPTY_FIELDS="${EMPTY_FIELDS}MAIL_PASSWORD "
fi

if [ -n "$EMPTY_FIELDS" ]; then
    echo -e "${YELLOW}Warning: The following fields need your attention:${NC}"
    echo -e "${YELLOW}$EMPTY_FIELDS${NC}"
    echo ""
fi

echo ""
echo -e "${BLUE}========================================${NC}"
echo -e "${GREEN}✓ Setup completed successfully!${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo -e "  1. Review and update .env with your specific settings"
echo -e "  2. Ensure Docker services are running: docker-compose up -d"
echo -e "  3. Run database migrations: php artisan migrate"
echo -e "  4. Start the application: php artisan start"
echo ""
echo -e "${YELLOW}Important:${NC}"
echo -e "  - The generated secrets (APP_KEY, JWT_SECRET) are for development"
echo -e "  - For production, regenerate these secrets and update .env"
echo -e "  - Never commit .env file to version control"
echo ""
