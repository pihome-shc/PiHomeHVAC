# EMS Bus

Experimental support for control of Worcester Bosch type boilers using EMS 2-wire bus.

In order to send and receive commands to and from the serial interface a 'stub' is required:
  
1. Compile using - sudo gcc emsctl_2.c -o emsctl
2. Make executable using - sudo chmod +x emsctl
3. Make available using - sudo cp emsctl /usr/bin

## RC10 Hardware Adapter

1. The RC10 Adapter uses a WeMos ESP32 to emulate a RC10 type thermostat control.
2. It connects to the EMS bus using the serial interface of the ESP32, operating at 9600 baud
3. The second serial interface of the ESP32 is connected to the Raspberry Pi serial interface operating at 115200 baud.
4. Commands are sent to the adapter in the format 'emsctl command r' to read a value or 'emsctl command w value'.

## rc10_control.py

The 'rc10_control.py' Python script is used to capture EMS bus values and populate the MaxAir 'messages_in' queue. It :

1. Reads table 'ebus_messages' to identify sensors linked to EMS commands and their associated commands.
2. Sends each command using 'emsctl r command' and captures the response message.
3. Process the response message to capture the the first item in any multi-part response and convert any text response to a numeric value, e.g 'off' will be converted to 0 and 'on' to 1.
4. Add any required offset value to the response value.
5. Add the response value to the 'messages_in' queue.
6. Update the associated 'nodes' table 'last_seen' field.
7. Check if the associated sensor is to be graphed and if so add the response value to the 'sensor_graphs' table. 
