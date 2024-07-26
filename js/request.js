// request function used for every function below.
function request(url, method, data, callback) {
	var http = new XMLHttpRequest;
	if (!http)
		return false;
	var _data;
	if (data != null && typeof data == "object") {
		_data = [];
		for (var i in data)
			_data.push(i + "=" + data[i]);
		_data = _data.join("&");
	} else {
		_data = data;
	}
	method = method.toUpperCase();
	if (method == "POST") {
		http.open(method, url, true);
		http.setRequestHeader("Method", "POST "+url+" HTTP/1.1");
		http.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	} else {
		if (_data)
			url += _data;
		_data = "";
		http.open(method, url, true);
	}
	if (callback)
		http.onreadystatechange = function() {
			if (http.readyState == 4) {
				http.onreadystatechange = function(){};
				callback(http, data);
			}
		};
	http.send(_data);
	return http;
}

//delete Zone 
function delete_zone(wid){
	var quest = "?w=zone&o=delete&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php?s_id=5&zone_deleted"; } );
}

//activate and deactivate holidays schedule 
function active_holidays(wid){
	var quest = "?w=holidays&o=active&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ $('#holidayslist').load('holidayslist.php'); } );
}

//delete holidays
function delete_holidays(wid){
        var quest = "?w=holidays&o=delete&wid=" + wid + "&frost_temp=0";
        request('db.php', 'GET', quest, function(){ $('#holidayslist').load('holidayslist.php'); } );
}

//activate and deactivate schedule 
function active_schedule(wid){
	var quest = "?w=schedule&o=active&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ $('#schedulelist').load('schedulelist.php'); } );
}

//activate and deactivate schedule 
function schedule_zone(wid){
	var quest = "?w=schedule_zone&o=active&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ $(`#sdtz_` + wid).load("ajax_fetch_data.php?id=" + wid + "&type=20").fadeIn("slow"); } );
}

//delete schedule 
function delete_schedule(wid){
	var quest = "?w=schedule&o=delete&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ $('#schedulelist').load('schedulelist.php'); } );
}

//activate and deactivate override 
function active_override(wid){
	var quest = "?w=override&o=active&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ $('#overridelist').load('overridelist.php'); } );
}

//activate and deactivate boost 
function active_boost(wid){
	var quest = "?w=boost&o=active&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ $('#boostlist').load('boostlist.php'); } );
}

//activate and deactivate away 
function active_away(){
	var quest = "?w=away&o=active" + "&frost_temp=0" + "&wid=0";
	request('db.php', 'GET', quest, function(){ $('#homelist').load('homelist.php'); } );
}

//toggle hvac mode
function active_sc_mode(){
        var quest = "?w=sc_mode&o=active" + "&frost_temp=0" + "&wid=0";
        request('db.php', 'GET', quest, function(){ $('#homelist').load('homelist.php'); } );
}

