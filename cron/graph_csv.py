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
print("*               Graph csv File Script                  *")
print("*                                                      *")
print("*               Build Date: 09/02/2024                 *")
print("*       Version 0.01 - Last Modified 13/02/2024        *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

line_len = 100; #length of seperator lines

import csv
import sys
import configparser
import MySQLdb as mdb

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
    csv_file_path = row[graph_to_index["archive_file"]]
    archive_pointer = row[graph_to_index["archive_pointer"]].strftime("%Y-%m-%d %H:%M:%S")
    sql = "SELECT name, payload, datetime FROM sensor_graphs WHERE datetime > %s ORDER BY name ASC, datetime ASC"
    cur.execute(
        sql,
        (archive_pointer,),
    )
    rows = cur.fetchall()

    # Continue only if there are rows returned.
    if rows:
        # New empty list called 'result'. This will be written to a file.
        result = list()

        # The row name is the first entry for each entity in the description tuple.
#        column_names = list()
#        for i in cur.description:
#            column_names.append(i[0])

#        result.append(column_names)

        name = ''
        payload = ''
        for row in rows:
            if row[0] != name or row[1] != payload:
                result.append(row)
                name = row[0]
                payload = row[1]
            dt = row[2].strftime("%Y-%m-%d %H:%M:%S")
            if dt > archive_pointer:
                archive_pointer = dt

        # Write result to file.
        with open(csv_file_path, 'a', newline='') as csvfile:
            csvwriter = csv.writer(csvfile, delimiter=',', quotechar='"', quoting=csv.QUOTE_MINIMAL)
            for row in result:
                csvwriter.writerow(row)

        # Update the last record added pointer
        cur.execute(
            "UPDATE graphs SET archive_pointer = %s;",
            (archive_pointer,),
        )
        con.commit()  # commit above

    else:
        sys.exit("No rows found for query: {}".format(sql))

cur.close()
con.close()

