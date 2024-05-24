//           __  __                             _
//          |  \/  |                    /\     (_)
//          | \  / |   __ _  __  __    /  \     _   _ __
//          | |\/| |  / _` | \ \/ /   / /\ \   | | |  __|
//          | |  | | | (_| |  >  <   / ____ \  | | | |
//          |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|
//
//                 S M A R T   T H E R M O S T A T
// *****************************************************************
// *           Heating Zone Controller Relay Sketch                *
// *            Version 0.34 Build Date 06/11/2017                 *
// *            Last Modification Date 20/05/2024                  *
// *                                          Have Fun - PiHome.eu *
// *****************************************************************


// Enable debug prints to serial monitor
#define MY_DEBUG

//Set MY_SPLASH_SCREEN_DISABLED to disable MySensors splash screen. (This saves 120 bytes of flash)
#define MY_SPLASH_SCREEN_DISABLED

//Define Sketch Name 
#define SKETCH_NAME "Zone Controller Relay"
//Define Sketch Version 
#define SKETCH_VERSION "0.34"

// Enable and select radio type attached
//#define MY_RADIO_RF24
//#define MY_RADIO_NRF5_ESB
//#define MY_RADIO_RFM69
#define MY_RADIO_RFM95
//#define   MY_DEBUG_VERBOSE_RFM95
#define MY_RFM95_MAX_POWER_LEVEL_DBM (20) //Set max TX power in dBm if local legislation requires this - 1mW = 0dBm, 10mW = 10dBm, 25mW = 14dBm, 100mW = 20dBm
#define MY_RFM95_FREQUENCY (RFM95_434MHZ) //The frequency to use - RFM95_169MHZ, RFM95_315MHZ, RFM95_434MHZ, RFM95_868MHZ, RFM95_915MHZ
#define MY_RFM95_MODEM_CONFIGRUATION RFM95_BW125CR45SF128 
/* 
#define MY_RFM95_MODEM_CONFIGRUATION RFM95_BW125CR45SF128 
RFM95 modem configuration.
BW = Bandwidth in kHz CR = Error correction code SF = Spreading factor, chips / symbol
CONFIG           BW    CR    SF    Comment
RFM95_BW125CR45SF128  125   4/5   128   Default, medium range
RFM95_BW500CR45SF128  500   4/5   128   Fast, short range
RFM95_BW31_25CR48SF512  31.25   4/8   512   Slow, long range
RFM95_BW125CR48SF4096   125   4/8   4096  Slow, long range 
*/
//#define MY_RFM95_TCXO // Enable to force your radio to use an external frequency source (e.g. TCXO, if present). This allows for better stability using SF 9 to 12. 
//#define MY_RFM95_TX_POWER_DBM   (13u) //Set TX power level, default 13dBm (overridden if ATC mode enabled) 
//#define MY_RFM95_TCXO // Enable to force your radio to use an external frequency source (e.g. TCXO, if present). This allows for better stability using SF 9 to 12. 
//#define MY_RFM95_TX_POWER_DBM   (13u) //Set TX power level, default 13dBm (overridden if ATC mode enabled) 
//#define MY_RFM95_ATC_TARGET_RSSI (-70)  //target RSSI -70dBm Target RSSI level (in dBm) for RFM95 ATC mode. 
#define MY_TRANSPORT_STATE_TIMEOUT_MS  (3*1000ul) //general state timeout (in ms) 
#define RFM95_RETRY_TIMEOUT_MS  (3000ul)
#define MY_RFM95_IRQ_PIN 2
#define MY_RFM95_IRQ_NUM digitalPinToInterrupt(MY_RFM95_IRQ_PIN)
#define MY_RFM95_CS_PIN 8
 
//PiHome Zone Controller Node ID
//#define MY_NODE_ID 101
#define BASE_NODE_ID 101
int8_t myNodeId;
#define MY_NODE_ID myNodeId

//Enable Signing 
//#define MY_SIGNING_SIMPLE_PASSWD "pihome2019"

//Enable Encryption This uses less memory, and hides the actual data.
//#define MY_ENCRYPTION_SIMPLE_PASSWD "pihome2019"

// Enable repeater functionality for this node
//#define MY_REPEATER_FEATURE

