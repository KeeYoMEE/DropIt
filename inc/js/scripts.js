//LOGIC

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

                    // Check if timeout has been set. If it has, 'reset' the clock and
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

function setMainDiv(context) {
    document.getElementById('mainDiv').innerHTML = context
}

//NOTE HANDLERS

function getNote(id) {
    $.ajax({
        url: 'index.php',
        type: 'post',
        statusCode: {
            204: function () {
                error('NO CONTENT')
            },
            409: function () {
                error('PAGE CONFLICT')
            },
            404: function () {
                error('NOTE NOT FOUND')
            }
        },
        data: {
            a: 'homeApi',
            api: 'getNote',
            id: id
        },
        success: function (response, textStatus, jQxhr) {
            if (response['id'] !== null) {
                if (document.getElementById('home-id') == null) {
                    $('#mainDiv').load('tpl/NoteForm.html', function () {
                        setNoteHtml(response['id'], response['name'], response['hashtags'], response['content'])
                    });
                } else {
                    setNoteHtml(response['id'], response['name'], response['hashtags'], response['content'])
                }
            }
        },
        error: function (jqXhr, textStatus, errorThrown) {
            console.log(errorThrown)
        }
    });
}

function setNoteHtml(id, name, hashtag, content) {
    $('#home-name').val(name);
    $('#home-hashtags').val(hashtag);
    $('#home-text').val(content);
    $('#home-id').val(id);
}

function sendNote() {
    $.ajax({
        url: 'index.php',
        type: 'post',
        statusCode: {
            204: function () {
                error('NO CONTENT')
            },
            409: function () {
                error('PAGE CONFLICT')
            },
            406: function () {
                error('NOT SEND PROPERLY')
            },
            400: function () {
                error('SAVING DID NOT GO WELL')
            }
        },
        data: {
            a: 'homeApi',
            api: 'setNote',
            j_data: JSON.stringify({
                name: $('#home-name').val(),
                hashtags: $('#home-hashtags').val(),
                id: $('#home-id').val(),
                content: $('#home-text').val()
            })
        },
        success: function () {
            notice('SAVED')
        },
        error: function (jqXhr, textStatus, errorThrown) {
            console.log(errorThrown)
        }
    });

}

function setPinned() {
    $.ajax({
        url: 'index.php',
        type: 'post',
        statusCode: {
            204: function () {
                error('NO CONTENT')
            },
            409: function () {
                error('PAGE CONFLICT')
            },
            406: function () {
                error('NOT SEND PROPERLY')
            },
            400: function () {
                error('PINNIG DID NOT GO WELL ')
            }
        },
        data: {
            a: 'homeApi',
            api: 'setPinned',
            id: $('#home-id').val()
        },
        success: function (response, textStatus, jQxhr) {
            if (response['pinned'] === '1') {
                notice('PINNED')
            } else {
                notice('UNPINNED')
            }
        },
        error: function (jqXhr, textStatus, errorThrown) {
            console.log(errorThrown)
        }
    });
}

function setArchived() {
    $.ajax({
        url: 'index.php',
        type: 'post',
        statusCode: {
            204: function () {
                error('NO CONTENT')
            },
            409: function () {
                error('PAGE CONFLICT')
            },
            406: function () {
                error('NOT SEND PROPERLY')
            },
            400: function () {
                error('PINNIG DID NOT GO WELL ')
            }
        },
        data: {
            a: 'homeApi',
            api: 'setArchived',
            id: $('#home-id').val()
        },
        success: function (response, textStatus, jQxhr) {
            if (response['archived'] === '1') {
                notice('ARCHIVED')
            } else {
                notice('UN ARCHIVED')
            }
        },
        error: function (jqXhr, textStatus, errorThrown) {
            console.log(errorThrown)
        }
    });
}

function setExported() {
    let textFile = null;

    function makeTextFile(text) {
        let data = new Blob([text], {type: 'text/plain'});

        // If we are replacing a previously generated file we need to
        // manually revoke the object URL to avoid memory leaks.
        if (textFile !== null) {
            window.URL.revokeObjectURL(textFile)
        }
        textFile = window.URL.createObjectURL(data);
        return textFile
    }

    let name = document.getElementById('home-name'),
        hashtags = document.getElementById('home-hashtags'),
        textbox = document.getElementById('home-text');
    if (name.value && textbox.value) {
        let link = document.createElement('a');

        link.setAttribute('download', name.value.replace(/ +?/g, '') + '.txt');
        let text = 'Name: ' + name.value + '\n'
            + 'Hashtags: ' + hashtags.value + '\n'
            + '--//----------------//----------------//--\n'
            + textbox.value;
        link.href = makeTextFile(text);
        document.body.appendChild(link);

        // wait for the link to be added to the document
        window.requestAnimationFrame(function () {
            let event = new MouseEvent('click');
            link.dispatchEvent(event);
            document.body.removeChild(link)
        })
    }
}

