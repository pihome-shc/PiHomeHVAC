Drop View if exists messages_in_view_24h;
CREATE VIEW messages_in_view_24h AS
select id, node_id, child_id, datetime, payload
from messages_in
where datetime > DATE_SUB( NOW(), INTERVAL 24 HOUR);
