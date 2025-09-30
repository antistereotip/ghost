import socket
import threading

# Konfiguracija servera
HOST = '0.0.0.0'  # prihvata konekcije sa svih interfejsa
PORT = 12345

clients = []  # lista konektovanih klijenata
usernames = []  # lista korisničkih imena

def broadcast(message, _client=None):
    for client in clients:
        if client != _client:
            try:
                client.send(message)
            except:
                clients.remove(client)

def handle_client(client):
    while True:
        try:
            message = client.recv(1024)
            if not message:
                break
            broadcast(message, client)
        except:
            index = clients.index(client)
            clients.remove(client)
            left_user = usernames[index]
            broadcast(f"{left_user} has left the chat.".encode('utf-8'))
            usernames.pop(index)
            break

def receive_connections():
    server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    server.bind((HOST, PORT))
    server.listen()
    print(f"Server listening on {HOST}:{PORT}")

    while True:
        client, address = server.accept()
        print(f"Connected with {str(address)}")

        client.send("Enter username: ".encode('utf-8'))
        username = client.recv(1024).decode('utf-8').strip()
        usernames.append(username)
        clients.append(client)

        print(f"Username: {username} has joined")
        broadcast(f"{username} has joined the chat.".encode('utf-8'))

        client.send("Connected to the server!".encode('utf-8'))

        thread = threading.Thread(target=handle_client, args=(client,))
        thread.start()

if __name__ == "__main__":
    receive_connections()
