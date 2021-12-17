#!/usr/bin/python3
import subprocess
import sys
import time, os, fnmatch, MySQLdb as mdb, logging
from datetime import datetime
import configparser
class bc:
	hed = '\033[0;36;40m'
	dtm = '\033[0;36;40m'
	ENDC = '\033[0m'
	SUB = '\033[3;30;45m'
	WARN = '\033[0;31;40m'
	grn = '\033[0;32;40m'
	wht = '\033[0;37;40m'
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
print("*       Background Installer for Add_On software       *")
print("*                                                      *")
print("*                                                      *")
print("*      Build Date: 03/03/2021                          *")
print("*      Version 0.01 - Last Modified 03/04/2021         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

logging.basicConfig(filename='/var/www/cron/logs/INSTALLER_error.log', level=logging.DEBUG, format='%(asctime)s %(levelname)s %(name)s %(message)s')
logger=logging.getLogger(__name__)

# Initialise the database access varables
config = configparser.ConfigParser()
config.read('/var/www/st_inc/db_config.ini')
dbhost = config.get('db', 'hostname')
dbuser = config.get('db', 'dbusername')
dbpass = config.get('db', 'dbpassword')
dbname = config.get('db', 'dbname')

def is_running(pid):
    if os.path.isdir('/proc/{}'.format(pid)):
        return True
    return False

print(bc.dtm + time.ctime() + bc.ENDC + ' - Software Install Script Started')
print( "-" * 68)

try:
	con = mdb.connect(dbhost, dbuser, dbpass, dbname);
	cur = con.cursor()
	cur.execute('SELECT * FROM `sw_install` WHERE `pid` IS NULL ORDER BY `id` DESC LIMIT 1;')
	row = cur.fetchone()
	if cur.rowcount > 0:
		sw_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
		id = row[sw_to_index["id"]]
		script = row[sw_to_index["script"]]
		pid = row[sw_to_index["pid"]]
		if pid is None:
			print("Starting Execution of script: ", script)
			command = '/bin/bash ' + script
			process = subprocess.Popen(command + ' > /var/www/cron/sw_install.txt &', shell=True)
			running_pid = process.pid + 1
			print("PID: ", running_pid)
			cur.execute('UPDATE `sw_install` SET `start_datetime`=now(),`pid` = %s WHERE id = %s', [running_pid, id])
			con.commit()
			while is_running(running_pid) :
				pass
			time.sleep(10)
			timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
			cur.execute('UPDATE `sw_install` SET `stop_datetime`=now() WHERE id = %s', [timestamp, id])
			con.commit()
			con.close()
			print("Finished Executing script: ", script)
		else:
			print("Executing script: ", script)
	else:
		print('Nothing to Install')

except mdb.Error as e:
	logger.error(e)
	print(bc.dtm + time.ctime() + bc.ENDC + ' - DB Connection Closed: %s' % e)

print(bc.dtm + time.ctime() + bc.ENDC + ' - Software Install Script Ended')
print( "-" * 68)

