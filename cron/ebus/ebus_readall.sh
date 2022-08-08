#!/bin/bash
i=0
echo "Reading all values from id $1."
sleep 3
stdbuf -oL ebusctl find -c $1 | awk '{print $2}' > output.txt
awk 'NF' output.txt > o.txt
file='o.txt'

while IFS= read -r line
do
((i=i+1))
echo "$i: $line:"
ebusctl read "$line" < /dev/null
done < "$file"
echo "Values readed. Ebusctl find will be run in 3 secs."
sleep 3
ebusctl find -c $1
