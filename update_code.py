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
print("* Compare installed code against GITHUB repository and *")
print("* allow update.                                        *")
print("*      Build Date: 26/02/2021                          *")
print("*      Version 0.01 - Last Modified 26/02/2021         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

import filecmp
import os, shutil

def yes_or_no(question):
    reply = str(input(bc.WARN + question+' (y/n): ' + bc.ENDC)).lower().strip()
    if reply[0] == 'y':
        return True
    if reply[0] == 'n':
        return False
    else:
        return yes_or_no("Uhhhh... please enter ")

def report_recursive(dcmp):
    global count

    for name in dcmp.diff_files:
        print("DIFF file %s found in %s and %s" % (name,
            dcmp.left, dcmp.right))
        count += 1
    for name in dcmp.left_only:
        print("MISSING file %s found in %s" % (name, dcmp.left))
        count += 1
    for sub_dcmp in dcmp.subdirs.values():
        report_recursive(sub_dcmp)

def replace_recursive(dcmp):
    global target_dir
    global source_dir

    for name in dcmp.diff_files:
        path = dcmp.left.replace(source_dir, target_dir)
        print(path,name)
        os.system('cp -f ' + dcmp.left + "/" + name + ' ' + path)
    for name in dcmp.left_only:
        path = dcmp.left.replace(source_dir, target_dir)
        print(path,name)
        os.system('cp -f ' + dcmp.left + "/" + name + ' ' + path)
    for sub_dcmp in dcmp.subdirs.values():
        replace_recursive(sub_dcmp)

print(bc.ylw + " ")
print("********************************************************")
print("                  Script Started                        ")
print("********************************************************")
print(" " + bc.ENDC)

source_dir = './temp_dir'
target_dir = '/var/www'
count = 0
c = filecmp.dircmp(source_dir, target_dir)
os.system('sudo git clone https://github.com/pihome-shc/PiHomeHVAC.git ' + source_dir) 
report_recursive(c)

if count > 0:
    if yes_or_no('\nOverwrite Files'):
        if yes_or_no('\nARE YOU REALY SURE'):
            replace_recursive(c)
else:
    print(bc.ylw + " ")
    print("********************************************************")
    print("              No Changed Files Found                    ")
    print("********************************************************")
    print(" " + bc.ENDC)

shutil.rmtree(source_dir)
print(bc.ylw + " ")
print("********************************************************")
print("                    Script Ended                        ")
print("********************************************************")
print(" " + bc.ENDC)
