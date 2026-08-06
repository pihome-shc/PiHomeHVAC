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
print("*            PVVX to MQTT Bridge Script                *")
print("*      Build Date: 01/06/2026                          *")
print("*      Version 0.01 - Last Modified 01/06/2026         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

line_len = 120; #length of seperator lines

import asyncio
import struct
import time
from datetime import datetime
from bleak import BleakScanner
import paho.mqtt.client as mqtt
import json
import configparser
import MySQLdb as mdb
import subprocess
import platform
if int(platform.python_version().split(".")[1]) < 8:
    import pkg_resources
    paho_version = pkg_resources.get_distribution("paho-mqtt").version
else:
    from importlib.metadata import version
    paho_version = version("paho-mqtt")

# =========== DB CONNECTION ==============
config = configparser.ConfigParser()
config.read('/var/www/st_inc/db_config.ini')
con = mdb.connect(
    config.get('db', 'hostname'),
    config.get('db', 'dbusername'),
    config.get('db', 'dbpassword'),
    config.get('db', 'dbname')
)
cur = con.cursor()

# ================= MQTT =================
cur.execute("SELECT ip, port, username FROM mqtt WHERE enabled=1 AND type=2 LIMIT 1;")
row = cur.fetchone()
row_to_index = dict(
    (d[0], i) for i, d in enumerate(cur.description)
)
MQTT_HOST = row[row_to_index["ip"]]
MQTT_PORT = row[row_to_index["port"]]
MQTT_USER = row[row_to_index["username"]]
if "anonymous" not in MQTT_USER:
    result = subprocess.run(
        ['php', '/var/www/cron/mqtt_passwd_decrypt.php', '2'],         # program and arguments
        stdout=subprocess.PIPE,                     # capture stdout
        check=True                                  # raise exception if program fails
        )
    MQTT_PASS = result.stdout.decode("utf-8").split()[0] # result.stdout contains a byte-string
MQTT_CLIENT_ID = "PVVX_MQTT_Bridge"  # MQTT Client ID

cur = con.cursor()
cur.execute("SELECT mqtt_topic FROM mqtt_devices WHERE attribute LIKE 'ATC%' OR mqtt_topic LIKE 'atc%' LIMIT 1;")
if cur.rowcount > 0 :
    row = cur.fetchone()
    row_to_index = dict(
        (d[0], i) for i, d in enumerate(cur.description)
    )
    TOPIC = row[row_to_index["mqtt_topic"]]
    cur.close()
else:
    TOPIC = "tele/SENSOR"

cur.close()

# ================= BEHAVIOUR =================
PUBLISH_INTERVAL = 55  # seconds
state = {}
last_publish = 0

# ================= MQTT CLIENT =================
if paho_version.find("1.5.0") != -1:
    client = mqtt.Client(MQTT_CLIENT_ID)
else:
    client = mqtt.Client(callback_api_version=mqtt.CallbackAPIVersion.VERSION2, client_id=MQTT_CLIENT_ID)
if "anonymous" not in MQTT_USER:
    client.username_pw_set(MQTT_USER, MQTT_PASS)
client.connect(MQTT_HOST, MQTT_PORT, 60)
client.loop_start()


# ===== MAC -> Tasmota-style ATC key =====
def mac_to_atc(mac: str):
    return "ATC" + mac.replace(":", "")[-6:].lower()

# ===== UPDATE STATE =====
def update_state(mac, parsed):

    key = mac_to_atc(mac)

    state[key] = {
        "Temperature": parsed["Temperature"],
        "Humidity": parsed["Humidity"],
        "Battery": parsed["Battery"],
        "LastUpdate": time.time()
    }

# ===== PUBLISH FULL TASMOTA SENSOR FRAME =====
def publish():
    global last_publish

    payload = {
        "Time": datetime.now().isoformat(),
    }

    # strip internal metadata before publishing
    for k, v in state.items():
        payload[k] = {
            "Temperature": v["Temperature"],
            "Humidity": v["Humidity"],
            "Battery": v["Battery"]
        }

    client.publish(TOPIC, json.dumps(payload), qos=0, retain=False)
    print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Message Published: " + str(payload))
    print("-" * line_len)

    last_publish = time.time()

# ===== BLE CALLBACK =====
def callback(device, advertisement_data):
    ENVIRONMENTAL_SENSING_UUID = "0000181a-0000-1000-8000-00805f9b34fb"

    service_data = advertisement_data.service_data

    if not service_data:
        return

    for uuid, data in service_data.items():

        # Environmental sensing UUID
        if uuid.lower() == ENVIRONMENTAL_SENSING_UUID:

            try:
                # PVVX custom format — little-endian, hundredths
                temp = int.from_bytes(data[6:8], 'little', signed=True) / 100
                hum  = int.from_bytes(data[8:10], 'little') / 100
                batt = data[12]  # battery % at offset 12, after 2-byte voltage at 10-11

                parsed = {
                    "Temperature": round(temp, 1),
                    "Humidity":    round(hum, 1),
                    "Battery":     batt
                }

                update_state(device.address, parsed)
                print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Address: " + str(device.address) + ", Meassage Read" + str(parsed))
                print("-" * line_len)

            except Exception as e:
                print("PARSE ERROR:", e)

# ===== MAIN LOOP =====
async def main():
    global last_publish

    print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - PVVX To MQTT Bridge Script  Started")
    print("-" * line_len)

    scanner = BleakScanner(callback)
    await scanner.start()

    while True:
        await asyncio.sleep(5)

        # publish every 55 seconds regardless of sensor updates
        if time.time() - last_publish > PUBLISH_INTERVAL:
            if state:
                publish()


if __name__ == "__main__":
    asyncio.run(main())
