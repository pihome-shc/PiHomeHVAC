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
print("        " +bc.SUB + "S M A R T   T H E R M O S T A T " + bc.ENDC)
print(bc.WARN +" ")
print("********************************************************")
print("* Update files using source in code_updates directory, *")
print("* remove files from update directory, kill any jobs  . *")
print("* effected and update and update the 'systems' table   *")
print("* if the contents of db_config.ini has changed.        *")
print("*                                                      *")
print("*      Build Date: 04/09/2021                          *")
print("*      Version 0.02 - Last Modified 09/02/2024         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

import MySQLdb as mdb
import configparser
import os, time

print( "-" * 56)
print(bc.dtm + time.ctime() + bc.ENDC + ' - Move Files Script Started')
print( "-" * 56)

def getListOfFiles(dirName):
    # create a list of file and sub directories
    # names in the given directory
    listOfFile = os.listdir(dirName)
    allFiles = list()
    # Iterate over all the entries
    for entry in listOfFile:
        # Create full path
        fullPath = os.path.join(dirName, entry)
        # If entry is a directory then get the list of files in this directory
        if os.path.isdir(fullPath):
            allFiles = allFiles + getListOfFiles(fullPath)
        else:
            allFiles.append(fullPath)

    return allFiles

# directory which hold copies of the updated files
code_update_dir = '/var/www/code_updates'

# process the update directory
listOfFiles = getListOfFiles(code_update_dir)
if len(listOfFiles) > 1:
    for entry in listOfFiles:
        if not entry.endswith('updates.txt'):
            # check for any jobs files
            if entry.endswith('gateway.py'):
                gateway_found = True
            else:
                gateway_found = False
            if entry.endswith('gpio_ds18b20.py'):
                gpio_ds18b20_found = True
            else:
                gpio_ds18b20_found = False
            if entry.endswith('gpio_switch.py'):
                gpio_switch_found = True
            else:
                gpio_switch_found = False
            if entry.endswith('ebus.py'):
                ebus_found = True
            else:
                ebus_found = False
            if entry.endswith('controller.py'):
                controller_found = True
            else:
                controller_found = False
            if entry.endswith('jobs_schedule.py'):
                jobs_schedule_found = True
            else:
                jobs_schedule_found = False
    	# check if version or build have been updated
            if entry.endswith('db_config.ini'):
                # Initialise the database access variables
                config = configparser.ConfigParser()
                config.read("/var/www/code_updates/st_inc/db_config.ini")
                dbhost = config.get("db", "hostname")
                dbuser = config.get("db", "dbusername")
                dbpass = config.get("db", "dbpassword")
                dbname = config.get("db", "dbname")
                version = config.get("db", "version")
                build = config.get("db", "build")
                con = mdb.connect(dbhost, dbuser, dbpass, dbname)
                cur = con.cursor()
                cur.execute(
                "UPDATE system SET version = %s, build = %s",
                    (version, build),
                )
                con.commit()

            # make any missing sub-directories
            if '/' in entry[22:]:
                dirs = entry[22:].rsplit('/', 1)
                dirs = dirs[0]
                sub_dirs = dirs.split("/")
                update_path = '/var/www'
                for x in sub_dirs:
                    if not os.path.isdir(update_path + '/' + x):
                        cmd = 'mkdir ' + update_path + '/' + x
                        os.system(cmd)
                    update_path = update_path + '/' + x

            # copy the file to its correct location
            cmd = 'cp ' + entry + ' ' + entry.replace('/code_updates', '')
            os.system(cmd)
            # remove the file from the update directory
            cmd = 'rm -r ' + entry
            os.system(cmd)

    # remove any sub-directories from upgrade dir
    for it in os.scandir(code_update_dir):
        if it.is_dir():
            cmd = 'rm -R ' + it.path
            os.system(cmd)

    # kill any updated running jobs
    if gateway_found:
        cmd = 'sudo pkill -f gateway.py'
        os.system(cmd)
    if gpio_ds18b20_found:
        cmd = 'sudo pkill -f gpio_ds18b20.py'
        os.system(cmd)
    if gpio_switch_found:
        cmd = 'sudo pkill -f gpio_switch.py'
        os.system(cmd)
    if ebus_found:
        cmd = 'sudo pkill -f ebus.py'
        os.system(cmd)
    if controller_found:
        cmd = 'sudo pkill -f controller.py'
        os.system(cmd)
    if jobs_schedule_found:
        cmd = 'sudo pkill -f jobs_schedule.py'
        os.system(cmd)
else:
    print(bc.dtm + time.ctime() + bc.ENDC + ' - No Files to Move')

print( "-" * 56)
print(bc.dtm + time.ctime() + bc.ENDC + ' - Move Files Script Ended')
print( "-" * 56)
