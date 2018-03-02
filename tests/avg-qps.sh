#!/usr/bin/env bash

n=1000
c=200
target="http://example.org/"

rc=3
total=0
for ((i=1; i<=${rc}; ++i))
do
    ab=$(ab -k -n ${n} -c ${c} "$target" 2>&1|grep 'Requests per second'|awk '{print $4}')
    echo ${i}"-QPS: "${ab}
    total=$(echo "${total}+${ab}"|bc)
    #echo ${i}"-Total QPS:" ${total}
done
echo "AVG QPS:" $(echo "scale=2;${total}/${rc}"|bc)