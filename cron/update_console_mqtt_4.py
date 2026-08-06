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
print("*              Update Boost ConsoleScript              *")
print("*                                                      *")
print("*               Build Date: 17/06/2024                 *")
print("*      Version mqtt_4 - Last Modified 12/09/2024       *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

line_len = 90; #length of seperator lines

import os, sys, time
import subprocess
from datetime import datetime
import MySQLdb as mdb
import configparser
import struct
from math import floor

# MQTT specific functions
# Function run when the MQTT client connect to the brooker
# Initialise MQTT connection status
MQTT_CONNECTED = 0

# Used by MQTT function 'on_message' to get attribute value
def deep_get(dictionary, keys, default=None):
    return reduce(lambda d, key: d.get(key, default) if isinstance(d, dict) else default, keys.split("."), dictionary)

def on_connect_1(client, userdata, flags, rc):
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
def on_disconnect_1(client, userdata, rc):
    MQTT_CONNECTED = 0
    con_mqtt.close()
    if rc != 0:
        print("\nUnexpected disconnection.\n")
        cmd = 'sudo pkill -f update_console_mqtt_4.py'
        os.system(cmd)
    else:
        print("\nSuccessfully disconnected from the brooker\n")

# Function run when the MQTT client connect to the brooker
def on_connect_2(client, userdata, flags, reason_code, properties):
    if reason_code.is_failure:
        print("\nConnection failed\n")
        MQTT_CONNECTED = 0
    else:
        # we should always subscribe from on_connect callback to be sure
        # our subscribed is persisted across reconnections.
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

# Function run when the MQTT client disconnects to the brooker
def on_disconnect_2(client, userdata, flags, reason_code, properties):
    MQTT_CONNECTED = 0
    con_mqtt.close()
    if reason_code == 0:
        print("\nSuccessfully disconnected from the brooker\n")
    if reason_code > 0:
        print("\nUnexpected disconnection.\n")
        cmd = 'sudo pkill -f update_console_mqtt_4.py'
        os.system(cmd)

def on_publish(client, userdata, mid, reason_code, properties):
    # reason_code and properties will only be present in MQTTv5. It's always unset in MQTTv3
    try:
        userdata.remove(mid)
    except KeyError:
        print("on_publish() is called with a mid not present in unacked_publish")
        print("This is due to an unavoidable race-condition:")
        print("* publish() return the mid of the message sent.")
        print("* mid from publish() is added to unacked_publish by the main thread")
        print("* on_publish() is called by the loop_start thread")
        print("While unlikely (because on_publish() will be called after a network round-trip),")
        print(" this is a race-condition that COULD happen")
        print("")
        print("The best solution to avoid race-condition is using the msg_info from publish()")
        print("We could also try using a list of acknowledged mid rather than removing from pending list,")
        print("but remember that mid could be re-used !")

# To be run when an MQTT message is received to write the sensor value into messages_in
def on_message(client, userdata, message):
    if not os.path.isfile("/tmp/db_cleanup_running"):
        global mqtt_msgcount
        global clear_hour_timer
        if message.topic.find("esp32/") != -1:
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

class ProgramKilled(Exception):
    pass


def signal_handler(signum, frame):
    raise ProgramKilled("Program killed: running cleanup code")

# define Python user-defined exceptions
class GatewayException(Exception):
    pass

# Initialise the database access variables
config = configparser.ConfigParser()
config.read("/var/www/st_inc/db_config.ini")
dbhost = config.get("db", "hostname")
dbuser = config.get("db", "dbusername")
dbpass = config.get("db", "dbpassword")
dbname = config.get("db", "dbname")
con = mdb.connect(dbhost, dbuser, dbpass, dbname)
cur = con.cursor()

print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Update Boost Console Script Started")
print("-" * line_len)

messages_out_dict = {}

flag = True # force display refresh on script startup
startup = False # used to terminate the script if the hardware restarts
heartbeat_timer = time.time()
heartbeat_flag = False

# Get the Boost Button id's
cur.execute("""SELECT `boost_button_child_id`
               FROM `boost`
               ORDER BY `boost_button_child_id` ASC;"""
            )
if cur.rowcount > 0:
    boost = cur.fetchall()
    boost_to_index = dict(
        (d[0], i) for i, d in enumerate(cur.description)
    )
    # Initialise a dictionary to hold the previous readings
    stable_mode_dict = {}
    for b in boost:
        child_id = b[boost_to_index["boost_button_child_id"]]
        stable_mode_dict[child_id] = {}
        stable_mode_dict[child_id]["mode"] = 0
        stable_mode_dict[child_id]["mode_prev"] = 0
        stable_mode_dict[child_id]["last_active_state"] = 0

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
            import platform
            if int(platform.python_version().split(".")[1]) < 8:
                import pkg_resources
                paho_version = pkg_resources.get_distribution("paho-mqtt").version
            else:
                from importlib.metadata import version
                paho_version = version("paho-mqtt")
            import json
            import signal
            from functools import reduce
        except ImportError:
            print(
                "Missing MQTT dependencies, MQTT nodes cannot be enabled. Please install the required dependencies using /add_on/MQTT_dependencies/install.sh"
            )
            MQTT_CONNECTED = 0
        else:
            print("Setting up MQTT, using paho version:", paho_version)
            con_mqtt = mdb.connect(dbhost, dbuser, dbpass, dbname)
            cur_mqtt = con_mqtt.cursor()
            MQTT_CLIENT_ID = "Console_MaxAir"  # MQTT Client ID
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
            if paho_version.find("1.5.0") != -1:
                mqttClient = mqtt.Client(MQTT_CLIENT_ID)
                mqttClient.on_connect = on_connect_1  # attach function to callback
                mqttClient.on_disconnect = on_disconnect_1
            else:
                mqttClient = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2, client_id=MQTT_CLIENT_ID)
                mqttClient.on_connect = on_connect_2  # attach function to callback
                mqttClient.on_disconnect = on_disconnect_2
                unacked_publish = set()
                mqttClient.on_publish = on_publish
                mqttClient.user_data_set(unacked_publish)

            mqttClient.on_message = on_message
            mqttClient.username_pw_set(MQTT_USERNAME, MQTT_PASSWORD)
            signal.signal(signal.SIGTERM, signal_handler)
            signal.signal(signal.SIGINT, signal_handler)
            mqttClient.connect(MQTT_HOSTNAME, MQTT_PORT)
            mqttClient.loop_start()
            MQTT_CONNECTED = 1
