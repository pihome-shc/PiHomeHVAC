#!/usr/bin/env python
# =============================================================================
# Console colours
# =============================================================================
class bc:
    hed = "\033[95m"
    ENDC = '\033[0m'
    SUB = "\033[3;30;45m"
    WARN = '\033[0;31;40m'
    grn  = '\033[0;32;40m'
    wht  = '\033[0;37;40m'
    fail = '\033[91m'
    blu  = '\033[36m'
    dtm = "\033[0;36;40m"

print(bc.hed + " ")
print(r"    __  __                             _         ")
print(r"   |  \/  |                    /\     (_)        ")
print(r"   | \  / |   __ _  __  __    /  \     _   _ __  ")
print(r"   | |\/| |  / _` | \ \/ /   / /\ \   | | | '__| ")
print(r"   | |  | | | (_| |  >  <   / ____ \  | | | |    ")
print(r"   |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|    ")
print(" ")
print("             " +bc.SUB + "S M A R T   THERMOSTAT " + bc.ENDC)
print("********************************************************")
print("*  Script to read data from an eBUS Boiler interface   *")
print("*             and store in message_in queue.           *")
print("*                Build Date: 05/08/2022                *")
print("*      Version 0.03 - Last Modified 11/06/2026         *")
print("*                                 Have Fun - PiHome.eu *")
print("********************************************************")
print(" ")
print(" " + bc.ENDC)

import time
import sys
import socket
from datetime import datetime
import MySQLdb
import MySQLdb.cursors
from configparser import ConfigParser

# =============================================================================
# Constants
# =============================================================================
MAX_RETRIES      = 10
RETRY_SLEEP_BASE = 0.05
TCP_RETRIES      = 3
TCP_BACKOFF_BASE = 0.05
EBUSD_MAX_MSG_AGE = "5" # maximum age of cached ebusd message
line_len = 60; #length of seperator lines

def log_error(context, err):
    print(f"{bc.fail}[{context}] {err}{bc.ENDC}")


def log_status(timestamp, message, text):
    print(f"{bc.blu}{timestamp}{bc.wht} - {message} - {text}")

# =============================================================================
# Config
# =============================================================================
config = ConfigParser()
config.read('/var/www/st_inc/db_config.ini')
servername = config.get('db', 'hostname')
username   = config.get('db', 'dbusername')
password   = config.get('db', 'dbpassword')
dbname     = config.get('db', 'dbname')

# =============================================================================
# ebusd TCP settings
# =============================================================================
EBUSD_HOST    = '127.0.0.1'
EBUSD_PORT    = 8888
EBUSD_TIMEOUT = 1

# =============================================================================
# DB connection
# =============================================================================
_db_conn = None


def get_connection():
    global _db_conn
    try:
        if _db_conn is None:
            raise Exception("No connection")
        _db_conn.ping(True)
    except Exception:
        _db_conn = MySQLdb.connect(
            host=servername,
            user=username,
            passwd=password,
            db=dbname,
            cursorclass=MySQLdb.cursors.DictCursor
        )
    return _db_conn

# =============================================================================
# Persistent TCP
# =============================================================================
_ebusd_socket = None


def _ebusd_connect(timeout=EBUSD_TIMEOUT):
    global _ebusd_socket

    if _ebusd_socket is not None:
        return _ebusd_socket

    s = socket.create_connection(
        (EBUSD_HOST, EBUSD_PORT),
        timeout=timeout
    )

    s.settimeout(timeout)

    _ebusd_socket = s
    return s


def _ebusd_disconnect():
    global _ebusd_socket

    if _ebusd_socket is not None:
        try:
            _ebusd_socket.close()
        except Exception:
            pass

    _ebusd_socket = None


def _ebusd_tcp(command, timeout=EBUSD_TIMEOUT):
    global _ebusd_socket

    for attempt in range(TCP_RETRIES):
        try:
            s = _ebusd_connect(timeout)

            payload = f"{command}\n".encode()
            s.sendall(payload)

            chunks = []

            while True:
                chunk = s.recv(4096)

                if not chunk:
                    raise ConnectionError("ebusd closed connection")

                chunks.append(chunk)

                # ebusd responses terminate with newline
                if b'\n' in chunk:
                    break

            return b''.join(chunks).decode().strip()

        except (socket.timeout, OSError, ConnectionError) as e:

            _ebusd_disconnect()

            if attempt == TCP_RETRIES - 1:
                log_error("ebusd not running", e)
                sys.exit(0)

            time.sleep(min(0.2, TCP_BACKOFF_BASE * (2 ** attempt)))

