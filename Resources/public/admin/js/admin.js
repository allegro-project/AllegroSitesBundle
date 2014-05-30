/**
 * Converts a string into a web slug
 */
function slugify(value, showDate) {
    value = value.trim().toLowerCase();

    var from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/_,:;";
    var to   = "aaaaaeeeeeiiiiooooouuuunc------";
    for (var i=0, l=from.length ; i<l ; i++) {
        value = value.replace(new RegExp(from.charAt(i), "g"), to.charAt(i));
    }
    value = value
            .replace(/[^a-z0-9-]/g, "-")
            .replace(/\s+|-+/g, "-")
            .substring(0,50);

    if (showDate) {
        var d = new Date();
        var day = d.getDate();
        var month = d.getMonth() + 1;
        var year = d.getFullYear();
        value = year
            + "-" + (month<10?"0":"") + month
            + "-" + (day<10?"0":"") + day
            + "-" + value;
    }
    return value;
}

function setSlug(input) {
    var id = input.id;
    var slugId = id.replace(/title/, 'slug');
    $('input[id="'+slugId+'"]').attr("value", slugify(input.value, false));
}

/* ****************************************************************** */

/**
 * Clones an admin widget block (label + field). Clone and its children id, name
 * and for attributes are suffixed. Clone is inserted after cloned block.
 * 
 * @return the cloned (div) block
 */
function blockClone(block, idSuffix) {
    var contToClone = $($('div.form-group[id*="' + block + '"]')[0]);
    var newLinkCont = contToClone.clone();
    newLinkCont.find('.CodeMirror').remove();

    if (newLinkCont.attr('id')) {
        newLinkCont.attr('id', newLinkCont.attr('id')   + idSuffix);
    }
    if (newLinkCont.attr('name')) {
        newLinkCont.attr('name', newLinkCont.attr('name') + idSuffix);
    }

    newLinkCont.find('*').each(function() {
        if ($(this).attr('id')) {
            $(this).attr('id', $(this).attr('id') + idSuffix);
        }
        if ($(this).attr('name')) {
            $(this).attr('name', $(this).attr('name') + idSuffix);
        }
        if ($(this).attr('for')) {
            $(this).attr('for', $(this).attr('for') + idSuffix);
        }
    });

    newLinkCont.insertAfter(contToClone);
    return newLinkCont;
}

/**
 * Replaces a textarea with an input field
 * 
 * @param string ta Textarea's ending id
 * @param array attributes to copy from textarea to input
 */
function textareaToInput(ta, attributes) {
    var textarea = $('textarea[id*="' + ta + '"]');
    var input = $('<input>');
    for (i=0; i<attributes.length; i++) {
        input.attr(attributes[i], textarea.attr(attributes[i]));
    }
    textarea = textarea.replaceWith(input);
}

function switchAttributes(toElem, fromElem, attributes) {
    for (i=0; i<attributes.length; i++) {
        var attr = attributes[i];
        var antAttr = toElem.attr(attr);
        toElem.attr(attr, fromElem.attr(attr));
        fromElem.attr(attr, antAttr);
    }
}

function blockSetVisible(block, visible) {
    block.css('display', visible ? '' : 'none');
}

function changeLabel(className, value) {
    var elems = $('label[for*="' + className + '"]');
    elems.html(value);
}

function blockFind(nodeName, id, suffix) {
    var elem = $(nodeName + '[id*="' + id + suffix + '"]');
    return elem.length > 0 ? elem[0] : null;
}

function showPageTypeFields(type) {
    var cloneSuffix = '_clone';
    if (null == blockFind('div', '_body', cloneSuffix)) {
        var clone = blockClone('_body', cloneSuffix);
        changeLabel('_body' + cloneSuffix, 'Link');
        textareaToInput('_body' + cloneSuffix, [ 'id', 'name' ]);

        var orig = $($('div.form-group[id*="_body"]')[0]);
        orig.attr('elem', 'body');
        clone.attr('elem', 'link');
    }

    bodyBlock = $("div[elem='body']");
    linkBlock = $("div[elem='link']");
    headBlock = $('div[id*="head"]');
    textarea = $(bodyBlock.find('textarea')[0]);
    input = $(linkBlock.find('input')[0]);
    switchAttributes(textarea, input, ['id', 'name']);

    if ('l' == type) {
        blockSetVisible(headBlock, false);
        blockSetVisible(linkBlock, true);
        blockSetVisible(bodyBlock, false);
    }
    else if ('p' == type) {
        blockSetVisible(headBlock, true);
        blockSetVisible(linkBlock, false);
        blockSetVisible(bodyBlock, true);
    }
}

/* ****************************************************************** */

$(document).ready(function() {
    $('textarea.page-code').click(function() {
        var cssDisp = $(this).css('display');
        if (cssDisp !== 'hidden' && cssDisp != 'none') {
            transformToEditor(this);
        }
    });

    var menu = $('ol.sortable-menu').sortable({
        group: 'sortable-menu',
        onDrop: function (item, container, _super) {
            var data = menu.sortable("serialize").get();

            var jsonString = JSON.stringify(data, null, ' ');
            $("[id*='menuOrder']").val(jsonString);
            _super(item, container);
        }
    });
});

function transformToEditor(textarea) {
    editor = CodeMirror.fromTextArea(textarea, {
        mode: "application/x-httpd-php",
        lineNumbers: true,
        theme: "mdn-like",
        styleActiveLine: true,
        matchBrackets: true,
        matchTags: {bothTags: true},
        extraKeys: {
            "Tab": function(cm){
                cm.replaceSelection("   " , "end");
            }
        }
    });
}