// Set baud rate to same as optibot
//#define MY_BAUD_RATE 115200

//set how long to wait for transport ready in milliseconds
//#define MY_TRANSPORT_WAIT_READY_MS 3000

//If Following LED Blink does not work then modify C:\Program Files (x86)\Arduino\libraries\MySensors_2_1_1\MyConfig.h 
#define MY_DEFAULT_ERR_LED_PIN 16 //A0 previous version 8 
#define MY_DEFAULT_TX_LED_PIN 14
#define MY_DEFAULT_RX_LED_PIN 15
#define MY_WITH_LEDS_BLINKING_INVERSE

#define MY_DEFAULT_LED_BLINK_PERIOD 400

//#define MY_OTA_FIRMWARE_FEATURE

#include <MySensors.h>
#include <avr/wdt.h>

#define RELAY_1 3  // Arduino Digital I/O pin number for first relay (second on pin+1 etc)
#define NUMBER_OF_RELAYS 6 // Total number of attached relays
#define RELAY_ON 1  // GPIO value to write to turn on attached relay
#define RELAY_OFF 0 // GPIO value to write to turn off attached relay

//int oldStatus = RELAY_OFF;

byte pinarray[] = { 3, 4, 5, 6, 7, 8 };
long double send_heartbeat_time = millis();
long double recieve_heartbeat_time = millis();
long double HEARTBEAT_TIME = 30000; // Send heartbeat every seconds
int trigger = 1; // set LOW to indicate the relays are posotive level triggered
char heartbeat_str[] = "Heartbeat,10x,abc";

#define CHILD_ID_TXT 255
MyMessage msgTxt(CHILD_ID_TXT, V_TEXT);

// xnor function to be used to apply relay trigger setting
bool xnor(int a, int b)
{
  if (a == b) {
    return 1;
  } else {
    return 0;
  }
}

void before()
{
  myNodeId = BASE_NODE_ID;
  // heartbeat message including NODE_ID
  heartbeat_str[12] = 48 + (myNodeId%10);
  #ifdef MY_DEBUG
    Serial.print("MY_NODE_ID: ");
    Serial.println(myNodeId);
    Serial.print("TRIGGER: ");
    Serial.println(trigger);
  #endif

  // relays 1-6 are connected to pins D3-D8
  // initialize the relays to the OFF state
  for (int pin=0; pin<NUMBER_OF_RELAYS; pin++) {
    pinMode(pinarray[pin], OUTPUT);
    digitalWrite(pinarray[pin], xnor(trigger, RELAY_OFF));
    delay(500);
    #ifdef MY_DEBUG
      Serial.print("Initialize Pin: ");
      Serial.print(pinarray[pin]);
      Serial.print(" - ");
      Serial.println(xnor(trigger, RELAY_OFF));
    #endif
  }
  #ifdef MY_DEBUG
    Serial.println("INITIAL CONFIGURATION FINISHED");
  #endif
}

void setup(){
  wdt_disable();
  //do something here if needed 
  wdt_enable (WDTO_8S);
  #ifdef MY_DEBUG
    Serial.println("WATCHDOG CONFIGURED");
  #endif
  sendHeartbeat();
}

//declare reset function @ address 0
void(* resetFunc) (void) = 0; 

void presentation(){
  // Send the sketch version information to the gateway and Controller
  sendSketchInfo(SKETCH_NAME, SKETCH_VERSION);
  for (int sensor=1; sensor<=NUMBER_OF_RELAYS; sensor++) {
    // Register all sensors to gw (they will be created as child devices)
    present(sensor, S_BINARY);
    delay(200);
  }
}

