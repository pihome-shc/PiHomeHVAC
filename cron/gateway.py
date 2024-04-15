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
print("*      Version 0.28 - Last Modified 09/03/2024         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

import MySQLdb as mdb, sys, serial, time, datetime, os, fnmatch
import configparser, logging
from datetime import datetime, timedelta
import struct
import requests
import socket, re
import threading
import queue

try:
    from Pin_Dict import pindict
    import board, digitalio
    blinka = True
except:
    blinka = False
import traceback
import subprocess
from math import floor

# Debug print to screen configuration
dbgLevel = 3  # 0-off, 1-info, 2-detailed, 3-all
dbgMsgOut = 1  # 0-disabled, 1-enabled, show details of outgoing messages
dbgMsgIn = 1  # 0-disabled, 1-enabled, show details of incoming messages

# create dictionary for mode and sub-mode
main_mode_dict = {
   0:   "Idle",
   10:  "Fault",
   20:  "Frost",
   30:  "Over Temperature",
   40:  "Holiday",
   50:  "Night Climate",
   60:  "Boost",
   70:  "Override",
   80:  "Scheduled",
   90:  "Away",
   100: "Hysteresis",
   110: "Add On",
   120: "HVAC",
   130: "Under Temperature",
   140: "Manual"
}

sub_mode_dict = {
   0: "Stopped",
   1: "Running",
   2: "Stopped",
   3: "Stopped",
   4: "Manual ON",
   5: "Manual OFF",
   6: "Cooling",
   7: "HVAC Fan",
   8: "Max Running Time Exceeded - Hysteresis active"
}

# create dictionary for relay lag timer
relay_lag_timer = dict()
# initialise the relay_on_flag
relay_on_flag = False

#initialize the transaction counters
mqtt_sent = 0
mysensor_sent = 0
gpio_sent = 0
minute_timer = time.time()
hour_timer = time.time()
clear_minute_timer = False
clear_hour_timer = False

# Logging exceptions to log file
logfile = "/var/www/logs/main.log"
infomsg = "More info in log file: " + logfile
logging.basicConfig(
    filename=logfile,
    level=logging.DEBUG,
    format=("\n### %(asctime)s - %(levelname)s - %(message)s  ###"),
)

#process the incoming messages from the gateway hardware
def process_message(in_str):
    global clear_minute_timer
    global msgcount

    if dbgLevel >= 2:  # Debug print to screen
        if time.time() - minute_timer >= 60:
            print(bc.hed + "\nMessages processed in last 60s:	", msgcount)
            if gatewaytype == "serial":
                try:
                    print("Bytes in outgoing buffer:	", gw.in_waiting)
                except Exception:
                    pass
            print("Date & Time:                 	", time.ctime(), bc.ENDC)
            msgcount = 0
            clear_minute_timer = True
        if not sys.getsizeof(in_str) <= 22 and not clear_minute_timer:
            msgcount += 1

    if (
#        not sys.getsizeof(in_str) <= 25 and in_str[:1] != "0"
        not sys.getsizeof(in_str) <= 25 and len(in_str) > 0
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

        if (
            len(statement) == 6 and statement[0].isdigit()
        ):  # check if received message is right format
            node_id = str(statement[0])
            child_sensor_id = int(statement[1])
            message_type = int(statement[2])
            ack = int(statement[3])
            sub_type = int(statement[4])
            payload = statement[5].rstrip()  # remove \n from payload

            if dbgLevel >= 3 and dbgMsgIn == 1:  # Debug print to screen
                print("Node ID:                     ", node_id)
                print("Child Sensor ID:             ", child_sensor_id)
                print("Message Type:                ", message_type)
                print("Acknowledge:                 ", ack)
                print("Sub Type:                    ", sub_type)
                print("Pay Load:                    ", payload)
                if gatewaytype == "wifi":
                     print("FIFO Queue lines remaining:  ", fifo.qsize())
                # ..::Step One::..
                # First time Temperature Sensors Node Comes online: Add Node to The Nodes Table.
            if (
                node_id != '0'
                and child_sensor_id == 255
                and message_type == 0
                and sub_type == 17
            ):
                # if (child_sensor_id != 255 and message_type == 0):
                cur.execute(
                    "SELECT COUNT(*) FROM `nodes` where node_id = (%s)", (node_id,)
                )
                row = cur.fetchone()
                row = int(row[0])
                if row == 0:
                    if dbgLevel >= 2 and dbgMsgIn == 1:
                        print(
                            "1: Adding Node ID:",
                            node_id,
                            "MySensors Version:",
                            payload,
                        )
                    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                    cur.execute(
                        "INSERT INTO `nodes`(`sync`, `purge`, `type`, `node_id`, `max_child_id`, `sub_type`, `name`, `last_seen`, `notice_interval`, `min_value`, `status`, `ms_version`, `sketch_version`, `repeater`) VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                        (
                            0,
                            0,
                            "MySensor",
                            node_id,
                            0,
                            0,
                            null_value,
                            timestamp,
                            0,
                            0,
                            "Active",
                            payload,
                            null_value,
                            0,
                        ),
                    )
                    con.commit()
                else:
                    if dbgLevel >= 2 and dbgMsgIn == 1:
                        print(
                            "1: Node ID:",
                            node_id,
                            " Already Exist In Node Table, Updating MS Version",
                        )
                    try:
                        cur.execute(
                            "UPDATE nodes SET ms_version = %s where node_id = %s",
                            (payload, node_id),
                        )
                        con.commit()
                    except mdb.Error as e:
                        # skip deadlock error (being caused when mysqldunp runs
                        if e.args[0] == 1213:
                            pass
                        else:
                            print("DB Error %d: %s" % (e.args[0], e.args[1]))
                            print(traceback.format_exc())
                            logging.error(e)
                            logging.info(traceback.format_exc())
                            con.close()
                            if MQTT_CONNECTED == 1:
                                mqttClient.disconnect()
                                mqttClient.loop_stop()
                            print(infomsg)
                            sys.exit(1)

                    # ..::Step One B::..
                    # First time Node Comes online with Repeater Feature Enabled: Add Node to The Nodes Table.
            if (
                node_id != '0'
                and child_sensor_id == 255
                and message_type == 0
                and sub_type == 18
            ):
                # if (child_sensor_id != 255 and message_type == 0):
                cur.execute(
                    "SELECT COUNT(*) FROM `nodes` where node_id = (%s)", (node_id,)
                )
                row = cur.fetchone()
                row = int(row[0])
                if row == 0:
                    if dbgLevel >= 2 and dbgMsgIn == 1:
                        print(
                            "1-B: Adding Node ID:",
                            node_id,
                            "MySensors Version:",
                            payload,
                        )
                    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                    try:
                        cur.execute(
                            "INSERT INTO nodes(`sync`, `purge`, `type`, `node_id`, `max_child_id`, `sub_type`, `name`, `last_seen`, `notice_interval`, `min_value`, `status`, `ms_version`, `sketch_version`, `repeater`) VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                            (
                                0,
                                0,
                                "MySensor",
                                node_id,
                                0,
                                0,
                                null_value,
                                timestamp,
                                0,
                                0,
                                "Active",
                                payload,
                                null_value,
                                1,
                            ),
                        )
                        con.commit()
                    except mdb.Error as e:
                        # skip deadlock error (being caused when mysqldunp runs
                        if e.args[0] == 1213:
                            pass
                        else:
                            print("DB Error %d: %s" % (e.args[0], e.args[1]))
                            print(traceback.format_exc())
                            logging.error(e)
                            logging.info(traceback.format_exc())
                            con.close()
                            if MQTT_CONNECTED == 1:
                                mqttClient.disconnect()
                                mqttClient.loop_stop()
                            print(infomsg)
                            sys.exit(1)
                else:
                    if dbgLevel >= 2 and dbgMsgIn == 1:
                        print(
                            "1-B: Node ID:",
                            node_id,
                            " Already Exist In Node Table, Updating MS Version",
                        )
                    try:
                        cur.execute(
                            "UPDATE nodes SET ms_version = %s where node_id = %s",
                            (payload, node_id),
                        )
                        con.commit()
                    except mdb.Error as e:
                        # skip deadlock error (being caused when mysqldunp runs
                        if e.args[0] == 1213:
                            pass
                        else:
                            print("DB Error %d: %s" % (e.args[0], e.args[1]))
                            print(traceback.format_exc())
                            logging.error(e)
                            logging.info(traceback.format_exc())
                            con.close()
                            if MQTT_CONNECTED == 1:
                                mqttClient.disconnect()
                                mqttClient.loop_stop()
                            print(infomsg)
                            sys.exit(1)

                    # ..::Step One C::..
                    # First time a Gateway Controller Node Comes online: Add Node to The Nodes Table.
            if (
                node_id == '0'
                and child_sensor_id == 255
                and message_type == 0
                and sub_type == 18
            ):
                # if (child_sensor_id != 255 and message_type == 0):
                cur.execute(
                    "SELECT COUNT(*) FROM `nodes` where type = 'MySensor' AND node_id = (%s)", (node_id,)
                )
                row = cur.fetchone()
                row = int(row[0])
                if row == 0:
                    if dbgLevel >= 2 and dbgMsgIn == 1:
                        print(
                            "1-C: Adding Node ID:",
                            node_id,
                            "MySensors Version:",
                            payload,
                        )
                    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                    try:
                        cur.execute(
                            "INSERT INTO nodes(`sync`, `purge`, `type`, `node_id`, `max_child_id`, `sub_type`, `name`, `last_seen`, `notice_interval`, `min_value`, `status`, `ms_version`, `sketch_version`, `repeater`) VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                            (
                                0,
                                0,
                                "MySensor",
                                node_id,
                                0,
                                0,
                                "Gateway",
                                timestamp,
                                0,
                                0,
                                "Active",
                                payload,
                                "0.00",
                                1,
                            ),
                        )
                        con.commit()
                    except mdb.Error as e:
                        # skip deadlock error (being caused when mysqldunp runs
                        if e.args[0] == 1213:
                            pass
                        else:
                            print("DB Error %d: %s" % (e.args[0], e.args[1]))
                            print(traceback.format_exc())
                            logging.error(e)
                            logging.info(traceback.format_exc())
                            con.close()
                            if MQTT_CONNECTED == 1:
                                mqttClient.disconnect()
                                mqttClient.loop_stop()
                            print(infomsg)
                            sys.exit(1)
                else:
                    if dbgLevel >= 2 and dbgMsgIn == 1:
                        print(
                            "1-C: Node ID:",
                            node_id,
                            " Already Exist In Node Table, Updating MS Version",
                        )
                    try:
                        cur.execute(
                            "UPDATE nodes SET ms_version = %s where type = 'MySensor' AND node_id = %s",
                            (payload, node_id),
                        )
                        con.commit()
                    except mdb.Error as e:
                        # skip deadlock error (being caused when mysqldunp runs
                        if e.args[0] == 1213:
                            pass
                        else:
                            print("DB Error %d: %s" % (e.args[0], e.args[1]))
                            print(traceback.format_exc())
                            logging.error(e)
                            logging.info(traceback.format_exc())
                            con.close()
                            if MQTT_CONNECTED == 1:
                                mqttClient.disconnect()
                                mqttClient.loop_stop()
                            print(infomsg)
                            sys.exit(1)

                    # ..::Step One D::..
                    # First time Node Comes online set the min_value.
            if (
                node_id != '0'
                and child_sensor_id != 255
                and message_type == 1
                and sub_type == 24
            ):
                cur.execute(
                    "SELECT min_value FROM `nodes` where node_id = (%s)", (node_id,)
                )
                row = cur.fetchone()
                row = int(row[0])
                if row == 0:
                    if dbgLevel >= 2 and dbgMsgIn == 1:
                        print(
                            "1-D: Adding Node's min_value for Node ID:",
                            node_id,
                            " min_value:",
                            payload,
                        )
                    try:
                        cur.execute(
                            "UPDATE nodes SET min_value = %s where node_id = %s",
                            (payload, node_id),
                        )
                        con.commit()
                    except mdb.Error as e:
                        # skip deadlock error (being caused when mysqldunp runs
                        if e.args[0] == 1213:
                            pass
                        else:
                            print("DB Error %d: %s" % (e.args[0], e.args[1]))
                            print(traceback.format_exc())
                            logging.error(e)
                            logging.info(traceback.format_exc())
                            con.close()
                            if MQTT_CONNECTED == 1:
                                mqttClient.disconnect()
                                mqttClient.loop_stop()
                            print(infomsg)
                            sys.exit(1)

                # ..::Step Two A::..
                # Add Nodes Name i.e. Relay, Temperature Sensor etc. to Nodes Table.
            if (
                node_id != '0'
                and child_sensor_id == 255
                and message_type == 3
                and sub_type == 11
            ):
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print(
                        "2-A: Update Node Record for Node ID:",
                        node_id,
                        " Sensor Type:",
                        payload,
                    )
                try:
                    cur.execute(
                        "UPDATE nodes SET name = %s where node_id = %s",
                        (payload, node_id),
                    )
                    con.commit()
                except mdb.Error as e:
                    # skip deadlock error (being caused when mysqldunp runs
                    if e.args[0] == 1213:
                        pass
                    else:
                        print("DB Error %d: %s" % (e.args[0], e.args[1]))
                        print(traceback.format_exc())
                        logging.error(e)
                        logging.info(traceback.format_exc())
                        con.close()
                        if MQTT_CONNECTED == 1:
                            mqttClient.disconnect()
                            mqttClient.loop_stop()
                        print(infomsg)
                        sys.exit(1)

                # ..::Step Two B::..
                # Add Gateway Nodes Name.
            if (
                node_id == '0'
                and child_sensor_id == 255
                and message_type == 3
                and sub_type == 11
            ):
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print(
                        "2-B: Update Node Record for Node ID:",
                        node_id,
                        " Sensor Type:",
                        payload,
                    )
                try:
                    cur.execute(
                        "UPDATE nodes SET name = %s where type = 'MySensor' AND node_id = %s",
                        (payload, node_id),
                    )
                    con.commit()
                except mdb.Error as e:
                    # skip deadlock error (being caused when mysqldunp runs
                    if e.args[0] == 1213:
                        pass
                    else:
                        print("DB Error %d: %s" % (e.args[0], e.args[1]))
                        print(traceback.format_exc())
                        logging.error(e)
                        logging.info(traceback.format_exc())
                        con.close()
                        if MQTT_CONNECTED == 1:
                            mqttClient.disconnect()
                            mqttClient.loop_stop()
                        print(infomsg)
                        sys.exit(1)

                # ..::Step Three A::..
                # Add Nodes Sketch Version to Nodes Table.
            if (
                node_id != '0'
               and child_sensor_id == 255
                and message_type == 3
                and sub_type == 12
            ):
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print(
                        "3-A: Update Node ID: ",
                        node_id,
                        " Node Sketch Version: ",
                        payload,
                    )
                try:
                    cur.execute(
                        "UPDATE nodes SET sketch_version = %s where node_id = %s",
                        (payload, node_id),
                    )
                    con.commit()
                except mdb.Error as e:
                    # skip deadlock error (being caused when mysqldunp runs
                    if e.args[0] == 1213:
                        pass
                    else:
                        print("DB Error %d: %s" % (e.args[0], e.args[1]))
                        print(traceback.format_exc())
                        logging.error(e)
                        logging.info(traceback.format_exc())
                        con.close()
                        if MQTT_CONNECTED == 1:
                            mqttClient.disconnect()
                            mqttClient.loop_stop()
                        print(infomsg)
                        sys.exit(1)

                # ..::Step Three B::..
                # Add Gateway Controller Nodes Sketch Version to Nodes Table.
            if (
                node_id == '0'
               and child_sensor_id == 255
                and message_type == 3
                and sub_type == 12
            ):
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print(
                        "3-B: Update Node ID: ",
                        node_id,
                        " Node Sketch Version: ",
                        payload,
                    )
                try:
                    cur.execute(
                        "UPDATE nodes SET sketch_version = %s where type = 'MySensor' AND node_id = %s",
                        (payload, node_id),
                    )
                    con.commit()
                except mdb.Error as e:
                    # skip deadlock error (being caused when mysqldunp runs
                    if e.args[0] == 1213:
                        pass
                    else:
                        print("DB Error %d: %s" % (e.args[0], e.args[1]))
                        print(traceback.format_exc())
                        logging.error(e)
                        logging.info(traceback.format_exc())
                        con.close()
                        if MQTT_CONNECTED == 1:
                            mqttClient.disconnect()
                            mqttClient.loop_stop()
                        print(infomsg)
                        sys.exit(1)

                # ..::Step Four A::..
                # Add Node Child ID to Node Table
                # 25;0;0;0;6;
            if (
                node_id != '0'
                and child_sensor_id != 255
                and message_type == 0
                and (sub_type == 3 or sub_type == 6)
            ):
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print(
                        "4-A: Adding Node's Max Child ID for Node ID:",
                        node_id,
                        " Child Sensor ID:",
                        child_sensor_id,
                    )
                try:
                    cur.execute(
                        "UPDATE nodes SET max_child_id = %s WHERE node_id = %s",
                        (child_sensor_id, node_id),
                    )
                    con.commit()
                except mdb.Error as e:
                    # skip deadlock error (being caused when mysqldunp runs
                    if e.args[0] == 1213:
                        pass
                    else:
                        print("DB Error %d: %s" % (e.args[0], e.args[1]))
                        print(traceback.format_exc())
                        logging.error(e)
                        logging.info(traceback.format_exc())
                        con.close()
                        if MQTT_CONNECTED == 1:
                            mqttClient.disconnect()
                            mqttClient.loop_stop()
                        print(infomsg)
                        sys.exit(1)

                # ..::Step Four A::..
                # Add Node Child ID to Node Table
                # 25;0;0;0;6;
            if (
                node_id == '0'
                and child_sensor_id != 255
                and message_type == 0
                and (sub_type == 3 or sub_type == 6)
            ):
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print(
                        "4-B: Adding Node's Max Child ID for Gateway Controller Node ID:",
                        node_id,
                        " Child Sensor ID:",
                        child_sensor_id,
                    )
                try:
                    cur.execute(
                        "UPDATE nodes SET max_child_id = %s WHERE type = 'MySensor' AND node_id = %s",
                        (child_sensor_id, node_id),
                    )
                    con.commit()
                except mdb.Error as e:
                    # skip deadlock error (being caused when mysqldunp runs
                    if e.args[0] == 1213:
                        pass
                    else:
                        print("DB Error %d: %s" % (e.args[0], e.args[1]))
                        print(traceback.format_exc())
                        logging.error(e)
                        logging.info(traceback.format_exc())
                        con.close()
                        if MQTT_CONNECTED == 1:
                            mqttClient.disconnect()
                            mqttClient.loop_stop()
                        print(infomsg)
                        sys.exit(1)

                # ..::Step Five::..
                # Add Temperature Reading to database
            if (
                node_id != '0'
                and child_sensor_id != 255
                and message_type == 1
                and sub_type == 0
            ):
                timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                try:
                    cur.execute(
                        "UPDATE `nodes` SET `last_seen`=%s, `sync`=0  WHERE node_id = %s",
                        [timestamp, node_id],
                    )
                    con.commit()
                except mdb.Error as e:
                    # skip deadlock error (being caused when mysqldunp runs
                    if e.args[0] == 1213:
                        pass
                    else:
                        print("DB Error %d: %s" % (e.args[0], e.args[1]))
                        print(traceback.format_exc())
                        logging.error(e)
                        logging.info(traceback.format_exc())
                        con.close()
                        if MQTT_CONNECTED == 1:
                            mqttClient.disconnect()
                            mqttClient.loop_stop()
                        print(infomsg)
                        sys.exit(1)

                # Check if this sensor has a correction factor
                cur.execute(
                    "SELECT nodes.id, sensors.mode, sensors.timeout, sensors.correction_factor, sensors.resolution FROM sensors, `nodes` WHERE (sensors.sensor_id = nodes.`id`) AND  nodes.node_id = (%s) AND sensors.sensor_child_id = (%s) LIMIT 1;",
                    (node_id, child_sensor_id),
                )
                results = cur.fetchone()
                if cur.rowcount > 0:
                    sensor_to_index = dict(
                        (d[0], i) for i, d in enumerate(cur.description)
                    )
                    payload = round(
                        float(payload)
                        + float(results[sensor_to_index["correction_factor"]]),
                        2,
                    )
                    sensor_id = results[sensor_to_index["id"]]
                    mode = results[sensor_to_index["mode"]]
                    sensor_timeout = int(results[sensor_to_index["timeout"]])*60
                    tdelta = 0
                    last_message_payload = 0
                    resolution = float(results[sensor_to_index["resolution"]])
                    # Update last reading for this sensor
                    cur.execute(
                        "UPDATE `sensors` SET `current_val_1` = %s WHERE sensor_id = %s AND sensor_child_id = %s;",
                        [payload, sensor_id, child_sensor_id],
                    )
                    con.commit()
                    # # Check is sensor is attached to a zone which is being graphed
                    cur.execute(
                        """SELECT sensors.id, sensors.zone_id, nodes.node_id, sensors.sensor_child_id, sensors.name, sensors.graph_num, sensors.message_in FROM sensors, `nodes`
                           WHERE (sensors.sensor_id = nodes.`id`) AND  nodes.node_id = (%s) AND sensors.sensor_child_id = (%s) LIMIT 1;""",
                        (node_id, child_sensor_id),
                    )
                    results = cur.fetchone()
                    if cur.rowcount > 0:
                        sensor_to_index = dict(
                            (d[0], i) for i, d in enumerate(cur.description)
                        )
                        sensor_id = int(results[sensor_to_index["id"]])
                        sensor_name = results[sensor_to_index["name"]]
                        zone_id = results[sensor_to_index["zone_id"]]
                        # type = results[zone_view_to_index['type']]
                        # category = int(results[zone_view_to_index['category']])
                        graph_num = int(results[sensor_to_index["graph_num"]])
                        msg_in = int(results[sensor_to_index["message_in"]])
                        # sensor exists and it is required to update the messages_in table
                        if msg_in == 1:
                            if mode == 1:
                                # Get previous data for this sensorr
                                cur.execute(
                                    'SELECT datetime, payload FROM messages_in_view_24h WHERE node_id = %s AND child_id = %s ORDER BY id DESC LIMIT 1;',
                                    [node_id, child_sensor_id],
                                )
                                results = cur.fetchone()
                                if cur.rowcount > 0:
                                    message_to_index = dict(
                                        (d[0], i) for i, d in enumerate(cur.description)
                                    )
                                    last_message_datetime = results[message_to_index["datetime"]]
                                    last_message_payload = float(results[message_to_index["payload"]])
                                    tdelta = datetime.strptime(timestamp, "%Y-%m-%d %H:%M:%S").timestamp() -  datetime.strptime(str(last_message_datetime), "%Y-%m-%d %H:%M:%S").timestamp()
                            if mode == 0 or (cur.rowcount == 0 or (cur.rowcount > 0 and ((payload < last_message_payload - resolution or payload > last_message_payload + resolution) or tdelta > sensor_timeout))):
                                if sensor_timeout > 0 and tdelta > sensor_timeout:
                                    payload = last_message_payload
                                if dbgLevel >= 2 and dbgMsgIn == 1:
                                    print(
                                        "5: Adding Temperature Reading From Node ID:",
                                        node_id,
                                        " Child Sensor ID:",
                                        child_sensor_id,
                                        " PayLoad:",
                                        payload,
                                    )
                                cur.execute(
                                    "INSERT INTO messages_in(`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s)",
                                    (0, 0, node_id, child_sensor_id, sub_type, payload, timestamp),
                                )
                                con.commit()
                        # Check is sensor is attached to a zone which is being graphed
                        if graph_num > 0:
                            if mode == 1:
                                # Get previous data for this sensorr
                                cur.execute(
                                    'SELECT datetime, payload FROM sensor_graphs WHERE node_id = %s AND child_id = %s ORDER BY id DESC LIMIT 1;',
                                    [node_id, child_sensor_id],
                                )
                                results = cur.fetchone()
                                if cur.rowcount > 0:
                                    message_to_index = dict(
                                        (d[0], i) for i, d in enumerate(cur.description)
                                    )
                                    last_message_datetime = results[message_to_index["datetime"]]
                                    last_message_payload = float(results[message_to_index["payload"]])
                                    tdelta = datetime.strptime(timestamp, "%Y-%m-%d %H:%M:%S").timestamp() -  datetime.strptime(str(last_message_datetime), "%Y-%m-%d %H:%M:%S").timestamp()
                            if mode == 0 or (cur.rowcount == 0 or (cur.rowcount > 0 and ((payload < last_message_payload - resolution or payload > last_message_payload + resolution) or tdelta > sensor_timeout))):
                                if sensor_timeout > 0 and tdelta > sensor_timeout:
                                    payload = last_message_payload
                                if c_f:
                                    payload = round((payload * 9/5) + 32, 1)
                                if dbgLevel >= 2 and dbgMsgIn == 1:
                                    print(
                                        "5a: Adding Temperature Reading to Graph Table From Node ID:",
                                        node_id,
                                        " Child Sensor ID:",
                                        child_sensor_id,
                                        " PayLoad:",
                                        payload,
                                    )
                                if zone_id == 0:
                                    cur.execute(
                                        """INSERT INTO sensor_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`,
                                           `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)""",
                                        (
                                            0,
                                            0,
                                            sensor_id,
                                            sensor_name,
                                            "Sensor",
                                            0,
                                            node_id,
                                            child_sensor_id,
                                            sub_type,
                                            payload,
                                            timestamp,
                                        ),
                                    )
                                    con.commit()
                                else:
                                    cur.execute(
                                        "SELECT * FROM `zone_view` where id = (%s) LIMIT 1;",
                                        (zone_id,),
                                    )
                                    results = cur.fetchone()
                                    if cur.rowcount > 0:
                                        zone_view_to_index = dict(
                                            (d[0], i) for i, d in enumerate(cur.description)
                                        )
                                        zone_name = results[zone_view_to_index["name"]]
                                        type = results[zone_view_to_index["type"]]
                                        category = int(
                                            results[zone_view_to_index["category"]]
                                        )
                                        zone_sensors_id = results[zone_view_to_index["sensors_id"]]

                                        if zone_sensors_id is not None:
                                            cur.execute(
                                                """INSERT INTO sensor_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`,
                                                   `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)""",
                                                (
                                                    0,
                                                    0,
                                                    sensor_id,
                                                    zone_name,
                                                    type,
                                                    category,
                                                    node_id,
                                                    child_sensor_id,
                                                    sub_type,
                                                    payload,
                                                    timestamp,
                                                ),
                                            )
                                            con.commit()

                # ..::Step Six ::..
                # Add Humidity Reading to database
            if (
                node_id != '0'
                and child_sensor_id != 255
                and message_type == 1
                and sub_type == 1
            ):
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print(
                        "6: Adding Humidity Reading From Node ID:",
                        node_id,
                        " Child Sensor ID:",
                        child_sensor_id,
                        " PayLoad:",
                        payload,
                    )
                timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                cur.execute(
                    "INSERT INTO messages_in(`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s)",
                    (0, 0, node_id, child_sensor_id, sub_type, payload, timestamp),
                )
                con.commit()
                try:
                    cur.execute(
                        "UPDATE `nodes` SET `last_seen`=%s, `sync`=0  WHERE node_id = %s",
                         [timestamp, node_id],
                    )
                    con.commit()
                except mdb.Error as e:
                    # skip deadlock error (being caused when mysqldunp runs
                    if e.args[0] == 1213:
                        pass
                    else:
                        print("DB Error %d: %s" % (e.args[0], e.args[1]))
                        print(traceback.format_exc())
                        logging.error(e)
                        logging.info(traceback.format_exc())
                        con.close()
                        if MQTT_CONNECTED == 1:
                            mqttClient.disconnect()
                            mqttClient.loop_stop()
                        print(infomsg)
                        sys.exit(1)

                # Check is sensor is attached to a zone which is being graphed
                cur.execute(
                    """SELECT sensors.id, sensors.zone_id, nodes.id AS n_id, nodes.node_id, sensors.sensor_child_id, sensors.name, sensors.graph_num
                       FROM sensors, `nodes`
                       WHERE (sensors.sensor_id = nodes.`id`) AND  nodes.node_id = (%s) AND sensors.sensor_child_id = (%s)  LIMIT 1;""",
                    (node_id, child_sensor_id),
                )
                results = cur.fetchone()
                if cur.rowcount > 0:
                    sensor_to_index = dict(
                        (d[0], i) for i, d in enumerate(cur.description)
                    )
                    sensor_id = int(results[sensor_to_index["id"]])
                    sensor_name = results[sensor_to_index["name"]]
                    zone_id = results[sensor_to_index["zone_id"]]
                    n_id = int(results[sensor_to_index["n_id"]])
                    # Update last reading for this sensor
                    cur.execute(
                        "UPDATE `sensors` SET `current_val_1` = %s WHERE sensor_id = %s AND sensor_child_id = %s;",
                        [payload, n_id, child_sensor_id],
                    )
                    con.commit()
                    # type = results[zone_view_to_index['type']]
                    # category = int(results[zone_view_to_index['category']])
                    if dbgLevel >= 2 and dbgMsgIn == 1:
                        print(
                             "6a: Adding Humidity Reading to Graph Table From Node ID:",
                            node_id,
                            " Child Sensor ID:",
                            child_sensor_id,
                            " PayLoad:",
                            payload,
                        )
                    if zone_id == 0:
                        cur.execute(
                            "INSERT INTO sensor_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
                            (
                                0,
                                0,
                                sensor_id,
                                sensor_name,
                                "Sensor",
                                0,
                                node_id,
                                child_sensor_id,
                                sub_type,
                                payload,
                                timestamp,
                            ),
                        )
                        con.commit()
                    else:
                        cur.execute(
                            "SELECT * FROM `zone_view` where id = (%s) LIMIT 1;",
                            (zone_id,),
                        )
                        results = cur.fetchone()
                        if cur.rowcount > 0:
                            zone_view_to_index = dict(
                                (d[0], i) for i, d in enumerate(cur.description)
                            )
                            zone_name = results[zone_view_to_index["name"]]
                            type = results[zone_view_to_index["type"]]
                            category = int(results[zone_view_to_index["category"]])
                            if category < 2:
                                cur.execute(
                                    "INSERT INTO sensor_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
                                    (
                                        0,
                                        0,
                                        sensor_id,
                                        zone_name,
                                        type,
                                        category,
                                        node_id,
                                        child_sensor_id,
                                        sub_type,
                                        payload,
                                        timestamp,
                                    ),
                                )
                                con.commit()

                # ..::Step Seven ::..
                # Add Switch Reading to database
            if (
                node_id != '0'
                and child_sensor_id != 255
                and message_type == 1
                and sub_type == 16
            ):
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print(
                        "7: Adding Switch Reading From Node ID:",
                        node_id,
                        " Child Sensor ID:",
                        child_sensor_id,
                        " PayLoad:",
                        payload,
                    )
                timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                cur.execute(
                    "INSERT INTO messages_in(`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s)",
                    (0, 0, node_id, child_sensor_id, sub_type, payload, timestamp),
                )
                con.commit()
                try:
                    cur.execute(
                        "UPDATE `nodes` SET `last_seen`=%s, `sync`=0  WHERE node_id = %s",
                        [timestamp, node_id],
                    )
                    con.commit()
                except mdb.Error as e:
                    # skip deadlock error (being caused when mysqldunp runs
                    if e.args[0] == 1213:
                        pass
                    else:
                        print("DB Error %d: %s" % (e.args[0], e.args[1]))
                        print(traceback.format_exc())
                        logging.error(e)
                        logging.info(traceback.format_exc())
                        con.close()
                        if MQTT_CONNECTED == 1:
                            mqttClient.disconnect()
                            mqttClient.loop_stop()
                        print(infomsg)
                        sys.exit(1)

                cur.execute(
                    "SELECT id FROM `nodes` WHERE node_id = (%s) LIMIT 1;",
                    (node_id, ),
                )
                result = cur.fetchone()
                if cur.rowcount > 0:
                    node_to_index = dict(
                        (d[0], i) for i, d in enumerate(cur.description)
                    )
                    sensor_id = int(result[node_to_index["id"]])
                    # Update last reading for this sensor
                    cur.execute(
                        "UPDATE `sensors` SET `current_val_1` = %s WHERE sensor_id = %s AND sensor_child_id = %s;",
                        [payload, sensor_id, child_sensor_id],
                    )
                    con.commit()

                # ..::Step Eight::..
                # Add Battery Voltage Nodes Battery Table
                # Example: 25;1;1;0;38;4.39
            if (
                node_id != '0'
                and child_sensor_id != 255
                and message_type == 1
                and sub_type == 38
            ):
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print(
                        "8: Battery Voltage for Node ID:",
                        node_id,
                        " Battery Voltage:",
                        payload,
                    )
                    ##b_volt = payload # dont add record to table insted add record with battery voltage and level in next step
                timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                cur.execute(
                    "INSERT INTO nodes_battery(`sync`, `purge`, `node_id`, `bat_voltage`, `update`) VALUES(%s,%s,%s,%s,%s)",
                    (0, 0, node_id, payload, timestamp),
                )
                ##cur.execute('UPDATE `nodes` SET `last_seen`=now() WHERE node_id = %s', [node_id])
                con.commit()

                # ..::Step Nine::..
                # Add Battery Level Nodes Battery Table
                # Example: 25;255;3;0;0;104
            if (
                node_id != '0'
                and child_sensor_id == 255
                and message_type == 3
               and sub_type == 0
            ):
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print(
                        "9: Adding Battery Level & Voltage for Node ID:",
                        node_id,
                        "Battery Level:",
                        payload,
                    )
                    ##cur.execute('INSERT INTO nodes_battery(node_id, bat_voltage, bat_level) VALUES(%s,%s,%s)', (node_id, b_volt, payload)) ## This approach causes to crash this script, if variable b_volt is missing. As well battery voltage could be assigned to wrong node.
                cur.execute(
                    "UPDATE nodes_battery SET bat_level = %s WHERE id=(SELECT nid from (SELECT MAX(id) as nid FROM nodes_battery WHERE node_id = %s ) as n)",
                    (payload, node_id),
                )
                timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                try:
                    cur.execute(
                        "UPDATE nodes SET last_seen=%s, `sync`=0 WHERE node_id = %s",
                        [timestamp, node_id],
                    )
                    con.commit()
                except mdb.Error as e:
                    # skip deadlock error (being caused when mysqldunp runs
                    if e.args[0] == 1213:
                        pass
                    else:
                        print("DB Error %d: %s" % (e.args[0], e.args[1]))
                        print(traceback.format_exc())
                        logging.error(e)
                        logging.info(traceback.format_exc())
                        con.close()
                        if MQTT_CONNECTED == 1:
                            mqttClient.disconnect()
                            mqttClient.loop_stop()
                        print(infomsg)
                        sys.exit(1)

                cur.execute(
                    "SELECT * FROM `battery` where node_id = (%s) LIMIT 1;",
                    (node_id,),
                )
                results = cur.fetchone()
                if cur.rowcount == 0:
                    if dbgLevel >= 2 and dbgMsgIn == 1:
                        print(
                            "9b: Adding Battery for Node ID:",
                            node_id,
                        )
                    cur.execute(
                        "INSERT INTO battery(`node_id`) VALUES(%s)",
                        (node_id, )
                    )
                    con.commit()

                # ..::Step Ten::..
                # Add Boost Status Level to Database/Relay Last seen gets added here as well when ACK is set to 1 in messages_out table.
            if (
                node_id != '0'
                and child_sensor_id != 255
                and message_type == 1
               and sub_type == 2
            ):
                # print "2 insert: ", node_id, " , ", child_sensor_id, "payload", payload
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print(
                        "10. Adding Database Record: Node ID:",
                        node_id,
                        " Child Sensor ID:",
                        child_sensor_id,
                        " PayLoad:",
                        payload,
                    )
                xboost = "UPDATE boost SET status=%s WHERE boost_button_id=%s AND boost_button_child_id = %s"
                cur.execute(xboost, (payload, node_id, child_sensor_id))
                con.commit()
                timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                try:
                    cur.execute(
                        "UPDATE `nodes` SET `last_seen`=%s, `sync`=0 WHERE node_id = %s",
                        [timestamp, node_id],
                    )
                    con.commit()
                except mdb.Error as e:
                    # skip deadlock error (being caused when mysqldunp runs
                    if e.args[0] == 1213:
                        pass
                    else:
                        print("DB Error %d: %s" % (e.args[0], e.args[1]))
                        print(traceback.format_exc())
                        logging.error(e)
                        logging.info(traceback.format_exc())
                        con.close()
                        if MQTT_CONNECTED == 1:
                            mqttClient.disconnect()
                            mqttClient.loop_stop()
                        print(infomsg)
                        sys.exit(1)

                # ..::Step Eleven::..
                # Add Away Status Level to Database
            if (
                node_id != '0'
                and child_sensor_id != 255
                and child_sensor_id == 4
                and message_type == 1
                and sub_type == 2
            ):
                # print "2 insert: ", node_id, " , ", child_sensor_id, "payload", payload
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print(
                        "11. Adding Database Record: Node ID:",
                        node_id,
                        " Child Sensor ID:",
                        child_sensor_id,
                        " PayLoad:",
                        payload,
                    )
                xaway = "UPDATE away SET status=%s WHERE away_button_id=%s AND away_button_child_id = %s"
                cur.execute(xaway, (payload, node_id, child_sensor_id))
                con.commit()
                timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                try:
                    cur.execute(
                        "UPDATE `nodes` SET `last_seen`=%s, `sync`=0  WHERE node_id = %s",
                        [timestamp, node_id],
                    )
                    con.commit()
                except mdb.Error as e:
                    # skip deadlock error (being caused when mysqldunp runs
                    if e.args[0] == 1213:
                        pass
                    else:
                        print("DB Error %d: %s" % (e.args[0], e.args[1]))
                        print(traceback.format_exc())
                        logging.error(e)
                        logging.info(traceback.format_exc())
                        con.close()
                        if MQTT_CONNECTED == 1:
                            mqttClient.disconnect()
                            mqttClient.loop_stop()
                        print(infomsg)
                        sys.exit(1)

                # else:
                # print bc.WARN+ "No Action Defined Incomming Node Message Ignored \n\n" +bc.ENDC

                # ..::Step Twelve::..
                # When Gateway Startup Completes
            if (
                node_id == '0'
                and child_sensor_id == 255
                and message_type == 0
                and sub_type == 18
            ):
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print("12: PiHome MySensors Gateway Version :", payload)
                cur.execute("UPDATE gateway SET version = %s", [payload])
                con.commit()

                # ..::Step Thirteen::.. 40;0;3;0;1;02:27
                # When client is requesting time
            if (
                node_id != '0'
                and child_sensor_id == 255
                and message_type == 3
                and sub_type == 1
            ):
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print("13: Node ID: ", node_id, " Requested Time")
                    # nowtime = time.ctime()
                nowtime = time.strftime("%H:%M")
                ntime = "UPDATE messages_out SET payload=%s, sent=%s WHERE node_id=%s AND child_id = %s"
                cur.execute(ntime, (nowtime, "0", node_id, child_sensor_id))
                con.commit()

                # ..::Step Fourteen::.. 40;0;3;0;1;02:27
                # When client is requesting text
            if node_id != '0' and message_type == 2 and sub_type == 47:
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print(
                        "14: Node ID: ",
                        node_id,
                        "Child ID: ",
                        child_sensor_id,
                        " Requesting Text",
                    )
                nowtime = time.strftime("%H:%M")
                ntime = "UPDATE messages_out SET payload=%s, sent=%s WHERE node_id=%s AND child_id = %s"
                # cur.execute(ntime, (nowtime, '0', node_id, child_sensor_id,))
                # con.commit()

                # ..::Step Fiveteen::.. 255;18;3;0;3;
                # When Node is requesting ID
            if (
                node_id != '0' and message_type == 3 and sub_type == 3
            ):  # best is to check node_id is 255 but i can not get to work with that.
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print(
                        "15: Node ID: ",
                        node_id,
                        " Child ID: ",
                        child_sensor_id,
                        " Requesting Node ID",
                    )
                nowtime = time.strftime("%H:%M")
                cur.execute(
                    "SELECT COUNT(*) FROM `node_id` where sent = 0"
                )  # MySQL query statemen
                count = cur.fetchone()
                count = count[0]
                if count > 0:
                    cur.execute(
                        "SELECT * FROM `node_id` where sent = 0 Limit 1;"
                    )  # MySQL query statement
                    node_row = cur.fetchone()
                    node_id_to_index = dict(
                        (d[0], i) for i, d in enumerate(cur.description)
                    )
                    out_id = node_row[
                        node_id_to_index["id"]
                    ]  # Record ID - only DB info
                    new_node_id = node_row[
                        node_id_to_index["node_id"]
                    ]  # Node ID from Table
                    msg = str(node_id)  # Broadcast Node ID
                    msg += ";"  # Separator
                    msg += str(child_sensor_id)  # Child ID of the Node.
                    msg += ";"  # Separator
                    msg += str(3)
                    msg += ";"  # Separator
                    msg += str(0)
                    msg += ";"  # Separator
                    msg += str(4)
                    msg += ";"  # Separator
                    msg += str(new_node_id)  # Payload from DB
                    msg += " \n"  # New line
                    if dbgLevel >= 3 and dbgMsgOut == 1:
                        print(
                            "Full Message to Send:        ",
                            msg.replace("\n", "\\n"),
                        )  # Print Full Message
                        print("Node ID:                     ", node_id)
                        print("Child Sensor ID:             ", child_sensor_id)
                        print("Command Type:                ", 3)
                        print("Ack Req/Resp:                ", 0)
                        print("Type:                        ", 4)
                        print("Pay Load:                    ", new_node_id)
                        # node-id ; child-sensor-id ; command ; ack ; type ; payload \n
                    if gatewaytype == "serial":
                        gw.write(
                            msg.encode("utf-8")
                        )  # !!!! send it to serial (arduino attached to rPI by USB port)
                    else:
                        print("write")
                        gw.sendall(msg.encode("utf-8"))
                        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                        cur.execute(
                            "UPDATE `node_id` set sent=1, `date_time`=%s where id=%s",
                            [timestamp, out_id],
                        )  # update DB so this message will not be processed in next loop
                        con.commit()  # commit above
                else:
                    print(bc.WARN + "All exiting IDs are assigned: " + bc.ENDC)

                # ..::Step Sixteen::..
                # Update Gateway Relay Controller last seen
            if (
                node_id == '0'
                and child_sensor_id == 255
                and message_type == 1
                and sub_type == 47
            ):
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print(
                        "16: Updating last seen for Gateway Relay Controller:",
                        node_id,
                        " Child Sensor ID:",
                        child_sensor_id,
                        " PayLoad:",
                        payload,
                    )
                timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                try:
                    cur.execute(
                        "UPDATE `nodes` SET `last_seen`=%s, `sync`=0  WHERE type = 'MySensor' AND node_id = %s",
                        [timestamp, node_id],
                    )
                    con.commit()
                except mdb.Error as e:
                    # skip deadlock error (being caused when mysqldunp runs
                    if e.args[0] == 1213:
                        pass
                    else:
                        print("DB Error %d: %s" % (e.args[0], e.args[1]))
                        print(traceback.format_exc())
                        logging.error(e)
                        logging.info(traceback.format_exc())
                        con.close()
                        if MQTT_CONNECTED == 1:
                            mqttClient.disconnect()
                            mqttClient.loop_stop()
                        print(infomsg)
                        sys.exit(1)

                # The Heartbeat timer is reset within the socket read thread

                # ..::Step Seventeen::..
                # Update Relay Controller last seen
            if (
                node_id != '0'
                and child_sensor_id == 255
                and message_type == 1
                and sub_type == 47
            ):
                if dbgLevel >= 2 and dbgMsgIn == 1:
                    print(
                        "17: Updating last seen for Relay Controller:",
                        node_id,
                        " Child Sensor ID:",
                        child_sensor_id,
                        " PayLoad:",
                        payload,
                    )
                timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                try:
                    cur.execute(
                        "UPDATE `nodes` SET `last_seen`=%s, `sync`=0  WHERE type = 'MySensor' AND node_id = %s",
                        [timestamp, node_id],
                    )
                    con.commit()
                except mdb.Error as e:
                    # skip deadlock error (being caused when mysqldunp runs
                    if e.args[0] == 1213:
                        pass
                    else:
                        print("DB Error %d: %s" % (e.args[0], e.args[1]))
                        print(traceback.format_exc())
                        logging.error(e)
                        logging.info(traceback.format_exc())
                        con.close()
                        if MQTT_CONNECTED == 1:
                            mqttClient.disconnect()
                            mqttClient.loop_stop()
                        print(infomsg)
                        sys.exit(1)