function clearNoteHtml() {
    $('#home-name').val('');
    $('#home-hashtags').val('');
    $('#home-text').val('');
    $('#home-id').val('');
}

function setEncrypted() {
    let key = prompt('Please enter your key');
    $('#home-text').val(CryptoJS.AES.encrypt($('#home-text').val(), key))
}

function setDecrypted() {
    let key = prompt('Please enter your key');
    let decryptedBytes = CryptoJS.AES.decrypt($('#home-text').val(), key);
    $('#home-text').val(decryptedBytes.toString(CryptoJS.enc.Utf8))
}

//SEARCH HANDLER

$(document).ready(function () {
    $('#search').donetyping(function () {
        searchFactory()
    });
});

function searchFactory() {
    let search = $('#search').val(),
        str;
    if (search.charAt(0)) {
        switch (search.charAt(0)) {
            case '#':
                if (search.length > 1) {
                    let str = search.substring(1);
                    getNotesListByHashtag(str)
                } else {
                    getAllHashtags()
                }
                break;
            case '*':
                getAllNotes();
                break;
            case '/':
                str = search.substring(1);
                getNotesByName(str);
                break;
            default:
                if (search.length > 2) {
                    getNotesListByFulltext(search);
                } else {
                    error('at least 2 chars')
                }
                break;
        }
    }
    setTimeout(500)
}

function getNotesListByHashtag(whatIWant) {
    $.ajax({
        url: 'index.php',
        type: 'post',
        statusCode: {
            204: function () {
                error('NO CONTENT')
            },
            409: function () {
                error('PAGE CONFLICT')
            },
            406: function () {
                error('NOT SEND PROPERLY')
            },
            404: function () {
                error('NOT FOUND')
            }
        },
        data: {
            a: 'searchApi',
            api: 'getNotesByHashtag',
            j_data: JSON.stringify({
                whatIWant: whatIWant,
            })
        },
        success: function (response, textStatus, jQxhr) {
            setMainDiv(response['content'])
        },
        error: function (jqXhr, textStatus, errorThrown) {
            console.log(errorThrown)
        }
    });
}

function getNotesListByFulltext(whatIWant) {
    $.ajax({
        url: 'index.php',
        type: 'post',
        statusCode: {
            204: function () {
                error('NO CONTENT')
            },
            409: function () {
                error('PAGE CONFLICT')
            },
            406: function () {
                error('NOT SEND PROPERLY')
            },
            404: function () {
                error('NOT FOUND')
            }
        },
        data: {
            a: 'searchApi',
            api: 'getNotesByFulltext',
            j_data: JSON.stringify({
                whatIWant: whatIWant,
            })
        },
        success: function (response, textStatus, jQxhr) {
            setMainDiv(response['content'])
        },
        error: function (jqXhr, textStatus, errorThrown) {
            console.log(errorThrown)
        }
    });
}

function getAllHashtags() {
    $.ajax({
        url: 'index.php',
        type: 'post',
        statusCode: {
            204: function () {
                error('NO CONTENT')
            },
            409: function () {
                error('PAGE CONFLICT')
            },
            400: function () {
                error('ERROR')
            }
        },
        data: {
            a: 'searchApi',
            api: 'getAllHashtags',
        },
        success: function (response, textStatus, jQxhr) {
            setMainDiv(response['content'])
        },
        error: function (jqXhr, textStatus, errorThrown) {
            console.log(errorThrown)
        }
    });
}

function getAllNotes() {
    $.ajax({
        url: 'index.php',
        type: 'post',
        statusCode: {
            204: function () {
                error('NO CONTENT')
            },
            409: function () {
                error('PAGE CONFLICT')
            },
            400: function () {
                error('ERROR')
            },
            404: function () {
                error('NOT FOUND')
            }
        },
        data: {
            a: 'searchApi',
            api: 'getAllNotes',
        },
        success: function (response, textStatus, jQxhr) {
            setMainDiv(response['content'])
        },
        error: function (jqXhr, textStatus, errorThrown) {
            console.log(errorThrown)
        }
    });
}

function getNotesByName(whatIWant) {
    $.ajax({
        url: 'index.php',
        type: 'post',
        statusCode: {
            204: function () {
                error('NO CONTENT')
            },
            409: function () {
                error('PAGE CONFLICT')
            },
            406: function () {
                error('NOT SEND PROPERLY')
            },
            404: function () {
                error('NOT FOUND')
            }
        },
        data: {
            a: 'searchApi',
            api: 'getNotesByName',
            j_data: JSON.stringify({
                whatIWant: whatIWant,
            })
        },
        success: function (response, textStatus, jQxhr) {
            setMainDiv(response['content'])
        },
        error: function (jqXhr, textStatus, errorThrown) {
            console.log(errorThrown)
        }
    });
}