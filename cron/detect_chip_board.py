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
print("             " +bc.SUB + "S M A R T   THERMOSTAT " + bc.ENDC)
print(bc.WARN +" ")
print("*********************************************************")
print("* Get CHIP and BOARD IDs using Adafruit Platform Detect *")
print("*     Build Date: 02/01/2021 Version 0.01               *")
print("*     Last Modified: 02/01/2021                         *")
print("*                                  Have Fun - PiHome.eu *")
print("*********************************************************")
print(" " + bc.ENDC)

try:
    import board
    from adafruit_platformdetect import Detector

    detector = Detector()
    print("Chip id: ", detector.chip.id)
    print("Board id: ", detector.board.id)
except:
    print("Board id: NONE")
