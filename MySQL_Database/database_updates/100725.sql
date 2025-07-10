UPDATE sensors SET last_seen = now() WHERE last_seen IS NULL;
UPDATE relays SET last_seen = now() WHERE last_seen IS NULL;
