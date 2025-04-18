//             __  __                             _
//            |  \/  |                    /\     (_)
//            | \  / |   __ _  __  __    /  \     _   _ __
//            | |\/| |  / _` | \ \/ /   / /\ \   | | |  __|
//            | |  | | | (_| |  >  <   / ____ \  | | | |
//            |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|
//           
//                   S M A R T   T H E R M O S T A T
// *****************************************************************
// *  Combined OneWire DS18B20 Temperature Sensor and Reed Switch  *
// *    can be cofigured for either Battery or %V USB-C power.     *
// *           Version 0.37 Build Date 07/03/2024                  *
// *            Last Modification Date 26/03/2025                  *
// *                                          Have Fun - PiHome.eu *
// *****************************************************************

// Enable debug prints to serial monitor
#define MY_DEBUG

//Set MY_SPLASH_SCREEN_DISABLED to disable MySensors splash screen. (This saves 120 bytes of flash)
#define MY_SPLASH_SCREEN_DISABLED

//Define Sketch Name 
#define SKETCH_NAME_1 "Temperature Sensor"
#define SKETCH_NAME_2 "Switch Sensor"
#define SKETCH_NAME_3 "Temperature/Switch Sensor"
#define SKETCH_NAME_4 "No Attached Sensors"

//Define Sketch Version 
#define SKETCH_VERSION "0.37"

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
 
//Default RF channel Default is 91
int8_t myChannelId;
#define MY_RF24_CHANNEL myChannelId

//PiHome - Make Sure you change Node ID, for each temperature sensor. 21 for Ground Floor, 20 for First Floor, 30 for Domastic Hot Water.
//#define MY_NODE_ID 20
int8_t myNodeId;
#define MY_NODE_ID myNodeId

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
#include <DallasTemperature.h>
#include <OneWire.h>

#define ledpin 4      // LED for one Blink Power On, second blink for temperature sensors after successfull radio contact with gateway and three blinks for low battery
#define BUTTON_PIN A4  // Arduino Digital I/O pin for button/reed switch

// Define sensor node childs
int8_t myChildId;
#define CHILD_ID_SWITCH 2
#define CHILD_ID_BATT 1
#define CHILD_ID_TEMP 0

// Jumpers J1-J4 are used to configure the NODE_ID, all combinations are valid
// J1 - Add 10 to the base address of 20
// J2 - Add 20 to the base address of 20
// J3 - Add 1 to the base address of 20
// J4 - Add 2 to the base address of 20

// Jumpers J5-J10 are used to configure the sketch dependant on user preferences
// J5 - COMPARE_TEMP (No Jumper = 1, Jumper = 0, Jumpers ignored if myCompareTemp not initialised to 0 in sketch)
// J6 - COMPARE_BVOLT (No Jumper = 0, Jumper = 1, Jumpers ignored if myCompareBvolt not initialised to 0 in sketch)
// J7 - NO jumper - MY_RF24_CHANNEL = 91, Jumper - MY_RF24_CHANNEL = 74
// J8 - not used
// J9 - MAX_ATTACHED_DS18B20 (No Jumper = 1, Jumper = 2)
// J10 - Enable Read Switch

int8_t myCompareBvolt = 0; // Send battery voltage only if changed? 1 = Yes 0 = No, > 1 - force send if it value not sent that number of times
int8_t myCompareTemp = 0; // Send temperature only if changed? 1 = Yes 0 = No, > 1 - force send if it value not sent that number of times and value is valid (keep lower than notice interval)
int8_t myNumDS18B20;
#define MAX_ATTACHED_DS18B20 myNumDS18B20

#define ONE_WIRE_BUS 3 // Pin where dallas sensor is connected 

unsigned long SLEEP_TIME = 1000; // Sleep time between loop executons (in milliseconds)
int sleep_counter = 0;

bool myEnableBvolt = false;
int batteryNotSentCount=0;
 
// Battery related init
// select the input pin for the battery sense point
#define BATTERY_SENSE_PIN A3
float oldBatteryV = 0;
MyMessage msgBatt(CHILD_ID_BATT, V_VOLTAGE);
#define MIN_VALUE 80 // threshold battery level to trigger notifications
MyMessage msgBattLevel(CHILD_ID_BATT, V_VAR1);

// Dallas Temperature related init
OneWire oneWire(ONE_WIRE_BUS); // Setup a oneWire instance to communicate with any OneWire devices (not just Maxim/Dallas temperature ICs)
DallasTemperature sensors(&oneWire); // Pass the oneWire reference to Dallas Temperature.
#define TEMPERATURE_PRECISION 12 // Temperature resolution

