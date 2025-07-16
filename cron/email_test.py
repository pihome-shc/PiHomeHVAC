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
print("            __  __                             _         ")
print("           |  \/  |                    /\     (_)        ")
print("           | \  / |   __ _  __  __    /  \     _   _ __  ")
print("           | |\/| |  / _` | \ \/ /   / /\ \   | | | '__| ")
print("           | |  | | | (_| |  >  <   / ____ \  | | | |    ")
print("           |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|    ")
print(" ")
print("                       " +bc.SUB + "S M A R T   THERMOSTAT " + bc.ENDC)
print("      ********************************************************")
print("      *           Script to send test Email messages         *")
print("      *                Build Date: 16/07/2025                *")
print("      *      Version 0.01 - Last Modified 16/07/2025         *")
print("      *                                 Have Fun - PiHome.eu *")
print("      ********************************************************")
print(" ")
print(" " + bc.ENDC)

import MySQLdb as mdb, datetime, sys, string
import configparser
import subprocess

# Import smtplib for the actual sending function
import smtplib

# Here are the email package modules we'll need
from email.mime.application import MIMEApplication
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText

sline = "-----------------------------------------------------------------------------"
# Initialise the database access varables
config = configparser.ConfigParser()
config.read('/var/www/st_inc/db_config.ini')
dbhost = config.get('db', 'hostname')
dbuser = config.get('db', 'dbusername')
dbpass = config.get('db', 'dbpassword')
dbname = config.get('db', 'dbname')

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
        SUBJECT = ""
        MESSAGE = "MaxAir EMail Test Message."
        send_status = results[name_to_index['status']]
    else:
        print("Error - No Email Account Found in Database.")
        sys.exit(1)

except mdb.Error as e:
    print("Error %d: %s" % (e.args[0], e.args[1]))
    sys.exit(1)
finally:
    if con:
        con.close()

print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - EMail Script Started")
print(sline)

# Send Email Message
if send_status:
    if len(MESSAGE) > 0:
        print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Sending Email Message")
        msg = MIMEMultipart()
        msg['Subject'] = 'MaxAir EMail Test'
        me = FROM
        to = [TO]
        Body = MESSAGE
        msg['From'] = me
        msg['To'] =  ', '.join(to)
        msg.preamble = 'System Status'
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
    else:
        print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - NO Email Message Sent")
else:
    print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Email Sending Disabled")
print(sline)

print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - EMail Test Script Ended \n")
