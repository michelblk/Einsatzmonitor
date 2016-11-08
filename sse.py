#!/usr/bin/python
from socket import *
import sys
import time
import threading
import MySQLdb

class DatenbankDeamon (threading.Thread):
	def __init__ (self):
		threading.Thread.__init__(self)
		self.EinsatzEvent = threading.Event()
		dbDeamon = threading.Thread(target=self.run(), args=())
		dbDeamon.start()

	def run (self):
		db = MySQLdb.connect(host="localhost",user="daemon",passwd="feuerwehr112",db="feuerwehr")
		alarmierteID = ""
		while 1:
			cur = db.cursor(MySQLdb.cursors.DictCursor)
			cur.execute("SELECT * FROM einsaetze WHERE `zeit` >= NOW() - INTERVAL 1 HOUR AND `zeit` <= NOW() ORDER BY `zeit` DESC LIMIT 1")
			if (cur.rowcount == 0 and alarmierteID != ""):
				print "Einsatzende" #endeEinsatz
			elif (cur.rowcount == 1):
				daten = cur.fetchone()
				self.EinsatzEvent.set()
				#self.EinsatzEvent.clear()
				alarmierteID = daten["id"]
			print "[Daemon acitve]" + str(cur.rowcount)
			cur.close()
			db.commit()
			time.sleep(1)
		db.close()

	def newClient (self, client, addr):
		threading.Thread(target=self.newClientThread(client, addr))

	def newClientThread (self, conn, addr):
		print "Neue Verbindung mit: "+str(addr)
		clientHeader = conn.recv(1024).strip().split()
		if (len(clientHeader) > 1):
			path = clientHeader[1]
		else:
			path = ""
		if(path == "/sse"):
			conn.send("HTTP/1.0 200 OK\r\n")
			conn.send("Content-Type: text/event-stream;charset=UTF-8\r\n");
			conn.send("Connection: Keep-Alive\r\n")
			conn.send("Access-Control-Allow-Origin: *\r\n")
			conn.send("Transfer-Encoding: chunked\r\n")
			conn.send("Keep-Alive: 10\r\n")
			conn.send("\r\n") #End of header

			conn.send("retry: 1000\n\n")
			timeout = time.time() + 60*5
			while 1:
				self.EinsatzEvent.wait()
				print "AHHHH"
				self.einsatz(conn, addr)
		else:
			self.conn.send("HTTP/1.0 404 NOT FOUND\r\n")
			self.conn.send("\r\n")
		self.conn.close()

	def einsatz (self, conn, addr):
		print "Einsatz angekommen"


HOST = ""
PORT = 1122
if __name__ == "__main__":
	dbThread = DatenbankDeamon()
	s = socket(AF_INET, SOCK_STREAM)
	s.setsockopt(SOL_SOCKET, SO_REUSEADDR, 1)
	s.bind((HOST, PORT))
	s.listen(5)
	while 1:
		client, addr = s.accept()
		dbThread.newClient(client, addr)