void loop(){
  // send a heatbeat signal to the gateway
  long double temp = (millis() - send_heartbeat_time);
  if (temp > HEARTBEAT_TIME) {
    // If it exceeds the heartbeat time then send a heartbeat
    // Build an 8 bit mask for the current relay ON/OFF states by reading PORTS B, C and D
    // Read digital pins 3 - 8
    byte port_mask = (PORTD >> 3) + ((PORTB & 0b00000001) << 5);
    // If 8 Releay controller, the add A0 and A1 pin states
    #ifdef MY_DEBUG
      Serial.print("Current Relay States: ");
      Serial.println(port_mask);
    #endif
    String myString = String(port_mask);
    if (port_mask < 10) {
      heartbeat_str[14] = '0';
      heartbeat_str[15] = '0';
      heartbeat_str[16] = myString[0];
    } else if (port_mask >= 10 and port_mask < 100) {
      heartbeat_str[14] = '0';
      heartbeat_str[15] = myString[0];
      heartbeat_str[16] = myString[1];
    } else {
      heartbeat_str[14] = myString[0];
      heartbeat_str[15] = myString[1];
      heartbeat_str[16] = myString[2];
    }
    send(msgTxt.set(heartbeat_str));
    // reset the heartbeat timer
    send_heartbeat_time = millis();
    #ifdef MY_DEBUG
      Serial.print("Sent heartbeat NODE_ID: ");
      Serial.println(myNodeId);
    #endif
  }

  // check that a heartbeat signal has been sent from the gateway
  temp = (millis() - recieve_heartbeat_time);
  if (temp > (HEARTBEAT_TIME * 2)) {
    #ifdef MY_DEBUG
      Serial.println("No heartbeat recieved" );
    #endif
    recieve_heartbeat_time = millis();
    // set all relays to the OFF state
    for (int pin=0; pin<NUMBER_OF_RELAYS; pin++) {
      digitalWrite(pinarray[pin], xnor(trigger, RELAY_OFF));
      delay(100);
    }
    //call reset function 
    resetFunc(); 
  } else {
    //Reset to Watch Dog to not to reboot
    wdt_reset(); 
  }
}

//void sendHeartbeat(){
//}

void receive(const MyMessage &message){
  // We only expect one type of message from controller. But we better check anyway.
  if (message.type==V_STATUS) {
    int pin = message.sensor - 1;
    digitalWrite(pinarray[pin], message.getBool()?xnor(trigger, RELAY_OFF):xnor(trigger, RELAY_ON));
    // Store state in eeprom
    saveState(message.sensor, message.getBool());
    delay(100);
    // Write some debug info
    #ifdef MY_DEBUG
      Serial.print("Incoming Change for Relay: ");
      Serial.print(message.sensor);
      Serial.print(" - pin: ");
      Serial.print(pinarray[pin]);
      Serial.print(" - New Status: ");
      Serial.println(message.getBool());
    #endif
  }
  if (message.type==V_VAR1) {
    #ifdef MY_DEBUG
      Serial.print("Node ID:    ");
      Serial.println(message.destination);
      Serial.print("Type:    "); //Message type, the number assigned
      Serial.println(message.type); // V_VAR1 is 24 zero. etc.
      Serial.print("Child:   "); // Child ID of the Sensor/Device
      Serial.println(message.sensor);
      Serial.print("Payload: "); // This is where the wheels fall off
      Serial.println(message.getString()); // This works great!
    #endif
    //if (message.sensor==99 && message.type==24 && message.getString()=="99"){
    if (message.sensor==99 && message.type==24){
      #ifdef MY_DEBUG
        Serial.print("Reboot Command Received!!! \n");
        Serial.print("..::Rebooting Controller::.. \n");
      #endif
      for (int pin=0; pin<NUMBER_OF_RELAYS; pin++) {
        digitalWrite(pinarray[pin], xnor(trigger, RELAY_OFF));
        delay(100);
      }
      //call reset function 
      resetFunc();
    }
    // a heartbeat message has been sent to this NODE_ID, so reset the heartbeat timer
    if (message.destination==MY_NODE_ID && message.type==24){
      #ifdef MY_DEBUG
        Serial.println("Heartbeat Recieved from Gateway Script");
      #endif
      recieve_heartbeat_time = millis();
    }
  }
  // Clear any unallocated relays when the Gateway script heartbeat message is returned
  if (message.type==V_VAR3) {
    Serial.print("Relay Mask: ");
    Serial.println(message.getUInt());      
    for (int pin=0; pin<NUMBER_OF_RELAYS; pin++) {
      if (bitRead(message.getUInt(), pin) == 1) {
        digitalWrite(pinarray[pin], xnor(trigger, RELAY_OFF));
        delay(100);
      }
    }
  }
}
