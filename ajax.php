<?php 
/*
             __  __                             _
            |  \/  |                    /\     (_)
            | \  / |   __ _  __  __    /  \     _   _ __
            | |\/| |  / _` | \ \/ /   / /\ \   | | |  __|
            | |  | | | (_| |  >  <   / ____ \  | | | |
            |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|

                   S M A R T   T H E R M O S T A T

*************************************************************************"
* MaxAir is a Linux based Central Heating Control systems. It runs from *"
* a web interface and it comes with ABSOLUTELY NO WARRANTY, to the      *"
* extent permitted by applicable law. I take no responsibility for any  *"
* loss or damage to you or your property.                               *"
* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTILL UNLESS YOU KNOW *"
* WHAT YOU ARE DOING                                                    *"
*************************************************************************"
*/
require_once(__DIR__.'/st_inc/session.php');
confirm_logged_in();
require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');

if(!isset($_GET['Ajax'])){
    //Check this once, instead of everytime. Should be more efficient.
    //if($DEBUG==true)
    //{
        var_dump($_GET);
        echo '<br />';
    //}
    echo __FILE__ . ' ' . __LINE__ . ' Error: Ajax action is not set.';
    return;
}

function GetModal_OpenWeather($conn){
	global $lang;
	//foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";

    echo '<div class="modal-header '.theme($conn, settings($conn, 'theme'), 'text_color').' bg-'.theme($conn, settings($conn, 'theme'), 'color').'">
            <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
            <h5 class="modal-title" id="ajaxModalLabel">'.$lang['openweather_settings'].'</h5>
        </div>
        <div class="modal-body" id="ajaxModalBody">
            <p class="text-muted">'.$lang['openweather_text1'].' <a class="green" target="_blank" href="http://OpenWeatherMap.org">'.$lang['openweather_text2'].'</a> '.$lang['openweather_text3'].'
            <p>'.$lang['openweather_text4'].'

            <form name="form-openweather" id="form-openweather" role="form" onSubmit="return false;" action="javascript:return false;" >
            <div class="form-group">
                <label>Country</label>&nbsp;(ISO-3166-1: Alpha-2 Codes)
                <select class="form-control" id="sel_Country" name="sel_Country" >
                    <option value="AF">Afghanistan</option>
                    <option value="AX">Åland Islands</option>
                    <option value="AL">Albania</option>
                    <option value="DZ">Algeria</option>
                    <option value="AS">American Samoa</option>
                    <option value="AD">Andorra</option>
                    <option value="AO">Angola</option>
                    <option value="AI">Anguilla</option>
                    <option value="AQ">Antarctica</option>
                    <option value="AG">Antigua and Barbuda</option>
                    <option value="AR">Argentina</option>
                    <option value="AM">Armenia</option>
                    <option value="AW">Aruba</option>
                    <option value="AU">Australia</option>
                    <option value="AT">Austria</option>
                    <option value="AZ">Azerbaijan</option>
                    <option value="BS">Bahamas</option>
                    <option value="BH">Bahrain</option>
                    <option value="BD">Bangladesh</option>
                    <option value="BB">Barbados</option>
                    <option value="BY">Belarus</option>
                    <option value="BE">Belgium</option>
                    <option value="BZ">Belize</option>
                    <option value="BJ">Benin</option>
                    <option value="BM">Bermuda</option>
                    <option value="BT">Bhutan</option>
                    <option value="BO">Bolivia, Plurinational State of</option>
                    <option value="BQ">Bonaire, Sint Eustatius and Saba</option>
                    <option value="BA">Bosnia and Herzegovina</option>
                    <option value="BW">Botswana</option>
                    <option value="BV">Bouvet Island</option>
                    <option value="BR">Brazil</option>
                    <option value="IO">British Indian Ocean Territory</option>
                    <option value="BN">Brunei Darussalam</option>
                    <option value="BG">Bulgaria</option>
                    <option value="BF">Burkina Faso</option>
                    <option value="BI">Burundi</option>
                    <option value="KH">Cambodia</option>
                    <option value="CM">Cameroon</option>
                    <option value="CA">Canada</option>
                    <option value="CV">Cape Verde</option>
                    <option value="KY">Cayman Islands</option>
                    <option value="CF">Central African Republic</option>
                    <option value="TD">Chad</option>
                    <option value="CL">Chile</option>
                    <option value="CN">China</option>
                    <option value="CX">Christmas Island</option>
                    <option value="CC">Cocos (Keeling) Islands</option>
                    <option value="CO">Colombia</option>
                    <option value="KM">Comoros</option>
                    <option value="CG">Congo</option>
                    <option value="CD">Congo, the Democratic Republic of the</option>
                    <option value="CK">Cook Islands</option>
                    <option value="CR">Costa Rica</option>
                    <option value="CI">Côte d\'Ivoire</option>
                    <option value="HR">Croatia</option>
                    <option value="CU">Cuba</option>
                    <option value="CW">Curaçao</option>
                    <option value="CY">Cyprus</option>
                    <option value="CZ">Czech Republic</option>
                    <option value="DK">Denmark</option>
                    <option value="DJ">Djibouti</option>
                    <option value="DM">Dominica</option>
                    <option value="DO">Dominican Republic</option>
                    <option value="EC">Ecuador</option>
                    <option value="EG">Egypt</option>
                    <option value="SV">El Salvador</option>
                    <option value="GQ">Equatorial Guinea</option>
                    <option value="ER">Eritrea</option>
                    <option value="EE">Estonia</option>
                    <option value="ET">Ethiopia</option>
                    <option value="FK">Falkland Islands (Malvinas)</option>
                    <option value="FO">Faroe Islands</option>
                    <option value="FJ">Fiji</option>
                    <option value="FI">Finland</option>
                    <option value="FR">France</option>
                    <option value="GF">French Guiana</option>
                    <option value="PF">French Polynesia</option>
                    <option value="TF">French Southern Territories</option>
                    <option value="GA">Gabon</option>
                    <option value="GM">Gambia</option>
                    <option value="GE">Georgia</option>
                    <option value="DE">Germany</option>
                    <option value="GH">Ghana</option>
                    <option value="GI">Gibraltar</option>
                    <option value="GR">Greece</option>
                    <option value="GL">Greenland</option>
                    <option value="GD">Grenada</option>
                    <option value="GP">Guadeloupe</option>
                    <option value="GU">Guam</option>
                    <option value="GT">Guatemala</option>
                    <option value="GG">Guernsey</option>
                    <option value="GN">Guinea</option>
                    <option value="GW">Guinea-Bissau</option>
                    <option value="GY">Guyana</option>
                    <option value="HT">Haiti</option>
                    <option value="HM">Heard Island and McDonald Islands</option>
                    <option value="VA">Holy See (Vatican City State)</option>
                    <option value="HN">Honduras</option>
                    <option value="HK">Hong Kong</option>
                    <option value="HU">Hungary</option>
                    <option value="IS">Iceland</option>
                    <option value="IN">India</option>
                    <option value="ID">Indonesia</option>
                    <option value="IR">Iran, Islamic Republic of</option>
                    <option value="IQ">Iraq</option>
                    <option value="IE">Ireland</option>
                    <option value="IM">Isle of Man</option>
                    <option value="IL">Israel</option>
                    <option value="IT">Italy</option>
                    <option value="JM">Jamaica</option>
                    <option value="JP">Japan</option>
                    <option value="JE">Jersey</option>
                    <option value="JO">Jordan</option>
                    <option value="KZ">Kazakhstan</option>
                    <option value="KE">Kenya</option>
                    <option value="KI">Kiribati</option>
                    <option value="KP">Korea, Democratic People\'s Republic of</option>
                    <option value="KR">Korea, Republic of</option>
                    <option value="KW">Kuwait</option>
                    <option value="KG">Kyrgyzstan</option>
                    <option value="LA">Lao People\'s Democratic Republic</option>
                    <option value="LV">Latvia</option>
                    <option value="LB">Lebanon</option>
                    <option value="LS">Lesotho</option>
                    <option value="LR">Liberia</option>
                    <option value="LY">Libya</option>
                    <option value="LI">Liechtenstein</option>
                    <option value="LT">Lithuania</option>
                    <option value="LU">Luxembourg</option>
                    <option value="MO">Macao</option>
                    <option value="MK">Macedonia, the former Yugoslav Republic of</option>
                    <option value="MG">Madagascar</option>
                    <option value="MW">Malawi</option>
                    <option value="MY">Malaysia</option>
                    <option value="MV">Maldives</option>
                    <option value="ML">Mali</option>
                    <option value="MT">Malta</option>
                    <option value="MH">Marshall Islands</option>
                    <option value="MQ">Martinique</option>
                    <option value="MR">Mauritania</option>
                    <option value="MU">Mauritius</option>
                    <option value="YT">Mayotte</option>
                    <option value="MX">Mexico</option>
                    <option value="FM">Micronesia, Federated States of</option>
                    <option value="MD">Moldova, Republic of</option>
                    <option value="MC">Monaco</option>
                    <option value="MN">Mongolia</option>
                    <option value="ME">Montenegro</option>
                    <option value="MS">Montserrat</option>
                    <option value="MA">Morocco</option>
                    <option value="MZ">Mozambique</option>
                    <option value="MM">Myanmar</option>
                    <option value="NA">Namibia</option>
                    <option value="NR">Nauru</option>
                    <option value="NP">Nepal</option>
                    <option value="NL">Netherlands</option>
                    <option value="NC">New Caledonia</option>
                    <option value="NZ">New Zealand</option>
                    <option value="NI">Nicaragua</option>
                    <option value="NE">Niger</option>
                    <option value="NG">Nigeria</option>
                    <option value="NU">Niue</option>
                    <option value="NF">Norfolk Island</option>
                    <option value="MP">Northern Mariana Islands</option>
                    <option value="NO">Norway</option>
                    <option value="OM">Oman</option>
                    <option value="PK">Pakistan</option>
                    <option value="PW">Palau</option>
                    <option value="PS">Palestinian Territory, Occupied</option>
                    <option value="PA">Panama</option>
                    <option value="PG">Papua New Guinea</option>
                    <option value="PY">Paraguay</option>
                    <option value="PE">Peru</option>
                    <option value="PH">Philippines</option>
                    <option value="PN">Pitcairn</option>
                    <option value="PL">Poland</option>
                    <option value="PT">Portugal</option>
                    <option value="PR">Puerto Rico</option>
                    <option value="QA">Qatar</option>
                    <option value="RE">Réunion</option>
                    <option value="RO">Romania</option>
                    <option value="RU">Russian Federation</option>
                    <option value="RW">Rwanda</option>
                    <option value="BL">Saint Barthélemy</option>
                    <option value="SH">Saint Helena, Ascension and Tristan da Cunha</option>
                    <option value="KN">Saint Kitts and Nevis</option>
                    <option value="LC">Saint Lucia</option>
                    <option value="MF">Saint Martin (French part)</option>
                    <option value="PM">Saint Pierre and Miquelon</option>
                    <option value="VC">Saint Vincent and the Grenadines</option>
                    <option value="WS">Samoa</option>
                    <option value="SM">San Marino</option>
                    <option value="ST">Sao Tome and Principe</option>
                    <option value="SA">Saudi Arabia</option>
                    <option value="SN">Senegal</option>
                    <option value="RS">Serbia</option>
                    <option value="SC">Seychelles</option>
                    <option value="SL">Sierra Leone</option>
                    <option value="SG">Singapore</option>
                    <option value="SX">Sint Maarten (Dutch part)</option>
                    <option value="SK">Slovakia</option>
                    <option value="SI">Slovenia</option>
                    <option value="SB">Solomon Islands</option>
                    <option value="SO">Somalia</option>
                    <option value="ZA">South Africa</option>
                    <option value="GS">South Georgia and the South Sandwich Islands</option>
                    <option value="SS">South Sudan</option>
                    <option value="ES">Spain</option>
                    <option value="LK">Sri Lanka</option>
                    <option value="SD">Sudan</option>
                    <option value="SR">Suriname</option>
                    <option value="SJ">Svalbard and Jan Mayen</option>
                    <option value="SZ">Swaziland</option>
                    <option value="SE">Sweden</option>
                    <option value="CH">Switzerland</option>
                    <option value="SY">Syrian Arab Republic</option>
                    <option value="TW">Taiwan, Province of China</option>
                    <option value="TJ">Tajikistan</option>
                    <option value="TZ">Tanzania, United Republic of</option>
                    <option value="TH">Thailand</option>
                    <option value="TL">Timor-Leste</option>
                    <option value="TG">Togo</option>
                    <option value="TK">Tokelau</option>
                    <option value="TO">Tonga</option>
                    <option value="TT">Trinidad and Tobago</option>
                    <option value="TN">Tunisia</option>
                    <option value="TR">Turkey</option>
                    <option value="TM">Turkmenistan</option>
                    <option value="TC">Turks and Caicos Islands</option>
                    <option value="TV">Tuvalu</option>
                    <option value="UG">Uganda</option>
                    <option value="UA">Ukraine</option>
                    <option value="AE">United Arab Emirates</option>
                    <option value="GB">United Kingdom</option>
                    <option value="US">United States</option>
                    <option value="UM">United States Minor Outlying Islands</option>
                    <option value="UY">Uruguay</option>
                    <option value="UZ">Uzbekistan</option>
                    <option value="VU">Vanuatu</option>
                    <option value="VE">Venezuela, Bolivarian Republic of</option>
                    <option value="VN">Viet Nam</option>
                    <option value="VG">Virgin Islands, British</option>
                    <option value="VI">Virgin Islands, U.S.</option>
                    <option value="WF">Wallis and Futuna</option>
                    <option value="EH">Western Sahara</option>
                    <option value="YE">Yemen</option>
                    <option value="ZM">Zambia</option>
                    <option value="ZW">Zimbabwe</option>
                </select>
            </div>
            <div class="form-group">
                <label>City or Zip</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="rad_CityZip" id="rad_CityZip_City" value="City" onchange="rad_CityZip_Changed();">
                  City
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="rad_CityZip" id="rad_CityZip_Zip" value="Zip" onchange="rad_CityZip_Changed();">
                  Zip
                </div>
            </div>
            <div class="form-group CityZip City">
                <label>City:</label>
                <input type="text" class="form-control" name="inp_City" id="inp_City">
            </div>
            <div class="form-group CityZip Zip">
                <label>Zip:</label>
                <input type="text" class="form-control" name="inp_Zip" id="inp_Zip">
            </div>
            <div class="form-group">
                <label>API Key:</label>
                <input type="text" class="form-control" name="inp_APIKEY" id="inp_APIKEY">
            </div>
            </form>';
    echo '</div>';      //close class="modal-body">
    echo '<div class="modal-footer" id="ajaxModalFooter">
            <button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['cancel'].'</button>
            <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' login btn-sm" onclick="update_openweather()">
        </div>';      //close class="modal-footer">

    echo '<script language="javascript" type="text/javascript">
        rad_CityZip_Changed=function() {
            if($(\'#form-openweather [name="rad_CityZip"]:checked\').val()=="City") {
                $(".CityZip").hide();
                $(".CityZip.City").show();
            }
            else {
                $(".CityZip").hide();
                $(".CityZip.Zip").show();
            }
        };
        $("#sel_Country").val("' . settings($conn,'country') . '");
        $("#inp_APIKEY").val("' . settings($conn,'openweather_api') . '");';
    $City=settings($conn,'city');
    if($City!=NULL) {
        echo '$(\'#form-openweather [name="rad_CityZip"]\').val(["City"]);';
        echo '$("#inp_City").val("' . $City . '");';
    }else {
        echo '$(\'#form-openweather [name="rad_CityZip"]\').val(["Zip"]);';
        echo '$("#inp_Zip").val("' . settings($conn,'zip') . '");';
    }
    echo 'rad_CityZip_Changed();
        update_openweather=function(){
            var idata="w=openweather&o=update";
            idata+="&"+$("#form-openweather").serialize();
            $.get("db.php",idata)
            .done(function(odata){
                if(odata.Success)
                    $("#ajaxModal").modal("hide");
                else
                    alert(odata.Message);
            })
            .fail(function( jqXHR, textStatus, errorThrown ){
                if(jqXHR==401 || jqXHR==403) return;
                alert("update_openweather: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
            })
            .always(function() {
            });
        }
    </script>';

    return;
}
if($_GET['Ajax']=='GetModal_OpenWeather')
{
    GetModal_OpenWeather($conn);
    return;
}


function GetModal_System($conn)
{
        global $lang;
	//foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";
    //System temperature
    echo '<div class="modal-header '.theme($conn, settings($conn, 'theme'), 'text_color').' bg-'.theme($conn, settings($conn, 'theme'), 'color').'">
            <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
            <h5 class="modal-title" id="ajaxModalLabel">'.$lang['cpu_temperature'].'</h5>
        </div>
        <div class="modal-body" id="ajaxModalBody">
    <p class="text-muted"> '.$lang['cpu_temperature_text'].' </p>';
    $query = "select * from messages_in where node_id = 0 order by datetime desc limit 5";
    $results = $conn->query($query);
    echo '<div class="list-group">';
    while ($row = mysqli_fetch_assoc($results)) {
        echo '<div class="list-group-item">
		<div class="d-flex justify-content-between">
			<span>
        			<i class="bi bi-hdd-stack green"></i> '.$row['datetime'].'
			</span>
        		<span class="text-muted small"><em>'.number_format(DispSensor($conn,$row['payload'],1),1).'&deg;</em></span>
        	</div>
	</div>';
    }
    echo '</div>';      //close class="list-group">';
    echo '</div>';      //close class="modal-body">
    echo '<div class="modal-footer" id="ajaxModalFooter">
            <button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
        </div>';      //close class="modal-footer">
    return;
}
if($_GET['Ajax']=='GetModal_System')
{
    GetModal_System($conn);
    return;
}



function GetModal_MQTT($conn)
{
        global $lang;
	//foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";

    echo '<div class="modal-header '.theme($conn, settings($conn, 'theme'), 'text_color').' bg-'.theme($conn, settings($conn, 'theme'), 'color').'">
            <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
            <h5 class="modal-title" id="ajaxModalLabel">'.$lang['mqtt_connections'].'</h5>
            <div class="dropdown float-right">
                <a class="" data-bs-toggle="dropdown" href="#">
                        <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                        <li><a class="dropdown-item" href="pdf_download.php?file=setup_guide_mqtt.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_guide_mqtt'].'</a></li>
                        <li class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="pdf_download.php?file=setup_zigbee2mqtt.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_zigbee2mqtt'].'</a></li>
                </ul>
             </div>
        </div>
        <div class="modal-body" id="ajaxModalBody">';
    $query = "SELECT * FROM `mqtt` ORDER BY `name`;";
    $results = $conn->query($query);
    echo '<div class="list-group">';
    echo '<span class="list-group-item text-end" style="height:50px;">';
    echo '<span class="text-muted small"><button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" 
             data-bs-remote="false" data-bs-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_MQTTAdd" onclick="mqtt_AddEdit(this);">'.$lang['add'].'</button></span>';
    echo '</span>';
    while ($row = mysqli_fetch_assoc($results)) {
        echo '<span class="list-group-item">
		<div class="d-flex justify-content-between">
			<div>';
        			echo $row['name'] . ($row['enabled'] ? '' : ' (Disabled)');
			echo '</div>
			<div>
        			<span class="text-muted small" style="width:200px;text-align:right;">Username:&nbsp;' . $row['username'] . '</span>
			</div>
		</div>
                <div class="d-flex justify-content-between">
			<div>
				<span class="text-muted small">Type:&nbsp;';
        				if($row['type']==0) echo 'Default, monitor.';
        				else if($row['type']==1) echo 'Sonoff Tasmota.';
        				else if($row['type']==2) echo 'MQTT Node.';
        				else if($row['type']==3) echo 'Home Assistant.';
        				else echo 'Unknown.';
        			echo '</span>
			</div>
			<div>
        			<span class="text-muted small" style="width:200px;text-align:right;">Password:&nbsp;' . dec_passwd($row['password']) . '</span>
			</div>
		</div>
                <div class="d-flex justify-content-between">
                        <div>
        			<span class="text-muted small">' . $row['ip'] . '&nbsp;:&nbsp;' . $row['port'] . '</span>
			</div>
			<div>
        			<span class="text-muted small" style="width:200px;text-align:right;">
        				<button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-xs" data-bs-remote="false" data-bs-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_MQTTEdit&id=' . $row['id'] . '" onclick="mqtt_AddEdit(this);">
            					<span class="bi bi-pencil"></span>
					</button>&nbsp;&nbsp;
					<button class="btn btn-danger btn-xs" onclick="mqtt_delete(' . $row['id'] . ');"><span class="bi bi-trash-fill"></span></button>
        			</span>
        		</div>
		</div>
	</span>';
    }
    echo '</div>';      //close class="list-group">';
    echo '</div>';      //close class="modal-body">
    echo '<div class="modal-footer" id="ajaxModalFooter">
            <button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
        </div>';      //close class="modal-footer">
    echo '<script language="javascript" type="text/javascript">
        mqtt_AddEdit=function(ithis){ $("#ajaxModal").one("hidden.bs.modal", function() { $("#ajaxModal").modal("show",$(ithis)); }).modal("hide");};
    </script>';
    return;
}
if($_GET['Ajax']=='GetModal_MQTT')
{
    GetModal_MQTT($conn);
    return;
}
function GetModal_MQTTAddEdit($conn)
{
        global $lang;
	//foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";

    $IsAdd=true;
    if(isset($_GET['id'])) {
        $query = "SELECT * FROM `mqtt` WHERE `id`=" . $_GET['id'] . ";";
        $results = $conn->query($query);
        $row = mysqli_fetch_assoc($results);
        $IsAdd=false;
    }

    echo '<div class="modal-header '.theme($conn, settings($conn, 'theme'), 'text_color').' bg-'.theme($conn, settings($conn, 'theme'), 'color').'">
            <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
            <h5 class="modal-title" id="ajaxModalLabel">' . ($IsAdd ? $lang['add_mqtt_connection'] : $lang['edit_mqtt_connection']) . '</h5>
        </div>
        <div class="modal-body" id="ajaxModalBody">';


    echo '<form name="form-mqtt" id="form-mqtt" role="form" onSubmit="return false;" action="javascript:return false;" >
            ' . ($IsAdd ? '' : '<input type="hidden" name="inp_id" id="inp_id" value="' . $row['id'] . '">') . '
            <div class="form-group">
                <label>Name</label>
                <input type="text" class="form-control" name="inp_Name" id="inp_Name" value="' . ($IsAdd ? '' : $row['name']) . '">
            </div>
            <div class="form-group">
                <label>IP</label>
                <input type="text" class="form-control" name="inp_IP" id="inp_IP" value="' . ($IsAdd ? '' : $row['ip']) . '">
            </div>
            <div class="form-group">
                <label>Port</label>
                <input type="text" class="form-control" name="inp_Port" id="inp_Port" value="' . ($IsAdd ? '' : $row['port']) . '">
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" class="form-control" name="inp_Username" id="inp_Username" value="' . ($IsAdd ? '' : $row['username']) . '">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" class="form-control" name="inp_Password" id="inp_Password" value="' . ($IsAdd ? '' : dec_passwd($row['password'])) . '">
            </div>
            <div class="form-group">
                <label>Enabled</label>
                <select class="form-control" id="sel_Enabled" name="sel_Enabled" >
                    <option value="0" ' . ($IsAdd ? '' : ($row['enabled'] ? 'selected' : '')) . '>'.$lang['disabled'].'</option>
                    <option value="1" ' . ($IsAdd ? '' : ($row['enabled'] ? 'selected' : '')) . '>'.$lang['enabled'].'</option>
                </select>
            </div>
            <div class="form-group">
                <label>Type</label>
                <select class="form-control" id="sel_Type" name="sel_Type" >
                    <option value="0" ' . ($IsAdd ? '' : ($row['type'] == "0" ? 'selected' : '')) . '>Default - view all</option>
                    <option value="1" ' . ($IsAdd ? '' : ($row['type'] == "1" ? 'selected' : '')) . '>Sonoff - Tasmota</option>
                    <option value="2" ' . ($IsAdd ? '' : ($row['type'] == "2" ? 'selected' : '')) . '>MQTT Node</option>
                    <option value="3" ' . ($IsAdd ? '' : ($row['type'] == "3" ? 'selected' : '')) . '>Home Assistant integration</option>
                </select>
            </div>
            </form>';
    echo '</div>';      //close class="modal-body">
    echo '<div class="modal-footer" id="ajaxModalFooter">' . ($IsAdd ?
            '<button type="button" class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal" onclick="mqtt_add()">'.$lang['add_conn'].'</button>'
            : '<button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal" onclick="mqtt_edit()">'.$lang['edit_conn'].'</button>') . '
            <button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
        </div>';      //close class="modal-footer">
    echo '<script language="javascript" type="text/javascript">
        mqtt_add=function(){
            var idata="w=mqtt&o=add";
            idata+="&"+$("#form-mqtt").serialize();
            $.get("db.php",idata)
            .done(function(odata){
                if(odata.Success)
                    $("#ajaxModal").modal("hide");
                else
                    alert(odata.Message);
            })
            .fail(function( jqXHR, textStatus, errorThrown ){
                if(jqXHR==401 || jqXHR==403) return;
                alert("mqtt_add: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
            })
            .always(function() {
            });
        }
        mqtt_edit=function(){
            var idata="w=mqtt&o=edit";
            idata+="&"+$("#form-mqtt").serialize();
            $.get("db.php",idata)
            .done(function(odata){
                if(odata.Success)
                    $("#ajaxModal").modal("hide");
                else
                    alert(odata.Message);
            })
            .fail(function( jqXHR, textStatus, errorThrown ){
                if(jqXHR==401 || jqXHR==403) return;
                alert("mqtt_edit: Error.\r\n\r\njqXHR: "+jqXHR+"\r\n\r\ntextStatus: "+textStatus+"\r\n\r\nerrorThrown:"+errorThrown);
            })
            .always(function() {
            });
        }
    </script>';
    return;
}
if($_GET['Ajax']=='GetModal_MQTTEdit' || $_GET['Ajax']=='GetModal_MQTTAdd')
{
    GetModal_MQTTAddEdit($conn);
    return;
}



function GetModal_Services($conn)
{
	global $lang;
	//foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";

    	echo '<div class="modal-header '.theme($conn, settings($conn, 'theme'), 'text_color').' bg-'.theme($conn, settings($conn, 'theme'), 'color').'">
            	<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
            	<h5 class="modal-title" id="ajaxModalLabel">'.$lang['services'].'</h5>
        </div>
        <div class="modal-body" id="ajaxModalBody">';
    		$SArr=[['name'=>'Apache','service'=>'apache2.service'],
           		['name'=>'MySQL','service'=>'mysql.service'],
           		['name'=>'MariaDB','service'=>'mariadb.service'],
           		['name'=>'PiHome JOBS','service'=>'pihome_jobs_schedule.service'],
           		['name'=>'HomeAssistant Integration','service'=>'HA_integration.service'],
	   		['name'=>'Amazon Echo','service'=>'pihome_amazon_echo.service'],
           		['name'=>'Homebridge','service'=>'homebridge.service'],
           		['name'=>'Autohotspot','service'=>'autohotspot.service']];
    		echo '<div class="list-group">';
    			foreach($SArr as $SArrKey=>$SArrVal) {
        			echo '<span class="list-group-item">
					<div class="d-flex justify-content-start">';
        					echo $SArrVal['name'];
					echo '</div>';
        				$rval=my_exec("/bin/systemctl status " . $SArrVal['service']);
					echo '<div class="d-flex justify-content-between">
        					<span class="text-muted small">';
			        			if($rval['stdout']=='') {
            							echo 'Error: ' . $rval['stderr'];
        						} else {
            							$stat='Status: Unknown';
            							$rval['stdout']=explode(PHP_EOL,$rval['stdout']);
            							foreach($rval['stdout'] as $line) {
                							if(strstr($line,'Loaded:')) {
                    								if(strstr($line,'disabled;')) {
                        								$stat='Status: Disabled';
                       				 					break;
                    								}
                							}
                							if(strstr($line,'Active:')) {
                    								if(strstr($line,'active (running)')) {
                        								$stat=trim($line);
                        								break;
                    								} else if(strstr($line,'(dead)')) {
                        								$stat='Status: Dead';
                        								break;
                    								}
                							}
            							}
            							echo $stat;
        						}
        					echo '</span>
        					<span class="text-muted small" style="width:200px;text-align:right;">
        						<button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-xs" data-bs-remote="false" data-bs-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_ServicesInfo&id=' . $SArrVal['service'] . '" onclick="services_Info(this);">
            						<span class="bi bi-info-circle"></span></button>';
        					echo '</span>
        				</div>
				</span>';
    			}
    		echo '</div>';      //close class="list-group">';
    	echo '</div>';      //close class="modal-body">
    	echo '<div class="modal-footer" id="ajaxModalFooter">
        	<button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
        </div>';      //close class="modal-footer">
    	echo '<script language="javascript" type="text/javascript">
        	services_Info=function(ithis){ $("#ajaxModal").one("hidden.bs.modal", function() { $("#ajaxModal").modal("show",$(ithis)); }).modal("hide");};
    	</script>';
    return;
}
if($_GET['Ajax']=='GetModal_Services')
{
    GetModal_Services($conn);
    return;
}
function GetModal_ServicesInfo($conn)
{
        global $lang;
	//foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";

    echo '<div class="modal-header '.theme($conn, settings($conn, 'theme'), 'text_color').' bg-'.theme($conn, settings($conn, 'theme'), 'color').'">
            <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
            <h5 class="modal-title" id="ajaxModalLabel">'.$lang['services_info'].'</h5>
        </div>
        <div class="modal-body" id="ajaxModalBody">';
    echo '<div class="list-group">';
    if(isset($_GET['Action'])) {
        if($_GET['Action']=='start' || $_GET['Action']=='stop' || $_GET['Action']=='enable' || $_GET['Action']=='disable') {
            if(substr($_GET['id'],0,10)=='homebridge') {
                if($_GET['Action']=='start' || $_GET['Action']=='stop') {
                        $rval=my_exec("sudo hb-service " . $_GET['Action']);
                } elseif ($_GET['Action']=='enable') {
                        $rval=my_exec("sudo hb-service install --user homebridge");
                } else {
                        $rval=my_exec("sudo hb-service uninstall");
                }
            } else {
                $rval=my_exec("/usr/bin/sudo /bin/systemctl " . $_GET['Action'] . " " . $_GET['id']);
            }
            $per='';
            similar_text($rval['stderr'],'We trust you have received the usual lecture from the local System Administrator. It usually boils down to these three things: #1) Respect the privacy of others. #2) Think before you type. #3) With great power comes great responsibility. sudo: no tty present and no askpass program specified',$per);
            if($per>80) {
		if(substr($_GET['id'],0,10)=='homebridge') {
                	$rval['stdout']='www-data cannot issue  hb-service commands.<br/><br/>If you would like it to be able to, add<br/><code>www-data ALL=/usr/bin/hb-service<br/>www-data ALL=NOPASSWD: /usr/bin/hb-service</code><br/>to /etc/sudoers.d/010_pi-nopasswd.';
		} else {
			$rval['stdout']='www-data cannot issue systemctl commands.<br/><br/>If you would like it to be able to, add<br/><code>www-data ALL=/bin/systemctl<br/>www-data ALL=NOPASSWD: /bin/systemctl</code><br/>to /etc/sudoers.d/010_pi-nopasswd.';
		}
                $rval['stderr']='';
            }
            echo '<p class="text-muted">systemctl ' . $_GET['Action'] . ' ' . $_GET['id'] . '<br/>stdout: ' . $rval['stdout'] . '<br/>stderr: ' . $rval['stderr'] . '</p>';
        }
    }

    $rval=my_exec("/bin/systemctl status " . $_GET['id']);
    echo '<span class="list-group-item">' . $_GET['id'] . '<br/>';
    echo '<span class="text-muted small">';
    if($rval['stdout']=='') {
        echo 'Error: ' . $rval['stderr'];
    } else {
        $stat='Status: Unknown';
        $rval['stdout']=explode(PHP_EOL,$rval['stdout']);
        foreach($rval['stdout'] as $line) {
            if(strstr($line,'Loaded:')) {
                if(strstr($line,'disabled;')) {
                    $stat='Status: Disabled';
                    break;
                }
            }
            if(strstr($line,'Active:')) {
                if(strstr($line,'active (running)')) {
                    $stat=trim($line);
                    break;
                } else if(strstr($line,'(dead)')) {
                    $stat='Status: Dead';
                    break;
                }
            }
        }
        echo $stat . '<br/>';
    }
    echo '</span>';
    echo '</span>';

    if(substr($_GET['id'],0,7)=='pihome.' or substr($_GET['id'],0,7)=='pihome_' or substr($_GET['id'],0,10)=='homebridge' or substr($_GET['id'],0,11)=='autohotspot' or substr($_GET['id'],0,14)=='HA_integration') {
        echo '<span class="list-group-item" style="height:55px;">&nbsp;';
        echo '<span class="float-right text-muted small">
              <button class="btn btn-warning btn-xs" data-bs-remote="false" data-bs-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_ServicesInfo&id=' . $_GET['id'] . '&Action=start" onclick="services_Info(this);">
                Start</button>
              <button class="btn btn-warning btn-xs" data-bs-remote="false" data-bs-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_ServicesInfo&id=' . $_GET['id'] . '&Action=stop" onclick="services_Info(this);">
                Stop</button>
              <button class="btn btn-warning btn-xs" data-bs-remote="false" data-bs-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_ServicesInfo&id=' . $_GET['id'] . '&Action=enable" onclick="services_Info(this);">
                Enable</button>
              <button class="btn btn-warning btn-xs" data-bs-remote="false" data-bs-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_ServicesInfo&id=' . $_GET['id'] . '&Action=disable" onclick="services_Info(this);">
                Disable</button>
              </span>';
        echo '</span>';
    }

    $rval=my_exec("/bin/journalctl -u " . $_GET['id'] . " -n 10 --no-pager");
    $per='';
    similar_text($rval['stderr'],'Hint: You are currently not seeing messages from other users and the system. Users in the \'systemd-journal\' group can see all messages. Pass -q to turn off this notice. No journal files were opened due to insufficient permissions.',$per);
    if($per>80)
    {
        $rval['stdout']='www-data cannot access journalctl.<br/><br/>If you would like it to be able to, run<br/><code>sudo usermod -a -G systemd-journal www-data</code><br/>and then reboot the RPi.';
    }
    echo '<span class="list-group-item" style="overflow:hidden;">&nbsp;';
    echo 'Status: <i class="bi-bootstrap-reboot" data-bs-remote="false" data-bs-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_ServicesInfo&id=' . $_GET['id'] . '" onclick="services_Info(this);"></i><br/>';
    echo '<span class="text-muted small">';
    echo Convert_CRLF($rval['stdout'],'<br/>');
    echo '</span></span>';

    if($_GET['id']=='pihome_amazon_echo.service') {
        echo '<span class="list-group-item" style="overflow:hidden;">Install Service:';
        echo '<span class="float-right text-muted small">Edit /lib/systemd/system/' . $_GET['id'] . '<br/>
<code>sudo nano /lib/systemd/system/' . $_GET['id'] . '</code><br/>
Put the following contents in the file:<br/>
(make sure the -u is supplied to python<br/>
to ensure the output is not buffered and delayed)<br/>
<code>[Unit]<br/>';
if($_GET['id']=='pihome_amazon_echo.service') {
        echo 'Description=Amazon Echo Service<br/>';
}
echo 'After=multi-user.target<br/>
<br/>
[Service]<br/>
Type=simple<br/>';
if($_GET['id']=='pihome_amazon_echo.service') {
        echo 'ExecStart=/usr/bin/python -u /var/www/add_on/amazon_echo/echo_pihome.py<br/>';
}
echo 'Restart=on-abort<br/>
<br/>
[Install]<br/>
WantedBy=multi-user.target</code><br/>
Update the file permissions:<br/>
<code>sudo chmod 644 /lib/systemd/system/' . $_GET['id'] . '</code><br/>
Update systemd:<br/>
<code>sudo systemctl daemon-reload</code><br/>
<br/>
For improved performance, lower SD card writes:<br/>
Edit /etc/systemd/journald.conf<br/>
<code>sudo nano /etc/systemd/journald.conf</code><br/>
Edit/Add the following:<br/>
<code>Storage=volatile<br/>
RuntimeMaxUse=50M</code><br/>
Then restart journald:<br/>
<code>sudo systemctl restart systemd-journald</code><br/>
Refer to: <a href="www.freedesktop.org/software/systemd/man/journald.conf.html">www.freedesktop.org/software/systemd/man/journald.conf.html</a><br/>
              </span>';
        echo '</span>';
    }

    echo '</div>';      //close class="list-group">';
    echo '</div>';      //close class="modal-body">
    echo '<div class="modal-footer" id="ajaxModalFooter">
            <button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
        </div>';      //close class="modal-footer">
    echo '<script language="javascript" type="text/javascript">
        services_Info=function(ithis){ $("#ajaxModal").one("hidden.bs.modal", function() { $("#ajaxModal").modal("show",$(ithis)); }).modal("hide");};
    </script>';
    return;
}
if($_GET['Ajax']=='GetModal_ServicesInfo')
{
    GetModal_ServicesInfo($conn);
    return;
}



function GetModal_Uptime($conn)
{
        global $lang;
	//foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";

    echo '<div class="modal-header '.theme($conn, settings($conn, 'theme'), 'text_color').' bg-'.theme($conn, settings($conn, 'theme'), 'color').'">
            <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
            <h5 class="modal-title" id="ajaxModalLabel">'.$lang['system_uptime'].'</h5>
        </div>
        <div class="modal-body" id="ajaxModalBody">
			<p class="text-muted"> '.$lang['system_uptime_text'].' </p>
			<i class="bi bi-clock red"></i>';
    $uptime = (exec ("cat /proc/uptime"));
    $uptime=substr($uptime, 0, strrpos($uptime, ' '));
    echo '&nbsp'.secondsToWords($uptime) . '<br/><br/>';

    echo '<div class="list-group">';
    echo '<span class="list-group-item" style="overflow:hidden;"><pre>';
    $rval=my_exec("df -h");
    echo $rval['stdout'];
    echo '</pre></span>';

    echo '<span class="list-group-item" style="overflow:hidden;"><pre>';
    $rval=my_exec("free -h");
    echo $rval['stdout'];
    echo '</pre></span>';


/*    while ($row = mysqli_fetch_assoc($results)) {
        echo '<span class="list-group-item">';
        echo $row['name'] . ($row['enabled'] ? '' : ' (Disabled)');
        echo '<span class="float-right text-muted small" style="width:200px;text-align:right;">Username:&nbsp;' . $row['username'] . '</span>';
        echo '<br/><span class="text-muted small">Type:&nbsp;';
        if($row['type']==0) echo 'Default, monitor.';
        else if($row['type']==1) echo 'Sonoff Tasmota.';
        else echo 'Unknown.';
        echo '</span>';
        echo '<span class="float-right text-muted small" style="width:200px;text-align:right;">Password:&nbsp;' . $row['password'] . '</span>';
        echo '<br/><span class="text-muted small">' . $row['ip'] . '&nbsp;:&nbsp;' . $row['port'] . '</span>';

        echo '<span class="float-right text-muted small" style="width:200px;text-align:right;">';
        echo '<button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-xs" data-bs-remote="false" data-bs-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_MQTTEdit&id=' . $row['id'] . '" onclick="mqtt_AddEdit(this);">
            <span class="ionicons ion-edit"></span></button>&nbsp;&nbsp;
		<button class="btn btn-danger btn-xs" onclick="mqtt_delete(' . $row['id'] . ');"><span class="glyphicon glyphicon-trash"></span></button>';
        echo '</span>';
        echo '</span>';
    }*/
    echo '</div>';      //close class="list-group">';
    echo '</div>';      //close class="modal-body">
    echo '<div class="modal-footer" id="ajaxModalFooter">
            <button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
        </div>';      //close class="modal-footer">
    return;
}
if($_GET['Ajax']=='GetModal_Uptime')
{
    GetModal_Uptime($conn);
    return;
}

function GetModal_Sensor_Graph($conn)
{
        global $lang;
        //foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";

	//create array of colours for the graphs
	$query ="SELECT * FROM sensors ORDER BY id ASC;";
	$results = $conn->query($query);
	$counter = 0;
	$count = mysqli_num_rows($results) + 2; //extra space made for system temperature graph
	$sensor_color = array();
	while ($row = mysqli_fetch_assoc($results)) {
        	$graph_id = $row['sensor_id'].".".$row['sensor_child_id'];
        	$sensor_color[$graph_id] = graph_color($count, ++$counter);
	}

	$pieces = explode(',', $_GET['Ajax']);
        $sensor_id = $pieces[1];
	$query="SELECT * FROM sensors WHERE id = {$pieces[1]} LIMIT 1;";
        $result = $conn->query($query);
        $row = mysqli_fetch_assoc($result);
	$name = $row['name'];
	$nodes_id = $row['sensor_id'];
	$child_id = $row['sensor_child_id'];
	$type_id = $row['sensor_type_id'];
	if ($type_id == 1) { $title = $lang['temperature']; } else { $title = $lang['humidity']; }
        $title = $title.' '.$lang['graph'].' - '.$name;
        $graph_id = $row['sensor_id'].".".$row['sensor_child_id'];
	$query="SELECT node_id FROM nodes WHERE id = {$nodes_id} LIMIT 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_assoc($result);
	if ($pieces[2] == 0) {
        	$query="SELECT * from messages_in_view_24h  where node_id = '{$row['node_id']}' AND child_id = {$child_id} ORDER BY id ASC;";
                $ajax_modal = "ajax.php?Ajax=GetModal_Sensor_Graph,".$pieces[1].",1";
		$button_name = $lang['graph_1h'];
	} else {
                $query="SELECT * from messages_in_view_1h  where node_id = '{$row['node_id']}' AND child_id = {$child_id} ORDER BY id ASC;";
                $ajax_modal = "ajax.php?Ajax=GetModal_Sensor_Graph,".$pieces[1].",0";
                $button_name = $lang['graph_24h'];
	}
        $results = $conn->query($query);
	if (mysqli_num_rows($results) > 0) {
	        // create array of pairs of x and y values for every zone
        	$data_x = array();
	        $data_y = array();
        	while ($rowb = mysqli_fetch_assoc($results)) {
			$data_x[] = strtotime($rowb['datetime']) * 1000;
			$data_y[] = $rowb['payload'];
		        $js_array_x = json_encode($data_x);
        		$js_array_y = json_encode($data_y);
        	}
	} else {
        	$js_array_x = '';
                $js_array_y = '';
	}
	?>
	<script type="text/javascript" src="js/plugins/plotly/plotly-2.9.0.min.js"></script>
        <script type="text/javascript" src="js/plugins/plotly/d3.min.js"></script>
	<script>

        <?php if($type_id == 1) { ?>
                var ytitle = 'Temperature';
        <?php } else { ?>
                var ytitle = 'Humidity';
        <?php } ?>
        var xValues = [...<?php echo $js_array_x ?>];
        var yValues = [...<?php echo $js_array_y ?>];

	var data = [
		{
  			type: 'scatter',
  			x: xValues,
  			y: yValues,
  			hoverlabel: {
    				bgcolor: 'black',
    				font: {color: 'white'}
  			},
			hovertemplate: 'At: %{x}<extra></extra>' +
                        '<br><b>Temp: </b>: %{y:.2f}\xB0<br>',
			showlegend: false,
			line: {shape: 'spline', color: '<?php echo $sensor_color[$graph_id]; ?>'}
		}
	];

	var layout = {
  		xaxis: {
                title: 'Time',
                type: 'date',
                tickmode: "linear",
                <?php if ($pieces[2] == 0) { ?>
                        dtick: 2*60*60*1000,
                <?php } else { ?>
                        dtick: 10*60*1000,
                <?php } ?>
    		tickformat: '%H:%M'
  		},
  		yaxis: {
    		title: ytitle
  		},
                autosize: true,
                automargin: true
	};

        var config = {
                responsive: true, displayModeBar: true, displaylogo: false, // this is the line that hides the bar.
        };

        Plotly.react('myChart', data, layout, config);
        $('#ajaxModal').one('shown.bs.modal', function () {
                Plotly.relayout('myChart',layout);
        });
	</script>
<?php
        echo '<div class="modal-header '.theme($conn, settings($conn, 'theme'), 'text_color').' bg-'.theme($conn, settings($conn, 'theme'), 'color').'">
            <h5 class="modal-title" id="ajaxModalLabel">'.$title.'</h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
        </div>
        <div class="modal-body" id="ajaxModalBody">
		<div id="myChart" style="width:100%"></div>
    	</div>
    	<div class="modal-footer" id="ajaxModalFooter">
            <button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-toggle="modal" data-bs-remote="false" data-bs-target="#ajaxModal" data-ajax="'.$ajax_modal.'"  onclick="sensors_Graph(this);">'.$button_name.'</button>
            <button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
        </div>';      //close class="modal-footer">
        echo '<script language="javascript" type="text/javascript">
                sensors_Graph=function(gthis){ $("#ajaxModal").one("hidden.bs.modal", function() { $("#ajaxModal").modal("show",$(gthis)); })};
        </script>';
    return;
}
if(explode(',', $_GET['Ajax'])[0]=='GetModal_Sensor_Graph')
{
    GetModal_Sensor_Graph($conn);
    return;
}

function GetModal_Sensors($conn)
{
	global $lang;
	//foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";

	echo '<div class="modal-header '.theme($conn, settings($conn, 'theme'), 'text_color').' bg-'.theme($conn, settings($conn, 'theme'), 'color').'">
        	<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
            	<h5 class="modal-title" id="ajaxModalLabel">'.$lang['temperature_sensor'].'</h5>
        </div>
        <div class="modal-body" id="ajaxModalBody">
                <p class="text-muted"> '.$lang['temperature_sensor_text'].' </p>';
                $query = "SELECT * FROM sensors ORDER BY sensor_id asc;";
		$results = $conn->query($query);
		echo '<div class="list-group">';
			while ($row = mysqli_fetch_assoc($results)) {
				$query = "SELECT * FROM nodes where id = {$row['sensor_id']} LIMIT 1;";
				$nresult = $conn->query($query);
				$nrow = mysqli_fetch_array($nresult);
                                $batquery = "select * from nodes_battery where node_id = '{$nrow['node_id']}' ORDER BY id desc limit 1;";
                                $batresults = $conn->query($batquery);
                                $bcount = mysqli_num_rows($batresults);
                                if ($bcount > 0) { $brow = mysqli_fetch_array($batresults); }
                                $query = "SELECT payload FROM messages_in where node_id = '{$nrow['node_id']}' AND child_id = {$row['sensor_child_id']} ORDER BY datetime DESC LIMIT 1;";
                                $mresult = $conn->query($query);
				$mcount = mysqli_num_rows($mresult);
				$unit = SensorUnits($conn,$row['sensor_type_id']);
				if ($mcount > 0) { $mrow = mysqli_fetch_array($mresult); }
				echo '<div class="list-group-item">
                                        <div class="form-group row">
                                                <div class="text-start">&nbsp&nbsp'.$nrow['node_id'].'_'.$row['sensor_child_id'].' - '.$row['name'].'</div>
                                        </div>
					<div class="form-group row">';
						if ($bcount > 0) { echo '<div class="text-start">&nbsp&nbsp<i class="bi bi-battery-half"></i> '.round($brow ['bat_level'],0).'% - '.$brow ['bat_voltage'].'</div>'; } else { echo '<div class="text-start">&nbsp&nbsp<i class="bi bi-battery-half"></i></div>'; }
					echo '</div>
					<div class="form-group row">
						<div class="d-flex justify-content-between">';
							if ($mcount > 0) { echo '<span class="text" id="sensor_temp_'.$row['id'].'">&nbsp&nbsp<i class="bi bi-thermometer-half red"></i> - '.$mrow['payload'].$unit.'</span>'; } else { echo '<span class="text" id="sensor_temp_'.$row['id'].'">&nbsp&nbsp<i class="bi bi-thermometer-half red"></i></span>'; }
        	                                        echo '<span class="text-muted small"><button type="button"  data-bs-remote="false" data-bs-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_SensorsInfo&id=' . $row['id'] . '" onclick="sensors_Info(this);"><em>'.$nrow['last_seen'].'&nbsp</em></button>&nbsp</span>
						</div>
					</div>
				</div> ';
			}
		echo '</div>'; //close class=\"list-group\">
    	echo '</div>';      //close class="modal-body">
    	echo '<div class="modal-footer" id="ajaxModalFooter">
		<button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
        </div>';      //close class="modal-footer">
    	echo '<script language="javascript" type="text/javascript">
        	sensors_Info=function(ithis){ $("#ajaxModal").one("hidden.bs.modal", function() { $("#ajaxModal").modal("show",$(ithis)); }).modal("hide");};
    	</script>';
   	 return;
}
if($_GET['Ajax']=='GetModal_Sensors')
{
    	GetModal_Sensors($conn);
    	return;
}
function GetModal_SensorsInfo($conn)
{
        global $lang;
	$query = "SELECT name, sensor_id, sensor_child_id FROM sensors WHERE id = {$_GET['id']}";
	$sresult = $conn->query($query);
	$srow = mysqli_fetch_assoc($sresult);
	$s_name = $srow['name'];
        $sensor_id = $srow['sensor_id'];
        $sensor_child_id = $srow['sensor_child_id'];
	$query = "SELECT node_id FROM nodes WHERE id = {$sensor_id}";
        $nresult = $conn->query($query);
        $nrow = mysqli_fetch_assoc($nresult);
	$node_id = $nrow['node_id'];
        $query = "SELECT * FROM messages_in_view_24h WHERE node_id = '{$node_id}' AND child_id = {$sensor_child_id};";
        $results = $conn->query($query);
        $count = mysqli_num_rows($results);

        echo '<div class="modal-header '.theme($conn, settings($conn, 'theme'), 'text_color').' bg-'.theme($conn, settings($conn, 'theme'), 'color').'">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title" id="ajaxModalLabel">'.$lang['sensor_last24h'].$node_id.'&nbsp('.$sensor_child_id.')</h5>
        </div>
        <div class="modal-body" id="ajaxModalBody">';
                echo '<p class="text-muted">'.$lang['sensor_count_last24h'].$count.'<br>';
                echo $lang['average_count_last24h'].intval($count/24).'</p>';
                if ($count > 0) {
                	echo '<table class="table table-fixed">
                        	<thead>
                                	<tr>
                                        	<th class="col-6"><small>'.$lang['sensor_name'].'</small></th>
                                        	<th style="text-align:center; vertical-align:middle;" class="col-6"><small>'.$lang['last_seen'].'</small></th>
                                	</tr>
                        	</thead>
                        	<tbody>';
	                		while ($row = mysqli_fetch_assoc($results)) {
                        	        	echo '<tr>
                                	        	<td class="col-6">'.$s_name.'</td>
                                        		<td style="text-align:center; vertical-align:middle;" class="col-6">'.$row["datetime"].'</td>
                                		</tr>';
					}
			 	echo '</tbody>
			</table>';
		}
    	echo '</div>
    	<div class="modal-footer" id="ajaxModalFooter">
            	<button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
        </div>';      //close class="modal-footer">
    	echo '<script language="javascript" type="text/javascript">
        	services_Info=function(ithis){ $("#ajaxModal").one("hidden.bs.modal", function() { $("#ajaxModal").modal("show",$(ithis)); }).modal("hide");};
    	</script>';
    	return;
}
if($_GET['Ajax']=='GetModal_SensorsInfo')
{
    	GetModal_SensorsInfo($conn);
    	return;
}

function GetModal_SystemController($conn)
{
        global $lang;
        //query to get last system_controller operation time
        $query = "SELECT * FROM system_controller LIMIT 1";
        $result = $conn->query($query);
        $row = mysqli_fetch_array($result);
        $system_controller_id = $row['id'];
        $system_controller_name = $row['name'];

        //Get data from nodes table
        $query = "SELECT * FROM nodes WHERE id = {$row['node_id']} AND status IS NOT NULL LIMIT 1";
        $result = $conn->query($query);
        $system_controller_node = mysqli_fetch_array($result);
        $system_controller_node_id = $system_controller_node['node_id'];
        $system_controller_seen = $system_controller_node['last_seen'];

        echo '<div class="modal-header '.theme($conn, settings($conn, 'theme'), 'text_color').' bg-'.theme($conn, settings($conn, 'theme'), 'color').'">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title" id="ajaxModalLabel">'.$system_controller_name.' - '.$lang['system_controller_recent_logs'].'</h5>
        </div>
        <div class="modal-body" id="ajaxModalBody">';
		if ($system_controller_fault == '1') {
			$date_time = date('Y-m-d H:i:s');
			$datetime1 = strtotime("$date_time");
			$datetime2 = strtotime("$system_controller_seen");
			$interval  = abs($datetime2 - $datetime1);
			$ctr_minutes   = round($interval / 60);
			echo '
				<ul class="list-group list-group-flush">
					<li class="list-group-item">
						<div class="header">
							<div class="d-flex justify-content-between">
								<span>
									<strong class="primary-font red">System Controller Fault!!!</strong>
								</span>
								<span>
									<small class="text-muted">
										<i class="bi bi-clock icon-fw"></i> '.secondsToWords(($ctr_minutes)*60).' ago
									</small>
								</span>
							</div>
							<br>
							<p>Node ID '.$system_controller_node_id.' last seen at '.$system_controller_seen.' </p>
							<p class="text-info">Heating system will resume its normal operation once this issue is fixed. </p>
						</div>
					</li>
				</ul>';
  		}
		$bquery = "select DATE_FORMAT(start_datetime, '%H:%i') as start_datetime, DATE_FORMAT(stop_datetime, '%H:%i') as stop_datetime , DATE_FORMAT(expected_end_date_time, '%H:%i') as expected_end_date_time, TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime) as on_minuts
		from controller_zone_logs WHERE zone_id = ".$system_controller_id." order by id desc limit 5";
		$bresults = $conn->query($bquery);
		if (mysqli_num_rows($bresults) == 0){
			echo '<div class="list-group">
				<a href="#" class="list-group-item"><i class="bi bi-exclamation-triangle red"></i>&nbsp;&nbsp;'.$lang['system_controller_no_log'].'</a>
			</div>';
		} else {
			echo '<p class="text-muted">'. mysqli_num_rows($bresults) .' '.$lang['system_controller_last_records'].'</p>
			<div class="list-group">' ;
                        	echo '<a href="#" class="d-flex justify-content-between list-group-item list-group-item-action">
                                	<span>
                                        	<img src="images/flame.svg" class="colorize-red" width="20" height="20" alt=""> Start &nbsp; - &nbsp;End
                                        </span>
                                        <span class="text-muted small">
                                         	<em>'.$lang['system_controller_on_minuts'].'&nbsp;</em>
                                        </span>
                                </a>';
				while ($brow = mysqli_fetch_assoc($bresults)) {
                                	echo '<a href="#" class="d-flex justify-content-between list-group-item list-group-item-action">
                                        	<span>
							<img src="images/flame.svg" class="colorize-red" width="20" height="20" alt=""> '. $brow['start_datetime'].' - ' .$brow['stop_datetime'].'
                                                </span>
                                            	<span class="text-muted small">
							<em>'.$brow['on_minuts'].'&nbsp;</em>
						</span>
					</a>';
				}
			 echo '</div>';
		}
        echo '</div>
        <div class="modal-footer" id="ajaxModalFooter">
                <button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
        </div>';      //close class="modal-footer">
        return;
}
if($_GET['Ajax']=='GetModal_SystemController')
{
        GetModal_SystemController($conn);
        return;
}

function GetModal_Schedule_List($conn)
{
        global $lang;

        //following variable set to current day of the week.
        $dow = idate('w');

        //query to check away status
        $query = "SELECT * FROM away LIMIT 1";
        $result = $conn->query($query);
        $away = mysqli_fetch_array($result);
        $away_status = $away['status'];

        //query to check holidays status
        $query = "SELECT * FROM holidays WHERE NOW() between start_date_time AND end_date_time AND status = '1' LIMIT 1";
        $result = $conn->query($query);
        $rowcount=mysqli_num_rows($result);
        if ($rowcount > 0) {
        	$holidays = mysqli_fetch_array($result);
                $holidays_status = $holidays['status'];
        } else {
                $holidays_status = 0;
        }

        //foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";
        //query to get last system_controller operation time
        $query = "SELECT * FROM system_controller LIMIT 1";
        $result = $conn->query($query);
        $row = mysqli_fetch_array($result);
        $system_controller_id = $row['id'];
        $system_controller_name = $row['name'];

        //Get data from nodes table
        $query = "SELECT * FROM nodes WHERE id = {$row['node_id']} AND status IS NOT NULL LIMIT 1";
        $result = $conn->query($query);
        $system_controller_node = mysqli_fetch_array($result);
        $system_controller_node_id = $system_controller_node['node_id'];
        $system_controller_seen = $system_controller_node['last_seen'];

	$pieces = explode(',', $_GET['Ajax']);
        $zone_id = $pieces[1];

        $query = "SELECT zone.name, zone_type.category  FROM zone, zone_type WHERE (zone.type_id = zone_type.id) AND zone.id = {$zone_id} LIMIT 1";
        $result = $conn->query($query);
	$row = mysqli_fetch_array($result);
        $zone_name = $row['name'];
	$zone_category = $row['category'];

        if ($zone_category <> 3) {
        	$query = "SELECT relays.relay_id, relays.relay_child_id FROM zone_relays, relays WHERE (zone_relays.zone_relay_id = relays.id) AND zone_id = '{$zone_id}' LIMIT 1;";
                $result = $conn->query($query);
                $zone_relays = mysqli_fetch_array($result);
                $zone_relay_id=$zone_relays['relay_id'];
                $zone_relay_child_id=$zone_relays['relay_child_id'];
	}

        //query to get zone current state
        $query = "SELECT * FROM zone_current_state WHERE zone_id = '{$zone_id}' LIMIT 1;";
        $result = $conn->query($query);
        $zone_current_state = mysqli_fetch_array($result);
        $zone_mode = $zone_current_state['mode'];
        $zone_mode_main=floor($zone_mode/10)*10;
        $zone_mode_sub=floor($zone_mode%10);
        $zone_temp_reading = $zone_current_state['temp_reading'];
        $zone_temp_target = $zone_current_state['temp_target'];
        $zone_temp_cut_in = $zone_current_state['temp_cut_in'];
        $zone_temp_cut_out = $zone_current_state['temp_cut_out'];
        $zone_ctr_fault = $zone_current_state['controler_fault'];
        $controler_seen = $zone_current_state['controler_seen_time'];
        $zone_sensor_fault = $zone_current_state['sensor_fault'];
        $sensor_seen = $zone_current_state['sensor_seen_time'];
        $temp_reading_time= $zone_current_state['sensor_reading_time'];
        $overrun= $zone_current_state['overrun'];
        $schedule = $zone_current_state['schedule'];

        //get the current zone schedule status
        $sch_status = $schedule & 0b1;
      	$away_sch = ($schedule >> 1) & 0b1;

        if ($sch_status == 1) { $active_schedule = 1; }

        //get the sensor id
        $query = "SELECT * FROM sensors WHERE zone_id = '{$zone_id}' LIMIT 1;";
        $result = $conn->query($query);
        $sensor = mysqli_fetch_array($result);
        $temperature_sensor_id=$sensor['sensor_id'];
        $temperature_sensor_child_id=$sensor['sensor_child_id'];
        $sensor_type_id=$sensor['sensor_type_id'];
        $ajax_modal_24h = "ajax.php?Ajax=GetModal_Sensor_Graph,".$sensor['id'].",0";
        $ajax_modal_1h = "ajax.php?Ajax=GetModal_Sensor_Graph,".$sensor['id'].",1";

        echo '<div class="modal-header '.theme($conn, settings($conn, 'theme'), 'text_color').' bg-'.theme($conn, settings($conn, 'theme'), 'color').'">
            <h5 class="modal-title" id="ajaxModalLabel">'.$zone_name.'</h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
        </div>
        <div class="modal-body" id="ajaxModalBody">';
  		if ($system_controller_fault == '1') {
			$date_time = date('Y-m-d H:i:s');
			$datetime1 = strtotime("$date_time");
			$datetime2 = strtotime("$system_controller_seen");
			$interval  = abs($datetime2 - $datetime1);
			$ctr_minutes   = round($interval / 60);
			echo '
			<ul class="list-group">
				<li class="list-group-item">
					<div class="header">
						<div class="d-flex justify-content-between">
							<span>
								<strong class="primary-font red">System Controller Fault!!!</strong>
							</span>
							<span>
								<small class="text-muted">
									<i class="bi bi-clock icon-fw"></i> '.secondsToWords(($ctr_minutes)*60).' ago
								</small>
							</span>
						</div>
						<br>
						<p>Node ID '.$system_controller_node_id.' last seen at '.$system_controller_seen.' </p>
						<p class="text-info">Heating system will resume its normal operation once this issue is fixed. </p>
					</div>
				</li>
			</ul>';

  		}elseif ($zone_ctr_fault == '1') {
			$date_time = date('Y-m-d H:i:s');
			$datetime1 = strtotime("$date_time");
			echo '
			<ul class="list-group">
				<li class="list-group-item">
					<div class="header">';
						$cquery = "SELECT `zone_relays`.`zone_id`, `zone_relays`.`zone_relay_id`, n.`last_seen`, n.`notice_interval` FROM `zone_relays`
						LEFT JOIN `relays` r on `zone_relays`.`zone_relay_id` = r.`id`
						LEFT JOIN `nodes` n ON r.`relay_id` = n.`id`
						WHERE `zone_relays`.`zone_id` = ".$zone_id.";";
						$cresults = $conn->query($cquery);
						while ($crow = mysqli_fetch_assoc($cresults)) {
							$datetime2 = strtotime($crow['last_seen']);
							$interval  = abs($datetime2 - $datetime1);
							$ctr_minutes   = round($interval / 60);
							$zone_relay_id = $crow['zone_relay_id'];
        	                                        echo '<div class="d-flex justify-content-between">
	                                                        <span>
                                                  	              <strong class="primary-font red">Controller Fault!!!</strong>
                                                        	</span>
                                                        	<span>
                                                                	<small class="text-muted">
                                                                        	<i class="bi bi-clock icon-fw"></i> '.secondsToWords(($ctr_minutes)*60).' ago
                                                                	</small>
                                                        	</span>
                                                	</div>
                                                	<br>
                                                	<p>Controller ID '.$zone_relay_id.' last seen at '.$crow['last_seen'].' </p>';
						}
						echo '<p class="text-info">Heating system will resume its normal operation once this issue is fixed. </p>
					</div>
				</li>
			</ul>';
		//echo $zone_senros_txt;
		}elseif ($zone_sensor_fault == '1'){
			$date_time = date('Y-m-d H:i:s');
			$datetime1 = strtotime("$date_time");
			$datetime2 = strtotime("$sensor_seen");
			$interval  = abs($datetime2 - $datetime1);
			$sensor_minutes   = round($interval / 60);
			echo '
			<ul class="list-group">
				<li class="list-group-item">
					<div class="header">
						<div class="d-flex justify-content-between">
							<span>
								<strong class="primary-font red">Sensor Fault!!!</strong>
							</span>
							<span>
								<small class="text-muted">
									<i class="bi bi-clock icon-fw"></i> '.secondsToWords(($sensor_minutes)*60).' ago
								</small>
							</span>
						</div>
						<br>
						<p>Sensor ID '.$zone_node_id.' last seen at '.$sensor_seen.' <br>Last Temperature reading received at '.$temp_reading_time.' </p>
						<p class="text-info"> Heating system will resume for this zone its normal operation once this issue is fixed. </p>
					</div>
				</li>
			</ul>';
		}else{
			if ($sensor_type_id != 3) {
				//if temperature control active display cut in and cut out levels
                                $c_f = settings($conn, 'c_f');
                                if ($c_f == 0) { $units = 'C'; } else { $units = 'F'; }
				if (($zone_category <= 1 || $zone_category == 5) && (($zone_mode_main == 20 ) || ($zone_mode_main == 50 ) || ($zone_mode_main == 60 ) || ($zone_mode_main == 70 ) || ($zone_mode_main == 80 ) || ($zone_mode_main == 110 ))){
                                	echo '<p>Cut In Temperature : '.DispSensor($conn,$zone_temp_cut_in,$sensor_type_id).'&deg'.$units.'</p>
                                        <p>Cut Out Temperature : ' .DispSensor($conn,$zone_temp_cut_out,$sensor_type_id).'&deg'.$units.'</p>';
				}
				//display coop start info
				if($zone_mode_sub == 3){
					echo '<p>Coop Start Schedule - Waiting for System Controller start.</p>';
				}
			}
//			$squery = "SELECT * FROM schedule_daily_time_zone_view where zone_id ='{$zone_id}' AND tz_status = 1 AND time_status = '1' AND (WeekDays & (1 << {$dow})) > 0 AND type = 0 ORDER BY start asc";
                        $squery = "SELECT schedule_daily_time.sch_name, schedule_daily_time.start, schedule_daily_time.end,
                        schedule_daily_time_zone.temperature, schedule_daily_time_zone.id AS tz_id, schedule_daily_time_zone.coop, schedule_daily_time_zone.disabled
                        FROM `schedule_daily_time`, `schedule_daily_time_zone`
                        WHERE (schedule_daily_time.id = schedule_daily_time_zone.schedule_daily_time_id) AND schedule_daily_time.status = 1
                        AND (schedule_daily_time_zone.status = 1 OR schedule_daily_time_zone.disabled = 1) AND schedule_daily_time.type = 0 AND schedule_daily_time_zone.zone_id ='{$zone_id}'
                        AND (schedule_daily_time.WeekDays & (1 << {$dow})) > 0
                        ORDER BY schedule_daily_time.start asc;";
			$sresults = $conn->query($squery);
			if (mysqli_num_rows($sresults) == 0){
				echo '<div class="list-group">
					<a href="#" class="list-group-item"><i class="bi bi-exclamation-triangle red"></i>&nbsp;&nbsp;'.$lang['schedule_active_today'].' '.$zone_name.'!!! </a>
				</div>';
			} else {
				//echo '<h4>'.mysqli_num_rows($sresults).' Schedule Records found.</h4>';
				echo '<p>'.$lang['schedule_disble'].'</p>
				<br>
				<div class="list-group">' ;
					while ($srow = mysqli_fetch_assoc($sresults)) {
						$shactive="orangesch_list";
						$time = strtotime(date("G:i:s"));
						$start_time = strtotime($srow['start']);
						$end_time = strtotime($srow['end']);
						if ($time >$start_time && $time <$end_time){$shactive="redsch_list";}
                                                if ($srow['coop'] == "1") {
							$coop = '<i class="bi bi-tree-fill green" data-container="body" data-bs-toggle="popover" data-placement="right" data-content="' . $lang['schedule_coop_help'] . '"></i>';
                                                } else {
                                                        $coop = '';
                                                }
						//this line to pass unique argument  "?w=schedule_list&o=active&wid=" href="javascript:delete_schedule('.$srow["id"].');"
		                                echo '<li class="list-group-item">
                		                        <div class="d-flex justify-content-between">
                                		                <span>
                                                		        <div class="d-flex justify-content-start">
										<a href="javascript:schedule_zone('.$srow['tz_id'].');" style="text-decoration: none;">';
											if ($srow['disabled'] == 0) {
												echo '<div id="sdtz_'.$srow['tz_id'].'"><div class="circle_list '. $shactive.'"> <p class="schdegree">'.number_format(DispSensor($conn,$srow['temperature'],$sensor_type_id),0).$unit.'</p></div></div>';
											} else {
												echo '<div id="sdtz_'.$srow['tz_id'].'"><div class="circle_list bluesch_disable"> <p class="schdegree">D</p></div></div>';
											}
										echo '</a>
										<span class="label text-info">&nbsp&nbsp'.$srow['sch_name'].'</span>
									</div>
								</span>
								<span class="text-muted"><em>'. $coop. '&nbsp'.$srow['start'].' - ' .$srow['end'].'</em></span>';
							echo '</div>
						</li>';
					}
				echo '</div>';
			}
		}
    	echo '</div>
    	<div class="modal-footer" id="ajaxModalFooter">
            <button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-toggle="modal" data-bs-remote="false" data-bs-target="#ajaxModal" data-ajax="'.$ajax_modal_24h.'" onclick="graph_sensor(this);">'.$lang['graph_24h'].'</button>
            <button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
        </div>';      //close class="modal-footer">
        echo '<script language="javascript" type="text/javascript">
               graph_sensor=function(ithis){ $("#ajaxModal").one("hidden.bs.modal", function() { $("#ajaxModal").modal("show",$(ithis)); })};
        </script>';
    return;
}
if(explode(',', $_GET['Ajax'])[0]=='GetModal_Schedule_List')
{
    GetModal_Schedule_List($conn);
    return;
}
?>