# =============================================================================
# eBUS helpers
# =============================================================================
def normalize_response(response):
    if not response:
        return response

    response = response.strip()
    resp = response.lower()

    if resp == "off":
        return "0"

    if resp == "on":
        return "1"

    if ";" in response:
        return response.split(";", 1)[0]

    if " " in response:
        return response.split(" ", 1)[0]

    return response


def transact(command):
    counter = 0

    while counter < MAX_RETRIES:
        try:
            response = _ebusd_tcp(f'read -m {EBUSD_MAX_MSG_AGE} {command}')

            if "ERR:" not in response:
                return (0, response)

            time.sleep(min(0.2, RETRY_SLEEP_BASE * (2 ** counter)))
            counter += 1

        except socket.timeout:
            return (4, '')

        except OSError as e:
            log_error("TCP ERROR", e)
            return (6, '')

    print(f"{bc.WARN}[RETRY FAIL] {command} → {response}{bc.ENDC}")
    return (6, '')

# =============================================================================
# Weather compensation constants
# =============================================================================
A          = 2.55
B          = 0.78
T_SET      = 25.0
HEAT_CURVE = 1.2

OUT_NODE_ID    = 1
OUT_CHILD_ID   = 0

MAX_DELTA = 28.0

MIN_WRITE_INTERVAL   = 1
TEMP_WRITE_THRESHOLD = 1.0
TEMP_HYSTERESIS      = 0.2
MAX_SILENT_INTERVAL  = 600
NO_CHANGE_SUPPRESS   = 300

# =============================================================================
# SetMode state
# =============================================================================
_last_written_temp   = None
_last_write_ts       = 0.0
_last_sent_ts        = None
_target_stable_since = None
_last_stable_target  = None

# =============================================================================
# DB helper
# =============================================================================
def _get_sensor_rows(cur, *node_child_pairs):
    conditions = " OR ".join(
        ["(node_id = %s AND child_id = %s)"] * len(node_child_pairs)
    )

    params = [v for pair in node_child_pairs for v in pair]

    cur.execute(f"""
        SELECT m1.node_id, m1.child_id, m1.payload
        FROM messages_in m1
        INNER JOIN (
            SELECT node_id, child_id, MAX(id) AS max_id
            FROM messages_in
            WHERE {conditions}
            GROUP BY node_id, child_id
        ) m2 ON m1.node_id = m2.node_id
            AND m1.child_id = m2.child_id
            AND m1.id = m2.max_id
    """, params)

    result = {}

    for row in cur.fetchall():
        key = (int(row['node_id']), int(row['child_id']))

        try:
            result[key] = float(row['payload'])
        except (TypeError, ValueError):
            result[key] = None

    return result

# =============================================================================
# SetMode helpers
# =============================================================================
def _should_write(temp):
    if _last_written_temp is None:
        return True

    return abs(temp - _last_written_temp) >= TEMP_WRITE_THRESHOLD


def _can_write(now):
    return (now - _last_write_ts) >= MIN_WRITE_INTERVAL


def _force_keepalive(now):
    if _last_sent_ts is None:
        return True

    return (now - _last_sent_ts) >= MAX_SILENT_INTERVAL


def _is_target_stable_too_long(target, now):
    global _target_stable_since, _last_stable_target

    if target != _last_stable_target:
        _last_stable_target = target
        _target_stable_since = now
        return False

    if _target_stable_since is None:
        _target_stable_since = now
        return False

    return (now - _target_stable_since) >= NO_CHANGE_SUPPRESS

# =============================================================================
# eBUS send
# =============================================================================
def _send_ebus(temp):
    global _last_written_temp, _last_write_ts, _last_sent_ts

    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    try:
        if 'SetMode' in open('/etc/ebusd/en/08.bai.csv').read():
            payload = f"0;{temp};-;-;-;0;0;0;-;0;0;0"
            command = f'write -c bai SetMode {payload}'
            try:
                response = _ebusd_tcp(command)

                if response == '' or response.lower() in ('done', 'empty'):
                    now = time.time()
                    _last_written_temp = temp
                    _last_write_ts = now
                    _last_sent_ts = now

                    log_status(timestamp, "SetMode sent", str(temp) + "°C")
                    return True

                log_error("EBUS WRITE", response)
                return False

            except Exception as e:
                log_error("EBUS TCP", e)
                return False
        else:
                log_status(timestamp, "SetMode", "NOT defined in csv file.")
                return True
    except:
        log_status(timestamp, "/etc/ebusd/en/08.bai.csv", "File NOT Present.")
        return True

