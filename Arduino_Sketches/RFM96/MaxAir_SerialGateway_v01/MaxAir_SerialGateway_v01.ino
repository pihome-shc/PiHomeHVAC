//             __  __                             _
//            |  \/  |                    /\     (_)
//            | \  / |   __ _  __  __    /  \     _   _ __
//            | |\/| |  / _` | \ \/ /   / /\ \   | | |  __|
//            | |  | | | (_| |  >  <   / ____ \  | | | |
//            |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|
//           
//                   S M A R T   T H E R M O S T A T
// *****************************************************************
// *    MaxAir Serial Gateway Based on Arudino Mini Pro            *
// *            Version 0.2 Build Date 06/11/2017                  *
// *            Last Modification Date 08/06/2021                  *
// *                                          Have Fun - PiHome.eu *
// *****************************************************************

// Enable debug prints to serial monitor
#define MY_DEBUG
// Enable and select radio type attached
#define MY_RADIO_NRF24

#define MY_RADIO_RFM95
//#define MY_DEBUG_VERBOSE_RFM95
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
//#define MY_DEBUG_VERBOSE_RFM95_REGISTERS
//#define MY_RFM95_ATC_TARGET_RSSI (-70)  //target RSSI -70dBm Target RSSI level (in dBm) for RFM95 ATC mode. 

#define MY_TRANSPORT_STATE_TIMEOUT_MS  (3*1000ul)
#define RFM95_RETRY_TIMEOUT_MS  (3000ul)
#define MY_RFM95_IRQ_PIN 1
#define MY_RFM95_IRQ_NUM digitalPinToInterrupt(MY_RFM95_IRQ_PIN)
#define MY_RFM95_CS_PIN 8

//MaxAir Node ID - Not needed for Gateway 
//#define MY_NODE_ID 100

//Enable Signing <Make Sure you Change Password>
//#define MY_SIGNING_SIMPLE_PASSWD "maxair2021"

//Enable Encryption This uses less memory, and hides the actual data. <Make Sure you Change Password>
//#define MY_ENCRYPTION_SIMPLE_PASSWD "maxair2021"

//Enable repeater functionality for this node
//#define MY_REPEATER_FEATURE

//set how long to wait for transport ready in milliseconds
//#define MY_TRANSPORT_WAIT_READY_MS 3000


//Enable serial gateway
#define MY_GATEWAY_SERIAL

//Define a lower baud rate for Arduino's running on 8 MHz (Arduino Pro Mini 3.3V & SenseBender)
#if F_CPU == 8000000L
#define MY_BAUD_RATE 38400
#endif

// Enable inclusion mode
#define MY_INCLUSION_MODE_FEATURE
// Enable Inclusion mode button on gateway
//#define MY_INCLUSION_BUTTON_FEATURE

// Inverses behavior of inclusion button (if using external pullup)
//#define MY_INCLUSION_BUTTON_EXTERNAL_PULLUP

// Set inclusion mode duration (in seconds)
#define MY_INCLUSION_MODE_DURATION 60
// Digital pin used for inclusion mode button
//#define MY_INCLUSION_MODE_BUTTON_PIN  3

// Set blinking period
#define MY_DEFAULT_LED_BLINK_PERIOD 300

// Inverses the behavior of leds
#define MY_WITH_LEDS_BLINKING_INVERSE

// Flash leds on rx/tx/err
// Uncomment to override default HW configurations
#define MY_DEFAULT_ERR_LED_PIN 4  // Error led pin
#define MY_DEFAULT_RX_LED_PIN  6  // Receive led pin
#define MY_DEFAULT_TX_LED_PIN  5  // the PCB, on board LED

#include <MySensors.h>

void setup()
{
  // Setup locally attached sensors
}

void presentation()
{
  // Present locally attached sensors
}

void loop()
{
  // Send locally attached sensor data here
}

