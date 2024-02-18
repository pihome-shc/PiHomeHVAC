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
print("      *          Script to send status Email messages        *")
print("      *                Build Date: 26/10/2021                *")
print("      *      Version 0.07 - Last Modified 18/02/2024         *")
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
        SUBJECT = "MaxAir Database Backup"
        MESSAGE = ""
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

print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Notice Script Started")
print(sline)
# Check Gateway Logs for last 10 minuts and start search for gateway connected failed.
print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Checking Gateway Communication")

try:
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cur = con.cursor()
    query = ('SELECT COUNT(*) FROM gateway_logs WHERE pid_datetime >= DATE_SUB(NOW(),INTERVAL 10 MINUTE);')
    cur.execute(query)
    count = cur.fetchone()  # Grab all messages from database for gateway_logs.
    count = count[
        0]  # Parse first and the only one part of data table named "count" - there is number of records grabbed in SELECT above
    if count > 9:  # If greater then 10 then we have something to send out.
        message = "Gateway Connection Lost in Last 10 minutes: " + str(count) + "\n"
        query = ("SELECT * FROM notice WHERE message = '" + message + "'")
        cursorsel = con.cursor()
        cursorsel.execute(query)
        name_to_index = dict(
            (d[0], i)
            for i, d
            in enumerate(cursorsel.description)
        )
        messages = cursorsel.fetchone()  # Grab all notices with the same message content.
        cursorsel.close()
        cursorupdate = con.cursor()
        if cursorsel.rowcount > 0:
            if messages[name_to_index['status']] == 1:
                cursorupdate.execute("UPDATE notice SET status = '0'")
        else:
            cursorupdate.execute('INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES(%s,%s,%s,%s,%s)',
                                 (0, 0, datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"), message, 1))
            print(bc.blu + (datetime.datetime.now().strftime(
                "%Y-%m-%d %H:%M:%S")) + bc.wht + " - Gateway Connection Lost in Last 10 minutes: " + str(count))

        cursorupdate.close()
        con.commit()
    elif count == 0:  # no gateway errors in the last hour so clear any existing messages to allow new ones
        query = ("DELETE FROM notice WHERE message LIKE 'Gateway Connection Lost in Last 10 minutes:%'")
        cursordelete = con.cursor()
        cursordelete.execute(query)
        cursordelete.close()
        con.commit()

except mdb.Error as e:
    print("Error %d: %s" % (e.args[0], e.args[1]))
    sys.exit(1)
finally:
    if con:
        con.close()
print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Gateway Notice Finished")
print(sline)

# *************************************************************************************************************
# Active Nodes Last Seen status and Battery Level
print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Checking Node Communication")
try:
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cursorselect = con.cursor()
    query = ("SELECT * FROM nodes WHERE status = 'Active';")
    cursorselect.execute(query)
    node_to_index = dict(
        (d[0], i)
        for i, d
        in enumerate(cursorselect.description)
    )
    results = cursorselect.fetchall()
    cursorselect.close()
    if cursorselect.rowcount > 0:  # Some Active Nodes
        for i in results:  # loop through active nodes
            node_id = i[node_to_index['node_id']]
            name = i[node_to_index['name']]
            last_seen = i[node_to_index['last_seen']]
            notice_interval = i[node_to_index['notice_interval']]
            min_value = i[node_to_index['min_value']]
            timeDifference = (datetime.datetime.now() - last_seen)
            time_difference_in_minutes = (timeDifference.days * 24 * 60) + (timeDifference.seconds / 60)
            message = name + " " + node_id + " last reported on " + str(last_seen)
            # select any records in the notice table which match the current message
            query = ("SELECT * FROM notice WHERE message = '" + message + "'")
            cursorsel = con.cursor()
            cursorsel.execute(query)
            name_to_index = dict(
                (d[0], i)
                for i, d
                in enumerate(cursorsel.description)
            )
            messages = cursorsel.fetchone()
            cursorsel.close()
            if time_difference_in_minutes >= notice_interval and notice_interval > 0:  # Active Sensor found which has not reported in the last test interval
                cursorupdate = con.cursor()
                if cursorsel.rowcount > 0:  # This message already exists
                    if messages[name_to_index['status']] == 1:  # This node has already sent an email with this content
                        cursorupdate.execute("UPDATE notice SET status = '0'")  # so clear status to stop further emails
                else:  # new notification so add a new message to the notification table
                    cursorupdate.execute(
                        'INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES(%s,%s,%s,%s,%s)',
                        (0, 0, datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"), message, 1))
                    print(bc.blu + (
                        datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - " + name + " " + node_id + " - Last Reported " + str(
                        notice_interval) + " Minutes Ago.")

                cursorupdate.close()
                con.commit()
            else:  # node has now reported so delete any 'notice' records
                query = "DELETE FROM notice WHERE message LIKE '" + name + " " + str(node_id) + "%'"
                cursordelete = con.cursor()
                cursordelete.execute(query)
                cursordelete.close()
                con.commit()

            if min_value > 0:  # This is a Battery Powered Node, so check battery status
                print(bc.blu + (datetime.datetime.now().strftime(
                    "%Y-%m-%d %H:%M:%S")) + bc.wht + " - Checking Battery Node - " + str(node_id) + " Communication")
                try:
                    cnx = mdb.connect(dbhost, dbuser, dbpass, dbname)
                    cursorselect = cnx.cursor()
                    query = (
                            "SELECT * FROM `nodes_battery` WHERE `node_id` = '" + str(node_id) + "' ORDER BY `update` DESC LIMIT 1;")
                    cursorselect.execute(query)
                    bat_node_to_index = dict(
                        (d[0], i)
                        for i, d
                        in enumerate(cursorselect.description)
                    )
                    results = cursorselect.fetchone()
                    cursorselect.close()
                    if cursorselect.rowcount > 0:  # Battery Record Found
                        update = results[bat_node_to_index['update']]
                        if results[bat_node_to_index['bat_level']] is None :
                            bat_level = 100
                        else :
                            bat_level = int(results[bat_node_to_index['bat_level']])
                        timeDifference = (datetime.datetime.now() - update)
                        time_difference_in_minutes = (timeDifference.days * 24 * 60) + (timeDifference.seconds / 60)
                        message = "Battery Node " + str(node_id) + " last reported on " + str(update)
                        # select any records in the notice table which match the current message
                        query = ("SELECT * FROM notice WHERE message = '" + message + "'")
                        cursorsel = con.cursor()
                        cursorsel.execute(query)
                        name_to_index = dict(
                            (d[0], i)
                            for i, d
                            in enumerate(cursorsel.description)
                        )
                        messages = cursorsel.fetchone()
                        cursorsel.close()
                        if time_difference_in_minutes >= notice_interval and notice_interval > 0:  # Active Sensor found which has not reported in the last test interval
                            cursorupdate = con.cursor()
                            if cursorsel.rowcount > 0:  # This message already exists
                                if messages[name_to_index[
                                    'status']] == 1:  # This node has already sent an email with this content
                                    cursorupdate.execute(
                                        "UPDATE notice SET status = '0'")  # so clear status to stop further emails
                            else:  # new notification so add a new message to the notification table
                                cursorupdate.execute(
                                    'INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES(%s,%s,%s,%s,%s)',
                                    (0, 0, datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"), message, 1))
                                print(bc.blu + (datetime.datetime.now().strftime(
                                    "%Y-%m-%d %H:%M:%S")) + bc.wht + " - Battery Node - " + str(node_id) + " Reported - " + str(
                                    2) + " Minutes Ago.")

                            cursorupdate.close()
                            con.commit()
                        else:  # node has now reported so delete any 'notice' records
                            query = "DELETE FROM notice WHERE message LIKE 'Battery Node " + str(
                                node_id) + " last reported on%'"
                            cursordelete = con.cursor()
                            cursordelete.execute(query)
                            cursordelete.close()
                            con.commit()

                        # Check latest Battery Level
                        # ----------------------------
                        message = "Battery Node " + str(node_id) + " Level < " + str(min_value) + " %"
                        # select any records in the notice table which match the current message
                        query = ("SELECT * FROM notice WHERE message = '" + message + "'")
                        cursorsel = con.cursor()
                        cursorsel.execute(query)
                        name_to_index = dict(
                            (d[0], i)
                            for i, d
                            in enumerate(cursorsel.description)
                        )
                        messages = cursorsel.fetchone()
                        cursorsel.close()
                        print(bc.blu + (datetime.datetime.now().strftime(
                            "%Y-%m-%d %H:%M:%S")) + bc.wht + " - Measured Battery Level - " + str(bat_level) + "%, Threshold Level " + str(
                            min_value) + "%.")
                        if bat_level < min_value:  # Active Sensor found where level is less than minimum
                            cursorupdate = con.cursor()
                            if cursorsel.rowcount > 0:  # This message already exists
                                if messages[name_to_index[
                                    'status']] == 1:  # This node has already sent an email with this content
                                    cursorupdate.execute(
                                        "UPDATE notice SET status = '0'")  # so clear status to stop further emails
                            else:  # new notification so add a new message to the notification table
                                cursorupdate.execute(
                                    'INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES(%s,%s,%s,%s,%s)',
                                    (0, 0, datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"), message, 1))
                                print(bc.blu + (datetime.datetime.now().strftime(
                                    "%Y-%m-%d %H:%M:%S")) + bc.wht + " - Battery Node - " + str(node_id) + " Level < " + str(
                                    min_value) + " %.")

                            cursorupdate.close()
                            con.commit()
                        elif bat_level > min_value:  # node has now reported a sulitable level so delete any 'notice' records
                            query = "DELETE FROM notice WHERE message LIKE 'Battery Node " + str(node_id) + " Level <%'"
                            cursordelete = con.cursor()
                            cursordelete.execute(query)
                            cursordelete.close()
                            con.commit()

                        # Delete any No level found notices
                        query = "DELETE FROM notice WHERE message LIKE 'Battery Node " + str(
                            node_id) + " No Level Records%'"
                        cursordelete = con.cursor()
                        cursordelete.execute(query)
                        cursordelete.close()
                        con.commit()

                    else:  # no battery records found
                        message = "Battery Node " + str(node_id) + " No Level Records Found"
                        # select any records in the notice table which match the current message
                        query = ("SELECT * FROM notice WHERE message = '" + message + "'")
                        cursorsel = con.cursor()
                        cursorsel.execute(query)
                        name_to_index = dict(
                            (d[0], i)
                            for i, d
                            in enumerate(cursorsel.description)
                        )
                        messages = cursorsel.fetchone()
                        cursorsel.close()
                        cursorupdate = con.cursor()
                        if cursorsel.rowcount > 0:  # This message already exists
                            if messages[
                                name_to_index['status']] == 1:  # This node has already sent an email with this content
                                cursorupdate.execute(
                                    "UPDATE notice SET status = '0'")  # so clear status to stop further emails
                        else:  # new notification so add a new message to the notification table
                            cursorupdate.execute(
                                'INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES(%s,%s,%s,%s,%s)',
                                (0, 0, datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"), message, 1))
                            print(bc.blu + (datetime.datetime.now().strftime(
                                "%Y-%m-%d %H:%M:%S")) + bc.wht + " - Battery Node - " + str(node_id) + " No Level Records Found.")

                        cursorupdate.close()
                        con.commit()

                except mdb.Error as e:
                    print("Error %d: %s" % (e.args[0], e.args[1]))
                    sys.exit(1)
                finally:
                    if cnx:
                        cnx.close()

                print(bc.blu + (
                    datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Battery Node Check Finished")

except mdb.Error as e:
    print("Error %d: %s" % (e.args[0], e.args[1]))
    sys.exit(1)
finally:
    if con:
        con.close()

print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Active Node Check Finished")
print(sline)

# *************************************************************************************************************
# Check CPU Temperature from last one hour if it was over 50c
print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Checking CPU Temperature")

try:
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cursorselect = con.cursor()
    query = ("SELECT max_cpu_temp FROM system LIMIT 1")
    cursorselect.execute(query)
    name_to_index = dict(
        (d[0], i)
        for i, d
        in enumerate(cursorselect.description)
    )
    system = cursorselect.fetchone()
    max_cpu_temp = str(system[name_to_index['max_cpu_temp']])

    cursorselect.execute(
        "SELECT COUNT(*) FROM messages_in_view_24h WHERE node_id = '0' AND payload > " + max_cpu_temp +" AND DATETIME >= DATE_SUB(NOW(),INTERVAL 60 MINUTE);")
    count = cursorselect.fetchone()  # Grab all messages from database for CPU temperature.
    count = count[
        0]  # Parse first and the only one part of data table named "count" - there is number of records grabbed in SELECT above
    if count > 0:  # If greater then 0 then we have something to send out.
        message = "Over CPU Max Temperature Recorded in last one Hour"
        query = ("SELECT * FROM notice WHERE message = '" + message + "'")
        cursorsel = con.cursor()
        cursorsel.execute(query)
        name_to_index = dict(
            (d[0], i)
            for i, d
            in enumerate(cursorsel.description)
        )
        messages = cursorsel.fetchone()
#        cursorsel.close()
        cursorupdate = con.cursor()
        if cursorsel.rowcount > 0:
            if messages[name_to_index['status']] == 1:
                cursorupdate.execute("UPDATE notice SET status = '0'")
        else:
            cursorupdate.execute(
                'INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES(%s,%s,%s,%s,%s)',
                (0, 0, datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"), message, 1))
            print(bc.blu + (
                datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - CPU Temperature is very high")

        cursorsel.close()
        cursorupdate.close()
        con.commit()
    elif count == 0:  # no CPU temperature errors in the last hour so clear any existing messages to allow new ones
        query = ("DELETE FROM notice WHERE message LIKE 'Over CPU Max Temperature Recorded in last one Hour'")
        cursordelete = con.cursor()
        cursordelete.execute(query)
        cursordelete.close()
        con.commit()

except mdb.Error as e:
    print("Error %d: %s" % (e.args[0], e.args[1]))
    sys.exit(1)
finally:
    if con:
        con.close()

print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - CPU Temperature Check Finished")
print(sline)

# *************************************************************************************************************
# Check if any Sensors have exceeded set thier limits
print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Checking Sensors for Out of Limits Temperature")

try:
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cursorselect = con.cursor()
    query = ("SELECT * FROM sensor_limits WHERE status = 1")
    cursorselect.execute(query)
    sensor_to_index = dict(
        (d[0], i)
        for i, d
        in enumerate(cursorselect.description)
    )
    results = cursorselect.fetchall()
    cursorselect.close()
    if cursorselect.rowcount > 0:  # Some Sensors have limits set
        for i in results:  # loop through sensors with limits
            sensors_id = i[sensor_to_index['sensor_id']]
            sensors_id = str(sensors_id)
            min = i[sensor_to_index['min']]
            max = i[sensor_to_index['max']]
            # get the sensor node and child id
            query = ("SELECT name, sensor_id, sensor_child_id FROM sensors WHERE id = '" + sensors_id + "' LIMIT 1")
            cursorsel = con.cursor()
            cursorsel.execute(query)
            name_to_index = dict(
                (d[0], i)
                for i, d
                in enumerate(cursorsel.description)
            )
            result = cursorsel.fetchone()
            cursorsel.close()
            name = result[name_to_index['name']]
            sensor_id = result[name_to_index['sensor_id']]
            sensor_id = str(sensor_id)
            sensor_child_id = result[name_to_index['sensor_child_id']]
            sensor_child_id = str(sensor_child_id)
            # get the node id for this sensor
            query = ("SELECT node_id FROM nodes WHERE id = '" + sensor_id + "' LIMIT 1")
            cursorsel = con.cursor()
            cursorsel.execute(query)
            node_to_index = dict(
                (d[0], i)
                for i, d
                in enumerate(cursorsel.description)
            )
            result = cursorsel.fetchone()
            cursorsel.close()
            node_id = result[node_to_index['node_id']]
            node_id = str(node_id)
            # get last temperature for this sensor
            query = ("SELECT payload FROM messages_in_view_24h WHERE node_id = '" + node_id + "' AND child_id = '" + sensor_child_id + "' LIMIT 1;")
            cursorsel = con.cursor()
            cursorsel.execute(query)
            result = cursorsel.fetchone()
            cursorsel.close()
            if cursorsel.rowcount > 0: # check if we have any data for this sensor
                sensor_temp = result[0]
                if sensor_temp < min or sensor_temp > max:
                    if sensor_temp < min:
                        message = "Sensor - " + name + " is Below Minimum Limit"
                    elif sensor_temp > max:
                        message = "Sensor - " + name + " is Above Maximum Limit"
                    n_msg = message  + "\n"
                    query = ("SELECT * FROM notice WHERE message = '" + n_msg + "' LIMIT 1")
                    cursorsel = con.cursor()
                    cursorsel.execute(query)
                    name_to_index = dict(
                        (d[0], i)
                        for i, d
                        in enumerate(cursorsel.description)
                    )
                    messages = cursorsel.fetchone()
                    cursorsel.close()
                    cursorupdate = con.cursor()
                    if cursorsel.rowcount > 0:  # This message already exists
                        if messages[name_to_index['status']] == 1:  # This sensor has already sent an email with this content
                            cursorupdate.execute("UPDATE notice SET status = '0'")  # so clear status to stop further emails
                    else:  # new notification so add a new message to the notification table
                        cursorupdate.execute(
                            'INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES(%s,%s,%s,%s,%s)',
                            (0, 0, datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"), n_msg, 1))
                        print(bc.blu + (
                            datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - " + message)
                    cursorupdate.close()
                    con.commit()
                else: # sensor temp is back within limits so delete the notice
                    query = "DELETE FROM notice WHERE message LIKE 'Sensor - " + name + " is %'"
                    cursordelete = con.cursor()
                    cursordelete.execute(query)
                    cursordelete.close()
                    con.commit()
            else: # if not reported in the last 24 hours then delete any old messages
                    query = "DELETE FROM notice WHERE message LIKE 'Sensor - " + name + " is %'"
                    cursordelete = con.cursor()
                    cursordelete.execute(query)
                    cursordelete.close()
                    con.commit()

except mdb.Error as e:
    print("Error %d: %s" % (e.args[0], e.args[1]))
    sys.exit(1)
finally:
    if con:
        con.close()

print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Sensor Limits Check Finished")
print(sline)

# *************************************************************************************************************
# MQTT Devices Last Seen
print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Checking MQTT Devices Communication")
try:
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cursorselect = con.cursor()
#    query = ("SELECT * FROM mqtt_devices WHERE type = 0 AND last_seen IS NOT NULL;")
    query = """SELECT CONCAT(nodes.node_id,'-',mqtt_devices.child_id) AS node_id, mqtt_devices.id, mqtt_devices.name, mqtt_devices.last_seen, mqtt_devices.notice_interval,
            mqtt_devices.min_value
            FROM mqtt_devices, nodes
            WHERE (nodes.id = mqtt_devices.nodes_id) AND mqtt_devices.type = 0 AND mqtt_devices.last_seen IS NOT NULL;"""
    cursorselect.execute(query)
    mqtt_device_to_index = dict(
        (d[0], i)
        for i, d
        in enumerate(cursorselect.description)
    )
    results = cursorselect.fetchall()
    cursorselect.close()
    if cursorselect.rowcount > 0:  # Some MQTT Devices
        for i in results:  # loop through active nodes
            node_id = i[mqtt_device_to_index['node_id']]
            id = i[mqtt_device_to_index['id']]
            name = i[mqtt_device_to_index['name']]
            last_seen = i[mqtt_device_to_index['last_seen']]
            notice_interval = i[mqtt_device_to_index['notice_interval']]
            min_value = i[mqtt_device_to_index['min_value']]
            timeDifference = (datetime.datetime.now() - last_seen)
            time_difference_in_minutes = (timeDifference.days * 24 * 60) + (timeDifference.seconds / 60)
            message = name + " " + str(id) + " last reported on " + str(last_seen)
            # select any records in the notice table which match the current message
            query = ("SELECT * FROM notice WHERE message = '" + message + "'")
            cursorsel = con.cursor()
            cursorsel.execute(query)
            name_to_index = dict(
                (d[0], i)
                for i, d
                in enumerate(cursorsel.description)
            )
            messages = cursorsel.fetchone()
            cursorsel.close()
            if time_difference_in_minutes >= notice_interval and notice_interval > 0:  # Active Sensor found which has not reported in the last test interval
                cursorupdate = con.cursor()
                if cursorsel.rowcount > 0:  # This message already exists
                    if messages[name_to_index['status']] == 1:  # This node has already sent an email with this content
                        cursorupdate.execute("UPDATE notice SET status = '0'")  # so clear status to stop further emails
                else:  # new notification so add a new message to the notification table
                    cursorupdate.execute(
                        'INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES(%s,%s,%s,%s,%s)',
                        (0, 0, datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"), message, 1))
                    print(bc.blu + (
                        datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - " + name + " " + str(id) + " - Last Reported " + str(
                        notice_interval) + " Minutes Ago.")

                cursorupdate.close()
                con.commit()
            else:  # node has now reported so delete any 'notice' records
                query = "DELETE FROM notice WHERE message LIKE '" + name + " " + str(id) + "%'"
                cursordelete = con.cursor()
                cursordelete.execute(query)
                cursordelete.close()
                con.commit()

            # Process any Battery powered MQTT Devices
            if min_value != 0:
                print(bc.blu + (datetime.datetime.now().strftime(
                    "%Y-%m-%d %H:%M:%S")) + bc.wht + " - Checking Battery Node - " + node_id + " Communication")
                try:
                    cnx = mdb.connect(dbhost, dbuser, dbpass, dbname)
                    cursorselect = cnx.cursor()
                    query = (
                            "SELECT * FROM `nodes_battery` WHERE `node_id` = '" + node_id + "' ORDER BY `update` DESC LIMIT 1;")
                    cursorselect.execute(query)
                    bat_node_to_index = dict(
                        (d[0], i)
                        for i, d
                        in enumerate(cursorselect.description)
                    )
                    results = cursorselect.fetchone()
                    cursorselect.close()
                    if cursorselect.rowcount > 0:  # Battery Record Found
                        update = results[bat_node_to_index['update']]
                        if results[bat_node_to_index['bat_level']] is None :
                            bat_level = 100
                        else :
                            bat_level = int(results[bat_node_to_index['bat_level']])
                        timeDifference = (datetime.datetime.now() - update)
                        time_difference_in_minutes = (timeDifference.days * 24 * 60) + (timeDifference.seconds / 60)
                        message = "Battery Node " + node_id + " last reported on " + str(update)
                        # select any records in the notice table which match the current message
                        query = ("SELECT * FROM notice WHERE message = '" + message + "'")
                        cursorsel = con.cursor()
                        cursorsel.execute(query)
                        name_to_index = dict(
                            (d[0], i)
                            for i, d
                            in enumerate(cursorsel.description)
                        )
                        messages = cursorsel.fetchone()
                        cursorsel.close()
                        if time_difference_in_minutes >= notice_interval and notice_interval > 0:  # Active Sensor found which has not reported in the last test interval
                            cursorupdate = con.cursor()
                            if cursorsel.rowcount > 0:  # This message already exists
                                if messages[name_to_index[
                                    'status']] == 1:  # This node has already sent an email with this content
                                    cursorupdate.execute(
                                        "UPDATE notice SET status = '0'")  # so clear status to stop further emails
                            else:  # new notification so add a new message to the notification table
                                cursorupdate.execute(
                                    'INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES(%s,%s,%s,%s,%s)',
                                    (0, 0, datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"), message, 1))
                                print(bc.blu + (datetime.datetime.now().strftime(
                                    "%Y-%m-%d %H:%M:%S")) + bc.wht + " - Battery Node - " + node_id + " Reported - " + str(
                                    2) + " Minutes Ago.")

                            cursorupdate.close()
                            con.commit()
                        else:  # node has now reported so delete any 'notice' records
                            query = "DELETE FROM notice WHERE message LIKE 'Battery Node " + node_id + " last reported on%'"
                            cursordelete = con.cursor()
                            cursordelete.execute(query)
                            cursordelete.close()
                            con.commit()

                        # Check latest Battery Level
                        # ----------------------------
                        message = "Battery Node " + node_id + " Level < " + str(min_value) + " %"
                        # select any records in the notice table which match the current message
                        query = ("SELECT * FROM notice WHERE message = '" + message + "'")
                        cursorsel = con.cursor()
                        cursorsel.execute(query)
                        name_to_index = dict(
                            (d[0], i)
                            for i, d
                            in enumerate(cursorsel.description)
                        )
                        messages = cursorsel.fetchone()
                        cursorsel.close()
                        print(bc.blu + (datetime.datetime.now().strftime(
                            "%Y-%m-%d %H:%M:%S")) + bc.wht + " - Measured Battery Level - " + str(bat_level) + "%, Threshold Level " + str(
                            min_value) + "%.")
                        if bat_level < min_value:  # Active Sensor found where level is less than minimum
                            cursorupdate = con.cursor()
                            if cursorsel.rowcount > 0:  # This message already exists
                                if messages[name_to_index[
                                    'status']] == 1:  # This node has already sent an email with this content
                                    cursorupdate.execute(
                                        "UPDATE notice SET status = '0'")  # so clear status to stop further emails
                            else:  # new notification so add a new message to the notification table
                                cursorupdate.execute(
                                    'INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES(%s,%s,%s,%s,%s)',
                                    (0, 0, datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"), message, 1))
                                print(bc.blu + (datetime.datetime.now().strftime(
                                    "%Y-%m-%d %H:%M:%S")) + bc.wht + " - Battery Node - " + node_id + " Level < " + str(
                                    min_value) + " %.")

                            cursorupdate.close()
                            con.commit()
                        elif bat_level > min_value:  # node has now reported a sulitable level so delete any 'notice' records
                            query = "DELETE FROM notice WHERE message LIKE 'Battery Node " + node_id + " Level <%'"
                            cursordelete = con.cursor()
                            cursordelete.execute(query)
                            cursordelete.close()
                            con.commit()

                        # Delete any No level found notices
                        query = "DELETE FROM notice WHERE message LIKE 'Battery Node " + node_id + " No Level Records%'"
                        cursordelete = con.cursor()
                        cursordelete.execute(query)
                        cursordelete.close()
                        con.commit()

                    else:  # no battery records found
                        message = "Battery Node " + node_id + " No Level Records Found"
                        # select any records in the notice table which match the current message
                        query = ("SELECT * FROM notice WHERE message = '" + message + "'")
                        cursorsel = con.cursor()
                        cursorsel.execute(query)
                        name_to_index = dict(
                            (d[0], i)
                            for i, d
                            in enumerate(cursorsel.description)
                        )
                        messages = cursorsel.fetchone()
                        cursorsel.close()
                        cursorupdate = con.cursor()
                        if cursorsel.rowcount > 0:  # This message already exists
                            if messages[
                                name_to_index['status']] == 1:  # This node has already sent an email with this content
                                cursorupdate.execute(
                                    "UPDATE notice SET status = '0'")  # so clear status to stop further emails
                        else:  # new notification so add a new message to the notification table
                            cursorupdate.execute(
                                'INSERT INTO notice (sync, `purge`, datetime, message, status) VALUES(%s,%s,%s,%s,%s)',
                                (0, 0, datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"), message, 1))
                            print(bc.blu + (datetime.datetime.now().strftime(
                                "%Y-%m-%d %H:%M:%S")) + bc.wht + " - Battery Node - " + node_id + " No Level Records Found.")

                        cursorupdate.close()
                        con.commit()

                except mdb.Error as e:
                    print("Error %d: %s" % (e.args[0], e.args[1]))
                    sys.exit(1)
                finally:
                    if cnx:
                        cnx.close()

                print(bc.blu + (
                    datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Battery Node Check Finished")

except mdb.Error as e:
    print("Error %d: %s" % (e.args[0], e.args[1]))
    sys.exit(1)
finally:
    if con:
        con.close()

print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - MQTT Devices Check Finished")
print(sline)

# Send Email Message
if send_status:
    try:
        con = mdb.connect(dbhost, dbuser, dbpass, dbname)
        cursorselect = con.cursor()
        query = ("SELECT * FROM notice WHERE status = 1;")
        cursorselect.execute(query)
        name_to_index = dict(
            (d[0], i)
            for i, d
            in enumerate(cursorselect.description)
        )
        results = cursorselect.fetchall()
        cursorselect.close()
        if cursorselect.rowcount > 0:
            for i in results:
                MESSAGE = MESSAGE + i[name_to_index['message']] + "\n"

    except mdb.Error as e:
        print("Error %d: %s" % (e.args[0], e.args[1]))
        sys.exit(1)
    finally:
        if con:
            con.close()

    if len(MESSAGE) > 0:
        print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Sending Email Message")
        msg = MIMEMultipart()
        msg['Subject'] = 'MaxAir Status'
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

print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Notice Script Ended \n")