def custom_excepthook(exc_type, exc_value, exc_traceback):
    # Do not print exception when user cancels the program
    if issubclass(exc_type, KeyboardInterrupt):
        sys.__excepthook__(exc_type, exc_value, exc_traceback)
        return

    logging.error("An uncaught exception occurred:")
    logging.error("Type: %s", exc_type)
    logging.error("Value: %s", exc_value)

    if exc_traceback:
        format_exception = traceback.format_tb(exc_traceback)
        for line in format_exception:
            logging.error(repr(line))

sys.excepthook = custom_excepthook

null_value = None

# Initialise a dictionary to hold the relay id for Adafruit Blinka
relay_dict = {}

# Initialise MQTT connection status
MQTT_CONNECTED = 0

# Used by MQTT function 'on_message' to get attribute value
def deep_get(dictionary, keys, default=None):
    return reduce(lambda d, key: d.get(key, default) if isinstance(d, dict) else default, keys.split("."), dictionary)

def XNOR(a,b):
    if(int(a) == int(b)):
        return 1
    else:
        return 0

def set_relays(
    msg,
    n_id,
    node_type,
    sketch_version,
    out_id,
    out_child_id,
    out_on_trigger,
    out_payload,
    enable_outgoing,
):
    global mqtt_sent
    global mysensor_sent
    global gpio_sent
    global minute_timer
    global clear_minute_timer
    global hour_timer
    global clear_hour_timer

    # node-id ; child-sensor-id ; command ; ack ; type ; payload \n
    if node_type.find("MySensor") != -1 and enable_outgoing == 1:  # process normal node
        if time.time() - minute_timer <= 60:
            mysensor_sent += 1
            clear_minute_timer = False
        else:
            mysensor_sent = 0
            clear_minute_timer = True
        if gatewaytype.find("serial") != -1:
            gw.write(
                msg.encode("utf-8")
            )  # !!!! send it to serial (arduino attached to rPI by USB port)
        elif gatewaytype.find("wifi") != -1:
            print("write")
            gw.sendall(msg.encode("utf-8"))
        cur.execute(
            "UPDATE `messages_out` set sent=1 where id=%s", [out_id]
        )  # update DB so this message will not be processed in next loop
        con.commit()  # commit above
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        if sketch_version < 34: # relay controller pre sketch ver 0.34 do not use 'heartbeat'
            cur.execute(
                "UPDATE `nodes` SET `last_seen`=%s, `sync`=0 WHERE id = %s",
                [timestamp, n_id],
            )
            con.commit()
    elif node_type.find("GPIO") != -1 and blinka:  # process GPIO mode
        if time.time() - minute_timer <= 60 and not clear_minute_timer:
            gpio_sent += 1
        else:
            gpio_sent = 0
            clear_minute_timer = True
        child_id = str(out_child_id)
        if child_id in pindict:  # check if pin exists for this board
            pin_num = pindict[child_id]  # get pin identification
            if (
                out_child_id not in relay_dict.keys()
            ):  # if first time this GPIO is processed then add id to dictionary and configure output
                relay_name = "relay" + child_id
                relay_name = digitalio.DigitalInOut(getattr(board, pin_num))
                relay_name.direction = digitalio.Direction.OUTPUT
                relay_dict[out_child_id] = relay_name
            relay_name = relay_dict[
                out_child_id
            ]  # retrieve pin identification for this pin from dictionary
            # set pin state
            if int(out_payload) == out_on_trigger:
                relay_name.value = True
            else:
                relay_name.value = False
            cur.execute(
                "UPDATE `messages_out` set sent=1 where id=%s", [out_id]
            )  # update DB so this message will not be processed in next loop
            con.commit()  # commit above
            timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            cur.execute(
                "UPDATE `nodes` SET `last_seen`=%s, `sync`=0 WHERE id = %s",
                [timestamp, n_id],
            )
            con.commit()
    elif (
        node_type.find("Tasmota") != -1 and network_found == 1 and enable_outgoing == 1
    ):  # only process Sonoff device if connected to the local wlan
        # process the Sonoff device HTTP action
        url = "http://" + net_gw + str(out_child_id) + "/cm"
        cmd = out_payload.split(" ")[0].upper()
        param = out_payload.split(" ")[1]
        myobj = {"cmnd": str(out_payload)}
        try:
            x = requests.post(url, data=myobj)  # send request to Sonoff device
            if x.status_code == 200:
                if x.json().get(cmd) == param:  # clear send if response is okay
                    cur.execute("UPDATE `messages_out` set sent=1 where id=%s", [out_id])
                    con.commit()  # commit above
                    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                    cur.execute(
                        "UPDATE `nodes` SET `last_seen`=%s, `sync`=0 WHERE id = %s",
                        [timestamp, n_id],
                    )
                    con.commit()
        except:
           print("\nUnable to communicate with: %s" % url[0:-3])
    elif node_type.find("MQTT") != -1 and MQTT_CONNECTED == 1:  # process MQTT mode
        cur.execute(
            'SELECT `mqtt_topic`, `on_payload`, `off_payload`  FROM `mqtt_devices` WHERE `type` = "1" AND `nodes_id` = (%s) AND `child_id` = (%s) LIMIT 1',
            [n_id, out_child_id],
        )
        if cur.rowcount > 0:
            if time.time() - hour_timer <= 60*60 and not clear_hour_timer:
                mqtt_sent += 1
            else :
                mqtt_sent = 0
                clear_hour_timer = True
            results_mqtt_r = cur.fetchone()
            description_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
            mqtt_topic = results_mqtt_r[description_to_index["mqtt_topic"]]
            if int(out_payload) == out_on_trigger:
                payload_str = results_mqtt_r[description_to_index["on_payload"]]
            else:
                payload_str = results_mqtt_r[description_to_index["off_payload"]]
            print("\nSending the following MQTT Message:")
            print("Topic: %s" % mqtt_topic)
            print("Message: %s" % payload_str)
            mqttClient.publish(
                topic=mqtt_topic,
                payload=payload_str,
                qos=1,
                retain=False,
            )
            cur.execute(
                "UPDATE `messages_out` set sent=1 where id=%s", [out_id]
            )  # update DB so this message will not be processed in next loop
            con.commit()  # commit above
            timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            cur.execute(
                "UPDATE `nodes` SET `last_seen`=%s, `sync`=0 WHERE id = %s",
                [timestamp, n_id],
            )
            con.commit()
    elif node_type.find("Dummy") != -1 :  # process Dummy mode
        cur.execute(
            "UPDATE `messages_out` set sent=1 where id=%s", [out_id]
        )  # update DB so this message will not be processed in next loop
        con.commit()  # commit above
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        cur.execute(
            "UPDATE `nodes` SET `last_seen`=%s, `sync`=0 WHERE id = %s",
            [timestamp, n_id],
        )
        con.commit()
    # add a log record for relay changes, check that this is a relay node
    cur.execute(
        "SELECT `id`, `name`, `type` FROM `relays` WHERE relay_id = (%s) AND relay_child_id = (%s) LIMIT 1",
        (n_id, out_child_id),
    )
    if cur.rowcount > 0:
        if node_type.find("Tasmota") != -1:
            relay_msg = out_payload
        else :
            if int(out_payload) == int(out_on_trigger) :
                relay_msg = "ON"
            else :
                relay_msg = "OFF"
        relay = cur.fetchone()
        relay_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
        relay_id = relay[relay_to_index["id"]]
        relay_name = relay[relay_to_index["name"]]
        relay_type = relay[relay_to_index["type"]]
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        cur.execute(
            """SELECT zone_current_state.mode, zr.zone_id, z.name, zr.zone_relay_id
               FROM zone_current_state
               JOIN zone_relays zr ON zone_current_state.zone_id = zr.zone_id
               JOIN zone z ON zr.zone_id = z.id
               WHERE zr.zone_relay_id = (%s)
               AND (zone_current_state.status = 1 OR zone_current_state.status_prev = 1);""",
            (relay_id,),
        )
        if cur.rowcount > 0:
            mode = cur.fetchone()
            mode_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
            zone_mode = mode[mode_to_index["mode"]]
            zone_name = mode[mode_to_index["name"]]
            main_mode = floor(zone_mode/10)*10
            sub_mode = floor(zone_mode%10)
            mode_msg = main_mode_dict[main_mode] + " - " + sub_mode_dict[sub_mode]
            cur.execute(
                "SELECT `message` FROM `relay_logs` WHERE relay_id = (%s) ORDER BY id DESC LIMIT 1",
                (relay_id,),
            )
            if cur.rowcount > 0:
                last_message = cur.fetchone()
                last_message_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                l_message = last_message[last_message_to_index["message"]]
                if str(l_message).strip() != str(relay_msg).strip() :
                    cur.execute(
                        "INSERT INTO relay_logs(`sync`, `purge`, `relay_id`, `relay_name`, `message`, `zone_name`, `zone_mode`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s,%s)",
                        (0, 0, relay_id, relay_name, relay_msg, zone_name, mode_msg, timestamp),
                    )
            else:
                cur.execute(
                    "INSERT INTO relay_logs(`sync`, `purge`, `relay_id`, `relay_name`, `message`, `zone_name`, `zone_mode`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s,%s)",
                    (0, 0, relay_id, relay_name, relay_msg, zone_name, mode_msg, timestamp),
                )
            con.commit()
        elif relay_type == 1 : #Boiler relay
            cur.execute(
                "SELECT `message` FROM `relay_logs` WHERE relay_id = (%s) ORDER BY id DESC LIMIT 1",
                (relay_id,),
            )
            if cur.rowcount > 0:
                last_message = cur.fetchone()
                last_message_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                l_message = last_message[last_message_to_index["message"]]
                if str(l_message).strip() != str(relay_msg).strip() :
                    cur.execute(
                        "INSERT INTO relay_logs(`sync`, `purge`, `relay_id`, `relay_name`, `message`, `zone_name`, `zone_mode`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s,%s)",
                        (0, 0, relay_id, relay_name, relay_msg, 'System Controller', 'State Change', timestamp),
                    )
            else:
                cur.execute(
                    "INSERT INTO relay_logs(`sync`, `purge`, `relay_id`, `relay_name`, `message`, `zone_name`, `zone_mode`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s,%s)",
                    (0, 0, relay_id, relay_name, relay_msg, 'System Controller', 'State Change', timestamp),
                )
            con.commit()

