#!/bin/bash
T2="n-2"
if [ "$T2" = "n-2" ]; then
 egrep "n-1|n-3" node.1.data.txt
else
   echo no near nodes
fi
