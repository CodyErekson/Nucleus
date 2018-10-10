#!/usr/bin/env bash

#WARNING: This script MUST be executed ONLY from /bin/nucleate

# Copy CSS sources
echo -e "${YELLOW}Copying CSS files to temp directory...${NC}"
cp -R "$PARENT_PATH/$TEMPLATE_PATH/$TEMPLATE/css/" "$PARENT_PATH/$ASSETS_PATH/"

# Copy CSS from modules
cp "$PARENT_PATH/node_modules/bootstrap/dist/css/bootstrap.css" "$PARENT_PATH/$ASSETS_PATH/css/"
cp "$PARENT_PATH/node_modules/bootstrap-select/dist/css/bootstrap-select.css" "$PARENT_PATH/$ASSETS_PATH/css/"

# Prefix CSS
echo -e "${YELLOW}Prefixing CSS for browser compatibility...${NC}"
postcss -r "$PARENT_PATH/$ASSETS_PATH/css/*.css" --use autoprefixer --autoprefixer.browsers 'last 2 versions'

# Stitch into a single file
echo -e "${YELLOW}Stitching CSS into a single file...${NC}"
find "$PARENT_PATH/$ASSETS_PATH/css/" -iname *.css ! -name style.all.css -print0 | sort -z | xargs -0 cat > "$PARENT_PATH/$ASSETS_PATH/css/style.all.css"

# Minify
echo -e "${YELLOW}Minifying CSS...${NC}"
cssmin "$PARENT_PATH/$ASSETS_PATH/css/style.all.css" > "$PARENT_PATH/$ASSETS_PATH/style.min.css"
find "$PARENT_PATH/$ASSETS_PATH/css" -type f -print0| xargs -0 rm
mv "$PARENT_PATH/$ASSETS_PATH/style.min.css" "$PARENT_PATH/$ASSETS_PATH/css/style.min.css"

echo -e "${GREEN}CSS is ready to deploy!${NC}"