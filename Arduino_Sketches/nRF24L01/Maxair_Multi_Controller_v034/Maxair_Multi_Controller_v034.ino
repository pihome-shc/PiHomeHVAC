//           __  __                             _
//          |  \/  |                    /\     (_)
//          | \  / |   __ _  __  __    /  \     _   _ __
//          | |\/| |  / _` | \ \/ /   / /\ \   | | |  __|
//          | |  | | | (_| |  >  <   / ____ \  | | | |
//          |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|
//
//                 S M A R T   T H E R M O S T A T
// *****************************************************************
// *          8 Channel Multi Controller Relay Sketch              *
// *            Version 0.34 Build Date 06/11/2017                 *
// *            Last Modification Date 13/03/2025                  *
// *                                          Have Fun - PiHome.eu *
// *****************************************************************


// Enable debug prints to serial monitor
#define MY_DEBUG

//Set MY_SPLASH_SCREEN_DISABLED to disable MySensors splash screen. (This saves 120 bytes of flash)
#define MY_SPLASH_SCREEN_DISABLED

//Define Sketch Name 
#define SKETCH_NAME "Multi Controller Relay"
//Define Sketch Version 
#define SKETCH_VERSION "0.34"

// Enable and select radio type attached
#define MY_RADIO_RF24
//#define MY_RADIO_NRF5_ESB
//#define MY_RADIO_RFM69
//#define MY_RADIO_RFM95

#ifdef MY_RADIO_RF24
  #define MY_RF24_PA_LEVEL RF24_PA_LOW
  //#define MY_DEBUG_VERBOSE_RF24
  #define MY_RF24_IRQ_PIN 2
  //#define MY_RF24_IRQ_NUM digitalPinToInterrupt(MY_RF24_IRQ_PIN)
  //#define MY_RX_MESSAGE_BUFFER_FEATURE
  //#define MY_RX_MESSAGE_BUFFER_SIZE 5

  // We have to move CE/CSN pins for NRF radio
  #ifndef MY_RF24_CE_PIN
    #define MY_RF24_CE_PIN 9
  #endif
  #ifndef MY_RF24_CS_PIN
    #define MY_RF24_CS_PIN 10
  #endif

  // RF channel for the sensor net, 0-127
  #define MY_RF24_CHANNEL 91

  //RF24_250KBPS for 250kbs, RF24_1MBPS for 1Mbps, or RF24_2MBPS for 2Mbps
  #define MY_RF24_DATARATE RF24_250KBPS
#endif
 
//PiHome Zone Controller Node ID
//#define MY_NODE_ID 101
#define BASE_NODE_ID 100
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
#define MY_DEFAULT_ERR_LED_PIN A2
#define MY_DEFAULT_TX_LED_PIN A4
#define MY_DEFAULT_RX_LED_PIN A3
#define MY_WITH_LEDS_BLINKING_INVERSE

#define MY_DEFAULT_LED_BLINK_PERIOD 400

//#define MY_OTA_FIRMWARE_FEATURE

#include <MySensors.h>
#include <avr/wdt.h>

#define RELAY_1 3  // Arduino Digital I/O pin number for first relay (second on pin+1 etc)
#define NUMBER_OF_RELAYS 8 // Total number of attached relays
#define RELAY_ON 1  // GPIO value to write to turn on attached relay
#define RELAY_OFF 0 // GPIO value to write to turn off attached relay

//int oldStatus = RELAY_OFF;

byte pinarray[] = { 3, 4, 5, 6, 7, 8, A0, A1 };
long double send_heartbeat_time = millis();
long double recieve_heartbeat_time = millis();
long double HEARTBEAT_TIME = 30000; // Send heartbeat every seconds
int trigger;
char heartbeat_str[] = "Heartbeat,10x,abc";

#define CHILD_ID_TXT 255
MyMessage msgTxt(CHILD_ID_TXT, V_TEXT);

#define TRIGGER A5 // set LOW to indicate the relays are posotive level triggered

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
  // use A6 and A7 as inputs to offset the base NODE_ID of 100 (PCB has pullup resistors on both pins)
  pinMode (A6, INPUT);
  int val_A6 = 1;
  if (analogRead(A6) > 512) {
    val_A6 = 0;
  }
  pinMode (A7, INPUT);
  int val_A7 = 1;
  if (analogRead(A7) > 512) {
    val_A7 = 0;
  }
  int node_low = BASE_NODE_ID + val_A6 + (val_A7 * 2);
  //set MY_NODE_ID based on the jumper settings, options are BASE_NODE_ID, BASE_NODE_ID + 1, BASE_NODE_ID + 2 or BASE_NODE_ID + 3
  //no jumpers will give set MY_NODE_ID to BASE_NODE_ID, all jumpers installed will set MY_NODE_ID to BASE_NODE_ID + 3 
  myNodeId = node_low;
  // heartbeat message including NODE_ID
  heartbeat_str[12] = val_A6 + (val_A7 * 2) + '0';
  #ifdef MY_DEBUG
    Serial.print("MY_NODE_ID: ");
    Serial.println(myNodeId);
  #endif

  // read the J3 jumper to determine if LOW or HIGH trigger for the relays
  pinMode(TRIGGER, INPUT_PULLUP);
  trigger = digitalRead(TRIGGER);
  #ifdef MY_DEBUG
    Serial.print("TRIGGER: ");
    Serial.println(trigger);
  #endif

  // relays 1-6 are connected to pins D3-D8, relay 7 to A0 and relay 8 to A1
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
    if (NUMBER_OF_RELAYS == 8) {
      port_mask = port_mask + ((PORTC & 0b00000011) << 6);
    }
    // Invert bits depending on value of 'trigger'
    if (trigger == 0) {
      port_mask = ~port_mask;
    }
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
    // Use relay update to reset heartbeat timer
    recieve_heartbeat_time = millis();
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