//set system controller mode
function set_sc_mode(wid){
    var idata="w=sc_mode&o=update";
    idata+="&wid=" + wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_sc_mode: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update add_on
function update_add_on(wid){
    var idata="w=add_on&o=update&wid=" + wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_add_on: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//toggle add_on
function toggle_add_on(wid){
    var idata="w=add_on&o=toggle&wid=" + wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(!odata.Success)
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("toggle_add_on: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update units
function update_units(){
    var idata="w=units&o=update";
    idata+="&val="+$("#new_units").val();
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_units: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update system mode
function update_system_mode(){
    var idata="w=system_mode&o=update";
    idata+="&val="+$("#new_mode").val();
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_system_mode: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update language
function update_lang(){
    var idata="w=lang&o=update";
    idata+="&lang_val="+$("#new_lang").val();
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_lang: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update system controller settings
function system_controller_settings(wid){
var idata="w=system_controller_settings&o=update&status="+document.getElementById("checkbox2").checked;
    idata+="&name="+document.getElementById("name").value;
	idata+="&heat_relay_id="+document.getElementById("heat_relay_id").value;
        if(wid == 1) {
		idata+="&cool_relay_id="+document.getElementById("cool_relay_id").value;
                idata+="&fan_relay_id="+document.getElementById("fan_relay_id").value;
	} else {
                idata+="&overrun="+document.getElementById("overrun").value;
                idata+="&weather_factoring="+document.getElementById("weather_factoring").value;
                idata+="&weather_sensor_id="+document.getElementById("weather_sensor_id").value;
	}
	idata+="&hysteresis_time="+document.getElementById("hysteresis_time").value;
	idata+="&max_operation_time="+document.getElementById("max_operation_time").value;
    idata+="&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=4"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("system_controller_settings: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Add Boost
function add_boost(wid){
var idata="w=boost&o=add&zone_id="+document.getElementById("zone_id").value;
	idata+="&boost_temperature="+document.getElementById("boost_temperature").value;
	idata+="&boost_time="+document.getElementById("boost_time").value;
	if(wid==0) {
		idata+="&boost_console_id="+document.getElementById("boost_console_id").value;
		idata+="&boost_button_child_id="+document.getElementById("boost_button_child_id").value;
	} else {
                idata+="&boost_console_id=0";
                idata+="&boost_button_child_id=0";
	}
    idata+="&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=4"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("add_boost: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete Boost 
function delete_boost(wid){
var idata="w=boost&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=4"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_boost: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update boost settings
function update_boost(){
//var x = document.getElementsByTagName("input");
var x = document.getElementById("boost_setup").querySelectorAll("input");
var i;
var idata="w=boost&o=update";
    for (i = 0; i < x.length; i++) {
        idata+="&"+x[i].id+"="+x[i].value;
    }
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=4"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_boost: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Add Offset
function add_offset(){
var idata="w=offset&o=add&schedule_daily_time_id="+document.getElementById("schedule_daily_time_id").value;
	idata+="&status="+document.getElementById("checkbox5").checked;
        idata+="&low_temperature="+document.getElementById("low_temperature").value;
        idata+="&high_temperature="+document.getElementById("high_temperature").value;
        idata+="&start_time_offset="+document.getElementById("start_time_offset").value;
        idata+="&sensor_id="+document.getElementById("sensor_id").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("add_offset: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update offset settings
function update_offset(){
var x = document.getElementById("offset_setup").querySelectorAll("input");
var i;
var idata="w=offset&o=update";
    for (i = 0; i < x.length; i++) {
        if(x[i].name == "offset_enabled")
             idata+="&"+x[i].id+"="+x[i].checked;
        else
             idata+="&"+x[i].id+"="+x[i].value;
    }
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_offset: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete Offset
function delete_offset(wid){
var idata="w=offset&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_offset: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Add Node
function add_node(){
var idata="w=node&o=add&node_type="+document.getElementById("node_type").value;
	idata+="&add_node_id="+document.getElementById("add_node_id").value;
	idata+="&nodes_max_child_id="+document.getElementById("nodes_max_child_id").value;
    idata+="&node_name="+document.getElementById("node_type").value;
    if (document.getElementById("node_type").value == "MQTT")
        idata+=" "+document.getElementById("mqtt_type").value;
    else
        if(document.getElementById("node_type").value == "Dummy")
                idata+=" "+document.getElementById("dummy_type").value;
        else if(document.getElementById("node_type").value == "Switch")
                idata+=" GPIO";
	    else 
                idata+=" Controller";
    idata+="&notice_interval=0";
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=5"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("add_node: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete Node
function delete_node(wid){
var idata="w=node&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=5"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_node: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete Sensor
function delete_sensor(wid){
var idata="w=sensor&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=6"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_sensor: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete Relay
function delete_relay(wid){
var idata="w=relay&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=6"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_relay: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete MQTT Device
function delete_mqtt_device(wid){
var idata="w=mqtt_device&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=6"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_mqtt_device: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Add Zone Type
function add_zone_type(){
var idata="w=zone_type&o=add&zone_type="+document.getElementById("zone_type").value;
    idata+="&zone_category="+document.getElementById("category").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=5"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("add_zone_type: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete Zone Type
function delete_zone_type(wid){
var idata="w=zone_type&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=5"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_zone_type: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Add Sensor Type
function add_sensor_type(){
var idata="w=sensor_type&o=add&sensor_type="+document.getElementById("sensor_type").value;
    idata+="&sensor_units="+document.getElementById("sensor_units").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=6"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("add_sensor_type: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete Sensor Type
function delete_sensor_type(wid){
var idata="w=sensor_type&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=6"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_sensor_type: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

function reload_page()
{
    var loc = window.location;
    /*console.log(loc.protocol);
    console.log(loc.host);
    console.log(loc.pathname);
    console.log(loc.search);
    console.log(loc.protocol + '//' + loc.host + loc.pathname);*/
    window.location.href=loc.protocol + '//' + loc.host + loc.pathname;
}

//delete user account 
function del_user(wid){
	var quest = "?w=user&o=delete&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php?del_user"; });
}
//reboot pi
function reboot() {  
  	var quest = "?w=reboot" + "&o=0" + "&frost_temp=0" + "&wid=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php?reboot"; });
    //window.location="settings.php?status=reboot";  
}

//shutdown Pi
function shutdown() {  
  	var quest = "?w=shutdown" + "&o=0" + "&frost_temp=0" + "&wid=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php?shutdown"; });
    //window.location="settings.php?status=reboot";  
}

//start database backup <--- this function need some work.
function db_backup() {
  	var quest = "?w=db_backup" + "&o=0" + "&frost_temp=0" + "&wid=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php?s_id=2"; });
    //window.location="settings.php?status=reboot";
}

//update backup email adress 
function backup_email_update(){
var idata="w=backup_email_update&o=update&backup_email="+document.getElementById("backup_email").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(!odata.Success)
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("setup_email: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Restart MySensors Gateway
function resetgw(wid){
	var quest = "?w=resetgw&o=0&wid=" + wid + "&frost_temp=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php?s_id=5"; });
}

//triger search for PiHome network Gateway. 
function find_gw() {  
  	var quest = "?w=find_gw" + "&o=0" + "&frost_temp=0" + "&wid=0";
	request('db.php', 'GET', quest, function(){ window.location="settings.php?s_id=5"; });
    //window.location="settings.php?status=reboot";  
}

//update Gateway 
function setup_gateway(){
var selected_gw_type=document.getElementById("gw_type").value;
var idata="w=setup_gateway&o=update&status="+document.getElementById("checkbox1").checked;
    idata+="&enable_outgoing="+document.getElementById("checkbox4").checked;
    idata+="&gw_type="+document.getElementById("gw_type").value;
        if(selected_gw_type.includes("wifi")) {
            idata+="&gw_location="+document.getElementById("wifi_location").value;
            idata+="&gw_port="+document.getElementById("wifi_port_num").value;
        } else {
            idata+="&gw_location="+document.getElementById("serial_location").value;
            idata+="&gw_port="+document.getElementById("serial_port_speed").value;
        }
	idata+="&gw_timout="+document.getElementById("gw_timout").value;
        idata+="&gw_heartbeat="+document.getElementById("gw_heartbeat").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=5"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("setup_gateway: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update network settings
function setup_network(){
var idata="w=setup_network&o=update&n_primary="+document.getElementById("n_primary").value;
	idata+="&n_ap_mode="+document.getElementById("n_ap_mode").value;
        idata+="&n_int_num="+document.getElementById("n_int_num").value;
        idata+="&n_int_type="+document.getElementById("n_int_type").value;
        idata+="&n_mac="+document.getElementById("n_mac").value;
        idata+="&n_hostname"+document.getElementById("n_hostname").value;
        idata+="&n_ip="+document.getElementById("n_ip").value;
        idata+="&n_gateway="+document.getElementById("n_gateway").value;
        idata+="&n_net_mask="+document.getElementById("n_net_mask").value;
        idata+="&n_dns1="+document.getElementById("n_dns1").value;
        idata+="&n_dns2="+document.getElementById("n_dns2").value;
        idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("setup_network: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update email 
function setup_email(){
var idata="w=setup_email&o=update&status="+document.getElementById("checkbox3").checked;
    idata+="&e_port="+document.getElementById("e_port").value;
	idata+="&e_smtp="+document.getElementById("e_smtp").value;
	idata+="&e_username="+document.getElementById("e_username").value;
	idata+="&e_password="+document.getElementById("e_password").value;
	idata+="&e_from_address="+document.getElementById("e_from_address").value;
	idata+="&e_to_address="+document.getElementById("e_to_address").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("setup_email: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update graph
function setup_graph(){
//var x = document.getElementsByTagName("input");
var x = document.getElementById("zone_graph_body").querySelectorAll("input");
var i;
var idata="w=setup_graph&o=update";
    for (i = 0; i < x.length; i++) {
        if(x[i].name == "enable_archive")
             idata+="&"+x[i].id+"="+x[i].checked;
        else
             idata+="&"+x[i].id+"="+x[i].value;
    }
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("setup_graph: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update temperture sensors to display
function show_sensors(){
//var x = document.getElementsByTagName("input");
var x = document.getElementById("sensor_setup").querySelectorAll("input");
var i;
var idata="w=show_sensors&o=update";
    for (i = 0; i < x.length; i++) {
        idata+="&"+x[i].id+"="+x[i].checked;
    }
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=6"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("show_sensors: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update notice interval
function node_alerts(){
//var x = document.getElementsByTagName("input");
var x = document.getElementById("node_alerts").querySelectorAll("input");
var i;
var idata="w=node_alerts&o=update";
    for (i = 0; i < x.length; i++) {
        idata+="&"+x[i].id+"="+x[i].value;
    }
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=5"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("node_alerts: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update Time Zone
function update_timezone(){
    var idata="w=time_zone&o=update";
    idata+="&time_zone_val="+$("#new_time_zone").val();
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_lang: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update Live Temperature Temperature
function update_livetemp(){
    var idata="w=live_temp&o=update&active="+document.getElementById("checkbox").checked;
    idata+="&livetemp_c="+document.getElementById("livetemp_c").value;
    idata+="&zone_id="+document.getElementById("zone_id").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_default_temperature: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update PiConnect 
function setup_piconnect(){
var idata="w=setup_piconnect&o=update&status="+document.getElementById("checkbox0").checked;
    idata+="&api_key="+document.getElementById("api_key").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("setup_piconnect: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}


function mqtt_delete(wid){
    var result = confirm("Confirm delete MQTT server?");
    if (!result) return;

    var idata="w=mqtt&o=delete";
    idata+="&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            $('#ajaxModal').modal('hide')
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_mqtt: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Add Zone HTTP Message
function add_zone_http_msg(){
var idata="w=http_msg&o=add";
        idata+="&http_id="+document.getElementById("zone_http_id").value;
        idata+="&add_msg_type="+document.getElementById("zone_add_msg_type").value;
        idata+="&http_command="+document.getElementById("zone_http_command").value;
        idata+="&http_parameter="+document.getElementById("zone_http_parameter").value;
    idata+="&wid=1";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=6"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("add_zone_http_msg: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Add Node HTTP Message
function add_node_http_msg(){
var idata="w=http_msg&o=add";
        idata+="&http_id="+document.getElementById("node_http_id").value;
        idata+="&add_msg_type="+document.getElementById("node_add_msg_type").value;
        idata+="&http_command="+document.getElementById("node_http_command").value;
        idata+="&http_parameter="+document.getElementById("node_http_parameter").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=6"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("add_node_http_msg: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete HTTP Message
function delete_http_msg(wid){
var idata="w=http_msg&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=6"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_http_msg: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update user email address
function update_email(){
    var idata="w=user_email&o=update";
    idata+="&email_add="+document.getElementById("email_add").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            reload_page();
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_email: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update scheduled jobs
function schedule_jobs(){
//var x = document.getElementsByTagName("input");
var x = document.getElementById("jobs_schedule").querySelectorAll("input");
var i;
var idata="w=job&o=update";
    for (i = 0; i < x.length; i++) {
        if(x[i].name == "logit" || x[i].name == "enabled")
             idata+="&"+x[i].id+"="+x[i].checked;
        else
             idata+="&"+x[i].id+"="+x[i].value;
    }
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("schedule_jobs: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//add new scheduled jobs
function add_job(){
var idata="w=job&o=add";
        idata+="&enabled="+document.getElementById("checkbox_enabled").checked;
        idata+="&job_name="+document.getElementById("job_name").value;
        idata+="&job_script="+document.getElementById("job_script").value;
        idata+="&job_time="+document.getElementById("job_time").value;
        idata+="&log_it="+document.getElementById("checkbox_logit").checked;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("add_job: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete Job
function delete_job(wid){
var idata="w=job&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_node: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Install Software
function install_software(wid){
        var quest = "?w=sw_install&o=add&wid=" + wid + "&frost_temp=0";
        request('db.php', 'GET', quest, function(){ $("#add_install").modal('show'); } );
}

//start code update
function code_update() {
        var quest = "?w=code_update" + "&o=0" + "&frost_temp=0" + "&wid=0";
        request('db.php', 'GET', quest, function(){ window.location="home.php"; });
    //window.location="settings.php?status=reboot";
}

//check for code updates
function check_updates() {
        var quest = "?w=check_updates" + "&o=0" + "&frost_temp=0" + "&wid=0";
        request('db.php', 'GET', quest, function(){ window.location="home.php"; });
    //window.location="settings.php?status=reboot";
}

//start database update
function database_update() {
        var quest = "?w=database_update" + "&o=0" + "&frost_temp=0" + "&wid=0";
        request('db.php', 'GET', quest, function(){ window.location="home.php"; });
    //window.location="settings.php?status=reboot";
}

//set buttons
function set_buttons(){
//var x = document.getElementsByTagName("input");
var x = document.getElementById("set_buttons").querySelectorAll("input");
var i;
var idata="w=set_buttons&o=update";
    for (i = 0; i < x.length; i++) {
        idata+="&"+x[i].id+"="+x[i].value;
    }
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("set_buttons: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//set db cleanup
function set_db_cleanup(){
//var x = document.getElementsByTagName("input");
var x = document.getElementById("db_cleanup").querySelectorAll("input"); 
var i;
var idata="w=set_db_cleanup&o=update";
    for (i = 0; i < x.length; i++) {
        idata+="&"+x[i].id+"="+x[i].value;
    }
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=2"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("set_buttons: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//enable graphs to display
function enable_graphs(){
//var x = document.getElementsByTagName("input");
var x = document.getElementById("display_graphs").querySelectorAll("input");
var i;
var idata="w=enable_graphs&o=update";
    for (i = 0; i < x.length; i++) {
        idata+="&"+x[i].id+"="+x[i].checked;
    }
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("enable_graphs: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update GitHub Repository location
function set_repository(){
var idata="w=set_repository&o=update";
    idata+="&repository_id="+document.getElementById("rep_id").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=2"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("set_repository_job: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update max cpu temperature
function set_max_cpu_temp(){
var idata="w=set_max_cpu_temp&o=update";
    idata+="&max_cpu_temp="+document.getElementById("m_cpu_temp").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=2"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("set_max_cpu_temp: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update the home and onetouch page refresh rate
function update_refresh(){
var idata="w=page_refresh_rate&o=update";
    idata+="&new_refresh="+document.getElementById("new_refresh").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=2"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("page_refresh_rate: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete Sensor Limit
function delete_sensor_limits(wid){
var idata="w=sensor_limits&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_sensor_limits: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//set theme
function set_theme(){
//var x = document.getElementsByTagName("input");
var x = document.getElementById("theme").querySelectorAll("input");
var i;
var idata="w=set_theme&o=update";
    idata+="&theme_id="+$("#theme_id").val();
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=2"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("set_theme: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete Theme
function delete_theme(wid){
var idata="w=theme&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_theme: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

function relocate_page(page)
{
     location.href = page;
}

//Update Auto Backup
function set_auto_backup(){
//var x = document.getElementsByTagName("input");
var x = document.getElementById("auto_backup").querySelectorAll("input");
var i;
var idata="w=auto_backup&o=update";
    for (i = 0; i < x.length; i++) {
        if(x[i].name == "ab_enabled" || x[i].name == "ab_email_database" || x[i].name == "ab_email_confirmation")
             idata+="&"+x[i].id+"="+x[i].checked;
        else
             idata+="&"+x[i].id+"="+x[i].value;
    }
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=2"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("auto_backup_graphs: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//restore_db
function restore_db(wid){
        var quest = "?w=database_restore" + "&o=0" + "&frost_temp=0" + "&wid="+wid;
        request('db.php', 'GET', quest, function(){ window.location="settings.php?s_id=2"; });
}

//Add Sensor Message
function add_sensor_message(){
var idata="w=sensor_message&o=add&msg_sensor_id="+document.getElementById("msg_sensor_id").value;
    idata+="&msg_id="+document.getElementById("msg_id").value;
    idata+="&msg_type_id="+document.getElementById("msg_type_id").value;
    idata+="&msg_text="+document.getElementById("msg_text").value;
    idata+="&msg_status_color="+document.getElementById("msg_status_color").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=6"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("add_sensor_message: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete Sensor Message
function delete_sensor_message(wid){
var idata="w=sensor_message&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=6"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_sensor_message: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Add EBus Command
function add_ebus_command(){
var idata="w=ebus_command&o=add&ebus_sensor_id="+document.getElementById("ebus_sensor_id").value;
    idata+="&ebus_msg="+document.getElementById("ebus_msg").value;
    idata+="&ebus_position="+document.getElementById("ebus_position").value;
    idata+="&ebus_offset="+document.getElementById("ebus_offset").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=6"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("add_ebus_comand: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete EBus Command
function delete_ebus_command(wid){
var idata="w=ebus_command&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=6"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_ebus_command: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Update Live Temperature Zone
function update_livetemp_zone(){
    var idata="w=update_livetemp_zone&o=update";
    idata+="&zone_id="+document.getElementById("livetemp_zone_id").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_livetemp_zone: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Update Auto System Image Creation
function set_auto_image(){
    //var x = document.getElementsByTagName("input");
    var x = document.getElementById("auto_image").querySelectorAll("input");
    var i;
    var idata="w=auto_image&o=update";
    for (i = 0; i < x.length; i++) {
        if(x[i].name == "ai_enabled" || x[i].name == "ai_email_confirmation")
             idata+="&"+x[i].id+"="+x[i].checked;
        else
             idata+="&"+x[i].id+"="+x[i].value;
    }
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=2"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("auto_image: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Toggle Relay State
function toggle_relay_state(wid){
    var idata="w=toggle_relay&o=update&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(!odata.Success)
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("toggle_relay_state: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Exit Toggle Relay
function toggle_relay_exit(){
    var idata="w=toggle_relay&o=exit";
    idata+="&relay_map="+document.getElementById("relay_map").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="home.php"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("toggle_relay_exit: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Enter Relay State
function toggle_relay_load(){
    var idata="w=toggle_relay&o=enter&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(!odata.Success)
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("toggle_relay_load: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//set false runing time
function set_false_time(){
    let datetime = document.getElementById("false_time").value;
    const myArray = datetime.split("T");
    var date=myArray[0];
    var time=myArray[1];
    var idata="w=set_false_datetime";
        idata+="&date="+date;
        idata+="&time="+time;
        idata+="&sch_test_enabled="+document.getElementById("test_time_enabled").checked;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("set_false_datetime: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//set sensors/relays to hide per user, on the home screen display
function hide_sensors_relays(){
    var x = document.getElementById("hide_sensor_relay").querySelectorAll("input");
    var i;
    var idata="w=hide_sensor_relay&o=update";
    for (i = 0; i < x.length; i++) {
        idata+="&"+x[i].id+"="+x[i].checked;
    }
    idata+="&wid=0";
    console.log(idata);
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=6"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("hide_sensor_relay: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update openweather
function update_openweather(){
    var idata="w=openweather_update&o=update";
    idata+="&CityZip="+document.getElementById("CityZip").value;
    idata+="&inp_City_Zip="+document.getElementById("inp_City_Zip").value;
    idata+="&country_code="+document.getElementById("country_code").value;
    idata+="&inp_APIKEY="+document.getElementById("inp_APIKEY").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("update_openweather: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Delete MQTT Connection
function delete_mqtt_connection(wid){
    var idata="w=mqtt_connection&o=delete&wid="+wid;
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("delete_mqtt_connection: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Add Update MQTT Broker
function add_update_mqtt_broker(){
    var idata="w=mqtt_broker";
    idata+="&conn_id="+document.getElementById("conn_id").value;
    idata+="&inp_Enabled="+document.getElementById("inp_Enabled").value;
    idata+="&inp_Type="+document.getElementById("inp_Type").value;
    idata+="&inp_Name="+document.getElementById("inp_Name").value;
    idata+="&inp_Ip="+document.getElementById("inp_Ip").value;
    idata+="&inp_Port="+document.getElementById("inp_Port").value;
    idata+="&inp_Username="+document.getElementById("inp_Username").value;
    idata+="&inp_Password="+document.getElementById("inp_Password").value;
    idata+="&wid=0";
    console.log(idata);
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("add_update_mqtt_broker: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//Graph Archiving
function graph_archiving() {
var idata="w=setup_graph_archive&o=update&archive_status="+document.getElementById("checkbox6").checked;
    idata+="&graph_archive_file="+document.getElementById("graph_archive_file").value;
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("setup_graph_archive: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//update node max child id
function update_max_child_id(){
//var x = document.getElementsByTagName("input");
var x = document.getElementById("nodes").querySelectorAll("input");
var i;
var idata="w=node_max_child_id&o=update";
    for (i = 0; i < x.length; i++) {
        idata+="&"+x[i].id+"="+x[i].value;
    }
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=5"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("node_max_child_id: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}

//enable logging of the zone_current_state table
function enable_zone_current_state_logs() {
var x = document.getElementById("zone_current_state_logs").querySelectorAll("input");
var i;
var idata="w=enable_zone_current_state_logs&o=update";
    for (i = 0; i < x.length; i++) {
        idata+="&"+x[i].id+"="+x[i].checked;
    }
    idata+="&wid=0";
    $.get('db.php',idata)
    .done(function(odata){
        if(odata.Success)
            window.location="settings.php?s_id=3"
        else
            console.log(odata.Message);
    })
    .fail(function( jqXHR, textStatus, errorThrown ){
        if(jqXHR==401 || jqXHR==403) return;
        console.log("enable_zone_current_state_logs: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
    })
    .always(function() {
    });
}
