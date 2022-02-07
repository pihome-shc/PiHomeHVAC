#!/usr/bin/python3
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
print("*   Script to update Homebridge Switches and Sensors   *")
print("*      Build Date: 06/02/2022                          *")
print("*      Version 0.1 - Last Modified 06/02/2022          *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

import MySQLdb as mdb, sys, serial, telnetlib, time, datetime, os
import configparser, logging
from datetime import datetime
import struct
import requests
import traceback
import subprocess
import json
import urllib.request

# Debug print to screen configuration
dbgLevel = 3  # 0-off, 1-info, 2-detailed, 3-all
dbgMsgOut = 1  # 0-disabled, 1-enabled, show details of outgoing messages
dbgMsgIn = 1  # 0-disabled, 1-enabled, show details of incoming messages

# Logging exceptions to log file
logfile = "/var/www/logs/main.log"
infomsg = "More info in log file: " + logfile
logging.basicConfig(
    filename=logfile,
    level=logging.DEBUG,
    format=("\n### %(asctime)s - %(levelname)s ###"),
)
import json
import urllib.request

try:
    # Initialise the database access variables
    config = configparser.ConfigParser()
    config.read("/var/www/st_inc/db_config.ini")
    dbhost = config.get("db", "hostname")
    dbuser = config.get("db", "dbusername")
    dbpass = config.get("db", "dbpassword")
    dbname = config.get("db", "dbname")

    con = mdb.connect(dbhost, dbuser, dbpass, dbname)

    with open('/var/lib/homebridge/config.json') as json_file:
        data = json.load(json_file)

    while 1:
        # Process Switches
        x = data['platforms'][1]['switches']
        for i in x:
            switch_id = i['id']
            cursorselect = con.cursor()
            cursorselect.execute(
                "SELECT * FROM boost WHERE zone_id = (%s) LIMIT 1",
                (i['id'][6:],),
            )
            if cursorselect.rowcount > 0:
                switch_to_index = dict(
                    (d[0], i)
                    for i, d
                    in enumerate(cursorselect.description)
                )
                switch = cursorselect.fetchone()
                cursorselect.close()
                zone_boost = switch[switch_to_index['status']]
                if zone_boost == 1:
                    zone_boost = True
                else:
                    zone_boost = False
                request_url = urllib.request.urlopen('http://127.0.0.1:51828/?accessoryId=' + switch_id)
                x = request_url.read()
                y = x.decode("utf-8")
                z = json.loads(y)
                boost_state = z["state"]
                if zone_boost != boost_state:
#                print(boost_state, zone_boost)
                   payload = {'accessoryId': switch_id, 'value': zone_boost}
                   r = requests.get('http://127.0.0.1:51828/', params=payload)
#                print(r.url)

        # Process Sensors
        x = data['platforms'][1]['sensors']
        for i in x:
            sensor_id = i['id']
            id = i['id'][6:]
            cursorselect = con.cursor()
            cursorselect.execute(
                "SELECT nodes.node_id, sensors.sensor_child_id FROM sensors, nodes WHERE (sensors.sensor_id = nodes.id) AND sensors.id = (%s) LIMIT 1;",
                (i['id'][6:],),
            )
            sensor_to_index = dict(
                (d[0], i)
                for i, d
                in enumerate(cursorselect.description)
            )
            result = cursorselect.fetchone()
            cursorselect.close()
            node_id = result[sensor_to_index['node_id']]
            node_id = str(node_id)
            child_id = result[sensor_to_index['sensor_child_id']]
            cursorselect = con.cursor()
            cursorselect.execute(
                'SELECT `payload`  FROM `messages_in_view_24h` WHERE `node_id` = (%s) AND `child_id` = (%s) ORDER BY datetime DESC LIMIT 1',
                [node_id, child_id],
            )
            if cursorselect.rowcount > 0:
                msg_in_to_index = dict(
                    (d[0], i)
                    for i, d
                    in enumerate(cursorselect.description)
                )
                stemp = cursorselect.fetchone()
                cursorselect.close()
                sensor_temp = float(stemp[msg_in_to_index['payload']])

                request_url = urllib.request.urlopen('http://127.0.0.1:51828/?accessoryId=' + sensor_id)
                x = request_url.read()
                y = x.decode("utf-8")
                z = json.loads(y)
                webhooks_temp = float(z["state"])
                if webhooks_temp != sensor_temp:
#                print(webhooks_temp, sensor_temp)
                    payload = {'accessoryId': sensor_id, 'value': sensor_temp}
                    r = requests.get('http://127.0.0.1:51828/', params=payload)
#                print(r.url)
        time.sleep(30)

except configparser.Error as e:
    print("ConfigParser:", format(e))
    con.close()
except mdb.Error as e:
    print("DB Error %d: %s" % (e.args[0], e.args[1]))
    con.close()
except TypeError:
    print(traceback.format_exc())
    con.close()
except Exception as e:
    print(format(e))
    con.close()
except ProgramKilled:
    write_message_to_console("Program killed: running cleanup code")
    con.close()
finally:
    print(infomsg)
    logging.exception(Exception)
    sys.exit(1)
