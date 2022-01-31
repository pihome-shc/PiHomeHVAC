# echo
For controlling local devices with the Amazon Echo.

Instructions for installation and usage see https://github.com/n8henrie/fauxmo

Credit to Nathan Henrie (https://n8henrie.com)


## Quick Start

1. Execute the installer using - 'sh install_echo.sh'
2. Tell Echo, "discover my devices"
3. Use Echo's "zone name on" and "zone name off" to set the BOOST on or off as required
4. Use the Alexa app to edit zone names as required

## Network Priority

If using a system with multiple network interfaces eg wlan0 and eth0, then the network with the highest priority must be that which the Amazon Echo device is connected.
For example it the Amazon Echo is using the Wifi network which is connected to the wlan0 of the device running Fauxmo, then wlan0 must have an higher priority than the eth0 interface. The priority can be shown using the 'route -n' Linux command, eg

[route.txt](https://github.com/twa127/PiHomeHVAC/files/7970804/route.txt)
