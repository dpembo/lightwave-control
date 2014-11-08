//______________________________________________________________________________
//
// Lightwave RF Control
// (C)2014 Pembo.co.uk
//
// Receives commands over serial USB from associated PHP web app
// Parses command and transmits to a lightwave RF device
//
// Hardware Configured as follows:
// D13 - Transmitter LED
// D03 - 434MHz Transmitter
// A04 - I2C LCD Display
// A05 - I2C LCD Display
//
// Due to the LCD/I2C Device used, this needs a particular implementation of the
// Library
// Also requires the Lightwave RF library

//______________________________________________________________________________
//
// Includes
//______________________________________________________________________________

// Include the Lightwave RF Library
#include <LightwaveRF.h>

/* Include the SPI/IIC Library */
#include <Wire.h>

// Include the LCD I2C Library if required
#include <LiquidCrystal_I2C.h>

//______________________________________________________________________________
//
// Macros
//______________________________________________________________________________

#if defined(ARDUINO) && ARDUINO >= 100
#define printByte(args)  write(args);
#else
#define printByte(args)  print(args,BYTE);
#endif

//______________________________________________________________________________
//
// Global Variables
//______________________________________________________________________________

// Check/Tick Character for LCD Power On Display
uint8_t check[8] = {0x0,0x1,0x3,0x16,0x1c,0x8,0x0};

// Cross character for LCD Power off Display
uint8_t cross[8] = {0x0,0x1b,0xe,0x4,0xe,0x1b,0x0};

// Block character for LCD Dim Diplay
byte block[8] = {
  B00000,
  B11111,
  B11111,
  B11111,
  B11111,
  B11111,
  B11111,
  B00000};
  
// Spinner Charater for action '|' 
byte spin1[8] = {
  B00100,
  B00100,
  B00100,
  B00100,
  B00100,
  B00100,
  B00100,
  B00100};

// Spinner Character for action '/'
byte spin2[8] = {
  B00001,
  B00010,
  B00010,
  B00100,
  B00100,
  B01000,
  B01000,
  B10000};

// Spinner Character for action '-'
byte spin3[8] = {
  B00000,
  B00000,
  B00000,
  B11111,
  B00000,
  B00000,
  B00000,
  B00000};

// Spinner Character for action '\'
byte spin4[8] = {
  B10000,
  B01000,
  B01000,
  B00100,
  B00100,
  B00010,
  B00010,
  B00001};

char spinner = '\\';

//Lightwave Control Code for power on and off
byte on[]            = {0xf6,0xf6,0xf6,0xee,0,0,0,0,0,0};
byte off[]           = {0xf6,0xf6,0xf6,0xf6,0,0,0,0,0,0};

// min/max values for the dimmer setting
int low = 64;
int high = 95;

//Adjustment factor to apply to DIM level to 0/1 base it
int dimLevelAdjust = low - 1;

// Variables to hold the values received over serial from the
// web application
char readText[22];
char readDesc[16];
char transmitterID[19];
char onOff[2];
char dimLevel[3];

//Whether to provide additional output
boolean debug=false;

/* Initialise the LiquidCrystal library. The default address is 0x27 
   and this is a 20 x 4 line display */
LiquidCrystal_I2C lcd(0x27,20,4);

byte id[6];
char readChar = ' ';

//______________________________________________________________________________
//
// Methods/functions
//______________________________________________________________________________


//______________________________________________________________________________________________________
/**
 * Initialise the hardware including:
 * 1) LED Output on Pin 13
 * 2) LCD Display, offset and dimenstion (0x27, 20 wide, 4 tall)
 * 3) Serial input baud 9600
 * 4) Lightwave RF transmitter init
 */
void setup() 
{
  // Turn on Pin 13 for Transmitting LED
  pinMode(13, OUTPUT); 
 
  // Initialise the LCD and character 
  lcd.init();
  lcd.backlight();  
  lcd.createChar(0, check);
  lcd.createChar(1, cross);
  lcd.createChar(2, block);
  
  lcd.createChar(3, spin1);
  lcd.createChar(4, spin2);
  lcd.createChar(5, spin3);
  lcd.createChar(6, spin4);

  lcd.home();
  
  lcd.print("Lightwave RF Remote");
  lcd.setCursor(0,1);
  lcd.print("Pembo.co.uk");
  lcd.setCursor(0,2);
  lcd.print("Initialising...   ");
  
  Serial.begin(9600);
  if(debug)
  {
    Serial.println("~~");
    Serial.println(">> Lightwave RF Remote Power On");
    Serial.println(">> (C)2014 Pembo.co.uk");
    Serial.print  (">> Initialising 434MHz Transmitter.... ");
  }
  
  //Setup the lightwave Device
  lw_setup();
  
  //Print the tick character to show it's initialised
  lcd.printByte(0);
  
  if(debug)
  {
    Serial.println("Done");
    delay(2000);    
  }
  
  lcd.clear();
  
  if(debug)
  {
    Serial.println(">> Command Format #[i18 TransitterID][0/1][00-32]");
    Serial.println(">> Waiting for command");
  }
  lcdWait();
}

