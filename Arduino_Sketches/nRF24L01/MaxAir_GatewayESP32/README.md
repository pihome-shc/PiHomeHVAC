# Sketch for ESP8266 Gateway Board with WEMOS D1 Mini ESP32 Adapter.

**Implements WirelessManager.**

**Can be configured for NRF24L01, or RFM69 or RFM95 wireless modules.**

**Jumper to Clear WiFi Credentials.**

**Jumper to select NRF Channel 74, rather than the default of Channel 91.**

**Enables the use of the existing 0n-board ERR, TX and TX LEDs.**

**Compiled using version 1.0.6 esp32 by Espressif Systems (will not compile with version 2.0.x).**

### Pin Usage

| Pin Name      | Function                                                     |
| ------------- | ------------------------------------------------------------ |
| RSR           |                                                              |
| SVP           |                                                              |
| IO26          | Clear WiFi Settings Jumper (10K Pullup Resistor to 3V3)      |
| IO18          | Radio SCK                                                    |
| IO19          | Radio MISO                                                   |
| IO23          | Radio MOSI                                                   |
| IO05          | Radio SS                                                     |
| 3.3V          | 3.3volt Power Out to Radio                                   |
| TCK(IO13)     | Set NRF Radio Channel 75 Jumper (10K Pullup Resistor to 3V3) |
| CMD           |                                                              |
| TXD           | Serial Out                                                   |
| RXD           | Serial In                                                    |
| IO22          | Radio CE (10K Pullup Resistor to 3V3)                        |
| IO21          | ERR LED                                                      |
| IO17          | TX LED                                                       |
| IO16          | ERR LED                                                      |
| GND           | Common Ground                                                |
| 5V            | 5volt Power Input                                            |
| TD0           |                                                              |
| GDD           |                                                              |

**Install support for ESP32 boards by using Addional Boards Manager URL: https://dl.espressif.com/dl/package_esp32_index.json and searching for esp32**

**Board type to be selected is 'WEMOS D1 MINI ESP32'**

**The sketch uses WiFiManager and requires a change to the file MyGatewayTransportEthernet.cpp.**

**Modify the file ...\Documents\Arduino\libraries\MySensors\core\MyGatewayTransportEthernet.cpp as per the instructions at https://www.pihome.eu/2021/10/06/wifimanager-with-mysensors/**

**Programing mode:**

![esp32_wemos](https://github.com/twa127/PiHomeHVAC/assets/46624596/7a7ac8b4-c082-4dcf-b571-a032bd5e3c91)
