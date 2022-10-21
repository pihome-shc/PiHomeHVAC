import MySQLdb as mdb
import configparser
import datetime

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
cur.execute("SELECT COUNT(*) FROM jobs WHERE `script` = '/var/www/cron/auto_image.py'")
row_count = cur.fetchone()[0]
val = ('/var/www/cron/auto_image.py','1','0','02:00','',datetime.datetime.now(),'auto_image')
if row_count == 0:
        cur.execute("INSERT INTO jobs (`script`, `enabled`, `log_it`, `time`, `output`, `datetime`, `job_name`) VALUES (%s, %s, %s, %s, %s, %s, %s)", val)
else:
        cur.execute("UPDATE `jobs` SET `script` = (%s), `enabled` = (%s), `log_it` = (%s), `time` = (%s), `output` = (%s),`datetime` = (%s) WHERE `job_name` = (%s);", val)
con.commit()

# Check if the auto_image table exists and if not create and add the default record
cur.execute("SELECT COUNT(*) FROM information_schema.tables WHERE `table_name` = 'auto_image'")
row_count = cur.fetchone()[0]
if row_count == 0:
        query = ("CREATE TABLE `auto_image` ("
        "`id` int(11) NOT NULL AUTO_INCREMENT,"
        "`sync` tinyint(4) NOT NULL,"
        "`purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',"
        "`enabled` tinyint(4) NOT NULL,"
        "`frequency` char(50) COLLATE utf16_bin,"
        "`rotation` char(50) COLLATE utf16_bin,"
        "`destination` char(50) COLLATE utf16_bin,"
        "`email_confirmation` tinyint(4) NOT NULL,"
        "`last_image_creation` DATETIME NOT NULL,"
        "PRIMARY KEY (`id`)"
        ") ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16 COLLATE=utf16_bin")
        cur.execute(query)
        timestamp = datetime.datetime.now() - datetime.timedelta(days=21)
        val = ('1', '0', '0', '0', '1 WEEK', '2 WEEK', '/mnt/pibackups/', '0', timestamp)
        cur.execute("INSERT INTO auto_image (`id`, `sync`, `purge`, `enabled`, `frequency`, `rotation`, `destination`, `email_confirmation`, `last_image_creation`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)", val)
        con.commit()

cur.close()
con.close()