// Initialise the temperature arrays
int temperatureNotSentCount[16];
float lastTemperature[16];

int numTempSensors=0;
bool receivedConfig = false;
bool metric = true;
// Initialize temperature message
MyMessage msg(CHILD_ID_TEMP, V_TEMP);

// Reed Switch related init
int oldValue=-1;

// Change to V_LIGHT if you use S_LIGHT in presentation below
MyMessage msgSwitch(CHILD_ID_SWITCH,V_TRIPPED);

// Is reed switch connected
bool myEnableSwitch = false;

void before(){
  //Configure A0-A5 as inputs with a pullup resistor
  pinMode (A0, INPUT);
  digitalWrite (A0, HIGH); // turn on internal pullup
  int val_A0 = digitalRead(A0);   // read the input pin
  pinMode (A1, INPUT);
  digitalWrite (A1, HIGH); // turn on internal pullup
  int val_A1 = digitalRead(A1) ^ 1;   // read the input pin
  if (myCompareBvolt == 0) {
    myCompareBvolt = val_A1;
  }
  pinMode (A2, INPUT);
  digitalWrite (A2, HIGH); // turn on internal pullup
  int val_A2 = digitalRead(A2);   // read the input pin
  if (myCompareTemp == 0) {
    myCompareTemp = val_A2;
  }
  pinMode(BATTERY_SENSE_PIN, INPUT);    // ADC pin
  int val_A3 = analogRead(BATTERY_SENSE_PIN);
  if (val_A3 > 0) {
    myEnableBvolt = true;  
  } else {
    myEnableBvolt = false;
  }
  pinMode (A5, INPUT);
  digitalWrite (A5, HIGH); // turn on internal pullup
  int val_A5 = digitalRead(A5) ^ 1;   // read the input pin
  int val_A6 = analogRead(A6); // read the reed switch enable pin
  if (val_A6 > 0) {
    myEnableSwitch = false;
  } else {
    myEnableSwitch = true;
  }
  if (myEnableSwitch) {
    // Setup the button
    pinMode(BUTTON_PIN,INPUT_PULLUP);
    digitalWrite (BUTTON_PIN, HIGH); // turn on internal pullup
    oldValue = digitalRead(BUTTON_PIN);   // read the input pin
  }  
  
  //Configure D4-D8 as inputs with a pullup resistor
  pinMode (4, INPUT);
  digitalWrite (4, HIGH); // turn on internal pullup
  int val_D4 = digitalRead(4) ^ 1;   // read the input pin
  myNumDS18B20 = 1 + val_D4;
  pinMode (5, INPUT);
  digitalWrite (5, HIGH); // turn on internal pullup
  int val_D5 = digitalRead(5) ^ 1;   // read the input pin
  pinMode (6, INPUT);
  digitalWrite (6, HIGH); // turn on internal pullup
  int val_D6 = digitalRead(6) ^ 1;   // read the input pin
  pinMode (7, INPUT);
  digitalWrite (7, HIGH); // turn on internal pullup
  int val_D7 = digitalRead(7) ^ 1;   // read the input pin
  pinMode (8, INPUT);
  digitalWrite (8, HIGH); // turn on internal pullup
  int val_D8 = digitalRead(8) ^ 1;   // read the input pin
  int node_low = 0 + val_D6 + (val_D5 * 2);
  int node_high = 20 + (val_D8 + (val_D7 * 2)) * 10;
  //set MY_NODE_ID based on the jumper settings, options 20-23, 30-33, 40-43 or 50-53
  //no jumpers will give set MY_NODE_ID to 20, all jumpers installed will set MY_NODE_ID to 53 
  myNodeId = node_high + node_low;
  if (val_A0 == HIGH) {
    myChannelId = 91;
  } else {
    myChannelId = 74;
  }
  #ifdef MY_DEBUG
    Serial.print("Pin A0 Level: ");
    Serial.println(val_A0);
    Serial.print("Pin A1 Level: ");
    Serial.println(val_A1);
    Serial.print("Pin A2 Level: ");
    Serial.println(val_A2);
    Serial.print("Pin A3 Level: ");
    Serial.println(val_A3);
    Serial.print("Pin A5 Level: ");
    Serial.println(val_A5);
    Serial.print("Pin A6 Level: ");
    Serial.println(val_A6);
    Serial.print("Pin D4 Level: ");
    Serial.println(val_D4);
    Serial.print("Pin D5 Level: ");
    Serial.println(val_D5);
    Serial.print("Pin D6 Level: ");
    Serial.println(val_D6);
    Serial.print("Pin D7 Level: ");
    Serial.println(val_D7);
    Serial.print("Pin D8 Level: ");
    Serial.println(val_D8);
    Serial.print("MY_RF24_CHANNEL: ");
    Serial.println(myChannelId);
    Serial.print("MY_NODE_ID: ");
    Serial.println(myNodeId);
    Serial.print("BATTERY: ");
    if (myEnableBvolt) {
      Serial.println("In Use");
    } else {
      Serial.println("NOT In Use");
    }
    Serial.print("REED SWITCH: ");
    if (myEnableSwitch) {
      Serial.println("In Use");
    } else {
      Serial.println("NOT In Use");
    }
    Serial.print("COMPARE_BVOLT: ");
    Serial.println(myCompareBvolt);
    Serial.print("COMPARE_TEMP: ");
    Serial.println(myCompareTemp);
    Serial.print("MAX_ATTACHED_DS18B20: ");
    Serial.println(myNumDS18B20);
  #endif

  // Startup up the OneWire library
  sensors.begin();
}

