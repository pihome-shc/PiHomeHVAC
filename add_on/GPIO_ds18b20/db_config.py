import MySQLdb as mdb
import configparser

# Initialise the database access variables
config = configparser.ConfigParser()
config.read('/var/www/st_inc/db_config.ini')
dbhost = config.get('db', 'hostname')
dbuser = config.get('db', 'dbusername')
dbpass = config.get('db', 'dbpassword')
dbname = config.get('db', 'dbname')

con = mdb.connect(dbhost, dbuser, dbpass, dbname)
cur = con.cursor()

# Check if there is already a record in the job table
cur.execute("SELECT COUNT(*) FROM jobs WHERE `script` = '/var/www/cron/check_ds18b20.php'")
row_count = cur.fetchone()[0]
val = ("/var/www/cron/check_ds18b20.php", "1", "60", "check_ds18b20")
if row_count == 0:
        cur.execute("INSERT INTO jobs (`script`, `enabled`, `time`, `job_name`) VALUES (%s, %s, %s, %s)", val)
else:
        cur.execute("UPDATE `jobs` SET `script` = (%s), `enabled` = (%s), `time` = (%s) WHERE `job_name` = (%s);", val)
con.commit()
cur.close()
con.close()