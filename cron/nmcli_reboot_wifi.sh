#!/bin/bash
#=================================================================
# Script Variables Settings
wlan='wlan0'

# Where and what you want to call the Lockfile
lockfile='/var/www/cron/reboot_wifi.pid'

#=================================================================
echo "           __  __                             _        "
echo "          |  \/  |                    /\     (_)       "
echo "          | \  / |   __ _  __  __    /  \     _   _ __ "
echo "          | |\/| |  / _' | \ \/ /   / /\ \   | | |  __|"
echo "          | |  | | | (_| |  >  <   / ____ \  | | | |   "
echo "          |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|   "
echo ""
echo "                S M A R T   T H E R M O S T A T "
echo "*************************************************************************"
echo "* MaxAir is LINUX  based Central Heating Control systems. It runs from  *"
echo "* a web interface and it comes with ABSOLUTELY NO WARRANTY, to the      *"
echo "* extent permitted by applicable law. I take no responsibility for any  *"
echo "* loss or damage to you or your property.                               *"
echo "* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTIL UNLESS YOU KNOW  *"
echo "* WHAT YOU ARE DOING                                                    *"
echo "*************************************************************************"
echo
echo "                                                       Have Fun - PiHome "
echo " - Auto Reconnect Wi-Fi Status for $wlan Script Started ";
echo "   $(date)"
echo
echo "*************************************************************************"
echo
# Check to see if there is a lock file
if [ -e $lockfile ]; then
    # A lockfile exists... Lets check to see if it is still valid
    pid=`cat $lockfile`
    # if kill -0 &>1 > /dev/null $pid; then
	if kill -0 &>1 $pid; then
        # Still Valid... lets let it be...
        echo "Process still running, Lockfile valid"
        exit 1
    else
        # Old Lockfile, Remove it
        echo "Old lockfile, Removing Lockfile"
        rm $lockfile
    fi
fi
# If we get here, set a lock file using our current PID#
echo "Setting Lockfile"
echo $$ > $lockfile

# We can perform check if wlan interface is disconnected
echo "Performing Network check for $wlan"
if nmcli device | grep $wlan | awk -v N=3 '{print $N}' | grep -q 'disconnected'; then
# If disconnected then reconnect
  nmcli device wifi connect WLAN-2.4G password Tr3ll3b0rg
fi

# Check is complete, Remove Lock file and exit
#echo "process is complete, removing lockfile"
rm $lockfile
echo "Reboot WiFi Script Ended"
date
exit 0

##################################################################
# End of Script
##################################################################