//______________________________________________________________________________________________________
/**
 * Prints a rotating spinner character onto the LCD top right corner
 */
void lcdPrintSpinner()
{
  lcd.setCursor(19,0);
  if(spinner=='\\') 
  {
    spinner='|';
    lcd.write(byte(3));
  }
  else if(spinner=='|') 
  {
    spinner='/';
    lcd.write(byte(4));
  } 
  else if(spinner=='/')
  {
    spinner='-';
    lcd.write(byte(5));
  }
  else if(spinner=='-')
  {
    spinner='\\';
    lcd.write(byte(6));
  }
}

//______________________________________________________________________________________________________
/**
 * Prints the waiting message to the LCD screen
 */
void lcdWait()
{
  lcd.clear();
  lcd.home();
  lcd.print("Lightwave RF Remote");
  lcd.setCursor(0,1);
  lcd.print("Pembo.co.uk");
  lcd.setCursor(0,2);
  lcd.print("====================");
  lcd.setCursor(0,3);
  lcd.print("Waiting for Command?");
}

//______________________________________________________________________________________________________
/**
 * Prints a 'bar chart' to show the DIM value on the LCD when supplied in a command
 */
void lcdPrintDim()
{
  lcd.setCursor(1,3);

  boolean dim=false;  
  int onOffInt = atoi(onOff);
  int dimLevelInt = atoi(dimLevel);
  if(dimLevelInt>32)dimLevelInt = 32;
  if(dimLevelInt > 0 && onOffInt==1) dim = true;
  int showVal = dimLevelInt / 2;
  
  if(dim)
  {
    lcd.print("|");  
    lcd.setCursor(2,3);
    lcd.write(byte(2));
    for(int i=1;i<=showVal;i++)
    {
      lcd.setCursor(i+1,3);
      //lcd.print("#");
      lcd.write(byte(2));
    }
    lcd.setCursor(17,3);
    lcd.print("|");  
  }
}

//______________________________________________________________________________________________________
/**
 * Prints the command being issued to the LCD display
 */
void lcdCommandIssuing()
{
   lcd.clear();
   lcd.home();
   lcd.print("Sending Command:");
   lcd.setCursor(1,1);
   lcd.print(readDesc);
   
   lcd.setCursor(1,2);
   
   int onOffInt = atoi(onOff);
   int dimLevelInt = atoi(dimLevel);

   boolean dim = false;
   if(dimLevelInt>32)dimLevelInt = 32;
   if(dimLevelInt > 0 && onOffInt==1) dim = true;
  
   if(onOffInt==1)
   {
     if(dim)lcd.print("Dim Level");
     else
     {
       lcd.printByte(0);
       lcd.setCursor(4,2);
       lcd.print("Power on");
     }
   }
   else
   {
     lcd.printByte(1);
     lcd.setCursor(4,2);
     lcd.print("Power off");
   }
   lcdPrintDim();
}

//______________________________________________________________________________________________________
/**
 * checks for an available command supplied by serial over USB, starting with a '#'
 * Command supplied in the following format:
 *
 * Single Command
 * +-----------------+--------+-----------+--------------------+--------------------------------------+
 * | Description     | Length | Values    | Example            | Notes                                |
 * +-----------------+--------+-----------+--------------------+--------------------------------------+
 * | Start           | 1      | #         | #                  | always starts with a #               |
 * | Transmitter ID  | 18     | int       | 111235190237183123 | Up to 18                             |
 * | On/Off          | 1      | [0/1]     | 1                  |(1=on, 0=off)                         |
 * | Dim Level       | 2      | [00-32]   | 31                 | (32=max dim, 01=min dim) 00 Ignore   |
 * | Description     | [1-15] | [aA-zZ]   | Lounge             | desription                           |
 * | End             | 1      | *         | *                  | always ends with a *                 |
 * +-----------------+--------+-----------+--------------------+--------------------------------------+ 
 *
 * Queue Command
 * +-----------------+--------+-----------+--------------------+--------------------------------------+
 * | Description     | Length | Values    | Example            | Notes                                |
 * +-----------------+--------+-----------+--------------------+--------------------------------------+
 * | Start           | 1      | +         | +                  | always starts with a +               |
 * | On/Off          | 1      | [0/1]     | 1                  |(1=on, 0=off)                         |
 * | Description     | [1-15] | [aA-zZ]   | Lounge             | desription                           |
 * | Transmitter strt| 1      | ;         | ;                  | signifies transmitter start          |
 * | Transmitter ID  | 18     | int       | 111235190237183123 | Up to 18                             |
 * | Separator       | 1      | ,         | ,                  | ,                                    |
 * | Transmitter ID  | 18     | int       | 111235190237183123 | Up to 18                             |
 * | Separator       | 1      | ,         | ,                  | ,                                    |
 * | Transmitter ID  | 18     | int       | 111235190237183123 | Up to 18                             |
 * | Transmitter End | 1      | *         | *                  | *                                    |
 * +-----------------+--------+-----------+--------------------+--------------------------------------+ 

 * If any time a # or + character is received, the command reading is started again 
 * Also if any time a @ character is recieved, this will toggle debug mode supplying output over
 * the serial usb connect at 9600 baud
 *
 * If no comand is received after 10 seconds, turn off the LCD Backlight
 * On recieving a command turn on the backlight of the LCD display
 * and read the command
 *
 * Then show command on LCD Display
 * Light up LED on pin 13
 * Print the spinner
 * Transmit command 
 */
