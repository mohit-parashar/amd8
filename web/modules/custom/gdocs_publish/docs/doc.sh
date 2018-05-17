#!/bin/bash
#
# Creates a new PDF of the AWS documentation including version number in filename

FILE=$1

PANDOC=$(command -v pandoc) || { echo >&2 "Cannot find pandoc"; exit 1; }

[ -e "$FILE" ] || { echo >&2 "Can't find file $FILE"; exit 1; }

VERSION=$(grep -x '^version: [0-9a-z\.-]*$' "$FILE" | sed 's/version: //')

[ -z "$VERSION" ] && { echo >&2 "No version found"; exit 1; }

OUTPUT=$(echo "$FILE" | sed 's/\.[^\.]*$//' | tr '[:upper:]' '[:lower:]' | tr ' ' '-')
OUTPUT+="-$VERSION.pdf"

pandoc \
-f markdown+inline_notes+definition_lists+mmd_title_block+auto_identifiers \
-s \
-H ~/.pandoc/standard.sty \
--template=harmonica \
-o  "$OUTPUT" \
"$FILE"

if [ "$?" -eq 0 ]
then
  echo "$OUTPUT"
  open "$OUTPUT"
else
  echo "Error creating documentation"
fi