void setup(){
  //This is LED pin set to output and turn it on for short while
  pinMode(ledpin, OUTPUT);
  digitalWrite(ledpin, HIGH);
  delay(60);
  digitalWrite(ledpin, LOW);
  // requestTemperatures() will not block current thread
  sensors.setWaitForConversion(false);
  sensors.setResolution(TEMPERATURE_PRECISION);
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
  // Fetch the number of attached temperature sensors
  numTempSensors = sensors.getDeviceCount();
  #ifdef MY_DEBUG
    Serial.print("Number of Sensors Found: ");
    Serial.println(numTempSensors);
  #endif
  //Blink LED as number of sensors attached
  blink_led(numTempSensors, ledpin);

  //check if attached sensors number is grater then 0 if no then put led on solid (AT COMPILE TIME)
  #if numTempSensors > 0
    digitalWrite(ledpin, HIGH);
  #else
    digitalWrite(ledpin, LOW);
  #endif

  // Send the sketch version information to the gateway and Controller
  if (myEnableSwitch) {
    if (numTempSensors == 0) {
      sendSketchInfo(SKETCH_NAME_2, SKETCH_VERSION);
    } else {
      sendSketchInfo(SKETCH_NAME_3, SKETCH_VERSION);
    }  
  } else {
    if (numTempSensors > 0) {
      sendSketchInfo(SKETCH_NAME_1, SKETCH_VERSION);
    } else {
      sendSketchInfo(SKETCH_NAME_4, SKETCH_VERSION);
    }
  }
    
  int count = 0; // used to add the extra child_id if the SWITCH is enabled
  // Present all sensors to controller
  for (int i=0; i<numTempSensors && i<MAX_ATTACHED_DS18B20; i++) {
    present(i, S_TEMP);
    count++;
  }
  if (myEnableSwitch) {
    // Register binary input sensor to gw (they will be created as child devices)
    // You can use S_DOOR, S_MOTION or S_LIGHT here depending on your usage. 
    // If S_LIGHT is used, remember to update variable type you send in. See "msg" above.
    present(count, S_TEMP);
  }  
}

