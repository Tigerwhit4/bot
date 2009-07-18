#!/bin/sh

[ ! -f "$1" ] &&
	echo usage: $0 [file] >&2 &&
	exit 1

grep -v -- -- $1 | uniq |
sed -e "s/\ AUTO_INCREMENT=[0-9\ ]*//" |
tr "[:upper:]" "[:lower:]" > $1.tmp

mv $1.tmp $1
