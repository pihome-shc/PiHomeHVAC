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

Kernel IP routing table
Destination     Gateway         Genmask         Flags Metric Ref    Use Iface
0.0.0.0         192.168.0.1     0.0.0.0         UG    600    0        0 wlan0
0.0.0.0         10.0.0.1        0.0.0.0         UG    700    0        0 eth0
10.0.0.0        0.0.0.0         255.255.255.0   U     700    0        0 eth0
169.254.0.0     0.0.0.0         255.255.0.0     U     1000   0        0 eth0
192.168.0.0     0.0.0.0         255.255.255.0   U     600    0        0 wlan0
