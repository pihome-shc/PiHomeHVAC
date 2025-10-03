#!/usr/bin/env python3
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
print("*           Home Assistant Integration Script          *")
print("*      Build Date: 26/04/2025                          *")
print("*      Version 0.01 - Last Modified 26/04/2025         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

import argparse
import datetime as dt
import signal
import sys
import socket
import threading
import time
import logging
from datetime import timedelta
from re import findall
import subprocess
from subprocess import check_output
import paho.mqtt.client as mqtt
import platform
if int(platform.python_version().split(".")[1]) < 8:
    import pkg_resources
    paho_version = pkg_resources.get_distribution("paho-mqtt").version
else:
    from importlib.metadata import version
    paho_version = version("paho-mqtt")
import psutil
import pytz
import csv
from pytz import timezone
import MySQLdb as mdb
import configparser
import os
import numpy as n

if 'x86_64' not in platform.machine():
    try:
        from rpi_bad_power import new_under_voltage
        CHECK_RPI_POWER = True
    except ImportError:
        CHECK_RPI_POWER = False
else:
    CHECK_RPI_POWER = False

if CHECK_RPI_POWER:
    if new_under_voltage() is None:
        CHECK_RPI_POWER = False

logging.basicConfig(level=logging.WARNING)

DEFAULT_TIME_ZONE = None
WAIT_TIME_SECONDS = 60
MQTT_deviceName = "MaxAir"
MQTT_CLIENT_ID = "MaxAir_HA"
MQTT_TOPIC = "MaxAir/"
CHECK_AVAILABLE_UPDATES = bool(True)
CHECK_WIFI_STRENGHT = bool(True)
CHECK_WIFI_SSID = bool(False)
CHECK_DRIVES = bool(True)
FIRST_RECONNECT_DELAY = 1
RECONNECT_RATE = 2
MAX_RECONNECT_COUNT = 12
MAX_RECONNECT_DELAY = 60

# Initialise the database access variables
config = configparser.ConfigParser()
config.read("/var/www/st_inc/db_config.ini")
dbhost = config.get("db", "hostname")
dbuser = config.get("db", "dbusername")
dbpass = config.get("db", "dbpassword")
dbname = config.get("db", "dbname")

UTC = pytz.utc

old_net_data = psutil.net_io_counters()
previous_time = time.time()

# Get OS information
OS_DATA = {}
with open("/etc/os-release") as f:
    reader = csv.reader(f, delimiter="=")
    for row in reader:
        if row:
            OS_DATA[row[0]] = row[1]

mqttClient = None
deviceName = None
_underVoltage = None

con = mdb.connect(dbhost, dbuser, dbpass, dbname)
cur = con.cursor()
# Get Zones info
MA_Zone_Sensor_ID = []
MA_Frost_Protection = []
MA_Zone_ID = []
MA_Zone_Name = []
MA_Zone_Type = []
HA_Zone_Name = []
HA_Zone_UID = []
# Get MaxAir mode (0 - Boiler, 1 - HVAC)
cur.execute("SELECT `mode` FROM `system` LIMIT 1;")
MA_Mode = cur.fetchone()[0]
MA_Mode = MA_Mode & 0b1
# Get Zone info
cur.execute("""SELECT `n`.`node_id`, `zone_sensors`.`zone_id`, z.name, s.frost_temp, n.type, CONCAT(`zone_sensors`.`zone_id`, '_', `z`.`name`) AS `uid`
               FROM `zone_sensors`
               JOIN sensors s ON s.id = `zone_sensor_id`
               JOIN nodes n ON n.id = s.`sensor_id`
               JOIN zone z ON z.id = zone_sensors.zone_id
               ORDER BY zone_sensors.zone_sensor_id;""")
ZONES = cur.rowcount
description_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
results = cur.fetchall()
for row in results:
    MA_Zone_ID.append(row[description_to_index["zone_id"]])
    MA_Zone_Sensor_ID.append(row[description_to_index["node_id"]])
    MA_Zone_Name.append(row[description_to_index["name"]])
    HA_Zone_Name.append(row[description_to_index["name"]].lower().replace(" ", ""))
    MA_Frost_Protection.append(row[description_to_index["frost_temp"]])
    MA_Zone_Type.append(row[description_to_index["type"]])
    HA_Zone_UID.append(row[description_to_index["uid"]].lower().replace(" ", ""))

# Get stand alone sensors info
MA_Sensor_Node_ID = []
MA_Sensor_Child_ID = []
MA_Sensor_Measurement = []
MA_Sensor_Name = []
HA_Sensor_Name = []
MA_Sensor_Type = []
# Get Sensor info
MA_Sensor_Node_ID = []
MA_Sensor_Child_ID = []
MA_Sensor_Measurement = []
MA_Sensor_Name = []
HA_Sensor_Name = []
MA_Sensor_Type = []
HA_Sensor_UID = []
# Get Sensor info
cur.execute(
    """SELECT `sensors`.`sensor_id`, `sensors`.`sensor_child_id`, `sensors`.`sensor_type_id`, `sensors`.`name`, `nodes`.`node_id`, `nodes`.`type`,
       CONCAT(`sensors`.`name`, '_', `sensors`.`sensor_id`, '_', `sensors`.`sensor_child_id`) AS `uid`
       FROM `sensors`, `nodes`
       WHERE (`sensors`.`sensor_id` = `nodes`.`id`) AND `sensors`.`zone_id` = "0" AND (`sensors`.`sensor_type_id` = "1" OR `sensors`.`sensor_type_id` = "2");"""
)
SENSORS = cur.rowcount
description_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
results = cur.fetchall()
for row in results:
    MA_Sensor_Child_ID.append(row[description_to_index["sensor_child_id"]])
    MA_Sensor_Measurement.append(row[description_to_index["sensor_type_id"]])
    MA_Sensor_Name.append(row[description_to_index["name"]])
    HA_Sensor_Name.append(row[description_to_index["name"]].lower().replace(" ", ""))
    MA_Sensor_Node_ID.append(row[description_to_index["node_id"]])
    MA_Sensor_Type.append(row[description_to_index["type"]])
    HA_Sensor_UID.append(row[description_to_index["uid"]].lower().replace(" ", ""))

MA_Boost_ID = []
MA_Boost_Zone_ID = []
MA_Boost_Zone_Name = []
HA_Boost_Zone_Name = []
# get boost info
cur.execute(
    'SELECT  `boost`.`id`, `boost`.`zone_id`, CONCAT(`zone`.`name`, "_", `boost`.`temperature`, "_", `boost`.`minute`) AS `name`  FROM `boost`, `zone` WHERE `boost`.`zone_id` = `zone`.`id`;'
)
BOOSTS = cur.rowcount
description_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
results = cur.fetchall()
for row in results:
    MA_Boost_ID.append(row[description_to_index["id"]])
    MA_Boost_Zone_ID.append(row[description_to_index["zone_id"]])
    MA_Boost_Zone_Name.append(row[description_to_index["name"]])
    HA_Boost_Zone_Name.append(row[description_to_index["name"]].lower().replace(" ", ""))

con.close()

class ProgramKilled(Exception):
    pass


def signal_handler(signum, frame):
    raise ProgramKilled


class Job(threading.Thread):
    def __init__(self, interval, execute, *args, **kwargs):
        threading.Thread.__init__(self)
        self.daemon = False
        self.stopped = threading.Event()
        self.interval = interval
        self.execute = execute
        self.args = args
        self.kwargs = kwargs

    def stop(self):
        self.stopped.set()
        self.join()

    def run(self):
        while not self.stopped.wait(self.interval.total_seconds()):
            self.execute()


def write_message_to_console(message):
    print(bc.grn + message + bc.ENDC)
    sys.stdout.flush()


def utc_from_timestamp(timestamp: float) -> dt.datetime:
    """Return a UTC time from a timestamp."""
    return UTC.localize(dt.datetime.utcfromtimestamp(timestamp))


def as_local(dattim: dt.datetime) -> dt.datetime:
    """Convert a UTC datetime object to local time zone."""
    if dattim.tzinfo == DEFAULT_TIME_ZONE:
        return dattim
    if dattim.tzinfo is None:
        dattim = UTC.localize(dattim)

    return dattim.astimezone(DEFAULT_TIME_ZONE)


def get_last_boot():
    return str(as_local(utc_from_timestamp(psutil.boot_time())).isoformat())


def get_last_message():
    return str(as_local(utc_from_timestamp(time.time())).isoformat())


def on_message(client, userdata, message):
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cur = con.cursor()
    if (message.topic == "homeassistant/status") and (
        message.payload.decode() == "online"
    ):
        send_config_message(client)
    elif message.topic[-15:] == "SC/away_command":
        if message.payload.decode() == "ON":  # Turn away mode on
            cur.execute("UPDATE `away` SET `status` = 1 ORDER BY `id` desc LIMIT 1;")
        else:  # Turn off away
            cur.execute("UPDATE `away` SET `status` = 0 ORDER BY `id` desc LIMIT 1;")
    elif message.topic[-15:] == "SC/mode_command":
        cur.execute(
            "UPDATE `system_controller` SET `sc_mode` = (%s)",
            [message.payload.decode()],
        )
    elif message.topic[-13:] == "boost_command":
        boost = HA_Boost_Zone_Name.index(message.topic.split("/")[2])
        if message.payload.decode() == "ON":  # Turn boost on
            cur.execute(
                "UPDATE `boost` SET `status` = 1 WHERE `id` = (%s)",
                [MA_Boost_ID[boost]],
            )
        else:  # Turn boost off
            cur.execute(
                "UPDATE `boost` SET `status` = 0 WHERE `id` = (%s)",
                [MA_Boost_ID[boost]],
            )
    elif message.topic[-11:] == "target_temp":
        zone = HA_Zone_Name.index(message.topic.split("/")[1])
        cur.execute(
            "UPDATE `livetemp` SET `active` = 1, `temperature` = (%s), `zone_id` = (%s)",
            [message.payload.decode(), MA_Zone_ID[zone]],
        )
    con.commit()
    con.close()
    updateSensors()

def check_maxair_changes():
    changed = False
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cur = con.cursor()

    # check if BOOST configurtion has changed
    cur.execute(
        """SELECT  CONCAT(`zone`.`name`, '_', `boost`.`temperature`, '_', `boost`.`minute`) AS `name`
           FROM `boost`, `zone`
           WHERE `boost`.`zone_id` = `zone`.`id`;"""
    )
    if cur.rowcount > 0:
        temp_array_1 = []
        results = cur.fetchall()
        for row in results:
            temp_array_1.append(row[0].lower().replace(" ", ""))

        narr1 = n.array([temp_array_1])
        narr2 = n.array([HA_Boost_Zone_Name])

        result_variable = (narr1 == narr2).all()
        if(result_variable == False):
            # changed so re-populate the boost info
            MA_Boost_ID.clear()
            MA_Boost_Zone_ID.clear()
            MA_Boost_Zone_Name.clear()
            HA_Boost_Zone_Name.clear()
            cur.execute(
                """SELECT  `boost`.`id`, `boost`.`zone_id`, CONCAT(`zone`.`name`, '_', `boost`.`temperature`, '_', `boost`.`minute`) AS `name`
                   FROM `boost`, `zone`
                   WHERE `boost`.`zone_id` = `zone`.`id`;"""
            )
            BOOSTS = cur.rowcount
            description_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
            results = cur.fetchall()
            for row in results:
                MA_Boost_ID.append(row[description_to_index["id"]])
                MA_Boost_Zone_ID.append(row[description_to_index["zone_id"]])
                MA_Boost_Zone_Name.append(row[description_to_index["name"]])
                HA_Boost_Zone_Name.append(row[description_to_index["name"]].lower().replace(" ", ""))
            changed = True

    # check if stand alone sensor configurtion has changed
    cur.execute(
        """SELECT CONCAT(`sensors`.`name`, '_', `sensors`.`sensor_id`, '_', `sensors`.`sensor_child_id`) AS `uid`
           FROM `sensors`
           WHERE `sensors`.`zone_id` = "0" AND (`sensors`.`sensor_type_id` = "1" OR `sensors`.`sensor_type_id` = "2");"""
    )
    if cur.rowcount > 0:
        temp_array_1 = []
        results = cur.fetchall()
        for row in results:
            temp_array_1.append(row[0].lower().replace(" ", ""))

        narr1 = n.array([temp_array_1])
        narr2 = n.array([HA_Sensor_UID])

        result_variable = (narr1 == narr2).all()
        if(result_variable == False):
            # Get stand alone sensors info
            MA_Sensor_Node_ID.clear()
            MA_Sensor_Child_ID.clear()
            MA_Sensor_Measurement.clear()
            MA_Sensor_Name.clear()
            HA_Sensor_Name.clear()
            MA_Sensor_Type.clear()
            HA_Sensor_UID.clear()
            # Get Sensor info
            cur.execute(
                """SELECT `sensors`.`sensor_id`, `sensors`.`sensor_child_id`, `sensors`.`sensor_type_id`, `sensors`.`name`, `nodes`.`node_id`, `nodes`.`type`,
                   CONCAT(`sensors`.`name`, '_', `sensors`.`sensor_id`, '_', `sensors`.`sensor_child_id`) AS `uid`
                   FROM `sensors`, `nodes`
                   WHERE (`sensors`.`sensor_id` = `nodes`.`id`) AND `sensors`.`zone_id` = "0" AND (`sensors`.`sensor_type_id` = "1" OR `sensors`.`sensor_type_id` = "2");"""
            )
            SENSORS = cur.rowcount
            description_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
            results = cur.fetchall()
            for row in results:
                MA_Sensor_Child_ID.append(row[description_to_index["sensor_child_id"]])
                MA_Sensor_Measurement.append(row[description_to_index["sensor_type_id"]])
                MA_Sensor_Name.append(row[description_to_index["name"]])
                HA_Sensor_Name.append(row[description_to_index["name"]].lower().replace(" ", ""))
                MA_Sensor_Node_ID.append(row[description_to_index["node_id"]])
                MA_Sensor_Type.append(row[description_to_index["type"]])
                HA_Sensor_UID.append(row[description_to_index["uid"]].lower().replace(" ", ""))
            changed = True

    # check if zone configurtion has changed
    cur.execute(
        """SELECT CONCAT(`zone_sensors`.`zone_id`, '_', `z`.`name`) AS `uid`
           FROM `zone_sensors`
           JOIN zone z ON z.id = zone_sensors.zone_id
           ORDER BY zone_sensors.zone_sensor_id;"""
    )
    if cur.rowcount > 0:
        temp_array_1 = []
        results = cur.fetchall()
        for row in results:
            temp_array_1.append(row[0].lower().replace(" ", ""))

        narr1 = n.array([temp_array_1])
        narr2 = n.array([HA_Zone_UID])

        result_variable = (narr1 == narr2).all()
        if(result_variable == False):
            MA_Zone_Sensor_ID.clear()
            MA_Frost_Protection.clear()
            MA_Zone_ID.clear()
            MA_Zone_Name.clear()
            MA_Zone_Type.clear()
            HA_Zone_Name.clear()
            HA_Zone_UID.clear()
            cur.execute("""SELECT `n`.`node_id`, `zone_sensors`.`zone_id`, z.name, s.frost_temp, n.type, CONCAT(`zone_sensors`.`zone_id`, '_', `z`.`name`) AS `uid`
                           FROM `zone_sensors`
                           JOIN sensors s ON s.id = `zone_sensor_id`
                           JOIN nodes n ON n.id = s.`sensor_id`
                           JOIN zone z ON z.id = zone_sensors.zone_id
                           ORDER BY zone_sensors.zone_sensor_id;""")
            ZONES = cur.rowcount
            description_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
            results = cur.fetchall()
            for row in results:
                MA_Zone_ID.append(row[description_to_index["zone_id"]])
                MA_Zone_Sensor_ID.append(row[description_to_index["node_id"]])
                MA_Zone_Name.append(row[description_to_index["name"]])
                HA_Zone_Name.append(row[description_to_index["name"]].lower().replace(" ", ""))
                MA_Frost_Protection.append(row[description_to_index["frost_temp"]])
                MA_Zone_Type.append(row[description_to_index["type"]])
                HA_Zone_UID.append(row[description_to_index["uid"]].lower().replace(" ", ""))
            changed = True

    # on change re-send configuration
    if changed:
        mqttClient.disconnect()
        send_config_message(mqttClient)

    con.close()

def updateSensors():
    # check for MaxAir configuration changes
    check_maxair_changes()

    SC_Mode = get_SC_mode()
    payload_str = (
        "{"
        + f'"SC_mode": "{SC_Mode}",'  # System Controller Mode
        + f'"away_mode": "{get_away_status()}",'  # Away status
    )
    # System Controller Status
    if MA_Mode == 0:
        payload_str = payload_str + f'"boiler_status": "{get_SC_status()}"' + " }"
    else:
        payload_str = payload_str + f'"HVAC_status": "{get_SC_status()}"' + " }"
    mqttClient.publish(
        topic=f"{MQTT_TOPIC}SC/state",
        payload=payload_str,
        qos=1,
        retain=False,
    )

    # Stand-alone sensors status
    for sensor in range(SENSORS):
        sensor_status = get_sensor(sensor)
        if MA_Sensor_Measurement[sensor] == 1:  # Temperature sensor
            payload_str = "{" + f'"temperature": "{sensor_status[0]}"' + " }"
        elif MA_Sensor_Measurement[sensor] == 2:  # Humidity sensor
            payload_str = "{" + f'"humidity": "{sensor_status[0]}"' + " }"
        mqttClient.publish(
            topic=f"{MQTT_TOPIC}{HA_Sensor_Name[sensor]}/state",
            payload=payload_str,
            qos=1,
            retain=False,
        )
        if MA_Sensor_Type[sensor] == "MySensor":
            payload_str = (
                "{"
                + f'"last_seen": "{sensor_status[1]}",'
                + f'"batt_level": "{sensor_status[2]}",'
                + f'"batt_voltage": "{sensor_status[3]}"'
                + " }"
            )
        else:
            payload_str = "{" + f'"last_seen": "{sensor_status[1]}"' + " }"
        mqttClient.publish(
            topic=f"{MQTT_TOPIC}{HA_Sensor_Name[sensor]}/attributes",
            payload=payload_str,
            qos=1,
            retain=False,
        )

    # Zones status
    for zone in range(ZONES):
        zone_status = get_zone(zone, SC_Mode)
        # [0 - Zone Status, 1 - Traget Temp, 2 - Current Temp, 3 - Last seen 4 - Boost Status, 5 - Batt Level, 6 - Batt Voltage]
        payload_str = (
            "{"
            + f'"hvac_action": "{zone_status[0]}",'
            + f'"temperature": "{zone_status[1]}",'
            + f'"current_temperature": "{zone_status[2]}",'
            + f'"aux_heat": "{zone_status[4]}"'
            + " }"
        )
        mqttClient.publish(
            topic=f"{MQTT_TOPIC}{HA_Zone_Name[zone]}/state",
            payload=payload_str,
            qos=1,
            retain=False,
        )
        if MA_Zone_Type[zone] == "MySensor":
            payload_str = (
                "{"
                + f'"last_seen": "{zone_status[3]}",'
                + f'"batt_level": "{zone_status[5]}",'
                + f'"batt_voltage": "{zone_status[6]}"'
                + " }"
            )
        else:
            payload_str = "{" + f'"last_seen": "{zone_status[3]}"' + " }"
        mqttClient.publish(
            topic=f"{MQTT_TOPIC}{HA_Zone_Name[zone]}/attributes",
            payload=payload_str,
            qos=1,
            retain=False,
        )

    # MaxAir system status
    payload_str = (
        "{"
        + f'"temperature": {get_temp()},'
        + f'"disk_use": {get_disk_usage("/")},'
        + f'"memory_use": {get_memory_usage()},'
        + f'"cpu_usage": {get_cpu_usage()},'
        + f'"swap_usage": {get_swap_usage()},'
        + f'"last_boot": "{get_last_boot()}",'
        + f'"last_message": "{get_last_message()}",'
        + f'"host_name": "{get_host_name()}",'
        + f'"host_ip": "{get_host_ip()}",'
        + f'"host_os": "{get_host_os()}",'
        + f'"host_arch": "{get_host_arch()}",'
        + f'"load_1m": "{get_load(0)}",'
        + f'"load_5m": "{get_load(1)}",'
        + f'"load_15m": "{get_load(2)}",'
        + f'"net_tx": "{get_net_data()[0]}",'
        + f'"net_rx": "{get_net_data()[1]}"'
    )
    if CHECK_RPI_POWER:
        payload_str = payload_str + f', "power_status": "{get_rpi_power_status()}"'
    if CHECK_AVAILABLE_UPDATES:
        payload_str = payload_str + f', "updates": {get_updates()}'
    if CHECK_WIFI_STRENGHT:
        payload_str = payload_str + f', "wifi_strength": {get_wifi_strength()}'
    if CHECK_WIFI_SSID:
        payload_str = payload_str + f', "wifi_ssid": "{get_wifi_ssid()}"'
    payload_str = payload_str + "}"
    mqttClient.publish(
        topic=f"{MQTT_TOPIC}system/state",
        payload=payload_str,
        qos=1,
        retain=False,
    )

    # Boosts status
    for boost in range(BOOSTS):
        boost_status = get_boost(boost)
        payload_str = "{" + f'"boost_status": "{boost_status}"' + " }"
        mqttClient.publish(
            topic=f"{MQTT_TOPIC}BOOST/{HA_Boost_Zone_Name[boost]}/state",
            payload=payload_str,
            qos=1,
            retain=False,
        )

def get_updates():
    nFiles = 0
    for base, dirs, files in os.walk('/var/www/code_updates/'):
        for File in files:
            nFiles += 1
    nFiles = nFiles - 1
    return nFiles


# Temperature method depending on system distro
def get_temp():
    temp = ""
    if "rasp" in OS_DATA["ID"]:
        reading = check_output(["vcgencmd", "measure_temp"]).decode("UTF-8")
        temp = str(findall("\d+\.\d+", reading)[0])
    else:
        reading = check_output(["cat", "/sys/class/thermal/thermal_zone0/temp"]).decode(
            "UTF-8"
        )
        temp = str(reading[0] + reading[1] + "." + reading[2])
    return temp


def get_disk_usage(path):
    return str(psutil.disk_usage(path).percent)


def get_memory_usage():
    return str(psutil.virtual_memory().percent)


def get_load(arg):
    return str(psutil.getloadavg()[arg])


def get_net_data():
    global old_net_data
    global previous_time
    current_net_data = psutil.net_io_counters()
    current_time = time.time()
    net_data = (
        (current_net_data[0] - old_net_data[0])
        / (current_time - previous_time)
        * 8
        / 1024
    )
    net_data = (
        net_data,
        (current_net_data[1] - old_net_data[1])
        / (current_time - previous_time)
        * 8
        / 1024,
    )
    previous_time = current_time
    old_net_data = current_net_data
    return ["%.2f" % net_data[0], "%.2f" % net_data[1]]


def get_cpu_usage():
    return str(psutil.cpu_percent(interval=None))


def get_swap_usage():
    return str(psutil.swap_memory().percent)


def get_wifi_strength():  # check_output(["/proc/net/wireless", "grep wlan0"])
    wifi_strength_value = (
        check_output(
            [
                "bash",
                "-c",
                "cat /proc/net/wireless | grep wlan0: | awk '{print int($4)}'",
            ]
        )
        .decode("utf-8")
        .rstrip()
    )
    if not wifi_strength_value:
        wifi_strength_value = "0"
    return wifi_strength_value


def get_wifi_ssid():
    ssid = (
        check_output(
            [
                "bash",
                "-c",
                "/usr/sbin/iwgetid -r",
            ]
        )
        .decode("utf-8")
        .rstrip()
    )
    if not ssid:
        ssid = "UNKNOWN"
    return ssid


def get_rpi_power_status():
    _underVoltage = new_under_voltage()
    return _underVoltage.get()


def get_SC_status():
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cur = con.cursor()
    cur.execute("SELECT `active_status` FROM `system_controller` LIMIT 1;")
    results = cur.fetchone()
    con.close()
    if results[0] == 1:
        return "ON"
    else:
        return "OFF"


def get_away_status():
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cur = con.cursor()
    cur.execute("SELECT `status` FROM `away` LIMIT 1;")
    results = cur.fetchone()
    con.close()
    if results[0] == 0:
        return "OFF"
    else:
        return "ON"


def get_SC_mode():
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cur = con.cursor()
    cur.execute("SELECT `sc_mode` FROM `system_controller` LIMIT 1;")
    results = cur.fetchone()
    con.close()
    return results[0]


# [0 - Value, 1 - Last seen, 2 - Batt Level, 3 - Batt Voltage]
def get_sensor(sensor):
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cur = con.cursor()
    sensor_status = []
    cur.execute(
        "SELECT `payload`, `datetime` FROM `messages_in` WHERE `node_id` = (%s) AND `child_id` = (%s) ORDER BY `id` desc LIMIT 1;",
        [MA_Sensor_Node_ID[sensor], MA_Sensor_Child_ID[sensor]],
    )
    if cur.rowcount > 0:
        description_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
        results = cur.fetchone()
        if results[description_to_index["payload"]] is None:
            sensor_status.append("NA")  # [0] - Value
            sensor_status.append("NA")  # [1] - Last seen
        else:
            sensor_status.append(results[description_to_index["payload"]])  # [0] - Value
            sensor_status.append(
                results[description_to_index["datetime"]]
            )  # [1] - Last seen
    else:  # In case the database does not have yet a line for this sensor report NA
        sensor_status.append("NA")  # [0] - Value
        sensor_status.append("NA")  # [1] - Last seen
    if MA_Sensor_Type[sensor] == "MySensor":
        cur.execute(
            "SELECT `bat_level`, `bat_voltage`  FROM `nodes_battery` WHERE `node_id` = (%s) ORDER BY `id` desc LIMIT 1",
            [MA_Sensor_Node_ID[sensor]],
        )
        description_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
        if cur.rowcount > 0:
            results = cur.fetchone()
            if results[description_to_index["bat_level"]] is None:
                sensor_status.append("0")
            else:
                sensor_status.append(results[description_to_index["bat_level"]])
            if results[description_to_index["bat_voltage"]] is None:
                sensor_status.append("0")
            else:
                sensor_status.append(results[description_to_index["bat_voltage"]])
        else:  # In case the database does not have yet a line for this sensor report 0
            sensor_status.append("0")
            sensor_status.append("0")
    con.close()
    return sensor_status


# [0 - Zone Status, 1 - Traget Temp, 2 - Current Temp, 3 - Last seen, 4- Boost Status, 5 - Batt Level, 6 - Batt Voltage]
def get_zone(zone, SC_Mode):
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cur = con.cursor()
    zone_status = []
    cur.execute(
        "SELECT `status`, `temp_reading`, `temp_target`, IFNULL(`sensor_seen_time`,0) AS `sensor_seen_time` FROM `zone_current_state` WHERE `id` = (%s)",
        [MA_Zone_ID[zone]],
    )
    description_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
    results = cur.fetchone()
    # [0] - Zone Status & [1] - Traget Temp
    if results[2] == 0:  # No target temperature set, the zone is off
        zone_status.append("off")  # [0] - Zone Status
        zone_status.append(
            MA_Frost_Protection[zone]
        )  # [1] - Traget Temp equal to frost protection setpoint
    else:  # Traget temperature set the zone is active
        if (
            results[description_to_index["status"]] == 0
        ):  # The zone is not running at this time
            zone_status.append("idle")  # [0] - Zone Status
        else:  # The zone running at this time
            if MA_Mode == 0:  # SC in boiler mode, the zone can only be heating
                zone_status.append("heating")  # [0] - Zone Status
            else:  # SC in HVAC mode
                if SC_Mode == 4:  # HVAC in heating mode
                    zone_status.append("heating")  # [0] - Zone Status
                elif SC_Mode == 5:  # HVAC in colling mode
                    zone_status.append("cooling")  # [0] - Zone Status
                elif SC_Mode == 3:  # HVAC in fan only mode
                    zone_status.append("fan")  # [0] - Zone Status
                else:  # HVAC is in auto mode or timer mode
                    if (
                        results[description_to_index["temp_reading"]]
                        > results[description_to_index["temp_target"]]
                    ):  # Current temperature is higher than the set temperature
                        zone_status.append("cooling")  # [0] - Zone Status
                    else:  # Current temperature is lower than the set temperature
                        zone_status.append("heating")  # [0] - Zone Status
            # Modes for HVAC mode
            #   0 OFF   -> off
            #   1 Timer -> dry
            #   2 Auto  -> auto
            #   3 Fan   -> fan_only
            #   4 Heat  -> heat
            #   5 Cool  -> cool
        zone_status.append(results[2])  # [1] - Traget Temp
    # [2] - Current Temp
    zone_status.append(results[1])
    # [3] - Last seen
    zone_status.append(results[3])
    # [4] - Boost Status
    cur.execute(
        "SELECT `status` FROM `boost` WHERE `zone_id` = (%s)", [MA_Zone_ID[zone]]
    )
    if cur.rowcount > 0:
        if cur.fetchone()[0] == 0:
            zone_status.append("OFF")
        else:
            zone_status.append("ON")
    else:
        zone_status.append("OFF")
    # [5] - Batt Level & [6] - Batt Voltage
    if MA_Zone_Type[zone] == "MySensor":
        cur.execute(
            "SELECT `bat_level`, `bat_voltage`  FROM `nodes_battery` WHERE `node_id` = (%s) ORDER BY `id` desc LIMIT 1",
            [MA_Zone_Sensor_ID[zone]],
        )
        description_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
        if cur.rowcount > 0:
            results = cur.fetchone()
            if results[description_to_index["bat_level"]] is None:
                zone_status.append("0")
            else:
                zone_status.append(results[description_to_index["bat_level"]])
            if results[description_to_index["bat_voltage"]] is None:
                zone_status.append("0")
            else:
                zone_status.append(results[description_to_index["bat_voltage"]])
        else:  # In case the database does tnot have yet a line for this sensor report 0
            zone_status.append("0")
            zone_status.append("0")
    con.close()
    return zone_status

def get_boost(boost):
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cur = con.cursor()
    boost_status = []
    cur.execute(
        "SELECT `status` FROM `boost` WHERE `id` = (%s) LIMIT 1",
        [MA_Boost_ID[boost]],
    )
    result = cur.fetchone()
    con.close()
    if result[0] == 1:
        return "ON"
    else:
        return "OFF"

def get_host_name():
    return socket.gethostname()


def get_host_ip():
    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        sock.connect(("8.8.8.8", 80))
        return sock.getsockname()[0]
    except socket.error:
        try:
            return socket.gethostbyname(socket.gethostname())
        except socket.gaierror:
            return "127.0.0.1"
    finally:
        sock.close()


def get_host_os():
    try:
        return OS_DATA["PRETTY_NAME"]
    except:
        return "Unknown"


def get_host_arch():
    try:
        return platform.machine()
    except:
        return "Unknown"


def send_config_message(mqttClient):
    write_message_to_console("send config message")
    mqttClient.publish(
        topic=f"homeassistant/sensor/{deviceName}/temperature/config",
        payload='{"device_class":"temperature",'
        + f'"name":"{deviceNameDisplay} Temperature",'
        + f'"state_topic":"{MQTT_TOPIC}system/state",'
        + '"unit_of_measurement":"°C",'
        + '"value_template":"{{value_json.temperature}}",'
        + f'"unique_id":"{deviceName}_sensor_temperature",'
        + f'"availability_topic":"{MQTT_TOPIC}availability",'
        + f'"device":{{"identifiers":["{deviceName}_sensor"],'
        + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
        + f'"icon":"mdi:thermometer"}}',
        qos=1,
        retain=True,
    )

    mqttClient.publish(
        topic=f"homeassistant/sensor/{deviceName}/disk_use/config",
        payload=f'{{"name":"{deviceNameDisplay} Disk Use",'
        + f'"state_topic":"{MQTT_TOPIC}system/state",'
        + '"unit_of_measurement":"%",'
        + '"value_template":"{{value_json.disk_use}}",'
        + f'"unique_id":"{deviceName}_sensor_disk_use",'
        + f'"availability_topic":"{MQTT_TOPIC}availability",'
        + f'"device":{{"identifiers":["{deviceName}_sensor"],'
        + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
        + f'"icon":"mdi:micro-sd"}}',
        qos=1,
        retain=True,
    )

    mqttClient.publish(
        topic=f"homeassistant/sensor/{deviceName}/memory_use/config",
        payload=f'{{"name":"{deviceNameDisplay} Memory Use",'
        + f'"state_topic":"{MQTT_TOPIC}system/state",'
        + '"unit_of_measurement":"%",'
        + '"value_template":"{{value_json.memory_use}}",'
        + f'"unique_id":"{deviceName}_sensor_memory_use",'
        + f'"availability_topic":"{MQTT_TOPIC}availability",'
        + f'"device":{{"identifiers":["{deviceName}_sensor"],'
        + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
        + f'"icon":"mdi:memory"}}',
        qos=1,
        retain=True,
    )

    mqttClient.publish(
        topic=f"homeassistant/sensor/{deviceName}/cpu_usage/config",
        payload=f'{{"name":"{deviceNameDisplay} Cpu Usage",'
        + f'"state_topic":"{MQTT_TOPIC}system/state",'
        + '"unit_of_measurement":"%",'
        + '"value_template":"{{value_json.cpu_usage}}",'
        + f'"unique_id":"{deviceName}_sensor_cpu_usage",'
        + f'"availability_topic":"{MQTT_TOPIC}availability",'
        + f'"device":{{"identifiers":["{deviceName}_sensor"],'
        + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
        + f'"icon":"mdi:memory"}}',
        qos=1,
        retain=True,
    )

    mqttClient.publish(
        topic=f"homeassistant/sensor/{deviceName}/load_1m/config",
        payload=f'{{"name":"{deviceNameDisplay} Load 1m",'
        + f'"state_topic":"{MQTT_TOPIC}system/state",'
        + '"value_template":"{{value_json.load_1m}}",'
        + f'"unique_id":"{deviceName}_sensor_load_1m",'
        + f'"availability_topic":"{MQTT_TOPIC}availability",'
        + f'"device":{{"identifiers":["{deviceName}_sensor"],'
        + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
        + f'"icon":"mdi:cpu-64-bit"}}',
        qos=1,
        retain=True,
    )

    mqttClient.publish(
        topic=f"homeassistant/sensor/{deviceName}/load_5m/config",
        payload=f'{{"name":"{deviceNameDisplay} Load 5m",'
        + f'"state_topic":"{MQTT_TOPIC}system/state",'
        + '"value_template":"{{value_json.load_5m}}",'
        + f'"unique_id":"{deviceName}_sensor_load_5m",'
        + f'"availability_topic":"{MQTT_TOPIC}availability",'
        + f'"device":{{"identifiers":["{deviceName}_sensor"],'
        + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
        + f'"icon":"mdi:cpu-64-bit"}}',
        qos=1,
        retain=True,
    )

    mqttClient.publish(
        topic=f"homeassistant/sensor/{deviceName}/load_15m/config",
        payload=f'{{"name":"{deviceNameDisplay} Load 15m",'
        + f'"state_topic":"{MQTT_TOPIC}system/state",'
        + '"value_template":"{{value_json.load_15m}}",'
        + f'"unique_id":"{deviceName}_sensor_load_15m",'
        + f'"availability_topic":"{MQTT_TOPIC}availability",'
        + f'"device":{{"identifiers":["{deviceName}_sensor"],'
        + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
        + f'"icon":"mdi:cpu-64-bit"}}',
        qos=1,
        retain=True,
    )

    mqttClient.publish(
        topic=f"homeassistant/sensor/{deviceName}/net_tx/config",
        payload=f'{{"name":"{deviceNameDisplay} Network Upload",'
        + f'"state_topic":"{MQTT_TOPIC}system/state",'
        + '"unit_of_measurement":"Kb/sec",'
        + '"value_template":"{{value_json.net_tx}}",'
        + f'"unique_id":"{deviceName}_sensor_net_tx",'
        + f'"availability_topic":"{MQTT_TOPIC}availability",'
        + f'"device":{{"identifiers":["{deviceName}_sensor"],'
        + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
        + f'"icon":"mdi:server-network"}}',
        qos=1,
        retain=True,
    )

    mqttClient.publish(
        topic=f"homeassistant/sensor/{deviceName}/net_rx/config",
        payload=f'{{"name":"{deviceNameDisplay} Network Download",'
        + f'"state_topic":"{MQTT_TOPIC}system/state",'
        + '"unit_of_measurement":"Kb/sec",'
        + '"value_template":"{{value_json.net_rx}}",'
        + f'"unique_id":"{deviceName}_sensor_net_rx",'
        + f'"availability_topic":"{MQTT_TOPIC}availability",'
        + f'"device":{{"identifiers":["{deviceName}_sensor"],'
        + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
        + f'"icon":"mdi:server-network"}}',
        qos=1,
        retain=True,
    )

    mqttClient.publish(
        topic=f"homeassistant/sensor/{deviceName}/swap_usage/config",
        payload=f'{{"name":"{deviceNameDisplay} Swap Usage",'
        + f'"state_topic":"{MQTT_TOPIC}system/state",'
        + '"unit_of_measurement":"%",'
        + '"value_template":"{{value_json.swap_usage}}",'
        + f'"unique_id":"{deviceName}_sensor_swap_usage",'
        + f'"availability_topic":"{MQTT_TOPIC}availability",'
        + f'"device":{{"identifiers":["{deviceName}_sensor"],'
        + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
        + f'"icon":"mdi:harddisk"}}',
        qos=1,
        retain=True,
    )

    mqttClient.publish(
        topic=f"homeassistant/sensor/{deviceName}/last_boot/config",
        payload='{"device_class":"timestamp",'
        + f'"name":"{deviceNameDisplay} Last Boot",'
        + f'"state_topic":"{MQTT_TOPIC}system/state",'
        + '"value_template":"{{value_json.last_boot}}",'
        + f'"unique_id":"{deviceName}_sensor_last_boot",'
        + f'"availability_topic":"{MQTT_TOPIC}availability",'
        + f'"device":{{"identifiers":["{deviceName}_sensor"],'
        + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
        + f'"icon":"mdi:clock"}}',
        qos=1,
        retain=True,
    )
    mqttClient.publish(
        topic=f"homeassistant/sensor/{deviceName}/hostname/config",
        payload=f'{{"name":"{deviceNameDisplay} Hostname",'
        + f'"state_topic":"{MQTT_TOPIC}system/state",'
        + '"value_template":"{{value_json.host_name}}",'
        + f'"unique_id":"{deviceName}_sensor_host_name",'
        + f'"availability_topic":"{MQTT_TOPIC}availability",'
        + f'"device":{{"identifiers":["{deviceName}_sensor"],'
        + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
        + f'"icon":"mdi:card-account-details"}}',
        qos=1,
        retain=True,
    )
    mqttClient.publish(
        topic=f"homeassistant/sensor/{deviceName}/host_ip/config",
        payload=f'{{"name":"{deviceNameDisplay} Host Ip",'
        + f'"state_topic":"{MQTT_TOPIC}system/state",'
        + '"value_template":"{{value_json.host_ip}}",'
        + f'"unique_id":"{deviceName}_sensor_host_ip",'
        + f'"availability_topic":"{MQTT_TOPIC}availability",'
        + f'"device":{{"identifiers":["{deviceName}_sensor"],'
        + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
        + f'"icon":"mdi:lan"}}',
        qos=1,
        retain=True,
    )
    mqttClient.publish(
        topic=f"homeassistant/sensor/{deviceName}/host_os/config",
        payload=f'{{"name":"{deviceNameDisplay} Host OS",'
        + f'"state_topic":"{MQTT_TOPIC}system/state",'
        + '"value_template":"{{value_json.host_os}}",'
        + f'"unique_id":"{deviceName}_sensor_host_os",'
        + f'"availability_topic":"{MQTT_TOPIC}availability",'
        + f'"device":{{"identifiers":["{deviceName}_sensor"],'
        + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
        + f'"icon":"mdi:linux"}}',
        qos=1,
        retain=True,
    )
    mqttClient.publish(
        topic=f"homeassistant/sensor/{deviceName}/host_arch/config",
        payload=f'{{"name":"{deviceNameDisplay} Host Architecture",'
        + f'"state_topic":"{MQTT_TOPIC}system/state",'
        + '"value_template":"{{value_json.host_arch}}",'
        + f'"unique_id":"{deviceName}_sensor_host_arch",'
        + f'"availability_topic":"{MQTT_TOPIC}availability",'
        + f'"device":{{"identifiers":["{deviceName}_sensor"],'
        + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
        + f'"icon":"mdi:chip"}}',
        qos=1,
        retain=True,
    )
    mqttClient.publish(
        topic=f"homeassistant/sensor/{deviceName}/last_message/config",
        payload='{"device_class":"timestamp",'
        + f'"name":"{deviceNameDisplay} Last Message",'
        + f'"state_topic":"{MQTT_TOPIC}system/state",'
        + '"value_template":"{{value_json.last_message}}",'
        + f'"unique_id":"{deviceName}_sensor_last_message",'
        + f'"availability_topic":"{MQTT_TOPIC}availability",'
        + f'"device":{{"identifiers":["{deviceName}_sensor"],'
        + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
        + f'"icon":"mdi:clock-check"}}',
        qos=1,
        retain=True,
    )

    if CHECK_AVAILABLE_UPDATES:
        mqttClient.publish(
            topic=f"homeassistant/sensor/{deviceName}/updates/config",
            payload=f'{{"name":"{deviceNameDisplay} Updates",'
            + f'"state_topic":"{MQTT_TOPIC}system/state",'
            + '"value_template":"{{value_json.updates}}",'
            + f'"unique_id":"{deviceName}_sensor_updates",'
            + f'"availability_topic":"{MQTT_TOPIC}availability",'
            + f'"device":{{"identifiers":["{deviceName}_sensor"],'
            + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
            + f'"icon":"mdi:cellphone-arrow-down"}}',
            qos=1,
            retain=True,
        )

    if CHECK_RPI_POWER:
        mqttClient.publish(
            topic=f"homeassistant/binary_sensor/{deviceName}/power_status/config",
            payload='{"device_class":"problem",'
            + f'"name":"{deviceNameDisplay} Under Voltage",'
            + f'"state_topic":"{MQTT_TOPIC}system/state",'
            + '"value_template":"{{value_json.power_status}}",'
            + f'"unique_id":"{deviceName}_sensor_power_status",'
            + f'"availability_topic":"{MQTT_TOPIC}availability",'
            + f'"device":{{"identifiers":["{deviceName}_sensor"],'
            + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
            + f'"icon":"mdi:raspberry-pi"}}',
            qos=1,
            retain=True,
        )

    if CHECK_WIFI_STRENGHT:
        mqttClient.publish(
            topic=f"homeassistant/sensor/{deviceName}/wifi_strength/config",
            payload='{"device_class":"signal_strength",'
            + f'"name":"{deviceNameDisplay} Wifi Strength",'
            + f'"state_topic":"{MQTT_TOPIC}system/state",'
            + '"unit_of_measurement":"dBm",'
            + '"value_template":"{{value_json.wifi_strength}}",'
            + f'"unique_id":"{deviceName}_sensor_wifi_strength",'
            + f'"availability_topic":"{MQTT_TOPIC}availability",'
            + f'"device":{{"identifiers":["{deviceName}_sensor"],'
            + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
            + f'"icon":"mdi:wifi"}}',
            qos=1,
            retain=True,
        )

    if CHECK_WIFI_SSID:
        mqttClient.publish(
            topic=f"homeassistant/sensor/{deviceName}/wifi_ssid/config",
            payload='{"device_class":"signal_strength",'
            + f'"name":"{deviceNameDisplay} Wifi SSID",'
            + f'"state_topic":"{MQTT_TOPIC}system/state",'
            + '"value_template":"{{value_json.wifi_ssid}}",'
            + f'"unique_id":"{deviceName}_sensor_wifi_ssid",'
            + f'"availability_topic":"{MQTT_TOPIC}availability",'
            + f'"device":{{"identifiers":["{deviceName}_sensor"],'
            + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
            + f'"icon":"mdi:wifi"}}',
            qos=1,
            retain=True,
        )

    if MA_Mode == 0:
        mqttClient.publish(
            topic=f"homeassistant/binary_sensor/{deviceName}/boiler_status/config",
            payload='{"device_class":"heat",'
            + f'"name":"{deviceNameDisplay} Boiler",'
            + f'"state_topic":"{MQTT_TOPIC}SC/state",'
            + '"value_template":"{{value_json.boiler_status}}",'
            + f'"unique_id":"{deviceName}_boiler_status",'
            + f'"availability_topic":"{MQTT_TOPIC}availability",'
            + f'"device":{{"identifiers":["{deviceName}_sensor"],'
            + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
            + '"force_update":true'
            + f"}}",
            qos=1,
            retain=True,
        )
    else:
        mqttClient.publish(
            topic=f"homeassistant/binary_sensor/{deviceName}/HVAC_status/config",
            payload='{"device_class":"heat",'
            + f'"name":"{deviceNameDisplay} HVAC",'
            + f'"state_topic":"{MQTT_TOPIC}SC/state",'
            + '"value_template":"{{value_json.HVAC_status}}",'
            + f'"unique_id":"{deviceName}_HVAC_status",'
            + f'"availability_topic":"{MQTT_TOPIC}availability",'
            + f'"device":{{"identifiers":["{deviceName}_sensor"],'
            + f'"name":"{deviceNameDisplay} Sensors","model":"RPI {deviceNameDisplay}", "manufacturer":"RPI"}},'
            + '"force_update":true'
            + f"}}",
            qos=1,
            retain=True,
        )

    for sensor in range(SENSORS):
        if MA_Sensor_Measurement[sensor] == 1:  # Temperature sensor
            payload_str = (
                '{"device_class":"temperature",'
                + '"unit_of_measurement":"°C",'
                + '"value_template":"{{ value_json.temperature }}",'
                + '"icon":"mdi:thermometer",'
                + f'"name":"{deviceNameDisplay} {MA_Sensor_Name[sensor]} Temperature",'
                + f'"unique_id":"{deviceName}_{HA_Sensor_Name[sensor]}_temperature",'
                + f'"device":{{"identifiers":["{deviceName}_sensor"],'
                + f'"name":"{deviceNameDisplay} {MA_Sensor_Name[sensor]}","model":"Stand-alone Temperature sensor", "manufacturer":"MaxAir"}},'
            )
        elif MA_Sensor_Measurement[sensor] == 2:  # Humidity sensor
            payload_str = (
                '{"device_class":"humidity",'
                + '"unit_of_measurement":"%",'
                + '"value_template":"{{ value_json.humidity }}",'
                + '"icon":"mdi:water-percent",'
                + f'"name":"{deviceNameDisplay} {MA_Sensor_Name[sensor]} Humidity",'
                + f'"unique_id":"{deviceName}_{HA_Sensor_Name[sensor]}_humidity",'
                + f'"device":{{"identifiers":["{deviceName}_sensor"],'
                + f'"name":"{deviceNameDisplay} {MA_Sensor_Name[sensor]}","model":"Stand-alone Humidity sensor", "manufacturer":"MaxAir"}},'
            )
        payload_str = (
            payload_str
            + f'"state_topic":"{MQTT_TOPIC}{HA_Sensor_Name[sensor]}/state",'
            + f'"availability_topic":"{MQTT_TOPIC}availability",'
            + f'"json_attributes_topic":"{MQTT_TOPIC}{HA_Sensor_Name[sensor]}/attributes",'
            + f'"device":{{"identifiers":["{deviceName}_{HA_Sensor_Name[sensor]}"]'
            + "}}"
        )
        mqttClient.publish(
            topic=f"homeassistant/sensor/{deviceName}/{HA_Sensor_Name[sensor]}/config",
            payload=payload_str,
            qos=1,
            retain=True,
        )

    for zone in range(ZONES):
        if MA_Mode == 0:
            payload_str = (
                '{"modes": ["auto", "off", "heat", "dry", "fan_only"],'  # Modes when in boiler mod
                + "\"mode_state_template\":\"{% set values = { '0':'off', '1':'auto', '2':'heat', '3':'fan_only', '4':'dry'} %} {{ values[value_json.SC_mode]}}\","
                + "\"mode_command_template\":\"{% set values = { 'off':'0', 'auto':'1', 'heat':'2', 'fan_only':'3', 'dry':'4'} %} {{ values[value] }}\","
            )
            # Modes for boiler mode
            #   0 OFF   -> off
            #   1 Timer -> auto
            #   2 CH    -> heat
            #   3 HW    -> fan_only
            #   4 Both  -> dry
        else:
            payload_str = (
                '{"modes": ["auto", "off", "cool", "heat", "dry", "fan_only"],'  # Modes when in HVAC mode
                + "\"mode_state_template\":\"{% set values = { '0':'off', '1':'dry', '2':'auto', '3':'fan_only', '4':'heat', '5':'cool'} %} {{ values[value_json.SC_mode]}}\","
                + "\"mode_command_template\":\"{% set values = { 'off':'0', 'dry':'1', 'auto':'2', 'fan_only':'3', 'heat':'4', 'cool':'5'} %} {{ values[value] }}\","
            )
            # Modes for HVAC mode
            #   0 OFF   -> off
            #   1 Timer -> dry
            #   2 Auto  -> auto
            #   3 Fan   -> fan_only
            #   4 Heat  -> heat
            #   5 Cool  -> cool
        payload_str = (
            payload_str
            + '"temp_unit":"C",'
            # + '"force_update":true,'
            + f'"availability_topic":"{MQTT_TOPIC}availability",'
            + f'"away_mode_command_topic":"{MQTT_TOPIC}SC/away_command",'
            + f'"away_mode_state_topic":"{MQTT_TOPIC}SC/state",'
            + '"away_mode_state_template": "{{ value_json.away_mode}}",'
            + f'"unique_id":"{deviceName}_{HA_Zone_Name[zone]}",'
            + f'"name":"{deviceNameDisplay} {MA_Zone_Name[zone]}",'
            + f'"mode_state_topic":"{MQTT_TOPIC}SC/state",'
            + '"mode_command_topic":"MaxAir/SC/mode_command",'
            + f'"action_topic":"{MQTT_TOPIC}{HA_Zone_Name[zone]}/state",'
            + '"action_template":"{{ value_json.hvac_action }}",'
            + f'"aux_command_topic":"{MQTT_TOPIC}{HA_Zone_Name[zone]}/aux_command",'
            + f'"aux_state_topic":"{MQTT_TOPIC}{HA_Zone_Name[zone]}/state",'
            + '"aux_state_template":"{{ value_json.aux_heat }}",'
            + f'"current_temperature_topic":"{MQTT_TOPIC}{HA_Zone_Name[zone]}/state",'
            + '"current_temperature_template":"{{ value_json.current_temperature }}",'
            + f'"temperature_state_topic":"{MQTT_TOPIC}{HA_Zone_Name[zone]}/state",'
            + '"temperature_state_template":"{{ value_json.temperature }}",'
            + f'"temperature_command_topic":"{MQTT_TOPIC}{HA_Zone_Name[zone]}/target_temp",'
            + f'"json_attributes_topic":"{MQTT_TOPIC}{HA_Zone_Name[zone]}/attributes",'
            + f'"device":{{"identifiers":["{deviceName}_{HA_Zone_Name[zone]}"],'
            + f'"name":"{deviceNameDisplay} {MA_Zone_Name[zone]}","model":"{MA_Zone_Type[zone]}", "manufacturer":"MaxAir"}}'
            + "}"
        )
        mqttClient.publish(
            topic=f"homeassistant/climate/{deviceName}/{HA_Zone_Name[zone]}/config",
            payload=payload_str,
            qos=1,
            retain=True,
        )

    for boost in range(BOOSTS):
        mqttClient.publish(
            topic=f"homeassistant/switch/{deviceName}/{HA_Boost_Zone_Name[boost]}/config",
            payload='{'
            + f'"name":"{deviceNameDisplay} {MA_Boost_Zone_Name[boost]} BOOST",'
            + f'"state_topic":"{MQTT_TOPIC}BOOST/{HA_Boost_Zone_Name[boost]}/state",'
            + f'"command_topic":"{MQTT_TOPIC}BOOST/{HA_Boost_Zone_Name[boost]}/boost_command",'
            + '"value_template":"{{value_json.boost_status}}",'
            + f'"unique_id":"{deviceName}_{HA_Boost_Zone_Name[boost]}_boost_status",'
            + f'"availability_topic":"{MQTT_TOPIC}availability",'
            + f'"device":{{"identifiers":["{deviceName}_boost"],'
            + f'"name":"{deviceNameDisplay} BOOST","model":"MaxAir {deviceNameDisplay}", "manufacturer":"MaxAir"}},'
            + '"payload_on" : "ON" ,'
            + '"payload_off" : "OFF" ,'
            + '"force_update":true'
            + f"}}",
            qos=1,
            retain=True,
        )

    mqttClient.publish(f"{MQTT_TOPIC}availability", "online", retain=True)


def on_connect_1(client, userdata, flags, rc):
    if rc == 0:
        write_message_to_console("Connected to broker")
        subscribe_topics = [
            ("homeassistant/status", 0),
            (f"{MQTT_TOPIC}SC/away_command", 0),
            (f"{MQTT_TOPIC}SC/mode_command", 0),
        ]
        for zone in range(ZONES):
            subscribe_topics.append(
                (f"{MQTT_TOPIC}{HA_Zone_Name[zone]}/aux_command", 0)
            )
            subscribe_topics.append(
                (f"{MQTT_TOPIC}{HA_Zone_Name[zone]}/target_temp", 0)
            )
        for boost in range(BOOSTS):
            subscribe_topics.append(
                (f"{MQTT_TOPIC}BOOST/{HA_Boost_Zone_Name[boost]}/boost_command", 0)
            )
        client.subscribe(subscribe_topics)
        mqttClient.publish(f"{MQTT_TOPIC}availability", "online", retain=True)
    else:
        write_message_to_console("Connection failed")

def on_disconnect_1(client, userdata, reason_code):
    write_message_to_console("Disconnected with result code: " + str(reason_code))
    reconnect_count, reconnect_delay = 0, FIRST_RECONNECT_DELAY
    while reconnect_count < MAX_RECONNECT_COUNT:
        write_message_to_console("Reconnecting in " + str(reconnect_delay) +  " seconds...")
        time.sleep(reconnect_delay)

        try:
            client.reconnect()
            write_message_to_console("Reconnected successfully!")
            return
        except Exception as err:
            write_message_to_console(str(err) + " Reconnect failed. Retrying...")

        reconnect_delay *= RECONNECT_RATE
        reconnect_delay = min(reconnect_delay, MAX_RECONNECT_DELAY)
        reconnect_count += 1
    write_message_to_console("Reconnect failed after %s attempts. Exiting... " + str(reconnect_count))

# Function run when the MQTT client connect to the brooker for paho-mqtt Version 2
def on_connect_2(client, userdata, flags, reason_code, properties):
    if reason_code.is_failure:
        write_message_to_console("\nConnection failed\n")
    else:
        write_message_to_console("Connected to broker")
        subscribe_topics = [
            ("homeassistant/status", 0),
            (f"{MQTT_TOPIC}SC/away_command", 0),
            (f"{MQTT_TOPIC}SC/mode_command", 0),
        ]
        for zone in range(ZONES):
            subscribe_topics.append(
                (f"{MQTT_TOPIC}{HA_Zone_Name[zone]}/aux_command", 0)
            )
            subscribe_topics.append(
                (f"{MQTT_TOPIC}{HA_Zone_Name[zone]}/target_temp", 0)
            )
        for boost in range(BOOSTS):
            subscribe_topics.append(
                (f"{MQTT_TOPIC}BOOST/{HA_Boost_Zone_Name[boost]}/boost_command", 0)
            )
        client.subscribe(subscribe_topics)
        mqttClient.publish(f"{MQTT_TOPIC}availability", "online", retain=True)

# Function run when the MQTT client disconnects to the brooker for paho-mqtt Version 2
def on_disconnect_2(client, userdata, flags, reason_code, properties):
    write_message_to_console("Disconnected with result code: " + str(reason_code))
    reconnect_count, reconnect_delay = 0, FIRST_RECONNECT_DELAY
    while reconnect_count < MAX_RECONNECT_COUNT:
        write_message_to_console("Reconnecting in " + str(reconnect_delay) + " seconds...")
        time.sleep(reconnect_delay)

        try:
            client.reconnect()
            write_message_to_console("Reconnected successfully!")
            return
        except Exception as err:
            write_message_to_console(str(err) + "Reconnect failed. Retrying...")

        reconnect_delay *= RECONNECT_RATE
        reconnect_delay = min(reconnect_delay, MAX_RECONNECT_DELAY)
        reconnect_count += 1
    write_message_to_console("Reconnect failed after %s attempts. Exiting... " + str(reconnect_count))

# -----------------------------------------------------------------------------------------------------------------------------------
if __name__ == "__main__":
    write_message_to_console("paho-mqtt Version: " + str(paho_version))
    # Check that MQTT details have been added
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cur = con.cursor()
    cur.execute("SELECT `timezone` FROM `system` LIMIT 1;")
    DEFAULT_TIME_ZONE = timezone(cur.fetchone()[0])
    # cur.execute('SELECT COUNT(*) FROM `mqtt` WHERE `type` = 3 AND `enabled` = 1;')
    cur.execute("SELECT * FROM `mqtt` where `type` = 3 AND `enabled` = 1;")
    if cur.rowcount > 0:
        if cur.rowcount > 1:
            # If more than one MQTT connection has been defined do not connect
            write_message_to_console(
                "More than one MQTT connection defined in MaxAir for Home Assistant integration, please remove the unused ones."
            )
            MQTT_CONNECTED = 0
        else:
            results = cur.fetchone()
            con.close()
            description_to_index = dict(
                (d[0], i) for i, d in enumerate(cur.description)
            )
            MQTT_HOSTNAME = results[description_to_index["ip"]]
            MQTT_PORT = results[description_to_index["port"]]
            MQTT_USERNAME = results[description_to_index["username"]]
            result = subprocess.run(
                ['php', '/var/www/cron/mqtt_passwd_decrypt.php', '3'],         # program and arguments
                stdout=subprocess.PIPE,                     # capture stdout
                check=True                                  # raise exception if program fails
            )
            MQTT_PASSWORD = result.stdout.decode("utf-8").split()[0] # result.stdout contains a byte-string
            if paho_version.find("1.5.0") != -1:
                mqttClient = mqtt.Client(MQTT_CLIENT_ID)
                mqttClient.enable_logger()
                mqttClient.on_connect = on_connect_1  # attach function to callback
                mqttClient.on_disconnect = on_disconnect_1  # attach function to callback
            else:
                mqttClient = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2, client_id=MQTT_CLIENT_ID)
                mqttClient.enable_logger()
                mqttClient.on_connect = on_connect_2  # attach function to callback
                mqttClient.on_disconnect = on_disconnect_2  # attach function to callback
            mqttClient.on_message = on_message
            deviceName = MQTT_deviceName.replace(" ", "").lower()
            deviceNameDisplay = MQTT_deviceName
            mqttClient.will_set(f"{MQTT_TOPIC}availability", "offline", retain=True)
            mqttClient.username_pw_set(MQTT_USERNAME, MQTT_PASSWORD)
            signal.signal(signal.SIGTERM, signal_handler)
            signal.signal(signal.SIGINT, signal_handler)
            mqttClient.connect(MQTT_HOSTNAME, MQTT_PORT)
            try:
                send_config_message(mqttClient)
            except:
                write_message_to_console("something whent wrong")
                if CHECK_RPI_POWER:
                    _underVoltage = new_under_voltage()
            job = Job(
                interval=timedelta(seconds=WAIT_TIME_SECONDS), execute=updateSensors
            )
            job.start()
            mqttClient.loop_start()
    else:
        # If no MQTT connection has been defined do not connect
        write_message_to_console(
            "Home Assistant integration is disabled. To enable MQTT Home Assistant integration enter the connection details under Settings > System Configuration > MQTT."
        )

    while True:
        try:
            sys.stdout.flush()
            time.sleep(1)
        except ProgramKilled:
            write_message_to_console("Program killed: running cleanup code")
            mqttClient.publish(f"{MQTT_TOPIC}availability", "offline", retain=True)
            mqttClient.loop_stop()
            MQTT_CONNECTED = 0
            if (con.open):
                con.close()
            sys.stdout.flush()
            job.stop()
            break
