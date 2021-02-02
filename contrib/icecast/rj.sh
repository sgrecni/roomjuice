#! /bin/bash

ICECAST='/usr/bin/icecast2'
DARKICE='/usr/local/bin/darkice'

ICECASTCONFIG='/home/icecast/icecast.xml'
DARKICECONFIG='/home/icecast/darkice.cfg'

if [ "`ps ax| grep $DARKICE | grep -v grep`" ]
then
	DARKICE_IS_RUNNING=1
else
	DARKICE_IS_RUNNING=0	
fi

if [ "`ps ax| grep $ICECAST | grep -v grep`" ]
then
	ICECAST_IS_RUNNING=1
else
	ICECAST_IS_RUNNING=0	
fi
 
case "$1" in
    start)
		if [ "${ICECAST_IS_RUNNING}" -eq "0" ]; then
			echo "starting $ICECAST"
			$ICECAST -c $ICECASTCONFIG &
			sleep 2
		fi
		if [ "${DARKICE_IS_RUNNING}" -eq "0" ]; then
			echo "starting $DARKICE"
			$DARKICE -c $DARKICECONFIG 1>/dev/null 2>&1 &
		fi
        ;;
    stop)
		if [ "${DARKICE_IS_RUNNING}" -eq "1" ]; then
			killall -TERM $DARKICE
			echo "killed $DARKICE"
		fi
		if [ "${ICECAST_IS_RUNNING}" -eq "1" ]; then
			killall -TERM $ICECAST
			echo "killed $ICECAST"
		fi
        ;;
	status)
		if [ "${DARKICE_IS_RUNNING}" -eq "1" ]; then
			echo "DARKICE is running!"
		else
			echo "DARKICE isn't running!"
		fi
		if [ "${ICECAST_IS_RUNNING}" -eq "1" ]; then
			echo "icecast is running!"
		else
			echo "icecast isn't running!"
		fi
		;;
    restart)
        $0 stop && $0 start || return=$rc_failed
        ;;
    *)
        echo "Usage: $0 {start|stop|status|restart}"
        exit 1
esac
exit 0
