# Initial Release of Gateway sketch for WT32-ETH01 wireless-tag board.

**Implements WirelessManager.**

**Can be configured for NRF24L01, or RFM69 or RFM95 wireless modules.**

**Jumper for Ethernet only mode.**

**Jumper to clear EEPROM of saved wireless credentials.**

**Connection can be either WiFi or Ethernet.**

### Pin Mapping
**Currently there is no Arduino IDE option for the board and in order to implement the SPi connection to the radio module the file pins_arduino.h needs to be customized.**
**Copy the file pins_arduino.h to the 'Windows' directory C:\Users\xxxxxxxxxxx\Documents\ArduinoData\packages\esp32\hardware\esp32\1.0.6\variants\esp32**

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
| IO35          | Ethernet Only Jumper (10K Pullup Resistor to 3V3)            |
| IO36          | Clear WiFi Credentials Jumper (10K Pullup Resistor to 3V3)   |
| IO39          | Radio IRQ (10K Pullup Resistor to 3V3)                       |
| 5V            | 5volt Power Input                                            |
| 3V3           | 3.3volt Power Out to Radio                                   |
| GND           | Common Ground                                                |