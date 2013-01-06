if [ -n "$3" ]
then
	year=`date +'%Y'`
	month=`date +'%m' | sed 's/^0*//'`
	day=`date +'%d' | sed 's/^0*//'`
	for i in 1 11 21 31
	do 
		if [ $day -lt $i ]
		then
			dayDir=`expr ${i} - 10`
			break
		fi	
	done
	moveDir=$year"/"$month"/"$dayDir"day"
	if [ ! -d $2$moveDir ] 
	then
		mkdir -p $2$moveDir
	fi
	if [ -d $1 ]
	then 
		orgin=$1"/"
		dest=$2$moveDir"/"$3"/"
	elif [ -f $1 ] 
	then
		orgin=$1
		mkdir $2$moveDir"/"$3
		dest=$2$moveDir"/"$3"/"$3"."$4
	fi	
else
	moveDir=""
	orgin=$1
	if [ -d $1 ]
	then
		orgin=$1"/*"
	fi
	dest=$2
fi
mv $orgin $dest
echo $moveDir


