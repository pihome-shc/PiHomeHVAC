# Autohotspot

This code is based on the work of RaspberryConnect.com, thank you for this guide and code.

The installer has been modified to add support for the Armbian OS and to allow integartion with MaxAir

For systems running the NetworkManager service (Armbian) which supports Access Point operation natively, then a HotSpot connection is setup directly using NetworkManager. For Raspian based systems (Raspberry Pi), then Hostapd, dnsmasq and dhcpcd services are installed and configured to implement the HotSpot. 
