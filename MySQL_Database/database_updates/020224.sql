ALTER TABLE `gateway_logs` ADD COLUMN IF NOT EXISTS `mqtt_sent` int(11) DEFAULT '0';
ALTER TABLE `gateway_logs` ADD COLUMN IF NOT EXISTS `mqtt_recv` int(11) DEFAULT '0';
ALTER TABLE `gateway_logs` ADD COLUMN IF NOT EXISTS `mysensors_sent` int(11) DEFAULT '0';
ALTER TABLE `gateway_logs` ADD COLUMN IF NOT EXISTS `mysensors_recv` int(11) DEFAULT '0';
ALTER TABLE `gateway_logs` ADD COLUMN IF NOT EXISTS `gpio_sent` int(11) DEFAULT '0';
ALTER TABLE `gateway_logs` ADD COLUMN IF NOT EXISTS `gpio_recv` int(11) DEFAULT '0';
ALTER TABLE `gateway_logs` ADD COLUMN IF NOT EXISTS `heartbeat` timestamp DEFAULT now();