# MQTT specific functions
# Function run when the MQTT client connect to the brooker
def on_connect(client, userdata, flags, rc):
    if rc == 0:
        MQTT_CONNECTED = 1
        print("\nConnected to broker")
        subscribe_topics = []
        cur_mqtt.execute(
            'SELECT DISTINCT `mqtt_topic` FROM `mqtt_devices` WHERE `type` = "0"'
        )
        if cur_mqtt.rowcount > 0:
            for node in cur_mqtt.fetchall():
                subscribe_topics.append((f"{node[0]}", 0))
            client.subscribe(subscribe_topics)
            print("Subscribed to the followint MQTT topics:")
            for topic in subscribe_topics:
                print(topic[0])
        else:
            print("\nConnection failed\n")
            MQTT_CONNECTED = 0
    else:
        print("\nConnection failed\n")
        MQTT_CONNECTED = 0


# Function run when the MQTT client disconnects to the brooker
def on_disconnect(client, userdata, rc):
    MQTT_CONNECTED = 0
    con_mqtt.close()
    if rc != 0:
        print("\nUnexpected disconnection.\n")
        cmd = 'sudo pkill -f gateway.py'
        os.system(cmd)
    else:
        print("\nSuccessfully disconnected from the brooker\n")


# To be run when an MQTT message is received to write the sensor value into messages_in
def on_message(client, userdata, message):
    if not os.path.isfile("/tmp/db_cleanup_running"):
        global mqtt_msgcount
        global clear_hour_timer
        if time.time() - hour_timer <= 60*60:
            mqtt_msgcount += 1
            clear_hour_timer = False
        else:
            mqtt_msgcount = 0
            clear_hour_timer = True
        print("\nMQTT messaged received.")
        print("Topic: %s" % message.topic)
        print("Message: %s" % message.payload.decode())
        cur_mqtt.execute(
            """SELECT `nodes`.id, `nodes`.node_id, `mqtt_devices`.id AS mqtt_id, `mqtt_devices`.child_id, `mqtt_devices`.attribute, `mqtt_devices`.min_value
               FROM `mqtt_devices`, `nodes`
               WHERE `mqtt_devices`.nodes_id = `nodes`.id AND `mqtt_devices`.type = 0 AND `mqtt_devices`.mqtt_topic = (%s)""",
            [message.topic],
        )
        on_msg_description_to_index = dict(
            (d[0], i) for i, d in enumerate(cur_mqtt.description)
        )
        for child in cur_mqtt.fetchall():
            sensors_id = child[on_msg_description_to_index["id"]]
            mqtt_id = child[on_msg_description_to_index["mqtt_id"]]
            mqtt_node_id = child[on_msg_description_to_index["node_id"]]
            mqtt_child_sensor_id = int(child[on_msg_description_to_index["child_id"]])
            mqtt_min_value = child[on_msg_description_to_index["min_value"]]
            # Update node last seen
            timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            try:
                cur_mqtt.execute(
                    'UPDATE `nodes` SET `last_seen`= %s WHERE `node_id`= %s',
                    [timestamp, mqtt_node_id],
                )
                con_mqtt.commit()
            except mdb.Error as e:
                # skip deadlock error (being caused when mysqldunp runs
                if e.args[0] == 1213:
                    pass
                else:
                    print("DB Error %d: %s" % (e.args[0], e.args[1]))
                    print(traceback.format_exc())
                    logging.error(e)
                    logging.info(traceback.format_exc())
                    con.close()
                    if MQTT_CONNECTED == 1:
                        mqttClient.disconnect()
                        mqttClient.loop_stop()
                    print(infomsg)
                    sys.exit(1)

            # Update the mqtt_devices last seen
            cur_mqtt.execute(
                'UPDATE `mqtt_devices` SET `last_seen` = %s WHERE `id` = %s',
                [timestamp, mqtt_id],
            )
            con_mqtt.commit()
            # Process incomming STATE change messages for switches toggled by an external agent
            if fnmatch.fnmatch(message.topic, '*/STATE*'):
                mqtt_payload = mqtt_payload = json.loads(message.payload.decode())
                for e in mqtt_payload: # iterator over a dictionary
                    if fnmatch.fnmatch(e, 'POWER*'):
                        if child[on_msg_description_to_index["attribute"]] == e:
                            mqtt_payload = mqtt_payload.get(e)
                            cur_mqtt.execute(
                                'SELECT payload FROM messages_out WHERE node_id = %s AND child_id = %s LIMIT 1;',
                                [mqtt_node_id, mqtt_child_sensor_id],
                            )
                            result = cur_mqtt.fetchone()
                            if cur_mqtt.rowcount > 0:
                                mqtt_message_to_index = dict(
                                    (d[0], i) for i, d in enumerate(cur_mqtt.description)
                                )
                                current_state = result[mqtt_message_to_index["payload"]]
    #                            print(current_state,mqtt_payload)
                                if (current_state == "0" and mqtt_payload == "ON") or (current_state == "1" and mqtt_payload == "OFF"):
                                    if dbgLevel >= 2 and dbgMsgIn == 1:
                                         print(
                                             "17: Update MQTT Switch STATE Node ID:",
                                             mqtt_node_id,
                                             " Child Sensor ID:",
                                              mqtt_child_sensor_id,
                                             " PayLoad:",
                                             mqtt_payload,
                                         )
                                    if mqtt_payload == "ON":
                                        new_payload = "1"
                                    else:
                                        new_payload = "0"
                                    # Get previous data for this controller
                                    cur_mqtt.execute(
                                        """SELECT relays.relay_id, zone_relays.zone_id
                                           FROM zone_relays, relays
                                           WHERE (relays.id = zone_relays.zone_relay_id) AND relays.relay_id = %s AND relays.relay_child_id = %s LIMIT 1;""",
                                        [sensors_id, mqtt_child_sensor_id],
                                    )
                                    result = cur_mqtt.fetchone()
                                    if cur_mqtt.rowcount > 0:
                                        mqtt_relay_to_index = dict(
                                            (d[0], i) for i, d in enumerate(cur_mqtt.description)
                                        )
                                        mqtt_zone_id = result[mqtt_relay_to_index["zone_id"]]
                                        cur_mqtt.execute(
                                            'SELECT id, mode, status FROM zone_current_state WHERE zone_id = %s LIMIT 1;',
                                            [mqtt_zone_id],
                                        )
                                        result = cur_mqtt.fetchone()
                                        if cur_mqtt.rowcount > 0:
                                            mqtt_zone_state_to_index = dict(
                                                (d[0], i) for i, d in enumerate(cur_mqtt.description)
                                            )
                                            zone_current_state_id = result[mqtt_zone_state_to_index["id"]]
                                            zone_current_status = result[mqtt_zone_state_to_index["status"]]
                                            zone_mode = result[mqtt_zone_state_to_index["mode"]]
                                            main_mode = floor(zone_mode/10)*10
                                            if main_mode == 80:
                                                if new_payload == "0":
                                                    new_mode = 75
                                                    new_status = 0
                                                else:
                                                    new_mode = 74
                                                    new_status = 1
                                            else:
                                                if new_payload == "0":
                                                    new_mode = 0
                                                    new_status = 0
                                                else:
                                                    new_mode = 114
                                                    new_status = 1
                                            # Update the zone_current_state table
                                            cur_mqtt.execute(
                                                "UPDATE zone_current_state SET mode = %s, status = %s, status_prev = %s  WHERE id = %s;",
                                                (new_mode, new_status, zone_current_status, zone_current_state_id,),
                                            )
                                            con.commit()
                                            # Update the messages_out table
                                            cur_mqtt.execute(
                                                "UPDATE messages_out SET payload = %s  WHERE n_id = %s AND child_id = %s",
                                                (new_payload, sensors_id, mqtt_child_sensor_id,),
                                            )
                                            con.commit()
                                            # Update the zone_relays table
                                            cur_mqtt.execute(
                                                "UPDATE zone_relays SET state = %s WHERE zone_id = %s;",
                                                (new_status, mqtt_zone_id,),
                                            )
                                            con.commit()
                                            # Update the zone table
                                            cur_mqtt.execute(
                                                "UPDATE zone SET zone_state = %s WHERE id = %s;",
                                                (new_status, mqtt_zone_id,),
                                            )
                                            con.commit()
            else:
                # Process incomming Sensor messages
                if child[on_msg_description_to_index["attribute"]] == "":
                    mqtt_payload = float(message.payload.decode())
                    str_attribute = "Temperature"
                else:
                    mqtt_payload = json.loads(message.payload.decode())
                    attribute = child[on_msg_description_to_index["attribute"]]
                    mqtt_payload = deep_get(mqtt_payload, attribute)
                    if attribute.find(".") != -1:
                        str_attribute = attribute.split(".")[1]
                    else:
                        str_attribute = attribute
                if mqtt_payload is not None:
                    # Get reading type (continous or on-change)
                    cur_mqtt.execute(
                        'SELECT sensor_type_id, mode, timeout, correction_factor, resolution FROM sensors WHERE sensor_id = %s AND sensor_child_id = %s LIMIT 1;',
                        [sensors_id, mqtt_child_sensor_id],
                    )
                    if cur_mqtt.rowcount > 0:
                        result = cur_mqtt.fetchone()
                        sensor_to_index = dict(
                            (d[0], i) for i, d in enumerate(cur_mqtt.description)
                        )
                        sensor_type_id = result[sensor_to_index["sensor_type_id"]]
                        mode = result[sensor_to_index["mode"]]
                        sensor_timeout = int(result[sensor_to_index["timeout"]])*60
                        tdelta = 0
                        last_message_payload = 0
                        resolution = float(result[sensor_to_index["resolution"]])
                        correction_factor = float(result[sensor_to_index["correction_factor"]])
                        mqtt_payload = mqtt_payload + correction_factor
                        # Update last reading for this sensor
                        cur_mqtt.execute(
                            "UPDATE `sensors` SET `current_val_1` = %s WHERE sensor_id = %s AND sensor_child_id = %s;",
                            [mqtt_payload, sensors_id, mqtt_child_sensor_id],
                        )
                        con_mqtt.commit()
                        # Check is sensor is attached to a zone which is being graphed
                        cur_mqtt.execute(
                            """SELECT sensors.id, sensors.zone_id, nodes.node_id, sensors.sensor_child_id, sensors.name, sensors.graph_num, sensors.message_in, sensors.sensor_type_id
                               FROM sensors, `nodes`
                               WHERE (sensors.sensor_id = nodes.`id`) AND  nodes.node_id = %s AND sensors.sensor_child_id = %s LIMIT 1;""",
                            [mqtt_node_id, mqtt_child_sensor_id],
                        )
                        if cur_mqtt.rowcount > 0:
                            results = cur_mqtt.fetchone()
                            mqtt_sensor_to_index = dict(
                                (d[0], i) for i, d in enumerate(cur_mqtt.description)
                            )
                            mqtt_sensor_id = int(results[mqtt_sensor_to_index["id"]])
                            mqtt_sensor_name = results[mqtt_sensor_to_index["name"]]
                            mqtt_zone_id = results[mqtt_sensor_to_index["zone_id"]]
                            mqtt_sensor_type_id = results[mqtt_sensor_to_index["sensor_type_id"]]
                            # type = results[zone_view_to_index['type']]
                            # category = int(results[zone_view_to_index['category']])
                            mqtt_graph_num = int(results[mqtt_sensor_to_index["graph_num"]])
                            mqtt_msg_in = int(results[mqtt_sensor_to_index["message_in"]])
                            # sensor exists and it is required to update the messages_in table
                            if mqtt_msg_in == 1:
                                if mode == 1:
                                    # Get previous data for this sensorr
                                    cur_mqtt.execute(
                                        'SELECT datetime, payload FROM messages_in_view_24h WHERE node_id = %s AND child_id = %s ORDER BY id DESC LIMIT 1;',
                                        [mqtt_node_id, mqtt_child_sensor_id],
                                    )
                                    results = cur_mqtt.fetchone()
                                    if cur_mqtt.rowcount > 0:
                                        mqtt_message_to_index = dict(
                                            (d[0], i) for i, d in enumerate(cur_mqtt.description)
                                        )
                                        last_message_datetime = results[mqtt_message_to_index["datetime"]]
                                        last_message_payload = float(results[mqtt_message_to_index["payload"]])
                                        tdelta = datetime.strptime(timestamp, "%Y-%m-%d %H:%M:%S").timestamp() -  datetime.strptime(str(last_message_datetime), "%Y-%m-%d %H:%M:%S").timestamp()
                                if mode == 0 or (cur_mqtt.rowcount == 0 or (cur_mqtt.rowcount > 0 and ((mqtt_payload < last_message_payload - resolution or mqtt_payload > last_message_payload + resolution) or tdelta > sensor_timeout))):
                                    if sensor_timeout > 0 and tdelta > sensor_timeout:
                                        mqtt_payload = last_message_payload
                                    if dbgLevel >= 2 and dbgMsgIn == 1:
                                        print(
                                            "5: Adding " + str_attribute + " Reading From Node ID:",
                                            mqtt_node_id,
                                            " Child Sensor ID:",
                                            mqtt_child_sensor_id,
                                            " PayLoad:",
                                            mqtt_payload,
                                        )
                                    cur_mqtt.execute(
                                        "INSERT INTO `messages_in`(`node_id`, `sync`, `purge`, `child_id`, `sub_type`, `payload`, `datetime`) VALUES (%s, 0, 0, %s, 0, %s, %s)",
                                        [mqtt_node_id, mqtt_child_sensor_id, mqtt_payload, timestamp],
                                    )
                                    con_mqtt.commit()
                            # Check is sensor is attached to a zone which is being graphed
                            if  mqtt_sensor_type_id == 1 and mqtt_graph_num > 0:
                                if mode == 1:
                                    # Get previous data for this sensorr
                                    cur_mqtt.execute(
                                        'SELECT datetime, payload FROM sensor_graphs WHERE node_id = %s AND child_id = %s ORDER BY id DESC LIMIT 1;',
                                        [mqtt_node_id, mqtt_child_sensor_id],
                                    )
                                    results = cur_mqtt.fetchone()
                                    if cur_mqtt.rowcount > 0:
                                        mqtt_message_to_index = dict(
                                            (d[0], i) for i, d in enumerate(cur_mqtt.description)
                                        )
                                        last_message_datetime = results[mqtt_message_to_index["datetime"]]
                                        last_message_payload = float(results[mqtt_message_to_index["payload"]])
                                        tdelta = datetime.strptime(timestamp, "%Y-%m-%d %H:%M:%S").timestamp() -  datetime.strptime(str(last_message_datetime), "%Y-%m-%d %H:%M:%S").timestamp()
                                if mode == 0 or (cur_mqtt.rowcount == 0 or (cur_mqtt.rowcount > 0 and ((mqtt_payload < last_message_payload - resolution or mqtt_payload > last_message_payload + resolution) or tdelta > sensor_timeout))):
                                    if sensor_timeout > 0 and tdelta > sensor_timeout:
                                        mqtt_payload = last_message_payload
                                    cur_mqtt.execute("SELECT c_f FROM system LIMIT 1")
                                    row = cur_mqtt.fetchone()
                                    system_to_index = dict((d[0], i) for i, d in enumerate(cur_mqtt.description))
                                    c_f = row[system_to_index["c_f"]]  # 0 = centigrade, 1 = fahrenheit
                                    if c_f:
                                        mqtt_payload = round((mqtt_payload * 9/5) + 32, 1)
                                    if dbgLevel >= 2 and dbgMsgIn == 1:
                                        print(
                                            "5a: Adding Temperature Reading to Graph Table From Node ID:",
                                            mqtt_node_id,
                                            " Child Sensor ID:",
                                            mqtt_child_sensor_id,
                                            " PayLoad:",
                                            mqtt_payload,
                                        )
                                    if mqtt_zone_id == 0:
                                        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                                        cur_mqtt.execute(
                                            """INSERT INTO sensor_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`, `datetime`)
                                               VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)""",
                                            (
                                                0,
                                                0,
                                                mqtt_sensor_id,
                                                mqtt_sensor_name,
                                                "Sensor",
                                                0,
                                                mqtt_node_id,
                                                mqtt_child_sensor_id,
                                                0,
                                                mqtt_payload,
                                                timestamp,
                                            ),
                                        )
                                        con_mqtt.commit()
                                    else:
                                        cur_mqtt.execute(
                                            'SELECT * FROM `zone_view` where id = %s LIMIT 1;',
                                            [mqtt_zone_id],
                                        )
                                        results = cur_mqtt.fetchone()
                                        if cur_mqtt.rowcount > 0:
                                            mqtt_zone_view_to_index = dict(
                                                (d[0], i) for i, d in enumerate(cur_mqtt.description)
                                            )
                                            mqtt_zone_name = results[mqtt_zone_view_to_index["name"]]
                                            mqtt_type = results[mqtt_zone_view_to_index["type"]]
                                            mqtt_category = int(
                                                results[mqtt_zone_view_to_index["category"]]
                                            )
                                            mqtt_zone_sensors_id = results[mqtt_zone_view_to_index["sensors_id"]]

                                            if mqtt_zone_sensors_id is not None:
                                                timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                                                cur_mqtt.execute(
                                                    """INSERT INTO sensor_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`,
                                                       `datetime`)
                                                       VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)""",
                                                    (
                                                        0,
                                                        0,
                                                        mqtt_sensor_id,
                                                        mqtt_zone_name,
                                                        mqtt_type,
                                                        mqtt_category,
                                                        mqtt_node_id,
                                                        mqtt_child_sensor_id,
                                                        0,
                                                        mqtt_payload,
                                                        timestamp,
                                                    ),
                                                )
                                                con_mqtt.commit()
                            elif mqtt_sensor_type_id == 2:
                                if dbgLevel >= 2 and dbgMsgIn == 1:
                                    print(
                                        "6a: Adding Humidity Reading to Graph Table From Node ID:",
                                        mqtt_node_id,
                                        " Child Sensor ID:",
                                        mqtt_child_sensor_id,
                                        " PayLoad:",
                                        mqtt_payload,
                                    )
                                if mqtt_zone_id == 0:
                                    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                                    cur_mqtt.execute(
                                        """INSERT INTO sensor_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`, `datetime`)
                                           VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)""",
                                        (
                                            0,
                                            0,
                                            mqtt_sensor_id,
                                            mqtt_sensor_name,
                                            "Sensor",
                                            0,
                                            mqtt_node_id,
                                            mqtt_child_sensor_id,
                                            0,
                                            mqtt_payload,
                                            timestamp,
                                        ),
                                    )
                                    con_mqtt.commit()
                                else:
                                    cur_mqtt.execute(
                                        'SELECT * FROM `zone_view` where id = %s LIMIT 1;',
                                        [mqtt_zone_id],
                                    )
                                    results = cur_mqtt.fetchone()
                                    if cur_mqtt.rowcount > 0:
                                        mqtt_zone_view_to_index = dict(
                                            (d[0], i) for i, d in enumerate(cur_mqtt.description)
                                        )
                                        mqtt_zone_name = results[mqtt_zone_view_to_index["name"]]
                                        mqtt_type = results[mqtt_zone_view_to_index["type"]]
                                        mqtt_category = int(
                                            results[mqtt_zone_view_to_index["category"]]
                                        )
                                        if mqtt_category != 2:
                                            timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                                            cur_mqtt.execute(
                                                """INSERT INTO sensor_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`,
                                                   `datetime`)
                                                   VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)""",
                                               (
                                                    0,
                                                    0,
                                                    mqtt_sensor_id,
                                                    mqtt_zone_name,
                                                    mqtt_type,
                                                    mqtt_category,
                                                    mqtt_node_id,
                                                    mqtt_child_sensor_id,
                                                    0,
                                                    mqtt_payload,
                                                    timestamp,
                                                ),
                                            )
                                            con_mqtt.commit()

                    # Check if MQTT Device has min_value set, if so then store the battery level for this device
                    if mqtt_min_value != 0:
                        # Create a battery attribute
                        if child[on_msg_description_to_index["attribute"]].find(".") != -1:
                            battery_attribute = child[on_msg_description_to_index["attribute"]].split(".")[0] + ".Battery"
                        else:
                            battery_attribute = "Battery"
                        # Re-get the MQTT message
                        mqtt_payload = json.loads(message.payload.decode())
                        # Get the battery level value
                        mqtt_payload = deep_get(mqtt_payload, battery_attribute)
                        if  mqtt_payload is not None:
                            bat_voltage = 3 * (mqtt_payload/100)
                            bat_level = mqtt_payload
                            bat_update = False
                            mqtt_bat_id = mqtt_node_id + "-" + str(mqtt_child_sensor_id)
                            cur_mqtt.execute(
                                'SELECT * FROM `battery` where node_id = %s LIMIT 1;',
                                [mqtt_bat_id],
                            )
                            if cur_mqtt.rowcount == 0:
                                bat_update = True
                                if dbgLevel >= 2 and dbgMsgIn == 1:
                                    print(
                                        "9c: Adding Battery for MQTT Device:",
                                        mqtt_bat_id,
                                    )
                                cur_mqtt.execute(
                                    "INSERT INTO `battery`(`node_id`) VALUES (%s)",
                                    [mqtt_bat_id,],
                                )
                                con_mqtt.commit()
                            else:
                                cur_mqtt.execute(
                                    'SELECT `bat_level`, `update` FROM `nodes_battery` where `node_id` = %s AND `update` > DATE_SUB( NOW(), INTERVAL 24 HOUR) ORDER BY id DESC LIMIT 1;',
                                    [mqtt_bat_id],
                                )
                                if cur_mqtt.rowcount == 0:
                                    bat_update = True
                                else:
                                    row = cur_mqtt.fetchone()
                                    row_to_index = dict(
                                        (d[0], i) for i, d in enumerate(cur_mqtt.description)
                                    )
                                    if row[row_to_index["bat_level"]] != bat_level:
                                        bat_update = True
                            if bat_update:
                                if dbgLevel >= 2 and dbgMsgIn == 1:
                                    print(
                                        "9: Adding Battery Voltage & Level for MQTT Device:",
                                        mqtt_bat_id,
                                        "Battery Voltage:",
                                        bat_voltage,
                                        "Battery Level:",
                                        bat_level,
                                    )
                                timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                                cur_mqtt.execute(
                                    "INSERT INTO nodes_battery(`sync`, `purge`, `node_id`, `bat_voltage`, `bat_level`, `update`) VALUES(%s,%s,%s,%s,%s,%s)",
                                    (0, 0, mqtt_bat_id, bat_voltage, bat_level, timestamp),
                                )
                                ##cur.execute('UPDATE `nodes` SET `last_seen`=now() WHERE node_id = %s', [node_id])
                                con_mqtt.commit()

                    if dbgLevel >= 2 and dbgMsgIn == 1:
                        print(
                            "5b: MQTT Sensor Processed on Node ID:",
                            mqtt_node_id,
                            " Child Sensor ID:",
                            mqtt_child_sensor_id,
                        )

