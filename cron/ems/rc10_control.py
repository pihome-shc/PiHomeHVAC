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
print("*             EMS Set RC10 Emulator Script             *")
print("*                                                      *")
print("*               Build Date: 05/12/2025                 *")
print("*       Version 0.11 - Last Modified 29/04/2026        *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

line_len = 55; #length of seperator lines

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

# Initialise the database access variables
config = configparser.ConfigParser()
config.read("/var/www/st_inc/db_config.ini")
dbhost = config.get("db", "hostname")
dbuser = config.get("db", "dbusername")
dbpass = config.get("db", "dbpassword")
dbname = config.get("db", "dbname")

con = mdb.connect(dbhost, dbuser, dbpass, dbname)
cur = con.cursor()

read_error_count = 0
last_ems_read_error = ''
write_error_count = 0
last_ems_write_error = ''
run_22 = 0
current_state_time = time.time()
previous_state_time = '(NO Data)'

# Logging exceptions to log file
logfile = "/var/www/logs/main.log"
infomsg = "More info in log file: " + logfile
logging.basicConfig(
    filename=logfile,
    level=logging.DEBUG,
    format=("\n### %(asctime)s - %(levelname)s - %(message)s  ###"),
)

# Create a dictionary list containing the boiler state codes and descriptions
state_dict = {
    0: "gas valve",
    1: "",
    2: "fan",
    3: "ignition",
    4: "overrun",
    5: "boiler circuit pump",
    6: "3-way valve on WW",
    7: "circulation",
    32: "idle"
}

# Create a dictionary list containing the boiler fault codes and descriptions
fault_dict = {
   "b1": "Code plug not detected",
   "C6": "Fan Speed Too Low",
   "E2": "CH flow NTC defective",
   "E9": "Safetytemp. Limiter in CH flow has tripped",
   "EA": "Flame Not detected",
   "F0": "Internal Error",
   "f7": "Flame detected even though boiler switched off",
   "FA": "Flame detected after gas shut off",
   "Fd": "Reset button pressed by mistake",
   "CLEAR": "Faults Clear"
}

# Update MaxAir Database Sensors
def update_maxair_sensors (conn, node_id, sensor_id, val_1, val_2, msg_in, msg_in_val) :
    cnx = conn.cursor()
    # get 'current_val_1
    cnx.execute("SELECT * FROM `sensors` WHERE `id` = (%s) LIMIT 1;",
    (sensor_id,))
    result = cnx.fetchone()
    sensor_to_index = dict(
        (d[0], i) for i, d in enumerate(cnx.description)
    )
    sensor_name = result[sensor_to_index["name"]]
    sensor_child_id = int(result[sensor_to_index["sensor_child_id"]])
    current_val_1 = float(result[sensor_to_index["current_val_1"]])
    current_val_2 = float(result[sensor_to_index["current_val_2"]])
    graph_num = int(result[sensor_to_index["graph_num"]])
    timeout = int(result[sensor_to_index["timeout"]])
    resolution = int(result[sensor_to_index["resolution"]])
    if val_1 != current_val_1 or val_2 != current_val_2 :
        # update 'current_val_1' and 'current_val_2'
        try :
            query = ("UPDATE `sensors` SET `current_val_1` = " + str(val_1) + ", `current_val_2` = " + str(val_2) + " WHERE `id` = " + str(sensor_id) + ";")
            cnx.execute(query)
            conn.commit()
        except mdb.Error as e:
            print("DB Error %d: %s" % (e.args[0], e.args[1]))
            print(traceback.format_exc())
            logging.error(e)
            logging.info(traceback.format_exc())
            conn.close()
            print(infomsg)
            sys.exit(1)
        # update node last seen time
        try :
            query = ("UPDATE `nodes` SET `sync` = 0, `last_seen` = '" + str(datetime.now()) + "' WHERE `node_id` = '" + str(node_id) + "';")
            cnx.execute(query)
            conn.commit()
        except mdb.Error as e:
            print("DB Error %d: %s" % (e.args[0], e.args[1]))
            print(traceback.format_exc())
            logging.error(e)
            logging.info(traceback.format_exc())
            conn.close()
            print(infomsg)
            sys.exit(1)
    # check if the sensor is generating graph data
    if graph_num > 0 :
        tdelta = 0
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        cnx.execute("SELECT datetime FROM sensor_graphs WHERE node_id = (%s) AND child_id = (%s) ORDER BY id DESC LIMIT 1;",
        (node_id, sensor_child_id))
        if cnx.rowcount > 0 :
            result = cnx.fetchone()
            sensor_to_index = dict(
                (d[0], i) for i, d in enumerate(cnx.description)
            )
            last_message_datetime = result[sensor_to_index["datetime"]]
            tdelta = datetime.strptime(timestamp, "%Y-%m-%d %H:%M:%S").timestamp() -  datetime.strptime(str(last_message_datetime), "%Y-%m-%d %H:%M:%S").timestamp()
        if cnx.rowcount == 0 or tdelta > timeout * 60 or val_1 != current_val_1 :
            try :
                cnx.execute("""INSERT INTO sensor_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`, `datetime`)
                               VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)""",
                           (0,0,sensor_id,sensor_name,"Sensor", 0, node_id, sensor_child_id, 0, val_1, datetime.now().strftime('%Y-%m-%d %H:%M:%S')))
                conn.commit()
            except mdb.Error as e:
                # skip deadlock error (caused by something adding new data to the table)
                if e.args[0] == 1020:
                    pass
                else:
                    print("DB Error %d: %s" % (e.args[0], e.args[1]))
                    print(traceback.format_exc())
                    logging.error(e)
                    logging.info(traceback.format_exc())
                    conn.close()
                    print(infomsg)
                    sys.exit(1)
        try :
            cnx.execute("DELETE FROM sensor_graphs WHERE node_id = (%s) AND child_id = (%s) AND datetime < CURRENT_TIMESTAMP - INTERVAL 24 HOUR",
            (node_id, sensor_child_id))
            conn.commit()
        except mdb.Error as e:
            # skip deadlock error (caused by something adding new data to the table)
            if e.args[0] == 1020:
                pass
            else:
                print("DB Error %d: %s" % (e.args[0], e.args[1]))
                print(traceback.format_exc())
                logging.error(e)
                logging.info(traceback.format_exc())
                conn.close()
                print(infomsg)
                sys.exit(1)

    # update message_in table if flag is set
    if msg_in :
        tdelta = 0
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        cnx.execute("SELECT datetime FROM messages_in WHERE node_id = (%s) AND child_id = (%s) ORDER BY id DESC LIMIT 1;",
        (node_id, sensor_child_id))
        if cnx.rowcount > 0 :
            result = cnx.fetchone()
            message_to_index = dict(
                (d[0], i) for i, d in enumerate(cnx.description)
            )
            last_message_datetime = result[message_to_index["datetime"]]
            tdelta = datetime.strptime(timestamp, "%Y-%m-%d %H:%M:%S").timestamp() -  datetime.strptime(str(last_message_datetime), "%Y-%m-%d %H:%M:%S").timestamp()
        if cnx.rowcount == 0 or tdelta > timeout * 60 or val_1 != current_val_1 or val_2 != current_val_2:
            try :
                cnx.execute("INSERT INTO messages_in(`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `payload`) VALUES(%s,%s,%s,%s,%s,%s)",
                                    (0, 0, str(node_id), sensor_child_id, 0, msg_in_val))
                conn.commit()
            except mdb.Error as e:
                print("DB Error %d: %s" % (e.args[0], e.args[1]))
                print(traceback.format_exc())
                logging.error(e)
                logging.info(traceback.format_exc())
                conn.close()
                print(infomsg)
                sys.exit(1)

        cnx.close()
#        print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Database Update")
        return True

# Update MaxAir Database Relays
def update_maxair_relays (conn, relay_id, val_2) :
    cnx = conn.cursor()
    # get 'current_val_2
    cnx.execute("SELECT * FROM `relays` WHERE `id` = (%s) LIMIT 1;",
    (relay_id,))
    result = cnx.fetchone()
    relay_to_index = dict(
        (d[0], i) for i, d in enumerate(cnx.description)
    )
    relay_name = result[relay_to_index["name"]]
    relay_child_id = int(result[relay_to_index["relay_child_id"]])
    current_val_2 = float(result[relay_to_index["current_val_2"]])
    if val_2 != current_val_2 :
        # update 'current_val_1' and 'current_val_2'
        try :
            query = ("UPDATE `relays` SET `current_val_2` = " + str(val_2) + " WHERE `id` = " + str(relay_id) + ";")
            cnx.execute(query)
            conn.commit()
        except mdb.Error as e:
            print("DB Error %d: %s" % (e.args[0], e.args[1]))
            print(traceback.format_exc())
            logging.error(e)
            logging.info(traceback.format_exc())
            conn.close()
            print(infomsg)
            sys.exit(1)

        cnx.close()
#        print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Database Update")
        return True

def is_set(x, n):
    return x & 2**n != 0

def ems_read(command):
    global read_error_count
    global last_ems_read_error

    result = subprocess.run(['emsctl', command, 'r'], stdout=subprocess.PIPE)
    response = result.stdout.decode("utf-8")
    split = response.split(' ')
    if "Error" not in split[0] and ":" not in split[1]:
        return(split[1])
    else:
        last_ems_read_error = command + ' r'
        read_error_count = read_error_count + 1
        if read_error_count == 100:
            read_error_count = 0
        return(split[0])


def ems_write(command, param):
    global write_error_count
    global last_ems_write_error

    result = subprocess.run(['emsctl', command, 'w', param], stdout=subprocess.PIPE)
    response = result.stdout.decode("utf-8")
    if "Ok" in response:
        return True
    else:
        last_ems_write_error = command + ' w ' + param
        if write_error_count == 100:
            write_error_count = 0
        return False

# Find the node and child ids for the dummy sensors used to pass data back to the MaxAir database
# ***********************************************************************************************
# check if a 'Boiler State' sensor exists in the database
state_sensor = False
cur.execute("SELECT * FROM sensors WHERE name = 'Boiler State' LIMIT 1;")
result = cur.fetchone()
if cur.rowcount > 0 :
    sensor_to_index = dict(
        (d[0], i) for i, d in enumerate(cur.description)
    )
    state_id = int(result[sensor_to_index["id"]])
    state_sensor_id = int(result[sensor_to_index["sensor_id"]])
    if int(result[sensor_to_index["message_in"]]) == 1 :
        state_msg_in = True
    else :
        state_msg_in = False
    cur.execute('SELECT node_id FROM nodes WHERE id = (%s)', (state_sensor_id, ))
    result = cur.fetchone()
    if cur.rowcount > 0 :
        node_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        state_node_id = int(result[node_to_index["node_id"]])
        state_sensor = True

# check if a 'Boiler Status' sensor exists in the database
status_sensor = False
cur.execute("SELECT * FROM sensors WHERE name = 'Boiler Status' LIMIT 1;")
result = cur.fetchone()
if cur.rowcount > 0 :
    sensor_to_index = dict(
        (d[0], i) for i, d in enumerate(cur.description)
    )
    status_id = int(result[sensor_to_index["id"]])
    status_sensor_id = int(result[sensor_to_index["sensor_id"]])
    if int(result[sensor_to_index["message_in"]]) == 1 :
        status_msg_in = True
    else :
        status_msg_in = False
    cur.execute('SELECT node_id FROM nodes WHERE id = (%s)', (status_sensor_id, ))
    result = cur.fetchone()
    if cur.rowcount > 0 :
        node_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        status_node_id = int(result[node_to_index["node_id"]])
        status_sensor = True

# check if a 'Boiler Flow' sensor exists in the database
boiler_flow_sensor = False
cur.execute("SELECT * FROM sensors WHERE name = 'Boiler Flow' LIMIT 1;")
result = cur.fetchone()
if cur.rowcount > 0 :
    sensor_to_index = dict(
        (d[0], i) for i, d in enumerate(cur.description)
    )
    boiler_flow_id = int(result[sensor_to_index["id"]])
    boiler_flow_sensor_id = int(result[sensor_to_index["sensor_id"]])
    if int(result[sensor_to_index["message_in"]]) == 1 :
        boiler_flow_msg_in = True
    else :
        boiler_flow_msg_in = False
    cur.execute('SELECT node_id FROM nodes WHERE id = (%s)', (boiler_flow_sensor_id, ))
    result = cur.fetchone()
    if cur.rowcount > 0 :
        node_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        boiler_flow_node_id = int(result[node_to_index["node_id"]])
        boiler_flow_sensor = True

# check if a 'Boiler Target' sensor exists in the database
boiler_target_sensor = False
cur.execute("SELECT * FROM sensors WHERE name = 'Boiler Target' LIMIT 1;")
result = cur.fetchone()
if cur.rowcount > 0 :
    sensor_to_index = dict(
        (d[0], i) for i, d in enumerate(cur.description)
    )
    boiler_target_id = int(result[sensor_to_index["id"]])
    boiler_target_sensor_id = int(result[sensor_to_index["sensor_id"]])
    if int(result[sensor_to_index["message_in"]]) == 1 :
        boiler_target_msg_in = True
    else :
        boiler_target_msg_in = False
    cur.execute('SELECT node_id FROM nodes WHERE id = (%s)', (boiler_target_sensor_id, ))
    result = cur.fetchone()
    if cur.rowcount > 0 :
        node_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        boiler_target_node_id = int(result[node_to_index["node_id"]])
        boiler_target_sensor = True

# check if a 'Water Flow' sensor exists in the database
water_flow_sensor = False
cur.execute("SELECT * FROM sensors WHERE name = 'Water Flow' LIMIT 1;")
result = cur.fetchone()
if cur.rowcount > 0 :
    sensor_to_index = dict(
        (d[0], i) for i, d in enumerate(cur.description)
    )
    water_flow_id = int(result[sensor_to_index["id"]])
    water_flow_sensor_id = int(result[sensor_to_index["sensor_id"]])
    if int(result[sensor_to_index["message_in"]]) == 1 :
        water_flow_msg_in = True
    else :
        water_flow_msg_in = False
    cur.execute('SELECT node_id FROM nodes WHERE id = (%s)', (water_flow_sensor_id, ))
    result = cur.fetchone()
    if cur.rowcount > 0 :
        node_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        water_flow_node_id = int(result[node_to_index["node_id"]])
        water_flow_sensor = True

# check if a 'Water Target' sensor exists in the database
water_target_sensor = False
cur.execute("SELECT * FROM sensors WHERE name = 'Water Target' LIMIT 1;")
result = cur.fetchone()
if cur.rowcount > 0 :
    sensor_to_index = dict(
        (d[0], i) for i, d in enumerate(cur.description)
    )
    water_target_id = int(result[sensor_to_index["id"]])
    water_target_sensor_id = int(result[sensor_to_index["sensor_id"]])
    if int(result[sensor_to_index["message_in"]]) == 1 :
        water_target_msg_in = True
    else :
        water_target_msg_in = False
    cur.execute('SELECT node_id FROM nodes WHERE id = (%s)', (water_target_sensor_id, ))
    result = cur.fetchone()
    if cur.rowcount > 0 :
        node_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        water_target_node_id = int(result[node_to_index["node_id"]])
        water_target_sensor = True

# check if a 'Burner Power' sensor exists in the database
burner_power_sensor = False
cur.execute("SELECT * FROM sensors WHERE name = 'Burner Power' LIMIT 1;")
result = cur.fetchone()
if cur.rowcount > 0 :
    sensor_to_index = dict(
        (d[0], i) for i, d in enumerate(cur.description)
    )
    burner_power_id = int(result[sensor_to_index["id"]])
    burner_power_sensor_id = int(result[sensor_to_index["sensor_id"]])
    if int(result[sensor_to_index["message_in"]]) == 1 :
        burner_power_msg_in = True
    else :
        burner_power_msg_in = False
    cur.execute('SELECT node_id FROM nodes WHERE id = (%s)', (burner_power_sensor_id, ))
    result = cur.fetchone()
    if cur.rowcount > 0 :
        node_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        burner_power_node_id = int(result[node_to_index["node_id"]])
        burner_power_sensor = True

# check if a 'Regulation' sensor exists in the database
regulation_sensor = False
cur.execute("SELECT * FROM sensors WHERE name = 'Auto Reg' LIMIT 1;")
result = cur.fetchone()
if cur.rowcount > 0 :
    sensor_to_index = dict(
        (d[0], i) for i, d in enumerate(cur.description)
    )
    regulation_id = int(result[sensor_to_index["id"]])
    regulation_sensor_id = int(result[sensor_to_index["sensor_id"]])
    if int(result[sensor_to_index["message_in"]]) == 1 :
        regulation_msg_in = True
    else :
        regulation_msg_in = False
    cur.execute('SELECT node_id FROM nodes WHERE id = (%s)', (regulation_sensor_id, ))
    result = cur.fetchone()
    if cur.rowcount > 0 :
        node_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        regulation_node_id = int(result[node_to_index["node_id"]])
        regulation_sensor = True


# Find the node and child ids for the dummy relays used to pass data back to the MaxAir database
# ***********************************************************************************************
# check if a 'Auto Regulation' relay exists in the database
regulation_relay = False
cur.execute("SELECT * FROM relays WHERE name = 'Auto Reg' LIMIT 1;")
result = cur.fetchone()
if cur.rowcount > 0 :
    relay_to_index = dict(
        (d[0], i) for i, d in enumerate(cur.description)
    )
    auto_reg_id = int(result[relay_to_index["id"]])
    regulation_relay_id = int(result[relay_to_index["relay_id"]])
    if int(result[relay_to_index["message_in"]]) == 1 :
        regulation_msg_in = True
    else :
        regulation_msg_in = False
    cur.execute('SELECT node_id FROM nodes WHERE id = (%s)', (regulation_relay_id, ))
    result = cur.fetchone()
    if cur.rowcount > 0 :
        node_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        regulation_node_id = int(result[node_to_index["node_id"]])
        regulation_relay = True

# check if a 'RC10' sensor exists in the database
rc10_sensor = False
cur.execute("SELECT * FROM sensors WHERE name = 'RC10 Temp' LIMIT 1;")
result = cur.fetchone()
if cur.rowcount > 0 :
    sensor_to_index = dict(
        (d[0], i) for i, d in enumerate(cur.description)
    )
    rc10_id = int(result[sensor_to_index["id"]])
    rc10_sensor_id = int(result[sensor_to_index["sensor_id"]])
    rc10_correction_factor = float(result[sensor_to_index["correction_factor"]])
    if int(result[sensor_to_index["message_in"]]) == 1 :
        rc10_msg_in = True
    else :
        rc10_msg_in = False
    cur.execute('SELECT node_id FROM nodes WHERE id = (%s)', (rc10_sensor_id, ))
    result = cur.fetchone()
    if cur.rowcount > 0 :
        node_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        rc10_node_id = int(result[node_to_index["node_id"]])
        rc10_sensor = True

# =================================== MAIN BOILER PROCESS ==================================
print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - EMS BUS Control Started")
print("-" * line_len)

# setup the serial port connection
ser = serial.Serial( '/dev/ttyAMA0', 115200, serial.EIGHTBITS, serial.PARITY_NONE, serial.STOPBITS_ONE)

# Get the stored State Bytes from file and build list
# Read the entire file as a single byte string
state_bytes = []
try:
    with open('/var/www/cron/ems/StateBytes.bin', 'xb') as f:
        statedata = 0x00
        f.write(chr(statedata).encode(encoding='UTF-8')) 
except:
    with open('/var/www/cron/ems/StateBytes.bin', 'rb') as f:
        statedata = f.read()
        statedata = statedata.decode(encoding='UTF-8')
f.close()
# Build list
for x in range(0,len(statedata)):
    state_bytes.append(ord(statedata[x]))

# Get the stored Error Bytes from file and build list
# Read the entire file as a single byte string
error_bytes = []
try:
    with open('/var/www/cron/ems/ErrorBytes.bin', 'xb') as f:
#        errordata = 0x00
        f.write(chr(0).encode(encoding='UTF-8'))
        errordata = f.read()
        errordata = errordata.decode(encoding='UTF-8')
except:
    with open('/var/www/cron/ems/ErrorBytes.bin', 'rb') as f:
        errordata = f.read()
        errordata = errordata.decode(encoding='UTF-8')
f.close()
# Build list
for x in range(0,len(errordata)):
    error_bytes.append(ord(errordata[x]))

outside_temp_prev = -99

HW_prev = 0
heat_curve = 5

while 1:
    # get todays date and time
    today = datetime.today()
    runHour = today.strftime('%H')
    today = today.strftime('%Y-%m-%d')
    if (runHour == '22') and run_22 == 0:             # clear at 2200 hours
        test_count = 0
        FaultFlag = 0
        EBUS_Counter = 0
        new_log = 1                                    # flag to delete log file each day
        run_22 = 1                                     # flag to only do once
        update_target_temp = 1                         # flag to add target temp to database once a day
    else :
        new_log = 0

    if (runHour == '23'):
        run_22 = 0                                     # clear for next day

    log_txt = time.strftime("%H:%M:%S") + ' - '

    # check the boiler error status
    response = ems_read('servicecode')
    if response.find('Error') == -1:
        service_code = response.replace("\n", "")
        if "CLEAR" in service_code:
            error_code = 0
        else:
            error_code = int(service_code, 16)
        # add error changes to the error_bytes array
        error_bytes_len = len(error_bytes)
        if error_bytes_len > 0 :
            last_error = error_bytes[len(error_bytes) - 1]
        else :
            last_error = 0
        if error_code != last_error :
            if error_bytes_len < 20:
                error_bytes.append(error_code)
            else :
                for x in range(0,error_bytes_len - 1) :
                    error_bytes[x] = error_bytes[x + 1]
                error_bytes[error_bytes_len - 1] = error_code

        # Write binary data to a file
        with open('/var/www/cron/ems/ErrorBytes.bin', 'wb') as f:
            for y in range(0,len(error_bytes)) :
                f.write(chr(error_bytes[y]).encode(encoding='UTF-8'))
        f.close()
        boiler_status_error = False
    else:
        boiler_status_error = True

    if boiler_status_error :
        log_txt = log_txt + 'Current SERVICE CODE is *NO RESULT\n'
    else :
        log_txt = log_txt + 'Current SERVICE CODE is ' + service_code + '\n'

    message = 'ERROR Bytes '
    for i in range(0, len(error_bytes)):
        message = message + '[' + str(hex(error_bytes[i])) + '] '
    log_txt = log_txt + message + '\n'

    # get the current boiler status
    if status_sensor:
        boiler_status_error = False
        response = ems_read('status01')
        if "Error" not in response:
            status01= int(response,16)
            Valve_gas = is_set(status01, 0)
            Blower = is_set(status01, 2)
            Ignition = is_set(status01, 3)
            Pump_heater = is_set(status01, 5)
            Valve_WW = is_set(status01, 6)
            Circulation = is_set(status01, 7)
            # add state changes to the state_bytes array
            state_bytes_len = len(state_bytes)
            if state_bytes_len > 0 :
                last_state = state_bytes[len(state_bytes) - 1]
            else :
                last_state = 0
            # mask bit 4 as it's artificial, used to indicate post lit phase
            if is_set(last_state, 4):
                last_state = last_state & 0b11101111
            # status has changed
            if status01 != last_state:
                # reset the time count
                previous_state_time = time.strftime("%H:%M:%S", time.gmtime(time.time() - current_state_time))
                current_state_time = time.time()
                #check if pump status is post a boiler lit state
                if status01 == 32 and (last_state == 36 or last_state == 37 or last_state == 48):
                    # bit 4 is unused, set to indicate that the current status follows a boiler lit state
                    status01 = status01 | 0b00010000
                if state_bytes_len < 20:
                    state_bytes.append(status01)
                else :
                    for x in range(0,state_bytes_len - 1) :
                        state_bytes[x] = state_bytes[x + 1]
                    state_bytes[state_bytes_len - 1] = status01

                # Write binary data to a file
                with open('/var/www/cron/ems/StateBytes.bin', 'wb') as f:
                    for y in range(0,len(state_bytes)) :
                        f.write(chr(state_bytes[y]).encode(encoding='UTF-8'))
                f.close()

                # create message text for current state
                if status01 == 0:
                    current_state_msg = "idle"
                elif is_set(status01, 0):
                    current_state_msg = "running"
                elif is_set(status01, 4):
                    current_state_msg = state_dict[4]
                else:
                    for i in range(1, 7):
                        # bits 1 and 4 are not defined in the EMS telegram, but bit 4 is used to indicate post lit phase
                        if i != 1 and i != 4:
                            if is_set(status01, i):
                                current_state_msg = state_dict[i] + " "

                if status01 == 0:
                    val_1 = 0
                elif Valve_gas:
                    val_1 = 2
                else:
                    val_1 = 1

                update_maxair_sensors(con, status_node_id, status_id, val_1, status01, status_msg_in, status01)

        else :
            boiler_status_error = True

        # use the last state bytpe
        state_bytes_len = len(state_bytes)
        if state_bytes_len > 0 :
            status01 = state_bytes[len(state_bytes) - 1]
        else :
            status01 = 0

        # create message text for current state
        if status01 == 0:
            current_state_msg = "idle"
        elif is_set(status01, 0):
            current_state_msg = "running"
        elif is_set(status01, 4):
            current_state_msg = state_dict[4]
        else:
            for i in range(1, 7):
                # bits 1 and 4 are not defined in the EMS telegram, but bit 4 is used to indicate post lit phase
                if i != 1 and i != 4:
                    if is_set(status01, i):
                        current_state_msg = state_dict[i] + " "

        if not boiler_status_error:
            log_txt = log_txt + 'Current STATE is ' + current_state_msg.rstrip()  + '\n'
        else:
            log_txt = log_txt + 'Current STATE is *' + current_state_msg.rstrip()  + '\n'
        log_txt = log_txt + 'Current STATE has been active for ' + time.strftime("%H:%M:%S", time.gmtime(time.time() - current_state_time)) + '\n'
        log_txt = log_txt + 'Previous STATE was active for ' + previous_state_time + '\n'

        # display state bytes list
        message = 'STATE Bytes '
        for i in range(0, len(state_bytes)):
            message = message + '[' + str(state_bytes[i]) + '] '
        log_txt = log_txt + message + '\n'
        print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Boiler Status            - " + str(status01))

    # set auto regulation if dummy relay exits
    if regulation_relay:
        cur.execute("SELECT state FROM relays WHERE id = (%s) LIMIT 1;",
        (auto_reg_id, ))
        if cur.rowcount > 0 :
            result = cur.fetchone()
            relay_to_index = dict(
                (d[0], i) for i, d in enumerate(cur.description)
            )
            relay_state = result[relay_to_index["state"]]
            if relay_state == 0:
                ems_write('autoheatcurveregulation', 'off')
            else:
                ems_write('autoheatcurveregulation', 'on')

    # get the current auto regulation
    if regulation_sensor or regulation_relay:
        error_code = 0
        autoheatcurveregulation_error = heatcurve_error = heatcurve_update_error = heatingtemp1_update_error = False
        response = ems_read('autoheatcurveregulation')
        if "Error" not in response:
            if 'ON' in response:
                auto_reg = 1
                regulation = 'On'
            else:
                auto_reg = 0
                regulation = 'Off'
            if auto_reg == 1:
                # get the current Heat Curve
                response = ems_read('heatcurve')
                if "Error" not in response:
                    heatcurve = float(response.rstrip())
                    # check if any water type zones are active
                    cur.execute("SELECT `zone_state` FROM `zone` WHERE `type_id` = 3 AND `zone_state` = 1;")
                    if cur.rowcount > 0:
                        if HW_prev == 0 or heatcurve == 5:
                            # Hot water zone is now active so set Heat Curve 6
                            HW_prev = 1
                            heat_curve = 6
                            result = subprocess.run(['emsctl', 'heatcurve', 'w', str(heat_curve)], stdout=subprocess.PIPE)
                            response = result.stdout.decode("utf-8")
                            if "Ok" not in response:
                                heatcurve_update_error = True
                                error_code = error_code | 0b0010
                    else:
                        if HW_prev == 1 or heatcurve == 6:
                            # Hot water zone is no longer active so set Heat Curve 5
                            HW_prev = 0
                            heat_curve = 5
                            result = subprocess.run(['emsctl', 'heatcurve', 'w', str(heat_curve)], stdout=subprocess.PIPE)
                            response = result.stdout.decode("utf-8")
                            if "Ok" not in response:
                                heatcurve_update_error = True
                                error_code = error_code | 0b0010
                    if not heatcurve_update_error:
                        # get current outside temperature
                        #use the same control temperature sensor as for smart_hw (usually the weather channel sensor node_id = 1, child_id = 0
                        cur.execute(
                            "SELECT `sensor_id` FROM `hw_compensation` LIMIT 1;"
                        )
                        row = cur.fetchone()
                        row_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                        control_sensor_id = row[row_to_index["sensor_id"]]
                        if control_sensor_id == 0:
                            # node and child id for the Weather Channel node
                            node_id = 1
                            child_id = 0
                        else:
                            cur.execute(
                                "SELECT nodes.node_id, sensors.sensor_child_id FROM `nodes`, `sensors` WHERE (`nodes`.`id` = `sensors`.`sensor_id`) AND `sensors`.`id` = %s;",
                                (control_sensor_id,),
                            )
                            if cur.rowcount > 0:
                                nodes = cur.fetchone()
                                nodes_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                node_id = nodes[nodes_to_index["node_id"]]
                                child_id = nodes[nodes_to_index["sensor_child_id"]]
                            else:
                                node_id = 1
                                child_id =0
                        cur.execute(
                            "SELECT `payload` FROM `messages_in` WHERE `node_id` = %s AND `child_id` = %s ORDER BY `id` DESC LIMIT 1;",
                            (node_id, child_id),
                        )
                        # check that the outside temperature has been retrieved from the database
                        if cur.rowcount > 0 :
                            msg = cur.fetchone()
                            msg_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                            # get the outside temperature using either weather or a sensor
                            outside_temp = msg[msg_to_index["payload"]]
                            if outside_temp != outside_temp_prev:
                                outside_temp_prev = outside_temp
                                if not ems_write('heatingtemp1', str(outside_temp)):
                                    heatingtemp1_update_error = True
                                    error_code = error_code | 0b0001
                        else:
                            heatingtemp1_update_error = True
                            error_code = error_code | 0b0001
                else :
                    heatcurve_error = True
                    error_code = error_code | 0b0100
        else :
            autoheatcurveregulation_error = True
            error_code = error_code | 0b1000

        if autoheatcurveregulation_error or heatcurve_error or heatcurve_update_error or heatingtemp1_update_error:
            heat_curve = 99
            if regulation_sensor:
                update_maxair_sensors(con, regulation_node_id, regulation_id, 0, heat_curve, 0, 0)
            print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Auto Regulation          - ERROR")
            print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Heat Curve               - ERROR")
            print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Control Temp             - ERROR")
            print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Error Code               - " + str(error_code))
        else:
            if regulation_sensor:
                update_maxair_sensors(con, regulation_node_id, regulation_id, auto_reg, heat_curve, regulation_msg_in, auto_reg)
            print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Auto Regulation          - " + regulation)
            print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Heat Curve               - " + str(heat_curve))
            print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Control Temp             - " + str(outside_temp))
        log_txt = log_txt  + 'HEAT CURVE ' + str(heat_curve) + '\n'
        log_txt = log_txt  + 'CONTROL TEMP ' + str(outside_temp) + 'C\n'

    # get the current flowtempdesired
    if boiler_target_sensor :
        response = ems_read('flowtempdesired')
        if "Error" not in response:
            flowtempdesired = float(response.rstrip())
            flowtempdesired_error = False
        else :
            flowtempdesired_error = True
        update_maxair_sensors(con, boiler_target_node_id, boiler_target_id, flowtempdesired, 0, boiler_target_msg_in, flowtempdesired)
        print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Flow Target Temp         - " + str(flowtempdesired))
        if flowtempdesired_error :
            log_txt = log_txt  + 'BOILER TARGET TEMP 0C*\n'
        else :
            log_txt = log_txt  + 'BOILER TARGET TEMP ' + str(flowtempdesired) + 'C\n'

    # get the current flow temperature
    if boiler_flow_sensor :
        response = ems_read('flowtemp')
        if "Error" not in response:
            flowtemp = float(response.rstrip())
            flow_temp_error = False
        else :
            flow_temp_error = True
        update_maxair_sensors(con, boiler_flow_node_id, boiler_flow_id, flowtemp, 0, boiler_flow_msg_in, flowtemp)
        print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Flow Temp                - " + str(flowtemp))
        if flow_temp_error :
            log_txt = log_txt  + 'BOILER FLOW TEMP 0C*\n'
        else :
            log_txt = log_txt  + 'BOILER FLOW TEMP ' + str(flowtemp) + 'C\n'

    # get the current WaterTempDesired
    if water_target_sensor :
        response = ems_read('watertempdesired')
        if "Error" not in response:
            watertempdesired = float(response.rstrip())
            watertempdesired_error = False
        else :
            watertempdesired_error = True
        update_maxair_sensors(con, water_target_node_id, water_target_id, watertempdesired, 0, water_target_msg_in, watertempdesired)
        print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Water Target Temp        - " + str(watertempdesired))
        if watertempdesired_error :
            log_txt = log_txt  + 'WATER TARGET TEMP 0C*\n'
        else :
            log_txt = log_txt  + 'WATER TARGET TEMP ' + str(watertempdesired) + 'C\n'

    # get the current water temperature
    if water_flow_sensor :
        response = ems_read('hwtemp')
        if "Error" not in response:
            watertemp = float(response.rstrip())
            water_temp_error = False
        else :
            water_temp_error = True
        update_maxair_sensors(con, water_flow_node_id, water_flow_id, watertemp, 0, water_flow_msg_in, watertemp)
        print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Water Temp               - " + str(watertemp))
        if water_temp_error :
            log_txt = log_txt  + 'WATER FLOW TEMP 0C*\n'
        else :
            log_txt = log_txt  + 'WATER FLOW TEMP ' + str(watertemp) + 'C\n'

    # get the current burner power
    if burner_power_sensor :
        response = ems_read('burnerpower')
        if "Error" not in response:
            burnerpower = float(response.rstrip())
            burner_power_error = False
        else :
            burner_power_error = True
        update_maxair_sensors(con, burner_power_node_id, burner_power_id, burnerpower, 0, burner_power_msg_in, burnerpower)
        print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Burner Power             - " + str(burnerpower))
        if burner_power_error :
            log_txt = log_txt  + 'BURNER POWER 0%*\n'
        else :
            log_txt = log_txt  + 'BURNER POWER ' + str(burnerpower) + '%\n'

    # set lower right message if dummy relay exits
    if regulation_relay:
        # update 'current_val_2'
        update_maxair_relays(con, auto_reg_id, heat_curve)

    # get the current RC10 temperature
    result = subprocess.run(['emsctl', 'roomtemp', 'r'], stdout=subprocess.PIPE)
    response = result.stdout.decode("utf-8")
    if "No RTC Module" not in response:
        if rc10_sensor:
            split = response.split(' ')
            if "Error" not in split[0]:
                cur.execute(
                    "SELECT `correction_factor` FROM `sensors` WHERE `id` = %s LIMIT 1;",
                    (rc10_id,),
                )
                result = cur.fetchone()
                sensor_to_index = dict(
                    (d[0], i) for i, d in enumerate(cur.description)
                )
                rc10_correction_factor = float(result[sensor_to_index["correction_factor"]])
                roomtemp = float(split[1].rstrip()) + rc10_correction_factor
                room_temp_error = False
            else :
                room_temp_error = True
            if rc10_sensor :
                update_maxair_sensors(con, rc10_node_id, rc10_id, roomtemp, 0, rc10_msg_in, roomtemp)
                print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - RC10 Temp                - " + str(roomtemp))
            if room_temp_error :
                log_txt = log_txt  + 'RC10 TEMP 0C*\n'
            else :
                log_txt = log_txt  + 'RC10 TEMP ' + str(roomtemp) + 'C\n'
        else:
            log_txt = log_txt  + 'NO REAL TIME CLOCK Temp Sensor Defined\n'
    else:
        log_txt = log_txt  + 'NO REAL TIME CLOCK MODULE\n'

    # CPU Temp
    cputemp = int(open('/sys/class/thermal/thermal_zone0/temp').read()) / 1000.0
    cpu_temp = "{0:0.1f}".format(cputemp)
    print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - CPU Temp                 - " + str(cpu_temp))
    log_txt = log_txt + 'CPU TEMP ' + cpu_temp + 'C\n'

    log_txt = log_txt + 'EMS READ ERROR Count ' + str(read_error_count) + '\n'
    log_txt = log_txt + 'LAST EMS READ ERROR ' + last_ems_read_error + '\n'
    log_txt = log_txt + 'WRITE ERROR Count ' + str(write_error_count) + '\n'
    log_txt = log_txt + 'LAST EMS WRITE ERROR ' + last_ems_write_error + '\n'


    log_txt = log_txt + '\n'
    print("-" * line_len)

    # Write log to a file
    if new_log == 1 :                                 # create a new log file at 22hours every day
        # os.remove('/var/log/ebus/log.txt')            # remove existing log file
        with open('/var/www/cron/ems/ems.log', 'w') as f:
            f.write(log_txt)
        new_log = 0
    else :
        with open('/var/www/cron/ems/ems.log', 'a') as f:
            f.write(log_txt)
    time.sleep(15)
