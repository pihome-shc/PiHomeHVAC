# Rasberry Pi MySensor Gateway
Script to build and install a local MySensor Gateway for a nrf24 module connected to the Rasberry Pi GPIO.

The nrf24 moduled can be connected directly to the Rasberry Pi GPIO however a decoupling capacitor of 4.7µ - 47µFcapacitor is needed as nrf24 radios are extremely sensitive to noisy or unstable/insufficient power (see [this page](https://www.mysensors.org/build/connect_radio) for details).
If you are using the NRF24L01+ PA/LNA version you need to use a 5V->3.3V regulator because the Raspberry Pi 3.3V can't supply enough power.
For additional details please see https://www.mysensors.org/build/raspberry.

The config.conf file specifies the configuration to be used when building the gateway. The default configuration is:
* --my-transport=rf24
* --my-rf24-channel=91
* --my-gateway=ethernet
* --my-port=5003
* --my-rf24-ce-pin=22
* --my-rf24-cs-pin=24
* --my-rf24-irq-pin=15
* --my-leds-err-pin=36
* --my-leds-rx-pin=38
* --my-leds-tx-pin=40


## Quick Start

1. Update config.conf as needed (RF24 channel and GPIO pins to be used)
2. Execute the install.sh bash script this will:
   * Download the latest version of the MySensor code from GitHub
   * Build and install the MySensor Gateway
   * Create and enable a service for autostart
   * Start service that was created
   * Update the AirMax database to use the gateway
