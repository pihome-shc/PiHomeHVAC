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
    red = "\033[41m"
    red_txt = "\033[31m"
    blu = "\033[44m"

print(bc.hed + " ")
print(r"    __  __                             _         ")
print(r"   |  \/  |                    /\     (_)        ")
print(r"   | \  / |   __ _  __  __    /  \     _   _ __  ")
print(r"   | |\/| |  / _` | \ \/ /   / /\ \   | | | '__| ")
print(r"   | |  | | | (_| |  >  <   / ____ \  | | | |    ")
print(r"   |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|    ")
print(" ")
print("        " + bc.SUB + "S M A R T   T H E R M O S T A T " + bc.ENDC)
print(bc.WARN + " ")
print("********************************************************")
print("*               Graph csv File Script                  *")
print("*                                                      *")
print("*               Build Date: 09/02/2024                 *")
print("*       Version 0.02 - Last Modified 25/11/2025        *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

line_len = 100; #length of seperator lines

import csv
import sys
import time
import configparser
import MySQLdb as mdb
from datetime import datetime

timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

print( "-" * 75)
print(bc.dtm + time.ctime() + bc.ENDC + ' - Create CSV Archive File Script Started')
print( "-" * 75)

# Initialise the database access variables
config = configparser.ConfigParser()
config.read("/var/www/st_inc/db_config.ini")
dbhost = config.get("db", "hostname")
dbuser = config.get("db", "dbusername")
dbpass = config.get("db", "dbpassword")
dbname = config.get("db", "dbname")

con = mdb.connect(dbhost, dbuser, dbpass, dbname)
cur = con.cursor()

cur.execute("SELECT * FROM graphs LIMIT 1")
row = cur.fetchone()
graph_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
archive_enable = row[graph_to_index["archive_enable"]]
if archive_enable:
    update_flag = False
    csv_file_path = row[graph_to_index["archive_file"]]
    archive_pointer = row[graph_to_index["archive_pointer"]].strftime("%Y-%m-%d %H:%M:%S")
    cur.execute(
        """SELECT sensors.id, sensors.name, sg.payload, sg.datetime
           FROM sensors
           JOIN nodes n ON n.id = sensors.sensor_id
           JOIN sensor_graphs sg ON sg.node_id = n.node_id AND sg.child_id = sensors.sensor_child_id
           WHERE sg.datetime > %s
           ORDER BY sg.name ASC, sensor_type_id, sg.datetime ASC;""",
        (archive_pointer,),
    )
    rows = cur.fetchall()

    # Continue only if there are rows returned.
    if rows:
        update_flag = True
        # New empty list called 'result'. This will be written to a file.
        result = list()

        # The row name is the first entry for each entity in the description tuple.
#        column_names = list()
#        for i in cur.description:
#            column_names.append(i[0])

#        result.append(column_names)

        id = ''
        payload = ''
        for row in rows:
            if row[0] != id or row[2] != payload:
                result.append(row)
                id = row[0]
                payload = row[2]

        # Write result to file.
        with open(csv_file_path, 'a', newline='') as csvfile:
            csvwriter = csv.writer(csvfile, delimiter=',', quotechar='"', quoting=csv.QUOTE_MINIMAL)
            for row in result:
                csvwriter.writerow(row)

        print(bc.dtm + time.ctime() + bc.ENDC + ' - ' + str(cur.rowcount) + ' New Sensor Entries Added to Archive File')

    # Generate the Min/Max file
    # Create sensor temperature readings archive file per sensor
    cur.execute(
        """SELECT sensors.id, sensors.name, MAX(sg.payload) AS max, MIN(sg.payload) AS min, DATE(sg.datetime) DateOnly
           FROM sensors
           JOIN nodes n ON n.id = sensors.sensor_id
           JOIN sensor_graphs sg ON sg.node_id = n.node_id AND sg.child_id = sensors.sensor_child_id
           WHERE sg.datetime > %s
           GROUP BY sensors.id
           ORDER BY sg.name ASC, sensor_type_id, sg.datetime ASC;""",
        (archive_pointer,),
    )
    mrows = cur.fetchall()
    # Continue only if there are rows returned.
    if mrows:
        for row in mrows:
            # Write result to table.
            cur.execute(
                "INSERT INTO `sensor_min_max_graph`(`sensor_id`, `name`, `max`, `min`, `date`) VALUES(%s,%s,%s,%s,%s)",
                (row[0], row[1], row[2], row[3], row[4]),
            )
            con.commit()

        print(bc.dtm + time.ctime() + bc.ENDC + ' - ' + str(cur.rowcount) + ' New Sensor Max/Min Entries Added to Archive File')


    else:
        print(bc.dtm + time.ctime() + bc.ENDC + ' - No New Sensor Entries Added to Archive File')

    #get the outside temperature readings for the last 24 hours if it is active
    cur.execute(
        "SELECT * FROM weather WHERE last_update > DATE_SUB( NOW(), INTERVAL 24 HOUR);"
    )
    if cur.rowcount > 0:
        cur.execute(
            "SELECT 0 AS id, 'Outside Temp' AS name, payload, datetime FROM messages_in WHERE node_id = '1' AND child_id = 0 AND datetime > %s ORDER BY datetime ASC;",
            (archive_pointer,),
        )
        rows = cur.fetchall()

        # Continue only if there are rows returned.
        if rows:
            update_flag = True
            # New empty list called 'result'. This will be written to a file.
            result = list()

            id = ''
            payload = ''
            for row in rows:
                if row[2] != payload:
                    result.append(row)
                    id = row[0]
                    payload = row[2]

            # Write result to file.
            with open(csv_file_path, 'a', newline='') as csvfile:
                csvwriter = csv.writer(csvfile, delimiter=',', quotechar='"', quoting=csv.QUOTE_MINIMAL)
                for row in result:
                    csvwriter.writerow(row)

            print(bc.dtm + time.ctime() + bc.ENDC + ' - ' + str(cur.rowcount) + ' New Outside Temp Entries Added to Archive File')

        # Process Min/Max for Outside Temperature
        cur.execute(
            """SELECT 0 AS id, 'Outside Temp' AS name, MAX(payload) AS max, MIN(payload) AS min, DATE(datetime) DateOnly
               FROM messages_in
               WHERE node_id = '1' AND child_id = 0 AND datetime > %s
               GROUP BY DateOnly
               ORDER BY datetime ASC;""",
            (archive_pointer,),
        )

        mrows = cur.fetchall()

        # Continue only if there are rows returned.
        if mrows:
            update_flag = True
            for row in mrows:
                # Write result to table.
                cur.execute(
                    "INSERT INTO `sensor_min_max_graph`(`sensor_id`, `name`, `max`, `min`, `date`) VALUES(%s,%s,%s,%s,%s)",
                    (row[0], row[1], row[2], row[3], row[4]),
                )
                con.commit()

            print(bc.dtm + time.ctime() + bc.ENDC + ' - ' + str(cur.rowcount) + ' New Max/Min Outside Temp Entries Added to Archive File')

        else:
            print(bc.dtm + time.ctime() + bc.ENDC + ' - NO New Outside Temp Added to Archive File')
    else:
        print(bc.dtm + time.ctime() + bc.ENDC + ' - Weather Temperature NOT Active.')

    if update_flag:
        # Update the last record added pointer
        cur.execute(
            "UPDATE graphs SET archive_pointer = %s;",
            (timestamp,),
        )
        con.commit()  # commit above
        cur.close()
        con.close()
        print( "-" * 75)
        print(bc.dtm + time.ctime() + bc.ENDC + ' - Updating Archive Pointer to: ' + str(timestamp))

print( "-" * 75)
print(bc.dtm + time.ctime() + bc.ENDC + ' - Create CSV Archive File Script Ended')
print( "-" * 75)
