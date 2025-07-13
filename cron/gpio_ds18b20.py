#!/usr/bin/python
import time, os, fnmatch, MySQLdb as mdb, logging
from decimal import Decimal
import configparser
from datetime import datetime
import math

class bc:
    hed = "\033[0;36;40m"
    dtm = "\033[0;36;40m"
    ENDC = "\033[0m"
    SUB = "\033[3;30;45m"
    WARN = "\033[0;31;40m"
    grn = "\033[0;32;40m"
    wht = "\033[0;37;40m"


# Parameters for spike removal and data smoothing
dT_max = (
    3  # Maximum difference in tempearture between consecuive readings of the seonsor
)
skip_max = 3  # Maximum number of readings skipped if dT is greater than dT_Max
alpha = 1  # Alpha for expnential weighted moving average. Value must be between 0 and 1 (alpha = 1 means EWMA is disabled)

update_rate = 60  # Update rate for DS18b20 sensors in seconds

print(bc.hed + " ")
print("    __  __                             _         ")
print("   |  \/  |                    /\     (_)        ")
print("   | \  / |   __ _  __  __    /  \     _   _ __  ")
print("   | |\/| |  / _` | \ \/ /   / /\ \   | | | '__| ")
print("   | |  | | | (_| |  >  <   / ____ \  | | | |    ")
print("   |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|    ")
print(" ")
print("             " + bc.SUB + "S M A R T   THERMOSTAT " + bc.ENDC)
print(bc.WARN + " ")
print("***********************************************************")
print("*   PiHome DS18B20 Temperature Sensors Data to MySQL DB   *")
print("* Use this script if you have DS18B20 Temperature sensors *")
print("* Connected directly on Raspberry Pi GPIO.                *")
print("*                                  Build Date: 26/11/2022 *")
print("*                                    Have Fun - PiHome.eu *")
print("***********************************************************")
print(" " + bc.ENDC)
logging.basicConfig(
    filename="/var/www/cron/logs/DS18B20_error.log",
    level=logging.DEBUG,
    format="%(asctime)s %(levelname)s %(name)s %(message)s",
)
logger = logging.getLogger(__name__)

# Add in the w1_gpio and w1_therm modules
os.system("modprobe w1-gpio")
os.system("modprobe w1-therm")

# Initialise the database access variables
config = configparser.ConfigParser()
config.read("/var/www/st_inc/db_config.ini")
dbhost = config.get("db", "hostname")
dbuser = config.get("db", "dbusername")
dbpass = config.get("db", "dbpassword")
dbname = config.get("db", "dbname")

null_value = None
hour_timer = time.time()
gpio_recv = 0

print(bc.dtm + time.ctime() + bc.ENDC + " - DS18B20 Temperature Sensors Script Started")
print("-" * 70)

