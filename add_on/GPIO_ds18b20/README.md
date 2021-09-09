# 1-Wire ds18b20 sensors
Script to enable the use of ds18b20 sensors directly wired to the GPIO of the Rasberry Pi. This script automates the steps described [here](https://www.pihome.eu/2017/10/11/ds18b20-temperature-sensor-with-raspberry-pi/).

## Quick Start
1. Execute the install.sh bash script this will:
   * Update the AirMax database check every 60sec that the temperature is been acquired from the wired ds18b20 sensors
   * Check that the 1-Wire kernel module is loaded, if not configure the system to load it at boot and reboot the system.
