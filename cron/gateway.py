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
print("*      Version 0.11 - Last Modified 18/08/2021         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

import MySQLdb as mdb, sys, serial, telnetlib, time, datetime, os
import configparser, logging
from datetime import datetime
import struct
import requests
import socket, re
from Pin_Dict import pindict
import board, digitalio
import traceback

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

# Initialise a dictionary to hold the relay id for Adafruit Blinka
relay_dict = {}


def set_relays(
    msg,
    n_id,
    node_type,
    out_id,
    out_child_id,
    out_on_trigger,
    out_payload,
    enable_outgoing,
):
    # node-id ; child-sensor-id ; command ; ack ; type ; payload \n
    if node_type.find("MySensor") != -1 and enable_outgoing == 1:  # process normal node
        if gatewaytype == "serial":
            gw.write(
                msg.encode("utf-8")
            )  # !!!! send it to serial (arduino attached to rPI by USB port)
        else:
            print("write")
            gw.write(msg.encode("utf-8"))
        cur.execute(
            "UPDATE `messages_out` set sent=1 where id=%s", [out_id]
        )  # update DB so this message will not be processed in next loop
        con.commit()  # commit above
    elif node_type.find("GPIO") != -1:  # process GPIO mode
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
    elif (
        node_type.find("Tasmota") != -1 and network_found == 1 and enable_outgoing == 1
    ):  # only process Sonoff device if connected to the local wlan
        # process the Sonoff device HTTP action
        url = "http://" + net_gw + str(out_child_id) + "/cm"
        cmd = out_payload.split(" ")[0].upper()
        param = out_payload.split(" ")[1]
        myobj = {"cmnd": str(out_payload)}
        x = requests.post(url, data=myobj)  # send request to Sonoff device
        if x.status_code == 200:
            if x.json().get(cmd) == param:  # clear send if response is okay
                cur.execute("UPDATE `messages_out` set sent=1 where id=%s", [out_id])
                con.commit()  # commit above
    elif node_type.find("MQTT") != -1 and MQTT_CONNECTED == 1:  # process MQTT mode
        cur.execute(
            'SELECT `mqtt_topic`, `on_payload`, `off_payload`  FROM `mqtt_devices` WHERE `type` = "1" AND `nodes_id` = (%s) AND `child_id` = (%s) LIMIT 1',
            [n_id, out_child_id],
        )
        results_mqtt_r = cur.fetchone()
        description_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
        mqtt_topic = results_mqtt_r[description_to_index["mqtt_topic"]]
        if out_payload == "1":
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
        for node in cur_mqtt.fetchall():
            subscribe_topics.append((f"{node[0]}", 0))
        client.subscribe(subscribe_topics)
        print("Subscribed to the followint MQTT topics:")
        print(subscribe_topics)
    else:
        print("\nConnection failed\n")
        MQTT_CONNECTED = 0


# Function run when the MQTT client disconnects to the brooker
def on_disconnect(client, userdata, rc):
    MQTT_CONNECTED = 0
    con_mqtt.close()
    if rc != 0:
        print("\nUnexpected disconnection.\n")
    else:
        print("\nSuccessfully disconnected from the brooker\n")


# To be run when an MQTT message is received to write the sensor value into messages_in
def on_message(client, userdata, message):
    print("\nMQTT messaged received.")
    print("Topic: %s" % message.topic)
    print("Message: %s" % message.payload.decode())
    cur_mqtt.execute(
        "SELECT `nodes`.node_id, `mqtt_devices`.child_id, `mqtt_devices`.attribute  FROM `mqtt_devices`, `nodes` WHERE `mqtt_devices`.nodes_id = `nodes`.id AND `mqtt_devices`.type = 0 AND `mqtt_devices`.mqtt_topic = (%s)",
        [message.topic],
    )
    on_msg_description_to_index = dict(
        (d[0], i) for i, d in enumerate(cur_mqtt.description)
    )
    for child in cur_mqtt.fetchall():
        mqtt_node_id = child[on_msg_description_to_index["node_id"]]
        mqtt_child_sensor_id = int(child[on_msg_description_to_index["child_id"]])
        if child[on_msg_description_to_index["attribute"]] is None:
            mqtt_payload = message.payload.decode()
        else:
            json_data = json.loads(message.payload.decode())
            mqtt_payload = json_data[child[on_msg_description_to_index["attribute"]]]
        print(
            "5: Adding Temperature Reading From Node ID:",
            mqtt_node_id,
            " Child Sensor ID:",
            mqtt_child_sensor_id,
            " PayLoad:",
            mqtt_payload,
        )
        cur_mqtt.execute(
            "INSERT INTO `messages_in`(`node_id`, `sync`, `purge`, `child_id`, `sub_type`, `payload`, `datetime`) VALUES (%s, 0, 0, %s, 0, %s, NOW())",
            [mqtt_node_id, mqtt_child_sensor_id, mqtt_payload],
        )
        con_mqtt.commit()
        cur_mqtt.execute(
            'UPDATE `nodes` SET `last_seen`= NOW() WHERE `node_id`= "%s"',
            [mqtt_node_id],
        )
        con_mqtt.commit()
        # Check is sensor is attached to a zone which is being graphed
        cur_mqtt.execute(
            'SELECT sensors.id, sensors.zone_id, nodes.node_id, sensors.sensor_child_id, sensors.name, sensors.graph_num FROM sensors, `nodes` WHERE (sensors.sensor_id = nodes.`id`) AND  nodes.node_id = %s AND sensors.sensor_child_id = %s AND sensors.graph_num > 0 LIMIT 1;',
            [mqtt_node_id, mqtt_child_sensor_id],
        )
        results = cur_mqtt.fetchone()
        if cur_mqtt.rowcount > 0:
            mqtt_sensor_to_index = dict(
                (d[0], i) for i, d in enumerate(cur_mqtt.description)
            )
            mqtt_sensor_id = int(results[mqtt_sensor_to_index["id"]])
            mqtt_sensor_name = results[mqtt_sensor_to_index["name"]]
            mqtt_zone_id = results[mqtt_sensor_to_index["zone_id"]]
            # type = results[zone_view_to_index['type']]
            # category = int(results[zone_view_to_index['category']])
            mqtt_graph_num = int(results[mqtt_sensor_to_index["graph_num"]])
            if mqtt_graph_num > 0:
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
                    cur_mqtt.execute(
                        'INSERT INTO zone_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,NOW())',
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
                        if mqtt_category < 2:
                            cur_mqtt.execute(
                                'INSERT INTO zone_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,NOW())',
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
                                ),
                            )
                            con_mqtt.commit()
                cur_mqtt.execute(
                    'DELETE FROM zone_graphs WHERE node_id = %s AND child_id = %s AND datetime < CURRENT_TIMESTAMP - INTERVAL 24 HOUR;',
                    [mqtt_node_id, mqtt_child_sensor_id],
                )
                con_mqtt.commit()


class ProgramKilled(Exception):
    pass


def signal_handler(signum, frame):
    raise ProgramKilled


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
    else:
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
                MQTT_CONNECTED = 1
                results_mqtt = cur.fetchone()
                description_to_index = dict(
                    (d[0], i) for i, d in enumerate(cur.description)
                )
                MQTT_HOSTNAME = results_mqtt[description_to_index["ip"]]
                MQTT_PORT = results_mqtt[description_to_index["port"]]
                MQTT_USERNAME = results_mqtt[description_to_index["username"]]
                MQTT_PASSWORD = results_mqtt[description_to_index["password"]]
                mqttClient = mqtt.Client(MQTT_CLIENT_ID)
                mqttClient.on_connect = on_connect  # attach function to callback
                mqttClient.on_disconnect = on_disconnect
                mqttClient.on_message = on_message
                mqttClient.username_pw_set(MQTT_USERNAME, MQTT_PASSWORD)
                signal.signal(signal.SIGTERM, signal_handler)
                signal.signal(signal.SIGINT, signal_handler)
                mqttClient.connect(MQTT_HOSTNAME, MQTT_PORT)
                mqttClient.loop_start()
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
            "SELECT distinct relays.`relay_id`, relays.`relay_child_id` , relays.`on_trigger` FROM `relays`, system_controller, zone_relays WHERE (relays.id = zone_relays.zone_relay_id) OR (relays.id = system_controller.heat_relay_id) OR (relays.id = system_controller.cool_relay_id) OR (relays.id = system_controller.fan_relay_id);"
        )
        relays = cur.fetchall()
        relay_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
        # get the last relay state from the messages_out table
        for x in relays:
            controler_id = x[relay_to_index["relay_id"]]
            out_child_id = x[relay_to_index["relay_child_id"]]
            out_on_trigger = x[relay_to_index["on_trigger"]]
            cur.execute(
                "SELECT `id`, `node_id`, `type` FROM `nodes` where id = (%s) LIMIT 1",
                (controler_id,),
            )
            nd = cur.fetchone()
            node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
            n_id = nd[node_to_index["id"]]
            node_id = nd[node_to_index["node_id"]]
            node_type = nd[node_to_index["type"]]
            cur.execute(
                "SELECT `sub_type`, `ack`, `type`, `payload`, `id` FROM `messages_out` where node_id = (%s) AND child_id = (%s) ORDER BY id DESC LIMIT 1",
                (node_id, out_child_id),
            )
            if cur.rowcount > 0:
                msg = cur.fetchone()
                msg_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                out_id = int(msg[msg_to_index["id"]])
                out_sub_type = msg[msg_to_index["sub_type"]]
                out_ack = msg[msg_to_index["ack"]]
                out_type = msg[msg_to_index["type"]]
                out_payload = msg[msg_to_index["payload"]]
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
                    out_id,
                    out_child_id,
                    out_on_trigger,
                    out_payload,
                    gatewayenableoutgoing,
                )
        ping_timer = time.time()
    else:
        ping_timer = time.time()

    while 1:
        ## Terminate gateway script if no route to network gateway
        if gatewaytype == "wifi":
            if time.time() - ping_timer >= 60:
                ping_timer = time.time()
                gateway_up = (
                    True if os.system("ping -c 1 " + gatewaylocation) is 0 else False
                )
                if not gateway_up:
                    break
        ## Outgoing messages
        con.commit()
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
            msg = cur.fetchone()
            # Grab first record and build a message: if you change table fields order you need to change following lines as well.
            msg_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
            out_id = int(msg[msg_to_index["id"]])  # Record ID - only DB info,
            node_id = msg[msg_to_index["node_id"]]  # Node ID
            out_child_id = msg[
                msg_to_index["child_id"]
            ]  # Child ID of the node where sensor/relay is attached.
            out_sub_type = msg[msg_to_index["sub_type"]]  # Command Type
            out_ack = msg[msg_to_index["ack"]]  # Ack req/resp
            out_type = msg[msg_to_index["type"]]  # Type
            out_payload = msg[msg_to_index["payload"]]  # Payload to send out.
            sent = msg[
                msg_to_index["sent"]
            ]  # Status of message either its sent or not. (1 for sent, 0 for not sent yet)
            cur.execute("SELECT id, type FROM `nodes` where node_id = (%s)", (node_id,))
            nd = cur.fetchone()
            node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
            n_id = nd[node_to_index["id"]]
            node_type = nd[node_to_index["type"]]
            cur.execute(
                "SELECT on_trigger FROM `relays` where relay_id = (%s) AND relay_child_id = (%s) LIMIT 1",
                (
                    n_id,
                    out_child_id,
                ),
            )
            r = cur.fetchone()
            relay_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
            out_on_trigger = r[relay_to_index["on_trigger"]]
            if gatewayenableoutgoing == 1 or (
                node_type.find("GPIO") != -1 and gatewayenableoutgoing == 0
            ):
                if dbgLevel >= 1 and dbgMsgOut == 1:  # Debug print to screen
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

            # node-id ; child-sensor-id ; command ; ack ; type ; payload \n
            set_relays(
                msg,
                n_id,
                node_type,
                out_id,
                out_child_id,
                out_on_trigger,
                out_payload,
                gatewayenableoutgoing,
            )

        ## Incoming messages
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

                    # ..::Step One::..
                    # First time Temperature Sensors Node Comes online: Add Node to The Nodes Table.
                if (
                    node_id != 0
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
                        cur.execute(
                            "UPDATE nodes SET ms_version = %s where node_id = %s",
                            (payload, node_id),
                        )
                        con.commit()

                        # ..::Step One B::..
                        # First time Node Comes online with Repeater Feature Enabled: Add Node to The Nodes Table.
                if (
                    node_id != 0
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
                    else:
                        if dbgLevel >= 2 and dbgMsgIn == 1:
                            print(
                                "1-B: Node ID:",
                                node_id,
                                " Already Exist In Node Table, Updating MS Version",
                            )
                        cur.execute(
                            "UPDATE nodes SET ms_version = %s where node_id = %s",
                            (payload, node_id),
                        )
                        con.commit()

                        # ..::Step One C::..
                        # First time Node Comes online set the min_value.
                if (
                    node_id != 0
                    and child_sensor_id != 255
                    and message_type == 1
                    and sub_type == 24
                ):
                    if dbgLevel >= 2 and dbgMsgIn == 1:
                        print(
                            "4: Adding Node's min_value for Node ID:",
                            node_id,
                            " min_value:",
                            payload,
                        )
                    cur.execute(
                        "UPDATE nodes SET min_value = %s where node_id = %s",
                        (payload, node_id),
                    )
                    con.commit()

                    # ..::Step Two ::..
                    # Add Nodes Name i.e. Relay, Temperature Sensor etc. to Nodes Table.
                if child_sensor_id == 255 and message_type == 3 and sub_type == 11:
                    if dbgLevel >= 2 and dbgMsgIn == 1:
                        print(
                            "2: Update Node Record for Node ID:",
                            node_id,
                            " Sensor Type:",
                            payload,
                        )
                    cur.execute(
                        "UPDATE nodes SET name = %s where node_id = %s",
                        (payload, node_id),
                    )
                    con.commit()

                    # ..::Step Three ::..
                    # Add Nodes Sketch Version to Nodes Table.
                if (
                    node_id != 0
                    and child_sensor_id == 255
                    and message_type == 3
                    and sub_type == 12
                ):
                    if dbgLevel >= 2 and dbgMsgIn == 1:
                        print(
                            "3: Update Node ID: ",
                            node_id,
                            " Node Sketch Version: ",
                            payload,
                        )
                    cur.execute(
                        "UPDATE nodes SET sketch_version = %s where node_id = %s",
                        (payload, node_id),
                    )
                    con.commit()

                    # ..::Step Four::..
                    # Add Node Child ID to Node Table
                    # 25;0;0;0;6;
                if (
                    node_id != 0
                    and child_sensor_id != 255
                    and message_type == 0
                    and (sub_type == 3 or sub_type == 6)
                ):
                    if dbgLevel >= 2 and dbgMsgIn == 1:
                        print(
                            "4: Adding Node's Max Child ID for Node ID:",
                            node_id,
                            " Child Sensor ID:",
                            child_sensor_id,
                        )
                    cur.execute(
                        "UPDATE nodes SET max_child_id = %s WHERE node_id = %s",
                        (child_sensor_id, node_id),
                    )
                    con.commit()

                    # ..::Step Five::..
                    # Add Temperature Reading to database
                if (
                    node_id != 0
                    and child_sensor_id != 255
                    and message_type == 1
                    and sub_type == 0
                ):
                    # Check if this sensor has a correction factor
                    cur.execute(
                        "SELECT sensors.correction_factor FROM sensors, `nodes` WHERE (sensors.sensor_id = nodes.`id`) AND  nodes.node_id = (%s) AND sensors.sensor_child_id = (%s) LIMIT 1;",
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
                    if dbgLevel >= 2 and dbgMsgIn == 1:
                        print(
                            "5: Adding Temperature Reading From Node ID:",
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
                    cur.execute(
                        "UPDATE `nodes` SET `last_seen`=now(), `sync`=0  WHERE node_id = %s",
                        [node_id],
                    )
                    con.commit()
                    # Check is sensor is attached to a zone which is being graphed
                    cur.execute(
                        "SELECT sensors.id, sensors.zone_id, nodes.node_id, sensors.sensor_child_id, sensors.name, sensors.graph_num FROM sensors, `nodes` WHERE (sensors.sensor_id = nodes.`id`) AND  nodes.node_id = (%s) AND sensors.sensor_child_id = (%s) AND sensors.graph_num > 0 LIMIT 1;",
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
                        if graph_num > 0:
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
                                    "INSERT INTO zone_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
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
                                    if category < 2:
                                        cur.execute(
                                            "INSERT INTO zone_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
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
                            cur.execute(
                                "DELETE FROM zone_graphs WHERE node_id = (%s) AND child_id = (%s) AND datetime < CURRENT_TIMESTAMP - INTERVAL 24 HOUR;",
                                (node_id, child_sensor_id),
                            )
                            con.commit()

                    # ..::Step Six ::..
                    # Add Humidity Reading to database
                if (
                    node_id != 0
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
                    cur.execute(
                        "UPDATE `nodes` SET `last_seen`=now(), `sync`=0  WHERE node_id = %s",
                        [node_id],
                    )
                    con.commit()
                    # Check is sensor is attached to a zone which is being graphed
                    cur.execute(
                        "SELECT sensors.id, sensors.zone_id, nodes.node_id, sensors.sensor_child_id, sensors.name, sensors.graph_num FROM sensors, `nodes` WHERE (sensors.sensor_id = nodes.`id`) AND  nodes.node_id = (%s) AND sensors.sensor_child_id = (%s)  LIMIT 1;",
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
                                "INSERT INTO zone_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
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
                                        "INSERT INTO zone_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
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
                        cur.execute(
                            "DELETE FROM zone_graphs WHERE node_id = (%s) AND child_id = (%s) AND datetime < CURRENT_TIMESTAMP - INTERVAL 24 HOUR;",
                            (node_id, child_sensor_id),
                        )
                        con.commit()

                    # ..::Step Seven ::..
                    # Add Switch Reading to database
                if (
                    node_id != 0
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
                    cur.execute(
                        "UPDATE `nodes` SET `last_seen`=now(), `sync`=0  WHERE node_id = %s",
                        [node_id],
                    )
                    con.commit()

                    # ..::Step Eight::..
                    # Add Battery Voltage Nodes Battery Table
                    # Example: 25;1;1;0;38;4.39
                if (
                    node_id != 0
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
                    node_id != 0
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
                    cur.execute(
                        "UPDATE nodes SET last_seen=now(), `sync`=0 WHERE node_id = %s",
                        [node_id],
                    )
                    con.commit()

                    # ..::Step Ten::..
                    # Add Boost Status Level to Database/Relay Last seen gets added here as well when ACK is set to 1 in messages_out table.
                if (
                    node_id != 0
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
                    cur.execute(
                        "UPDATE `nodes` SET `last_seen`=now(), `sync`=0 WHERE node_id = %s",
                        [node_id],
                    )
                    con.commit()

                    # ..::Step Eleven::..
                    # Add Away Status Level to Database
                if (
                    node_id != 0
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
                    cur.execute(
                        "UPDATE `nodes` SET `last_seen`=now(), `sync`=0  WHERE node_id = %s",
                        [node_id],
                    )
                    con.commit()
                    # else:
                    # print bc.WARN+ "No Action Defined Incomming Node Message Ignored \n\n" +bc.ENDC

                    # ..::Step Twelve::..
                    # When Gateway Startup Completes
                if (
                    node_id == 0
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
                    node_id != 0
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
                if node_id != 0 and message_type == 2 and sub_type == 47:
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
                    node_id != 0 and message_type == 3 and sub_type == 3
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
                            gw.write(msg.encode("utf-8"))
                            cur.execute(
                                "UPDATE `node_id` set sent=1, `date_time`=now() where id=%s",
                                [out_id],
                            )  # update DB so this message will not be processed in next loop
                            con.commit()  # commit above
                    else:
                        print(bc.WARN + "All exiting IDs are assigned: " + bc.ENDC)
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
