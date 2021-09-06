//             __  __                             _
//            |  \/  |                    /\     (_)
//            | \  / |   __ _  __  __    /  \     _   _ __
//            | |\/| |  / _` | \ \/ /   / /\ \   | | |  __|
//            | |  | | | (_| |  >  <   / ____ \  | | | |
//            |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|
//           
//                   S M A R T   T H E R M O S T A T
// *****************************************************************
// *           Heating Zone Controller Relay Sketch                *
// *            Version 0.31 Build Date 06/11/2017                 *
// *            Last Modification Date 08/06/2021                  *
// *                                          Have Fun - PiHome.eu *
// *****************************************************************

// Enable debug prints to serial monitor
#define MY_DEBUG
//Set MY_SPLASH_SCREEN_DISABLED to disable MySensors splash screen. (This saves 120 bytes of flash)
#define MY_SPLASH_SCREEN_DISABLED
//Define Sketch Name 
#define SKETCH_NAME "Zone Controller"
//Define Sketch Version 
#define SKETCH_VERSION "0.31"

#define MY_RADIO_RFM95
//#define   MY_DEBUG_VERBOSE_RFM95
#define MY_RFM95_MAX_POWER_LEVEL_DBM (20) //Set max TX power in dBm if local legislation requires this - 1mW = 0dBm, 10mW = 10dBm, 25mW = 14dBm, 100mW = 20dBm
#define MY_RFM95_FREQUENCY (RFM95_434MHZ) //The frequency to use - RFM95_169MHZ, RFM95_315MHZ, RFM95_434MHZ, RFM95_868MHZ, RFM95_915MHZ
#define MY_RFM95_MODEM_CONFIGRUATION RFM95_BW125CR45SF128 
/* 
#define MY_RFM95_MODEM_CONFIGRUATION RFM95_BW125CR45SF128 
RFM95 modem configuration.
BW = Bandwidth in kHz CR = Error correction code SF = Spreading factor, chips / symbol
CONFIG 					BW 		CR 		SF		Comment
RFM95_BW125CR45SF128 	125 	4/5 	128 	Default, medium range
RFM95_BW500CR45SF128 	500 	4/5 	128 	Fast, short range
RFM95_BW31_25CR48SF512 	31.25 	4/8 	512 	Slow, long range
RFM95_BW125CR48SF4096 	125 	4/8 	4096 	Slow, long range 
*/
//#define MY_RFM95_TCXO // Enable to force your radio to use an external frequency source (e.g. TCXO, if present). This allows for better stability using SF 9 to 12. 
//#define MY_RFM95_TX_POWER_DBM   (13u) //Set TX power level, default 13dBm (overridden if ATC mode enabled) 
//#define MY_RFM95_TCXO // Enable to force your radio to use an external frequency source (e.g. TCXO, if present). This allows for better stability using SF 9 to 12. 
//#define MY_RFM95_TX_POWER_DBM   (13u) //Set TX power level, default 13dBm (overridden if ATC mode enabled) 
//#define MY_RFM95_ATC_TARGET_RSSI (-70)  //target RSSI -70dBm Target RSSI level (in dBm) for RFM95 ATC mode. 
#define MY_TRANSPORT_STATE_TIMEOUT_MS  (3*1000ul) //general state timeout (in ms) 
#define RFM95_RETRY_TIMEOUT_MS  (3000ul)
#define MY_RFM95_IRQ_PIN 2
#define MY_RFM95_IRQ_NUM MY_RFM95_IRQ_PIN
#define MY_RFM95_CS_PIN 8

//MaxAir Zone Controller Node ID
#define MY_NODE_ID 101

//Enable Signing 
//#define MY_SIGNING_SIMPLE_PASSWD "maxair2021"

//Enable Encryption This uses less memory, and hides the actual data.
//#define MY_ENCRYPTION_SIMPLE_PASSWD "maxair2021"

// Enable repeater functionality for this node
//#define MY_REPEATER_FEATURE

// Set baud rate to same as optibot
//#define MY_BAUD_RATE 115200

//set how long to wait for transport ready in milliseconds
//#define MY_TRANSPORT_WAIT_READY_MS 3000

//If Following LED Blink does not work then modify C:\Program Files (x86)\Arduino\libraries\MySensors_2_1_1\MyConfig.h 
#define MY_DEFAULT_ERR_LED_PIN 8 //A0 previous version 8 
#define MY_DEFAULT_TX_LED_PIN 6
#define MY_DEFAULT_RX_LED_PIN 7
#define MY_WITH_LEDS_BLINKING_INVERSE
#define MY_DEFAULT_LED_BLINK_PERIOD 600

#include <MySensors.h>

#define RELAY_1 2  // Arduino Digital I/O pin number for first relay (second on pin+1 etc)
#define NUMBER_OF_RELAYS 4 // Total number of attached relays
#define RELAY_ON 0  // GPIO value to write to turn on attached relay
#define RELAY_OFF 1 // GPIO value to write to turn off attached relay

void before(){
	for (int sensor=1, pin=RELAY_1; sensor<=NUMBER_OF_RELAYS; sensor++, pin++) {
		// Then set relay pins in output mode
		pinMode(pin, OUTPUT);
		// Set relay to last known state (using eeprom storage)
		digitalWrite(pin, loadState(sensor)?RELAY_ON:RELAY_OFF);
		delay(3);
	}
}

void setup(){

}

void presentation(){
	// Send the sketch version information to the gateway and Controller
	sendSketchInfo(SKETCH_NAME, SKETCH_VERSION);

	for (int sensor=1, pin=RELAY_1; sensor<=NUMBER_OF_RELAYS; sensor++, pin++) {
		// Register all sensors to gw (they will be created as child devices)
		present(sensor, S_BINARY);
		delay(6);
	}
}

void loop(){

}

void receive(const MyMessage &message){
	// We only expect one type of message from controller. But we better check anyway.
	if (message.type==V_STATUS) {
		// Change relay state
		digitalWrite(message.sensor-1+RELAY_1, message.getBool()?RELAY_ON:RELAY_OFF);
		delay(3);
		// Store state in eeprom 
		saveState(message.sensor, message.getBool());
		// Write some debug info
		#ifdef MY_DEBUG
			Serial.print("Incoming Change for Relay: ");
			Serial.print(message.sensor);
			Serial.print(" - New Status: ");
			Serial.println(message.getBool());
		#endif
	}
}
