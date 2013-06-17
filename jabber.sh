#! /usr/bin/env bash

cd $(dirname $0)

while true; do
  ./jabber.php >>logs/output.log 2>&1
  sleep 5
  killall -9 jabber.php
done
