#!/bin/bash

# KaelyAuth Release Script
# Usage: ./release.sh <version> <message>
# Example: ./release.sh v1.0.0 "Release v1.0.0 - Stable web authentication"

VERSION=$1
MESSAGE=$2

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if version and message are provided
if [ -z "$VERSION" ] || [ -z "$MESSAGE" ]; then
    echo -e "${RED}Error: Missing version or message${NC}"
    echo "Usage: ./release.sh <version> <message>"
    echo "Example: ./release.sh v1.0.0 'Release v1.0.0 - Stable web authentication'"
    exit 1
fi

echo -e "${BLUE}🚀 Starting release process for $VERSION...${NC}"

# Check if we're in a git repository
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo -e "${RED}Error: Not in a git repository${NC}"
    exit 1
fi

# Check if there are uncommitted changes
if ! git diff-index --quiet HEAD --; then
    echo -e "${YELLOW}Warning: You have uncommitted changes${NC}"
    echo "Please commit your changes before releasing"
    exit 1
fi

echo -e "${BLUE}📋 Removing old tag $VERSION...${NC}"

# Remove old tag locally
git tag -d $VERSION 2>/dev/null || echo "Tag $VERSION not found locally"

# Remove old tag from remote
git push origin :refs/tags/$VERSION 2>/dev/null || echo "Tag $VERSION not found on remote"

echo -e "${BLUE}🏷️  Creating new tag $VERSION...${NC}"

# Create new tag
git tag -a $VERSION -m "$MESSAGE"

echo -e "${BLUE}📤 Pushing tag to remote...${NC}"

# Push tag to remote
git push origin $VERSION

echo -e "${GREEN}✅ Release $VERSION completed successfully!${NC}"

# Show current tags
echo -e "${BLUE}📋 Current tags:${NC}"
git tag -l | tail -5

echo -e "${GREEN}🎉 Release process completed!${NC}"
echo -e "${YELLOW}💡 Don't forget to:${NC}"
echo "   - Update CHANGELOG.md if needed"
echo "   - Create GitHub release if applicable"
echo "   - Test the release in a clean environment" 