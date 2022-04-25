# Initial Release of Gateway sketch for WT32-ETH01 wireless-tag board.

**Implements WirelessManager.**

**Can be configured for NRF24L01, or RFM69 or RFM95 wireless modules.**

**Jumper for WiFi only mode.**

**Jumper for Ethernet only mode.**

**Jumper to clear EEPROM of saved wireless credentials.**

**Connection can be either WiFi or Ethernet or both.**

**Compiled as ESP32 Dev Module.**

### Pin Usage

| Pin Name      | Function                                                     |
| ------------- | ------------------------------------------------------------ |
| TX0           | Serial OUT                                                   |
| RX0           | Serial IN                                                    |
| IO0           | Program Select Jumper (10K Pullup Resistor to 3V3)           |
| IO2           | Radio MOSI                                                   |
| IO4           | Radio CE                                                     |
| TXD (IO5)     | RX LED                                                       |
| IO12          | Radio MISO                                                   |
| IO14          | Radio SCK                                                    |
| IO15          | Radio SS                                                     |
| RXD (IO17)    | ERR LED                                                      |
| CFG (IO32)    | Disable Ethernet Interface Jumper (10K Pullup Resistor to 3V3) |
| 485_EN (IO33) | TX LED                                                       |
| IO35          | Disable WiFi Interface Jumper (10K Pullup Resistor to 3V3)   |
| IO36          | Radio IRQ (10K Pullup Resistor to 3V3)                       |
| IO39          | Clear WiFi Credentials Jumper (10K Pullup Resistor to 3V3)   |
| 5V            | 5volt Power Input                                            |
| 3V3           | 3.3volt Power Out to Radio                                   |
| GND           | Common Ground                                                |

**Install support for ESP32 boards by using Addional Boards Manager URL: https://dl.espressif.com/dl/package_esp32_index.json and searching for esp32**

**Board type to be selected is 'ESP32 Dev Module'**

**The sketch uses WiFiManager and WebServer_WT32_ETH01 libraries and requires a change to the file MyGatewayTransportEthernet.cpp.**

**In the Arduino IDE install the ZIP library WebServer_WT32_ETH01.zip**

**Copy the folder WiFiManager to ...\Documents\Arduino\libraries**

**Modify the file ...\Documents\Arduino\libraries\MySensors\core\MyGatewayTransportEthernet.cpp as per the instructions at https://www.pihome.eu/2021/10/06/wifimanager-with-mysensors/**

**Programing mode:**
![node2](https://user-images.githubusercontent.com/46624596/165150860-83dca036-2a0b-4fa3-9599-fd0ba9f42fa2.JPG)

