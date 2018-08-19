function makeTextFile(text) {
    var data = new Blob([text], {type: 'text/plain'});

    // If we are replacing a previously generated file we need to
    // manually revoke the object URL to avoid memory leaks.
    if (textFile !== null) {
        window.URL.revokeObjectURL(textFile);
    }

    textFile = window.URL.createObjectURL(data);

    return textFile;
}

function Get(yourUrl) {
    let Httpreq = new XMLHttpRequest(); // a new request
    Httpreq.open("GET", yourUrl, false);
    Httpreq.send(null);
    return Httpreq.responseText;
}

function getNote(id) {
    let json_obj = JSON.parse(Get('index.php?a=homeapi&api=getnote&id=' + id));
    console.log(json_obj);
    if (json_obj['id'] !== null) {
        if (document.getElementById('home-id') == null) {
            $("#mainDiv").load("tpl/NoteForm.html", function () {
                $('#home-name').val(json_obj['name']);
                $('#home-hashtags').val(json_obj['hashtags']);
                $('#home-text').val(json_obj['content']);
                $('#home-id').val(json_obj['id']);
            });
        } else {
            $('#home-name').val(json_obj['name']);
            $('#home-hashtags').val(json_obj['hashtags']);
            $('#home-text').val(json_obj['content']);
            $('#home-id').val(json_obj['id']);
        }
    }
}

function setPinned() {
    let json_obj = JSON.parse(Get('index.php?a=homeapi&api=setpinned&id=' + $('#home-id').val()));
    if (json_obj['response'] === 'OK') {
        if (json_obj['pinned'] === '1') {
            notice('PINNED');
        } else {
            notice('UNPINNED');
        }
    } else if (json_obj['response'] === 'KO') {
        error('NOT PINNED')
    } else {
        error('ERROR NOT PINNED')
    }
}

function setArchived() {
    let json_obj = JSON.parse(Get('index.php?a=homeapi&api=setarchived&id=' + $('#home-id').val()));
    if (json_obj['response'] === 'OK') {
        if (json_obj['archived'] === '1') {
            notice('ARCHIVED');
        } else {
            notice('UN ARCHIVED');
        }
    } else if (json_obj['response'] === 'KO') {
        error('NOT ARCHIVED')
    } else {
        error('ERROR NOT ARCHIVED')
    }
}

function clearNote() {
    $('#home-name').val('');
    $('#home-hashtags').val('');
    $('#home-text').val('');
    $('#home-id').val('');
}


function sendNote() {
    let paramName = jQuery('#home-name').val();
    let paramId = jQuery('#home-id').val();
    let paramHashtags = jQuery('#home-hashtags').val();
    let paramText = jQuery('#home-text').val();
    $.post("index.php?a=homeapi&api=setnote", {
        json_string: JSON.stringify({
            name: paramName,
            hashtags: paramHashtags,
            id: paramId,
            content: paramText
        })
    }, function f(response) {
        response = JSON.parse(response);
        if (response['response'] === 'OK') {
            notice('SAVED');
        } else if (response['response'] === 'KO') {
            error('NOT SAVED');
        }
    });
}

function clearInfo() {
    return new Promise(resolve => {
        setTimeout(() => {
            deleteInfo();
        }, 5000);
    });
}

async function error(text) {
    document.getElementById('error').innerHTML = text;
    await clearInfo();
}

async function notice(text) {
    document.getElementById('notice').innerHTML = text;
    await clearInfo();
}

function deleteInfo() {
    document.getElementById('notice').innerHTML = 'NULL';
    document.getElementById('error').innerHTML = 'NULL';
}


$(document).ready(function () {
    $('#search').donetyping(function () {
        searchFactory();
    });
});

function setExported() {
    var textFile = null;

    function makeTextFile(text) {
        var data = new Blob([text], {type: 'text/plain'});

        // If we are replacing a previously generated file we need to
        // manually revoke the object URL to avoid memory leaks.
        if (textFile !== null) {
            window.URL.revokeObjectURL(textFile);
        }
        textFile = window.URL.createObjectURL(data);
        return textFile;
    }

    var name = document.getElementById('home-name'),
        hashtags = document.getElementById('home-hashtags'),
        textbox = document.getElementById('home-text');
    if (name.value && textbox.value) {
        var link = document.createElement('a');

        link.setAttribute('download', name.value.replace(/ +?/g, '') + '.txt');
        var text = 'Name: ' + name.value + '\n'
            + 'Hashtags: ' + hashtags.value + '\n'
            + '-------------------------------------------------------------------------------------------\n'
            + textbox.value;
        link.href = makeTextFile(text);
        document.body.appendChild(link);

        // wait for the link to be added to the document
        window.requestAnimationFrame(function () {
            var event = new MouseEvent('click');
            link.dispatchEvent(event);
            document.body.removeChild(link);
        });
    }
}