# =============================================================================
# Hot-water relay check
# =============================================================================
def _is_hw_relay_on(cur):
    cur.execute("""
        SELECT 1
        FROM zone_current_state
        JOIN zone ON zone_current_state.zone_id = zone.id
        WHERE zone_current_state.mode IN (61, 81)
          AND zone.type_id = 3
        LIMIT 1
    """)

    return cur.fetchone() is not None

# =============================================================================
# set_mode
# =============================================================================
def set_mode(conn):
    now = time.time()

    cur = conn.cursor()
    cur.execute("""
        SELECT `s`.`name`, `n`.`node_id`, s.sensor_child_id
        FROM `sensors` `s`
        JOIN `nodes` `n` ON `n`.`id` = `s`.`sensor_id`
        WHERE `s`.`name` = 'Boiler Flow' OR `s`.`name` = 'Boiler Return';
    """)
    for row in cur.fetchall():
        if 'Boiler Flow' in row['name']:
            flow_node_id = int(row['node_id'])
            flow_child_id = int(row['sensor_child_id'])
        else:
            return_node_id = int(row['node_id'])
            return_child_id = int(row['sensor_child_id'])

    try:
        with conn.cursor() as cur:
            rows = _get_sensor_rows(
                cur,
                (OUT_NODE_ID, OUT_CHILD_ID),
                (flow_node_id, flow_child_id),
                (return_node_id, return_child_id),
            )

            Tout = rows.get((OUT_NODE_ID, OUT_CHILD_ID))

            if Tout is None:
                log_error("SETMODE", "No outdoor temp in DB — skipping")
                return

            target = T_SET + A * ((T_SET - Tout) * HEAT_CURVE) ** B

            if _is_hw_relay_on(cur):
                target = 60.0

            flow = rows.get((flow_node_id, flow_child_id))
            ret  = rows.get((return_node_id, return_child_id))

            if flow is not None and ret is not None:
                delta = flow - ret

                if delta > MAX_DELTA:
                    clamped = ret + MAX_DELTA

                    print(
                        f"{bc.WARN}[ΔT CLAMP] flow={flow}°C ret={ret}°C "
                        f"ΔT={delta:.1f} > {MAX_DELTA} — "
                        f"target clamped {target:.1f}→{clamped:.1f}°C{bc.ENDC}"
                    )

                    target = min(target, clamped)

            target = round(target, 1)

            if _last_written_temp is not None:
                if abs(target - _last_written_temp) < TEMP_HYSTERESIS:
                    target = _last_written_temp

            keepalive = _force_keepalive(now)
            stable = _is_target_stable_too_long(target, now)

            if not _can_write(now):
                return

            if stable and not keepalive:
                return

            if _should_write(target) or keepalive:
                _send_ebus(target)

    except Exception as e:
        log_error("SETMODE", e)

# =============================================================================
# Boiler read loop
# =============================================================================
last_readings = {}
last_date_time = {}


