<div class="modalHeader">Album Uploader</div>
<div class="modalBody center mCustomScrollbar">

    <div class="col-row">
        <div class="col-left">
            <input type="text" placeholder="Album Title" title="Album Title" class="full-wide" id="metaDataAlbumTitle"/>
        </div>
        <div class="col-right">
            <div id="metaDataTitlesList">
            </div>
        </div>
    </div>

    <div>
        <hr/>
        <table class="cooltable">
            <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Artist</th>
                <th>File Name</th>
            </tr>
            </thead>
            <tbody id="metaDataSongsTableBody"></tbody>
        </table>
    </div>

</div>
<div class="modalFooter">
    <button id="btnBack">Back</button>
    <button class="right" id="btnNext">Next</button>
</div>

<?php
require_once '../../../vendor/autoload.php';
use Lib\ICanHaz;

ICanHaz::js('2-Metadata.js', false, true);
?>