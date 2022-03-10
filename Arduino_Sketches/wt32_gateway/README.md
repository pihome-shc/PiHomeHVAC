# Initial Release of Gateway sketch for WT32-ETH01 wireless-tag board.

### Implements WirelessManager.
### Can be configured for NRF24L01, or RFM69 or RFM95 wireless modules.
### Jumper for Ethernet only mode.
### Jumper to clear EEPROM of saved wireless credentials.
### Connection can be either WiFi or Ethernet.
### Pin Mapping
Currently there is no Arduino IDE option for the board and in order to implement the SPi connection to the radio module the file pins_arduino.h needs to be customized.
Copy the file pins_arduino.h to the 'Windows' directory C:\Users\xxxxxxxxxxx\Documents\ArduinoData\packages\esp32\hardware\esp32\1.0.6\variants\esp32
### Pin Usage
#### IO2  - Program Select/Radio MOSI
#### IO4  - Radio CE
#### IO5  - RX
#### IO12 - Radio MISO
#### IO14 - Radio SCK
#### IO15 - Radio SS
#### IO17 - ERR
#### IO33 - TX
#### IO35 - ETH Only
#### IO36 - Clear WiFi Creditional
#### IO39 - Radio IRQ
#### 5V   - 5volt Power In
#### 3V3  - 3.3volt power to Radio Module
#### GND  - Common Ground
