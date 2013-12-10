#!/bin/bash
for i in $(ls offerings/athens)
do
	echo $i;
	sort -t\, -k 3,3d -k 4,4d -k 2,2n "offerings/athens/$i" > "offerings/athens/sorted_$i";
done

for i in $(ls offerings/griffin)
do
	echo $i;
	sort -t\, -k 3,3d -k 4,4d -k 2,2n "offerings/griffin/$i" > "offerings/griffin/sorted_$i";
done

for i in $(ls offerings/gwinnett)
do
	echo $i;
	sort -t\, -k 3,3d -k 4,4d -k 2,2n "offerings/gwinnett/$i" > "offerings/gwinnett/sorted_$i";
done

for i in $(ls offerings/na)
do
	echo $i;
	sort -t\, -k 3,3d -k 4,4d -k 2,2n "offerings/na/$i" > "offerings/na/sorted_$i";
done

