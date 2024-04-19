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
print("*    Script Auto Backup Database to a gz file and      *")
print("*                send as an Email message.             *")
print("*                Build Date: 07/06/2022                *")
print("*      Version 0.04 - Last Modified 27/06/2022         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" ")
print(" " + bc.ENDC)

line_len = 70

import MySQLdb as mdb, datetime, sys, smtplib, string, os
import configparser
import subprocess
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
query = ("SELECT * FROM auto_backup LIMIT 1;")
cursorselect.execute(query)
backup_to_index = dict(
    (d[0], i)
    for i, d
    in enumerate(cursorselect.description)
)
ab_result = cursorselect.fetchone()
cursorselect.close()
if ab_result[backup_to_index['enabled']] == 1:
    backup_id = ab_result[backup_to_index['id']]
    destination = ab_result[backup_to_index['destination']]
    frequency = ab_result[backup_to_index['frequency']]
    f = frequency.split(" ")
    if f[1] == "DAY" :
        if f[0] == "1":
            freq = int(f[0]) * 23 * 60 * 60
        else:
            freq = int(f[0]) * 24 * 60 * 60
    else :
        freq = int(f[0]) * 7 * 24 * 60 * 60

    rotation = ab_result[backup_to_index['rotation']]
    r = rotation.split(" ")
    if r[1] == "DAY" :
        if r[0] == "1":
            rot = int(r[0]) * 23 * 60 * 60
        else:
            rot = int(r[0]) * 24 * 60 * 60
    else :
        rot = int(r[0]) * 7 * 24 * 60 * 60

    # Get datetime of last backup
    last_backup = ab_result[backup_to_index['last_backup']]

    if ab_result[backup_to_index['email_backup']] == 1 or ab_result[backup_to_index['email_confirmation']] == 1:
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

    print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Database Backup Script Started")
    print("-" * line_len)

    # Check if new backup is required
    timestamp = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    elapsed_time = datetime.datetime.strptime(timestamp, "%Y-%m-%d %H:%M:%S").timestamp() - datetime.datetime.strptime(str(last_backup), "%Y-%m-%d %H:%M:%S").timestamp()
    if elapsed_time >= freq:
        print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Creating Database Backup SQL File")
        print("-" * line_len)
        # Temporary file storage path
        tempPath = "./"
        # Backup file path
        dumpfname = dbname + "_" + datetime.datetime.now().strftime("%Y-%m-%d_%H:%M:%S") + ".sql";
        tempfname = tempPath + dumpfname
        # Create the backup
        cmd = "mysqldump --ignore-table=" + dbname + ".backup --add-drop-table --host=" + dbhost +" --user=" + dbuser + " --password=" + dbpass + " " + dbname + " > " + tempfname
        os.system(cmd)
        print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Database Backup SQL File Created")
        print("-" * line_len)

        print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Creating ZIP Archive of SQL File")
        print("-" * line_len)
        # Record datetime of backup creation
        con = mdb.connect(dbhost, dbuser, dbpass, dbname)
        cursorupdate = con.cursor()
        cursorupdate.execute(
            "UPDATE `auto_backup` SET `last_backup`=%s, `sync`=0 WHERE `id` = %s",
            [timestamp, backup_id],
        )
        con.commit()
        cursorupdate.close()
        con.close()
        # Create a local copy of the backup
        zipfname = tempfname + ".gz"
        cmd = "gzip " + tempfname
        os.system(cmd)

        print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - ZIP Archive Created")
        print("-" * line_len)

        # Check if sent email copy is enabled, if so use local copy as the source
        if ab_result[backup_to_index['email_backup']] == 1:
            print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Emailing the ZIP Archive File")
            print("-" * line_len)

            # Send Email Message
            msg = MIMEMultipart()
            msg['Subject'] = 'MaxAir Database Backup'
            me = FROM
            to = [TO]
            Body = 'Database Backup.'
            msg['From'] = me
            msg['To'] =  ', '.join(to)
            msg.preamble = 'Database Backup'
            Body = MIMEText(Body) # convert the body to a MIME compatible string
            msg.attach(Body) # attach it to your main message
            # add local zip file as an attachment
            with open(zipfname,'rb') as file:
                msg.attach(MIMEApplication(file.read(), Name=zipfname))

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
        #Check to see if confirmation email is to be sent
        if ab_result[backup_to_index['email_confirmation']] == 1:
            print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Emailing Backup Confrmation")
            print("-" * line_len)

            # Send Email Message
            msg = MIMEMultipart()
            msg['Subject'] = 'MaxAir Database Backup Confirmation'
            me = FROM
            to = [TO]
            Body = 'Database Backup Created - ' + destination + dumpfname + ".gz"
            msg['From'] = me
            msg['To'] =  ', '.join(to)
            msg.preamble = 'Database Backup Created'
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
        # Copy the local archive copy to the destination
        print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Copying Archive File to Destination Folder")
        print("-" * line_len)
        cmd = "cp " + zipfname  + " " + destination
        os.system(cmd)
        print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Copied to - " + destination)
        print("-" * line_len)
        # Delete the local archive copy
        print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Deleting Local Archive File")
        print("-" * line_len)
        cmd = "rm -f ./" + zipfname
        os.system(cmd)
        print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Deleted - " + zipfname)
        print("-" * line_len)
    else :
            print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Backup Not Yet Scheduled")
            print("-" * line_len)

    print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Checking for Rotation Deletions")
    print("-" * line_len)
    list_of_files = glob.glob(destination + 'maxair_*.gz')
    for f in list_of_files:
        c_time = os.path.getctime(f)
        dt_c = datetime.datetime.fromtimestamp(c_time)
        elapsed_time = datetime.datetime.now() - dt_c
        elapsed_time = elapsed_time.total_seconds()
        elapsed_time = datetime.datetime.now() - dt_c
        elapsed_time = elapsed_time.total_seconds()
        # Check if a backup file should be rotated is required
        if elapsed_time >= rot:
            print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Rotating Database Backup SQL File")
            print("-" * line_len)
            cmd = 'rm -r ' + f
            os.system(cmd)
            print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " File - " + f + " Deleted")
            print("-" * line_len)

    print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Rotation Deletion Check Ended")
    print("-" * line_len)

    print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Database Backup Script Ended \n")
    print("-" * line_len)
else :
    if con:
        con.close()
    print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Database Backup Script NOT Enabled \n")
    print("-" * line_len)

