# Sketch for ESP8266 Gateway Board with WEMOS D1 Mini ESP32 Adapter.

**Implements WirelessManager.**

**Can be configured for NRF24L01, or RFM69 or RFM95 wireless modules.**

**Jumper to Clear WiFi Credentials.**

**Jumper to select NRF Channel 74, rather than the default of Channel 91.**

**Compiled using version 1.0.6 esp32 by Espressif Systems (will not compile with version 2.0.x).**

### Pin Usage

| Pin Name      | Function                                                     |
| ------------- | ------------------------------------------------------------ |
| TX0           | Serial OUT                                                   |
| RX0 (IO3)     | Serial IN and i2c SCL                                        |
| IO0           | Program Select Jumper (10K Pullup Resistor to 3V3)           |
| IO2           | Radio MOSI                                                   |
| IO4           | Radio CE                                                     |
| TXD (IO5)     | Set Relay Trigger HIGH or LOW (10K Pullup Resistor to 3V3)   |
| IO12          | Radio MISO                                                   |
| IO14          | Radio SCK                                                    |
| IO15          | Radio SS                                                     |
| RXD (IO17)    | RX LED                                                       |
| CFG (IO32)    | Clear WiFi Credentials Jumper (10K Pullup Resistor to 3V3) and i2c SDA |
| 485_EN (IO33) | TX LED                                                       |
| IO35          | ERR LED                                                      |
| IO36          | Radio IRQ (10K Pullup Resistor to 3V3)                       |
| IO39          | Used as ADC, Disable WiFi (low), Disable Ethernet (High)     |
| 5V            | 5volt Power Input                                            |
| 3V3           | 3.3volt Power Out to Radio                                   |
| GND           | Common Ground                                                |

**Install support for ESP32 boards by using Addional Boards Manager URL: https://dl.espressif.com/dl/package_esp32_index.json and searching for esp32**

**Board type to be selected is 'WEMOS D1 MINI ESP32'**

**The sketch uses WiFiManager and requires a change to the file MyGatewayTransportEthernet.cpp.**

**Modify the file ...\Documents\Arduino\libraries\MySensors\core\MyGatewayTransportEthernet.cpp as per the instructions at https://www.pihome.eu/2021/10/06/wifimanager-with-mysensors/**

**Programing mode:**

![prog_wt32](https://user-images.githubusercontent.com/46624596/165151005-1c7dc885-25be-42cb-b770-7853ee7b7912.JPG)

