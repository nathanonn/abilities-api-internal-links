#!/bin/bash
#
# Build script for {{PLUGIN_NAME}}
# Creates a distributable zip file for WordPress installation
#

set -e

# Configuration
PLUGIN_SLUG="{{PLUGIN_SLUG}}"
MAIN_FILE="{{MAIN_FILE}}"
PLUGIN_VERSION=$(grep -oP "Version:\s*\K[0-9.]+" "$MAIN_FILE" 2>/dev/null || echo "1.0.0")
BUILD_DIR="build"
DIST_DIR="dist"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Building ${PLUGIN_SLUG} v${PLUGIN_VERSION}${NC}"
echo "======================================"

# Get the script directory (plugin root)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Clean previous builds
echo -e "${YELLOW}Cleaning previous builds...${NC}"
rm -rf "$BUILD_DIR"
rm -rf "$DIST_DIR"

# Create build directories
mkdir -p "$BUILD_DIR/$PLUGIN_SLUG"
mkdir -p "$DIST_DIR"

# Files and directories to include
echo -e "${YELLOW}Copying plugin files...${NC}"

# Copy main plugin file
cp "$MAIN_FILE" "$BUILD_DIR/$PLUGIN_SLUG/"

# Copy source directory (if exists)
if [ -d "src" ]; then
    cp -r src "$BUILD_DIR/$PLUGIN_SLUG/"
fi

# Copy includes directory (if exists)
if [ -d "includes" ]; then
    cp -r includes "$BUILD_DIR/$PLUGIN_SLUG/"
fi

# Copy assets directory (if exists)
if [ -d "assets" ]; then
    cp -r assets "$BUILD_DIR/$PLUGIN_SLUG/"
fi

# Copy templates directory (if exists)
if [ -d "templates" ]; then
    cp -r templates "$BUILD_DIR/$PLUGIN_SLUG/"
fi

# Copy languages directory (if exists)
if [ -d "languages" ]; then
    cp -r languages "$BUILD_DIR/$PLUGIN_SLUG/"
fi

# Copy composer files (if exists)
if [ -f "composer.json" ]; then
    cp composer.json "$BUILD_DIR/$PLUGIN_SLUG/"
    if [ -f "composer.lock" ]; then
        cp composer.lock "$BUILD_DIR/$PLUGIN_SLUG/"
    fi
fi

# Copy README if it should be included in distribution
if [ -f "README.md" ]; then
    cp README.md "$BUILD_DIR/$PLUGIN_SLUG/"
fi

# Install production dependencies (if composer.json exists)
if [ -f "$BUILD_DIR/$PLUGIN_SLUG/composer.json" ]; then
    echo -e "${YELLOW}Installing production dependencies...${NC}"
    cd "$BUILD_DIR/$PLUGIN_SLUG"

    if command -v composer &> /dev/null; then
        composer install --no-dev --optimize-autoloader --no-interaction --quiet

        # Remove composer files after install (optional - keeps the zip cleaner)
        # Uncomment the following lines if you don't want composer files in the final build
        # rm composer.json
        # rm composer.lock
    else
        echo -e "${RED}Error: Composer is not installed. Please install Composer first.${NC}"
        exit 1
    fi

    cd "$SCRIPT_DIR"
fi

# Create the zip file
echo -e "${YELLOW}Creating zip archive...${NC}"
cd "$BUILD_DIR"
zip -rq "../$DIST_DIR/${PLUGIN_SLUG}-${PLUGIN_VERSION}.zip" "$PLUGIN_SLUG"
cd "$SCRIPT_DIR"

# Also create a latest version for convenience
cp "$DIST_DIR/${PLUGIN_SLUG}-${PLUGIN_VERSION}.zip" "$DIST_DIR/${PLUGIN_SLUG}-latest.zip"

# Cleanup build directory
echo -e "${YELLOW}Cleaning up...${NC}"
rm -rf "$BUILD_DIR"

# Output results
echo ""
echo -e "${GREEN}Build complete!${NC}"
echo "======================================"
echo -e "Output files:"
echo -e "  ${GREEN}$DIST_DIR/${PLUGIN_SLUG}-${PLUGIN_VERSION}.zip${NC}"
echo -e "  ${GREEN}$DIST_DIR/${PLUGIN_SLUG}-latest.zip${NC}"
echo ""
echo -e "File size: $(du -h "$DIST_DIR/${PLUGIN_SLUG}-${PLUGIN_VERSION}.zip" | cut -f1)"
