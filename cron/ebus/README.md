# eBUS

eBUS (energy bus) is a 2-wire digital serial data-bus communication interface used in heating and solar energy appliances, by mainly German manufacturers. It was originally proposed by the Karl Dungs company, and has since been adopted by several other manufacturers. The eBUS interface has also been used by home-automation enthusiasts to connect their domestic solar or heating system to a networked PC for monitoring or remote control.

The eBUS 2-wire interface is an asynchronous [serial port](https://en.wikipedia.org/wiki/Serial_port) with active-low voltage that exchanges 8-bit bytes with start and (single) stop bits (no parity bit), at a symbol rate of 2400 [baud](https://en.wikipedia.org/wiki/Baud), and can be implemented with a standard [UART](https://en.wikipedia.org/wiki/UART) plus a voltage converter. It differs from the [RS-232](https://en.wikipedia.org/wiki/RS-232) interface, from which it is derived, in that the voltage levels were chosen to allow the bus also to supply power to bus participants, that can use a voltage stabilizer to derive an internal 5 V supply:

logical 0 = 9–12 volt

logical 1 = 15–24 volt (typical: 20 V)

MaxAir is interfaced to eBUS using 'ebusd', see [john30/ebusd: daemon for communication with eBUS heating systems (github.com)](https://github.com/john30/ebusd)

## ebus.py

The 'ebus.py' Python script is used to capture eBUS values and populate the MaxAir 'messages_in' queue. It :

1. Reads table 'ebus_messages' to identify sensors linked to eBUS commands and their associated commands.
2. Sends each command to 'ebusd' using 'ebusctl read -f command' and captures the response message.
3. Process the response message to capture the the first item in any multi-part response and convert any text response to a numeric value, e.g 'off' will be converted to 0 and 'on' to 1.
4. Add any required offset value to the response value.
5. Add the response value to the 'messages_in' queue.
6. Update the associated 'nodes' table 'last_seen' field.
7. Check if the associated sensor is to be graphed and if so add the response value to the 'sensor_graphs' table. 

## ebus_readall.sh
This shell script can be used to read all the values for a selected device ID, e.g

./readall.sh **bai**  (reads data from Vailant boiler)
