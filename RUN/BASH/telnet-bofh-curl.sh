#!/bin/bash

curl -s http://pages.cs.wisc.edu/~ballard/bofh/bofhserver.pl |grep 'is:' |awk 'BEGIN { FS=">"; } { print $10; }'
