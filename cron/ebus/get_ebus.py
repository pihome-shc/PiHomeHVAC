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
print("* MySensors Wifi/Ethernet/Serial Gateway Communication *")
print("* Script to communicate with MySensors Nodes, for more *")
print("* info please check MySensors API.                     *")
print("*      Build Date: 18/09/2017                          *")
print("*      Version 0.12 - Last Modified 31/01/2022         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

import MySQLdb as mdb, sys, serial, telnetlib, time, datetime, os
import configparser, logging
from datetime import datetime
import struct
import requests
import socket, re
try:
    from Pin_Dict import pindict
    import board, digitalio
    blinka = True
except:
    blinka = False
import traceback
import subprocess

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
null_value = None
ping_timer = 0
MQTT_CONNECTED = 1

try:
    # Initialise the database access variables
    config = configparser.ConfigParser()
    config.read("/var/www/st_inc/db_config.ini")
    dbhost = config.get("db", "hostname")
    dbuser = config.get("db", "dbusername")
    dbpass = config.get("db", "dbpassword")
    dbname = config.get("db", "dbname")

    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cur = con.cursor()
    cur.execute("SELECT c_f FROM system LIMIT 1")
    row = cur.fetchone()
    system_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
    c_f = row[system_to_index["c_f"]]  # 0 = centigrade, 1 = fahrenheit
    cur.execute("SELECT * FROM gateway where status = 1 order by id asc limit 1")
    row = cur.fetchone()
    gateway_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
    gatewaytype = row[gateway_to_index["type"]]  # serial/wifi
    gatewaylocation = row[
        gateway_to_index["location"]
    ]
    gatewayport = row[
        gateway_to_index["port"]
    ]  # UDP port or bound rate for MySensors gateway
    gatewaytimeout = int(
        row[gateway_to_index["timout"]]
    )  # Connection timeout in Seconds

    gatewayenableoutgoing = int(
        row[gateway_to_index["enable_outgoing"]]
    )  # Flag to indicate if outgoing messages sgould be processed

    if gatewaytype == "serial":
        # ps. you can troubleshoot with "screen"
        # screen /dev/ttyAMA0 115200
        # gw = serial.Serial('/dev/ttyMySensorsGateway', 115200, timeout=0)
        gw = serial.Serial(gatewaylocation, gatewayport, timeout=5)
        print(bc.grn + "Gateway Type:  Serial", bc.ENDC)
        print(bc.grn + "Serial Port:   ", gatewaylocation, bc.ENDC)
        print(bc.grn + "Baud Rate:     ", gatewayport, bc.ENDC)
    elif gatewaytype == "wifi":
        # MySensors Wifi/Ethernet Gateway Manuall override to specific ip Otherwise ip from MySQL Databased is used.
        # mysgw = "192.168.99.3" 	#ip address of your MySensors gateway
        # mysport = "5003" 		#UDP port number for MySensors gateway
        # gw = telnetlib.Telnet(mysgw, mysport, timeout=3) # Connect mysensors gateway
        gw = telnetlib.Telnet(
            gatewaylocation, gatewayport, timeout=gatewaytimeout
        )  # Connect mysensors gateway from MySQL Database
        print(bc.grn + "Gateway Type:  Wifi/Ethernet", bc.ENDC)
        print(bc.grn + "IP Address:    ", gatewaylocation, bc.ENDC)
        print(bc.grn + "UDP Port:      ", gatewayport, bc.ENDC)
    else:
        print(bc.grn + "Gateway Type:  Virtual", bc.ENDC)

    msgcount = 0  # Defining variable for counting messages processed

    # Get the network address for use by Tasmota devices
    cur.execute(
        'SELECT gateway_address FROM network_settings where interface_type LIKE "wlan%" limit 1'
    )
    row = cur.fetchone()
    if cur.rowcount > 0:
        network_found = 1
        net_gw = row[0]
        net_gw = net_gw[0 : (net_gw.rfind(".") + 1)]
    else:
        network_found = 0

    while 1:
        ## Terminate gateway script if no route to network gateway
        if gatewaytype == "wifi":
            if time.time() - ping_timer >= 60:
                ping_timer = time.time()
                gateway_up = (
                    True if os.system("ping -c 1 " + gatewaylocation) == 0 else False
                )
                if not gateway_up:
                    break
        if gatewaytype != "virtual":
            if gatewaytype == "serial":
                in_str = gw.readline()  # Here is receiving part of the code for serial GW
                in_str = in_str.decode("utf-8")
            else:
                in_str = gw.read_until(
                b"\n", timeout=1
                )  # Here is receiving part of the code for Wifi
                in_str = in_str.decode("utf-8")

            if dbgLevel >= 2:  # Debug print to screen
                if time.strftime("%S", time.gmtime()) == "00" and msgcount != 0:
                    print(bc.hed + "\nMessages processed in last 60s:	", msgcount)
                    if gatewaytype == "serial":
                        try:
                            print("Bytes in outgoing buffer:	", gw.in_waiting)
                        except Exception:
                            pass
                    print("Date & Time:                 	", time.ctime(), bc.ENDC)
                    msgcount = 0
                if not sys.getsizeof(in_str) <= 22:
                    msgcount += 1

            if (
                not sys.getsizeof(in_str) <= 25 and in_str[:1] != "0"
            ):  # here is the line where sensor are processed
                if dbgLevel >= 1 and dbgMsgIn == 1:  # Debug print to screen
                    print(
                        bc.ylw + "\nSize of the String Received: ",
                        sys.getsizeof(in_str),
                        bc.ENDC,
                    )
                    print("Date & Time:                 ", time.ctime())
                    in_str.replace("\n", "\\n")
                    print("Full String Received:         ", end=in_str)
                statement = in_str.split(";")
                if dbgLevel >= 3 and dbgMsgIn == 1:
                    print("Full Statement Received:     ", statement)

        time.sleep(0.1)

except configparser.Error as e:
    print("ConfigParser:", format(e))
    con.close()
    if MQTT_CONNECTED == 1:
        mqttClient.disconnect()
        mqttClient.loop_stop()
except mdb.Error as e:
    print("DB Error %d: %s" % (e.args[0], e.args[1]))
    con.close()
    if MQTT_CONNECTED == 1:
        mqttClient.disconnect()
        mqttClient.loop_stop()
except serial.SerialException as e:
    print("SerialException:", format(e))
    con.close()
    if MQTT_CONNECTED == 1:
        mqttClient.disconnect()
        mqttClient.loop_stop()
except EOFError as e:
    print("EOFError:", format(e))
    con.close()
    if MQTT_CONNECTED == 1:
        mqttClient.disconnect()
        mqttClient.loop_stop()
except TypeError:
    print(traceback.format_exc())
    con.close()
    if MQTT_CONNECTED == 1:
        mqttClient.disconnect()
        mqttClient.loop_stop()
except Exception as e:
    print(format(e))
    con.close()
    if MQTT_CONNECTED == 1:
        mqttClient.disconnect()
        mqttClient.loop_stop()
except ProgramKilled:
    write_message_to_console("Program killed: running cleanup code")
    con.close()
    if MQTT_CONNECTED == 1:
        mqttClient.disconnect()
        mqttClient.loop_stop()
finally:
    print(infomsg)
    logging.exception(Exception)
    sys.exit(1)
