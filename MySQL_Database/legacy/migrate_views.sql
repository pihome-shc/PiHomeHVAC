Drop View if exists zone_view;
CREATE VIEW zone_view AS
select zone.status, zone.zone_state, zone.sync, zone.id, zone.index_id, zone.name, ztype.type, ztype.category, ts.graph_num, zs.min_c, zs.max_c, zs.default_c, max_operation_time, zs.hysteresis_time,
zs.sp_deadband, sid.node_id as sensors_id, ts.sensor_child_id,
ctype.`type` AS controller_type, cr.controler_id as controler_id, cr.controler_child_id,
IFNULL(lasts.last_seen, lasts_2.last_seen) as last_seen, IFNULL(msv.ms_version, msv_2.ms_version) as ms_version, IFNULL(skv.sketch_version, skv_2.sketch_version) as sketch_version
from zone
LEFT join zone_sensors zs on zone.id = zs.zone_id
LEFT join sensors ts on zone.id = ts.zone_id
LEFT join zone_controllers zc on zone.id = zc.zone_id
LEFT join relays cr on zc.controller_relay_id = cr.id
join zone_type ztype on zone.type_id = ztype.id
LEFT join nodes sid on ts.sensor_id = sid.id
LEFT join nodes ctype on cr.controler_id = ctype.id
LEFT join nodes lasts on ts.sensor_id = lasts.id
LEFT join nodes lasts_2 on cr.controler_id = lasts_2.id
LEFT join nodes msv on ts.sensor_id = msv.id
LEFT join nodes msv_2 on cr.controler_id = msv_2.id
LEFT join nodes skv on ts.sensor_id = skv.id
LEFT join nodes skv_2 on cr.controler_id = skv_2.id
where zone.`purge` = '0';

Drop View if exists system_controller_view;
CREATE VIEW system_controller_view AS
select system_controller.status, system_controller.sync, system_controller.`purge`, system_controller.active_status, system_controller.name, ctype.`type` AS controller_type, cr.controler_id, cr.controler_child_id, system_controller.hysteresis_time, system_controller.max_operation_time, system_controller.overrun, system_controller.heat_relay_id, system_controller.cool_relay_id, system_controller.fan_relay_id
from system_controller
join relays cr on system_controller.heat_relay_id = cr.id
join nodes ctype on cr.controler_id = ctype.id
where system_controller.`purge` = '0';