# Function for Storing DS18B20 Temperature Readings into MySQL
def insertDB(IDs, temperature):
    global hour_timer
    global gpio_recv
    try:
        con = mdb.connect(dbhost, dbuser, dbpass, dbname)
        cur = con.cursor()
        for i in range(0, len(temperature)):
            # Check if Sensors Already Exit in Nodes Table, if no then add Sensors into Nodes Table otherwise just update Temperature Readings.
            cur.execute("SELECT COUNT(*) FROM `nodes` where node_id = (%s)", [IDs[i]])
            row = cur.fetchone()
            row = int(row[0])
            if row == 0:
                print(
                    bc.dtm
                    + time.ctime()
                    + bc.ENDC
                    + " - New DS18B20 Sensors Discovered"
                    + bc.grn,
                    IDs[i],
                    bc.ENDC,
                )
                cur.execute(
                    "INSERT INTO nodes(`sync`, `purge`, `type`, `node_id`, `max_child_id`, `sub_type`, `name`, `last_seen`, `notice_interval`, `min_value`, `status`, `ms_version`, `sketch_version`, `repeater`) VALUES( %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                    (
                        0,
                        0,
                        "GPIOSensor",
                        IDs[i],
                        "0",
                        "0",
                        "Temperature Sensor",
                        time.strftime("%Y-%m-%d %H:%M:%S"),
                        0,
                        0,
                        "Active",
                        0,
                        0,
                        0,
                    ),
                )
                con.commit()
            # Check if this sensor has a correction factor
            cur.execute(
                "SELECT sensors.sensor_id, sensors.mode, sensors.timeout, sensors.correction_factor, sensors.resolution FROM sensors, `nodes` WHERE (sensors.sensor_id = nodes.`id`) AND  nodes.node_id = (%s) LIMIT 1;",
                [IDs[i]],
            )
            results = cur.fetchone()
            if cur.rowcount > 0:
                sensor_to_index = dict(
                    (d[0], i) for i, d in enumerate(cur.description)
                )
                correction_factor = float(results[sensor_to_index["correction_factor"]])
                temp = temperature[i] + correction_factor
                # If DS18B20 Sensor record exist: Update Nodes Table with Last seen status.
                if row == 1:
                    cur.execute(
                        "UPDATE `nodes` SET `last_seen`=%s WHERE node_id = %s", [time.strftime("%Y-%m-%d %H:%M:%S"), IDs[i]]
                    )
                    con.commit()
                print(
                    bc.dtm + time.ctime() + bc.ENDC + " - Sensors ID" + bc.grn,
                    IDs[i],
                    bc.ENDC + "Temperature" + bc.grn,
                    temp,
                    bc.ENDC,
                )

                payload = math.floor(temp*100)/100
                timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                sensor_id = results[sensor_to_index["sensor_id"]]
                mode = results[sensor_to_index["mode"]]
                sensor_timeout = int(results[sensor_to_index["timeout"]])*60
                tdelta = 0
                last_message_payload = 0
                resolution = float(results[sensor_to_index["resolution"]])
                # Update last reading for this sensor
                cur.execute(
                    "UPDATE `sensors` SET `current_val_1` = %s, `last_seen` = %s WHERE sensor_id = %s AND sensor_child_id = 0",
                    [payload, datetime.now().strftime("%Y-%m-%d %H:%M:%S"), sensor_id],
                )
                con.commit()

                #update the gateway_logs entry
                if time.time() - hour_timer <= 60*60:
                    gpio_recv += 1
                    cur.execute(
                        "UPDATE gateway_logs SET gpio_recv = %s ORDER BY id DESC LIMIT 1;",
                        (gpio_recv, ),
                    )
                    con.commit()
                else:
                    gpio_recv = 0
                    hour_timer = time.time()

                if mode == 1:
                    # Get previous data for this sensorr
                    cur.execute(
                        'SELECT datetime, payload FROM messages_in WHERE node_id = %s AND child_id = %s ORDER BY id DESC LIMIT 1;',
                        (IDs[i], 0),
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
                    if tdelta > sensor_timeout:
                        payload = last_message_payload
                    cur.execute(
                        "INSERT INTO messages_in(`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s)",
                        (
                            0,
                            0,
                            IDs[i],
                            0,
                            0,
                            payload,
                            time.strftime("%Y-%m-%d %H:%M:%S"),
                        ),
                    )
                    con.commit()
                    # Check is sensor is attached to a zone which is being graphed
                    # cur.execute('SELECT * FROM `zone_view` where sensors_id = (%s) LIMIT 1;', [IDs[i]])
                    cur.execute(
                        "SELECT sensors.id, sensors.zone_id, nodes.node_id, sensors.sensor_child_id, sensors.name, sensors.graph_num FROM sensors, `nodes` WHERE (sensors.sensor_id = nodes.`id`) AND  nodes.node_id = (%s) AND sensors.graph_num > 0 LIMIT 1;",
                        [IDs[i]],
                    )
                    results = cur.fetchone()
                    if cur.rowcount > 0:
                        sensor_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                        sensor_id = int(results[sensor_to_index["id"]])
                        sensor_name = results[sensor_to_index["name"]]
                        zone_id = results[sensor_to_index["zone_id"]]
                        # type = results[zone_view_to_index['type']]
                        # category = int(results[zone_view_to_index['category']])
                        graph_num = int(results[sensor_to_index["graph_num"]])
                        if graph_num > 0:
                            print(
                                bc.dtm
                                + time.ctime()
                                + bc.ENDC
                                + " - Adding Temperature Reading to Graph Table From Node ID:",
                                IDs[i],
                                " PayLoad:",
                                payload,
                            )
                            if zone_id == 0:
                                category = 0
                                type = "Sensor"
                                cur.execute(
                                    "INSERT INTO sensor_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
                                    (
                                        0,
                                        0,
                                        sensor_id,
                                        sensor_name,
                                        type,
                                        category,
                                        IDs[i],
                                        0,
                                        0,
                                        payload,
                                        time.strftime("%Y-%m-%d %H:%M:%S"),
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
                                    if category != 2:
                                        cur.execute(
                                            "INSERT INTO sensor_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
                                            (
                                                0,
                                                0,
                                                sensor_id,
                                                zone_name,
                                                type,
                                                category,
                                                IDs[i],
                                                0,
                                                0,
                                                payload,
                                                time.strftime("%Y-%m-%d %H:%M:%S"),
                                            ),
                                        )
                                        con.commit()
                            cur.execute(
                                "DELETE FROM sensor_graphs WHERE node_id = (%s) AND datetime < CURRENT_TIMESTAMP - INTERVAL 24 HOUR;",
                                [IDs[i]],
                            )
                            con.commit()
        con.close()
    except mdb.Error as e:
        logger.error(e)
        print(bc.dtm + time.ctime() + bc.ENDC + " - DB Connection Closed: %s" % e)


# Read DS18B20 Sensors and Save Them to MySQL
temperature = []
IDs = []
skip_count = []
while True:
    for filename in os.listdir("/sys/bus/w1/devices"):
        if fnmatch.fnmatch(filename, "28-*"):
            with open("/sys/bus/w1/devices/" + filename + "/w1_slave") as fileobj:
                lines = fileobj.readlines()
                # print lines
                if len(lines) > 0:
                    if lines[0].find("YES"):
                        pok = lines[1].find("=")
                        current_temperature = (
                            float(lines[1][pok + 1 : pok + 6]) / 1000
                        )  # Current tempearture reading
                        current_ID = filename  # Current sensor ID
                        if (
                            filename in IDs
                        ):  # Check if data from this sensor had alread been received
                            i = IDs.index(filename)  # Find the index for the sensore
                            if (
                                skip_count[i] == skip_max
                            ):  # If the maximum number of readings as been reached force and update
                                old_temperature[i] = current_temperature
                            if (
                                abs(current_temperature - old_temperature[i]) < dT_max
                            ):  # If the new reading is within the max range update temperature with the EMA
                                skip_count[i] = 0
                                temperature[i] = (1 - alpha) * old_temperature[
                                    i
                                ] + alpha * current_temperature
                            else:  # If the new reading is not within the max range return the revious reading
                                skip_count[i] += 1
                                temperature[i] = old_temperature[i]
                        else:  # If this is a new sensor append it to the end and set the skip count to 0
                            temperature.append(current_temperature)
                            IDs.append(current_ID)
                            skip_count.append(0)
                    else:
                        logger.error("Error reading sensor with ID: %s" % (filename))
    old_temperature = temperature  # Update the previous tempearture record
    if len(temperature) > 0:
        insertDB(IDs, temperature)

    time.sleep(update_rate)
