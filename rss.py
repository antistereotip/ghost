# -*- coding: utf-8 -*-

import socket
import time
import feedparser

# Set server and channel information
server = "localhost"
port = 6667
channel = "#test"
bot_nickname = "RSS-bot"

# Function to read news from a specified RSS feed
def read_rss_feed(url, num_items=1):
    feed = feedparser.parse(url)
    if len(feed.entries) > 0:
        news_items = feed.entries[:num_items]
        return news_items
    else:
        return []

# Create a socket for connecting to the server
irc = socket.socket(socket.AF_INET, socket.SOCK_STREAM)

# Connect to the IRC server
irc.connect((server, port))

# Log in to the IRC server with the chosen nickname
irc.send(bytes("USER {} 0 * :{}\r\n".format(bot_nickname, bot_nickname), "UTF-8"))
irc.send(bytes("NICK {}\r\n".format(bot_nickname), "UTF-8"))

# Join the specified channel
irc.send(bytes("JOIN {}\r\n".format(channel), "UTF-8"))

# Main loop for message processing
while True:
    data = irc.recv(2048).decode("UTF-8")
    print(data)  # Display all messages received on the channel

    # Respond to specific commands or messages
    if "PING" in data:
        irc.send(bytes("PONG {}\r\n".format(data.split()[1]), "UTF-8"))
    
    # Command to greet
    if "!komande" in data:
        response = "hello, vreme, rss bbc, rss cnn, !github, !help, !quit"
        irc.send(bytes("PRIVMSG {} :{}\r\n".format(channel, response), "UTF-8"))
    
    # Command to display time
    if "!vreme" in data:
        current_time = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())
        response = "Trenutno vreme je: {}\u0107".format(current_time)
        irc.send(bytes("PRIVMSG {} :{}\r\n".format(channel, response), "UTF-8"))
    
    # Command to greet
    if "!hello" in data:
        response = "Hello! Kako mogu da vam pomognem?"
        irc.send(bytes("PRIVMSG {} :{}\r\n".format(channel, response), "UTF-8"))
    
    # Command to read news from BBC
    if "!rss bbc" in data:
        rss_url = "http://feeds.bbci.co.uk/news/rss.xml"
        num_items = 10  # Read the 10 latest news items from BBC
        news_items = read_rss_feed(rss_url, num_items)
        
        for item in news_items:
            headline = item.title
            description = item.description
            irc.send(bytes("PRIVMSG {} :{}\r\n".format(channel, headline), "UTF-8"))
            irc.send(bytes("PRIVMSG {} :{}\r\n".format(channel, description), "UTF-8"))
    
    # Command to read news from CNN
    if "!rss cnn" in data:
        rss_url = "https://rss.cnn.com/rss/cnn_topstories.rss"
        num_items = 10  # Read the 10 latest news items from CNN
        news_items = read_rss_feed(rss_url, num_items)
        
        for item in news_items:
            headline = item.title
            description = item.description
            irc.send(bytes("PRIVMSG {} :{}\r\n".format(channel, headline), "UTF-8"))
            irc.send(bytes("PRIVMSG {} :{}\r\n".format(channel, description), "UTF-8"))
    
    # Command to provide GitHub repository link
    if "!github" in data:
        response = "GitHub repozitorijum za ovog bota se nalazi na: https://github.com/antistereotip/ghost/"
        irc.send(bytes("PRIVMSG {} :{}\r\n".format(channel, response), "UTF-8"))
    
    # Command to provide help information
    if "!help" in data:
        response = "Ovaj bot podržava sledeće komande: "
        response += "!komande - Prikazuje listu dostupnih komandi, "
        response += "!vreme - Prikazuje trenutno vreme, "
        response += "!rss bbc - Čita vesti sa BBC-a, "
        response += "!rss cnn - Čita vesti sa CNN-a, "
        response += "!github - Prikazuje link ka GitHub repozitorijumu bota, "
        response += "!help - Prikazuje ovu pomoć."
        irc.send(bytes("PRIVMSG {} :{}\r\n".format(channel, response), "UTF-8"))
    
    # Command to quit and exit the channel
    if "!quit" in data:
        irc.send(bytes("PRIVMSG {} :{}\r\n".format(channel, "Napuštam kanal. Doviđenja!"), "UTF-8"))
        irc.send(bytes("QUIT\r\n", "UTF-8"))
        break
