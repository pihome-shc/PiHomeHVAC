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
print("*              Database Cleanup Script                 *")
print("*      Build Date: 18/09/2017                          *")
print("*      Version 0.01 - Last Modified 21/03/2026         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

import MySQLdb as mdb, os, datetime
import configparser

line_len = 189

# set running flag
with open('/tmp/db_cleanup_running', 'w') as fp:
    pass

# Initialise the database access varables
config = configparser.ConfigParser()
config.read('/var/www/st_inc/db_config.ini')
dbhost = config.get('db', 'hostname')
dbuser = config.get('db', 'dbusername')
dbpass = config.get('db', 'dbpassword')
dbname = config.get('db', 'dbname')
con = mdb.connect(dbhost, dbuser, dbpass, dbname)
cur = con.cursor()

while os.path.isfile("/tmp/on_message_running") or os.path.isfile("/tmp/sc_running") or os.path.isfile("/tmp/gpio_ds18b20_running"):
    print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Waiting for gateway.py")
    print("-" * line_len)

print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - DB Cleanup Script Started")
print("-" * line_len)

cur.execute("SELECT * FROM db_cleanup LIMIT 1;")
if cur.rowcount > 0:
    row  = cur.fetchone()
    row_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
    interval_1 = row[row_to_index["messages_in"]]
    interval_2 = row[row_to_index["nodes_battery"]]
    interval_3 = row[row_to_index["gateway_logs"]]
    interval_4 = row[row_to_index["relay_logs"]]
    interval_5 = "24 HOUR"

    qry_tuple = ('DELETE FROM messages_in WHERE datetime < DATE_SUB(curdate(), INTERVAL {});'.format(interval_1),
                 'DELETE FROM nodes_battery WHERE `update` < DATE_SUB(CURDATE(), INTERVAL {});'.format(interval_2),
                 'DELETE FROM nodes_battery WHERE node_id NOT IN (SELECT nodes.node_id  FROM nodes UNION SELECT CONCAT(nodes.node_id,"-",mqtt_devices.child_id) AS node_id FROM mqtt_devices, nodes WHERE mqtt_devices.nodes_id = nodes.id);',
                 'DELETE FROM `sensor_graphs` WHERE `datetime` < DATE_SUB(CURDATE(), INTERVAL {});'.format(interval_5),
                 'DELETE FROM `gateway_logs` WHERE pid_datetime < DATE_SUB(CURDATE(), INTERVAL {})  AND id != (SELECT id FROM (SELECT id FROM `gateway_logs` ORDER BY id DESC LIMIT 1) myselect);'.format(interval_3),
                 'DELETE FROM relay_logs WHERE datetime < DATE_SUB(curdate(), INTERVAL {});'.format(interval_4))
    for q in qry_tuple:
        try:
            cur.execute(q)
            con.commit()
            print(bc.dtm + datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Query '" + q + "' Successful.")
        except:
            print(bc.dtm + datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Query '" + q + " 'Failed.")

if os.path.exists("/tmp/db_cleanup_running"):
    os.remove("/tmp/db_cleanup_running")

if con:
    con.close()

print("-" * line_len)
print(bc.blu + (datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - DB Cleanup Script Ended \n")
