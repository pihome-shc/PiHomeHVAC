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
print("* download any new or changed files, for later update. *")
print("*                                                      *")
print("*      Build Date: 02/08/2021                          *")
print("*      Version 0.03 - Last Modified 06/09/2021         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

import os, time, fnmatch, filecmp

def report_recursive(dcmp):
    global target_dir
    global source_dir
    global code_update_dir
    global database_update_dir

    # update existing code modules
    for name in dcmp.diff_files:
        copy_dir = False
        if name.endswith('.json') or name.endswith('.log'):
            continue
        if len(dcmp.left[18:]) > 0:
            if dcmp.left[18:].endswith('database_updates'):
                 update_path = db_update_dir
                 path = target_dir + '/' + dcmp.left[18:] + '/' + name
            else:
                sub_dirs = dcmp.left[18:].split("/")
                update_path = code_update_dir
                for x in sub_dirs:
                    update_path = update_path + '/' + x
                    if not os.path.isdir(update_path):
                        cmd = 'mkdir ' + update_path
                        os.system(cmd)
                path = target_dir + '/' + dcmp.left[18:] + '/' + name
                if os.path.isdir(source_dir + '/' + dcmp.left[18:] + '/' + name):
                    cmd = 'mkdir ' + code_update_dir + '/' + dcmp.left[18:] + '/' + name
                    os.system(cmd)
                    copy_dir = True
        else:
            update_path = code_update_dir
            path = target_dir + '/' + name
            if os.path.isdir(source_dir + '/' + name):
                copy_dir = True
        if copy_dir :
            cmd = 'cp -R ' + dcmp.left + '/' + name + ' ' + update_path
        else :
            cmd = 'cp ' + dcmp.left + '/' + name + ' ' + update_path
        os.system(cmd)
        print(path)

    # add new code modules
    for name in dcmp.left_only:
        copy_dir = False
        if name.endswith('.json') or name.endswith('.log'):
            continue
        if len(dcmp.left[18:]) > 0:
            if dcmp.left[18:].endswith('database_updates'):
                 update_path = db_update_dir
                 path = target_dir + '/' + dcmp.left[18:] + '/' + name
            else:
                sub_dirs = dcmp.left[18:].split("/")
                update_path = code_update_dir
                for x in sub_dirs:
                    update_path = update_path + '/' + x
                    if not os.path.isdir(update_path):
                        cmd = 'mkdir ' + update_path
                        os.system(cmd)
                path = target_dir + '/' + dcmp.left[18:] + '/' + name
                if os.path.isdir(source_dir + '/' + dcmp.left[18:] + '/' + name):
                    cmd = 'mkdir ' + code_update_dir + '/' + dcmp.left[18:] + '/' + name
                    os.system(cmd)
                    copy_dir = True
        else:
            update_path = code_update_dir
            path = target_dir + '/' + name
            if os.path.isdir(source_dir + '/' + name):
                copy_dir = True
        if copy_dir :
            cmd = 'cp -R ' + dcmp.left + '/' + name + ' ' + update_path
        else :
            cmd = 'cp ' + dcmp.left + '/' + name + ' ' + update_path
        os.system(cmd)
        print(path)

    for sub_dcmp in dcmp.subdirs.values():
        report_recursive(sub_dcmp)

print( "-" * 56)
print(bc.dtm + time.ctime() + bc.ENDC + ' - Code Update Script Started')
print( "-" * 56)

source_dir = '/var/www/temp_dir'
target_dir = '/var/www'
code_update_dir = '/var/www/code_updates'
db_update_dir = '/var/www/database_updates'
repository = 'https://github.com/pihome-shc/PiHomeHVAC.git'

# remove any sub-directories and content from upgrade dir
for it in os.scandir(code_update_dir):
    if it.is_dir():
        cmd = 'rm -R ' + it.path
        os.system(cmd)

# remove all files except place holder file from code upgrade dir
pattern = '*.*'
listOfFiles = os.listdir(code_update_dir)
for entry in listOfFiles:
    if fnmatch.fnmatch(entry, pattern):
        if not entry.startswith('updates.txt'):
            cmd = 'rm ' + code_update_dir + '/' + entry
            os.system(cmd)

# remove all files except place holder file from database upgrade dir
listOfFiles = os.listdir(db_update_dir)
for entry in listOfFiles:
    if fnmatch.fnmatch(entry, pattern):
        if not entry.startswith('updates.txt'):
            cmd = 'rm ' + db_update_dir + '/' + entry
            os.system(cmd)

# download current repository to a tempory directory ready for compare
os.system('git clone ' + repository + ' ' + source_dir)

# do recursive comparison and store copies of any changed or new code modules
c = filecmp.dircmp(source_dir, target_dir)
report_recursive(c)

# remove temporary copy of downloaded repository
cmd = 'rm -R ' + source_dir
os.system(cmd)
print( "-" * 56)
print(bc.dtm + time.ctime() + bc.ENDC + ' - Code Update Script Ended')
print( "-" * 56)