void commandAvailable()
{
  int lcdPowerOffCount = 0;
  int powerOff=1000;
  
  //Loop until we find a #
  //If read a @ switch serial to debug mode
  while(readChar!='#' && readChar !='+')
  {
    lcdPowerOffCount ++;
    if(Serial.available())
    {
      lcd.backlight();
      lcdPowerOffCount = 0;
      readChar = Serial.read();
      if(readChar=='@')
      {
        debug = !debug;
        Serial.print("Debug: ");
        Serial.println(debug);
      }
    }
    delay(10);
    
    //Power of the backlight if nothing has been received
    //over serial for 10 seconds
    if(lcdPowerOffCount == powerOff)
    {
      lcd.noBacklight();
      lcdPowerOffCount = 0;
    }
  }

  if(readChar=='#')singleCommand();
  if(readChar=='+')queueCommand();
  
}

void queueCommand()
{
  //Found a # Assume the start of a new command
  if(debug)Serial.println("New Queue Command Starting");

  int onCount = 0;
  readChar=' ';
  
  //A queue is either all on, or all off
  
  //Already read the +
  //so it's 16 chars for the id then a ,
  //repeating till find a ;
  //then a 1 or a 0 (on/off)
  //then a description
  //Ending with a *  
    
  boolean readingTransmitters = false;
  boolean readingDescription = false;
  int descCount = 0;
  
  readDesc[0]=' ';
  readDesc[1]=' ';
  readDesc[2]=' ';
  readDesc[3]=' ';
  readDesc[4]=' ';
  readDesc[5]=' ';
  readDesc[6]=' ';
  readDesc[7]=' ';
  readDesc[8]=' ';
  readDesc[9]=' ';
  readDesc[10]=' ';
  readDesc[11]=' ';
  readDesc[12]=' ';
  readDesc[13]=' ';
  readDesc[14]=' ';
  readDesc[15]='\0';
  
  while (readChar != '#' && readChar != '+' && readChar !='*')
  {
    if(Serial.available())
    {
      readChar = Serial.read();
    
      if(readingTransmitters)
      {
              
        if(readChar==',')
        {
          if(debug)Serial.println(">>End of Transmitter Id");
          transmitterID[18]='\0';
          //end of a transmission ID
          onCount = 0;

          dimLevel[0]='0';
          dimLevel[1]='0';
          dimLevel[2]='\0';

          processIndividualCommand();
  
        }
        else if(readChar=='*')
        {
          if(debug)Serial.print(">>End of Final Transmitters");
          processIndividualCommand();
          readingTransmitters = false;
          readingDescription = false;
        }
        else
        {
          transmitterID[onCount] = readChar;
          
          if(debug)Serial.print(onCount);
          if(debug)Serial.print(":");
          if(debug)Serial.println(readChar);
          onCount++;  
  
          if(onCount>=18)onCount = 0;
      
  
        }
     
      }
      else if(readingDescription)
      {
        if(debug)Serial.println("Reading desc");
        
        if(readChar!=';')
        {        
          if(descCount<=15)
          {
            readDesc[descCount] = readChar;
            descCount++;
          }
          readDesc[16]='\0';
        }
        else
        {
          readingDescription=false;
          readingTransmitters=true;
        }
        
      }
      else
      {
        //Reading switch
        if(debug)Serial.println(">>Reading on/off");
        onOff[0]=readChar;
        onOff[1]='\0';
 
        readingDescription = true;
        readingTransmitters = false;  
      }
    }
    
    
    
  }
  
  if(readChar =='*')
  {
    //Command finised successfully...
    
  }
  else
  {
    //Command transmission retarted...
  }
  
}

