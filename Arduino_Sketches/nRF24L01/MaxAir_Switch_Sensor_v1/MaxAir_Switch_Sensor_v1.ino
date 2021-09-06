//           __  __                             _
//          |  \/  |                    /\     (_)
//          | \  / |   __ _  __  __    /  \     _   _ __
//          | |\/| |  / _` | \ \/ /   / /\ \   | | |  __|
//          | |  | | | (_| |  >  <   / ____ \  | | | |
//          |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|
//
//                 S M A R T   T H E R M O S T A T
// *****************************************************************
// *             Battery Powered Switch Sensor                     *
// *           Version 0.01 Build Date 03/09/2021                  *
// *            Last Modification Date 03/09/2021                  *
// *                                          Have Fun - PiHome.eu *
// *****************************************************************

// Enable debug prints to serial monitor
#define MY_DEBUG

//Set MY_SPLASH_SCREEN_DISABLED to disable MySensors splash screen. (This saves 120 bytes of flash)
//#define MY_SPLASH_SCREEN_DISABLED

//Define Sketch Name 
#define SKETCH_NAME "Switch Sensor"
//Define Sketch Version 
#define SKETCH_VERSION "0.01"

// Enable and select radio type attached
#define MY_RADIO_RF24
//#define MY_RADIO_NRF5_ESB
//#define MY_RADIO_RFM69
//#define MY_RADIO_RFM95

//IRQ Pin on Arduino
#define MY_RF24_IRQ_PIN 2

//Enable OTA 
//#define MY_OTA_FIRMWARE_FEATURE

// * - RF24_PA_MIN = -18dBm
// * - RF24_PA_LOW = -12dBm
// * - RF24_PA_HIGH = -6dBm
// * - RF24_PA_MAX = 0dBm
// Set LOW transmit power level as default, if you have an amplified NRF-module and
// power your radio separately with a good regulator you can turn up PA level.
// RF24_PA_MIN RF24_PA_LOW RF24_PA_HIGH RF24_PA_MAX RF24_PA_ERROR
#define MY_RF24_PA_LEVEL RF24_PA_MIN
//#define MY_DEBUG_VERBOSE_RF24

/**
 * @brief RF channel for the sensor net, 0-125.
 * Frequencies: 2400 Mhz - 2525 Mhz
 * @see https://www.nordicsemi.com/eng/nordic/download_resource/8765/2/42877161/2726
 * - 0 => 2400 Mhz (RF24 channel 1)
 * - 1 => 2401 Mhz (RF24 channel 2)
 * - 76 => 2476 Mhz (RF24 channel 77)
 * - 83 => 2483 Mhz (RF24 channel 84)
 * - 124 => 2524 Mhz (RF24 channel 125)
 * - 125 => 2525 Mhz (RF24 channel 126)
 * In some countries there might be limitations, in Germany for example only the range
 * 2400,0 - 2483,5 Mhz is allowed.
 * @see http://www.bundesnetzagentur.de/SharedDocs/Downloads/DE/Sachgebiete/Telekommunikation/Unternehmen_Institutionen/Frequenzen/Allgemeinzuteilungen/2013_10_WLAN_2,4GHz_pdf.pdf
 */
 
//Default RF channel Default is 76
#define MY_RF24_CHANNEL  74

//PiHome - Make Sure you change Node ID, for each temperature sensor. 21 for Ground Floor, 20 for First Floor, 30 for Domastic Hot Water.
#define MY_NODE_ID 90

//RF24_250KBPS for 250kbs, RF24_1MBPS for 1Mbps, or RF24_2MBPS for 2Mbps
#define MY_RF24_DATARATE RF24_250KBPS

//Enable Signing 
//#define MY_SIGNING_SIMPLE_PASSWD "pihome"

//Enable Encryption This uses less memory, and hides the actual data.
//#define MY_ENCRYPTION_SIMPLE_PASSWD "pihome"

// Set baud rate to same as optibot
//#define MY_BAUD_RATE 9600

//set how long to wait for transport ready in milliseconds
//#define MY_TRANSPORT_WAIT_READY_MS 3000

#include <MySensors.h>
#include <Bounce2.h>

#define ledpin 4      // LED for one Blink Power On, second blink for temperature sensors after successfull radio contact with gateway and three blinks for low battery 

#define CHILD_ID 0
#define BUTTON_PIN  3  // Arduino Digital I/O pin for button/reed switch
#define CHILD_ID_BATT 2

#define COMPARE_BVOLT 0 // Send battery voltage only if changed? 1 = Yes 0 = No, > 1 - force send if it value not sent that number of times

unsigned long SLEEP_TIME = 5000; // Sleep time between reads (in milliseconds)

int batteryNotSentCount=0;