void loop(){
  if (sleep_counter == 0) {
    if (myEnableBvolt) {
      // get the battery Voltage
      //ref http://www.ohmslawcalculator.com/voltage-divider-calculator
      // Sense point is bypassed with 0.1 uF cap to reduce noise at that point

      // 1M, 100K divider across battery and using internal ADC ref of 1.1V
      // ((1e6+100e3)/100e3)*1.1 = Vmax = 12.1 Volts
      // 12.1/1023 = Volts per bit = 0.011828

      //R1 820k, R2 220k
      //((820e3+220e3)/220e3)*1.1 = Vmax = 5.2 Volts
      //5.2/1023 = Volts per bit = 0.005083089

      burn8Readings(A3);            // make 8 readings but don't use them
      delay(10);                    // idle some time
      int battSensorValue = analogRead(BATTERY_SENSE_PIN);
      //float batteryV  = battSensorValue * 0.005083089; //R1 820k, R2 220k divider across battery and using internal ADC ref of 1.1v
      float batteryV  = battSensorValue * 0.011828;    //R1 1M, R2 100K divider across battery and using internal ADC ref of 1.1v

      int batteryPcnt = ((batteryV - 2.9) / (4.2 - 2.9) * 100); // for 18650 Battery Powred
      //int batteryPcnt = ((batteryV - 2.1) / (3.0 - 2.1) * 100); // for 2 x AAA Battery Powered
      //int batteryPcnt = ((batteryV - 2.5) / (4.5 - 2.5) * 100); // for 3 x AAA Battery Powered

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

      if (myCompareBvolt == 1) {
        #ifdef MY_DEBUG
          Serial.println("COMPARE_BVOLT: 1");
        #endif
        if (oldBatteryV != batteryV) {
          send(msgBatt.set(batteryV, 2));
          sendBatteryLevel(batteryPcnt);
          oldBatteryV = batteryV;
        }
      } else if (myCompareBvolt == 0) {
        #ifdef MY_DEBUG
          Serial.println("COMPARE_BVOLT: 0");
        #endif
        send(msgBatt.set(batteryV, 2));
        sendBatteryLevel(batteryPcnt);
      } else {
        #ifdef MY_DEBUG
          Serial.println("COMPARE_BVOLT: > 1");
        #endif
        if (oldBatteryV != batteryV || batteryNotSentCount>=myCompareBvolt) {
          send(msgBatt.set(batteryV, 2));
          sendBatteryLevel(batteryPcnt);
          oldBatteryV = batteryV;
          batteryNotSentCount=0;
        }else{
          batteryNotSentCount++;
        }
      }
    } // end if (myenableBvolt)

    if (numTempSensors > 0) {
      // Fetch temperatures from Dallas sensors
      sensors.requestTemperatures();
      // query conversion time and sleep until conversion completed
      int16_t conversionTime = sensors.millisToWaitForConversion(sensors.getResolution());
      //sleep() call can be replaced by wait() call if node need to process incoming messages (or if node is repeater)
      sleep(conversionTime);
      #ifdef MY_DEBUG
        Serial.print("Conversion Time: ");
        Serial.println(conversionTime);
      #endif
      // Read temperatures and send them to controller
      for (int i=0; i<numTempSensors && i<MAX_ATTACHED_DS18B20; i++) {
        // Fetch and round temperature to one decimal
        float temperature = static_cast<float>(static_cast<int>((getControllerConfig().isMetric?sensors.getTempCByIndex(i):sensors.getTempFByIndex(i)) * 10.)) / 10.;
 
        // Only send data if temperature has changed and no error
        if (myCompareTemp == 1) {
          #ifdef MY_DEBUG
            Serial.println("COMPARE_TEMP: 1");
          #endif
          if (lastTemperature[i] != temperature && temperature != -127.00 && temperature != 85.00) {
            // Send in the new temperature
            send(msg.setSensor(i).set(temperature,1));
            // Save new temperatures for next compare
            lastTemperature[i]=temperature;
          }
        } else if (myCompareTemp == 0) {
          #ifdef MY_DEBUG
            Serial.println("COMPARE_TEMP: 0");
          #endif
          if (temperature != -127.00 && temperature != 85.00) {
            // Send in the new temperature
            send(msg.setSensor(i).set(temperature,1));
          }
        } else {
          #ifdef MY_DEBUG
            Serial.println("COMPARE_TEMP: > 1");
          #endif
          if ((lastTemperature[i] != temperature || temperatureNotSentCount[i]>=myCompareTemp) && temperature != -127.00 && temperature != 85.00) {
            // Send in the new temperature
            send(msg.setSensor(i).set(temperature,1));
            // Save new temperatures for next compare
            lastTemperature[i]=temperature;
            //Reset values not sent count
            temperatureNotSentCount[i]=0;
          }else{
            lastTemperature[i]=temperature;
            temperatureNotSentCount[i]++;
          }
        }
      }
    } // end if (numTempSensors > 0)
    
    //Condition to check battery levell is lower then minimum then blink led 3 times
    //if (batteryV < 2.9) { //for 18650 Battery Powered Sensor
    //if (batteryV < 2.0) { //for 2 x AAA Battery Powered Sensor

    //if (batteryV < 2.0) { //for 3 x AAA Battery Powered Sensor
      //blink_led(3, ledpin);
      //Serial.print("Low Voltage");
    //}

  } // end of sleep_counter loop, will execute eb=very 56 seconds
  
  if (myEnableSwitch) {
   // get switch state
    int value = digitalRead(BUTTON_PIN);
 
    if (value != oldValue) {
      // Send in the new value
      send(msgSwitch.set(value==HIGH ? 1 : 0));
      oldValue = value;
    }
    #ifdef MY_DEBUG
      Serial.print("Switch State: ");
      Serial.println(value);
    #endif
  }

  //go to sleep for while
  //smartSleep(SLEEP_TIME);
  sleep(SLEEP_TIME);
  if (sleep_counter++ > 56) {
    sleep_counter = 0;
  }
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

void burn8Readings(int pin)
{
  for (int i = 0; i < 8; i++)
  {
    analogRead(pin);
  }
}