function searchFactory() {
    let search = $('#search');
    if (search.val().charAt(0)) {
        switch (search.val().charAt(0)) {
            case '#':
                if (search.val().length > 1) {
                    let str = search.val().substring(1);
                    getNotesListByHashtag(str);
                } else {
                    getAllHashtags();
                }
                break;
            case '*':
                getAllNotes();
                break;
            case '/':
                let str = search.val().substring(1);
                getNotesByName(str);
                break;
            default:
                str = search.val().substring(1);
                getNotesListByFulltext(str);
                break
        }
    }
    setTimeout(500);
}

function getNotesListByHashtag(whatIWant) {
    $.post("index.php?a=searchapi&api=getnotesbyhashtag", {
        json_string: JSON.stringify({
            whatIWant: whatIWant,
        })
    }, function f(response) {
        response = JSON.parse(response);
        if (response['response'] === 'OK') {
            document.getElementById('mainDiv').innerHTML = response['content'];
        } else if (response['response'] === 'NOT FOUND') {
            notice('NOT FOUND');
        } else {
            error('ERROR WHILE SEARCHING (NoteListByHashtag)');
        }
    });
}

function getNotesListByFulltext(whatIWant) {
    $.post("index.php?a=searchapi&api=getnotesbyfulltext", {
        json_string: JSON.stringify({
            whatIWant: whatIWant,
        })
    }, function f(response) {
        response = JSON.parse(response);
        if (response['response'] === 'OK') {
            document.getElementById('mainDiv').innerHTML = response['content'];
        } else if (response['response'] === 'NOT FOUND') {
            notice('NOT FOUND');
        } else {
            error('ERROR WHILE SEARCHING (NoteListByFulltext)');
        }
    });
}

function getAllHashtags() {
    let json_obj = JSON.parse(Get('index.php?a=searchapi&api=getallhashtags'));
    if (json_obj['response'] === 'OK') {
        document.getElementById('mainDiv').innerHTML = json_obj['content'];
    } else {
        error('ERROR WHILE SEARCHING (AllHashtags)')
    }
}

function getAllNotes() {
    let json_obj = JSON.parse(Get('index.php?a=searchapi&api=getallnotes'));
    if (json_obj['response'] === 'OK') {
        document.getElementById('mainDiv').innerHTML = json_obj['content'];
    } else {
        error('ERROR WHILE SEARCHING (AllNotes)')
    }
}

function setEncrypted() {
    var key = prompt("Please enter your key");
    $('#home-text').val(CryptoJS.AES.encrypt($('#home-text').val(), key));
}

function setDecrypted() {
    var key = prompt("Please enter your key");
    var decryptedBytes = CryptoJS.AES.decrypt($('#home-text').val(), key);
    $('#home-text').val(decryptedBytes.toString(CryptoJS.enc.Utf8));
}

function getNotesByName(whatIWant) {
    $.post("index.php?a=searchapi&api=getnotesbyname", {
        json_string: JSON.stringify({
            whatIWant: whatIWant,
        })
    }, function f(response) {
        response = JSON.parse(response);
        if (response['response'] === 'OK') {
            document.getElementById('mainDiv').innerHTML = response['content'];
        } else if (response['response'] === 'NOT FOUND') {
            notice('NOT FOUND');
        } else {
            error('ERROR WHILE SEARCHING (NoteListByName)');
        }
    });
}

//donetyping
(function ($) {
    $.fn.extend({
        donetyping: function (callback, timeout) {
            timeout = timeout || 1e3; // 1 second default timeout
            var timeoutReference,
                doneTyping = function (el) {
                    if (!timeoutReference) return;
                    timeoutReference = null;
                    callback.call(el);
                };
            return this.each(function (i, el) {
                var $el = $(el);
                // Chrome Fix (Use keyup over keypress to detect backspace)
                // thank you @palerdot
                $el.is(':input') && $el.on('change keyup keypress submit paste', function (e) {
                    // This catches the backspace button in chrome, but also prevents
                    // the event from triggering too preemptively. Without this line,
                    // using tab/shift+tab will make the focused element fire the callback.
                    if (e.type == 'keyup' && e.keyCode != 8) return;

                    // Check if timeout has been set. If it has, "reset" the clock and
                    // start over again.
                    if (timeoutReference) clearTimeout(timeoutReference);
                    timeoutReference = setTimeout(function () {
                        // if we made it here, our timeout has elapsed. Fire the
                        // callback
                        doneTyping(el);
                    }, timeout);
                }).on('blur', function () {
                    // If we can, fire the event since we're leaving the field
                    doneTyping(el);
                });
            });
        }
    });
})(jQuery);