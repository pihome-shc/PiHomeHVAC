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
    blu = '\033[36m'


print(bc.hed + " ")
print("    __  __                             _         ")
print("   |  \/  |                    /\     (_)        ")
print("   | \  / |   __ _  __  __    /  \     _   _ __  ")
print("   | |\/| |  / _` | \ \/ /   / /\ \   | | | '__| ")
print("   | |  | | | (_| |  >  <   / ____ \  | | | |    ")
print("   |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|    ")
print(" ")
print("             " +bc.SUB + "S M A R T   THERMOSTAT " + bc.ENDC)
print("********************************************************")
print("*       Script Auto Image System to a gz file and      *")
print("*         send Confirmation Email message.             *")
print("*                Build Date: 19/10/2022                *")
print("*      Version 0.03 - Last Modified 23/03/2024         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" ")
print(" " + bc.ENDC)

line_len = 70

import MySQLdb as mdb, datetime, sys, smtplib, string, os
import configparser
import subprocess, re
import glob

# Import smtplib for the actual sending function
import smtplib

# Here are the email package modules we'll need
from email.mime.application import MIMEApplication
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

# Initialise the database access varables
config = configparser.ConfigParser()
config.read('/var/www/st_inc/db_config.ini')
dbhost = config.get('db', 'hostname')
dbuser = config.get('db', 'dbusername')
dbpass = config.get('db', 'dbpassword')
dbname = config.get('db', 'dbname')

con = mdb.connect(dbhost, dbuser, dbpass, dbname)
cursorselect = con.cursor()
query = ("SELECT * FROM auto_image LIMIT 1;")
cursorselect.execute(query)
image_to_index = dict(
    (d[0], i)
    for i, d
    in enumerate(cursorselect.description)
)
ai_result = cursorselect.fetchone()
cursorselect.close()
if ai_result[image_to_index['enabled']] == 1:
    image_id = ai_result[image_to_index['id']]
    destination = ai_result[image_to_index['destination']]
    frequency = ai_result[image_to_index['frequency']]
    f = frequency.split(" ")
    if f[1] == "DAY" :
        if f[0] == "1":
            freq = int(f[0]) * 23 * 60 * 60
        else:
            freq = int(f[0]) * 24 * 60 * 60
    else :
        freq = int(f[0]) * 7 * 24 * 60 * 60

    rotation = ai_result[image_to_index['rotation']]
    r = rotation.split(" ")
    if r[1] == "DAY" :
        if r[0] == "1":
            rot = int(r[0]) * 23 * 60 * 60
        else:
            rot = int(r[0]) * 24 * 60 * 60
    else :
        rot = int(r[0]) * 7 * 24 * 60 * 60

    # Get datetime of last image creation
    last_image_creation = ai_result[image_to_index['last_image_creation']]

    if ai_result[image_to_index['email_confirmation']] == 1:
        # Create the container (outer) email message.
        try:
            con = mdb.connect(dbhost, dbuser, dbpass, dbname)
            cursorselect = con.cursor()
            query = ("SELECT * FROM email;")
            cursorselect.execute(query)
            name_to_index = dict(
                (d[0], i)
                for i, d
                in enumerate(cursorselect.description)
            )
            results = cursorselect.fetchone()
            cursorselect.close()
            if cursorselect.rowcount > 0:
                USER = results[name_to_index['username']]
                result = subprocess.run(
                    ['php', '/var/www/cron/email_passwd_decrypt.php'],         # program and arguments
                    stdout=subprocess.PIPE,                     # capture stdout
                    check=True                                  # raise exception if program fails
                )
                PASS = result.stdout.decode("utf-8").split()[0] # result.stdout contains a byte-string
                HOST = results[name_to_index['smtp']]
                PORT = results[name_to_index['port']]
                TO = results[name_to_index['to']]
                FROM = results[name_to_index['from']]
                SUBJECT = "MaxAir Database Backup"
                MESSAGE = ""
                send_status = results[name_to_index['status']]
            else:
                print("Error - No Email Account Found in Database.")
                sys.exit(1)

            cursorselect = con.cursor()
            query = ("SELECT backup_email FROM system;")
            cursorselect.execute(query)
            name_to_index = dict(
                (d[0], i)
                for i, d
                in enumerate(cursorselect.description)
            )
            results = cursorselect.fetchone()
            cursorselect.close()
            if cursorselect.rowcount > 0:
                TO = results[name_to_index['backup_email']]
            else:
                print("Error - No Backup Email Account Found in Database.")
                sys.exit(1)

        except mdb.Error as e:
            print("Error %d: %s" % (e.args[0], e.args[1]))
            sys.exit(1)
        finally:
            if con:
                con.close()

    print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Create System Image File Script Started")
    print("-" * line_len)

    # Check if image file is required
    timestamp = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    elapsed_time = datetime.datetime.strptime(timestamp, "%Y-%m-%d %H:%M:%S").timestamp() - datetime.datetime.strptime(str(last_image_creation), "%Y-%m-%d %H:%M:%S").timestamp()
    if elapsed_time >= freq:
        con = mdb.connect(dbhost, dbuser, dbpass, dbname)
        cursorupdate = con.cursor()
        print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Suspend Gateway and Controller Processing")
        print("-" * line_len)
        # put controller in test mode to suspend database activity
        cursorupdate.execute(
            "UPDATE `system` SET `test_mode`= 4;"
        )
        con.commit()
        # create flag file to suspend Gatewau processing
        running_flag = file = open('/tmp/db_cleanup_running', 'w+')
        print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Creating System Image File")
        print("-" * line_len)
        # Image file path
        imagefname = destination + dbname + "_" + datetime.datetime.now().strftime("%Y_%m_%d") + ".img";
        # Create the image file
        if os.path.exists('/etc/armbian-release'):
            imagefname = destination + dbname + "_" + datetime.datetime.now().strftime("%Y_%m_%d") + ".img";
            regex = re.compile('mmcblk.')
            result = subprocess.run(['lsblk'], stdout=subprocess.PIPE)
            disk_name = re.findall(regex, result.stdout.decode('utf-8'))[0]
            cmd = "sudo dcfldd bs=4M if=/dev/" + disk_name + " | gzip > " + imagefname + ".gz"
        else:
            cmd = "sudo /usr/local/bin/image-backup -i " + imagefname
        print(cmd)
        os.system(cmd)
        print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - System Image File Created")
        print("-" * line_len)
        print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Restore Gateway and Controller Processing")
        print("-" * line_len)

        # remove the flag file and clear test_mode
        if os.path.exists('/tmp/db_cleanup_running'):
            os.remove('/tmp/db_cleanup_running')
        cursorupdate.execute(
            "UPDATE `system` SET `test_mode`= 0;"
        )
        con.commit()

        # Record datetime of backup creation
        cursorupdate.execute(
            "UPDATE `auto_image` SET `last_image_creation`=%s, `sync`=0 WHERE `id` = %s",
            [timestamp, image_id],
        )
        con.commit()
        cursorupdate.close()
        con.close()

        #Check to see if confirmation email is to be sent
        if ai_result[image_to_index['email_confirmation']] == 1:
            print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Emailing Confrmation")
            print("-" * line_len)

            # Send Email Message
            msg = MIMEMultipart()
            msg['Subject'] = 'MaxAir System Image File Confirmation'
            me = FROM
            to = [TO]
            Body = 'System Image Created - ' + imagefname
            msg['From'] = me
            msg['To'] =  ', '.join(to)
            msg.preamble = 'System Image Created'
            Body = MIMEText(Body) # convert the body to a MIME compatible string
            msg.attach(Body) # attach it to your main message

            # Send the email via our own SMTP server.
            try:
                if PORT == 465 :
                    server = smtplib.SMTP_SSL(HOST, PORT)
                else :
                    server = smtplib.SMTP(HOST, PORT)
                #server.set_debuglevel(1)
                server.login(USER, PASS)
                server.sendmail(FROM, TO, msg.as_string())
                server.quit()
            except:
                print(bc.fail + (
                    datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - ERROR Sending Email Message")

            print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Email Sent")
            print("-" * line_len)
    else :
            print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - System Image File Creation Not Yet Scheduled")
            print("-" * line_len)

    print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Checking for Rotation Deletions")
    print("-" * line_len)
    list_of_files = glob.glob(destination + 'maxair_*.img')
    for f in list_of_files:
        c_time = os.path.getctime(f)
        dt_c = datetime.datetime.fromtimestamp(c_time)
        elapsed_time = datetime.datetime.now() - dt_c
        elapsed_time = elapsed_time.total_seconds()
        elapsed_time = datetime.datetime.now() - dt_c
        elapsed_time = elapsed_time.total_seconds()
        # Check if a backup file should be rotated is required
        if elapsed_time >= rot:
            print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Rotating System Image File")
            print("-" * line_len)
            cmd = 'rm -r ' + f
            os.system(cmd)
            print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " File - " + f + " Deleted")
            print("-" * line_len)

    print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Rotation Deletion Check Ended")
    print("-" * line_len)

    print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - System Image File Creation Script Ended \n")
    print("-" * line_len)
else :
    if con:
        con.close()
    print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - System Image File Creation Script NOT Enabled \n")
    print("-" * line_len)