class ProgramKilled(Exception):
    pass


def signal_handler(signum, frame):
    raise ProgramKilled("Program killed: running cleanup code")

# define Python user-defined exceptions
class GatewayException(Exception):
    pass

# threading process to read from socket until newline and then add string to the FIFO buffer
def socket_handler(sock,buf):
    global heartbeat_timer
    dow = -1
    while True:
        try:
            sock.settimeout(None)
            # read from the socket 1 byte at a time and add character to in_str buffer, until a newline character
            in_str = ''
            while True:
                data = sock.recv(1).decode("utf-8")
                in_str += data
                if data == "\n":
                    break
            # add newline terminated string to the FIFO queue
            buf.put(in_str)

            # if a heatbeat message then reset the timer
            if in_str.find("Heartbeat") != -1:
                timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                heartbeat_delta = time.time() - heartbeat_timer
                heartbeat_timer = time.time()
                log_txt = timestamp + " Heartbeat Recieved after: " + str(round(heartbeat_delta,1)) + " seconds."
                # clear log file at midnight or append
                if datetime.now().weekday() != dow:
                    dow = datetime.now().weekday()
                    with open('/var/www/logs/gateway_heartbeat.log', 'w') as f:
                        f.write(log_txt + "\n")
                else:
                    with open('/var/www/logs/gateway_heartbeat.log', 'a') as f:
                        f.write(log_txt + "\n")
                f.close()
        except:
            # Restart
            pass
    sock.close()

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
    cur.execute("SELECT test_mode FROM system LIMIT 1")
    row = cur.fetchone()
    system_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
    test_mode = row[system_to_index["test_mode"]]
    cur.execute("SELECT * FROM gateway where status = 1 order by id asc limit 1")
    row = cur.fetchone()
    gateway_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
    gatewaytype = row[gateway_to_index["type"]]  # serial/wifi
    gatewaylocation = row[
        gateway_to_index["location"]
    ]  # ip address or serial port of your MySensors gateway
    gatewayport = row[
        gateway_to_index["port"]
    ]  # UDP port or bound rate for MySensors gateway

    gatewaytimeout = int(
        row[gateway_to_index["timout"]]
    )  # Interface Connection timeout in Minutes

    gatewayheartbeat = int(
        row[gateway_to_index["heartbeat_timeout"]]
    )  # Heartbeat timeout in Seconds

    gatewayenableoutgoing = int(
        row[gateway_to_index["enable_outgoing"]]
    )  # Flag to indicate if outgoing messages sgould be processed

    #check for gateway/controller, that sens a Heartbeat message
    cur.execute(
        "SELECT COUNT(*) FROM `nodes` WHERE `node_id` = '0' AND `name` LIKE '%Gateway%' AND `sketch_version` > 0.35"
    )  # MySQL query statement
    count = cur.fetchone()  # Grab all messages from database for Outgoing.
    count = count[
        0
    ]  # Parse first and the only one part of data table named "count" - there is number of records grabbed in SELECT above
    if count >0:
        gateway_v2 = True
    else:
        gateway_v2 = False

    if gatewaytype == "serial":
        # ps. you can troubleshoot with "screen"
        # screen /dev/ttyAMA0 115200
        # gw = serial.Serial('/dev/ttyMySensorsGateway', 115200, timeout=0)
        gw = serial.Serial(gatewaylocation, gatewayport, timeout=5)
        print(bc.grn + "Gateway Type:  Serial", bc.ENDC)
        print(bc.grn + "Serial Port:   ", gatewaylocation, bc.ENDC)
        print(bc.grn + "Baud Rate:     ", gatewayport, bc.ENDC)
    elif gatewaytype == "wifi":
        # MySensors Wifi/Ethernet Gateway Manuall override to specific ip Otherwise ip from MySQL Database is used.
        # mysgw = "192.168.99.3" 	#ip address of your MySensors gateway
        # mysport = "5003" 		#UDP port number for MySensors gateway
        # telnetlib depricates in Python 3.11, replaced using socket library
        # gw = telnetlib.Telnet(mysgw, mysport, timeout=3) # Connect mysensors gateway
        gw = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        gw.settimeout(gatewaytimeout)
        gw.connect((gatewaylocation, int(gatewayport)))
        gw.setblocking(False)
        # setup fifo queue and start the treaded process for reading the socket
        fifo = queue.Queue()
        # read until newline implemented as a thtread. readin a string into a FIFO queue
        t = threading.Thread(target=socket_handler, args=(gw, fifo))
        # run as a deamon, so will terminate along with main
        t.daemon = True
        t.start() # start the thread and continue
        print(bc.grn + "Gateway Type      : Wifi/Ethernet", bc.ENDC)
        print(bc.grn + "IP Address        :", gatewaylocation, bc.ENDC)
        print(bc.grn + "UDP Port          :", gatewayport, bc.ENDC)
        if gateway_v2:
            print(bc.grn + "Heartbeat Timeout :", gatewayheartbeat, "Seconds", bc.ENDC)
    else:
        print(bc.grn + "Gateway Type:  Virtual", bc.ENDC)

    msgcount = 0  # Defining variable for counting messages processed
    mqtt_msgcount = 0

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

    # Check if the MQTT option is enabled
    cur.execute("SELECT * FROM `mqtt` where `type` = 2 AND `enabled` = 1;")
    if cur.rowcount > 0:
        if cur.rowcount > 1:
            # If more than one MQTT connection has been defined do not connect
            print(
                "More than one MQTT connection defined in MaxAir for MQTT Nodes, please remove the unused ones."
            )
            MQTT_CONNECTED = 0
        else:
            try:
                import paho.mqtt.client as mqtt
                import json
                import signal
                from functools import reduce
            except ImportError:
                print(
                    "Missing MQTT dependencies, MQTT nodes cannot be enabled. Please install the required dependencies using /add_on/MQTT_dependencies/install.sh"
                )
                MQTT_CONNECTED = 0
            else:
                print("Setting up MQTT")
                con_mqtt = mdb.connect(dbhost, dbuser, dbpass, dbname)
                cur_mqtt = con_mqtt.cursor()
                MQTT_CLIENT_ID = "Gateway_MaxAir"  # MQTT Client ID
                results_mqtt = cur.fetchone()
                description_to_index = dict(
                    (d[0], i) for i, d in enumerate(cur.description)
                )
                MQTT_HOSTNAME = results_mqtt[description_to_index["ip"]]
                MQTT_PORT = results_mqtt[description_to_index["port"]]
                MQTT_USERNAME = results_mqtt[description_to_index["username"]]
                result = subprocess.run(
                    ['php', '/var/www/cron/mqtt_passwd_decrypt.php', '2'],         # program and arguments
                    stdout=subprocess.PIPE,                     # capture stdout
                    check=True                                  # raise exception if program fails
                )
                MQTT_PASSWORD = result.stdout.decode("utf-8").split()[0] # result.stdout contains a byte-string
                mqttClient = mqtt.Client(MQTT_CLIENT_ID)
                mqttClient.on_connect = on_connect  # attach function to callback
                mqttClient.on_disconnect = on_disconnect
                mqttClient.on_message = on_message
                mqttClient.username_pw_set(MQTT_USERNAME, MQTT_PASSWORD)
                signal.signal(signal.SIGTERM, signal_handler)
                signal.signal(signal.SIGINT, signal_handler)
                mqttClient.connect(MQTT_HOSTNAME, MQTT_PORT)
                mqttClient.loop_start()
                MQTT_CONNECTED = 1

    else:
        # If no MQTT connection has been defined do not connect
        print(
            "MQTT nodes are disabled. To enable MQTT nodes enter the connection details under Settings > System Configuration > MQTT."
        )
        MQTT_CONNECTED = 0

    # re-sync any setup relays
    cur.execute("SELECT COUNT(*) FROM `relays`")
    count = cur.fetchone()
    count = count[0]
    # process any relays present
    if count > 0:
        cur.execute(
            """SELECT distinct relays.`id`, relays.`relay_id`, relays.`relay_child_id`, relays.`on_trigger`, relays.`lag_time` FROM `relays`, system_controller, zone_relays
               WHERE (relays.id = zone_relays.zone_relay_id) OR (relays.id = system_controller.heat_relay_id) OR (relays.id = system_controller.cool_relay_id)
               OR (relays.id = system_controller.fan_relay_id);"""
        )
        relays = cur.fetchall()
        relay_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
        # get the last relay state from the messages_out table
        for x in relays:
            relays_id = x[relay_to_index["id"]]
            controler_id = x[relay_to_index["relay_id"]]
            out_child_id = x[relay_to_index["relay_child_id"]]
            out_on_trigger = x[relay_to_index["on_trigger"]]
            relay_lag = x[relay_to_index["lag_time"]]
            cur.execute(
                "SELECT `id`, `node_id`, `type`, `name`, `sketch_version` FROM `nodes` where id = (%s) LIMIT 1",
                (controler_id,),
            )
            nd = cur.fetchone()
            node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
            n_id = nd[node_to_index["id"]]
            node_id = nd[node_to_index["node_id"]]
            node_type = nd[node_to_index["type"]]
            node_name = nd[node_to_index["name"]]
            sketch_version = float(nd[node_to_index["sketch_version"]])
            if sketch_version > 0:
                sketch_version = int(sketch_version * 100)

            if (node_type.find("MQTT") != -1 and MQTT_CONNECTED == 1) or node_type.find("MQTT") == -1:
                cur.execute(
                    "SELECT `sub_type`, `ack`, `type`, `payload`, `id` FROM `messages_out` where n_id = (%s) AND child_id = (%s) ORDER BY id DESC LIMIT 1",
                    (n_id, out_child_id),
                )
                if cur.rowcount > 0:
                    msg = cur.fetchone()
                    msg_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                    out_id = int(msg[msg_to_index["id"]])
                    out_sub_type = msg[msg_to_index["sub_type"]]
                    out_ack = msg[msg_to_index["ack"]]
                    out_type = msg[msg_to_index["type"]]
                    db_payload = msg[msg_to_index["payload"]]
                    if db_payload == "1" and relay_lag != 0 and test_mode == 0:
                        # initialise the lag timer value and set the relay to the OFF state
                        relay_lag_timer[relays_id] = time.time()
                        out_payload = 0
                    else:
                        # initialise the lag timer dictionary key and value and set the relay state from the current database value
                        relay_lag_timer[relays_id] = 0
                        out_payload = db_payload
                    # action trigger setting for MySensor relays attached to the combined gateway/controller
                    if node_type.find("MySensor") != -1 and node_name.find("Controller") != -1 and sketch_version >= 34:
                        out_payload = XNOR(out_on_trigger, out_payload)
                    # catch any missing HTTP type messages
                    if node_type.find("Tasmota") != -1 and len(out_payload) == 1:
                        cur.execute("SELECT `command`, `parameter` FROM `http_messages` where node_id = (%s) AND message_type = 0 LIMIT 1", (node_id,))
                        if cur.rowcount > 0:
                            http_msg = cur.fetchone()
                            http_msg_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                            out_payload = http_msg[http_msg_to_index["command"]] + " " + http_msg[http_msg_to_index["parameter"]]
                        else:
                            out_payload = "Power OFF"
                        cur.execute(
                            "UPDATE messages_out SET payload = %s WHERE n_id = %s AND child_id = %s",
                            (out_payload, out_id, out_child_id),
                        )
                        con.commit()

                    msg = str(node_id)  # Node ID
                    msg += ";"  # Separator
                    msg += str(out_child_id)  # Child ID of the Node.
                    msg += ";"  # Separator
                    msg += str(out_sub_type)
                    msg += ";"  # Separator
                    msg += str(out_ack)
                    msg += ";"  # Separator
                    msg += str(out_type)
                    msg += ";"  # Separator
                    msg += str(out_payload)  # Payload from DB
                    msg += " \n"  # New line
                    set_relays(
                        msg,
                        n_id,
                        node_type,
                        sketch_version,
                        out_id,
                        out_child_id,
                        out_on_trigger,
                        out_payload,
                        gatewayenableoutgoing,
                    )

        ping_timer = time.time()
    else:
        ping_timer = time.time()

    # initialise heartbeat pulse to the gateway every 30 seconds
    wifi_gateway_heartbeat = time.time()
    heartbeat_timer = time.time()
    # check for relay controller using heartbeats
    cur.execute(
        "SELECT `node_id` FROM `nodes` where node_id > 0 AND `sketch_version` >= 0.34 AND `type` LIKE 'MySensor' AND (`name` LIKe '%Relay%' OR `name` LIKe '%Controller%');"
    )  # MySQL query statement
    if cur.rowcount > 0:
        # create dictionary for relay lag timer
        relay_controller_heartbeat_dict = dict()
        nodes = cur.fetchall()
        nodes_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
        for n in nodes:
            node_id = int(n[nodes_to_index["node_id"]])
            relay_controller_heartbeat_dict[node_id] = time.time()

    while 1:
        if not os.path.isfile("/tmp/db_cleanup_running"):
            #initialize the transaction acounters
            cur.execute("SELECT c_f, test_mode FROM system LIMIT 1")
            row = cur.fetchone()
            system_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
            c_f = row[system_to_index["c_f"]]  # 0 = centigrade, 1 = fahrenheit
            test_mode = row[system_to_index["test_mode"]]

            ## Terminate gateway script if no route to network gateway
            if gatewaytype == "wifi":
                if not gateway_v2 or gatewayheartbeat == 0:
                    if time.time() - ping_timer >= 60:
                        ping_timer = time.time()
                        gateway_up = (
                            True if os.system("ping -c 1 " + gatewaylocation) == 0 else False
                        )
                        if not gateway_up:
                            raise GatewayException("Unable to contact Gateway at: - " + gatewaylocation)
                else:
                    # For WT32-ETH01 Ver 2 Gateways, check that they are sending a regular Heartbeat message
                    if time.time() - heartbeat_timer >= gatewayheartbeat:
                        heartbeat_timer = time.time()
                        if dbgLevel >= 2 and dbgMsgIn == 1:
                            print(bc.grn + "\nNO Heatbeat Message from Gateway", bc.ENDC)
                        raise GatewayException("No Heartbeat from Gateway at: - " + gatewaylocation)

                    # Send heartbeat message to gateway every 30seconds
                    if time.time() - wifi_gateway_heartbeat >= 30:
                        wifi_gateway_heartbeat = time.time()
                        msg = "0;0;0;0;24;Gateway Script Heartbeat \n"
                        if dbgLevel >= 3 and dbgMsgOut == 1:
                            print(bc.grn + "\nHeatbeat Message to Gateway", bc.ENDC)
                            print("Date & Time:                 ", time.ctime())
                            print(
                                "Full Message to Send:        ", msg.replace("\n", "\\n")
                            )
                        gw.send(msg.encode("utf-8"))

            ## Outgoing messages
            con.commit()
            ## Heartbeat to Relay Controllers if script version >= 0.34
            heartbeat_sent = False
            cur.execute(
                "SELECT `node_id` FROM `nodes` where node_id > 0 AND `sketch_version` >= 0.34 AND `type` LIKE 'MySensor' AND (`name` LIKE '%Relay%' OR `name` LIKE '%Controller%');"
            )  # MySQL query statement
            nodes = cur.fetchall()
            if cur.rowcount > 0:
                nodes_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                for n in nodes:
                    node_id = int(n[nodes_to_index["node_id"]])
                    if time.time() - relay_controller_heartbeat_dict[node_id] >= 30:
                        relay_controller_heartbeat_dict[node_id] = time.time()
                        msg = str(node_id) + ";0;0;0;24;Relay Heartbeat \n"
                        if dbgLevel >= 3 and dbgMsgOut == 1:
                            print(bc.grn + "\nHeatbeat Message to Relay Controller Node ID - " + str(node_id), bc.ENDC)
                            print("Date & Time:                 ", time.ctime())
                            print(
                                "Full Message to Send:        ", msg.replace("\n", "\\n")
                            )
                        gw.send(msg.encode("utf-8"))
                        heartbeat_sent = True # block any other pending message

            if (heartbeat_sent == False):
                cur.execute(
                    "SELECT COUNT(*) FROM `messages_out` where sent = 0"
                )  # MySQL query statement
                count = cur.fetchone()  # Grab all messages from database for Outgoing.
                count = count[
                    0
                ]  # Parse first and the only one part of data table named "count" - there is number of records grabbed in SELECT above
                if count > 0:  # If greater then 0 then we have something to send out.
                    cur.execute(
                        "SELECT * FROM `messages_out` where sent = 0"
                    )  # grab all messages that where not send yet (sent ==0)
                    msg = cur.fetchall()
                    msg_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                    for m in msg:
                        out_id = int(m[msg_to_index["id"]])  # Record ID - only DB info,
                        n_id = m[msg_to_index["n_id"]]  # Node Table ID
                        node_id = m[msg_to_index["node_id"]]  # Node ID
                        out_child_id = m[msg_to_index["child_id"]]  # Child ID of the node where sensor/relay is attached.
                        out_sub_type = m[msg_to_index["sub_type"]]  # Command Type
                        out_ack = m[msg_to_index["ack"]]  # Ack req/resp
                        out_type = m[msg_to_index["type"]]  # Type
                        db_payload = m[msg_to_index["payload"]]  # Payload to send out.
                        out_payload = db_payload
                        sent = m[msg_to_index["sent"]]  # Status of message either its sent or not. (1 for sent, 0 for not sent yet)
                        cur.execute("SELECT type, name, sketch_version FROM `nodes` where id = (%s)", (n_id,))
                        nd = cur.fetchone()
                        node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                        node_type = nd[node_to_index["type"]]
                        node_name = nd[node_to_index["name"]]
                        sketch_version = float(nd[node_to_index["sketch_version"]])
                        if sketch_version > 0:
                            sketch_version = int(sketch_version * 100)
                        if (node_type.find("MQTT") != -1 and MQTT_CONNECTED == 1) or node_type.find("MQTT") == -1:
                            # Get the trigger level if the node/child is present in the relays table
                            cur.execute(
                                "SELECT COUNT(*) FROM `relays` where relay_id = (%s) AND relay_child_id = (%s) LIMIT 1",
                                (
                                    n_id,
                                    out_child_id,
                                ),
                            )
                            count = cur.fetchone()  # Grab all messages from database for Outgoing.
                            count = count[
                                0
                            ]  # Parse first and the only one part of data table named "count" - there is number of records grabbed in SELECT above
                            if count > 0:  # If greater then 0 then it is a relay, so get the trigger level.
                                cur.execute(
                                    "SELECT id, type, on_trigger, lag_time FROM `relays` where relay_id = (%s) AND relay_child_id = (%s) LIMIT 1",
                                    (
                                        n_id,
                                        out_child_id,
                                    ),
                                )
                                r = cur.fetchone()
                                relay_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                relays_id = r[relay_to_index["id"]]
                                relay_type = r[relay_to_index["type"]]
                                out_on_trigger = r[relay_to_index["on_trigger"]]
                                relay_lag = r[relay_to_index["lag_time"]]
                            else:
                                relay_lag = 0

                            if gatewayenableoutgoing == 1 or (
                                node_type.find("GPIO") != -1 and gatewayenableoutgoing == 0 and blinka
                            ):
                                if node_type.find("MySensor") != -1 and node_name.find("Controller") != -1 and sketch_version >= 34:
                                    out_payload = XNOR(out_on_trigger, out_payload)

                                # if a relay ON command check if relay has a ON lag time setting
                                if db_payload == "1" and (relay_lag != 0 and test_mode == 0):
                                    if relay_lag_timer.get(relays_id) == 0:
                                        # initialise relay ON trigger timer
                                        relay_lag_timer[relays_id] = time.time()
                                    else:
                                        # if lag time has expired then set the relay ON flag and re-initialise the counter
                                        if time.time() -  relay_lag_timer.get(relays_id) >= relay_lag:
                                            relay_on_flag = True
                                            relay_lag_timer[relays_id] = 0
                                else:
                                    relay_lag_timer[relays_id] = 0
                                    relay_on_flag = True

                                if relay_on_flag:
                                    # set relays when level is LOW or when HIGH and the Lag setting is 0 or the lag timer has expired
                                    msg = str(node_id)  # Node ID
                                    msg += ";"  # Separator
                                    msg += str(out_child_id)  # Child ID of the Node.
                                    msg += ";"  # Separator
                                    msg += str(out_sub_type)
                                    msg += ";"  # Separator
                                    msg += str(out_ack)
                                    msg += ";"  # Separator
                                    msg += str(out_type)
                                    msg += ";"  # Separator
                                    msg += str(out_payload)  # Payload from DB
                                    msg += " \n"  # New line
                                    if dbgLevel >= 3 and dbgMsgOut == 1:
                                        if test_mode != 0:
                                            print(bc.WARN + "\nOPERATING IN TEST MODE" + bc.ENDC)
                                        print(
                                            bc.grn + "\nTotal Messages to Sent:      ", count, bc.ENDC
                                        )  # Print how many Messages we have to send out.
                                        print("Date & Time:                 ", time.ctime())
                                        print(
                                            "Message From Database:       ",
                                            out_id,
                                            node_id,
                                            out_child_id,
                                            out_sub_type,
                                            out_ack,
                                            out_type,
                                            out_payload,
                                            sent,
                                        )  # Print what will be sent including record id and sent status.
                                        print(
                                            "Full Message to Send:        ", msg.replace("\n", "\\n")
                                        )  # Print Full Message
                                        print("Node ID:                     ", node_id)
                                        print("Child Sensor ID:             ", out_child_id)
                                        print("Command Type:                ", out_sub_type)
                                        print("Ack Req/Resp:                ", out_ack)
                                        print("Type:                        ", out_type)
                                        print("Pay Load:                    ", out_payload)
                                        print("Node Type:                   ", node_type)
                                    # node-id ; child-sensor-id ; command ; ack ; type ; payload
                                    set_relays(
                                        msg,
                                        n_id,
                                        node_type,
                                        sketch_version,
                                        out_id,
                                        out_child_id,
                                        out_on_trigger,
                                        out_payload,
                                        gatewayenableoutgoing,
                                    )
                                    # reset the relay_on_flag ready for next pass through
                                    relay_on_flag = False

            # remove any sensor_graphs table records older than 24 hours
            timestamp = (datetime.now()- timedelta(hours = 24)).strftime("%Y-%m-%d %H:%M:%S")
            cur.execute(
                'DELETE FROM sensor_graphs WHERE datetime < (%s)', (timestamp,)
            )

            ## Incoming messages
            if gatewaytype != "virtual":
                if gatewaytype == "serial":
                    in_str = gw.readline()  # Here is receiving part of the code for serial GW
                    in_str = in_str.decode("utf-8")
                    process_message(in_str)
                else:
                    # Here is receiving part of the code for Wifi
                    #process the incomming message
                    while not fifo.empty():
                        process_message(fifo.get())

            #update the gateway_transactions table
            timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            try:
                cur.execute(
                    "UPDATE gateway_logs SET mqtt_sent = %s, mqtt_recv = %s, mysensors_sent = %s, mysensors_recv = %s, gpio_sent = %s, heartbeat = %s ORDER BY id DESC LIMIT 1;",
                    (mqtt_sent, mqtt_msgcount, mysensor_sent, msgcount, gpio_sent, timestamp),
                )
                con.commit()
            except mdb.Error as e:
                # skip deadlock error (being caused when mysqldunp runs
                if e.args[0] == 1213:
                    pass
                else:
                    print("DB Error %d: %s" % (e.args[0], e.args[1]))
                    print(traceback.format_exc())
                    logging.error(e)
                    logging.info(traceback.format_exc())
                    con.close()
                    if MQTT_CONNECTED == 1:
                        mqttClient.disconnect()
                        mqttClient.loop_stop()
                    print(infomsg)
                    sys.exit(1)

            if clear_minute_timer :
                minute_timer = time.time()
            if clear_hour_timer :
                hour_timer = time.time()

            time.sleep(0.1)

