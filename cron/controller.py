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
print("*              System Controller Script                *")
print("*                                                      *")
print("*               Build Date: 10/02/2023                 *")
print("*       Version 0.01 - Last Modified 12/01/2024        *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" " + bc.ENDC)

line_len = 100; #length of seperator lines

import MySQLdb as mdb, sys, serial, os, fnmatch, subprocess
import configparser, logging
import datetime
import time
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
from math import floor

# Debug print to screen configuration
if len(sys.argv) == 1:
    dbgLevel = 0  # 0-off, 1-info, 2-detailed, 3-all
else:
    dbgLevel = int(sys.argv[1])

#check if within a datetime window
def isNowInTimePeriod(startTime, endTime, nowTime):
    if startTime < endTime:
        return nowTime >= startTime and nowTime <= endTime
    else:
        #Over midnight:
        return nowTime >= startTime or nowTime <= endTime

#calculate time based on elapse from start time
def script_run_time(script_start_timestamp, int_time_stamp):
    time_delta = int(datetime.datetime.now().timestamp() - script_start_timestamp)
    run_time = int_time_stamp + time_delta
    run_time = datetime.datetime.fromtimestamp(run_time)
    date_time = run_time.strftime("%Y-%m-%d %H:%M:%S")
    return date_time

#set on/off for pump type relays
def process_pump_relays(
    relay_id,
    command,
):
    #Get data from relays table
    cur.execute(
        "SELECT * FROM `relays` WHERE id = %s LIMIT 1",
        (relay_id,),
    )
    if cur.rowcount > 0:
        relay = cur.fetchone()
        relay_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
        relay_id = relay[relay_to_index["relay_id"]]
        relay_child_id = relay[relay_to_index["relay_child_id"]]
        relay_type = relay[relay_to_index["type"]]
        relay_on_trigger = relay[relay_to_index["on_trigger"]]

        #Get data from nodes table
        cur.execute(
            "SELECT * FROM `nodes` WHERE id = %s AND status IS NOT NULL LIMIT 1",
            (relay_id,),
        )
        if cur.rowcount > 0:
            nodes = cur.fetchone()
            nodes_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
            relay_node_id = nodes[nodes_to_index["node_id"]]
            relay_node_type = nodes[nodes_to_index["type"]]

        #************************************************************************************
        # Pump Wired to Raspberry Pi GPIO Section: Pump Connected Raspberry Pi GPIO.
        #*************************************************************************************
        if 'GPIO' in relay_node_type:
            if relay_on_trigger == 1:
                relay_on = '1' #GPIO value to write to turn on attached relay
                relay_off = '0' #GPIO value to write to turn off attached relay
            else:
                relay_on = '0' #GPIO value to write to turn on attached relay
                relay_off = '1' #GPIO value to write to turn off attached relay
            if command == 1:
                relay_status = relay_on
            else:
                relay_status = relay_off
            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Pump: GIOP Relay Status:  " + bc.red + relay_status + bc.ENDC + " ("  + relay_on + "=On, " + relay_off + "=off)")
            cur.execute(
                "UPDATE `messages_out` set sent = 0, payload = %s  WHERE node_id = %s AND child_id = %s;",
                [str(command), relay_node_id, relay_child_id],
            )
            con.commit()  # commit above

        #************************************************************************************
        # Pump Wired over I2C Interface Make sure you have i2c Interface enabled
        #*************************************************************************************
        if 'I2C' in relay_node_type:
            subprocess.call("/var/www/cron/i2c/i2c_relay.py " + relay_node_id + " " + relay_child_id + " " + str(command), shell=True)
            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Pump: Relay Board: " + relay_node_id + " Relay No: "  + relay_child_id + " Status: " + str(command))

        #************************************************************************************
        # Pump Wireless Section: MySensors Wireless or MQTT Relay module for your Pump control.
        #*************************************************************************************
        if 'MySensor'  in relay_node_type or 'MQTT' in relay_node_type:
            #update messages_out table with sent status to 0 and payload to as zone status.
            cur.execute(
                "UPDATE `messages_out` set sent = 0, payload = %s  WHERE node_id = %s AND child_id = %s;",
                [str(command), relay_node_id, relay_child_id],
            )
            con.commit()  # commit above

        #************************************************************************************
        # Sonoff Switch Section: Tasmota WiFi Relay module for your Zone control.
        #*************************************************************************************
        if 'Tasmota' in relay_node_type:
            cur.execute(
                "SELECT * FROM http_messages WHERE zone_id = %s AND message_type = %s LIMIT 1;",
                (relay_id, str(command)),
            )
            if cur.rowcount > 0:
                http = cur.fetchone()
                http_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                add_on_msg =  http[http_to_index["command"]] + " " + http[http_to_index["parameter"]]
                cur.execute(
                    "UPDATE `messages_out` set sent = 0, payload = %s  WHERE node_id = %s AND child_id = %s;",
                    [add_on_msg, relay_node_id, relay_child_id],
                )
                con.commit()  # commit above

#return schedule status
def get_schedule_status(
    zone_id,
    holidays_status,
    away_status,
):

    end_time = int_time_stamp

    #get raw data
    qry_str = """SELECT schedule_daily_time.id AS time_id, schedule_daily_time.start, schedule_daily_time.start_sr, schedule_daily_time.start_ss, schedule_daily_time.start_offset,
        schedule_daily_time.end, schedule_daily_time.end_sr, schedule_daily_time.end_ss, schedule_daily_time.end_offset,
        schedule_daily_time.WeekDays, schedule_daily_time.status AS time_status, schedule_daily_time.sch_name, schedule_daily_time.type AS sch_type
        FROM `schedule_daily_time`, `schedule_daily_time_zone`
        WHERE (schedule_daily_time.id = schedule_daily_time_zone.schedule_daily_time_id) AND schedule_daily_time_zone.status = 1
        AND schedule_daily_time.status = 1 AND zone_id = %s"""
    if away_status == 1:
        qry_str = qry_str + " AND schedule_daily_time.type = 1"
    else:
        qry_str = qry_str + " AND schedule_daily_time.type = 0"
    if holidays_status == 0:
        qry_str = qry_str + " AND holidays_id = 0;"
    else:
        qry_str = qry_str + " AND holidays_id > 0;"
    cur.execute(qry_str,
        (zone_id,),
    )
    if cur.rowcount > 0:
        sch_count = cur.rowcount
        sch_status = 0;
        sch = cur.fetchall()
        sch_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
        for s in sch:
            #check each schedule for this zone
            time_now = int_time_stamp
            midnight = time_stamp.replace(hour=0, minute=0, second=0, microsecond=0)
            seconds_since_midnight = (time_stamp - midnight).seconds
            time_id = s[sch_to_index["time_id"]]
            WeekDays = s[sch_to_index["WeekDays"]]
            start_time = s[sch_to_index["start"]]
            end_time = s[sch_to_index["end"]]
            #process case where end time is tomorrow, ie starts day 1 and stops day 2
            if end_time.total_seconds() < start_time.total_seconds():
                #time now is day 2
                if (start_time.total_seconds() - seconds_since_midnight >= 0) and (seconds_since_midnight < end_time.total_seconds()):
                    WeekDays = WeekDays  & (1 << prev_dow)
                    start_time = yesterday_date + ", " + str(start_time)
                    end_time = today_date + ", " + str(end_time)
                #time now is day 1
                else:
                    WeekDays = WeekDays  & (1 << dow)
                    start_time = today_date + ", " + str(start_time)
                    end_time = tomorrow_date + ", " + str(end_time)
            else: #start and stop time on the same day
                WeekDays = WeekDays  & (1 << dow)
                start_time = today_date + ", " + str(start_time)
                end_time = today_date + ", " + str(end_time)
            start_time = time.mktime(datetime.datetime.strptime(start_time, "%d/%m/%Y, %H:%M:%S").timetuple())
            start_sr = s[sch_to_index["start_sr"]]
            start_ss = s[sch_to_index["start_ss"]]
            start_offset = s[sch_to_index["start_offset"]]
            end_time = time.mktime(datetime.datetime.strptime(end_time, "%d/%m/%Y, %H:%M:%S").timetuple())
            end_sr = s[sch_to_index["end_sr"]]
            end_ss = s[sch_to_index["end_ss"]]
            end_offset = s[sch_to_index["end_offset"]]
            time_status = s[sch_to_index["time_status"]]
            sch_name = s[sch_to_index["sch_name"]]
            #use sunrise/sunset if any flags set
            if start_sr == 1 or start_ss == 1 or end_sr == 1 or end_ss == 1:
                #get the sunrise and sunset times
                cur.execute("SELECT * FROM weather WHERE last_update > DATE_SUB( NOW(), INTERVAL 24 HOUR);")
                if cur.rowcount > 0:
                    weather = cur.fetchone()
                    weather_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                    sunrise_time = int(weather[weather_to_index["sunrise"]])
                    sunrise_time = today_date + ", " + datetime.datetime.fromtimestamp(sunrise_time).strftime("%H:%M:%S")
                    sunrise_time = time.mktime(datetime.datetime.strptime(sunrise_time, "%d/%m/%Y, %H:%M:%S").timetuple())
                    sunset_time = int(weather[weather_to_index["sunset"]])
                    sunset_time = today_date + ", " + datetime.datetime.fromtimestamp(sunset_time).strftime("%H:%M:%S")
                    sunset_time = time.mktime(datetime.datetime.strptime(sunset_time, "%d/%m/%Y, %H:%M:%S").timetuple())
                    if start_sr == 1 or start_ss == 1:
                        if start_sr == 1:
                            start_time = sunrise_time
                        else:
                             start_time = sunset_time
                        start_time = start_time + (start_offset * 60)
                    if end_sr == 1 or end_ss == 1:
                        if end_sr == 1:
                            end_time = sunrise_time
                        else:
                            end_time = sunset_time
                        end_time = end_time + (end_offset * 60);
            cur.execute(
                "SELECT * FROM schedule_time_temp_offset WHERE schedule_daily_time_id = %s AND status = 1 LIMIT 1;",
                (time_id,),
            )
            if cur.rowcount > 0:
                offset = cur.fetchone()
                offset_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                low_temp = offset[offset_to_index["low_temperature"]]
                high_temp = offset[offset_to_index["high_temperature"]]
                sensors_id = offset[offset_to_index["sensors_id"]]
                start_time_offset = offset[offset_to_index["start_time_offset"]]
                if sensors_id == 0:
                    cur.execute("SELECT c FROM weather WHERE last_update > DATE_SUB( NOW(), INTERVAL 24 HOUR);")
                    if cur.rowcount > 0:
                        weather = cur.fetchone()
                        weather_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                        outside_temp = int(weather[weather_to_index["c"]])
                        if outside_temp >= low_temp and outside_temp <= high_temp:
                            temp_span = high_temp - low_temp
                            step_size = start_time_offset/temp_span
                            start_time_temp_offset = (high_temp - outside_temp) * step_size
                        elif outside_temp < low_temp:
                            start_time_temp_offset = start_time_offset
                        else:
                            start_time_temp_offset = 0;
                        start_time = start_time - (start_time_temp_offset * 60)
                else:
                    cur.execute(
                        "SELECT current_val_1 FROM sensors WHERE id = %s LIMIT 1;",
                        (sensors_id,),
                    )
                    if cur.rowcount > 0:
                        sensor = cur.fetchone()
                        sensor_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                        outside_temp = sensor[sensor_to_index["current_val_1"]]
                        if outside_temp >= low_temp and outside_temp <= high_temp:
                            temp_span = high_temp - low_temp
                            step_size = start_time_offset/temp_span
                            start_time_temp_offset = (high_temp - outside_temp) * step_size
                        elif outside_temp < low_temp:
                            start_time_temp_offset = start_time_offset
                        else:
                            start_time_temp_offset = 0;
                        start_time = start_time - (start_time_temp_offset * 60)

            run_time = end_time - start_time
            cur.execute(
                "UPDATE schedule_daily_time SET run_time = %s WHERE id = %s;",
                (run_time, time_id),
            )
            con.commit()  # commit above
            if time_now > start_time and time_now < end_time and WeekDays  > 0 and time_status == 1:
                sch_status = 1
                break #exit the loop if an active schedule found
            else:
                sch_status = 0;
        #end for s in sch: loop
    else:
        sch_name = ""
        sch_status = 0
        time_id = 0
        sch_count = 0

    rval_dict = {}
    rval_dict["sch_name"] = sch_name
    rval_dict["sch_status"] = sch_status
    rval_dict["time_id"] = time_id
    rval_dict["end_time"] = end_time
    rval_dict["sch_count"] = sch_count
    return rval_dict

#---------------------
#Start processing loop
#---------------------
timer_flag = 0
try:
    while 1:
        NULL = "NULL"

        #set to indicate controller condition
        start_cause ='';
        stop_cause = '';
        add_on_start_cause ='';
        add_on_stop_cause = '';

        #initialise for when used as test variables in none HVAC system
        hvac_state = 0; # 0 = COOL, 1 = HEAT
        cool_relay_type = '';
        fan_relay_type = '';

        #initialise z_state dictionay
        z_state_dict = dict()

        #initialise dictionay for start, stop cause and expected_end_date_time
        z_start_cause_dict = dict()
        z_stop_cause_dict = dict()
        z_expected_end_date_time_dict = dict()

        #initialise the commands dictionary
        command_index = 0
        zone_commands_dict = {}

        #initialise the system_controller dictionary.
        system_controller_dict = {}
        system_controller_index = 0

        #initialise the zone_log dictionary.
        zone_log_dict = {}

        # Initialise the database access variables
        config = configparser.ConfigParser()
        config.read("/var/www/st_inc/db_config.ini")
        dbhost = config.get("db", "hostname")
        dbuser = config.get("db", "dbusername")
        dbpass = config.get("db", "dbpassword")
        dbname = config.get("db", "dbname")

        con = mdb.connect(dbhost, dbuser, dbpass, dbname)
        cur = con.cursor()

        # initialise system variables
        cur.execute("SELECT * FROM system LIMIT 1")
        row = cur.fetchone()
        system_to_index = dict((d[0], i) for i, d in enumerate(cur.description))

        settings_dict = dict()
        settings_dict["name"] = row[system_to_index["name"]]
        settings_dict["version"] = row[system_to_index["version"]]
        settings_dict["build"] = row[system_to_index["build"]]
        settings_dict["country"] = row[system_to_index["country"]]
        settings_dict["language"] = row[system_to_index["language"]]
        settings_dict["city"] = row[system_to_index["city"]]
        settings_dict["zip"] = row[system_to_index["zip"]]
        settings_dict["openweather_api"] = row[system_to_index["openweather_api"]]
        settings_dict["backup_email"] = row[system_to_index["backup_email"]]
        settings_dict["ping_home"] = row[system_to_index["ping_home"]]
        settings_dict["timezone"] = row[system_to_index["timezone"]]
        settings_dict["shutdown"] = row[system_to_index["shutdown"]]
        settings_dict["reboot"] = row[system_to_index["reboot"]]
        settings_dict["c_f"] = row[system_to_index["c_f"]]  # 0 = centigrade, 1 = fahrenheit
        settings_dict["mode"] = row[system_to_index["mode"]]
        settings_dict["max_cpu_temp"] = row[system_to_index["max_cpu_temp"]]
        settings_dict["page_refresh"] = row[system_to_index["page_refresh"]]
        settings_dict["theme"] = row[system_to_index["theme"]]
        settings_dict["test_mode"] = row[system_to_index["test_mode"]]
        settings_dict["test_run_time"] = row[system_to_index["test_run_time"]]

        #following variables set for date time functions.
        #if in test mode 3 use false time taken from the system table
        script_start_timestamp = datetime.datetime.now().timestamp()
        if settings_dict["test_mode"] == 3:
            if timer_flag == 0:
                 timer_flag = 1
                 elapse__timestamp = datetime.datetime.now().timestamp()
                 base_time = settings_dict["test_run_time"]
            datetime_str = script_run_time(elapse__timestamp, int(base_time.timestamp()))
            cur.execute(
                "UPDATE system SET test_run_time = %s;",
                (datetime_str,),
            )
            con.commit()  # commit above
            time_stamp = datetime.datetime.strptime(datetime_str, '%Y-%m-%d %H:%M:%S')
        else:
            if timer_flag == 1:
                 timer_flag = 0
            time_stamp = datetime.datetime.now()

        script_start_timestamp = datetime.datetime.now().timestamp()
        int_time_stamp = int(time_stamp.timestamp())
        dow = int(time_stamp.date().strftime('%w'))
        prev_dow = int((time_stamp + datetime.timedelta(days =- 1)).strftime("%w"))
        today_date = time_stamp.strftime("%d/%m/%Y")
        tomorrow_date = (time_stamp + datetime.timedelta(days = 1)).strftime("%d/%m/%Y")
        yesterday_date = (time_stamp + datetime.timedelta(days =- 1)).strftime("%d/%m/%Y")
        sensor_seen_time = None
        temp_reading_time = None
        expected_end_date_time = None

        if settings_dict["test_mode"] == 0 or settings_dict["test_mode"] == 3:
            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Controller Scan Started")
            #only for de-bugging
            if dbgLevel == 1:
                cur.execute("""SELECT zone.id as tz_id, zone.name, zone.status as tz_status, zone_type.type, zone_type.category
                    FROM zone, zone_type
                    WHERE (zone.type_id = zone_type.id) AND status = 1 AND zone.`purge`= 0 ORDER BY index_id asc;
                """)
                if cur.rowcount > 0:
                    zones = cur.fetchall()
                    zones_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                    for z in zones:
                        print("ID       " + str(z[zones_to_index["tz_id"]]))
                        print("Name     " + z[zones_to_index["name"]])
                        print("Status   " + str(z[zones_to_index["tz_status"]]))
                        print("Type     " + z[zones_to_index["type"]])
                        print("Category " + str(z[zones_to_index["category"]]))
                        if z[zones_to_index["category"]] == 1 or z[zones_to_index["category"]]:
                            print("Found")

            #Mode 0 is EU Boiler Mode, Mode 1 is US HVAC Mode
            system_controller_mode = settings_dict.get('mode') & 0b1

            #query to check system controller status
            cur.execute("SELECT * FROM system_controller LIMIT 1")
            row = cur.fetchone()
            system_controller_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
            system_controller_id = row[system_controller_to_index["id"]]
            system_controller_status = row[system_controller_to_index["status"]]
            system_controller_active_status = row[system_controller_to_index["active_status"]]
            system_controller_hysteresis_time = row[system_controller_to_index["hysteresis_time"]]
            system_controller_max_operation_time = row[system_controller_to_index["max_operation_time"]] * 60
            system_controller_overrun_time = row[system_controller_to_index["overrun"]]
            sc_mode = row[system_controller_to_index["sc_mode"]]
            sc_mode_prev  = row[system_controller_to_index["sc_mode_prev"]]
            sc_weather_factoring  = row[system_controller_to_index["weather_factoring"]]
            sc_weather_sensor_id  = row[system_controller_to_index["weather_sensor_id"]]
            #calulate system controller on time in seconds
            if system_controller_active_status == 1:
                system_controller_on_time = int_time_stamp - int(row[system_controller_to_index["datetime"]].timestamp())
            else:
                system_controller_on_time = 0
            if dbgLevel == 1:
                print("System Controller on time - " + str(system_controller_on_time) + " seconds")

            #Get data from relays table
            cur.execute(
                "SELECT * FROM `relays` WHERE id = %s LIMIT 1",
                (row[system_controller_to_index["heat_relay_id"]],),
            )
            if cur.rowcount > 0:
                relay = cur.fetchone()
                relay_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                heat_relay_id = relay[relay_to_index["relay_id"]]
                heat_relay_child_id = relay[relay_to_index["relay_child_id"]]
                heat_relay_on_trigger = relay[relay_to_index["on_trigger"]]
                if heat_relay_on_trigger == 1:
                    heat_relay_on = '1'; #GPIO value to write to turn on attached relay
                    heat_relay_off = '0'; #GPIO value to write to turn off attached relay
                else:
                    heat_relay_on = '0'; #GPIO value to write to turn on attached relay
                    heat_relay_off = '1'; #GPIO value to write to turn off attached relay

                #Get data from nodes table
                cur.execute(
                    "SELECT * FROM `nodes` WHERE id = %s AND status IS NOT NULL LIMIT 1",
                    (heat_relay_id,),
                )
                if cur.rowcount > 0:
                    nodes = cur.fetchone()
                    nodes_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                    heat_relay_node_id = nodes[nodes_to_index["node_id"]]
                    heat_relay_seen = nodes[nodes_to_index["last_seen"]]
                    heat_relay_notice = nodes[nodes_to_index["notice_interval"]]
                    heat_relay_type = nodes[nodes_to_index["type"]]

            #system operating in HVAC Mode
            if system_controller_mode == 1:
                #Relay Control
                # 0 = off
                # 1 = timer
                # 2 = auto
                # 3 = fan
                # 4 = heat
                # 5 = cool

                #Get data from relays table
                cur.execute(
                    "SELECT * FROM `relays` WHERE id = %s LIMIT 1",
                    (row[system_controller_to_index["cool_relay_id"]],),
                )
                if cur.rowcount > 0:
                    relay = cur.fetchone()
                    relay_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                    cool_relay_id = relay[relay_to_index["relay_id"]]
                    cool_relay_child_id = relay[relay_to_index["relay_child_id"]]
                    cool_relay_on_trigger = relay[relay_to_index["on_trigger"]]
                    if cool_relay_on_trigger == 1:
                        cool_relay_on = '1'; #GPIO value to write to turn on attached relay
                        cool_relay_off = '0'; #GPIO value to write to turn off attached relay
                    else:
                        cool_relay_on = '0'; #GPIO value to write to turn on attached relay
                        cool_relay_off = '1'; #GPIO value to write to turn off attached relay

                    #Get data from nodes table
                    cur.execute(
                        "SELECT * FROM `nodes` WHERE id = %s AND status IS NOT NULL LIMIT 1",
                        (cool_relay_id,),
                    )
                    if cur.rowcount > 0:
                        nodes = cur.fetchone()
                        nodes_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                        cool_relay_node_id = nodes[nodes_to_index["node_id"]]
                        cool_relay_seen = nodes[nodes_to_index["last_seen"]]
                        cool_relay_notice = nodes[nodes_to_index["notice_interval"]]
                        cool_relay_type = nodes[nodes_to_index["type"]]

                cur.execute(
                    "SELECT * FROM `relays` WHERE id = %s LIMIT 1",
                    (row[system_controller_to_index["fan_relay_id"]],),
                )
                if cur.rowcount > 0:
                    relay = cur.fetchone()
                    relay_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                    fan_relay_id = relay[relay_to_index["relay_id"]]
                    fan_relay_child_id = relay[relay_to_index["relay_child_id"]]
                    fan_relay_on_trigger = relay[relay_to_index["on_trigger"]]
                    if fan_relay_on_trigger == 1:
                        fan_relay_on = '1'; #GPIO value to write to turn on attached relay
                        fan_relay_off = '0'; #GPIO value to write to turn off attached relay
                    else:
                        fan_relay_on = '0'; #GPIO value to write to turn on attached relay
                        fan_relay_off = '1'; #GPIO value to write to turn off attached relay

                    #Get data from nodes table
                    cur.execute(
                        "SELECT * FROM `nodes` WHERE id = %s AND status IS NOT NULL LIMIT 1",
                        (fan_relay_id,),
                    )
                    if cur.rowcount > 0:
                        nodes = cur.fetchone()
                        nodes_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                        fan_relay_node_id = nodes[nodes_to_index["node_id"]]
                        fan_relay_seen = nodes[nodes_to_index["last_seen"]]
                        fan_relay_notice = nodes[nodes_to_index["notice_interval"]]
                        fan_relay_type = nodes[nodes_to_index["type"]]

            if system_controller_mode == 0:
                if sc_mode == 0:
                        current_sc_mode = "OFF"
                elif sc_mode == 1:
                        current_sc_mode = "TIMER"
                elif sc_mode == 2:
                        current_sc_mode = "CE"
                elif sc_mode == 3:
                        current_sc_mode = "HW"
                elif sc_mode == 4:
                        current_sc_mode = "BOTH"
                if dbgLevel >= 2:
                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Operating in Boiler Mode")
            else:
                if sc_mode == 0:
                        current_sc_mode = "OFF"
                        timer_mode = ""
                elif sc_mode == 1:
                        current_sc_mode = "TIMER"
                        timer_mode = "(HEAT)"
                elif sc_mode == 2:
                        current_sc_mode = "TIMER"
                        timer_mode = "(COOL)"
                elif sc_mode == 3:
                        Current_sc_mode = "TIMER"
                        timer_mode = "(AUTO)"
                elif sc_mode == 4:
                        current_sc_mode = "AUTO"
                        timer_mode = ""
                elif sc_mode == 5:
                        current_sc_mode = "FAN ONLY"
                        timer_mode = ""
                elif sc_mode == 6:
                        current_sc_mode = "HEAT"
                        timer_mode = ""
                elif sc_mode == 7:
                        current_sc_mode = "COOL"
                        timer_mode = ""
                if dbgLevel >= 2:
                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Operating in HVAC Mode - " + str(current_sc_mode) + str(timer_mode))

            if dbgLevel >= 2:
                print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Day of the Week: " + bc.red + str(dow) + bc.ENDC)
            print("-" * line_len)

            #query to check away status
            cur.execute("SELECT * FROM away LIMIT 1")
            row = cur.fetchone()
            away_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
            away_status = row[away_to_index["status"]]

            #query to check holidays status
            cur.execute(
                "SELECT * FROM `holidays` WHERE %s between start_date_time AND end_date_time AND status = '1' LIMIT 1",
                (time_stamp.strftime("%Y-%m-%d %H:%M:%S"),),
            )
            if cur.rowcount > 0:
                row = cur.fetchone()
                holidays_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                holidays_status = row[holidays_to_index["status"]]
            else:
                holidays_status = 0

            #query to get last system controller statues change time
            cur.execute(
                "SELECT * FROM `controller_zone_logs` WHERE zone_id = %s ORDER BY id desc LIMIT 1",
                (system_controller_id,),
            )
            cz_logs_count = cur.rowcount
            if cz_logs_count > 0:
                logs = cur.fetchone()
                logs_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                sc_stop_datetime = logs[logs_to_index["stop_datetime"]]
            else:
                sc_stop_datetime = None

            #query to active network gateway address
            cur.execute("SELECT gateway_address FROM network_settings WHERE primary_interface = 1 LIMIT 1;")
            if cur.rowcount > 0:
                row = cur.fetchone()
                network_settings_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                n_gateway = row[network_settings_to_index["gateway_address"]]
                base_addr = n_gateway[0 : (n_gateway.rfind(".") + 1)]
            else:
                base_addr = '000.000.000.000'

            #query to check the live temperature status
            cur.execute("SELECT * FROM livetemp LIMIT 1;")
            if cur.rowcount > 0:
                row = cur.fetchone()
                livetemp_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                livetemp_zone_id = row[livetemp_to_index["zone_id"]]
                livetemp_active = row[livetemp_to_index["active"]]
                livetemp_c = row[livetemp_to_index["temperature"]]
            else:
                livetemp_zone_id = ""
                livetemp_active = 0
                livetemp_c = 0

            sch_active = 0
            cur.execute("""SELECT zone.id, zone.status, zone.zone_state, zone.name, zone_type.type, zone_type.category, zone.max_operation_time FROM zone, zone_type
                           WHERE zone.type_id = zone_type.id order by index_id asc;""")
            zones = cur.fetchall()
            zones_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
            controllers_dict = {}
            for z in zones:
                zone_status=z[zones_to_index["status"]]
                zone_state_current = z[zones_to_index["zone_state"]]
                zone_id = z[zones_to_index["id"]]
                zone_name=z[zones_to_index["name"]]
                zone_type=z[zones_to_index["type"]]
                zone_category = z[zones_to_index["category"]]
                zone_max_operation_time=z[zones_to_index["max_operation_time"]]
                cur.execute("""SELECT zone_relays.id AS zc_id, cid.node_id as relay_id, zr.relay_child_id, zr.on_trigger, zr.type AS relay_type_id, zone_relays.zone_relay_id,
                               zone_relays.state, zone_relays.current_state, ctype.`type`
                               FROM zone_relays
                               join relays zr on zone_relay_id = zr.id
                               join nodes ctype on zr.relay_id = ctype.id
                               join nodes cid on zr.relay_id = cid.id
                               WHERE zone_id = %s;""",
                               (zone_id,),
                )
                relays = cur.fetchall()
                relay_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                index = 0
                controllers_dict[zone_id] = {}
                for relay in relays:
    #                print("T1",zone_id,relay[relay_to_index["zc_id"]])
                    zc_id = relay[relay_to_index["zc_id"]]
                    controllers_dict[zone_id][zc_id] = {}
                    controllers_dict[zone_id][zc_id ]["controler_id"] = relay[relay_to_index["relay_id"]]
                    controllers_dict[zone_id][zc_id ]["controler_child_id"] = relay[relay_to_index["relay_child_id"]]
                    controllers_dict[zone_id][zc_id ]["relay_type_id"] = relay[relay_to_index["relay_type_id"]]
                    controllers_dict[zone_id][zc_id ]["controler_on_trigger"] = relay[relay_to_index["on_trigger"]]
                    controllers_dict[zone_id][zc_id ]["controller_relay_id"] = relay[relay_to_index["zone_relay_id"]]
                    controllers_dict[zone_id][zc_id ]["zone_controller_state"] = relay[relay_to_index["state"]]
                    controllers_dict[zone_id][zc_id ]["zone_controller_current_state"] = relay[relay_to_index["current_state"]]
                    controllers_dict[zone_id][zc_id ]["zone_controller_type"] = relay[relay_to_index["type"]]
                    controllers_dict[zone_id][zc_id ]["manual_button_override"] = 0
                #query to check if zone_current_state record exists tor the zone
                cur.execute(
                    "SELECT * FROM zone_current_state WHERE zone_id = %s LIMIT 1;",
                    (zone_id,),
                )
                if cur.rowcount == 0:
                    qry_str = """INSERT INTO `zone_current_state`(id, `sync`, `purge`, `zone_id`, `mode`, `status`, `status_prev`, `schedule`, `sch_time_id`, `temp_reading`, `temp_target`,
                                 `temp_cut_in`, `temp_cut_out`, `controler_fault`, `controler_seen_time`, `sensor_fault`, `sensor_seen_time`,
                                  `sensor_reading_time`, `overrun`, `add_on_toggle`)
                                  VALUES ({}, {}, {}, {}, {}, {}, {}, {}, {}, {}, {}, {}, {}, {}, {}, {}, {}, {}, {}, {});""".format(
                                  zone_id, 0, 0, zone_id, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 0,  NULL,  NULL, 0, 0
                                  )
                    cur.execute(qry_str)
                    con.commit()

                #query to get zone previous running status
                cur.execute(
                    "SELECT * FROM zone_current_state WHERE zone_id = %s LIMIT 1",
                    (zone_id,),
                )
                if cur.rowcount > 0:
                    zone_current_state = cur.fetchone()
                    zone_current_state_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                    zone_status_current = zone_current_state[zone_current_state_to_index["status"]]
                    zone_status_prev = zone_current_state[zone_current_state_to_index["status_prev"]]
                    zone_overrun_prev = zone_current_state[zone_current_state_to_index["overrun"]]
                    zone_mode_current = zone_current_state[zone_current_state_to_index["mode"]]
                    zone_schedule_current = zone_current_state[zone_current_state_to_index["schedule"]]
                    zone_add_on_toggle = zone_current_state[zone_current_state_to_index["add_on_toggle"]]

                    if (zone_id == livetemp_zone_id) and (livetemp_active == 1) and (zone_mode_current == 0):
                        cur.execute(
                            'UPDATE livetemp SET active = 0 WHERE zone_id = %s',
                            [zone_id, ],
                        )
                        con.commit()

                # process if a sensor is attached to this zone
                if zone_category == 0 or zone_category == 1 or zone_category == 3 or zone_category == 4 or zone_category == 5:
                    cur.execute(
                        """SELECT zone_sensors.*, sensors.sensor_id, sensors.sensor_child_id, sensors.name, sensors.sensor_type_id, sensors.frost_controller,
                        sensors.frost_temp, sensors.current_val_1
                        FROM  zone_sensors, sensors
                        WHERE (zone_sensors.zone_sensor_id = sensors.id) AND zone_sensors.zone_id = %s LIMIT 1;""",
                        (zone_id,),
                    )
                    sensor_rowcount = cur.rowcount
                    if sensor_rowcount > 0:
                        sensor = cur.fetchone()
                        sensor_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                        zone_min_c = sensor[sensor_to_index["min_c"]]
                        zone_max_c = sensor[sensor_to_index["max_c"]]
                        zone_hysteresis_time = sensor[sensor_to_index["hysteresis_time"]]
                        zone_sp_deadband = sensor[sensor_to_index["sp_deadband"]]
                        zone_sensor_id = sensor[sensor_to_index["sensor_id"]]
                        zone_sensor_child_id = sensor[sensor_to_index["sensor_child_id"]]
                        default_c = sensor[sensor_to_index["default_c"]]
                        sensor_type_id = sensor[sensor_to_index["sensor_type_id"]]
                        zone_sensor_name = sensor[sensor_to_index["name"]]
                        zone_frost_controller = sensor[sensor_to_index["frost_controller"]]
                        zone_frost_temp = sensor[sensor_to_index["frost_temp"]]
                        zone_maintain_default = sensor[sensor_to_index["default_m"]]
                        zone_c = sensor[sensor_to_index["current_val_1"]]
                        sensor_found = False
                        # check if an MQTT type sensor
                        cur.execute(
                            "SELECT * FROM `mqtt_devices` WHERE nodes_id = %s AND child_id = %s LIMIT 1",
                            (zone_sensor_id, zone_sensor_child_id),
                        )
                        if cur.rowcount > 0:
                            sensor_found = True
                            mqtt_device = cur.fetchone()
                            mqtt_device_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                            zone_node_id = mqtt_device[mqtt_device_to_index["nodes_id"]]
                            node_name = mqtt_device[mqtt_device_to_index["name"]]
                            temp_reading_time = mqtt_device[mqtt_device_to_index["last_seen"]]
                        else:
                            cur.execute(
                                "SELECT * FROM `nodes` WHERE id = %s AND status IS NOT NULL LIMIT 1",
                                (zone_sensor_id,),
                            )
                            if cur.rowcount > 0:
                                sensor_found = True
                                zone_node = cur.fetchone()
                                zone_node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                zone_node_id = zone_node[zone_node_to_index["node_id"]]
                                node_name = zone_node[zone_node_to_index["name"]]
                                temp_reading_time = zone_node[zone_node_to_index["last_seen"]]
                        if sensor_found:
                            #check frost protection linked to this zone controller
                            if zone_frost_controller != 0:
                                frost_active = 0
                                frost_target_c = 99
                                frost_sensor_c = zone_c
                                if frost_sensor_c < (zone_frost_temp - zone_sp_deadband) and zone_frost_temp != 0:
                                    frost_active = 1
                                    #use the lowest value if multiple values
                                    if zone_frost_temp < frost_target_c:
                                        frost_target_c = zone_frost_temp
                                elif frost_sensor_c >= (frost_target_c - zone_sp_deadband) and frost_sensor_c < frost_target_c:
                                    frost_active = 2
                                    #use the lowest value if multiple values
                                    if zone_frost_temp < frost_target_c:
                                        frost_target_c = zone_frost_temp
                            else:
                                frost_active = 0
                            if dbgLevel == 1:
                                print("Sensor Name - " + zone_sensor_name + ", Frost Target Temperture - " + str(frost_target_c) + ", Frost Sensor Temperature - " + str(frost_sensor_c))
                        else:
                            zone_c = None;
                            temp_reading_time = None;
                            frost_active = 0
                else:
                    zone_frost_controller = 0

                #only process active zones with a sensor or a category 2 type zone
                if zone_status == 1 and (sensor_rowcount != 0 or zone_category == 2):
                    rval = get_schedule_status(
                        zone_id,
                        holidays_status,
                        away_status,
                    )
                    sch_status = rval['sch_status'];
                    sch_name = rval['sch_name'];
                    if sch_active == 0 and sch_status == 1:
                        sch_active = 1
                    if rval['sch_count'] == 0:
                        sch_status = 0
                        sch_c = 0
                        sch_holidays = 0
                        time_id = 0
                    else:
                        sch_end_time = rval['end_time']
                        sch_end_time_str = datetime.datetime.fromtimestamp(sch_end_time).strftime('%Y-%m-%d %H:%M:%S')
                        sch_status = rval['sch_status']
                        time_id = rval['time_id']
                        cur.execute(
                            "SELECT temperature, coop, holidays_id FROM schedule_daily_time_zone WHERE schedule_daily_time_id = %s AND zone_id = %s LIMIT 1;",
                            (time_id, zone_id),
                        )
                        if cur.rowcount > 0:
                            sdtz = cur.fetchone()
                            sdtz_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                            sch_c = sdtz[sdtz_to_index["temperature"]]
                            sch_coop = sdtz[sdtz_to_index["coop"]]
                            if sdtz[sdtz_to_index["holidays_id"]] > 0:
                                sch_holidays = 1
                            else:
                                sch_holidays = 0

                    #update the current schedule status
                    cur.execute(
                        "UPDATE zone_current_state SET schedule = %s, sch_time_id = %s WHERE zone_id = %s;",
                        [sch_status, time_id, zone_id],
                    )
                    con.commit()  # commit above

                    #query to check override status and get temperature from override table
                    cur.execute(
                        "SELECT * FROM override WHERE zone_id = %s LIMIT 1;",
                        (zone_id,),
                    )
                    if cur.rowcount > 0:
                        override = cur.fetchone()
                        override_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                        zone_override_status = override[override_to_index["status"]]
                        override_c = override[override_to_index["temperature"]]
                    else:
                        zone_override_status = 0;

                    if zone_category != 3:
                        manual_button_override = 0
                        for key in controllers_dict[zone_id]:
                            zone_controler_id = controllers_dict[zone_id][key]["controler_id"]
                            zone_controler_child_id = controllers_dict[zone_id][key]["controler_child_id"]
                            zone_controller_type = controllers_dict[zone_id][key]["zone_controller_type"]
                            zone_fault = 0
                            zone_ctr_fault = 0
                            zone_sensor_fault = 0

                            controler_found = False
                            # check if an MQTT type sensor
                            cur.execute(
                                "SELECT * FROM `mqtt_devices` WHERE nodes_id = %s AND child_id = %s AND type = 0 LIMIT 1",
                                (zone_controler_id, zone_controler_child_id),
                            )
                            if cur.rowcount > 0:
                                controler_found = True
                                mqtt_device = cur.fetchone()
                                mqtt_device_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                controler_seen_time = mqtt_device[mqtt_device_to_index["last_seen"]]
                                controler_notice = mqtt_device[mqtt_device_to_index["notice_interval"]]
                            else:
                                #Get data from nodes table
                                cur.execute(
                                    "SELECT * FROM nodes WHERE node_id = %s AND status IS NOT NULL LIMIT 1;",
                                    (zone_controler_id,),
                                )
                                if cur.rowcount > 0:
                                    controler_found = True
                                    node = cur.fetchone()
                                    node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                    controler_seen_time = node[node_to_index["last_seen"]]
                                    controler_notice = node[node_to_index["notice_interval"]]
                            if controler_found:
                                if controler_notice > 0 and settings_dict["test_mode"] != 3:
                                    if controler_seen_time <  time_stamp + datetime.timedelta(minutes =- controler_notice):
                                        zone_fault = 1
                                        zone_ctr_fault = 1
                                        if dbgLevel >= 2:
                                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone valve communication timeout for This Zone. Node Last Seen: " + str(controler_seen_time))

                            #if add-on controller then process state change from GUI or api call
                            if zone_category == 2:
                                current_state = zone_status_prev;
                                if dbgLevel == 1:
                                    print("current_state",current_state)
                                add_on_state = controllers_dict[zone_id][key]["zone_controller_state"]
                                zone_controler_child_id = controllers_dict[zone_id][key]["controler_child_id"]
                                if zone_mode_current == 74 or zone_mode_current == 75:
                                    if sch_status == 1:
                                        if current_state != add_on_state:
                                            cur.execute(
                                                "UPDATE override SET status = 1, sync = '0' WHERE zone_id = %s;",
                                                [zone_id,],
                                            )
                                            con.commit()  # commit above
                                    else:
                                        if zone_override_status == 1:
                                            cur.execute(
                                                "UPDATE override SET status = 0, sync = '0' WHERE zone_id = %s;",
                                                [zone_id,],
                                            )
                                            con.commit()  # commit above

                                #check is switch has manually changed the ON/OFF state
                                #for zones with multiple controllers - only capture the first change
                                if 'Tasmota' in zone_controller_type and manual_button_override == 0:
                                    if base_addr == '000.000.000.000':
                                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - NO Gateway Address is Set")
                                    else:
                                        cur.execute(
                                            "SELECT * FROM http_messages WHERE zone_id = %s AND message_type = 1 LIMIT 1;",
                                            (zone_id,),
                                        )
                                        if cur.rowcount > 0:
                                            http = cur.fetchone()
                                            http_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                            http_command = http[http_to_index["command"]]
                                            url = "http://" + base_addr + str(zone_controler_child_id) + "/cm"
                                            cmd = "POWER"
                                            param = "ON"
                                            myobj = {"cmnd": "power"}
                                            try:
                                                x = requests.post(url, data=myobj, timeout=1.5)  # send request to Sonoff device
                                                if x.status_code == 200:
                                                    if dbgLevel == 1:
                                                        print("Tasmota State: ",x.json().get(cmd))
                                                    if x.json().get(cmd) == param:
                                                        new_add_on_state = 1
                                                    else:
                                                        new_add_on_state = 0
                                                    if manual_button_override == 0 and current_state != new_add_on_state:
                                                        manual_button_override = 1
                                                else:
                                                    manual_button_override = 0
                                            except:
                                                print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Unable to communicate with: %s" % url[0:-3])
                        #end for key in controllers_dict[zone_id]:

                        #if there has been an external update to any of the relays associated with this zone (both Tasmota and MySensor), then update MaxAir to capture the new state
                        #will update the following tables - messages_out, zone, zone_relays, zone_current state and override
                        #the Home screen will be updated once this script has executed
                        manual_button_override = 0 ################ need to capture multiple buttons for zones with more than 1 controller
                        if manual_button_override == 1:
                            add_on_state = new_add_on_state
                            zone_c = new_add_on_state
                            cur.execute(
                                "SELECT * FROM messages_out WHERE zone_id =%s",
                                (zone_id,),
                            )
                            if cur.rowcount > 0:
                                messages_out = cur.fetchall()
                                messages_out_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                for m in messages_out:
                                    id = m[messages_out_to_index["id"]]
                                    node_id = m[messages_out_to_index["node_id"]]
                                    cur.execute(
                                        "SELECT type FROM nodes WHERE node_id = %s LIMIT 1;",
                                        (node_id,),
                                    )
                                    if cur.rowcount > 0:
                                        node = cur.fetchone()
                                        node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                        if 'Tasmota' in node[node_to_index['type']]:
                                            if add_on_state == 0:
                                                message_type = "0"
                                            else:
                                                message_type = "1"
                                            cur.execute(
                                                "SELECT  command, parameter FROM http_messages WHERE node_id = %s AND message_type = %s LIMIT 1;",
                                                (node_id, message_type),
                                            )
                                            if cur.rowcount > 0:
                                                http_message = cur.fetchone()
                                                http_message_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                                set =  int(message_type)
                                                payload = http_message[http_message_to_index['command']] + " " + http_message[http_message_to_index['parameter']]
                                        else:
                                            if add_on_state == 0:
                                                set = 0
                                                payload = "0"
                                            else:
                                                set = 1
                                                payload = "1"
                                        cur.execute(
                                            "UPDATE messages_out SET payload = %s, datetime = %s, sent = '0' WHERE id = %s;",
                                            [payload, time_stamp.strftime("%Y-%m-%d %H:%M:%S"), id],
                                        )
                                        con.commit()  # commit above
                                cur.execute(
                                    "UPDATE zone_relays SET state = %s, current_state = %s WHERE zone_id = %s;",
                                    [set, set, zone_id],
                                )
                                con.commit()  # commit above
                                if sch_active == 0:
                                    if add_on_state == 0:
                                        mode = 0
                                    else:
                                        mode = 114
                                else:
                                    if add_on_state == 0:
                                        mode = 75
                                    else:
                                        mode = 74
                                cur.execute(
                                    "UPDATE zone_current_state SET mode  = %s, status = %s , status_prev = %s WHERE zone_id = %s;",
                                    [mode, set, zone_status_current, zone_id],
                                )
                                con.commit()  # commit above
                                cur.execute(
                                    "UPDATE zone SET zone_state = %s WHERE id = %s;",
                                    [set, zone_id],
                                )
                                con.commit()  # commit above
                                zone_state_current = set
                                if sch_status == 1:
                                    if zone_override_status == 0:
                                        cur.execute(
                                            "UPDATE override SET status = 1, sync = '0' WHERE zone_id = %s;",
                                            [zone_id,],
                                        )
                                        con.commit()  # commit above
                                else:
                                    if zone_override_status == 1:
                                        cur.execute(
                                            "UPDATE override SET status = 0, sync = '0' WHERE zone_id = %s;",
                                            [zone_id,],
                                        )
                                        con.commit()  # commit above
                    else:
                        zone_fault = 0
                        zone_ctr_fault = 0
                        zone_sensor_fault = 0
                        manual_button_override = 0
                        controler_seen_time = ""

                    #query to check boost status and get temperature from boost table
                    cur.execute(
                        "SELECT * FROM boost WHERE zone_id = %s AND status = 1 LIMIT 1;",
                        (zone_id, ),
                    )
                    if cur.rowcount > 0:
                        boost = cur.fetchone()
                        boost_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                        boost_status = boost[boost_to_index['status']]
                        boost_time = boost[boost_to_index['time']]
                        boost_c = boost[boost_to_index['temperature']]
                        boost_minute = boost[boost_to_index['minute']]
                        boost_mode = boost[boost_to_index['hvac_mode']]
                    else:
                        boost_status = 0

                    #check boost time is passed, if it passed then update db and set to boost status to 0
                    if boost_status == 1:
                        if time_stamp < boost_time + datetime.timedelta(minutes = boost_minute):
                            boost_active = 1
                            if dbgLevel >= 2:
                                print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Boost is Active for This Zone")
                        elif time_stamp >= boost_time + datetime.timedelta(minutes = boost_minute) and  boost_status == 1:
                            boost_active = 0
                            #You can comment out if you dont have Boost Button Console installed.
                            cur.execute(
                                "SELECT * FROM boost WHERE zone_id = %s AND status = '1';",
                                (zone_id, ),
                            )
                            if cur.rowcount > 0:
                                brow = cur.fetchone()
                                brow_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                boost_button_id = brow[brow_to_index['boost_button_id']]
                                boost_button_child_id = brow[brow_to_index['boost_button_child_id']]
                                cur.execute(
                                    "UPDATE messages_out SET payload = %s, sent = '0' WHERE zone_id = %s AND node_id = %s AND child_id = %s LIMIT 1;",
                                    [str(boost_active), zone_id, boost_button_id, boost_button_child_id],
                                )
                                con.commit()  # commit above
                                #update Boost Records in database
                                cur.execute(
                                    "UPDATE boost SET status = %s, sync = '0' WHERE zone_id = %s AND status = '1';",
                                    [str(boost_active), zone_id],
                                )
                                con.commit()  # commit above
                        else:
                            boost_active = 0
                    else:
                        boost_active = 0

                    #Check Zones with sensor associated
                    if zone_category == 0 or zone_category == 1 or zone_category == 3 or zone_category == 4 or zone_category == 5:
                        cur.execute(
                            "SELECT * from schedule_night_climat_zone_view WHERE ((`end` > `start` AND CURTIME() between `start` AND `end`) OR (`end` < `start` AND CURTIME() < `end`) OR (`end` < `start` AND CURTIME() > `start`)) AND `zone_id` = %s AND `time_status` = '1' AND `tz_status` = '1'  AND (`WeekDays` & (1 << %s)) > 0 LIMIT 1;",
                            (zone_id, dow),
                        )
                        if cur.rowcount > 0:
                            nc = cur.fetchone()
                            nc_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                            nc_time_status = nc[nc_to_index['time_status']]
                            nc_zone_status = nc[nc_to_index['tz_status']]
                            nc_zone_id = nc[nc_to_index['zone_id']]
                            nc_start_time = nc[nc_to_index['start']]
                            nc_end_time = nc[nc_to_index['end']]
                            nc_min_c = nc[nc_to_index['min_temperature']]
                            nc_max_c = nc[nc_to_index['max_temperature']]
                            nc_weekday = nc[nc_to_index['WeekDays']] & (1 << dow);
                            #work out slope of the temperature graph
                            cur.execute(
                                "SELECT  sensors_id AS node_id, sensor_child_id AS child_id FROM zone_view WHERE id = %s LIMIT 1;",
                                (nc_zone_id, ),
                            )
                            if cur.rowcount > 0:
                                sensor = cur.fetchone()
                                sensor_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                node_id = sensor[sensor_to_index['node_id']]
                                child_id = sensor[sensor_to_index['child_id']]
                                cur.execute(
                                    "SELECT payload, datetime FROM  `messages_in_view_24h` WHERE node_id = %s AND child_id = %s;",
                                    (node_id, child_id),
                                )
                                if cur.rowcount > 0:
                                    msg_in = cur.fetchall()
                                    msg_in_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                    index = 0
                                    temp1 = 0
                                    temp2 = 0
                                    for m in msg_in:
                                        if index < 10:
                                            temp1 = temp1 + m[msg_in_to_index['payload']]
                                        else:
                                            temp2 = temp2 + m[msg_in_to_index['payload']]
                                        index = index + 1
                                        if index == 20:
                                            break
                                    avg_temp1 = temp1/10
                                    avg_temp2 = temp2/10
                                    if avg_temp2 < avg_temp1:
                                        nc_slope = 1
                                    elif avg_temp2 == avg_temp1:
                                        nc_slope = 0
                                    else:
                                        nc_slope = -1


                            #night climate time to add 10 minuts for record purpose
                            nc_end_time_rc = time_stamp+ datetime.timedelta(minutes = 10)
                            nc_end_time_rc_str = nc_end_time_rc.strftime('%Y-%m-%d %H:%M:%S')
                            if sch_status == 0 and isNowInTimePeriod((datetime.datetime.min + nc_start_time).time(), (datetime.datetime.min + nc_end_time).time(), time_stamp.time()) and nc_time_status == 1 and nc_zone_status == 1 and nc_weekday > 0:
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Night Climate Enabled for This Zone")
                                night_climate_status = 1
                            else:
                                night_climate_status = 0
                        else:
                            night_climate_status = 0

                        #Get Weather Temperature
                        weather_fact = 0
                        if system_controller_mode == 0 and sc_weather_factoring == 1:
                            if sc_weather_sensor_id == 0:
                                weather_sensor_node_id = 1
                                weather_sensor_child_id = 0
                            else:
                                cur.execute(
                                    "SELECT sensor_id, sensor_child_id FROM sensors WHERE id = %s LIMIT 1;",
                                    (sc_weather_sensor_id, ),
                                )
                                if cur.rowcount > 0:
                                    sensor = cur.fetchone()
                                    sensor_in_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                    weather_sensor_id = sensor[sensor_in_to_index['sensor_id']]
                                    weather_sensor_child_id = sensor[sensor_in_to_index['sensor_child_id']]
                                    cur.execute(
                                        "SELECT * FROM nodes WHERE id = %s LIMIT 1;",
                                        (weather_sensor_id, ),
                                    )
                                    if cur.rowcount > 0:
                                        node = cur.fetchone()
                                        node_in_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                        weather_sensor_node_id = node[node_in_to_index['node_id']]

                            cur.execute(
                                "SELECT * FROM messages_in WHERE node_id = %s AND child_id = %s ORDER BY id desc LIMIT 1;",
                                (weather_sensor_node_id, weather_sensor_child_id),
                            )
                            if cur.rowcount > 0:
                                messages_in = cur.fetchone()
                                messages_in_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                weather_c = messages_in[messages_in_to_index['payload']]
                                #    1    00-05    0.3
                                #    2    06-10    0.4
                                #    3    11-15    0.5
                                #    4    16-20    0.6
                                #    5    21-30    0.7
                                if weather_c <= 5 :
                                    weather_fact = 0.3
                                elif weather_c <= 10:
                                    weather_fact = 0.4
                                elif weather_c <= 15:
                                    weather_fact = 0.5
                                elif weather_c <= 20:
                                    weather_fact = 0.6
                                elif weather_c <= 30:
                                    weather_fact = 0.7

                        #Following to decide which temperature is target temperature
                        if livetemp_active == 1 and livetemp_zone_id == zone_id:
                            target_c = float(livetemp_c)
                        elif boost_active == 1:
                            target_c = boost_c
                        elif night_climate_status == 1:
                            target_c = nc_min_c
                        elif zone_override_status == 1:
                            target_c = override_c
                        elif sch_status == 0:
                            target_c = default_c
                        else:
                            target_c = sch_c

                        #calculate cutin/cut out temperatures
                        temp_cut_out_rising = target_c - weather_fact - zone_sp_deadband
                        temp_cut_out_falling = target_c - weather_fact + zone_sp_deadband
                        temp_cut_in = target_c - weather_fact - zone_sp_deadband
                        if night_climate_status == 0:
                            temp_cut_out = target_c - weather_fact
                        else:
                            temp_cut_out = nc_max_c - weather_fact;

                        #check if hysteresis is passed its time or not
                        #only ptocess hysteresis for EU systems when stop time is set
                        #also only process if not in test mode 3 using a false run time
                        if system_controller_mode == 0 and sc_stop_datetime is not None and settings_dict["test_mode"] != 3:
                            hysteresis_time = sc_stop_datetime + datetime.timedelta(minutes = system_controller_hysteresis_time)
                            if hysteresis_time > time_stamp:
                                hysteresis = 1
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Hysteresis time: " + hysteresis_time.strftime('%Y-%m-%d %H:%M:%S'))
                            else:
                                hysteresis = 0
                        else:
                            hysteresis = 0

                        # check if an MQTT type sensor
                        sensor_found = False
                        cur.execute(
                            "SELECT * FROM `mqtt_devices` WHERE nodes_id = %s AND child_id = %s LIMIT 1",
                            (zone_sensor_id, zone_sensor_child_id),
                        )
                        if cur.rowcount > 0:
                            sensor_found = True
                            mqtt_device = cur.fetchone()
                            mqtt_device_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                            #sensor_seen_time = node[node_to_index['last_seen']] #not using this cause it updates on battery update
                            sensor_notice = mqtt_device[mqtt_device_to_index['notice_interval']]
                        else:
                            #Check sensor notice interval and notice logic
                            cur.execute(
                                "SELECT * FROM nodes WHERE id =%s AND status IS NOT NULL LIMIT 1;",
                                (zone_sensor_id,),
                            )
                            if cur.rowcount > 0:
                                sensor_found = True
                                node = cur.fetchone()
                                node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                #sensor_seen_time = node[node_to_index['last_seen']] #not using this cause it updates on battery update
                                sensor_notice = node[node_to_index['notice_interval']]
                        if sensor_found:
                            if sensor_notice > 0 and temp_reading_time is not None and settings_dict["test_mode"] != 3:
                                sensor_seen_time = temp_reading_time #using time from messages_in
                                if sensor_seen_time <  time_stamp + datetime.timedelta(minutes =- sensor_notice):
                                    zone_fault = 1
                                    zone_sensor_fault = 1
                                    if dbgLevel >= 2:
                                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Temperature sensor communication timeout for This Zone. Last temperature reading: " + str(temp_reading_time))

                        #Check system controller notice interval and notice logic
                        if heat_relay_notice > 0:
                            heat_relay_seen_time = heat_relay_seen
                            if heat_relay_seen_time  < time_stamp + datetime.timedelta(minutes =- heat_relay_notice) and settings_dict["test_mode"] != 3:
                                zone_fault = 1
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller controler communication timeout. System Controller Last Seen: " + str(heat_relay_seen))

                        #create array zone states, used to determine if new zone log table entry is required
                        z_state_dict[zone_id] = zone_state_current
                    #end Check Zone category 0 or 1

                    #****************************** MAIN PROCESSING SECTION ******************************

                    #initialize variables
                    zone_mode = 0
                    if sc_mode != 0 and away_status == 1 and sch_status == 1:
                        active_sc_mode = 1
                    else:
                        active_sc_mode = sc_mode
                    #check no zone fault and if not a switch zone (cat 2) that there is a valid zone sensor reading
                    if zone_fault == 0 and (zone_c is not None or zone_category == 2):

                        if dbgLevel == 1:
                            print("zone_state_current",zone_state_current,type(zone_state_current),"zone_status_prev",zone_status_prev,type(zone_status_prev),"sch_status",sch_status,"boost_status",boost_status,"zone_status",zone_status,"zone_override_status",zone_override_status,type(zone_override_status))

                        #process category 0 zone (BOILER)
                        #----------------------------------------------------------------------
                        if zone_category == 0:
                            #check system controller not in OFF mode
                            if sc_mode != 0:
                                if frost_active == 1:
                                    zone_status = 1
                                    zone_mode = 21
                                    start_cause="Frost Protection"
                                    zone_state = 1
                                elif frost_active == 2:
                                    zone_status = zone_status_prev
                                    zone_mode = 22 - zone_status_prev
                                    start_cause = "Frost Protection Deadband"
                                    stop_cause = "Frost Protection Deadband"
                                    zone_state = zone_status_prev
                                elif frost_active == 0 and zone_c < zone_max_c and hysteresis == 0:
                                    #system controller has not exceeded max running timw
                                    if (system_controller_on_time < system_controller_max_operation_time) or (system_controller_max_operation_time == 0):
                                        if active_sc_mode == 4 or (active_sc_mode == 2 and 'Heating' in zone_type)  or (active_sc_mode == 3 and 'Water' in zone_type):
                                            if zone_c < temp_cut_out_rising:
                                                zone_status = 1
                                                zone_mode = 141
                                                start_cause="Manual Start"
                                                zone_state = 1
                                            if (zone_c >= temp_cut_out_rising) and (zone_c < temp_cut_out):
                                                zone_status = zone_status_prev
                                                zone_mode = 142 - zone_status_prev
                                                start_cause = "Manual Target Deadband"
                                                stop_cause = "Manual Target Deadband (" + str(zone_c) + ")" 
                                                zone_state = zone_status_prev
                                            if zone_c >= temp_cut_out:
                                                zone_status = 0
                                                zone_mode = 140
                                                stop_cause = "Manual Target C Achieved (" + str(zone_c) + ")"
                                                zone_state = 0
                                        elif away_status == 0 or (away_status == 1 and sch_status == 1):
                                            if holidays_status == 0 or sch_holidays == 1:
                                                if sch_active and zone_override_status == 1:
                                                    zone_status = 0
                                                    stop_cause = "Override Finished"
                                                    if (zone_c < temp_cut_out_rising):
                                                        zone_status = 1
                                                        zone_mode = 71
                                                        start_cause = "Schedule Override Started"
                                                        expected_end_date_time = sch_end_time_str
                                                        zone_state = 1
                                                    if zone_c >= temp_cut_out_rising and zone_c < temp_cut_out:
                                                        zone_status = zone_status_prev
                                                        zone_mode = 72 - zone_status_prev
                                                        start_cause = "Schedule Override Target Deadband"
                                                        stop_cause = "Schedule Override Target Deadband (" + str(zone_c) + ")"
                                                        zone_state = zone_status_prev;
                                                    if zone_c >= temp_cut_out:
                                                        zone_status = 0
                                                        zone_mode = 70
                                                        stop_cause = "Schedule Override Target C Achieved (" + str(zone_c) + ")"
                                                        zone_state = 0
                                                elif boost_status == 0:
                                                    zone_status = 0
                                                    stop_cause = ""
                                                    if night_climate_status == 0:
                                                        if sch_status == 1 and zone_c < temp_cut_out_rising and (sch_coop == 0 or system_controller_active_status == 1):
                                                            zone_status = 1
                                                            zone_mode = 81
                                                            if zone_schedule_current == 0 and sch_status == 1:
                                                                start_cause = "Schedule Started"
                                                            else:
                                                                start_cause = "Schedule Restarted"
                                                            expected_end_date_time = sch_end_time_str
                                                            zone_state = 1
                                                        if (system_controller_mode == 0 and sch_status == 1 and zone_c < temp_cut_out_rising) and (sch_coop == 1 and system_controller_mode == 0) and system_controller_active_status == 0:
                                                            zone_status = 0
                                                            zone_mode = 83
                                                            stop_cause = "Coop Start Schedule Waiting for Controller Start"
                                                            expected_end_date_time = sch_end_time_str
                                                            zone_state = 0
                                                        if sch_status == 1 and zone_c >= temp_cut_out_rising and zone_c < temp_cut_out:
                                                            zone_status = zone_status_prev
                                                            zone_mode = 82 - zone_status_prev
                                                            start_cause = "Schedule Target Deadband"
                                                            stop_cause = "Schedule Target Deadband (" + str(zone_c) + ")"
                                                            zone_state = zone_status_prev
                                                        if sch_status == 1 and zone_c >= temp_cut_out:
                                                            zone_status = 0
                                                            zone_mode = 80
                                                            stop_cause = "Schedule Target C Achieved (" + str(zone_c) + ")"
                                                            zone_state = 0
                                                        if sch_status == 0 and sch_holidays == 1:
                                                            zone_status = 0
                                                            zone_mode = 40
                                                            stop_cause = "Holidays - No Schedule"
                                                            zone_state = 0
                                                        if sch_status == 0 and sch_holidays == 0:
                                                            zone_status = 0
                                                            zone_mode = 0
                                                            # set the stop_cause dependant on the running mode
                                                            if floor(zone_mode_current/10)*10 == 60:
                                                                stop_cause = "Boost Finished"
                                                            elif floor(zone_mode_current/10)*10 == 80:
                                                                stop_cause = "Schedule Finished"
                                                            zone_state = 0
                                                    elif night_climate_status == 1 and zone_c < temp_cut_out_rising:
                                                        zone_status = 1
                                                        zone_mode = 51
                                                        start_cause = "Night Climate"
                                                        expected_end_date_time = nc_end_time_rc_str
                                                        zone_state = 1
                                                    elif night_climate_status == 1 and zone_c >= temp_cut_out_rising and zone_c < temp_cut_out:
                                                        zone_status = zone_status_prev
                                                        zone_mode = 52 - zone_status_prev
                                                        start_causec = "Night Climate Deadband"
                                                        stop_cause = "Night Climate Deadband (" + str(zone_c) + ")"
                                                        expected_end_date_time = nc_end_time_rc_str
                                                        zone_state = zone_status_prev
                                                    elif night_climate_status == 1 and zone_c >= temp_cut_out:
                                                        zone_status = 0
                                                        zone_mode = 50
                                                        stop_cause = "Night Climate C Reached (" + str(zone_c) + ")"
                                                        expected_end_date_time = nc_end_time_rc_str
                                                        zone_state = 0
                                                elif boost_status == 1 and zone_c < temp_cut_out_rising:
                                                    zone_status = 1
                                                    zone_mode = 61
                                                    start_cause = "Boost Active"
                                                    expected_end_date_time = boost_time + datetime.timedelta(minutes = boost_minute)
                                                    zone_state = 1
                                                elif boost_status == 1 and zone_c >= temp_cut_out_rising and zone_c < temp_cut_out:
                                                    zone_status = zone_status_prev
                                                    zone_mode = 62 - zone_status_prev
                                                    start_cause = "Boost Target Deadband"
                                                    stop_cause = "Boost Target Deadband (" + str(zone_c) + ")"
                                                    zone_state = zone_status_prev
                                                elif boost_status == 1 and zone_c >= temp_cut_out:
                                                    zone_status = 0
                                                    zone_mode = 60
                                                    stop_cause = "Boost Target C Achived (" + str(zone_c) + ")"
                                                    zone_state = 0
                                                #end if($boost_status=='0')
                                            elif holidays_status == 1 and sch_holidays == 0:
                                                zone_status = 0
                                                zone_mode = 40
                                                stop_cause = "Holiday Active"
                                                zone_state = 0
                                        elif away_status == 1 and sch_status == 0:
                                            zone_status = 0
                                            zone_mode = 90
                                            stop_cause = "Away Active"
                                            zone_state = 0
                                    elif system_controller_max_operation_time != 0:
                                        zone_status = 0
                                        zone_mode = (floor(zone_status_prev/10)*10) + 8;
                                        stop_cause = "Max Running Time Exceeded - Hysteresis active"
                                        zone_state = 0
                                elif zone_c >= zone_max_c:
                                    zone_status = 0
                                    zone_mode = 30
                                    stop_cause="Zone Reached its Max Temperature " + str(zone_max_c) + ")"
                                    zone_state = 0
                                elif hysteresis == 1 and floor(zone_status_prev%10) != 8:
                                    zone_status = 0
                                    zone_mode = 100
                                    stop_cause = "Hysteresis active "
                                    zone_state = 0
                            else:
                                zone_status = 0
                                zone_mode = 0
                                stop_cause = "System is OFF"
                                zone_state = 0

                        #process category 3 zone (HVAC)
                        #----------------------------------------------------------------------
                        elif zone_category == 3 or zone_category == 4:
                            #check system controller not in OFF mode
                            if sc_mode != 0:
                                if frost_active == 1:
                                    zone_status = 1
                                    zone_mode = 21
                                    start_cause = "Frost Protection"
                                    zone_state= 1
                                    hvac_state = 1
                                elif frost_active == 2:
                                    zone_status = zone_status_prev
                                    zone_mode = 22 - zone_status_prev
                                    start_cause = "Frost Protection Deadband"
                                    stop_cause = "Frost Protection Deadband"
                                    zone_state = zone_status_prev
                                    hvac_state = 1
                                elif frost_active == 0 and zone_c < zone_max_c and zone_c > zone_min_c:
                                    if away_status == 0 or (away_status == 1 and sch_status == 1):
                                        if holidays_status == 0 or sch_holidays == 1:
                                            if boost_status == 0:
                                                zone_status="0"
                                                stop_cause = "Boost Finished"
                                                if active_sc_mode == 0: #OFF
                                                    zone_status = 0
                                                    zone_mode = 0
                                                    stop_cause = "HVAC OFF "
                                                    zone_state = 0
                                                elif active_sc_mode == 1: # TIMER mode HEAT Only
                                                    if sch_status == 1:
                                                        if zone_c <= temp_cut_out_rising:
                                                            zone_status = 1
                                                            zone_mode = 81
                                                            start_cause = "HVAC Heat Cycle Started "
                                                            zone_state = 1
                                                            hvac_state = 1
                                                        elif zone_c > temp_cut_out_rising:
                                                            zone_status = 0
                                                            zone_mode = 80
                                                            stop_cause = "HVAC Climate C Reached "
                                                            zone_state = 0
                                                            hvac_state = 1
                                                elif active_sc_mode == 2: # TIMER mode COOL Only
                                                    if sch_status == 1:
                                                        if zone_c >= temp_cut_out_falling:
                                                            zone_status = 1
                                                            zone_mode = 86
                                                            start_cause="HVAC Cool Cycle Started "
                                                            zone_state = 1
                                                            hvac_state = 0
                                                        elif zone_c < temp_cut_out_falling:
                                                            zone_status = 0
                                                            zone_mode = 80
                                                            stop_cause = "HVAC Climate C Reached "
                                                            zone_state = 0
                                                            hvac_state = 0
                                                elif active_sc_mode == 3: # TIMER mode AUTO
                                                    if sch_status == 1:
                                                        if zone_c <= temp_cut_out_rising:
                                                            zone_status = 1
                                                            zone_mode = 81
                                                            start_cause = "HVAC Heat Cycle Started "
                                                            zone_state = 1
                                                            hvac_state = 1
                                                        elif zone_c > temp_cut_out_rising and zone_c < temp_cut_out_falling:
                                                            zone_status = 0
                                                            zone_mode = 80
                                                            stop_cause = "HVAC Climate C Reached "
                                                            zone_state = 0
                                                        elif zone_c >= temp_cut_out_falling:
                                                            zone_status = 1
                                                            zone_mode = 86
                                                            start_cause = "HVAC Cool Cycle Started "
                                                            zone_state = 1
                                                            hvac_state = 0
                                                elif active_sc_mode ==  4: # AUTO mode
                                                        if zone_c <= temp_cut_out_rising:
                                                            zone_status = 1
                                                            zone_mode = 121
                                                            start_cause = "HVAC Heat Cycle Started "
                                                            zone_state = 1
                                                            hvac_state = 1
                                                        elif zone_c > temp_cut_out_rising and zone_c < temp_cut_out_falling:
                                                            zone_status = 0
                                                            zone_mode = 120
                                                            stop_cause = "HVAC Climate C Reached "
                                                            zone_state = 0
                                                        elif zone_c >= temp_cut_out_falling:
                                                            zone_status = 1
                                                            zone_mode = 126
                                                            start_cause = "HVAC Cool Cycle Started "
                                                            zone_state = 1
                                                            hvac_state = 0
                                                elif active_sc_mode == 5: # FAN mode
                                                     zone_status = 1
                                                     if sch_status == 1:
                                                         zone_mode = 87
                                                     else:
                                                         zone_mode = 127
                                                     start_cause = "HVAC Fan Only ";
                                                     zone_state = 1
                                                elif active_sc_mode == 6: # HEAT mode
                                                    if zone_c >= temp_cut_out_rising:
                                                        zone_status = 0
                                                        if sch_status == 1:
                                                            zone_mode = 80
                                                        else:
                                                            zone_mode = 120
                                                        stop_cause = "HVAC Climate C Reached "
                                                        zone_state = 0
                                                    elif zone_c < temp_cut_out_rising:
                                                        zone_status = 1
                                                        if system_controller_active_status == 1:
                                                            if sch_status == 1:
                                                                zone_mode = 81
                                                            else:
                                                                zone_mode = 121
                                                        else:
                                                            if sch_status == 1:
                                                                zone_mode = 83
                                                            else:
                                                                zone_mode = 123
                                                        start_cause = "HVAC Heat Cycle Started "
                                                        zone_state = 1
                                                    hvac_state = 1
                                                elif active_sc_mode == 7: # COOL mode
                                                    if zone_c <= temp_cut_out_falling:
                                                        zone_status = 0
                                                        if sch_status == 1:
                                                            zone_mode = 80
                                                        else:
                                                            zone_mode = 120
                                                        stop_cause = "HVAC Climate C Reached "
                                                        zone_state = 0
                                                    elif zone_c > temp_cut_out_falling:
                                                        zone_status = 1
                                                        if sch_status == 1:
                                                            zone_mode = 86
                                                        else:
                                                            zone_mode = 126
                                                        start_cause="HVAC Cool Cycle Started "
                                                        zone_state = 1
                                                    hvac_state = 0
                                                # end switch
                                            elif boost_status == 1: #end boost == 0
                                                if boost_mode == 3: # FAN Boost
                                                    zone_status = 1
                                                    zone_mode = 67
                                                    start_cause = "FAN Boost Active"
                                                    expected_end_date_time = boost_time + datetime.timedelta(minutes = boost_minute)
                                                    zone_state = 1
                                                elif boost_mode == 4: # HEAT Boost
                                                    if zone_c < temp_cut_out_rising:
                                                        zone_status= 1
                                                        zone_mode = 61
                                                        start_cause = "HEAT Boost Active"
                                                        expected_end_date_time = boost_time + datetime.timedelta(minutes = boost_minute)
                                                        zone_state = 1
                                                    elif zone_c >= temp_cut_out:
                                                        zone_status = 0
                                                        zone_mode = 60
                                                        stop_cause = "HEAT Boost Target C Achived"
                                                        zone_state = 0
                                                    hvac_state = 1
                                                elif boost_mode == 5: # COOL Boost
                                                    if zone_c > temp_cut_out_falling:
                                                        zone_status = 1
                                                        zone_mode = 66
                                                        start_cause = "COOL Boost Active";
                                                        expected_end_date_time = boost_time + datetime.timedelta(minutes = boost_minute)
                                                        zone_state = 1
                                                    elif zone_c <= temp_cut_out_falling:
                                                        zone_status = 0
                                                        zone_mode = 60
                                                        stop_cause = "COOL Boost Target C Achived"
                                                    zone_state = 0
                                                hvac_state = 0
        				    # end switch
                                        # boost = 1
                                        elif holidays_status == 1 and sch_holidays == 0:
                                            zone_status = 0
                                            zone_mode = 40
                                            stop_cause = "Holiday Active"
                                            zone_state = 0
        		                # end holidays
                                    elif away_status == 1 and sch_status == 0: # end away = 0
                                        zone_status = 0
                                        zone_mode = 90
                                        stop_cause = "Away Active"
                                        zone_state = 0
                                elif zone_c >= zone_max_c:
                                    zone_status = 0
                                    zone_mode = 30
                                    stop_cause="Zone Reached its Max Temperature " + str(zone_max_c)
                                    zone_state = 0
                                elif zone_c <= zone_min_c:
                                    zone_status = 0
                                    zone_mode = 130
                                    stop_cause="Zone Reached its Min Temperature " + str(zone_min_c)
                                    zone_state = 0
                            else:
                                zone_status = 0
                                zone_mode = 0
                                stop_cause = "System is OFF"
                                zone_state = 0

                        #process Binary type zone
                        #----------------------------------------------------------------------
                        elif (zone_category == 1 or zone_category == 5) and sensor_type_id == 3:
        	            #check system controller not in OFF mode
                            if sc_mode != 0:
                                if active_sc_mode == 4 or active_sc_mode == 2:
                                    if zone_c == 1:
                                        zone_status = 1
                                        zone_mode = 141
                                        add_on_start_cause = "Manual Start";
                                        zone_state = 1
                                elif away_status == 1 or (away_status == 1 and sch_status == 1):
                                    if holidays_status == 0 or sch_holidays == 1:
                                        if sch_active and zone_override_status == 1:
                                            zone_status = 0
                                            stop_cause = "Override Finished"
                                            if zone_c < temp_cut_out_rising:
                                                zone_status = 1
                                                zone_mode = 71
                                                start_cause = "Schedule Override Started"
                                                expected_end_date_time = sch_end_time_str
                                                zone_state = 1
                                            if zone_c >= temp_cut_out_rising and zone_c < temp_cut_out:
                                                zone_status = zone_status_prev
                                                zone_mode = 72 - zone_status_prev
                                                start_cause = "Schedule Override Target Deadband"
                                                stop_cause = "Schedule Override Target Deadband"
                                                zone_state = zone_status_prev
                                            if zone_c >= temp_cut_out:
                                                zone_status = 0
                                                zone_mode = 70
                                                stop_cause = "Schedule Override Target C Achieved"
                                                zone_state = 0
                                        elif boost_status == 0:
                                            zone_status = 0
                                            add_on_stop_cause = "Boost Finished"
                                            if sch_status ==1:
                                                sensor_state = int(zone_c)
                                                zone_status = 1
                                                zone_mode = 111
                                                add_on_start_cause = "Schedule Started"
                                                expected_end_date_time = sch_end_time_str
                                                zone_state = sensor_state
                                            if sch_status == 0 and sch_holidays == 1:
                                                zone_status = 0
                                                zone_mode = 40
                                                add_on_stop_cause = "Holidays - No Schedule"
                                                zone_state = 0
                                            if sch_status == 0 and sch_holidays == 0:
                                                zone_status = 0
                                                zone_mode = 0
                                                add_on_stop_cause = "No Schedule"
                                                zone_state = 0
                                    elif holidays_status == 1 and sch_holidays == 0:
                                        zone_status = 0
                                        zone_mode = 40
                                        add_on_stop_cause = "Holiday Active"
                                        zone_state = 0
                                elif away_status == 1 and sch_status == 0:
                                    zone_status = 0
                                    zone_mode = 90
                                    add_on_stop_cause = "Away Active"
                                    zone_state = 0
                            else:
                                zone_status = 0
                                zone_mode = 0
                                add_on_stop_cause = "System is OFF"
                                zone_state = 0

        		#process Zone Category 2 Switch type zone
                        elif zone_category == 2:
                            if sc_mode != 0:
                                if away_status == 1 and sch_status == 0:
                                    zone_status = 0
                                    zone_mode = 90
                                    zone_state = 0
                                    add_on_stop_cause = "Away Active"
                                elif holidays_status == 1 and sch_holidays == 0:
                                    zone_status = 0
                                    zone_mode = 40
                                    zone_state = 0
                                    add_on_stop_cause = "Holiday Active"
                                elif boost_status == 0 and zone_mode_current == 64:
                                    zone_status = 0
                                    zone_mode = 0
                                    zone_state = 0
                                    add_on_stop_cause = "Boost Finished"
                                elif zone_state_current == 0 and zone_override_status == 0 and zone_status_prev == 1 and zone_mode_current == 114:
                                    zone_status = 0
                                    zone_mode = 115
                                    zone_state = 0
                                    add_on_stop_cause = "Manual Stop"
                                elif sch_status == 0 and zone_state_current == 0 and boost_status == 0:
                                    zone_status = 0
                                    zone_mode = 0
                                    zone_state = 0
                                    add_on_stop_cause = "No Schedule"
                                elif sch_status == 1:
    #                                zone_override_status = 0
                                    if zone_override_status == 0:
                                        zone_status = 1
                                        zone_mode = 111
                                        zone_state = 1
                                        add_on_start_cause = "Schedule Started"
                                        add_on_expected_end_date_time = sch_end_time_str
                                    else:
                                        if add_on_state == 1 or zone_state == 1:
                                            zone_status = 1
                                            zone_state = 1
                                        else:
                                            zone_status = 0
                                            zone_mode = 75 - add_on_state
                                            zone_state = add_on_state
                                            if zone_mode == 74:
                                                add_on_start_cause = "Manual Override ON State"
                                            elif zone_mode == 75:
                                                add_on_stop_cause = "Manual Override OFF State"
                                            add_on_expected_end_date_time = sch_end_time_str
                                elif boost_status == 1:
                                    zone_status = 1
                                    zone_mode = 64
                                    add_on_start_cause = "Boost Active"
                                    zone_state = 1
                                    add_on_expected_end_date_time = boost_time + datetime.timedelta(minutes = boost_minute)
                                elif zone_state_current == 1:
                                    if zone_mode_current == 111:
                                        zone_status = 0
                                        zone_mode = 0
                                        zone_state = 0
                                        add_on_stop_cause = "Schedule Finished"
                                    elif zone_mode_current == 74 or zone_mode_current == 75:
                                        zone_status = 0
                                        zone_mode = 0
                                        zone_state = 0
                                        add_on_stop_cause = "Override Finished"
                                    else:
                                        zone_status = 1
                                        zone_mode = 114
                                        zone_state = 1
                                        add_on_start_cause = "Manual Start"
                            else:
                                zone_status = 0
                                zone_mode = 0
                                add_on_stop_cause = "System is OFF"
                                zone_state = 0
                        #process Zones with NO System Controller and a Positive Sensor Gradient
                        #----------------------------------------------------------------------
                        elif zone_category == 1 and sensor_type_id != 3:
                            if sc_mode != 0:
                                if frost_active == 1:
                                    zone_status = 1
                                    zone_mode = 21
                                    add_on_start_cause = "Frost Protection"
                                    zone_state = 1
                                elif frost_active == 2:
                                    zone_status = zone_status_prev
                                    zone_mode = 22 - zone_status_prev
                                    add_on_start_cause = "Frost Protection Deadband"
                                    add_on_stop_cause = "Frost Protection Deadband"
                                    zone_state = zone_status_prev
                                elif frost_active == 0 and zone_c < zone_max_c:
                                    if away_status == 0 or (away_status == 1 and sch_status == 1):
                                        if holidays_status == 0 or sch_holidays == 1:
                                            if zone_maintain_default == 1 and sch_status == 0:
                                                if zone_c < temp_cut_out_rising:
                                                    zone_status = 1
                                                    zone_mode = 141
                                                    add_on_start_cause = "Maintain Default Start"
                                                    add_on_stop_cause = "Maintain Default Start"
                                                    zone_state = 1
                                                if zone_c >= temp_cut_out_rising and zone_c < temp_cut_out:
                                                    zone_status = zone_status_prev
                                                    zone_mode = 142 - zone_status_prev
                                                    add_on_start_cause = "Maintain Default Target Deadband"
                                                    add_on_stop_cause = "Maintain Default Target Deadband"
                                                    zone_state = zone_status_prev
                                                if zone_c >= temp_cut_out:
                                                    zone_status = 0
                                                    zone_mode = 140
                                                    add_on_start_cause = "Maintain Default Target C Achieved"
                                                    add_on_stop_cause = "Maintain Default Target C Achieved"
                                                    zone_state = 0
                                            elif sch_active and zone_override_status== 1:
                                                zone_status = 0
                                                stop_cause = "Override Finished"
                                                if zone_c < temp_cut_out_rising:
                                                    zone_status = 1
                                                    zone_mode = 71
                                                    add_on_start_cause = "Schedule Override Started"
                                                    add_on_expected_end_date_time = sch_end_time_str
                                                    zone_state = 1
                                                if zone_c >= temp_cut_out_rising and zone_c < temp_cut_out:
                                                    zone_status = zone_status_prev
                                                    zone_mode = 72 - zone_status_prev
                                                    add_on_start_cause = "Schedule Override Target Deadband"
                                                    add_on_stop_cause = "Schedule Override Target Deadband"
                                                    zone_state = zone_status_prev
                                                if zone_c >= temp_cut_out:
                                                    zone_status = 0
                                                    zone_mode = 70
                                                    add_on_stop_cause = "Schedule Override Target C Achieved"
                                                    zone_state = 0
                                            elif boost_status == 0:
                                                zone_status = 0
                                                add_on_stop_cause = "Boost Finished"
                                                if night_climate_status == 0:
                                                    if sch_status == 1 and zone_c < temp_cut_out_rising:
                                                        zone_status = 1
                                                        zone_mode = 111
                                                        add_on_start_cause = "Schedule Started"
                                                        add_on_stop_cause = "Schedule Started"
                                                        add_on_expected_end_date_time = sch_end_time_str
                                                        zone_state = 1
                                                    if sch_status == 1 and zone_c >= temp_cut_out_rising and zone_c < temp_cut_out:
                                                        zone_status = zone_status_prev
                                                        zone_mode = 82 - zone_status_prev
                                                        add_on_start_cause = "Schedule Target Deadband"
                                                        add_on_stop_cause = "Schedule Target Deadband"
                                                        zone_state = zone_status_prev
                                                    if sch_status == 1 and zone_c >= temp_cut_out:
                                                        zone_status = 0
                                                        zone_mode = 80
                                                        add_on_stop_cause = "Schedule Target C Achieved"
                                                        zone_state = 0
                                                    if sch_status == 0 and sch_holidays== 1:
                                                        zone_status = 0
                                                        zone_mode = 40
                                                        add_on_stop_cause = "Holidays - No Schedule"
                                                        zone_state = 0
                                                    if sch_status == 0 and sch_holidays == 0:
                                                        zone_status = 0
                                                        zone_mode = 0
                                                        add_on_stop_cause = "No Schedule"
                                                        zone_state = 0
                                                elif night_climate_status== 1 and zone_c < temp_cut_out_rising:
                                                    zone_status = 1
                                                    zone_mode = 51
                                                    add_on_start_cause = "Night Climate"
                                                    add_on_expected_end_date_time = nc_end_time_rc_str
                                                    zone_state = 1
                                                elif night_climate_status== 1 and zone_c >= temp_cut_out_rising and zone_c < temp_cut_out:
                                                    zone_status = zone_status_prev
                                                    zone_mode = 52 - zone_status_prev
                                                    add_on_start_cause = "Night Climate Deadband"
                                                    add_on_stop_cause = "Night Climate Deadband"
                                                    add_on_expected_end_date_time = nc_end_time_rc_str
                                                    zone_state = zone_status_prev
                                                elif night_climate_status == 1 and zone_c >= temp_cut_out:
                                                    zone_status = 0
                                                    zone_mode = 50
                                                    add_on_stop_cause = "Night Climate C Reached"
                                                    add_on_expected_end_date_time = nc_end_time_rc_str
                                                    zone_state = 0
                                            elif boost_status == 1 and zone_c < temp_cut_out_rising:
                                                zone_status = 1
                                                zone_mode = 61
                                                add_on_start_cause = "Boost Active"
                                                add_on_expected_end_date_time = boost_time + datetime.timedelta(minutes = boost_minute)
                                                zone_state = 1
                                            elif boost_status == 1 and zone_c >= temp_cut_out_rising and zone_c < temp_cut_out:
                                                zone_status = zone_status_prev
                                                zone_mode = 62 - zone_status_prev
                                                add_on_start_cause = "Boost Target Deadband"
                                                add_on_stop_cause = "Boost Target Deadband"
                                                zone_state = zone_status_prev
                                            elif boost_status == 1 and zone_c >= temp_cut_out:
                                                zone_status = 0
                                                zone_mode = 60
                                                add_on_stop_cause = "Boost Target C Achived"
                                                zone_state = 0
                                        elif holidays_status == 1 and sch_holidays == 0:
                                            zone_status = 0
                                            zone_mode = 40
                                            add_on_stop_cause = "Holiday Active"
                                            zone_state = 0
                                    elif away_status == 1 and sch_status == 0:
                                        zone_status = 0
                                        zone_mode = 90
                                        add_on_stop_cause = "Away Active"
                                        zone_state = 0
                                elif zone_c >= zone_max_c:
                                    zone_status = 0
                                    zone_mode = 30
                                    add_on_stop_cause = "Zone Reached its Max Temperature ".zone_max_c
                                    zone_state = 0
                            else:
                                zone_status = 0
                                zone_mode = 0
                                add_on_stop_cause = "System is OFF"
                                zone_state = 0

                        #process Zones with NO System Controller and a Negative Sensor Gradient
                        #----------------------------------------------------------------------
                        elif zone_category == 5 and sensor_type_id != 3:
                            if sc_mode != 0:
                                if away_status == 0 or (away_status == 1 and sch_status == 1):
                                    if holidays_status == 0 or sch_holidays == 1:
                                        if zone_maintain_default == 1 and sch_status == 0:
                                            if zone_c > temp_cut_out_falling:
                                                zone_status= 1
                                                zone_mode = 141
                                                add_on_start_cause = "Maintain Default Start"
                                                zone_state = 1
                                            if zone_c <= temp_cut_out_falling and zone_c > temp_cut_out:
                                                zone_status = zone_status_prev
                                                zone_mode = 142 - zone_status_prev
                                                add_on_start_cause = "Maintain Default Target Deadband"
                                                add_on_stop_cause = "Maintain Default Target Deadband"
                                                zone_state = zone_status_prev
                                            if zone_c <= temp_cut_out:
                                                zone_status = 0
                                                zone_mode = 140
                                                add_on_stop_cause = "Maintain Default Target C Achieved"
                                                zone_state = 0
                                        elif sch_active and zone_override_status == 1:
                                            zone_status= 0
                                            stop_cause = "Override Finished"
                                            if zone_c > temp_cut_out_falling:
                                                zone_status= 1
                                                zone_mode = 71
                                                start_cause = "Schedule Override Started"
                                                expected_end_date_time = sch_end_time_str
                                                zone_state = 1
                                            if zone_c <= temp_cut_out_falling and zone_c > temp_cut_out:
                                                zone_status = zone_status_prev
                                                zone_mode = 72 - zone_status_prev
                                                start_cause = "Schedule Override Target Deadband"
                                                stop_cause = "Schedule Override Target Deadband"
                                                zone_state = zone_status_prev
                                            if zone_c <= temp_cut_out:
                                                zone_status= 0
                                                zone_mode = 70
                                                stop_cause = "Schedule Override Target C Achieved"
                                                zone_state = 0
                                        elif boost_status == 0:
                                            zone_status = 0
                                            add_on_stop_cause = "Boost Finished"
                                            if night_climate_status == 0:
                                                if sch_status == 1 and zone_c > temp_cut_out_falling:
                                                    zone_status= 1
                                                    zone_mode = 111
                                                    add_on_start_cause = "Schedule Started"
                                                    expected_end_date_time = sch_end_time_str
                                                    zone_state = 1
                                                if sch_status == 1 and zone_c <= temp_cut_out_falling and zone_c > temp_cut_out:
                                                    zone_status = zone_status_prev
                                                    zone_mode = 112 - zone_status_prev
                                                    add_on_start_cause = "Schedule Target Deadband"
                                                    add_on_stop_cause = "Schedule Target Deadband"
                                                    zone_state = zone_status_prev
                                                if sch_status == 1 and zone_c <= temp_cut_out:
                                                    zone_status= 0
                                                    zone_mode = 110
                                                    add_on_stop_cause = "Schedule Target C Achieved"
                                                    zone_state = 0
                                                if sch_status == 0 and sch_holidays == 1:
                                                    zone_status = 0
                                                    zone_mode = 40
                                                    add_on_stop_cause = "Holidays - No Schedule"
                                                    zone_state = 0
                                                if sch_status == 0 and sch_holidays == 0:
                                                    zone_status = 0
                                                    zone_mode = 0
                                                    add_on_stop_cause = "No Schedule"
                                                    zone_state = 0
                                            elif night_climate_status == 1 and zone_c > temp_cut_out_falling:
                                                zone_status = 1
                                                zone_mode = 51
                                                add_on_start_cause = "Night Climate"
                                                add_on_expected_end_date_time = nc_end_time_rc_str
                                                zone_state = 1
                                            elif night_climate_status == 1 and zone_c <= temp_cut_out_falling and zone_c > temp_cut_out:
                                                zone_status = zone_status_prev
                                                zone_mode = 52 - zone_status_prev
                                                add_on_start_cause = "Night Climate Deadband"
                                                add_on_stop_cause = "Night Climate Deadband"
                                                add_on_expected_end_date_time = nc_end_time_rc_str
                                                zone_state = zone_status_prev
                                            elif night_climate_status == 1 and zone_c <= temp_cut_out:
                                                zone_status = 0
                                                zone_mode = 50
                                                add_on_stop_cause = "Night Climate C Reached"
                                                add_on_expected_end_date_time = nc_end_time_rc_str
                                                zone_state = 0
                                        elif boost_status == 1 and zone_c > temp_cut_out_falling:
                                            zone_status= 1
                                            zone_mode = 61
                                            add_on_start_cause = "Boost Active"
                                            add_on_expected_end_date_time = boost_time + datetime.timedelta(minutes = boost_minute)
                                            zone_state = 1
                                        elif boost_status == 1 and zone_c <= temp_cut_out_falling and zone_c > temp_cut_out:
                                            zone_status = zone_status_prev
                                            zone_mode = 62 - zone_status_prev
                                            add_on_start_cause = "Boost Target Deadband"
                                            add_on_stop_cause = "Boost Target Deadband"
                                            zone_state = zone_status_prev
                                        elif boost_status == 1 and zone_c <= temp_cut_out:
                                            zone_status = 0
                                            zone_mode = 60
                                            add_on_stop_cause = "Boost Target C Achived"
                                            zone_state = 0
                                    elif holidays_status == 1 and sch_holidays == 0:
                                        zone_status= 0
                                        zone_mode = 40
                                        add_on_stop_cause = "Holiday Active"
                                        zone_state = 0
                                elif away_status == 1 and sch_status == 0:
                                    zone_status = 0
                                    zone_mode = 90
                                    add_on_stop_cause = "Away Active"
                                    zone_state = 0
                            else:
                                zone_status = 0
                                zone_mode = 0
                                add_on_stop_cause = "System is OFF"
                                zone_state = 0

                    #zone fault
                    else:
                        zone_status = 0
                        zone_mode = 10
                        zone_state = 0
                        stop_cause = "Zone fault"

                    cur.execute(
                        "UPDATE zone SET sync = '0', zone_state = %s WHERE id = %s LIMIT 1",
                        [zone_state, zone_id],
                    )
                    con.commit()  # commit above
                    if dbgLevel == 1:
                        print("sch_status " + str(sch_status) + ", zone_state " + str(zone_state) + ", boost_status " + str(boost_status) + ", override_status " + str(zone_override_status) + ", zone_mode_current " + str(zone_mode_current) + ", zone_status_prev " + str(zone_status_prev), ", hysteresis_status " + str(hysteresis))

                    #Save the start_cause, stop_causes and expected_end_date_time for this zone
                    z_start_cause_dict[zone_id] = start_cause
                    z_stop_cause_dict[zone_id] = stop_cause
                    z_expected_end_date_time_dict[zone_id] = expected_end_date_time

                    #Update the individual zone controller states for controllers associated with this zone
                    for zc_id in controllers_dict[zone_id]:
                        controllers_dict[zone_id][zc_id ]["zone_controller_state"] = zone_state

                    #Update temperature values fore zone current status table (frost protection and overtemperature)
                    if floor(zone_mode/10) == 2:
                        target_c = frost_target_c
                        temp_cut_out_rising = frost_target_c - zone_sp_deadband
                        temp_cut_out = frost_target_c
                    if floor(zone_mode/10) == 3:
                        target_c = zone_max_c
                        temp_cut_out_rising = 0
                        temp_cut_out = 0
                    #reset if temperature control is not active
                    if floor(zone_mode/10) == 0  or floor(zone_mode/10) == 1  or floor(zone_mode/10) == 4  or floor(zone_mode/10) == 9  or floor(zone_mode/10) == 10:
                        target_c = 0
                        temp_cut_out_rising = 0
                        temp_cut_out = 0

                    #***************************************************************************************
                    # update zone_current_state table
                    #***************************************************************************************
                    # Zone Main Mode
                    #      0 - idle
                    #      10 - fault
                    #      20 - frost
                    #      30 - overtemperature
                    #      40 - holiday
                    #      50 - nightclimate
                    #      60 - boost
                    #      70 - override
                    #      80 - sheduled
                    #      90 - away
                    #      100 - hysteresis
                    #      110 - Add-On
                    #      120 - HVAC
                    #      130 - undertemperature
                    #      140 - manual*/

                    # Zone sub mode - running/ stopped different types
                    #      0 - stopped (above cut out setpoint or not running in this mode)
                    #      1 - heating running
                    #      2 - stopped (within deadband)
                    #      3 - stopped (coop start waiting for the system_controller)
                    #      4 - manual operation ON
                    #      5 - manual operation OFF
                    #      6 - cooling running
                    #      7 - HVAC Fan Only
                    #      8 - Max Running Time Exceeded - Hysteresis active*/

                    if dbgLevel == 1:
                        print("zone_id - " + str(zone_id))
                        print("zone_status - " + str(zone_status))
                        print("zone_c - " + str(zone_c))

                    if zone_category == 3 and zone_c is not None:
                        cur.execute(
                            """UPDATE zone_current_state SET `sync` = 0, mode = %s, status = %s, status_prev = %s, temp_reading = %s, temp_target = %s, temp_cut_in = %s, temp_cut_out = %s,
                            controler_fault = %s, sensor_fault  = %s, sensor_seen_time = %s, sensor_reading_time = %s WHERE zone_id = %s LIMIT 1;""",
                            [zone_mode, zone_status, zone_status_current, zone_c, target_c, temp_cut_out_rising, temp_cut_out, zone_ctr_fault, zone_sensor_fault, sensor_seen_time, temp_reading_time, zone_id],
                        )
                    elif zone_category == 2:
                        cur.execute(
                            "UPDATE zone_current_state SET `sync` = 0, mode = %s, status = %s, status_prev = %s, controler_fault = %s, controler_seen_time = %s WHERE zone_id = %s LIMIT 1;",
                            [zone_mode, zone_status, zone_status_current, zone_ctr_fault, controler_seen_time, zone_id],
                        )
                    elif zone_category == 1 and zone_c is not None:
                        cur.execute(
                            """UPDATE zone_current_state SET `sync` = 0, mode = %s, status = %s, status_prev = %s, temp_reading = %s, temp_target = %s, controler_fault = %s, controler_seen_time = %s,
                            sensor_fault  = %s, sensor_seen_time = %s, sensor_reading_time = %s WHERE zone_id = %s LIMIT 1;""",
                            [zone_mode, zone_status, zone_status_current, zone_c, target_c, zone_ctr_fault, controler_seen_time, zone_sensor_fault, sensor_seen_time, temp_reading_time, zone_id],
                        )
                    elif zone_c is not None:
                        cur.execute(
                            """UPDATE zone_current_state SET `sync` = 0, mode = %s, status = %s, status_prev = %s, temp_reading = %s, temp_target = %s, temp_cut_in = %s, temp_cut_out = %s,
                            controler_fault = %s, controler_seen_time = %s, sensor_fault  = %s, sensor_seen_time = %s, sensor_reading_time = %s WHERE zone_id = %s LIMIT 1;""",
                            [zone_mode, zone_status, zone_status_current, zone_c, target_c, temp_cut_in, temp_cut_out, zone_ctr_fault, controler_seen_time, zone_sensor_fault, sensor_seen_time, temp_reading_time, zone_id],
                        )
                    con.commit()  # commit above

                    if dbgLevel >= 2:
                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Name     " + bc.red + str(zone_name) + bc.ENDC)
                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Type     " + bc.red + str(zone_type) + bc.ENDC)
                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: ID       " + bc.red + str(zone_id) + bc.ENDC)

                    if zone_category == 1:
                        if dbgLevel >= 2:
                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Mode     " + bc.red + str(zone_mode) + bc.ENDC)
                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Sensor Reading     " + bc.red + str(int(zone_c)) + bc.ENDC)
                    elif zone_category == 2:
                        if dbgLevel >= 2:
                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Mode     " + bc.red + str(zone_mode) + bc.ENDC)
                    else:
                        if dbgLevel >= 2:
                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Mode     " + bc.red + str(zone_mode) + bc.ENDC)
                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Sensor Reading     " + bc.red + str(zone_c) + bc.ENDC)
                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Weather Factor     " + bc.red + str(weather_fact) + bc.ENDC)
                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: DeadBand           " + bc.red + str(zone_sp_deadband) + bc.ENDC)
                            if zone_category == 5:
                                print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Cut In Temperature        " + bc.red + str(temp_cut_out_rising) + bc.ENDC)
                            else:
                                print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Cut In Temperature        " + bc.red + str(temp_cut_out_falling) + bc.ENDC)
                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Cut Out Temperature       " + bc.red + str(temp_cut_out) + bc.ENDC)

                    for key in controllers_dict[zone_id]:
                        zone_controler_id = controllers_dict[zone_id][key]["controler_id"]
                        zone_controler_child_id = controllers_dict[zone_id][key]["controler_child_id"]
                        if controllers_dict[zone_id][key]["relay_type_id"] == 5:
                            zp = "Pump"
                        else:
                            zp = "Zone"
                        if dbgLevel >= 2:
                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: " + str(zone_name) + " Controller: " + bc.red + str(zone_controler_id) + bc.ENDC + " Controller Child: " + bc.red + str(zone_controler_child_id) + bc.ENDC + " Status: " + bc.red + str(zone_status) + bc.ENDC)

                    if zone_category == 0 or zone_category == 3 or zone_category == 4:
                        if zone_status == 1:
                            if dbgLevel >= 2:
                                print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: " + str(zone_name) + " Start Cause: " + str(start_cause) + " Target C: " + bc.red + str(target_c) + bc.ENDC + " Zone C: " + bc.red_txt + str(zone_c) + bc.ENDC)
                                if floor(zone_mode/10)*10 == 80:
                                    if dbgLevel >= 2:
                                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Running Schedule " + bc.red + str(sch_name) + bc.ENDC)
                        if zone_status == 0:
                            if dbgLevel >= 2:
                                print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: " + str(zone_name) + " Stop Cause: " + str(stop_cause) + " Target C: " + bc.red + str(target_c) + bc.ENDC + " Zone C: " + bc.red_txt + str(zone_c) + bc.ENDC)
                                if zone_mode == 30 or floor(zone_mode/10)*10 == 80:
                                    if dbgLevel >= 2:
                                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Running Schedule " + bc.blu + str(sch_name) + bc.ENDC)
                    else:
                        if zone_status == 1:
                            if dbgLevel >= 2:
                                print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: " + str(zone_name) + " Start Cause: " + str(add_on_start_cause))
                            if sch_status == 1:
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Running Schedule " + bc.red + str(sch_name) + bc.ENDC)
                        if zone_status == 0:
                            if dbgLevel >= 2:
                                print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: " + str(zone_name) + " Stop Cause: " + str(add_on_stop_cause))
                            if zone_mode == 30 or floor(zone_mode/10)*10 == 80:
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Running Schedule " + bc.blu + str(sch_name) + bc.ENDC)

                    #Pass data to zone commands loop
                    zone_commands_dict[command_index] = {}
                    zone_commands_dict[command_index]["controllers"] = controllers_dict[zone_id]
                    zone_commands_dict[command_index]["zone_id"] = zone_id
                    zone_commands_dict[command_index]["zone_name"] = zone_name
                    zone_commands_dict[command_index]["zone_category"] = zone_category
                    zone_commands_dict[command_index]["zone_status"] = zone_status
                    zone_commands_dict[command_index]["zone_status_prev"] = zone_status_prev
                    zone_commands_dict[command_index]["zone_overrun_prev"] = zone_overrun_prev
                    zone_commands_dict[command_index]["zone_override_status"] = zone_override_status
                    command_index = command_index + 1

                    #process Zone Cat 0 logs
                    if zone_category == 0 or zone_category == 3 or zone_category == 4:
                        #all zone status to system controller array and increment array index
                        system_controller_dict[system_controller_index] = zone_status
                        system_controller_index = system_controller_index + 1
                        #all zone ids and status to multidimensional Array. and increment array index.
                        zone_log_dict[zone_id] = zone_status
                    else:
                        #Process Logs Category 1, 2 and 5 logs if zone status has changed
                        #zone switching ON
                        mode_1 = floor(zone_mode_current/10)*10
                        mode_2 = floor(zone_mode/10)*10
#                        print("mode_1",mode_1,"mode_2",mode_2,"zone_mode_current",zone_mode_current,"zone_mode",zone_mode,"zone_status",zone_status,"zone_status_prev",zone_status_prev,"zone_state",zone_state)
                        if zone_add_on_toggle or zone_mode_current != zone_mode:
                            try:
                                cur.execute(
                                    "UPDATE zone_current_state SET add_on_toggle = 0 WHERE `zone_id` = %s LIMIT 1;",
                                    [zone_id,],
                                )
                                con.commit()  # commit above
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - add_on_toggle updated Successfully.")
                                if (mode_1 == 110 and mode_2 == 140) or (mode_1 == 140 and mode_2 == 110):
                                    try:
                                        cur.execute(
                                            "UPDATE add_on_logs SET stop_datetime = %s, stop_cause = %s WHERE `zone_id` = %s ORDER BY id DESC LIMIT 1;",
                                            [time_stamp.strftime("%Y-%m-%d %H:%M:%S"), add_on_stop_cause, zone_id],
                                        )
                                        con.commit()  # commit above
                                        if dbgLevel >= 2:
                                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Add-On Log table updated Successfully.")
                                    except:
                                        if dbgLevel >= 2:
                                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Add-On Log table update failed.")
                                    if zone_mode == 114 or zone_mode == 21 or  zone_mode == 10:
                                        qry_str = """INSERT INTO `add_on_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`,
                                                  `expected_end_date_time`) VALUES ({}, {}, {}, '{}', '{}', {}, {},{});""".format(0,0,zone_id,time_stamp.strftime("%Y-%m-%d %H:%M:%S"),add_on_start_cause,NULL,NULL,NULL)
                                    else:
                                        qry_str = """INSERT INTO `add_on_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`,
                                                  `expected_end_date_time`) VALUES ({}, {}, {}, '{}', '{}', {}, {},'{}');""".format(0,0,zone_id,time_stamp.strftime("%Y-%m-%d %H:%M:%S"),add_on_start_cause,NULL,NULL,add_on_expected_end_date_time)
                                    try:
                                        cur.execute(qry_str)
                                        con.commit()
                                        if dbgLevel >= 2:
                                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Add-On Log table updated Successfully.")
                                    except:
                                        if dbgLevel >= 2:
                                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Add-On Log table update failed.")
                                elif (mode_1 == 0 and mode_2 == 110) or (zone_status_prev == 0 and  (zone_status == 1 or zone_state  == 1)):
                                    if zone_mode == 114 or zone_mode == 21 or  zone_mode == 10 or  zone_mode == 141:
                                        qry_str = """INSERT INTO `add_on_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`,
                                                  `expected_end_date_time`) VALUES ({}, {}, {}, '{}', '{}', {}, {},{});""".format(0,0,zone_id,time_stamp.strftime("%Y-%m-%d %H:%M:%S"),add_on_start_cause,NULL,NULL,NULL)
                                    else:
                                        qry_str = """INSERT INTO `add_on_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`,
                                                  `expected_end_date_time`) VALUES ({}, {}, {}, '{}', '{}', {}, {},'{}');""".format(0,0,zone_id,time_stamp.strftime("%Y-%m-%d %H:%M:%S"),add_on_start_cause,NULL,NULL,add_on_expected_end_date_time)
                                    try:
                                        cur.execute(qry_str)
                                        con.commit()
                                        if dbgLevel >= 2:
                                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Add-On Log table updated Successfully.")
                                    except:
                                        if dbgLevel >= 2:
                                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Add-On Log table update failed.")
                                #zone switching OFF
                                elif (mode_1 !=0  and  mode_2 == 0) or (zone_status_prev == 1 and  zone_status == 0):
                                    try:
                                        cur.execute(
                                            "UPDATE add_on_logs SET stop_datetime = %s, stop_cause = %s WHERE `zone_id` = %s ORDER BY id DESC LIMIT 1;",
                                            [time_stamp.strftime("%Y-%m-%d %H:%M:%S"), add_on_stop_cause, zone_id],
                                        )
                                        con.commit()  # commit above
                                        if dbgLevel >= 2:
                                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Add-On Log table updated Successfully.")
                                    except:
                                        if dbgLevel >= 2:
                                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Add-On Log table update failed.")
                            except:
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - add_on_toggle update failed.")
                        #end process Zone Cat 1 and 2 logs
                    if dbgLevel >= 2:
                        print("-" * line_len)
                    #process Zone Cat 1 and 2 logs
                else: #end if($zone_status == 1)
                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Name     " + zone_name)
                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Type     " + zone_type)
                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: ID       " + str(zone_id))
                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Error          " + bc.red + "ZONE NOT PROCESSED DUE TO MISSING ASSOCIATED RECORDS" + bc.ENDC)
                    print("-" * line_len)
            # end for x in zones:

            #process any zones present
            if zone_commands_dict:
                #***************************************************************************************
                #                               Zone Commands loop
                #***************************************************************************************
                pump_relays_dict = {}
                for zc in zone_commands_dict:
                    zone_id = zone_commands_dict[zc]["zone_id"]
                    zone_category = zone_commands_dict[zc]["zone_category"]
                    zone_name = zone_commands_dict[zc]["zone_name"]
                    zone_status = zone_commands_dict[zc]["zone_status"]
                    zone_status_prev = zone_commands_dict[zc]["zone_status_prev"]
                    zone_overrun_prev = zone_commands_dict[zc]["zone_overrun_prev"]
                    zone_override_status = zone_commands_dict[zc]["zone_override_status"]
                    controllers = zone_commands_dict[zc]["controllers"]

                    #Zone category 0 and system controller is not requested calculate if overrun needed
                    if (zone_category == 0 or cz_logs_count > 0) and not 1 in system_controller_dict.values():
                        #overrun time <0 - latch overrun for the zone zone untill next system controller start
                        if system_controller_overrun_time < 0:
                            if zone_status_prev == 1 or zone_overrun_prev == 1 or (zone_override_status == 1 and not 1 in system_controller_dict.values()):
                                zone_overrun = 1
                            else:
                                zone_overrun = 0
                        #overrun time = 0 - overrun not needed
                        elif system_controller_overrun_time == 0:
                            zone_overrun = 0
                        #overrun time > 0
                        else:
                            if system_controller_active_status == 1:
                                overrun_end_time = time_stamp + datetime.timedelta(minutes = system_controller_overrun_time)
                                zone_overrun = zone_status_prev;
                            #system controller was switched of previous script run
                            else:
                                if sc_stop_datetime is not None:
                                    overrun_end_time = sc_stop_datetime  + datetime.timedelta(minutes = system_controller_overrun_time)
                                    # if overrun flag was switched on when system controller was switching on and ovverrun did not pass keep overrun on
                                    if time_stamp < overrun_end_time and zone_overrun_prev == 1:
                                        zone_overrun = 1
                                    else:
                                        zone_overrun = 0
                                else:
                                    zone_overrun = 0
                        if zone_overrun != zone_overrun_prev:
                            cur.execute(
                                "UPDATE zone_current_state SET `sync` = 0, overrun = %s WHERE id = %s LIMIT 1;",
                                [zone_overrun, zone_id],
                            )
                            con.commit()  # commit above
                        if zone_overrun == 1:
                            #zone status needs to be 1 when in overrun mode
                            cur.execute(
                                "UPDATE zone_current_state SET status = 1, status_prev = %s  WHERE id =%s LIMIT 1;",
                                [zone_status_current, zone_id],
                            )
                            con.commit()  # commit above
                            if dbgLevel >= 2:
                                print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone " + str(zone_id) + " circulation pump overrun active.")
                            if system_controller_overrun_time > 0:
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Overrun end time " + str(overrun_end_time))
                            else:
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Overrun will end on the next System Controller start.")
                    else:
                        #if zone is not category 0 or system controller is running overrun not needed
                        zone_overrun = 0

                    if zone_status == 1 or zone_overrun == 1:
                        zone_command = 1
                    else:
                        zone_command = 0

                    #process all the zone relays associated with this zone
                    for key in controllers_dict[zone_id]:
                        zc_id = key
                        zone_controler_id = controllers_dict[zone_id][key]["controler_id"]
                        zone_controler_child_id = controllers_dict[zone_id][key]["controler_child_id"]
                        controller_relay_id = controllers_dict[zone_id][key]["controller_relay_id"]
                        zone_relay_type_id = controllers_dict[zone_id][key]["relay_type_id"]
                        zone_on_trigger = controllers_dict[zone_id][key]["controler_on_trigger"]
                        zone_controller_type = controllers_dict[zone_id][key]["zone_controller_type"]
                        manual_button_override = controllers_dict[zone_id][key]["manual_button_override"]
                        zone_controller_state = controllers_dict[zone_id][key]["zone_controller_state"]
                        if zone_controller_state == 1 or zone_overrun == 1:
                            zone_command = 1
                        else:
                            zone_command = 0
                        if zone_on_trigger == 1:
                            relay_on = '1' #GPIO value to write to turn on attached relay
                            relay_off = '0' # GPIO value to write to turn off attached relay
                        else:
                            relay_on = '0' #GPIO value to write to turn on attached relay
                            relay_off = '1' #GPIO value to write to turn off attached relay
                        if dbgLevel == 1:
                            print("zone_controler_id " + str(zone_controler_id) + ", zone_controler_child_id " + str(zone_controler_child_id) + ", zone_controller_state " + str(zone_controller_state) + ", manual_button_override " + str(manual_button_override))
                            print("relay_type_id - " + str(zone_relay_type_id))
                            print("zone_overrun - " + str(zone_overrun) + ", manual_button_override - " + str(manual_button_override) + ", zone_command - " + str(zone_command) + ", zone_status_prev - " + str(zone_status_prev))
            #           if (($manual_button_override == 0) || ($manual_button_override == 1 && $zone_command == 0)) {
                        #process zone relays
                        if zone_relay_type_id == 0:
                            if (manual_button_override == 0 or (manual_button_override == 1 and zone_command == 0)) and (zone_command != zone_status_prev or zone_controller_type == 'MySensor'):
                                #***************************************************************************************
                                # Zone Valve Wired to Raspberry Pi GPIO Section: Zone Valve Connected Raspberry Pi GPIO.
                                #****************************************************************************************
                                if 'GPIO' in zone_controller_type:
                                    if zone_command == 1:
                                        relay_status = relay_on
                                    else:
                                        relay_status = relay_off
                                    if dbgLevel == 1:
                                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: GIOP Relay Status: " + bc.red + str(relay_status) + bc.ENDC + " (" + str(relay_on) + "=On, " + str(relay_off) + "=Off)")
                                    cur.execute(
                                        "UPDATE messages_out SET sent = '0', payload = %s WHERE node_id = %s AND child_id = %s LIMIT 1;",
                                        [str(zone_command), zone_controler_id, zone_controler_child_id],
                                    )
                                    con.commit()  # commit above

                                #***************************************************************************************
                                # Zone Valve Wired over I2C Interface Make sure you have i2c Interface enabled
            	            #****************************************************************************************
                                if 'I2C' in zone_controller_type:
                                    subprocess.call("/var/www/cron/i2c/i2c_relay.py " + zone_controler_id + " " + zone_controler_child_id + " " + str(zone_command), shell=True)
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone: Relay Board: " + zone_controler_id + " Relay No: "  + zone_controler_child_id + " Status: " + str(zone_command))

                                #***************************************************************************************
                                # Zone Valve Wireless Section: MySensors Wireless or MQTT Relay module for your Zone Valve control.
                                #****************************************************************************************
                                if 'MySensor'  in zone_controller_type or 'MQTT' in zone_controller_type:
                                    cur.execute(
                                        "UPDATE `messages_out` set sent = 0, payload = %s  WHERE node_id = %s AND child_id = %s;",
                                        [str(zone_command), zone_controler_id, zone_controler_child_id],
                                    )
                                    con.commit()  # commit above

                                #************************************************************************************
                                # Sonoff Switch Section: Tasmota WiFi Relay module for your Zone control.
                                #*************************************************************************************
                                if 'Tasmota' in zone_controller_type:
                                    cur.execute(
                                        "SELECT * FROM http_messages WHERE zone_id = %s AND message_type = %s LIMIT 1;",
                                        (zone_id, zone_command),
                                    )
                                    if cur.rowcount > 0:
                                        http = cur.fetchone()
                                        http_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                        add_on_msg = http[http_to_index["command"]] + " " + http[http_to_index["parameter"]]
                                        cur.execute(
                                            "UPDATE `messages_out` set sent = 0, payload = %s  WHERE node_id = %s AND child_id = %s;",
                                            [add_on_msg, zone_controler_id, zone_controler_child_id],
                                        )
                                        con.commit()  # commit above

                                        if zone_category != 3:
                                            if zone_override_status == 0:
                                                cur.execute(
                                                    "UPDATE zone_relays SET state = %s, current_state = %s WHERE id = %s LIMIT 1;",
                                                    [zone_command, zone_command, zc_id],
                                                )
                                            con.commit()  # commit above
                                #end if ($manual_button_override ==
                        elif zone_relay_type_id == 5: #end if ($zone_relay_type_id == 0)
                            if not pump_relays_dict: #add first pump type relay
                                pump_relays_dict[controller_relay_id] = zone_command
                            elif controller_relay_id in pump_relays_dict and zone_command == 1:
                                pump_relays_dict[controller_relay_id] = zone_command

                    #end for ($crow = 0; $crow < count($controllers); $crow++)
                #end for ($row = 0; $row < count($zone_commands); $row++)

                #process any pump relays
                if pump_relays_dict:
                    #array_walk($pump_relays, "process_pump_relays");
                    for key in pump_relays_dict:
                        process_pump_relays(key, pump_relays_dict[key])

                #For debug info only
                if dbgLevel == 1:
                    print("zone_log Array and Count")
                    print(zone_log_dict)
                    print(len(zone_log_dict))
                    print("z_state Array and Count")
                    print(z_state_dict)
                    print(len(z_state_dict))
                    print("z_start_cause Array")
                    print(z_start_cause_dict)
                    print("z_stop_cause Array")
                    print(z_start_cause_dict)
                    print("z_expected_end_date_time Array")
                    print(z_expected_end_date_time_dict)
                    print("system_controller Array")
                    print(system_controller_dict)
                    print("controllers Array")
                    print(controllers_dict)
                    print("zone_commands Array")
                    print(zone_commands_dict)
                    print("pump_relays Array")
                    print(pump_relays_dict)

                if sc_stop_datetime is not None:
                    if dbgLevel >= 2:
                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller Switched Off At: " + str(sc_stop_datetime))

                if expected_end_date_time is not None:
            #        pass
                    if dbgLevel >= 2:
                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller Expected End Time: " + str(expected_end_date_time))
                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller ON Time: " + bc.red + str(system_controller_on_time) + bc.ENDC + " seconds")

                #***********************************
                #  System Controller On section
                #***********************************/
                #Search inside array if any value is set to 1 then we need to update db with system controller status
                if 1 in system_controller_dict.values():
                    if system_controller_mode == 1:
                        if hvac_state == 0:
                            on_relay_id = cool_relay_id
                            on_relay_child_id = cool_relay_child_id
                            on_relay_type = cool_relay_type
                            on_relay_on = cool_relay_on
                            on_relay_off = cool_relay_off
                            off_relay_id = heat_relay_id
                            off_relay_child_id = heat_relay_child_id
                            off_relay_type = heat_relay_type
                            off_relay_on = heat_relay_on
                            off_relay_off = heat_relay_off
                        else:
                            on_relay_id = heat_relay_id
                            on_relay_child_id = heat_relay_child_id
                            on_relay_type = heat_relay_type
                            on_relay_on = heat_relay_on
                            on_relay_off = heat_relay_off
                            off_relay_id = cool_relay_id
                            off_relay_child_id = cool_relay_child_id
                            off_relay_type = cool_relay_type
                            off_relay_on = cool_relay_on
                            off_relay_off = cool_relay_off
                    else:
                        on_relay_id = heat_relay_id
                        on_relay_child_id = heat_relay_child_id
                        on_relay_type = heat_relay_type
                        on_relay_on = heat_relay_on
                        on_relay_off = heat_relay_off
                        off_relay_id = heat_relay_id
                        off_relay_child_id = heat_relay_child_id
                        off_relay_type = heat_relay_type
                        off_relay_on = heat_relay_on
                        off_relay_off = heat_relay_off

                    new_system_controller_status = 1
                    #change relay states on change
                    if (system_controller_active_status != new_system_controller_status) or active_sc_mode != sc_mode_prev or off_relay_type == 'MySensor' or on_relay_type == 'MySensor':
                        #update system controller active status to 1
                        cur.execute(
                            "UPDATE system_controller SET sync = '0', active_status = %s, sc_mode_prev = %s WHERE id ='1' LIMIT 1;",
                            [new_system_controller_status, active_sc_mode],
                        )
                        con.commit()  # commit above

                        #************************************************************************************************************
                        # System Controller Wirelss Section:      MySensors Wireless or MQTT Relay module for your System Controller
                        #************************************************************************************************************
                        #update messages_out table with sent status to 0 and payload to as system controller status.
                        if active_sc_mode != 5: #process if NOT  HVAC fan only mode
                            if system_controller_mode == 1 and (off_relay_type == 'MySensor' or off_relay_type == 'MQTT'):
                                cur.execute(
                                    "SELECT node_id FROM nodes WHERE id = %s LIMIT 1;",
                                    (off_relay_id, ),
                                )
                                if cur.rowcount > 0:
                                    node = cur.fetchone()
                                    node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                    node_id = node[node_to_index["node_id"]]
                                    cur.execute(
                                        "UPDATE messages_out SET sent = 0, payload = '0' WHERE node_id = %s AND child_id = %s LIMIT 1;",
                                        [node_id, off_relay_child_id],
                                    )
                                    con.commit()  # commit above
                                    if dbgLevel >= 2:
                                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller Node ID: " + bc.red + str(node_id)+ bc.ENDC + " Child ID: " + bc.red + str(off_relay_child_id) + bc.ENDC)
                            if on_relay_type == 'MySensor' or on_relay_type == 'MQTT':
                                cur.execute(
                                    "SELECT node_id FROM nodes WHERE id = %s LIMIT 1;",
                                    (on_relay_id, ),
                                )
                                if cur.rowcount > 0:
                                    node = cur.fetchone()
                                    node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                    node_id = node[node_to_index["node_id"]]
                                    cur.execute(
                                        "UPDATE messages_out SET sent = 0, payload = '%s' WHERE node_id = %s AND child_id = %s LIMIT 1;",
                                        [new_system_controller_status ,node_id, on_relay_child_id],
                                    )
                                    con.commit()  # commit above
                                    if dbgLevel >= 2:
                                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller Node ID: " + bc.red + str(node_id)+ bc.ENDC + " Child ID: " + bc.red + str(off_relay_child_id) + bc.ENDC)
                        if system_controller_mode == 1 and (fan_relay_type == 'MySensor' or fan_relay_type == 'MQTT'):
                            cur.execute(
                                "SELECT node_id FROM nodes WHERE id = %s LIMIT 1;",
                                (fan_relay_id, ),
                            )
                            if cur.rowcount > 0:
                                node = cur.fetchone()
                                node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                node_id = node[node_to_index["node_id"]]
                                cur.execute(
                                    "UPDATE messages_out SET sent = 0, payload = '%s' WHERE node_id = %s AND child_id = %s LIMIT 1;",
                                    [new_system_controller_status ,node_id, fan_relay_child_id],
                                )
                                con.commit()  # commit above
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller Node ID: " + bc.red + str(node_id)+ bc.ENDC + " Child ID: " + bc.red + str(off_relay_child_id) + bc.ENDC)

                        #******************************************************
                        # System Controller Wired to Raspberry Pi GPIO Section.
                        #******************************************************
                        if active_sc_mode != 5: #process if NOT  HVAC fan only mode
                            if system_controller_mode == 1 and off_relay_type == 'GPIO':
                                cur.execute(
                                    "SELECT node_id FROM nodes WHERE id = %s LIMIT 1;",
                                    (off_relay_id, ),
                                )
                                if cur.rowcount > 0:
                                    node = cur.fetchone()
                                    node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                    node_id = node[node_to_index["node_id"]]
                                    cur.execute(
                                        "UPDATE messages_out SET sent = '0', payload = '0' WHERE node_id = %s AND child_id = %s LIMIT 1;",
                                        [node_id, off_relay_child_id],
                                    )
                                    con.commit()  # commit above
                                    if dbgLevel >= 2:
                                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller GPIO: " + bc.red + str(off_relay_child_id)+ bc.ENDC + " Status: " + bc.red + str(off_relay_off) + bc.ENDC + " (" + str(off_relay_on) + "=On, " + str(off_relay_off) + "=Off)")
                            if on_relay_type == 'GPIO':
                                cur.execute(
                                    "SELECT node_id FROM nodes WHERE id = %s LIMIT 1;",
                                    (on_relay_id, ),
                                )
                                if cur.rowcount > 0:
                                    node = cur.fetchone()
                                    node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                    node_id = node[node_to_index["node_id"]]
                                    cur.execute(
                                        "UPDATE messages_out SET sent = '0', payload = '%s' WHERE node_id = %s AND child_id = %s LIMIT 1;",
                                        [new_system_controller_status, node_id, on_relay_child_id],
                                    )
                                    con.commit()  # commit above
                                    if dbgLevel >= 2:
                                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller GPIO: " + bc.red + str(on_relay_child_id)+ bc.ENDC + " Status: " + bc.red + str(on_relay_on) + bc.ENDC + " (" + str(on_relay_on) + "=On, " + str(on_relay_off) + "=Off)")
                        if system_controller_mode == 1 and fan_relay_type == 'GPIO':
                            cur.execute(
                                "SELECT node_id FROM nodes WHERE id = %s LIMIT 1;",
                                (fan_relay_id, ),
                            )
                            if cur.rowcount > 0:
                                node = cur.fetchone()
                                node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                node_id = node[node_to_index["node_id"]]
                                cur.execute(
                                    "UPDATE messages_out SET sent = '0', payload = '%s' WHERE node_id = %s AND child_id = %s LIMIT 1;",
                                    [new_system_controller_status, node_id, fan_relay_child_id],
                                )
                                con.commit()  # commit above
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller GPIO: " + bc.red + str(fan_relay_child_id)+ bc.ENDC + " Status: " + bc.red + str(fan_relay_on) + bc.ENDC + " (" + str(fan_relay_on) + "=On, " + str(fan_relay_off) + "=Off)")


                        #******************************************************************************************
                        # System Controller Wired over I2C Interface Make sure you have i2c Interface enabled
                        #******************************************************************************************
                        if active_sc_mode != 5: #process if NOT  HVAC fan only mode
                            if system_controller_mode == 1 and off_relay_type == 'I2C':
                                subprocess.call("/var/www/cron/i2c/i2c_relay.py " + off_relay_id + " " + off_relay_child_id + " " + command, shell=True)
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller I2C Rrelay Board: " + bc.red + str(off_relsy_id)+ bc.ENDC + " Relay ID: " + bc.red + str(off_relay_child_id) + bc.ENDC)
                            if on_relay_type == 'I2C':
                                subprocess.call("/var/www/cron/i2c/i2c_relay.py " + on_relay_id + " " + on_relay_child_id + " " + command, shell=True)
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller I2C Rrelay Board: " + bc.red + str(on_relsy_id)+ bc.ENDC + " Relay ID: " + bc.red + str(on_relay_child_id) + bc.ENDC)
                        if system_controller_mode == 1 and fan_relay_type == 'I2C':
                            subprocess.call("/var/www/cron/i2c/i2c_relay.py " + fan_relay_id + " " + fan_relay_child_id + " " + command, shell=True)
                            if dbgLevel >= 2:
                                print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller I2C Rrelay Board: " + bc.red + str(fan_relsy_id)+ bc.ENDC + " Relay ID: " + bc.red + str(fan_relay_child_id) + bc.ENDC)
                    if system_controller_active_status != new_system_controller_status:
                        for key in zone_log_dict:
                            #insert date and time into Log table so we can record system controller start date and time.
                            if zone_log_dict[key] == 1:
                                if z_expected_end_date_time_dict[key] is not None:
                                    qry_str = """INSERT INTO `controller_zone_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`,
                                              `expected_end_date_time`) VALUES ({}, {}, {}, '{}', '{}', {}, {},'{}');""".format(0,0,key,time_stamp.strftime("%Y-%m-%d %H:%M:%S"),start_cause,NULL,NULL,z_expected_end_date_time_dict[key])
                                else:
                                    qry_str = """INSERT INTO `controller_zone_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`,
                                              `expected_end_date_time`) VALUES ({}, {}, {}, '{}', '{}', {}, {},{});""".format(0,0,key,time_stamp.strftime("%Y-%m-%d %H:%M:%S"),start_cause,NULL,NULL,NULL)
                                try:
                                    cur.execute(qry_str)
                                    con.commit()
                                    if dbgLevel >= 2:
                                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone Log table updated Successfully.")
                                except:
                                    if dbgLevel >= 2:
                                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone Log table update failed.")
                        #end foreach($zone_log as $key => $value)

                        #query to set last system controller statues change time based on the latest zone change
                        qry_str = """SELECT `start_datetime`, `start_cause`, `stop_datetime`, MAX(`expected_end_date_time`) AS `expected_end_date_time`
                                  FROM `controller_zone_logs`
                                  WHERE NOT `zone_id` = {} AND `stop_cause` IS NULL
                                  ORDER BY `id` DESC;""".format(system_controller_id)
                        cur.execute(qry_str)
                        if cur.rowcount > 0:
                            logs = cur.fetchone()
                            logs_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                            system_controller_start_datetime = logs[logs_to_index["start_datetime"]]
                            system_controller_start_cause = logs[logs_to_index["start_cause"]]
                            system_controller_expoff_datetime = logs[logs_to_index["expected_end_date_time"]]
                        else:
                            system_controller_start_datetime = None
                            system_controller_start_cause = None
                            system_controller_expoff_datetime = None

                        #insert date and time into system controller log table so we can record system controller start date and time.
                        if system_controller_expoff_datetime is not None:
                            qry_str = """INSERT INTO `controller_zone_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`, `expected_end_date_time`)
                                      VALUES ({}, {}, {}, '{}', '{}', {}, {},'{}');""".format(0,0,system_controller_id,time_stamp.strftime("%Y-%m-%d %H:%M:%S"),start_cause,NULL,NULL,system_controller_expoff_datetime)
                        else:
                            qry_str = """INSERT INTO `controller_zone_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`, `expected_end_date_time`)
                                      VALUES ({}, {}, {}, '{}', '{}', {}, {},{});""".format(0,0,system_controller_id,time_stamp.strftime("%Y-%m-%d %H:%M:%S"),start_cause,NULL,NULL,NULL)
                        try:
                            cur.execute(qry_str)
                            con.commit()
                            if dbgLevel >= 2:
                                print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone Log table updated Successfully.")
                        except:
                            if dbgLevel >= 2:
                                print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone Log table update failed.")
                    else:
                        for key in zone_log_dict:
                            if zone_log_dict[key] != z_state_dict[key]:
                                if zone_log_dict[key] == 0:
                                    qry_str = """UPDATE controller_zone_logs SET stop_datetime = '{}', stop_cause = '{}' WHERE `zone_id` = {} ORDER BY id DESC LIMIT 1;""".format(time_stamp.strftime("%Y-%m-%d %H:%M:%S"), z_stop_cause_dict[key], key)
                                else:
                                    if z_expected_end_date_time_dict[key] is not None:
                                        qry_str = """INSERT INTO `controller_zone_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`,
                                                  `expected_end_date_time`) VALUES ({}, {}, {}, '{}', '{}', {}, {},'{}');""".format(0,0,key,time_stamp.strftime("%Y-%m-%d %H:%M:%S"),z_start_cause_dict[key],NULL,NULL,z_expected_end_date_time_dict[key])
                                    else:
                                        qry_str = """INSERT INTO `controller_zone_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`,
                                                  `expected_end_date_time`) VALUES ({}, {}, {}, '{}', '{}', {}, {},{});""".format(0,0,key,time_stamp.strftime("%Y-%m-%d %H:%M:%S"),z_start_cause_dict[key],NULL,NULL,NULL)
                                try:
                                    cur.execute(qry_str)
                                    con.commit()
                                    if dbgLevel >= 2:
                                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone Log table updated Successfully.")
                                except:
                                    if dbgLevel >= 2:
                                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone Log table update failed.")

                #end system_controller ON section

                #************************************
                #  System Controller Off section
                #************************************
                else:
                    new_system_controller_status = 0
                    #change relay states on change
                    if system_controller_active_status != new_system_controller_status or active_sc_mode != sc_mode_prev or zone_mode_current != zone_mode or zone_mode_current != zone_mode or heat_relay_type == 'MySensor' or cool_relay_type == 'MySensor' or fan_relay_type == 'MySensor':
                        #update system controller active status to 1
                        cur.execute(
                            "UPDATE system_controller SET sync = 0, active_status = %s, sc_mode_prev = %s WHERE id = 1 LIMIT 1;",
                            [new_system_controller_status, active_sc_mode],
                        )
                        con.commit()  # commit above

                        #************************************************************************************************************
                        # System Controller Wirelss Section:      MySensors Wireless or MQTT Relay module for your System Controller
                        #************************************************************************************************************
                        #update messages_out table with sent status to 0 and payload to as system controller status.
                        if heat_relay_type == 'MySensor' or heat_relay_type == 'MQTT':
                            cur.execute(
                                "SELECT node_id FROM nodes WHERE id = %s LIMIT 1;",
                                (heat_relay_id, ),
                            )
                            if cur.rowcount > 0:
                                node = cur.fetchone()
                                node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                node_id = node[node_to_index["node_id"]]
                                cur.execute(
                                    "UPDATE messages_out SET sent = 0, payload = %s WHERE node_id = %s AND child_id = %s LIMIT 1;",
                                    [new_system_controller_status, node_id, heat_relay_child_id],
                                )
                                con.commit()  # commit above
                                if dbgLevel >= 2:
                                     print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller Node ID: " + bc.red + str(node_id)+ bc.ENDC + " Child ID: " + bc.red + str(heat_relay_child_id) + bc.ENDC)
                        if system_controller_mode == 1:
                            #update messages_out table with sent status to 0 and payload to as system controller status.
                            if cool_relay_type == 'MySensor' or cool_relay_type == 'MQTT': # HVAC cool relay OFF
                                cur.execute(
                                    "SELECT node_id FROM nodes WHERE id = %s LIMIT 1;",
                                    (cool_relay_id, ),
                                )
                                if cur.rowcount > 0:
                                    node = cur.fetchone()
                                    node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                    node_id = node[node_to_index["node_id"]]
                                    cur.execute(
                                        "UPDATE messages_out SET sent = 0, payload = '0' WHERE node_id = %s AND child_id = %s LIMIT 1;",
                                        [node_id, cool_relay_child_id],
                                    )
                                    con.commit()  # commit above
                            if fan_relay_type == 'MySensor' or fan_relay_type == 'MQTT': # HVAC cool relay OFF
                                cur.execute(
                                    "SELECT node_id FROM nodes WHERE id = %s LIMIT 1;",
                                    (fan_relay_id, ),
                                )
                                if cur.rowcount > 0:
                                    node = cur.fetchone()
                                    node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                    node_id = node[node_to_index["node_id"]]
                                    if active_sc_mode == 5: # HVAC fan ON if set to fan mode, else turn OFF
                                        cur.execute(
                                            "UPDATE messages_out SET sent = 0, payload = '1' WHERE node_id = %s AND child_id = %s LIMIT 1;",
                                            [node_id, cool_relay_child_id],
                                         )
                                    else:
                                        cur.execute(
                                            "UPDATE messages_out SET sent = 0, payload = '%s' WHERE node_id = %s AND child_id = %s LIMIT 1;",
                                            [new_system_controller_status, node_id, cool_relay_child_id],
                                        )
                                    con.commit()  # commit above
                                    if dbgLevel >= 2:
                                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller Node ID: " + bc.red + str(node_id)+ bc.ENDC + " Child ID: " + bc.red + str(fan_relay_child_id) + bc.ENDC)

                        #******************************************************
                        # System Controller Wired to Raspberry Pi GPIO Section.
                        #******************************************************
                        if heat_relay_type == 'GPIO':
                            cur.execute(
                                "SELECT node_id FROM nodes WHERE id = %s LIMIT 1;",
                                (heat_relay_id, ),
                            )
                            if cur.rowcount > 0:
                                node = cur.fetchone()
                                node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                node_id = node[node_to_index["node_id"]]
                                cur.execute(
                                    "UPDATE messages_out SET sent = 0, payload = '%s' WHERE node_id = %s AND child_id = %s LIMIT 1;",
                                    [new_system_controller_status, node_id, heat_relay_child_id],
                                )
                                con.commit()  # commit above
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller GPIO: " + bc.red + str(heat_relay_child_id)+ bc.ENDC + " Status: " + bc.red + str(heat_relay_off) + bc.ENDC + " (" + str(heat_relay_on) + "=On, " + str(heat_relay_off) + "=Off)")
                        if system_controller_mode == 1:
                            if cool_relay_type == 'GPIO':
                                cur.execute(
                                    "SELECT node_id FROM nodes WHERE id = %s LIMIT 1;",
                                    (cool_relay_id, ),
                                )
                                if cur.rowcount > 0:
                                    node = cur.fetchone()
                                    node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                    node_id = node[node_to_index["node_id"]]
                                    cur.execute(
                                        "UPDATE messages_out SET sent = 0, payload = '0' WHERE node_id = %s AND child_id = %s LIMIT 1;",
                                        [node_id, cool_relay_child_id],
                                    )
                                    con.commit()  # commit above
                                    if dbgLevel >= 2:
                                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller GPIO: " + bc.red + str(cool_relay_child_id)+ bc.ENDC + " Status: " + bc.red + str(cool_relay_off) + bc.ENDC + " (" + str(cool_relay_on) + "=On, " + str(cool_relay_off) + "=Off)")
                            if fan_relay_type == 'GPIO':
                                cur.execute(
                                    "SELECT node_id FROM nodes WHERE id = %s LIMIT 1;",
                                    (fan_relay_id, ),
                                )
                                if cur.rowcount > 0:
                                    node = cur.fetchone()
                                    node_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                    node_id = node[node_to_index["node_id"]]
                                    if active_sc_mode == 5: #HVAC fan ON if set to fan mode, else turn OFF
                                        cur.execute(
                                            "UPDATE messages_out SET sent = 0, payload = '1' WHERE node_id = %s AND child_id = %s LIMIT 1;",
                                            [node_id, cool_relay_child_id],
                                        )
                                    else:
                                        cur.execute(
                                            "UPDATE messages_out SET sent = '0', payload = '%s' WHERE node_id = %s AND child_id = %s LIMIT 1;",
                                            [new_system_controller_status, node_id, heat_relay_child_id],
                                    )
                                    con.commit()  # commit above
                                    if dbgLevel >= 2:
                                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller GPIO: " + bc.red + str(fan_relay_child_id)+ bc.ENDC + " Status: " + bc.red + str(fan_relay_off) + bc.ENDC + " (" + str(fan_relay_on) + "=On, " + str(fan_relay_off) + "=Off)")

                        #******************************************************************************************
                        # System Controller Wired over I2C Interface Make sure you have i2c Interface enabled
                        #******************************************************************************************
                        if heat_relay_type == 'I2C':
                            subprocess.call("/var/www/cron/i2c/i2c_relay.py " + heat_relay_id + " " + heat_relay_child_id + " " + command, shell=True)
                            if dbgLevel >= 2:
                                print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller I2C Rrelay Board: " + bc.red + str(heat_relsy_id)+ bc.ENDC + " Relay ID: " + bc.red + str(heat_relay_child_id) + bc.ENDC)
                        if system_controller_mode == 1:
                            if cool_relay_type == 'I2C': # HVAC cool relay OFF
                                subprocess.call("/var/www/cron/i2c/i2c_relay.py " + cool_relay_id + " " + cool_relay_child_id + " " + command, shell=True)
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller I2C Rrelay Board: " + bc.red + str(cool_relsy_id)+ bc.ENDC + " Relay ID: " + bc.red + str(cool_relay_child_id) + bc.ENDC)
                            if fan_relay_type == 'I2C':
                                if active_sc_mode == 5: # HVAC fan ON if set to fan mode, else turn OFF
                                    subprocess.call("/var/www/cron/i2c/i2c_relay.py " + cool_relay_id + " " + cool_relay_child_id + " " + command, shell=True)
                                else:
                                    subprocess.call("/var/www/cron/i2c/i2c_relay.py " + cool_relay_id + " " + cool_relay_child_id + " " + command, shell=True)
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller I2C Rrelay Board: " + bc.red + str(fan_relsy_id)+ bc.ENDC + " Relay ID: " + bc.red + str(fan_relay_child_id) + bc.ENDC)

                        #Update last record with system controller stop date and time in System Controller Log table.
                        if system_controller_active_status != new_system_controller_status:
                            #zone updates
                            for key in zone_log_dict:
                                if zone_log_dict[key] != z_state_dict[key]:
                                    #use the zone stop_cause for the system controller log record
                                    sc_stop_cause = z_stop_cause_dict[key]
                                    qry_str = """UPDATE controller_zone_logs SET stop_datetime = '{}', stop_cause = '{}' WHERE `zone_id` = {} ORDER BY id DESC LIMIT 1;""".format(time_stamp.strftime("%Y-%m-%d %H:%M:%S"), z_stop_cause_dict[key], key)
                                    try:
                                        cur.execute(qry_str)
                                        con.commit()
                                        if dbgLevel >= 2:
                                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone Log table updated Successfully.")
                                    except:
                                        if dbgLevel >= 2:
                                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Zone Log table update failed.")
                            #system controller update
                            qry_str = """UPDATE controller_zone_logs SET stop_datetime = '{}', stop_cause = '{}' WHERE `zone_id` = {} ORDER BY id DESC LIMIT 1;""".format(time_stamp.strftime("%Y-%m-%d %H:%M:%S"), sc_stop_cause, system_controller_id)
                            try:
                                cur.execute(qry_str)
                                con.commit()
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller Log table updated Successfully.")
                            except:
                                if dbgLevel >= 2:
                                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller Log table update failed.")


                #if HVAC mode get the heat, cool and fan relay on/off state
                if system_controller_mode == 1:
                    if new_system_controller_status == 1:
                        if active_sc_mode != 5:
                            cur.execute(
                                "SELECT name FROM relays WHERE relay_id = %s AND relay_child_id = %s LIMIT 1",
                                (on_relay_id, on_relay_child_id),
                            )
                            if cur.rowcount > 0:
                                relay = cur.fetchone()
                                relay_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                                h_relay = relay[relay_to_index["name"]]
                                if 'Heat' in h_relay:
                                    hvac_relays_state = 0b101
                                else:
                                    hvac_relays_state = 0b011
                        else:
                            hvac_relays_state = 0b001
                    else:
                        if active_sc_mode == 5:
                            hvac_relays_state = 0b001
                        else:
                            hvac_relays_state = 0b000
                    try:
                        cur.execute(
                            "UPDATE system_controller SET hvac_relays_state = %s;",
                            [hvac_relays_state,],
                            )
                        con.commit()  # commit above
                        if dbgLevel >= 2:
                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller HVAC Relay State updated Successfully.")
                    except:
                        if dbgLevel >= 2:
                             print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller HVAC Relay State update failed..")
                    if dbgLevel == 1:
                        if dbgLevel >= 2:
                            print("hvac_state - " + str(hvac_state))
                            print("hvac_relays_state - "+ str(hvac_relays_state))
                            if hvac_relays_state != 0:
                                print("On Relay Name - " + str(h_relay['name']))

                #********************************************************************************************************************************************************************
                #Following section is Optional for States collection

                if dbgLevel >= 2:
                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller Active Status: " + bc.red + str(new_system_controller_status) + bc.ENDC)
                if system_controller_mode == 0:
                    if dbgLevel >= 2:
                        print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - System Controller Hysteresis Status: " + bc.red + str(hysteresis) + bc.ENDC)
                if dbgLevel >= 2:
                    print("-" * line_len)
                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Purging Marked Records.")
                #delete records where purge is set to 1
                qry_tuple = ('DELETE FROM boost WHERE `purge`= 1 LIMIT 1;',
                            'DELETE FROM override WHERE `purge`= 1  LIMIT 1;',
                            'DELETE FROM schedule_daily_time_zone WHERE `purge`= 1;',
                            'DELETE FROM schedule_night_climat_zone WHERE `purge`= 1;',
                            'DELETE FROM controller_zone_logs WHERE `purge`= 1;',
                            'DELETE FROM zone_sensors WHERE `purge`= 1;',
                            'DELETE FROM zone_relays WHERE `purge`= 1;',
                            'DELETE FROM livetemp WHERE `purge`= 1 LIMIT 1;',
                            'DELETE FROM zone WHERE `purge`= 1 LIMIT 1;',
                            'DELETE FROM schedule_daily_time_zone WHERE `purge`= 1;',
                            'DELETE FROM holidays WHERE `purge`= 1;',
                            'DELETE FROM schedule_daily_time WHERE `purge`= 1;')
                for q in qry_tuple:
                    try:
                        cur.execute(q)
                        con.commit()
                        if dbgLevel == 1:
                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Query '" + q + "' Successful.")
                    except:
                        if dbgLevel == 1:
                            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Query '" + q + " 'Failed.")

                if dbgLevel >= 1:
                    print("-" * line_len)

            #no zones to process
            else:
                if dbgLevel >= 1:
                    print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - NO Zones to Process.")
                    print("-" * line_len)

            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Controller Scan Ended")
            print(bc.grn + "*" * line_len + bc.ENDC)
            if dbgLevel >= 1:
                time.sleep(10)
            else:
                time.sleep(1)
        else: # system in test mode
            print(bc.dtm + script_run_time(script_start_timestamp, int_time_stamp) + bc.ENDC + " - Test Mode, Controller Suspended")
            cur.execute(
                "SELECT test_mode FROM system LIMIT 1",
            )
            if cur.rowcount > 0:
                test = cur.fetchone()
                test_to_index = dict((d[0], i) for i, d in enumerate(cur.description))
                test_mode = test[test_to_index["test_mode"]]
                if test_mode == 1:
                    cur.execute(
                        "UPDATE system SET test_mode = 2;",
                    )
                    con.commit()  # commit above
            time.sleep(10)

except:
    print(traceback.format_exc())
finally:
    con.close()
    sys.exit(1)
