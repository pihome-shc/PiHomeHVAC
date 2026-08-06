#!/usr/bin/env python
class bc:
    hed = "\033[95m"
    dtm = "\033[0;36;40m"
    ENDC = "\033[0m"
    SUB = "\033[3;30;45m"
    WARN = "\033[0;31;40m"
    grn = "\033[0;32;40m"
    wht = "\033[0;37;40m"
    ylw = "\033[93m"
    fail = "\033[91m"
    red = "\033[41m"
    red_txt = "\033[31m"
    blu = "\033[44m"

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
print("*            Boiler Reset Control  Script              *")
print("*                                                      *")
print("*               Build Date: 18/02/2025                 *")
print("*       Version 0.02 - Last Modified 02/08/2026        *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

import time
import datetime
import string
import os
import sys
import socket
import fcntl
import struct
import subprocess
import serial
import urllib.request, urllib.parse, urllib.error                                       # URL functions
import urllib.request, urllib.error, urllib.parse                                       # URL functions
import smtplib
#from w1thermsensor import W1ThermSensor
import platform
import busio
import digitalio
import board
import schedule
import MySQLdb as mdb

try:
    from configparser import ConfigParser
except ImportError:
    from configparser import ConfigParser

line_len = 55; #length of seperator lines
## Write raw messages to the EBus
print(bc.dtm + datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Boiler Reset Script Started")
print("-" * line_len)

# Initialise the database access variables
config = ConfigParser()
config.read("/var/www/st_inc/db_config.ini")
dbhost = config.get("db", "hostname")
dbuser = config.get("db", "dbusername")
dbpass = config.get("db", "dbpassword")
dbname = config.get("db", "dbname")

# Initialise  database connection string
# **************************************
con = mdb.connect(dbhost, dbuser, dbpass, dbname)
cur = con.cursor()

# Create the container (outer) email message.
USER    = 'boiler@overkillsystems.com'
PASS    = 'Tr3!!3b0rg'
HOST    = 'smtp.livemail.co.uk'
SUBJECT = "HX15 Boiler Status"
TO      = "terry.adams@overkillsystems.com"
FROM    = "boiler@overkillsystems.com"

# Initialise variables
# ********************
MAX_RELIGHTS = 10                                       # Maximum number of relight attempts
rl_count = 0

def reset_boiler(conn, n_id, child_id):
    global EMails
    global Failed_EMails

    cursorupdate = conn.cursor()
    query = ("UPDATE messages_out SET payload = 1, sent = 0 WHERE `n_id` = " + str(n_id) + " AND `child_id` = " + str(child_id) + ";")
    cursorupdate.execute(query)
    cursorupdate.close()
    conn.commit()
    time.sleep(2)
    cursorupdate = conn.cursor()
    query = ("UPDATE messages_out SET payload = 0, sent = 0 WHERE `n_id` = " + str(n_id) + " AND `child_id` = " + str(child_id) + ";")
    cursorupdate.execute(query)
    cursorupdate.close()
    conn.commit()
    print(bc.dtm + datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Triggering Boiler Reset Relay")

    # send an email notification
    today = datetime.datetime.today()
    TEXT = "Boiler Reset\n"
    TEXT = TEXT + " - " + today.strftime('%d-%m-%Y, %H:%M:%S')
    BODY = f"From: {FROM}\nTo: {TO}\nSubject: {SUBJECT}\n\n{TEXT}\r\n"

    try:
        server = smtplib.SMTP(HOST)
#       server.set_debuglevel(1)
        server.login(USER, PASS)
        server.sendmail(FROM, TO, BODY)
        server.quit()
        EMails = EMails + 1
    except:
        Failed_EMails = Failed_EMails + 1
        pass

    return

# =================================== MAIN BOILER PROCESS ==================================
def boiler():
#    if demand_off_flag == 1 and FaultFlag == 1 :      # gone to fault condition while in the switched OFF state
#        if test_once == 1 :
#            test_once = 0                               # clear to enable relight on next change to ON state

#    print("MAX_RELIGHTS ",MAX_RELIGHTS)
    global EMails
    global Failed_EMails

    error_flag = False
    status = False
    type = ""
    cnx = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cursorselect = cnx.cursor()
    # check for active reset requests
    cursorselect.execute("SELECT * FROM `reset` WHERE id=(SELECT MAX(id) FROM `reset`);")
    result = cursorselect.fetchone()
    if cursorselect.rowcount > 0 :
        reset_to_index = dict(
            (d[0], i) for i, d in enumerate(cursorselect.description)
        )
        id = int(result[reset_to_index["id"]])
        status = bool(result[reset_to_index["status"]])
        type = result[reset_to_index["type"]]
        reset_count = int(result[reset_to_index["reset_count"]])
        if "POWER_CYCLE" in type:
            relay_id = boiler_power_relay_id
            relay_child_id = boiler_power_relay_child_id
        else:
            relay_id = reset_relay_id
            relay_child_id = reset_relay_child_id

        # if reset request is present, then clear by using 'reset_boiler'
        if status :
            print(bc.dtm + datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Actioning ReBoot Request")
            cursorupdate = cnx.cursor()
            query = ("UPDATE reset SET status = 0, sync = 0, end_datetime = '" + str(datetime.datetime.now()) + "' WHERE id = " + str(id) + ";")
            cursorupdate.execute(query)
            cnx.commit()
            # clear the 'state' sensor error indicator
            query = ("UPDATE `sensors` SET `current_val_1` = 0 WHERE `id` = " + str(state_id) + ";")
            cursorupdate.execute(query)
            cursorupdate.close()
            cnx.commit()
            reset_boiler(cnx, relay_id, relay_child_id)
            cursorselect.close()
            cnx.close()
            return
        # no reset requests in the database, so check if boiler is in error state
        else :
            # check if any schedules are running
            cursorselect.execute("""SELECT `zone`.id, `zone`.name, `zone_current_state`.`schedule`
                            FROM `zone_current_state`, `zone`
                            WHERE (`zone_current_state`.`zone_id` = `zone`.`id`) AND `zone_current_state`.`schedule` = 1
                            AND (`zone`.`type_id` = 2 OR `zone`.`type_id` = 3);""")
            if cursorselect.rowcount > 0 :
                schedule = True
            else:
                schedule = False
            # check the current error state of the boiler
            cursorselect.execute("SELECT `current_val_1` FROM `sensors` WHERE id = " + str(state_id) + ";")
            result = cursorselect.fetchone()
            if cursorselect.rowcount > 0 :
                sensor_to_index = dict(
                    (d[0], i) for i, d in enumerate(cursorselect.description)
                )
                error_flag = bool(result[sensor_to_index["current_val_1"]])
                # the boiler is in error state
                if error_flag :
                    timestamp = datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')
                    cursor_reset = cnx.cursor()
                    print(bc.dtm + datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Add New AUTO ReSet Request")
                    reset_count = 1
                    qry_str = "INSERT INTO `reset`(`sync`, `purge`, `status`, `type`, `reset_count`, `start_datetime`) VALUES (0,0,1,'AUTO'," + str(rl_count) + ",'" + timestamp + "');"
                    cursor_reset.execute(qry_str)
                    cursor_reset.close()
                    cnx.commit()
#                    print("reset_count ",reset_count)
                else :
                    print(bc.dtm + datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Boiler NOT in Error State")

            cursorselect.close()
            cnx.close()

            print(bc.dtm + datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Number of EMails sent    - " + str(EMails))
            print(bc.dtm + datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Number of EMails FAILED  - " + str(Failed_EMails))
            print("-" * line_len)
            return

def main() :
    global state_id
    global reset_id
    global reset_relay_child_id
    global reset_relay_id
    global boiler_power_id
    global boiler_power_relay_child_id
    global boiler_power_relay_id
    global status_id
    global status_sensor_id
    global status_sensor_child_id
    global EMails
    global Failed_EMails
    EMails = 0
    Failed_EMails = 0
    rl_count = 0

    # check if a 'Boiler Status' sensor exists in the database
    status_sensor = False
    cur.execute("SELECT * FROM sensors WHERE name = 'Boiler Status' LIMIT 1;")
    result = cur.fetchone()
    if cur.rowcount > 0 :
        sensor_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        status_id = int(result[sensor_to_index["id"]])
        status_sensor_id = int(result[sensor_to_index["sensor_id"]])
        status_sensor_child_id = int(result[sensor_to_index["sensor_child_id"]])
        cur.execute('SELECT node_id FROM nodes WHERE id = (%s)', (status_sensor_id, ))
        result =cur.fetchone()
        if cur.rowcount > 0 :
            node_to_index = dict(
                (d[0], i) for i, d in enumerate(cur.description)
            )
            status_node_id = int(result[node_to_index["node_id"]])
            status_sensor = True

    # check if a 'Boiler State' sensor exists in the database
    state_sensor = False
    cur.execute("SELECT * FROM sensors WHERE name = 'Boiler State' LIMIT 1;")
    result = cur.fetchone()
    if cur.rowcount > 0 :
        sensor_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        state_id = int(result[sensor_to_index["id"]])
        state_sensor_id = int(result[sensor_to_index["sensor_id"]])
        state_sensor_child_id = int(result[sensor_to_index["sensor_child_id"]])
        cur.execute('SELECT node_id FROM nodes WHERE id = (%s)', (state_sensor_id, ))
        result =cur.fetchone()
        if cur.rowcount > 0 :
            node_to_index = dict(
                (d[0], i) for i, d in enumerate(cur.description)
            )
            state_node_id = int(result[node_to_index["node_id"]])
            state_sensor = True

    # check if a 'Boiler Reset' relay exists in the database
    reset_relay = False
    cur.execute("SELECT * FROM relays WHERE name = 'Boiler Reset' LIMIT 1;")
    result = cur.fetchone()
    if cur.rowcount > 0 :
        relay_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        reset_id = int(result[relay_to_index["id"]])
        reset_relay_id = int(result[relay_to_index["relay_id"]])
        reset_relay_on_trigger = int(result[relay_to_index["on_trigger"]])
        reset_relay_child_id = int(result[relay_to_index["relay_child_id"]])
        cur.execute('SELECT node_id FROM nodes WHERE id = (%s)', (reset_relay_id, ))
        result =cur.fetchone()
        if cur.rowcount > 0 :
            node_to_index = dict(
                (d[0], i) for i, d in enumerate(cur.description)
            )
            reset_relay_node_id = int(result[node_to_index["node_id"]])
            reset_relay = True

    # check if a 'Boiler Power' relay exists in the database
    boiler_power_relay = False
    cur.execute("SELECT * FROM relays WHERE name = 'Boiler Power' LIMIT 1;")
    result = cur.fetchone()
    if cur.rowcount > 0 :
        relay_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        boiler_power_id = int(result[relay_to_index["id"]])
        boiler_power_relay_id = int(result[relay_to_index["relay_id"]])
        boiler_power_relay_on_trigger = int(result[relay_to_index["on_trigger"]])
        boiler_power_relay_child_id = int(result[relay_to_index["relay_child_id"]])
        cur.execute('SELECT node_id FROM nodes WHERE id = (%s)', (boiler_power_relay_id, ))
        result =cur.fetchone()
        if cur.rowcount > 0 :
            node_to_index = dict(
                (d[0], i) for i, d in enumerate(cur.description)
            )
            boiler_power_relay_node_id = int(result[node_to_index["node_id"]])
            boiler_power_relay = True

    # Create the container (outer) email message.
    USER    = 'boiler@overkillsystems.com'
    PASS    = 'Tr3!!3b0rg'
    HOST    = 'smtp.livemail.co.uk'
    SUBJECT = "HX15 Boiler Status"
    TO      = "terry.adams@overkillsystems.com"
    FROM    = "boiler@overkillsystems.com"

    # Send startup email
    today = datetime.datetime.today()
    TEXT = "Boiler Startup at - " + today.strftime('%d-%m-%Y, %H:%M:%S')
    BODY = f"From: {FROM}\nTo: {TO}\nSubject: {SUBJECT}\n\n{TEXT}\r\n"

    try:
        server = smtplib.SMTP(HOST)
#       server.set_debuglevel(1)
        server.login(USER, PASS)
        server.sendmail(FROM, TO, BODY)
        server.quit()
        EMails = EMails + 1
    except:
        Failed_EMails = Failed_EMails + 1

    # Schedule boiler function to run every 15 seconds
    schedule.every(15).seconds.do(boiler)

    # ***************************************
    # Start scheduler and run every 1 second
    # ***************************************
    while True:
      # Checks whether a scheduled task
      # is pending to run or not
      schedule.run_pending()
      time.sleep(1)

if __name__=="__main__":
   main()
