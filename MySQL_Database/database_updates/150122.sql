Drop View if exists zone_view;
CREATE VIEW zone_view AS
select zone.status, zone.zone_state, zone.sync, zone.id, zone.index_id, zone.name, ztype.type, ztype.category, ts.graph_num, zs.min_c, zs.max_c, zs.default_c, max_operation_time,
zs.hysteresis_time, zs.sp_deadband, sid.node_id as sensors_id, ts.sensor_child_id, ts.sensor_type_id,
ctype.`type` AS relay_type, r.relay_id, r.relay_child_id, r.type AS r_type,
IFNULL(lasts.last_seen, lasts_2.last_seen) as last_seen, IFNULL(msv.ms_version, msv_2.ms_version) as ms_version, IFNULL(skv.sketch_version, skv_2.sketch_version) as sketch_version
from zone
LEFT join zone_sensors zs on zone.id = zs.zone_id
LEFT join sensors ts on zone.id = ts.zone_id
LEFT join zone_relays zr on zone.id = zr.zone_id
LEFT join relays r on zr.zone_relay_id = r.id
join zone_type ztype on zone.type_id = ztype.id
LEFT join nodes sid on ts.sensor_id = sid.id
LEFT join nodes ctype on r.relay_id = ctype.id
LEFT join nodes lasts on ts.sensor_id = lasts.id
LEFT join nodes lasts_2 on r.relay_id = lasts_2.id
LEFT join nodes msv on ts.sensor_id = msv.id
LEFT join nodes msv_2 on r.relay_id = msv_2.id
LEFT join nodes skv on ts.sensor_id = skv.id
LEFT join nodes skv_2 on r.relay_id = skv_2.id
where zone.`purge` = '0';
