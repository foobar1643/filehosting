#!/bin/bash
CURRENT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
STRINGS_FILENAME="translation-strings.po"
EXTRACT_TPL_STRINGS="$CURRENT_DIR/template-extractor.php"
GENERATE_PO_FILE="find $CURRENT_DIR/../../ -path $CURRENT_DIR/../../vendor -prune -o -iname '*.php' -print | xargs xgettext --from-code=UTF-8 --language=PHP --add-comments=notes -o $CURRENT_DIR/../../locale/$STRINGS_FILENAME"
REMOVE_CACHE_DIR="rm -rf $CURRENT_DIR/../../translation-cache"

eval $EXTRACT_TPL_STRINGS
eval $GENERATE_PO_FILE
eval $REMOVE_CACHE_DIR
echo "Translation stings generated successfully."