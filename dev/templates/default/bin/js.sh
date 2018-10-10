#!/usr/bin/env bash

#WARNING: This script MUST be executed ONLY from /bin/nucleate

# Copy JS sources
echo -e "${YELLOW}Copying Javascript files to temp directory...${NC}"
cp -R "$PARENT_PATH/$TEMPLATE_PATH/$TEMPLATE/js/" "$PARENT_PATH/$ASSETS_PATH/"
mkdir "$PARENT_PATH/$ASSETS_PATH/min.js"

cp "$PARENT_PATH/node_modules/jquery/dist/jquery.min.js" "$PARENT_PATH/$ASSETS_PATH/min.js/"
cp "$PARENT_PATH/node_modules/bootstrap/dist/js/bootstrap.min.js" "$PARENT_PATH/$ASSETS_PATH/min.js/"

cp "$PARENT_PATH/node_modules/bootstrap-notify/bootstrap-notify.min.js" "$PARENT_PATH/$ASSETS_PATH/min.js/"
cp "$PARENT_PATH/node_modules/moment/min/moment-with-locales.min.js" "$PARENT_PATH/$ASSETS_PATH/min.js/"
cp "$PARENT_PATH/node_modules/bootstrap-select/dist/js/bootstrap-select.js" "$PARENT_PATH/$ASSETS_PATH/js/"
cp -R "$PARENT_PATH/node_modules/bootstrap-select/dist/js/i18n/" "$PARENT_PATH/$ASSETS_PATH/min.js/"
cp "$PARENT_PATH/node_modules/jquery.cookie/jquery.cookie.js" "$PARENT_PATH/$ASSETS_PATH/js/"

# Minify
echo -e "${YELLOW}Minifying Javascript...${NC}"
#mv "$PARENT_PATH/$ASSETS_PATH/js/jquery.js" "$PARENT_PATH/$ASSETS_PATH/min.js/"
uglifyjs "$PARENT_PATH/$ASSETS_PATH/js/*" --compress --mangle -o "$PARENT_PATH/$ASSETS_PATH/min.js/bundle.min.js" --source-map "filename='$PARENT_PATH/$ASSETS_PATH/min.js/bundle.min.js.map'"

echo -e "${YELLOW}Sorting Javascript files...${NC}"
#mv "$PARENT_PATH/$ASSETS_PATH/js/" "$PARENT_PATH/$ASSETS_PATH/raw.js/"
rm -rf "$PARENT_PATH/$ASSETS_PATH/js/"
mv "$PARENT_PATH/$ASSETS_PATH/min.js/" "$PARENT_PATH/$ASSETS_PATH/js/"

echo -e "${GREEN}Javascript is ready to deploy!${NC}"