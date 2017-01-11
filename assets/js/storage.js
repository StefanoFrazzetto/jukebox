/**
 * Created by Vittorio on 10/01/2017.
 */

var albums_storage = [];
var radios_storage = [];
var artists_storage = [];

var cover_placeholder;

var album_store_secondary;
var is_storage_partial = false;

function load_storages(callback) {
    function pack(what) {
        var where = [];

        what.forEach(function (elem) {
            where[elem.id] = elem;
        });

        return where;
    }

    $.getJSON('/assets/API/storage.php')
        .done(function (data) {
            artists_storage = pack(data.artists);
            albums_storage = pack(data.albums);
            radios_storage = pack(data.radios);

            cover_placeholder = data.placeholder;

            console.log("Loaded", memorySizeOf(data), "of storage.");
        })
        .fail(function (a, b, c) {
            error("Failed to load album storage. " + b);
            console.log(c)
        })
        .always(function () {
            if (typeof callback === "function")
                callback();
        });

    function memorySizeOf(obj) {
        var bytes = 0;

        function sizeOf(obj) {
            if (obj !== null && obj !== undefined) {
                switch (typeof obj) {
                    case 'number':
                        bytes += 8;
                        break;
                    case 'string':
                        bytes += obj.length * 2;
                        break;
                    case 'boolean':
                        bytes += 4;
                        break;
                    case 'object':
                        var objClass = Object.prototype.toString.call(obj).slice(8, -1);
                        if (objClass === 'Object' || objClass === 'Array') {
                            for (var key in obj) {
                                if (!obj.hasOwnProperty(key)) continue;
                                sizeOf(obj[key]);
                            }
                        } else bytes += obj.toString().length * 2;
                        break;
                }
            }
            return bytes;
        }

        function formatByteSize(bytes) {
            if (bytes < 1024) return bytes + " bytes";
            else if (bytes < 1048576) return (bytes / 1024).toFixed(3) + " KiB";
            else if (bytes < 1073741824) return (bytes / 1048576).toFixed(3) + " MiB";
            else return (bytes / 1073741824).toFixed(3) + " GiB";
        }

        return formatByteSize(sizeOf(obj));
    }
}

function restoreStorage() {
    if (is_storage_partial) {
        albums_storage = album_store_secondary;
        is_storage_partial = false;
    }
}

function backupStorage() {
    restoreStorage();
    album_store_secondary = albums_storage.slice(0); // clones database
    is_storage_partial = true;
}

// SAFE GETTERS

function getArtistName(id) {
    if (typeof artists_storage[id] === "undefined") {
        return "[NO ARTIST]";
    }
    else
        return artists_storage[id].name;
}

// SORTING

function artistSortingFunction(a, b) {
    var x = artists_storage[a.artists[0]].name.toLowerCase();
    var y = artists_storage[b.artists[0]].name.toLowerCase();
    var gne = x < y ? -1 : x > y ? 1 : 0;

    if (gne == 0) {
        x = a.title.toLowerCase();
        y = b.title.toLowerCase();
        return x < y ? -1 : x > y ? 1 : 0;
    }

    return gne;
}

function sortByArtist() {
    albums_storage.sort(artistSortingFunction);
}

function sortByTitle() {
    albums_storage.sort(function (a, b) {
        var x = a.title.toLowerCase();
        var y = b.title.toLowerCase();
        var gne = x < y ? -1 : x > y ? 1 : 0;

        if (gne == 0) {
            x = artists_storage[a.artists[0]].name.toLowerCase();
            y = artists_storage[b.artists[0]].name.toLowerCase();
            return x < y ? -1 : x > y ? 1 : 0;
        }

        return gne;
    });
}

function sortByHits() {
    albums_storage.sort(function (a, b) {
        var gne = b.hits - a.hits;

        if (gne == 0) {
            return b.last_played - a.last_played;
        }

        return gne;
    });
}

function sortByLastPlayed() {
    albums_storage.sort(function (a, b) {
        return b.last_played - a.last_played;
    });
}

function sortByLastAdded() {
    albums_storage.sort(function (a, b) {
        return b.id - a.id;
    });
}

function getItSorted(method) {
    switch (method) {
        case '1':
            sortByArtist();
            break;
        case '2':
            sortByTitle();
            break;
        case '3':
            sortByHits();
            break;
        case '4':
            sortByLastPlayed();
            break;
        case '5':
            sortByLastAdded();
            break;
        default:
            sortByArtist();
    }
}

// Array Intersect
function intersect(a, b) {
    var t;
    if (b.length > a.length)
        t = b, b = a, a = t; // indexOf to loop over shorter
    return a.filter(function (e) {
        if (b.indexOf(e) !== -1) return true;
    });
}