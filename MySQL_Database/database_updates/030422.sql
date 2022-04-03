Drop View if exists messages_in_view_1h;
CREATE VIEW messages_in_view_1h AS
select id, node_id, child_id, datetime, payload
from messages_in
where datetime > DATE_SUB( NOW(), INTERVAL 1 HOUR)
order by id desc;
