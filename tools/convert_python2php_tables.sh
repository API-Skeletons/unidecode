#!/bin/bash
# Convert unidecode python module table files for php

PYTHON_TABLE_DIR=$1
PHP_TABLE_DIR=$2

function usage() {
	ERROR="$1"
	EXITCODE=0
	[ -n "$ERROR" ] && echo "$ERROR\n" && EXITCODE=1
	echo "Usage: $0 [python tables directory] [php tables directory]"
	exit $EXITCODE
}

[ ! -d "$PYTHON_TABLE_DIR" ] && usage "Python table directory not provided or not found"
[ ! -d "$PHP_TABLE_DIR" ] && usage "PHP table directory not provided or not found"

for pyfile in $PYTHON_TABLE_DIR/x*.py
do
	table="$(basename "$pyfile"|sed "s/\.py$//")"
	phpfile="$PHP_TABLE_DIR/$table.php"
	cp "$pyfile" "$phpfile"
	sed -i "s/^data = ($/<?php\n\$GLOBALS['UNIDECODE_TABLE_$table'] = array (/" "$phpfile"
	sed -i 's/None,/null,/' "$phpfile"
	sed -i "s/^)$/);/" "$phpfile"
done
