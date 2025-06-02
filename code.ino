#include <WiFi.h>
#include <HTTPClient.h>
#include <Stepper.h>
#include <ArduinoJson.h>

struct Config {
  const char* ssid = "AndroidShare_RC";
  const char* password = "58923676";
  const char* serverUrl = "http://192.168.154.240/turnstile_gate/qr_receive.php";
  const int wifiRetryDelay = 5000;
  const int maxRetries = 3;
  
  // Stepper Motor Configuration - Optimized for speed
  const int stepsPerRevolution = 2048;
  const int motorSpeed = 18;      // Increased RPM (max reliable speed for 28BYJ-48)
  const int motorPins[4] = {25, 26, 27, 14};
  const int quarterRotation = 512; // 2048 / 4 = 512 steps (90°)
  
  // IR Sensor Configuration
  const int irSensorPin = 34;     // GPIO pin for IR sensor
};

Config config;
Stepper stepper(
  config.stepsPerRevolution,
  config.motorPins[0],
  config.motorPins[1],
  config.motorPins[2],
  config.motorPins[3]
);

// Global variables
unsigned long lastWifiCheck = 0;
const unsigned long WIFI_CHECK_INTERVAL = 30000;
bool personPresent = false;

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

void rotateGate(bool isEntry) {
  Serial.printf("Rotating gate %s\n", isEntry ? "IN (anti-clockwise)" : "OUT (clockwise)");
  stepper.setSpeed(config.motorSpeed);
  
  // Optimized rotation - no intermediate delays
  if (isEntry) {
    stepper.step(config.quarterRotation);  // Anti-clockwise for IN
  } else {
    stepper.step(-config.quarterRotation); // Clockwise for OUT
  }
}

bool sendToServer(const char* qrData, bool personDetected) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi disconnected");
    return false;
  }

  HTTPClient http;
  String url = String(config.serverUrl) + "?qr_data=" + String(qrData) + 
               "&direction=" + String(personDetected ? "IN" : "OUT");
  
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
    const char* direction = doc["direction"];

    if (authorized) {
      rotateGate(personDetected); // Rotate immediately after authorization
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

  // Check IR sensor
  personPresent = (digitalRead(config.irSensorPin) == LOW);

  // Read QR data
  if (Serial.available()) {
    String data = Serial.readStringUntil('\n');
    data.trim();
    
    if (validateQRData(data.c_str())) {
      sendToServer(data.c_str(), personPresent);
    }
  }
}
