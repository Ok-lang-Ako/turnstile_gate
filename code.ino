#include <WiFi.h>
#include <HTTPClient.h>
#include <Stepper.h>
#include <ArduinoJson.h>

// Define motor pin connections
#define IN1 25
#define IN2 26
#define IN3 27
#define IN4 14

struct Config {
  const char* ssid = "AndroidShare_RC";
  const char* password = "58923676";
  const char* serverUrl = "http://192.168.154.240/turnstile_gate/qr_receive.php";
  const int wifiRetryDelay = 5000;
  const int maxRetries = 3;
  
  // Stepper Motor Configuration - Optimized for speed
  const int stepsPerRevolution = 2048;  // Standard for 28BYJ-48
  const int motorSpeed = 15;           // RPM (increased but still reliable)
  const int halfRotation = 1024;       // 2048 / 2 = 1024 steps (180°)
  const int motorDelay = 1000;         // Delay after rotation (ms)
  
  // IR Sensor Configuration
  const int irSensorPin = 34;     // GPIO pin for IR sensor
  const int irDebounceTime = 1000; // Debounce time in milliseconds
};

Config config;
Stepper stepper(
  config.stepsPerRevolution,
  IN1, IN3, IN2, IN4  // Changed pin order to match standard configuration
);

// Global variables
unsigned long lastWifiCheck = 0;
const unsigned long WIFI_CHECK_INTERVAL = 30000;
bool personPresent = false;

// Global variables for IR sensor handling
bool lastPersonState = false;
unsigned long lastIRTriggerTime = 0;
String currentDirection = "OUT";  // Track current direction, default is OUT (no one present)

bool connectToWiFi() {
  int retryCount = 0;
  while (retryCount < config.maxRetries) {
    Serial.printf("Connecting to WiFi (Attempt %d/%d)...\n", retryCount + 1, config.maxRetries);
    WiFi.begin(config.ssid, config.password);
    
    unsigned long startAttempt = millis();
    while (WiFi.status() != WL_CONNECTED && millis() - startAttempt < config.wifiRetryDelay) {
      delay(500);
      Serial.print(".");
    }
    
    if (WiFi.status() == WL_CONNECTED) {
      Serial.println("\nConnected to WiFi");
      Serial.printf("IP: %s\n", WiFi.localIP().toString().c_str());
      return true;
    }
    Serial.println("\nConnection failed");
    retryCount++;
  }
  Serial.println("Failed to connect after max retries");
  return false;
}

void stopMotor() {
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, LOW);
  digitalWrite(IN3, LOW);
  digitalWrite(IN4, LOW);
}

void rotateGate(bool isEntry) {
  Serial.printf("Rotating gate %s\n", isEntry ? "for entry" : "for exit");
  stepper.setSpeed(config.motorSpeed);
  
  // Always rotate clockwise (positive direction)
  stepper.step(config.halfRotation);
  
  delay(config.motorDelay);  // Wait for rotation to complete
  stopMotor();  // Stop motor to prevent heating
}

bool sendToServer(const char* qrData, bool personDetected) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi disconnected");
    return false;
  }

  HTTPClient http;
  // Update direction based on IR sensor state
  currentDirection = personDetected ? "IN" : "OUT";
  String url = String(config.serverUrl) + "?qr_data=" + String(qrData) + 
               "&direction=" + currentDirection;
  
  Serial.print("Sending to server with direction: ");
  Serial.println(currentDirection);
  
  http.begin(url);
  http.setTimeout(3000);  // Reduced timeout to 3 seconds

  int httpCode = http.GET();
  bool authorized = false;

  if (httpCode == HTTP_CODE_OK) {
    String payload = http.getString();
    
    // Parse JSON response
    StaticJsonDocument<200> doc;
    DeserializationError error = deserializeJson(doc, payload);
    
    if (error) {
      Serial.print("JSON parse failed: ");
      Serial.println(error.c_str());
      return false;
    }
    
    authorized = doc["authorized"];

    if (authorized) {
      // Always rotate the same direction, just log different directions
      Serial.println(personDetected ? "Person present - Entry rotation" : "No person - Exit rotation");
      rotateGate(personDetected);
    }
  }
  
  http.end();
  return authorized;
}

bool validateQRData(const char* data) {
  return (data && strlen(data) > 0);
}

void setup() {
  Serial.begin(115200);
  delay(1000);
  Serial.println("\nQR Access System Starting...");
  
  // Initialize motor control pins
  pinMode(IN1, OUTPUT);
  pinMode(IN2, OUTPUT);
  pinMode(IN3, OUTPUT);
  pinMode(IN4, OUTPUT);
  
  // Stop motor initially
  stopMotor();
  
  // Initialize IR sensor
  pinMode(config.irSensorPin, INPUT);
  
  if (!connectToWiFi()) {
    Serial.println("Warning: WiFi initialization failed");
  }
  
  stepper.setSpeed(config.motorSpeed);
  Serial.println("System ready. Waiting for QR data...");
}

void loop() {
  // Reconnect WiFi if down
  if (millis() - lastWifiCheck >= WIFI_CHECK_INTERVAL) {
    if (WiFi.status() != WL_CONNECTED) {
      Serial.println("Reconnecting to WiFi...");
      connectToWiFi();
    }
    lastWifiCheck = millis();
  }

  // Check IR sensor with debouncing
  bool currentPersonState = (digitalRead(config.irSensorPin) == LOW);
  unsigned long currentTime = millis();

  // Handle IR sensor detection with debouncing
  if (currentPersonState != lastPersonState && 
      (currentTime - lastIRTriggerTime) > config.irDebounceTime) {
    
    if (currentPersonState) {
      Serial.println("IR Sensor: Person detected - Ready for entry scan");
      currentDirection = "IN";
    } else {
      Serial.println("IR Sensor: No person detected - Ready for exit scan");
      currentDirection = "OUT";
    }
    lastPersonState = currentPersonState;
    lastIRTriggerTime = currentTime;
  }

  // Read QR data
  if (Serial.available()) {
    String data = Serial.readStringUntil('\n');
    data.trim();
    
    if (validateQRData(data.c_str())) {
      // Send current direction state with QR data
      sendToServer(data.c_str(), currentPersonState);
    }
  }
}
