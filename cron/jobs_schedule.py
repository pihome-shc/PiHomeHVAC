#!/usr/bin/python3
# ----------------------------------------------------------------------------
#           __  __                             _
#          |  \/  |                    /\     (_)
#          | \  / |   __ _  __  __    /  \     _   _ __
#          | |\/| |  / _` | \ \/ /   / /\ \   | | |  __|
#          | |  | | | (_| |  >  <   / ____ \  | | | |
#          |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|
#
#  jobs_schedule.py
#  Job Scheduler.
#
# Author  : Terry Adams
# Date    : 09/02/2021
# Version : 0.01
#
# Copyright 2021 Terry Adams
#
# 09/02/2021 - First Release
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ----------------------------------------------------------------------------

# Schedule Library imported
import schedule
import time
import os
import configparser
import MySQLdb as mdb

# Initialise the database access variables
config = configparser.ConfigParser()
config.read("/var/www/st_inc/db_config.ini")
dbhost = config.get("db", "hostname")
dbuser = config.get("db", "dbusername")
dbpass = config.get("db", "dbpassword")
dbname = config.get("db", "dbname")

con = mdb.connect(dbhost, dbuser, dbpass, dbname)
cur = con.cursor()

# Build functions based on entries in table
def func_builder(name, script, log_it):
    def f():
        action = os.popen(script.split(".", 1)[1] + " " + script + " \n")
        action_output = action.read()
        if log_it == 1:
            file_object = open("/var/www/cron/logs/" + name + ".log", "a")
            file_object.write(action_output)
            file_object.close()
        cur.execute(
            "UPDATE `jobs` SET `output` = %s WHERE `job_name` = %s",
            (action_output, name),
        )
        con.commit()
        return name

    return f


def main():

    # Clear any existing schedules
    schedule.clear()

    # Build the new schedules
    cur.execute("SELECT `job_name`, `script`, `enabled`, `log_it`, `time` FROM `jobs`")
    for row in cur:
        if row[2] == 1:
            func_builder(row[0], row[1], row[3])()
            schedule.every(row[4]).seconds.do(func_builder(row[0], row[1], row[3]))

    while True:

        # Checks whether a scheduled task
        # is pending to run or not

        schedule.run_pending()
        time.sleep(1)


if __name__ == "__main__":
    main()
