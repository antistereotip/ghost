import socket
import threading

# Konfiguracija servera
HOST = '127.0.0.1'  # ili IP servera
PORT = 12345

def receive_messages(client):
    while True:
        try:
            message = client.recv(1024).decode('utf-8')
            print(message)
        except:
            print("You have been disconnected from the server")
            break

def write_messages(client):
    while True:
        message = input()
        client.send(message.encode('utf-8'))

client = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
client.connect((HOST, PORT))

username_prompt = client.recv(1024).decode('utf-8')
username = input(username_prompt)
client.send(username.encode('utf-8'))

print(client.recv(1024).decode('utf-8'))

# Thread za primanje i slanje poruka
receive_thread = threading.Thread(target=receive_messages, args=(client,))
receive_thread.start()

write_thread = threading.Thread(target=write_messages, args=(client,))
write_thread.start()
