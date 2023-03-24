#!/usr/bin/python3
import MySQLdb as mdb
import configparser
import subprocess

# Initialise the database access variables
config = configparser.ConfigParser()
config.read("/var/www/st_inc/db_config.ini")
dbhost = config.get("db", "hostname")
dbuser = config.get("db", "dbusername")
dbpass = config.get("db", "dbpassword")
dbname = config.get("db", "dbname")

con = mdb.connect(dbhost, dbuser, dbpass, dbname)
cur = con.cursor()

# initialise system variables
cur.execute("SELECT shutdown, reboot FROM system LIMIT 1")
row = cur.fetchone()
system_to_index = dict((d[0], i) for i, d in enumerate(cur.description))

if row[system_to_index['reboot']] == 1:
    cur.execute(
        'UPDATE system SET reboot = 0;'
    )
    con.commit()

    command = "/usr/bin/sudo /sbin/reboot"
    process = subprocess.Popen(command.split(), stdout=subprocess.PIPE)
    output = process.communicate()[0]
    print(output)

if row[system_to_index['shutdown']] == 1:
    cur.execute(
        'UPDATE system SET shutdown = 0;'
    )
    con.commit()

    command = "/usr/bin/sudo /sbin/shutdown -h now"
    process = subprocess.Popen(command.split(), stdout=subprocess.PIPE)
    output = process.communicate()[0]
    print(output)