void singleCommand()
{
  int onCount = 0;
  
  //Found a # Assume the start of a new command
  if(debug)Serial.println("New Command Starting");
  readChar=' ';
  
  //Read 21 chars
  while (onCount <= 20)
  {
      if(Serial.available())
      {
        readChar = Serial.read();
        //if a # is read, restart the command reading again!
        if(readChar=='#') return;
        
        readText[onCount] = readChar;
        
        if(debug)Serial.print(onCount);
        if(debug)Serial.print(":");
        if(debug)Serial.println(readChar);
        onCount++;
      }
      delay(10);
  }
  
  //Now reset the read description characters
  readChar = ' ';
  int descCount = 0;
  readDesc[0]=' ';
  readDesc[1]=' ';
  readDesc[2]=' ';
  readDesc[3]=' ';
  readDesc[4]=' ';
  readDesc[5]=' ';
  readDesc[6]=' ';
  readDesc[7]=' ';
  readDesc[8]=' ';
  readDesc[9]=' ';
  readDesc[10]=' ';
  readDesc[11]=' ';
  readDesc[12]=' ';
  readDesc[13]=' ';
  readDesc[14]=' ';
  readDesc[15]='\0';
  //Finally read the description until a * is provided
  //If more than 16 chars are read, this will currently loop around back
  //to the start, so the description should be limited to 16 characters
  while (readChar != '*')
  {
    if(Serial.available())
    {
      readChar = Serial.read();
      if(readChar=='#' || readChar=='+') return;
      if(readChar!='*')
      {
        if(descCount<=15)
        {
          readDesc[descCount] = readChar;
          descCount++;
        }
      }
    }
  }
  readDesc[16]='\0';
 
  //Command now completely recieved. 
  if(debug)Serial.println(">> Command Received");
  int i=0;
  
  //Set the transmitter ID
  for(i=0;i<=17;i++)
  {
    transmitterID[i]=readText[i];
  }
  transmitterID[18]='\0';
  
  //Set On Off Value
  onOff[0]=readText[18];
  onOff[1]='\0';
  
  //Set Dim Value
  dimLevel[0]=readText[19];
  dimLevel[1]=readText[20];
  dimLevel[2]='\0';
    
  processIndividualCommand();
}


void processIndividualCommand()
{
  
  int i =0;
  if(debug)
  {  
    Serial.println(transmitterID);
    Serial.println(onOff);
    Serial.println(dimLevel);
  }
  
    //convert the Id to nibbles
  char nibble[4];
  int nibbleCount = 0;
  int byteCount = 0;
  for(i=0;i<=17;i++)
  {
    nibble[nibbleCount]=transmitterID[i];
    nibbleCount++;
    
    if(nibbleCount==3)
    {
      nibble[nibbleCount]='\0';
      nibbleCount = 0;
      id[byteCount]=atoi(nibble);
      byteCount++;      
    }
  }
  nibble[1]=transmitterID[1];
  nibble[2]=transmitterID[2];
  nibble[3]='\0';

  
  boolean dim=false;
  
  //Adjust the dim value to limit it and add the offset
  int onOffInt = atoi(onOff);
  int dimLevelInt = atoi(dimLevel);
  if(dimLevelInt>32)dimLevelInt = 32;
  
  if(dimLevelInt > 0 && onOffInt==1) dim = true;
  dimLevelInt += dimLevelAdjust;
  
  //show the issueing command on the LCD display
  lcdCommandIssuing();
  
  int count=0;
  
  //Transmit the DIM or on/off command
  //For each command, send 4 times with a 25ms delay to try and ensure
  //the command is received.  Flash LED on pin 13 as transmitting and update
  //the spinner character on the LCD display
  //On finish change the LCD to the wait command
  if(dim)
  {
    //Is a DIM Command
    digitalWrite(13, HIGH);
    //Repeat send 4 times with a 25ms delay
    while(count<=3)
    {
      lcdPrintSpinner();
      lw_cmd(0x80 + dimLevelInt,6,LW_ON,id);
      digitalWrite(13, LOW);
      delay(25);
      digitalWrite(13, HIGH);
      count++;
    }
    digitalWrite(13, LOW);
  }
  else
  {
    // Is an On Off command
    byte *msg = on;
    if(onOffInt==1)
    {
      msg = on;
    }
    else
    {
      msg = off;
    }
    msg[4]=id[0];
    msg[5]=id[1];
    msg[6]=id[2];
    msg[7]=id[3];
    msg[8]=id[4];
    msg[9]=id[5];
  
    digitalWrite(13, HIGH);
    while(count<=3)
    {
      lcdPrintSpinner();
      lw_send(msg); 
      digitalWrite(13, LOW);
      delay(25);
      digitalWrite(13, HIGH);
      count++;
    }
    digitalWrite(13, LOW);
  }
  lcdPrintSpinner();
  lcdWait();  
}

//______________________________________________________________________________________________________

/**
 * Main arduino loop
 * Just pass control to the command availale method
 */
void loop()
{
  commandAvailable();
    //queueCommand();
}
