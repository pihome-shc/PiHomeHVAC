# Homekit
For controlling local devices with the iOS Home App. This interface uses Homebridge and the Homebridge Http Webhooks plugin to provide control of BOOST for individual zones and to interogate temperature sensors. 

## Quick Start

1. For detailed installation description, see https://github.com/homebridge/homebridge/wiki/Install-Homebridge-on-Raspbian
2. An automated process is provided by executing 'bash install.sh' this will:
    Check that Apache mod-rewrite is enabled and configured and if not then setup.
    Install/Update nodejs, checking the processor type for the system (armv61 for the RPi Zero or armv71 for the RPi 3/4 or aarch64 for 64 bit ARM based systems.
    Install the Homebridge application as a service.
    Install the Homebridge Http Webhooks plugin.
    Check that the Webhooks cache storage directory has been created and is empty.
    Create a backup of /var/lib/homebridge/config.json.
    Add to config.json the Webhooks platform
    Add to config.json Webhooks switches for each zone where status = 1, the [id] value will be switchxx where xx is the zone_id.
    Add to config.json Webhooks sensors for each temperature and humidity type sensor.
    Install the sensor and switch update Python application as a service.
