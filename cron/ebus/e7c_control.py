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
print("*          EBus Set FlowTempDesired  Script            *")
print("*                                                      *")
print("*               Build Date: 24/04/2023                 *")
print("*       Version 0.05 - Last Modified 12/06/2026        *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

line_len = 55; #length of seperator lines

import os, sys, time
import subprocess
from datetime import datetime, timedelta
import MySQLdb as mdb
import configparser, logging, traceback
import struct

# Initialise the database access variables
config = configparser.ConfigParser()
config.read("/var/www/st_inc/db_config.ini")
dbhost = config.get("db", "hostname")
dbuser = config.get("db", "dbusername")
dbpass = config.get("db", "dbpassword")
dbname = config.get("db", "dbname")

# node and child id for the Weather Channel node
node_id = 1
child_id = 0

## Write raw messages to the EBus
print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - FlowTempDesired Check Started")
print("-" * line_len)

water_override = False
water_override_prev = False
outside_temp_prev = -99
initial_setup = True
blocked = False
blocked_prev = False
boost = False
boost_prev = False
schedule = False
schedule_prev = True
default_temp_max = 75
default_temp_old = 75
command_prev = ""
regulation = ""
blocked_temp = 27 # boiler blocks when flow and return difference is grater than 30C, a 27C difference check works okaym 28C does NOT prevent boiler blocking, ie STATE 53
flowtempdesired = 0
flame = 0
flow_temp = 0
return_temp = 0
state_number = 0
boiler_status_1 = 0
boiler_status_1_prev = 0
boiler_status_2 = 0
boiler_status_2_prev = 0
run_22 = 0
last_fault_code = 0
LastResetDateTime = ""
errorhistory_error = False
boiler_errors = ""
boiler_state = 0
boiler_state_prev = 0

# Logging exceptions to log file
logfile = "/var/www/logs/main.log"
infomsg = "More info in log file: " + logfile
logging.basicConfig(
    filename=logfile,
    level=logging.DEBUG,
    format=("\n### %(asctime)s - %(levelname)s - %(message)s  ###"),
)

# Create a dictionary list containing the boiler fault codes and descriptions
fault_dict = {
   1: "Failed to light after 5 attempts",
   3: "Fan Fault",
   4: "Flame out during demand",
   5: "Overheat",
   6: "CH Flow Thermistor connection Fault",
   10:  "CH Return Thermistor connection Fault",
   11: "Flow and/or return NTC fault",
   13: "PCB Memory or sensing Fault",
   14: "Gas valve control defective",
   15: "ebus voltage failure",
   22: "Low water pressure or Ignition temperature rise too slow",
   23: "Temperature Difference Flow/Return",
   25: "Temperature rise too high",
   28: "Failed to light after 5 attempts",
   32: "Fan or Flue Fault",
   43: "Generic Error",
   70: "Software incompatible",
   77: "Condensation pump error",
   49: "ebus voltage failure",
   72: "Flow and/or return NTC fault",
   0: "Undefined Fault Code"
}

# Create a dictionary list containing the boiler state codes and descriptions
state_dict = {
   0: "no heating required",
   1: "fan pre-run",
   2: "pump pre-run",
   3: "ignition",
   4: "burner on",
   5: "pump/fan overrun",
   6: "fan overrun",
   7: "pump overrun",
   8: "anti cycling period",
   30: "no call for heat",
   31: "No heat demand summer operating mode",
   53: "waiting due to boiler bloackage",
   98: "over heat",
   999: "Undefined State Code"
}

# Update MaxAir Database
def update_maxair (conn, node_id, sensor_id, val_1, val_2, msg_in, msg_in_val) :
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
        # update message_in table if flag is set
        if msg_in :
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
            cnx.execute("SELECT datetime FROM sensor_graphs WHERE node_id = (%s) AND child_id = (%s) LIMIT 1;",
            (node_id, sensor_child_id))
            if cnx.rowcount > 0 :
                result = cnx.fetchone()
                sensor_to_index = dict(
                    (d[0], i) for i, d in enumerate(cnx.description)
                )
                last_message_datetime = result[sensor_to_index["datetime"]]
                tdelta = datetime.strptime(timestamp, "%Y-%m-%d %H:%M:%S").timestamp() -  datetime.strptime(str(last_message_datetime), "%Y-%m-%d %H:%M:%S").timestamp()
            if cnx.rowcount == 0 or tdelta > timeout or val_1 != current_val_1 :
                try :
                    cnx.execute("""INSERT INTO sensor_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`, `datetime`)
                                   VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)""",
                               (0,0,sensor_id,sensor_name,"Sensor", 0, node_id, sensor_child_id, 0, val_1, datetime.now().strftime('%Y-%m-%d %H:%M:%S')))
                    conn.commit()
                except mdb.Error as e:
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
#    except :
#        print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Database Update FAIL")
#        return False

# check mode E7C00 or VRT350
status = os.system('systemctl is-active --quiet ebusd')
if status != 0:
    print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - ebusd Service is not running")
    exit()
result = subprocess.run(['ebusctl', 'i'], stdout=subprocess.PIPE)
response = result.stdout.decode("utf-8")
# set device id based on scan 15 result
if response.find('ID=35000') != -1:
    dev_id = "350"
    cmd1 = "OffsetDesTemp"
    cmd2 = ""
    cmd3 = "EDControlEnabled"
    cmd4 = "HwcTempDesired"
elif response.find('ID=E7C00') != -1:
    dev_id = "e7c"
    cmd1 = "HeatingTemp1"
    cmd2 = ""
    cmd3 = "AutoHeatCurveRegulation"
    cmd4 = "HeatingTemp2"
else:
    print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Unable to Identify Device")
    exit()
print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Device ID is             - " + dev_id)

# Connect to the MaxAir database the database
con = mdb.connect(dbhost, dbuser, dbpass, dbname)
cur = con.cursor()

# Find the node and child ids for the dummy sensors used to pass data back to the PiHome database
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
    result =cur.fetchone()
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
    result =cur.fetchone()
    if cur.rowcount > 0 :
        node_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        status_node_id = int(result[node_to_index["node_id"]])
        status_sensor = True

# check if a 'Boiler Flow' sensor exists in the database
flow_sensor = False
cur.execute("SELECT * FROM sensors WHERE name = 'Boiler Flow' LIMIT 1;")
result = cur.fetchone()
if cur.rowcount > 0 :
    sensor_to_index = dict(
        (d[0], i) for i, d in enumerate(cur.description)
    )
    flow_id = int(result[sensor_to_index["id"]])
    flow_sensor_id = int(result[sensor_to_index["sensor_id"]])
    if int(result[sensor_to_index["message_in"]]) == 1 :
        flow_msg_in = True
    else :
        flow_msg_in = False
    cur.execute('SELECT node_id FROM nodes WHERE id = (%s)', (flow_sensor_id, ))
    result =cur.fetchone()
    if cur.rowcount > 0 :
        node_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        flow_node_id = int(result[node_to_index["node_id"]])
        flow_sensor = True

# check if a 'Boiler Return' sensor exists in the database
return_sensor = False
cur.execute("SELECT * FROM sensors WHERE name = 'Boiler Return' LIMIT 1;")
result = cur.fetchone()
if cur.rowcount > 0 :
    sensor_to_index = dict(
        (d[0], i) for i, d in enumerate(cur.description)
    )
    return_id = int(result[sensor_to_index["id"]])
    return_sensor_id = int(result[sensor_to_index["sensor_id"]])
    if int(result[sensor_to_index["message_in"]]) == 1 :
        return_msg_in = True
    else :
        return_msg_in = False
    cur.execute('SELECT node_id FROM nodes WHERE id = (%s)', (return_sensor_id, ))
    result =cur.fetchone()
    if cur.rowcount > 0 :
        node_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        return_node_id = int(result[node_to_index["node_id"]])
        return_sensor = True

# check if a 'Boiler Target' sensor exists in the database
target_sensor = False
cur.execute("SELECT * FROM sensors WHERE name = 'Boiler Target' LIMIT 1;")
result = cur.fetchone()
if cur.rowcount > 0 :
    sensor_to_index = dict(
        (d[0], i) for i, d in enumerate(cur.description)
    )
    target_id = int(result[sensor_to_index["id"]])
    target_sensor_id = int(result[sensor_to_index["sensor_id"]])
    if int(result[sensor_to_index["message_in"]]) == 1 :
        target_msg_in = True
    else :
        target_msg_in = False
    cur.execute('SELECT node_id FROM nodes WHERE id = (%s)', (target_sensor_id, ))
    result =cur.fetchone()
    if cur.rowcount > 0 :
        node_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        target_node_id = int(result[node_to_index["node_id"]])
        target_sensor = True

# check if a 'Regulation' sensor exists in the database
regulation_sensor = False
cur.execute("SELECT * FROM sensors WHERE name = 'Regulation' LIMIT 1;")
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
    result =cur.fetchone()
    if cur.rowcount > 0 :
        node_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        regulation_node_id = int(result[node_to_index["node_id"]])
        regulation_sensor = True
cur.close()
con.close()

# Get the stored State Bytes from file and build list
# Read the entire file as a single byte string
state_bytes = []
with open('/var/log/ebus/StateBytes.bin', 'rb') as f:
    statedata = f.read()
    statedata = statedata.decode(encoding='UTF-8')
f.close()
# Build list
for x in range(0,len(statedata)):
    state_bytes.append(ord(statedata[x]))

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

    # exit if ebusd service is not running
    status = os.system('systemctl is-active --quiet ebusd')
    if status != 0:
        print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - ebusd Service is not running")
        exit()
    con = mdb.connect(dbhost, dbuser, dbpass, dbname)
    cur = con.cursor()

    # get flowtempdesired, flow, return and AutoHeatCurveRegulation (on startup), check for valid non ERR response before processing the returned value
    # get error history
    response = ""
    for x in range(10):
        result = subprocess.run(['ebusctl', 'r', '-f', '-i', str(x), 'errorhistory'], stdout=subprocess.PIPE)
        if result.stdout.decode("utf-8").find('ERR:') == 0:
            response = response + "0"
            errorhistory_error = True
        else:
            response = response + result.stdout.decode("utf-8").split(";")[3].replace("\n\n", "")
            errorhistory_error = False
        if x != 9:
             response = response + ";"
    boiler_errors = response
    last_fault_code = int(boiler_errors.split(";")[0])

    # get current_val_1 for the 'state' sensor
    cur.execute('SELECT `current_val_1` FROM `sensors` WHERE id = (%s)', (state_id, ))
    result = cur.fetchone()
    if cur.rowcount > 0 :
        sensor_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
    boiler_state_prev = int(result[sensor_to_index["current_val_1"]])

    # check the boiler error status
    result = subprocess.run(['ebusctl', 'r', '-f', 'ExternalFaultmessage'], stdout=subprocess.PIPE)
    response = result.stdout.decode("utf-8")
    if response.find('ERR:') == -1:
        boiler_state_error = False
#        LastResetDateTime = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        if response.rstrip().find('off') == 0 :
            boiler_state = 0
            log_txt = log_txt + 'External Fault Message is off\n'
        else :
            boiler_state = 1
            log_txt = log_txt + 'External Fault Message is on\n'
        # if state has changed then update the database
        if boiler_state != boiler_state_prev:
            if state_sensor :
                update_maxair(con, state_node_id, state_id, boiler_state, 0, state_msg_in, boiler_state)
        if boiler_state == 0:
            # Boiler NOT in fault condition
            result = subprocess.run(['ebusctl', 'r', '-f', 'Flame'], stdout=subprocess.PIPE)
            response = result.stdout.decode("utf-8")
            if response.find('ERR:') == -1:
                # check burner is ON or OFF
                if response.rstrip().find('on') == 0 :
                    boiler_status_1 = 91
                    Burner_State = "Lit"
                else :
                    boiler_status_1 = 90
                    Burner_State = "UnLit"
            else :
                Burner_State = "Fault"
        else :
            # Boiler is in fault condition, get the fault history last value
            boiler_status_1 = int(boiler_errors.split(";")[0])
            Burner_State = "Fault"
        log_txt = log_txt + 'Burner is ' + Burner_State + '\n'
    else :
        boiler_state_error = True
        if boiler_state_prev == 0 :
            log_txt = log_txt + 'External Fault Message is *off\n'
        else :
            log_txt = log_txt + 'External Fault Message is *on\n'

    # get the Boiler State number
    result = subprocess.run(['ebusctl', 'r', '-f', 'StateNumber'], stdout=subprocess.PIPE)
    response = result.stdout.decode("utf-8")
    if response.find('ERR:') == -1:
        boiler_status_2 = int(response.rstrip().split(";")[0])
        boiler_status_error = False
    else :
        boiler_status_error = True
    if boiler_status_1 != boiler_status_1_prev or boiler_status_2 != boiler_status_2_prev :
        if boiler_status_1_prev != boiler_status_1 :
            boiler_status_1_prev = boiler_status_1
            boiler_status_val = boiler_status_1
        if boiler_status_2_prev != boiler_status_2:
            boiler_status_2_prev = boiler_status_2
            boiler_status_val = boiler_status_2
        if status_sensor :
            update_maxair(con, status_node_id, status_id, boiler_status_1, boiler_status_2, status_msg_in, boiler_status_val)

    # add state changes to the state_bytes array
    state_bytes_len = len(state_bytes)
    if state_bytes_len > 0 :
       last_state = state_bytes[len(state_bytes) - 1]
    else :
        last_state = 0
    if boiler_status_2 != last_state :
        if state_bytes_len < 20:
            state_bytes.append(boiler_status_2)
        else :
            for x in range(0,state_bytes_len - 1) :
                state_bytes[x] = state_bytes[x + 1]
            state_bytes[state_bytes_len - 1] = boiler_status_2

    # Write binary data to a file
    with open('/var/log/ebus/StateBytes.bin', 'wb') as f:
        for y in range(0,len(state_bytes)) :
            f.write(chr(state_bytes[y]).encode(encoding='UTF-8'))
    f.close()

    if boiler_status_error :
        log_txt = log_txt + 'Current STATE is *' + state_dict[boiler_status_2]  + '\n'
    else :
        log_txt = log_txt + 'Current STATE is ' + state_dict[boiler_status_2]  + '\n'

    message = 'STATE Bytes '
    for i in range(0, len(state_bytes)):
        message = message + '[' + str(state_bytes[i]) + '] '
    log_txt = log_txt + message + '\n'

    # get the current flowtempdesired
    result = subprocess.run(['ebusctl', 'r', '-f', 'flowtempdesired'], stdout=subprocess.PIPE)
    response = result.stdout.decode("utf-8")
    if response.find('ERR:') == -1:
        flowtempdesired = float(response.rstrip())
        flowtempdesired_error = False
    else :
        flowtempdesired_error = True
    if target_sensor :
        update_maxair(con, target_node_id, target_id, flowtempdesired, 0, target_msg_in, flowtempdesired)

    if flowtempdesired_error :
        log_txt = log_txt  + 'TARGET TEMP 0C*\n'
    else :
        log_txt = log_txt  + 'TARGET TEMP ' + str(flowtempdesired) + 'C\n'

    # get the current flow temperature
    result = subprocess.run(['ebusctl', 'r', '-f', 'flowtemp'], stdout=subprocess.PIPE)
    response = result.stdout.decode("utf-8")
    if response.find('ERR:') == -1:
        flow_temp = float(response.rstrip().split(";")[0])
        flow_temp_error = False
    else :
        flow_temp_error = True
    print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Flow Temp                - " + str(flow_temp))
    if flow_sensor :
        update_maxair(con, flow_node_id, flow_id, flow_temp, 0, flow_msg_in, flow_temp)

    if flow_temp_error :
        log_txt = log_txt  + 'FLOW   TEMP 0C*\n'
    else :
        log_txt = log_txt  + 'FLOW   TEMP ' + str(flow_temp) + 'C\n'

    # get the current return temperature
    result = subprocess.run(['ebusctl', 'r', '-f', 'returntemp'], stdout=subprocess.PIPE)
    response = result.stdout.decode("utf-8")
    if response.find('ERR:') == -1:
        return_temp = float(response.rstrip().split(";")[0])
        return_temp_error = False
    else :
        return_temp_error = True
    print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Return Temp              - " + str(return_temp))
    if return_sensor :
        update_maxair(con, return_node_id, return_id, return_temp, 0, return_msg_in, return_temp)

    if return_temp_error :
        log_txt = log_txt  + 'RETURN TEMP 0C*\n'
    else :
        log_txt = log_txt  + 'RETURN TEMP ' + str(return_temp) + 'C\n'

    # CPU Temp
    cputemp = int(open('/sys/class/thermal/thermal_zone0/temp').read()) / 1000.0
    cpu_temp = "{0:0.1f}".format(cputemp)
    log_txt = log_txt + 'CPU    TEMP ' + cpu_temp + 'C\n'

    if boiler_state_error :
        log_txt = log_txt + 'Error Reading External Fault Message Indicator\n' 
    else :
        if errorhistory_error :
            log_txt = log_txt + 'Last Fault code was ' + str(last_fault_code)
            if last_fault_code == 72 :
                log_txt = log_txt + '(11)'
            elif last_fault_code == 49 :
                log_txt = log_txt + '(15)'
            elif last_fault_code == 28 :
                log_txt = log_txt + '(1)'
            log_txt = log_txt + " - " + fault_dict[last_fault_code] + '*\n'
        else :
            log_txt = log_txt + 'Last Fault code was ' + str(last_fault_code)
            if last_fault_code == 72 :
                log_txt = log_txt + '(11)'
            elif last_fault_code == 49 :
                log_txt = log_txt + '(15)'
            elif last_fault_code == 28 :
                log_txt = log_txt + '(1)'
            log_txt = log_txt + " - " + fault_dict[last_fault_code] + '\n'
            log_txt = log_txt + "Fault Memory " + boiler_errors + '\n'

    # get last reset date and time
    cur.execute("SELECT * FROM `reset` WHERE id=(SELECT MAX(id) FROM `reset`);")
    result = cur.fetchone()
    if cur.rowcount > 0 :
        reset_to_index = dict(
            (d[0], i) for i, d in enumerate(cur.description)
        )
        LastResetDateTime = str(result[reset_to_index["start_datetime"]])
    log_txt = log_txt + 'Last Boiler Reset at - ' + LastResetDateTime + '\n'

    # get number of 'AUTO' resets in the last 24 hours
    cur.execute("SELECT COUNT(*) FROM `reset` WHERE `start_datetime` > now() - interval 24 hour AND `type` = 'AUTO';")
    result = cur.fetchone()
    log_txt = log_txt + 'Number of Reset in Last 24 Hours - ' + str(result[0]) + '\n'

    log_txt = log_txt + '\n'

    # get the current AutoHeatCurveRegulation
    if initial_setup:
        result = subprocess.run(['ebusctl', 'r', '-f', cmd3], stdout=subprocess.PIPE)
        response = result.stdout.decode("utf-8")
        if response.find('ERR:') == -1:
            regulation = response.rstrip().split(";")[0]
            initial_setup = False

    # check the differnece between the flow and return temperatues and if grearter than 'blocked_temp' value (eg 27) the assume the boiler is blocked
    # and set the target temperature to be 'blocked_temp' value (eg 27) above the current return temperature, then if different to current value set 'Heatingtemp2'
    if flow_temp - return_temp >= blocked_temp:
        blocked = True
        default_temp = return_temp + blocked_temp
        if default_temp > default_temp_max:
            default_temp = default_temp_max
    else:
        blocked = False
        default_temp = default_temp_max
    default_temp = round(default_temp,2)
    if default_temp != default_temp_old:
        counter = 0
        result = subprocess.run(['ebusctl', 'w', '-c',  dev_id, cmd4, str(default_temp) + cmd2], stdout=subprocess.PIPE)
        response = result.stdout.decode("utf-8")
        while response.find('ERR:') == 1 and counter < 10:
            time.sleep(0.1)
            result = subprocess.run(['ebusctl', 'w', '-c',  dev_id, cmd4, str(default_temp) + cmd2], stdout=subprocess.PIPE)
            response = result.stdout.decode("utf-8")
            counter = counter + 1
        if counter == 10:
            print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - FAILED to Set Default Temperture ")
        else:
            default_temp_old = default_temp

    # check if any schedules are running
    cur.execute("""SELECT `zone`.id, `zone`.name, `zone_current_state`.`schedule`
                    FROM `zone_current_state`, `zone`
                    WHERE (`zone_current_state`.`zone_id` = `zone`.`id`) AND `zone_current_state`.`schedule` = 1
                    AND (`zone`.`type_id` = 2 OR `zone`.`type_id` = 3);""")
    if cur.rowcount > 0 :
        schedule = True
    else:
        schedule = False
    #    regulation_status = 0

    # get current outside temperature
    cur.execute(
        "SELECT `payload` FROM `messages_in` WHERE `node_id` = %s AND `child_id` = %s ORDER BY `id` DESC LIMIT 1;",
        (node_id, child_id),
    )
    msg = cur.fetchone()
    msg_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
    # get the outside temperature using either weather or a sensor
    outside_temp = msg[msg_to_index["payload"]]
    if outside_temp != outside_temp_prev:
       outside_temp_prev = outside_temp
       # Change control temperature setting on change
       counter = 0
       result = subprocess.run(['ebusctl', 'w', '-c',  dev_id, cmd1, str(outside_temp) + cmd2], stdout=subprocess.PIPE)
       response = result.stdout.decode("utf-8")
       while response.find('ERR:') == 1 and counter < 10:
          time.sleep(0.1)
          result = subprocess.run(['ebusctl', 'w', '-c',  dev_id, cmd1, str(outside_temp) + cmd2], stdout=subprocess.PIPE)
          response = result.stdout.decode("utf-8")
          counter = counter + 1
       if counter == 10:
          print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - FAILED to Set Control Temperture ")

    # only change when the bloacked state has not changed
    if blocked == blocked_prev:
        # check if a Hot Water Scedule is active and if so then override the weather correctionfeature (in order to get reasonably hot water)
        cur.execute("""SELECT `zone_current_state`.`mode`
                        FROM `zone_current_state`, `zone`
                        WHERE (`zone_current_state`.`zone_id` = `zone`.`id`) AND (`zone_current_state`.`mode` = 61 OR `zone_current_state`.`mode` = 81) AND `zone`.`type_id` = 3;
                     """)
        if cur.rowcount > 0 and flowtempdesired <= default_temp:
            water_override = True
        else:
            water_override = False

        # check if BOOST is active and if so then override the weather correctionfeature 
        cur.execute("SELECT * FROM `boost` WHERE `status` = 1;")
        if cur.rowcount > 0  and flowtempdesired <= default_temp:
            boost = True
        else:
            boost = False
    else:
        # if regulation sensor exists in the database then on change update 'current_val_2
        if regulation_sensor :
            try :
                if blocked:
                    param1 = 1
                else:
                    param1 = 0
                query = "UPDATE `sensors` SET `current_val_2` = "  + str(param1) + " WHERE `id` = " + str(regulation_id) + ";"
                cursorupdate = con.cursor()
                cursorupdate.execute(query)
                cursorupdate.close()
                con.commit()
            except :
                pass
        blocked_prev = blocked

    if water_override or boost or blocked:
        command = 'off'
    else:
        command = 'on'

    print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Blocked                  - " + str(blocked))
    print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Water Override           - " + str(water_override))
    print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Boost                    - " + str(boost))
    print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Control Temperture       - " + str(outside_temp))
    print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Default Temp             - " + str(default_temp))
    print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - FlowTempDesired:         - " + str(flowtempdesired))
    print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - Regulation               - " + regulation.upper())


    # process any change of regulation state
    if command != command_prev:
        counter = 0
        result = subprocess.run(['ebusctl', 'w', '-c', dev_id, cmd3, command], stdout=subprocess.PIPE)
        response = result.stdout.decode("utf-8")
        while response.find('ERR:') == 1 and counter < 10:
            time.sleep(0.1)
            result = subprocess.run(['ebusctl', 'w', '-c', dev_id, cmd3, command], stdout=subprocess.PIPE)
            response = result.stdout.decode("utf-8")
            counter = counter + 1
        if counter == 10:
            print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - FAILED to Set " + cmd3)
        else:
            regulation = str(command.upper())

#    print(bc.dtm + datetime.now().strftime("%Y-%m-%d %H:%M:%S") + bc.ENDC + " - " + cmd3 + "  - " + str(command.upper()))
    # if regulation sensor exists in the database then on change of command or schedule, then update 'current_val_1
    if regulation_sensor:
        if (command != command_prev) or (schedule != schedule_prev) or (boost != boost_prev) :
            # check if a schedule is running
            if schedule or boost :
                if command == 'on':
                    regulation_state = 2
                else:
                    regulation_state = 1
            else :
                regulation_state = 0
            # update 'current_val_1
            try :
                query = "UPDATE `sensors` SET `current_val_1` = "  + str(regulation_state) + " WHERE `id` = " + str(regulation_id) + ";"
                cursorupdate = con.cursor()
                cursorupdate.execute(query)
                cursorupdate.close()
                con.commit()
            except :
                pass

            # update previous states to match current staes
            command_prev = command
            schedule_prev = schedule
            boost_prev = boost

    print("-" * line_len)

    cur.close()
    con.close()

    # Write log to a file
    if new_log == 1 :                                 # create a new log file at 22hours every day
        # os.remove('/var/log/ebus/log.txt')            # remove existing log file
        with open('/var/log/ebus/log.txt', 'w') as f:
            f.write(log_txt)
        new_log = 0
    else :
        with open('/var/log/ebus/log.txt', 'a') as f:
            f.write(log_txt)
    f.close()
    time.sleep(10)
