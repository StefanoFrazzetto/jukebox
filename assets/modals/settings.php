<?php
function get_string_between($string, $start, $end)
{
    $string = " " . $string;
    $ini = strpos($string, $start);

    if ($ini == 0)
        return "";
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function factory($raw_materials)
{
    $components = explode(' ', $raw_materials); //Let's hope it's not kerosene;
    $goods = [];

    foreach ($components as $component)
        if (trim($component != '')) {
            $goods[] = $component;
        }

    return $goods;
}

function getSpeakerStatus()
{
    return boolval(intval(shell_exec("../cmd/get_speaker_active.sh")));
}

?>

<div class="modalHeader">Settings</div>
<div class="modalBody mCustomScrollbar" data-mcs-theme="dark">
    <div id="buttonSettings" style="text-align: center; line-height: 39px;">
        Speakers
        <div class="onoffswitch inline" id="speakers_div">
            <input type="checkbox" name="dhcp" class="onoffswitch-checkbox"
                   id="speakers" <?php if (getSpeakerStatus()) echo "checked" ?>>
            <label class="onoffswitch-label" for="speakers">
                <span class="onoffswitch-inner"></span>
                <span class="onoffswitch-switch"></span>
            </label>
        </div>
        <button onclick="$.ajax('assets/cmd/exec.php?cmd=reboot');">Reboot</button>
        <button onclick="$.ajax('assets/cmd/exec.php?cmd=eject');">Eject</button>
        <button onclick="$.ajax('assets/php/calibrate_screen.php');">Calibrate Screen</button>
        <button onclick="openModalPage('assets/modals/update');">Update</button>
        <button onclick="location.reload();">Refresh</button>
        <button class="nuclear" onclick="openModalPage('assets/modals/format.php');">Factory Reset</button>
    </div>


    <hr/>

    <div class="col-left">
        <?php
        $raw_materials = exec("df -hT /home");

        $refined = factory($raw_materials);

        ?>
        <p>Firmware Storage</p>
        <div class="progressBar" style="width: 100%;">
            <div class="progress" style="width: <?php echo $refined[5] ?>">
                <?php echo $refined[5]; ?>
            </div>
        </div>

        <?php

        $raw_materials = exec("df -hT /var/www/html/jukebox");
        $refined = factory($raw_materials);

        ?>

        <p>Music Storage</p>
        <div class="progressBar" style="width: 100%;">
            <div class="progress" style="width: <?php echo $refined[5] ?>">
                <?php echo $refined[5]; ?>
            </div>
        </div>
    </div>

    <div class="col-right">
        <?php
        //use -bn2 for more accurate resuls, it will take a long time, tho
        $percentage = exec('top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk \'{print 100 - $1"%"}\'');
        ?>

        <p>CPU Usage</p>
        <div class="progressBar" style="width: 100%;">
            <div class="progress" style="width: <?php echo $percentage ?>">
                <?php echo $percentage; ?>
            </div>
        </div>

        <?php
        $temp = exec('cat /sys/devices/platform/sunxi-i2c.0/i2c-0/0-0034/temp1_input');
        $temp = intval($temp) / 1000;
        ?>

        <p>CPU Temp</p>
        <div class="progressBar" style="width: 100%;">
            <div class="progress" style="width: <?php echo $temp ?>%">
                <?php echo $temp; ?> &deg;C
            </div>
        </div>
    </div>

    <script>
        $('#speakers').on("change", function () {
            if (!this.checked) {
                $.ajax('assets/cmd/exec.php?cmd=int_speakers_off');
            } else {
                $.ajax('assets/cmd/exec.php?cmd=int_speakers_on');
            }
        });
    </script>
</div>
