#!/usr/bin/python3
class bc:
    hed = '\033[95m'
    dtm = '\033[0;36;40m'
    ENDC = '\033[0m'
    SUB = '\033[3;30;45m'
    WARN = '\033[0;31;40m'
    grn = '\033[0;32;40m'
    wht = '\033[0;37;40m'
    ylw = '\033[93m'
    fail = '\033[91m'
    blu = '\033[36m'


print(bc.hed + " ")
print("    __  __                             _         ")
print("   |  \/  |                    /\     (_)        ")
print("   | \  / |   __ _  __  __    /  \     _   _ __  ")
print("   | |\/| |  / _` | \ \/ /   / /\ \   | | | '__| ")
print("   | |  | | | (_| |  >  <   / ____ \  | | | |    ")
print("   |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|    ")
print(" ")
print("             " +bc.SUB + "S M A R T   THERMOSTAT " + bc.ENDC)
print("********************************************************")
print("*  Script to read data from an eBUS Boiler interface   *")
print("*             and store in message_in queue.           *")
print("*                Build Date: 05/08/2022                *")
print("*      Version 0.02 - Last Modified 19/12/2022         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" ")
print(" " + bc.ENDC)

import time
from datetime import datetime
import string
import os
import sys
import MySQLdb
import schedule

try:
    from configparser import ConfigParser
except ImportError:
    from configparser import ConfigParser

# *****************************************************************************
# write the command message to ebusd and capture response
# *****************************************************************************
def Transact(command):

    global symbol_error_count
    global sync_error_count
    global element_error_count
    global timeout_error_count
    global nosignal_error_count
    global error_count
    global EBUS_Counter

    # Check if EBus is connected
    ebusd_status = os.system('systemctl is-active --quiet ebusd')
    if ebusd_status == 0:
       counter = 0
       response = os.popen('ebusctl read -f ' + command).read()
       while response.find('ERR:') == 1 and counter < 10:
          time.sleep(0.1)
          response = os.popen('ebusctl read -f ' + command).read()
          counter = counter + 1
       if response.find('ERR: wrong symbol received') != -1:
          symbol_error_count = symbol_error_count + 1
          fault = 1
          response = ''
          EBUS_Counter = EBUS_Counter + 1
          return [fault, response]
       elif response.find('ERR: SYN received') != -1:
          sync_error_count = sync_error_count + 1
          fault = 2
          response = ''
          EBUS_Counter = EBUS_Counter + 1
          return [fault, response]
       elif response.find('ERR: element not found') != -1:
          element_error_count = element_error_count + 1
          fault = 3
          response = ''
          EBUS_Counter = EBUS_Counter + 1
          return [fault, response]
       elif response.find('ERR: read timeout') != -1:
          timeout_error_count = timeout_error_count + 1
          fault = 4
          response = ''
          EBUS_Counter = EBUS_Counter + 1
          return [fault, response]
       elif response.find('ERR: no signal') != -1:
          nosignal_error_count = nosignal_error_count + 1
          fault = 5
          response = ''
          EBUS_Counter = EBUS_Counter + 1
          return [fault, response]
       elif response.find('ERR:') != -1:
          error_count = error_count + 1
          fault = 6
          response = ''
          EBUS_Counter = EBUS_Counter + 1
          return [fault, response]
       else:
          fault = 0
          return [fault, response]
    else:
       print(bc.blu + (datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - eBUS Daemon Connection Lost")
       print("------------------------------------------------------------------")
       quit()

########## Initialise the database access varables ##########
config = ConfigParser()
config.read('/var/www/st_inc/db_config.ini')
servername = config.get('db', 'hostname')
username = config.get('db', 'dbusername')
password = config.get('db', 'dbpassword')
dbname = config.get('db', 'dbname')
#############################################################

# Initialise variables
# ********************
EBUS_Counter = 0
symbol_error_count = 0
sync_error_count = 0
element_error_count = 0
timeout_error_count = 0
nosignal_error_count = 0
error_count = 0

# Initialise  database connection string
# **************************************
cnx = MySQLdb.connect(host=servername, user=username, passwd=password, db=dbname)

last_readings = dict()
last_date_time = dict()

# =================================== MAIN BOILER PROCESS ==================================
def boiler():

      global last_readings
      global last_date_time

      cnx = MySQLdb.connect(host=servername, user=username, passwd=password, db=dbname)

      cursorselect = cnx.cursor()
      cursorselect.execute('SELECT ebus_messages.*, sensors.sensor_type_id FROM ebus_messages, sensors WHERE sensors.id = ebus_messages.sensor_id;')
      ebus_message_to_index = dict(
         (d[0], i) for i, d in enumerate(cursorselect.description)
      )
      for msg in cursorselect.fetchall():
         message = msg[ebus_message_to_index["message"]]
         position = msg[ebus_message_to_index["position"]]
         offset = int(msg[ebus_message_to_index["offset"]])
         sensors_id = msg[ebus_message_to_index["sensor_id"]]
         sensors_type_id = msg[ebus_message_to_index["sensor_type_id"]]
         cursorselect.execute('SELECT * FROM sensors WHERE id = (%s)', (sensors_id, ))
         sensor_to_index = dict(
            (d[0], i) for i, d in enumerate(cursorselect.description)
         )
         result = cursorselect.fetchone()
         if cursorselect.rowcount > 0 :
            id = int(result[sensor_to_index["id"]])
            sensor_id = int(result[sensor_to_index["sensor_id"]])
            sensor_child_id = int(result[sensor_to_index["sensor_child_id"]])
            sensor_name = result[sensor_to_index["name"]]
            sensor_type_id = int(result[sensor_to_index["sensor_type_id"]])
            graph_num = int(result[sensor_to_index["graph_num"]])
            msg_in = result[sensor_to_index["message_in"]]
            mode = result[sensor_to_index["mode"]]
            sensor_timeout = int(result[sensor_to_index["timeout"]])*60
            correction_factor = int(result[sensor_to_index["correction_factor"]])
            resolution = float(result[sensor_to_index["resolution"]])
            cursorselect.execute('SELECT node_id FROM nodes WHERE id = (%s)', (sensor_id, ))
            result = cursorselect.fetchone()
            if cursorselect.rowcount > 0 :
               node_id = int(result[0])

         no_reading = True
         status = Transact(message)
         if status[0] == 0 :
            response = status[1]
            if len(response) > 0:
               no_reading = False
               if ";" in response:
                  response = response.split(";")[0]
               elif " " in response:
                  response = response.split(" ")[0]
               elif "off" in response:
                  response = 0
               elif "on" in response:
                  response = 1
               else:
                  response = response.rstrip()
               response = float(response) +  offset
         else :
            fault = 1

         timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
         date_time = datetime.now()
         tdelta = 0
         if no_reading :
            print(bc.blu + timestamp + bc.wht + " - " + message + " - No Response Message")
         else :
            # Update messages_in if required
            # process NOT a temperature sensor
            if sensor_type_id > 2 :
               if last_readings[message] != response :
                  print(bc.blu + timestamp + bc.wht + " - " + message + " - " + str(response))
                  try :
                     if msg_in == 1:
                         cursorinsert = cnx.cursor()
                         cursorinsert.execute('INSERT INTO messages_in(`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `payload`) VALUES(%s,%s,%s,%s,%s,%s)', (0,0,node_id,sensor_child_id,position,response))
                         cnx.commit()
                         cursorinsert.close()
                     cursorupdate = cnx.cursor()
                     if position == 0:
                         qry_str = "UPDATE `sensors` SET `current_val_1`  = {}, `last_seen`  = {} WHERE `sensor_id` = {} AND `sensor_child_id` = {} LIMIT 1;".format(response, datetime.now().strftime("%Y-%m-%d %H:%M:%S"), sensor_id, sensor_child_id)
                     else:
                         qry_str = "UPDATE `sensors` SET `current_val_2`  = {}, `last_seen`  = {} WHERE `sensor_id` = {} AND `sensor_child_id` = {} LIMIT 1;".format(response, datetime.now().strftime("%Y-%m-%d %H:%M:%S"), sensor_id, sensor_child_id)
                     cursorupdate.execute(qry_str)
                     cnx.commit()
                     cursorupdate.close()
                     last_readings[message] = response
                     last_date_time[message] = date_time
                  except :
                     pass
                  try :
                     cursorupdate = cnx.cursor()
                     cursorupdate.execute(
                        "UPDATE `nodes` SET `last_seen`=%s, `sync`=0 WHERE id = %s",
                        [timestamp, sensor_id],
                     )
                     cursorupdate.close()
                     cnx.commit()
                  except :
                     pass
               else :
                  print(bc.blu + timestamp + bc.wht + " - " + message + " - No Change")
            else:
               # process temperature sensor
               response = response + correction_factor
               if mode == 1:
                  tdelta = (datetime.now() - last_date_time[message]).total_seconds()
               if mode == 0 or (mode == 1 and (response < last_readings[message] - resolution or response > last_readings[message] + resolution) or tdelta > sensor_timeout):
                  print(bc.blu + timestamp + bc.wht + " - " + message + " - " + str(response))
                  try :
                     if msg_in == 1:
                         cursorinsert = cnx.cursor()
                         cursorinsert.execute('INSERT INTO messages_in(`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `payload`) VALUES(%s,%s,%s,%s,%s,%s)', (0,0,node_id,sensor_child_id,position,response))
                         cnx.commit()
                         cursorinsert.close()
                     cursorupdate = cnx.cursor()
                     if position == 0:
                         qry_str = "UPDATE `sensors` SET `current_val_1`  = {}, `last_seen`  = {} WHERE `sensor_id` = {} AND `sensor_child_id` = {} LIMIT 1;".format(response, datetime.now().strftime("%Y-%m-%d %H:%M:%S"), sensor_id, sensor_child_id)
                     else:
                         qry_str = "UPDATE `sensors` SET `current_val_2`  = {}, `last_seen`  = {} WHERE `sensor_id` = {} AND `sensor_child_id` = {} LIMIT 1;".format(response, datetime.now().strftime("%Y-%m-%d %H:%M:%S"), sensor_id, sensor_child_id)
                     cursorupdate.execute(qry_str)
                     cnx.commit()
                     cursorupdate.close()
                  except :
                     pass
                  try :
                     cursorupdate = cnx.cursor()
                     cursorupdate.execute(
                        "UPDATE `nodes` SET `last_seen`=%s, `sync`=0 WHERE id = %s",
                        [timestamp, sensor_id],
                     )
                     cursorupdate.close()
                     cnx.commit()
                  except :
                     pass

                  if msg_in ==1 and graph_num > 0 :
                     try :
                        cursorinsert = cnx.cursor()
                        cursorinsert.execute('INSERT INTO sensor_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`,`child_id`, `sub_type`, `payload`, `datetime`)VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)', (0,0,id,sensor_name,"Sensor",0,node_id,sensor_child_id,0,response,timestamp))
                        cursorinsert.close()
                        cnx.commit()
                        cursordelete = cnx.cursor()
                        cursordelete.execute('DELETE FROM sensor_graphs WHERE node_id = (%s) AND child_id = (%s) AND datetime < CURRENT_TIMESTAMP - INTERVAL 24 HOUR;',(node_id, sensor_child_id))
                        cursordelete.close()
                        cnx.commit()
                     except :
                        pass
                  last_readings[message] = response
               else :
                  print(bc.blu + timestamp + bc.wht + " - " + message + " - No Change")

               last_date_time[message] = date_time

      #  Display Current Number of Faults
      if sync_error_count > 0 :
         print(bc.blu + (datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Number of Sync Faults is %d" % sync_error_count)
      if timeout_error_count > 0 :
         print(bc.blu + (datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Number of Timeout Faults is %d" % timeout_error_count)
      if symbol_error_count > 0 :
         print(bc.blu + (datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Number of Symbol Faults is %d" % symbol_error_count)
      if element_error_count > 0 :
         print(bc.blu + (datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Number of Element Not Found Faults is %d" % element_error_count)
      if nosignal_error_count > 0 :
         print(bc.blu + (datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Number of No Signal Faults is %d" % nosignal_error_count)
      if error_count > 0 :
         print(bc.blu + (datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - Number of unknown Faults is %d" % error_count)

def main() :
   print(bc.blu + (datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - eBUS Data Capture Script Started")
   print("------------------------------------------------------------------")

   # *************
   #  Initialise
   # *************

   # Check if EBus is connected
   ebusd_status = os.system('systemctl is-active --quiet ebusd')
   if ebusd_status == 0:
      cursorselect = cnx.cursor()

      # Clear the last message seen dictionary to -1, for first pass through initialisation
      cursorselect = cnx.cursor()
      cursorselect.execute('SELECT * FROM ebus_messages;')
      ebus_message_to_index = dict(
         (d[0], i) for i, d in enumerate(cursorselect.description)
      )
      for msg in cursorselect.fetchall():
         message = msg[ebus_message_to_index["message"]]
         last_readings[message] = -1
         last_date_time[message] = datetime.now()

      cursorselect.close()
      cnx.close()

      # Schedule boiler function to run every 15 seconds
      schedule.every(15).seconds.do(boiler)

      # ***************************************
      # Start scheduler and run every 1 second
      # ***************************************
      while True:
        # Checks whether a scheduled task
        # is pending to run or not
        schedule.run_pending()
        time.sleep(1)
   else :
      print(bc.blu + (datetime.now().strftime("%Y-%m-%d %H:%M:%S")) + bc.wht + " - eBUS Daemon is NOT running")
      print("------------------------------------------------------------------")


if __name__=="__main__":
   main()
