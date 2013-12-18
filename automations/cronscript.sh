#!/bin/bash
#masterscript.sh resides in home_dir/csv/coursepicker

#get webpage
curl -o /tmp/staticReport.html https://apps.reg.uga.edu/reporting/staticReports

#remove old csv files 
rm -rfv /home/user/csv/coursepicker/csvfiles/*

#parse webpage for only csv files with course_offering in name and download the csv files
#need simple dom for this
/usr/local/php5/bin/php /home/user/csv/coursepicker/grabcsvfiles.php

cd /home/user/csvfiles
for i in `ls *.csv`; do
	echo '/home/user/csv/coursepicker/csvfiles/'$i; 
	
	#sort the csv files based on course prefix then course number then call number 
	sort -t\, -k 3,3d -k 4,4d -k 2,2n '/home/user/csv/coursepicker/csvfiles/'$i > '/home/user/csv/coursepicker/csvfiles/sorted_'$i
	
	#isolate just the course Prefix, course Number and course Name for slicing up for the typeahead.js json files
	cut -d',' -f3-5 '/home/user/csv/coursepicker/csvfiles/sorted_'$i | sort -u > '/home/user/csv/coursepicker/csvfiles/pre_tp_'$i
done
cd /home/user/

#generate the .json files needed for the autocomplete feature
/usr/local/php5/bin/php /home/user/csv/coursepicker/generateautocomplete.php

#use mysql load for rapidly loading the csv files into database
/usr/local/php5/bin/php /home/user/csv/coursepicker/loadcsvtodatabase.php