def boiler(conn):
    global last_readings, last_date_time

    with conn.cursor() as cursor:
        cursor.execute("""
            SELECT
                em.message, em.position, em.offset, em.sensor_id,
                s.id as sensor_db_id, s.sensor_id, s.sensor_child_id,
                s.name as sensor_name, s.sensor_type_id, s.graph_num,
                s.message_in, s.mode, s.timeout,
                s.correction_factor, s.resolution,
                n.node_id
            FROM ebus_messages em
            JOIN sensors s ON s.id = em.sensor_id
            JOIN nodes   n ON n.id = s.sensor_id
        """)

        rows = cursor.fetchall()

        msg_inserts = []
        sensor_updates_1 = []
        sensor_updates_2 = []
        graph_inserts = []
        graph_deletes = set()
        node_updates = {}

        for msg in rows:
            date_time = datetime.now()

            message = msg['message']
            position = msg['position']
            offset = int(msg['offset'])
            id_ = int(msg['sensor_db_id'])
            sensor_id = int(msg['sensor_id'])
            sensor_child_id = int(msg['sensor_child_id'])
            sensor_name = msg['sensor_name']
            sensor_type_id = int(msg['sensor_type_id'])
            graph_num = int(msg['graph_num'])
            msg_in = msg['message_in']
            mode = msg['mode']
            sensor_timeout = int(msg['timeout']) * 60
            correction_factor = int(msg['correction_factor'])
            resolution = float(msg['resolution'])
            node_id = int(msg['node_id'])

            status = transact(message)

            if status[0] != 0 or not status[1]:
                timestamp = date_time.strftime("%Y-%m-%d %H:%M:%S")
                log_status(timestamp, message, "No Response")
                continue

            response = normalize_response(status[1])

            try:
                response = float(response) + offset
            except ValueError:
                print(f"{bc.fail}[PARSE ERROR] '{response}' for {message}{bc.ENDC}")
                continue

            last_val = last_readings.get(message)
            last_time = last_date_time.get(message, date_time)

            timestamp = date_time.strftime("%Y-%m-%d %H:%M:%S")

            if sensor_type_id > 2:
                if last_val != response:
                    log_status(timestamp, message, response)

                    if msg_in == 1:
                        msg_inserts.append((0, 0, node_id, sensor_child_id, position, response))

                    if position == 0:
                        sensor_updates_1.append((response, id_))
                    else:
                        sensor_updates_2.append((response, id_))

                    node_updates[sensor_id] = (timestamp, sensor_id)

                    last_readings[message] = response
                    last_date_time[message] = date_time
                else:
                    log_status(timestamp, message, "No Change")

            else:
                response += correction_factor

                tdelta = (date_time - last_time).total_seconds()

                should_update = (
                    last_val is None or
                    mode == 0 or
                    (mode == 1 and abs(response - last_val) > resolution) or
                    tdelta > sensor_timeout
                )

                if should_update:
                    log_status(timestamp, message, response)

                    if msg_in == 1:
                        msg_inserts.append((0, 0, node_id, sensor_child_id, position, response))

                    if position == 0:
                        sensor_updates_1.append((response, id_))
                    else:
                        sensor_updates_2.append((response, id_))

                    if msg_in == 1 and graph_num > 0:
                        graph_inserts.append(
                            (
                                0, 0, id_, sensor_name, "Sensor", 0,
                                node_id, sensor_child_id, 0,
                                response, timestamp
                            )
                        )

                        graph_deletes.add((node_id, sensor_child_id))

                    last_readings[message] = response
                else:
                    log_status(timestamp, message, "No Change")

                last_date_time[message] = date_time

        if msg_inserts:
            cursor.executemany("INSERT INTO messages_in(`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `payload`) VALUES (%s, %s, %s, %s, %s, %s)", msg_inserts)

        if sensor_updates_1:
            cursor.executemany("UPDATE sensors SET current_val_1 = %s WHERE id = %s LIMIT 1", sensor_updates_1)

        if sensor_updates_2:
            cursor.executemany("UPDATE sensors SET current_val_2 = %s WHERE id = %s LIMIT 1", sensor_updates_2)

        if graph_inserts:
            cursor.executemany("""INSERT INTO sensor_graphs(`sync`, `purge`, `zone_id`, `name`, `type`, `category`, `node_id`, `child_id`, `sub_type`, `payload`, `datetime`)
                                  VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)""", graph_inserts)

        if graph_deletes:
            cursor.executemany("DELETE FROM sensor_graphs WHERE node_id=%s AND child_id=%s AND datetime < CURRENT_TIMESTAMP - INTERVAL 24 HOUR", list(graph_deletes))

        if node_updates:
            cursor.executemany("UPDATE nodes SET last_seen=%s, sync=0 WHERE id=%s", list(node_updates.values()))

    # commit the above
    conn.commit()

# =============================================================================
# Main
# =============================================================================
def main():
    try:
        conn = get_connection()

        with conn.cursor() as cursor:
            cursor.execute("SELECT message FROM ebus_messages")

            now = datetime.now()
            rows = cursor.fetchall()

            for row in rows:
                msg = row['message']
                last_readings[msg] = None
                last_date_time[msg] = now

        conn.close()

    except Exception as e:
        log_error("INIT", e)

    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    print(f"{bc.blu}{timestamp}{bc.wht} - eBUS Data Capture Script Started")
    print("-" * line_len)

    while True:
        try:
            start = time.time()

            conn = get_connection()

            try:
                boiler(conn)
                set_mode(conn)
                print("-" * line_len)
            finally:
                conn.close()

            duration = time.time() - start

            if duration > 10:
                print(f"{bc.WARN}[SLOW LOOP] {duration:.2f}s{bc.ENDC}")

            sleep_for = max(0, 10 - duration)
            time.sleep(sleep_for)

        except KeyboardInterrupt:
            _ebusd_disconnect()
            sys.exit(0)

        except Exception as e:
            log_error("MAIN LOOP", e)
            time.sleep(10)


if __name__ == "__main__":
    main()