cur.close()
con.close()

while 1:
    # force status update once every 60 seconds to catch missed button press
    if time.time() - heartbeat_timer >= 60:
        heartbeat_timer = time.time()
        heartbeat_flag = True
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cur = con.cursor()
    # build an array with entries for each zone
    cur.execute(
        """SELECT `z`.`name`, `z`.`id`, `mode`, `mode_prev`, `zone_current_state`.`status`, `temp_target`, `temp_reading`, `n`.`id` AS `n_id`, `bst`.`temperature`,
           `bst`.`boost_button_id`, `bst`.`boost_button_child_id`, `bst`.`status`, `bst`.`sync`, `bst`.`boost_button_state`, `n`.`sync` AS startup
           FROM `zone_current_state`
           JOIN `boost` bst ON `bst`.`zone_id` = `zone_current_state`.`zone_id`
           JOIN `zone` `z` ON `z`.`id` = `zone_current_state`.`zone_id`
           JOIN `nodes` `n` ON `n`.`node_id` = `bst`.`boost_button_id`
           ORDER BY `bst`.`boost_button_child_id` ASC;"""
    )
    if cur.rowcount > 0:
        boost = cur.fetchall()
        boost_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        status = 0
        for b in boost:
            z_status = 0
            # test the 'sync' field to see if the hardware has restarted
            if b[boost_to_index["startup"]] == 1:
                startup = True
            boost_status = b[boost_to_index["status"]]
            boost_sync = b[boost_to_index["sync"]]
            n_id = b[boost_to_index["n_id"]]
            boost_temp = b[boost_to_index["temperature"]]
            node_id = b[boost_to_index["boost_button_id"]]
            child_id = b[boost_to_index["boost_button_child_id"]]
            btn_state = b[boost_to_index["boost_button_state"]]
            name = b[boost_to_index["name"]]
            zone_id = b[boost_to_index["id"]]
            messages_out_dict[child_id] = {}
            mode = b[boost_to_index["mode"]]
            mode_prev = b[boost_to_index["mode_prev"]]
            if mode == mode_prev:
                stable_mode_dict[child_id]["mode_prev"] = stable_mode_dict[child_id]["mode"]
                stable_mode_dict[child_id]["mode"] = mode
            mode = int(stable_mode_dict[child_id]["mode"])
            mode_prev = int(stable_mode_dict[child_id]["mode_prev"])
            if mode == 100 and mode_prev != 100:
                 stable_mode_dict[child_id]["last_active_state"] = mode_prev
            # if in hysteresis mode then display keeps the last active state
            if floor(mode/10)*10 == 100:
                mode = stable_mode_dict[child_id]["last_active_state"]
            mode_1 = floor(mode/10)*10
            mode_2 = floor(mode%10)
            if floor(mode_prev/10)*10 == 100:
                mode_prev = stable_mode_dict[child_id]["last_active_state"]
            mode_prev_1 = floor(mode_prev/10)*10
            mode_prev_2 = floor(mode_prev%10)
            current_status = b[boost_to_index["status"]]
            if mode_1 == 80 or (mode_1 == 30 and current_status == 1):
                z_status = 2**(9 - child_id)
            elif mode_1 == 60:
                z_status = 2**(6 - child_id)
            if mode_2 == 1:
                z_status = z_status + 2**(3 - child_id)
            if btn_state == 1:
                z_status = z_status + 2**(12 - child_id)
            status = status + z_status
            node_id = str(b[boost_to_index["boost_button_id"]])
            payload = float(b[boost_to_index["temp_target"]])
