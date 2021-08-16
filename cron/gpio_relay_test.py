#!/usr/bin/python3
class bc:
	hed = '\033[95m'
	dtm = '\033[0;36;40m'
	ENDC = '\033[0m'
	SUB = '\033[3;30;45m'
	WARN = '\033[0;31;40m'
	grn = '\033[0;32;40m'
	wht = '\033[0;37;40m'
	ylw = '\033[93m'
	fail = '\033[91m'
print(bc.hed + " ")
print("    __  __                             _         ")
print("   |  \/  |                    /\     (_)        ")
print("   | \  / |   __ _  __  __    /  \     _   _ __  ")
print("   | |\/| |  / _` | \ \/ /   / /\ \   | | | '__| ")
print("   | |  | | | (_| |  >  <   / ____ \  | | | |    ")
print("   |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|    ")
print(" ")
print("        " + bc.SUB + "S M A R T   T H E R M O S T A T " + bc.ENDC)
print(bc.WARN + " ")
print("********************************************************")
print("* MySensors Wifi/Ethernet/Serial Gateway Communication *")
print("* Script to communicate with MySensors Nodes, for more *")
print("* info please check MySensors API.                     *")
print("*      Build Date: 18/09/2017                          *")
print("*      Version 0.11 - Last Modified 28/07/2021         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

import os, subprocess
import time
import board
import digitalio
import sys
from Pin_Dict import pindict

def main():
    if sys.argv[1] in pindict:
        command = subprocess.Popen(['ps', '-ax'], stdout=subprocess.PIPE)
        output, error = command.communicate()

        target_process = "python3 /var/www/cron/gateway.py"
        for line in output.splitlines():
            if target_process in str(line):
                pid = int(line.split(None, 1)[0])
                os.kill(pid, 9)
                time.sleep(5)

        pin_num = pindict[sys.argv[1]] 
        print("Pin Number ",pin_num)
        relay = digitalio.DigitalInOut(getattr(board, pin_num))
        relay.direction = digitalio.Direction.OUTPUT
        if sys.argv[2] == '0' :
            relay.value = False
            print ("LOW")
        else :
            relay.value = True
            print ("HIGH")
        input("Press Enter to continue...")

if __name__ == '__main__':
    main()
