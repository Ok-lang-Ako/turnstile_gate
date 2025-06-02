import serial

# Open QR reader serial port (COM5) and ESP32 serial port (COM3)
qr_reader = serial.Serial('COM5', 9600, timeout=1)
esp32 = serial.Serial('COM3', 115200, timeout=1)

print("Forwarding QR data from COM5 to ESP32 on COM3...")

try:
    while True:
        if qr_reader.in_waiting:
            data = qr_reader.readline().decode('utf-8').strip()
            if data:
                print(f"QR: {data}")
                esp32.write((data + '\n').encode('utf-8'))
except KeyboardInterrupt:
    print("Stopping bridge.")
finally:
    qr_reader.close()
    esp32.close()