// Battery related init
int BATTERY_SENSE_PIN = A0;  // select the input pin for the battery sense point
float oldBatteryV = 0;
MyMessage msgBatt(CHILD_ID_BATT, V_VOLTAGE);
#define MIN_VALUE 80 // threshold battery level to trigger notifications
MyMessage msgBattLevel(CHILD_ID_BATT, V_VAR1);

Bounce debouncer = Bounce(); 
int oldValue=-1;

// Change to V_LIGHT if you use S_LIGHT in presentation below
MyMessage msg(CHILD_ID,V_TRIPPED);

void setup()  
{  
  // Setup the button
  pinMode(BUTTON_PIN,INPUT_PULLUP);
  
  // After setting up the button, setup debouncer
  debouncer.attach(BUTTON_PIN);
  debouncer.interval(5);
  
  // needed for battery soc
  // use the 1.1 V internal reference
  #if defined(__AVR_ATmega2560__)
    analogReference(INTERNAL1V1);
  #else
    analogReference(INTERNAL);
  #endif
  // set minimum battery level threshold
  send(msgBattLevel.set(MIN_VALUE, 0));
}

void presentation() {
  // Send the sketch version information to the gateway and Controller
  sendSketchInfo(SKETCH_NAME, SKETCH_VERSION);
  // Register binary input sensor to gw (they will be created as child devices)
  // You can use S_DOOR, S_MOTION or S_LIGHT here depending on your usage. 
  // If S_LIGHT is used, remember to update variable type you send in. See "msg" above.
  present(CHILD_ID, S_DOOR);  
}


//  Check if digital input has changed and send in new value
void loop() 
{
  // get the battery Voltage
  //ref http://www.ohmslawcalculator.com/voltage-divider-calculator
  // Sense point is bypassed with 0.1 uF cap to reduce noise at that point
  
  // 1M, 100K divider across battery and using internal ADC ref of 1.1V
  // ((1e6+100e3)/100e3)*1.1 = Vmax = 12.1 Volts
  // 12.1/1023 = Volts per bit = 0.011828 
  
  //R1 820k, R2 220k
  //((820e3+220e3)/220e3)*1.1 = Vmax = 5.2 Volts
  //5.2/1023 = Volts per bit = 0.005083089
  
  int battSensorValue = analogRead(BATTERY_SENSE_PIN);
  //float batteryV  = battSensorValue * 0.005083089; //R1 820k, R2 220k divider across battery and using internal ADC ref of 1.1v
  float batteryV  = battSensorValue * 0.011828;    //R1 1M, R2 100K divider across battery and using internal ADC ref of 1.1v
  
  //int batteryPcnt = ((batteryV - 2.9) / (4.2 - 2.9) * 100); // for 18650 Battery Powred 
  //int batteryPcnt = ((batteryV - 2.1) / (3.0 - 2.1) * 100); // for 2 x AAA Battery Powered
  int batteryPcnt = ((batteryV - 2.5) / (4.5 - 2.5) * 100); // for 3 x AAA Battery Powered
    
  #ifdef MY_DEBUG
    Serial.print("Pin Reading: ");
    Serial.println(battSensorValue);
    Serial.print("Battery Voltage: ");
    Serial.print(batteryV);
    Serial.println(" v");
    //Print Battery Percentage
    Serial.print("Battery percent: ");
    Serial.print(batteryPcnt);
    Serial.println(" %");
  #endif
  
  #if COMPARE_BVOLT == 1
    if (oldBatteryV != batteryV) {
      send(msgBatt.set(batteryV, 2));
      sendBatteryLevel(batteryPcnt);
      oldBatteryV = batteryV;
    }
  #elif COMPARE_BVOLT == 0
    send(msgBatt.set(batteryV, 2));
    sendBatteryLevel(batteryPcnt);
  #else
    if (oldBatteryV != batteryV || batteryNotSentCount>=COMPARE_BVOLT) {
      send(msgBatt.set(batteryV, 2));
      sendBatteryLevel(batteryPcnt);
      oldBatteryV = batteryV;
      batteryNotSentCount=0;
    }else{
      batteryNotSentCount++;
    }
  #endif

  // get switch state
  debouncer.update();
  // Get the update value
  int value = debouncer.read();
 
  if (value != oldValue) {
     // Send in the new value
     send(msg.set(value==HIGH ? 1 : 0));
     oldValue = value;
  }
  #ifdef MY_DEBUG
    Serial.print("Switch State: ");
    Serial.println(value);
  #endif
  sleep(SLEEP_TIME);
} 

//Blink LED function, pass ping number and number of blinks usage: blink_led(variable or number of time blink, ledpin);
void blink_led(int count, int pin){
  for(int i=0;i<count;i++){
    digitalWrite(pin, HIGH);
    delay(700);
    digitalWrite(pin, LOW);
    delay(700);
  }
}
