UPDATE `mqtt_node_child`, `nodes`
SET `mqtt_node_child`.node_id = `nodes`.id
WHERE `nodes`.node_id REGEXP '^-?[0-9]+$' AND (`mqtt_node_child`.node_id = `nodes`.node_id);
ALTER TABLE `mqtt_node_child` CHANGE node_id nodes_id int(11);
ALTER TABLE `mqtt_node_child` ADD CONSTRAINT `FK_mqtt_node_child_nodes` FOREIGN KEY (`nodes_id`) REFERENCES `nodes`(`id`);

