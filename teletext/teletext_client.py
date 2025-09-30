#!/usr/bin/env python3
"""
teletext_client.py
Jednostavni klijent za teletext_server.py
Upotreba: python3 teletext_client.py [host] [port]
Default: localhost 7000
Komande (u klijentu): GET <page>, LIST, QUIT
"""

import socket
import sys

HOST = sys.argv[1] if len(sys.argv) > 1 else "127.0.0.1"
PORT = int(sys.argv[2]) if len(sys.argv) > 2 else 7000
ENC = "utf-8"

def recv_until_end(sock):
    """Prima linije dok ne stigne <ENDPAGE>"""
    data = b""
    sock.settimeout(10)
    while True:
        chunk = sock.recv(4096)
        if not chunk:
            break
        data += chunk
        if b"<ENDPAGE>\r\n" in data or b"<ENDPAGE>\n" in data:
            break
    return data.decode(ENC, errors="replace")

def interactive():
    with socket.create_connection((HOST, PORT)) as s:
        # read greeting
        try:
            greet = s.recv(4096).decode(ENC)
            if greet:
                print(greet.strip())
        except Exception:
            pass
        while True:
            try:
                cmd = input("teletext> ").strip()
            except EOFError:
                cmd = "QUIT"
            if not cmd:
                continue
            s.sendall((cmd + "\n").encode(ENC))
            if cmd.upper().startswith("QUIT"):
                print("Closing connection.")
                break
            # read response until <ENDPAGE>
            resp = recv_until_end(s)
            # strip the end marker and print
            resp = resp.replace("<ENDPAGE>\r\n", "").replace("<ENDPAGE>\n", "")
            print(resp.strip())
        try:
            s.close()
        except Exception:
            pass

if __name__ == "__main__":
    interactive()
