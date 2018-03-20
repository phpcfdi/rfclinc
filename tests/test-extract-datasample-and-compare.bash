#!/bin/bash

# dependencies
GUNZIP="$(which gunzip)"
OPENSSL="$(which openssl)"
ICONV="$(which iconv)"
SED="$(which sed)"
TAIL="$(which tail)"
DIFF="$(which diff)"

TESTPATH="$(dirname "$0")"
INFILE="$TESTPATH/assets/datasample-signed.txt.gz"
UNCOMPRESSED="$(mktemp --dry-run)"
UNPACKED="$(mktemp --dry-run)"
ENCODED="$(mktemp --dry-run)"
CLEANED="$(mktemp --dry-run)"

EXPECTED="$TESTPATH/assets/datasample-contents.txt"

cat "$INFILE" | "$GUNZIP" --stdout > "$UNCOMPRESSED"
if [ 0 -ne $? ]; then
    echo "Error while gunzip" 1>&2
    exit 1
fi

cat "$UNCOMPRESSED" | "$OPENSSL" smime -verify -in - -inform der -noverify > "$UNPACKED"
if [ 0 -ne $? ]; then
    echo "Error while openssl smime" 1>&2
    exit 1
fi

cat "$UNPACKED" | "$ICONV" --from iso8859-1 --to utf-8 > "$ENCODED"
if [ 0 -ne $? ]; then
    echo "Error while iconv" 1>&2
    exit 1
fi

cat "$ENCODED" | "$SED" 's/\r$//' > "$CLEANED"
if [ 0 -ne $? ]; then
    echo "Error while sed" 1>&2
    exit 1
fi

"$SED" -i '1,2d' "$CLEANED"
diff "$CLEANED" "$EXPECTED" > /dev/null
if [ 0 -ne $? ]; then
    echo "The file $CLEANED does not match with $EXPECTED (step by step)" 1>&2
    exit 1
fi


OUTFILE="$(mktemp --dry-run)"
"$GUNZIP" --stdout "$INFILE" \
    | "$OPENSSL" smime -verify -in - -inform der -noverify 2> /dev/null \
    | "$ICONV" --from iso8859-1 --to utf-8 \
    | "$SED" 's/\r$//' \
    > "$OUTFILE"

"$SED" -i '1,2d' "$OUTFILE"
"$DIFF" "$OUTFILE" "$EXPECTED" > /dev/null
if [ 0 -ne $? ]; then
    echo "The file $OUTFILE does not match with $EXPECTED (in one line)" 1>&2
    exit 1
fi

echo "OK"
exit 0