#            print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Node  : " + name + " - " + node_id + "/" + str(child_id))
#            print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Mode 1:  " + str(mode_1))
#            print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Mode 2:  " + str(mode_2))
            messages_out_dict[child_id]["node_id"] = node_id
            messages_out_dict[child_id]["temp_target"] = payload
            payload = float(b[boost_to_index["temp_reading"]])
            messages_out_dict[child_id]["temp_reading"] = payload
            messages_out_dict[child_id]["temp_boost"] = boost_temp
            messages_out_dict[child_id]["status"] = status
            messages_out_dict[child_id]["mode_1"] = mode_1
            messages_out_dict[child_id]["boost_status"] = boost_status
            messages_out_dict[child_id]["boost_sync"] = boost_sync
            messages_out_dict[child_id]["btn_state"] = btn_state
        # end of for b in boost: loop

#        print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - startup - ", startup)
#        print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Old     - ", old_messages_out_dict)
#        print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Current - ", messages_out_dict)

    mqtt_topic = "esp32/status"
    payload_str = "on"
    payload_str = '{"node_id": "50", "temp_target": 0.0, "temp_reading": 36.2, "status": 0, "mode_1": 10, "boost_status": 0, "boost_sync": 0, "boost_temp": 0, "btn_state": 0}'
    payload_str = str(messages_out_dict[1])

    mqtt_msg_dict = {}
    mqtt_msg_dict["status"] = status
    mqtt_msg_dict["z1_target"] = float(messages_out_dict[1]["temp_target"])
    mqtt_msg_dict["z1_value"] = float(messages_out_dict[1]["temp_reading"])
    mqtt_msg_dict["z1_boost"] = float(messages_out_dict[1]["temp_boost"])
    mqtt_msg_dict["z2_target"] = float(messages_out_dict[2]["temp_target"])
    mqtt_msg_dict["z2_value"] = float(messages_out_dict[2]["temp_reading"])
    mqtt_msg_dict["z2_boost"] = float(messages_out_dict[2]["temp_boost"])
    mqtt_msg_dict["z3_target"] = float(messages_out_dict[3]["temp_target"])
    mqtt_msg_dict["z3_value"] = float(messages_out_dict[3]["temp_reading"])
    mqtt_msg_dict["z3_boost"] = float(messages_out_dict[3]["temp_boost"])
    payload_str = str(mqtt_msg_dict)
    print("\nSending the following MQTT Message:")
    print("Topic: %s" % mqtt_topic)
    print("Message: %s" % payload_str)
    if paho_version.find("1.5.0") != -1:
        mqttClient.publish(
            topic=mqtt_topic,
            payload=payload_str,
            qos=1,
            retain=False,
        )
    else:
        msg_info = mqttClient.publish(mqtt_topic, payload_str, qos=1)
        unacked_publish.add(msg_info.mid)

        # Wait for all message to be published
        while len(unacked_publish):
           time.sleep(0.1)

        # Due to race-condition described above, the following way to wait for all publish is safer
        msg_info.wait_for_publish()

    cur.close()
    con.close()
#    print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Update Boost Console loop Finished")
#    print("-" * line_len)

    time.sleep(5)

cur.close()
con.close()
print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Update Boost Console Script Ended")
