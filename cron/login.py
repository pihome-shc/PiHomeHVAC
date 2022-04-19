#!/usr/bin/python
# add following line to show up when some one ssh to pi /etc/profile
# sudo python /var/www/cron/login.py
# clear everything from /etc/motd to remove generic message.
import socket, os, re, time, sys, subprocess, fcntl, struct
from threading import Thread
class bc:
	HEADER = '\033[0;36;40m'
	ENDC = '\033[0m'
	SUB = '\033[3;30;45m'
	WARN = '\033[0;31;40m'
	GREEN = '\033[0;32;40m'
	org = '\033[91m'
	hed = "\033[95m"
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
print("*      Version 0.12 - Last Modified 31/01/2022         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

df = subprocess.Popen(["df","-h"], stdout=subprocess.PIPE, stderr=subprocess.PIPE, universal_newlines=True)
output = df.communicate()[0]
device, size, used, available, percent, mountpoint = \
	output.split("\n")[1].split()
print(bc.org +"Disk/SD Card Usage" + bc.ENDC)
print("Filesystem  Size  Used   Avail  Used%")
print(device+"   "+size+"   "+used+"   "+available+"   "+percent)

def get_interface_ip(ifname):
	s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
	return socket.inet_ntoa(fcntl.ioctl(s.fileno(), 0x8915, struct.pack('256s', bytes(ifname[:15], 'utf-8')))[20:24])

def get_ip():
	ip = socket.gethostbyname(socket.gethostname())
	if ip.startswith("127."):
		interfaces = ["eth0","eth1","eth2","wlan0","wlan1","wifi0","ath0","ath1","ppp0"]
		for ifname in interfaces:
			try:
				ip = get_interface_ip(ifname)
				break
			except IOError:
				pass
	return ip
print ("WebServer:  "+bc.GREEN +"http://"+str(get_ip())+"/"+ bc.ENDC)
print ("PhpMyAdmin: "+bc.GREEN +"http://"+str(get_ip())+"/phpmyadmin"+ bc.ENDC)