except GatewayException as e:
    print(format(e))
    print(traceback.format_exc())
    logging.error(e)
    logging.info(traceback.format_exc())
except configparser.Error as e:
    print("ConfigParser:", format(e))
    print(traceback.format_exc())
    logging.error(e)
    logging.info(traceback.format_exc())
except mdb.Error as e:
    print("DB Error %d: %s" % (e.args[0], e.args[1]))
    print(traceback.format_exc())
    logging.error(e)
    logging.info(traceback.format_exc())
except serial.SerialException as e:
    print("SerialException:", format(e))
    print(traceback.format_exc())
    logging.error(e)
    logging.info(traceback.format_exc())
except EOFError as e:
    print("EOFError:", format(e))
    print(traceback.format_exc())
    logging.error(e)
    logging.info(traceback.format_exc())
except TypeError as e:
    print("TypeError:", format(e))
    print(traceback.format_exc())
    logging.error(e)
    logging.info(traceback.format_exc())
except Exception as e:
    print(format(e))
    print(traceback.format_exc())
    logging.error(e)
    logging.info(traceback.format_exc())
except ProgramKilled as e:
    print(format(e))
    print(traceback.format_exc())
    logging.error(e)
    logging.info(traceback.format_exc())
finally:
    con.close()
    if MQTT_CONNECTED == 1:
        mqttClient.disconnect()
        mqttClient.loop_stop()
    print(infomsg)
    sys.exit(1)
