#!/usr/bin/env python3
"""
teletext_server.py
Jednostavan teletext-like TCP server.
Pokretanje: python3 teletext_server.py [host] [port]
Default: 0.0.0.0 7000
"""

import socket
import threading
import sys

HOST = sys.argv[1] if len(sys.argv) > 1 else "0.0.0.0"
PORT = int(sys.argv[2]) if len(sys.argv) > 2 else 7000
ENC = "utf-8"

# In-memory store of pages. Keys are strings like "100", "101".
pages_lock = threading.Lock()
pages = {
    "100": [
        "TELETEXT Demo - Page 100",
        "Welcome to the Teletext-like server.",
        "Use GET <page> to fetch a page.",
        "",
        "Example pages:",
        "100 - Welcome",
        "101 - News",
    ],
    "101": [
        "TELETEXT Demo - Page 101 (News)",
        "1) Local team wins 3:1",
        "2) Weather: Sunny tomorrow",
        "3) New exhibit opens at museum",
    ],
}

def handle_client(conn, addr):
    with conn:
        conn.settimeout(300)
        # simple greeting
        try:
            conn.sendall(("WELCOME TeletextServer\r\n").encode(ENC))
        except Exception:
            return

        buf = b""
        while True:
            try:
                data = conn.recv(4096)
                if not data:
                    break
                buf += data
                # process lines
                while b"\n" in buf:
                    line, buf = buf.split(b"\n", 1)
                    line = line.decode(ENC).strip()
                    if not line:
                        continue
                    # Supported commands: GET <page>, LIST, QUIT
                    parts = line.split()
                    cmd = parts[0].upper()
                    if cmd == "GET" and len(parts) >= 2:
                        page = parts[1]
                        with pages_lock:
                            content = pages.get(page, None)
                        if content is None:
                            conn.sendall(f"ERROR NotFound {page}\r\n".encode(ENC))
                            conn.sendall(b"<ENDPAGE>\r\n")
                        else:
                            # send content lines
                            conn.sendall(f"PAGE {page} BEGIN\r\n".encode(ENC))
                            for l in content:
                                # ensure CRLF
                                safe = l.replace("\r", " ").replace("\n", " ")
                                conn.sendall((safe + "\r\n").encode(ENC))
                            conn.sendall(b"<ENDPAGE>\r\n")
                    elif cmd == "LIST":
                        with pages_lock:
                            keys = sorted(pages.keys())
                        conn.sendall(("PAGES " + " ".join(keys) + "\r\n").encode(ENC))
                        conn.sendall(b"<ENDPAGE>\r\n")
                    elif cmd == "QUIT":
                        conn.sendall(b"BYE\r\n")
                        return
                    else:
                        conn.sendall(b"ERROR UnknownCommand\r\n")
                        conn.sendall(b"<ENDPAGE>\r\n")
            except socket.timeout:
                break
            except Exception as e:
                # client disconnect or other error
                break

def admin_console():
    """
    Admin console runs in separate thread and allows:
    /set <page> - start entering lines, finish with a single dot '.' on a line
    /del <page>
    /show <page>
    /list
    /quit to stop the server
    """
    print("Admin console: /set <page>, /del <page>, /show <page>, /list, /quit")
    while True:
        try:
            cmd = input("> ").strip()
        except EOFError:
            break
        if not cmd:
            continue
        parts = cmd.split(None, 1)
        a = parts[0].lower()
        arg = parts[1].strip() if len(parts) > 1 else ""
        if a == "/set" and arg:
            page = arg
            print(f"Enter content for page {page}. Finish with a single '.' on a line.")
            lines = []
            while True:
                try:
                    ln = input()
                except EOFError:
                    ln = "."
                if ln == ".":
                    break
                lines.append(ln)
            with pages_lock:
                pages[page] = lines
            print(f"Page {page} set ({len(lines)} lines).")
        elif a == "/del" and arg:
            with pages_lock:
                if arg in pages:
                    del pages[arg]
                    print(f"Deleted page {arg}.")
                else:
                    print("Not found.")
        elif a == "/show" and arg:
            with pages_lock:
                content = pages.get(arg)
            if content is None:
                print("Not found.")
            else:
                print("----")
                for l in content:
                    print(l)
                print("----")
        elif a == "/list":
            with pages_lock:
                print("Pages:", " ".join(sorted(pages.keys())))
        elif a == "/quit":
            print("Shutting down requested by admin console.")
            # exit program
            # This will not immediately close existing threads gracefully,
            # but will stop the main accept loop by exiting process.
            sys.exit(0)
        else:
            print("Unknown admin command.")

def serve_forever():
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        s.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
        s.bind((HOST, PORT))
        s.listen(50)
        print(f"Teletext server listening on {HOST}:{PORT}")
        # admin console thread
        t = threading.Thread(target=admin_console, daemon=True)
        t.start()
        try:
            while True:
                try:
                    conn, addr = s.accept()
                except OSError:
                    break
                print("Connection from", addr)
                client_t = threading.Thread(target=handle_client, args=(conn, addr), daemon=True)
                client_t.start()
        except KeyboardInterrupt:
            print("Interrupted, shutting down.")

if __name__ == "__main__":
    serve_forever()
