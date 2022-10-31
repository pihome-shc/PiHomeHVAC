# Version 2 PCB Gateway sketch for WT32-ETH01 wireless-tag board.

**Implements WirelessManager.**

**Can be configured for NRF24L01, or RFM69 or RFM95 wireless modules.**

**Jumper for WiFi only mode.**

**Jumper for Ethernet only mode.**

**Jumper to clear EEPROM of saved wireless credentials.**

**Connection can be either WiFi or Ethernet or both.**

**Jumper for Relay Module trigger LOW (no jumper) or HIGH (jumper). This is used both to set the relays to the OFF state on powerup and if the connection to the MaxAir gateway script is lost. Most relay modules use a LOW logic level to turn the relays ON, in this case no jumper is required.**

**NOTE: this board responds to the trigger level defined in the MaxAir Relays configuration..**

**Compiled as ESP32 Dev Module.**

**Can be used with Version 1 PCBs and Version 2 PCBs without the PCF8575 i2c I/O Expander by setting environment variables within the sketch.**

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

**Board type to be selected is 'ESP32 Dev Module'**

**The sketch uses WiFiManager and WebServer_WT32_ETH01 libraries and requires a change to the file MyGatewayTransportEthernet.cpp.**

**In the Arduino IDE install the ZIP library WebServer_WT32_ETH01.zip**

**Copy the folder WiFiManager to ...\Documents\Arduino\libraries**

**Modify the file ...\Documents\Arduino\libraries\MySensors\core\MyGatewayTransportEthernet.cpp as per the instructions at https://www.pihome.eu/2021/10/06/wifimanager-with-mysensors/**

**In the Arduino IDE use 'Library Manager' to install the Library PCF8575 by Renzo Mischianti OR in the Arduino IDE use 'Add ZIP Library to installed the included PCF8575_library-master.zip file.**

**Programing mode:**

![prog_wt32](https://user-images.githubusercontent.com/46624596/165151005-1c7dc885-25be-42cb-b770-7853ee7b7912.JPG)

