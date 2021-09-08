import MySQLdb as mdb
import configparser
import socket

# Initialise the database access variables
config = configparser.ConfigParser()
config.read('/var/www/st_inc/db_config.ini')
dbhost = config.get('db', 'hostname')
dbuser = config.get('db', 'dbusername')
dbpass = config.get('db', 'dbpassword')
dbname = config.get('db', 'dbname')

con = mdb.connect(dbhost, dbuser, dbpass, dbname)
cur = con.cursor()

with open('./MySensors/README.md') as f:
    MySensors_version = f.readline().split("v",1)[1]

val = ("1", "0", "0", "wifi", socket.gethostbyname(socket.gethostname()), "5003", "3", "1", "0", MySensors_version, "1")
# Check if there is already a record in the gateway table
cur.execute("SELECT COUNT(*) FROM gateway WHERE 1")
row_count = cur.fetchone()[0]
if row_count == 0:
        cur.execute("INSERT INTO gateway (`status`, `sync`, `purge`, `type`, `location`, `port`, `timout`, `reboot`, `find_gw`, `version`, `enable_outgoing`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)", val)
else:
        cur.execute("UPDATE `gateway` SET `status` = (%s), `sync` = (%s), `purge` = (%s), `type` = (%s), `location` = (%s), `port` = (%s), `timout` = (%s), `reboot` = (%s), `find_gw` = (%s), `version` = (%s), `enable_outgoing` = (%s) WHERE 1 ;", val)
con.commit()
# Check if there is already a record in the job table
cur.execute("SELECT COUNT(*) FROM jobs WHERE `script` = '/var/www/cron/check_gw.php'")
row_count = cur.fetchone()[0]
val = ("/var/www/cron/check_gw.php", "1", "60", "check_gw")
if row_count == 0:
        cur.execute("INSERT INTO jobs (`script`, `enabled`, `time`, `job_name`) VALUES (%s, %s, %s, %s)", val)
else:
        cur.execute("UPDATE `jobs` SET `script` = (%s), `enabled` = (%s), `time` = (%s) WHERE `job_name` = (%s);", val)
con.commit()
cur.close()
con.close()