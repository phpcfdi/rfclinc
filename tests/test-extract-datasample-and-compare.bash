#!/bin/bash

TESTPATH="$(dirname $0)"
INFILE="$TESTPATH/assets/datasample-signed.txt.gz"
EXPECTEDFILE="$TESTPATH/assets/datasample-contents.txt"
OUTFILE="$(mktemp --dry-run)"

/bin/gunzip --stdout "$INFILE" \
    | /usr/bin/openssl smime -verify -in - -inform der -noverify 2> /dev/null \
    | /usr/bin/iconv --from iso8859-1 --to utf-8 \
    | /bin/sed 's/\r$//' \
    | tail -n 102 \
    > "$OUTFILE"

diff -u "$OUTFILE" "$EXPECTEDFILE"
if [ 0 -ne $? ]; then
    echo "The file $OUTFILE does not match with $EXPECTEDFILE" 1>&2
    exit 1
fi

echo "$OUTFILE and $EXPECTEDFILE are identical"
exit 0
