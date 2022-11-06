UPDATE messages_out
INNER JOIN zone_view mout ON messages_out.n_id = mout.relay_id AND messages_out.child_id = mout.relay_child_id
SET messages_out.zone_id = mout.id;
