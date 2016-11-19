<!DOCTYPE html>
<html>
<head>
    <title>Jukebox Remote</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <!--suppress HtmlUnknownTarget -->
    <link href="assets/css/main_remote.css?<?php echo uniqid() ?>" rel="stylesheet" type="text/css"/>
    <link href="assets/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="assets/css/jquery.mCustomScrollbar.min.css"/>
    <link rel="icon" type="image/png" href="assets/img/icons/vinyl1.png">
    <meta name="theme-color" content="#2a2a2a">
</head>
<body>

<div id="container">
    <div id="main-section">
        <div id="remote-upper-bar">
            <div id="remote-menu-btn">
                <i class="fa fa-bars"></i>
            </div>
            <div id="remote-search-bar">
                <input id="remote-search-field" type="text" class="full-wide" placeholder="Search..."/>
                <div id="results"></div>
            </div>
            <div id="remote-playlist-btn">
                <i class="fa fa-list-ul"></i>
            </div>
        </div>

        <div id="cover-container">
            <img src="/assets/img/album-placeholder.png" id="cover" class="cover-picture"/>
        </div>

        <div id="remote-controls-placeholder"></div>

        <div id="remote-controls">

            <div id="log" class="hidden"></div>

            <div id="cover-label">
                <div id="artist">-</div>
                <div id="title">-</div>
            </div>

            <div class="progressBar thin">
                <div class="progress" id="trackProgress"></div>
            </div>

            <div class="holo-btn" onclick="sendEvent('previous')"><i class="fa fa-step-backward"></i></div>

            <div class="holo-btn big" onclick="sendEvent('play/pause')" id="play-pause"><i class="fa fa-pause"></i>
            </div>

            <div class="holo-btn" onclick="sendEvent('next')"><i class="fa fa-step-forward"></i></div>

        </div>
    </div>
    <div id="playlist-section">
        <table id="remote-playlist-table" class="cooltable small">
            <thead>
            <tr>
                <th>#</th>
                <th>Song</th>
                <th>Time</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>

        <hr/>

    </div>
    <div id="menu-section">
        <ul class="multiselect">
            <li>
                <label for="debug-volume">Volume</label>
                <input step="0.1" type="number" id="debug-volume"/>
            </li>
            <li>Track time <span id="debug-time"></span></li>
            <li>Ping <span id="debug-latency"></span></li>
            <li>Delta Time <span id="debug-delta-time"></span></li>
            <li>
                <button onclick="sendEvent('refresh')">Refresh Jukebox Page</button>
            </li>
        </ul>
    </div>
</div>


<script type="text/javascript" src="assets/js/jquery.min.js"></script>
<script type="text/javascript" src="assets/js/remote_client.js?<?php echo uniqid() ?>"></script>
<script type="text/javascript" src="assets/js/remote_control_scripts.js?<?php echo uniqid() ?>"></script>
</body>
</html>