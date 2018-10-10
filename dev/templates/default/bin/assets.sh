#!/usr/bin/env bash

#WARNING: This script MUST be executed ONLY from /bin/nucleate

mkdir -p "$PARENT_PATH/$ASSETS_PATH"
rm -rf "$PARENT_PATH/$ASSETS_PATH/fonts/"
rm -rf "$PARENT_PATH/$ASSETS_PATH/img/"
echo -e "${YELLOW}Copying font files and images to temp directory...${NC}"

cp -R "$PARENT_PATH/$TEMPLATE_PATH/$TEMPLATE/fonts/" "$PARENT_PATH/$ASSETS_PATH/"
cp -R "$PARENT_PATH/$TEMPLATE_PATH/$TEMPLATE/img/" "$PARENT_PATH/$ASSETS_PATH/"

# Copy from modules
cp -R "$PARENT_PATH/node_modules/bootstrap/dist/fonts/" "$PARENT_PATH/$ASSETS_PATH/"

echo -e "${GREEN}Fonts and images are ready to deploy!${NC}"