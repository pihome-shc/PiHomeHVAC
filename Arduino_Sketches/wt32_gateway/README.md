# Initial Release of Gateway sketch for WT32-ETH01 wireless-tag board.

### Implements WirelessManager.
### Can be configured for NRF24L01, or RFM69 or RFM95 wireless modules.
### Jumper for Ethernet only mode.
### Jumper to clear EEPROM of saved wireless credentials.
### Connection can be either WiFi or Ethernet.
### Pin Mapping
Currently there is no Arduino IDE option for the board and in order to implement the SPi connection to the radio module the file pins_arduino.h needs to be customized.
Copy the file pins_arduino.h to the 'Windows' directory C:\Users\xxxxxxxxxxx\Documents\ArduinoData\packages\esp32\hardware\esp32\1.0.6\variants\esp32
