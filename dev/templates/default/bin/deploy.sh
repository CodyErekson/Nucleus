#!/usr/bin/env bash

#WARNING: This script MUST be executed ONLY from /bin/nucleate

if [ ! -d "$PARENT_PATH/$ASSETS_PATH/" ]; then
	echo -e "${RED}Temporary directory does not exist!${NC}"
	exit 1
fi

echo -e "${YELLOW}Removing existing directories in document root...${NC}"
find "$PARENT_PATH/$DOC_ROOT/" -maxdepth 1 -mindepth 1 -type d -exec rm -rf '{}' \;

echo -e "${YELLOW}Moving prepared assets to document root...${NC}"
find "$PARENT_PATH/$ASSETS_PATH/" -maxdepth 1 -mindepth 1 -type d -exec cp -R {} "$PARENT_PATH/$DOC_ROOT/" \;

echo -e "${GREEN}Asset deployment is complete!${NC}"