#!/usr/bin/env bash
ulimit -n 1024000

n=10000
c=1000
target="http://127.0.0.1:5200/"

rc=5
total=0
echo "QPS testing in progress"
for ((i = 1; i <= ${rc}; ++i)); do
  qps=$(ab -k -n ${n} -c ${c} "$target" 2>&1 | grep 'Requests per second' | awk '{print $4}')
  echo "TEST#${i}: ${qps:-"-"}"
  total=$(awk "BEGIN{print ${total}+${qps:-0}}")
done
echo "AVG QPS:" $(awk "BEGIN{printf \"%.3f\", ${total}/${rc}}")
