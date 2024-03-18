#!/bin/bash
SRC=~/bga/bga-fiverealms/src/ # with trailing slash
NAME=fiverealms

# Sass
sass "$NAME.scss" "$NAME.css"

# Copy
rsync $SRC/$NAME.css ~/bga/studio/$NAME/
rsync $SRC/$NAME.css.map ~/bga/studio/$NAME/
