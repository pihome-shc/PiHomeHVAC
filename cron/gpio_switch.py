#!/usr/bin/python
import time, os, fnmatch, MySQLdb as mdb, logging
from decimal import Decimal
import configparser
from datetime import datetime
from Pin_Dict import pindict
import board, digitalio

class bc:
    hed = "\033[0;36;40m"
    dtm = "\033[0;36;40m"
    ENDC = "\033[0m"
    SUB = "\033[3;30;45m"
    WARN = "\033[0;31;40m"
    grn = "\033[0;32;40m"
    wht = "\033[0;37;40m"


update_rate = 10  # Update rate for GPIO switch sensors in seconds

print(bc.hed + " ")
print("        __  __                             _         ")
print("       |  \/  |                    /\     (_)        ")
print("       | \  / |   __ _  __  __    /  \     _   _ __  ")
print("       | |\/| |  / _` | \ \/ /   / /\ \   | | | '__| ")
print("       | |  | | | (_| |  >  <   / ____ \  | | | |    ")
print("       |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|    ")
print(" ")
print("            " + bc.SUB + "S M A R T   T H E R M O S T A T " + bc.ENDC)
print(bc.WARN + " ")
print("***********************************************************")
print("*      MaxAir GPIO Switch Sensors Data to MySQL DB        *")
print("* Use this script if you have switches connected directly *")
print("*                      to GPIO pins.                      *")
print("*                                  Build Date: 04/09/2021 *")
print("*                                    Have Fun - PiHome.eu *")
print("***********************************************************")
print(" " + bc.ENDC)
logging.basicConfig(
    filename="/var/www/cron/logs/SWITCH_error.log",
    level=logging.DEBUG,
    format="%(asctime)s %(levelname)s %(name)s %(message)s",
)
logger = logging.getLogger(__name__)

# Initialise the database access variables
config = configparser.ConfigParser()
config.read("/var/www/st_inc/db_config.ini")
dbhost = config.get("db", "hostname")
dbuser = config.get("db", "dbusername")
dbpass = config.get("db", "dbpassword")
dbname = config.get("db", "dbname")

null_value = None

print(bc.dtm + time.ctime() + bc.ENDC + " - Read State GPIO Switch Sensors Script Started")
print("-" * 72)

while True:
    try:
        con = mdb.connect(dbhost, dbuser, dbpass, dbname)
        cur = con.cursor()
        # check if any Switch GPIO nodes have been associated with a switch sensor
        cur.execute(
            "SELECT nodes.node_id, max_child_id FROM `nodes`, `sensors` WHERE nodes.id = sensors.sensor_id AND `max_child_id` = sensors.sensor_child_id and nodes.name = 'Switch GPIO';"
        )
        switches = cur.fetchall()
        # found at least 1 switch GPIO
        if cur.rowcount > 0:
            switch_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
            # loop through the switches
            for x in switches:
                in_id = x[switch_to_index["node_id"]]
                in_child_id = x[switch_to_index["max_child_id"]]
                # setup yhe pin
                pin = digitalio.DigitalInOut(getattr(board, pindict[str(in_child_id)]))
                pin.direction = digitalio.Direction.INPUT
                # does data exist for this pin in the messages_in table
                cur.execute(
                    "SELECT `payload` FROM `messages_in` where node_id = (%s) AND child_id = (%s) ORDER BY id DESC LIMIT 1",
                    (in_id, in_child_id),
                )
                msg_update = False
                msg = cur.fetchone()
                # message for this pin already exists, check if state has changed
                if cur.rowcount > 0:
                    msg_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                    in_payload = int(msg[msg_to_index["payload"]])
                    if int(pin.value) != in_payload:
                        msg_update = True
                        if pin.value:
                            pin_state = 'ON'
                        else:
                            pin_state = 'OFF'
                else: # is first time this switch has been read
                    msg_update = True
                    if pin.value:
                        pin_state = 'ON'
                    else:
                        pin_state = 'OFF'
                # enter new message_in record if first time or the state of the pin has changed
                if  msg_update:
                    cur.execute(
                        "UPDATE `nodes` SET `last_seen`= %s WHERE node_id = %s", [time.strftime("%Y-%m-%d %H:%M:%S"), in_id]
                    )
                    con.commit()
                    print(
                        bc.dtm + time.ctime() + bc.ENDC + " - Switch ID" + bc.grn,
                        in_child_id,
                        bc.ENDC + "State" + bc.grn,
                        pin_state,
                        bc.ENDC,
                    )
                    cur.execute(
                        "INSERT INTO messages_in(`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `payload`, `datetime`) VALUES(%s,%s,%s,%s,%s,%s,%s)",
                        (
                            0,
                            0,
                            in_id,
                            in_child_id,
                            0,
                            int(pin.value),
                            time.strftime("%Y-%m-%d %H:%M:%S"),
                        ),
                    )
                    con.commit()

                    cur.execute(
                        """SELECT sensors.id
                           FROM sensors, `nodes`
                           WHERE (sensors.sensor_id = nodes.`id`) AND  nodes.node_id = (%s) AND sensors.sensor_child_id = (%s)  LIMIT 1;""",
                        (in_id, in_child_id),
                    )
                    results = cur.fetchone()
                    if cur.rowcount > 0:
                        sensor_to_index = dict(
                            (d[0], i) for i, d in enumerate(cur.description)
                        )
                        sensor_id = int(results[sensor_to_index["id"]])
                        # Update last reading for this sensor
                        cur.execute(
                            "UPDATE `sensors` SET `current_val_1` = %s, `last_seen` = %s WHERE id = %s",
                            [int(pin.value), datetime.now().strftime("%Y-%m-%d %H:%M:%S"), sensor_id],
                        )
                        con.commit()
                # release the GPIO pin
                pin.deinit()

        con.close()
    except mdb.Error as e:
        logger.error(e)
        print(bc.dtm + time.ctime() + bc.ENDC + " - DB Connection Closed: %s" % e)

    time.sleep(update_rate)

