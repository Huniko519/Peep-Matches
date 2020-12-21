
SEARCHSYS = {};

SEARCHSYS.Observer = function( context )
{
    this.events = {};
    this.context = context;
};

SEARCHSYS.Observer.PROTO = function()
{
    this.bind = function(eventName, callback, context)
    {
        context = context || false;

        if ( !this.events[eventName] )
        {
            this.events[eventName] = [];
        }

        this.events[eventName].push({
            callback: callback,
            context: context
        });
    };

    this.trigger = function( eventName, eventObj )
    {
        var self = this;

        eventObj = eventObj || {};

        if ( !this.events[eventName] )
        {
            return false;
        }

        $.each(this.events[eventName], function(i, o)
        {
            o.callback.call(o.context || self.context, eventObj);
        });
    };

    this.unbind = function( eventName )
    {
        this.events[eventName] = [];
    };
}

SEARCHSYS.Observer.prototype = new SEARCHSYS.Observer.PROTO();

SEARCHSYS.State = function( data )
{
    data = data || {};
    this.state = data;

    this.observer = new SEARCHSYS.Observer(this);
}

SEARCHSYS.State.PROTO = function()
{
    this.mergeState = function( state )
    {
        $.extend(this.state, state);

        this.observer.trigger('change');
    };

    this.setState = function( state )
    {
        state = state || {};
        this.state = state;

        this.observer.trigger('change');
    };

    this.getState = function()
    {
        return this.state;
    };
};

SEARCHSYS.State.prototype = new SEARCHSYS.State.PROTO();

SEARCHSYS.UserState = function( data )
{
    data = data || {};
    this.state = data;

    this.observer = new SEARCHSYS.Observer(this);

    this.searchedKWs = [];

    this.addKeyword = function( kw )
    {
        this.searchedKWs.push(kw);
    };

    this.isSearched = function( kw )
    {
        for ( var i = 0; i < this.searchedKWs.length; i++ )
        {
            if ( kw.search(this.searchedKWs[i]) === 0 )
            {
                return true;
            }
        }

        return false;
    };

    this.find = function( filter )
    {
        var out = [], cache, self = this;
        cache = this.getState();

        $.each(cache, function(id, item)
        {
            var found = $.isFunction(filter)
                ? filter.call(self, item, id)
                : true

            if ( found ) {
                out.push(item);
            }
        });

        return out;
    };
};

SEARCHSYS.UserState.prototype = new SEARCHSYS.State.PROTO();



SEARCHSYS.userSelector = (function() {

    var _cache = new SEARCHSYS.UserState();
    var ajaxTimeout, syncing = false;

    var node, esel2;
    var _settings = {};

    var formatResult, formatSelection, getData, syncData, getDataFromCache, highlightTerm, getGroupSettings, normalizeText;

    getGroupSettings = function( group ) {
        return _settings.groups[group] || _settings.groupDefaults;
    };

    highlightTerm = function( term, text ) {
        var match = text.toUpperCase().indexOf(term.toUpperCase()),
            tl=term.length,
            markup = [];

        if ( match < 0 ) {
            markup.push(text);
        } else {
            markup.push(text.substring(0, match));
            markup.push("<span class='esel2-match'>");
            markup.push(text.substring(match, match + tl));
            markup.push("</span>");
            markup.push(text.substring(match + tl, text.length));
        }

        return markup.join("");
    };

    formatResult = function( data, container, query ) {
        if ( data.type == "msg" )
            return '<div class="peep_small">' + data.text + '</div>';

        if ( !data.id )
            return '<div class="peep_small peep_remark">' + data.text + '</div>';

        var html = $(data.html);
        html.find(".us-ddi-text").html("<span>" + highlightTerm(query.term, data.text) + "</span>");

        return html;
    };

    formatSelection = function( data, container ) {
        document.location.href = data.url;
        return data.text;
    };

    syncData = function( term, callback ) {
        syncing = true;
        $.getJSON(_settings.rspUrl, {term: term, context: _settings.context}, function( data ) {
            syncing = false;
            if ( $.isFunction(callback) ) callback(data);
            _cache.mergeState(data);
        });
    };

    normalizeText = function (term) {
        if (term == null) {
            return term;
        }
        var accentMap = {
            "à": "a", "á": "a", "â": "a", "ã": "a", "ä": "a",
            "è": "e", "é": "e", "ê": "e",           "ë": "e",
            "ì": "i", "í": "i", "î": "i", "ñ": "n", "ï": "i",
            "ò": "o", "ó": "o", "ô": "o", "õ": "o", "ö": "o",
            "ù": "u", "ú": "u", "û": "u",           "ü": "u",
            "ý": "y",                     "ÿ": "y"
        };
        var ret = "";
        for (var i = 0; i < term.length; i++) {
            ret += accentMap[ term.charAt(i) ] || term.charAt(i);
        }

        return ret;
    };

    getDataFromCache = function( term, count ) {
        count = count || 10;
        var tmp;
        
        var out = [], groups = {}, orderedGroups = [], state = _cache.find(function( item ) {
            tmp = normalizeText(item.text).toUpperCase().indexOf(term.toUpperCase());
            if ( tmp < 0 ) {
                tmp = normalizeText(item.info).toUpperCase().indexOf(term.toUpperCase());
                if ( tmp < 0 ) {
                    return false;
                }
                return true;
            }

            return true;
        });

        state.reverse();

        var groupsCount = 0, lastGroup, val = esel2.val();

        $.each(state, function(id, item) {
            if ( $.inArray(item.id.toString(), val) >= 0 || count == 0 ) {
                return;
            }
            count--;

            if ( item.group ) {
                if ( !groups[item.group] ) {
                    groups[item.group] = {
                        text: item.group,
                        children: []
                    };
                    groupsCount++;
                    lastGroup = item.group;
                }

                groups[item.group].children.push(item);
            } else {
                out.push(item);
            }
        });

        if (out.length > 0 || groupsCount > 0) {
            _settings.groups = _settings.groups || {};

            $.each(_settings.groups, function(groupName, groupSettings) {
                if ( !groups[groupName] && groupSettings.alwaysVisible && groupSettings.noMatchMessage ) {
                    groups[groupName] = {
                        text: groupName,
                        children: [{
                            text: PEEP.getLanguageText(groupSettings.noMatchMessage.prefix, groupSettings.noMatchMessage.key, {
                                "term": term
                            }),
                            type: 'msg'
                        }]
                    };

                    groupsCount++;
                }
            });
        }

        $.each(groups, function( i, group ) {
            orderedGroups.push(group);
        });

        orderedGroups.sort(function(a, b) {
            var sA = getGroupSettings(a.text);
            var sB = getGroupSettings(b.text);

            return sA.priority - sB.priority;
        });

        if ( lastGroup ) {
            var temp = groupsCount == 1 && !getGroupSettings(lastGroup).alwaysVisible ? groups[lastGroup].children : orderedGroups;

            $.each(temp, function(id, group) {
                out.unshift(group);
            });
        }

        return out;
    };

    getData = function( options ) {
        var state = getDataFromCache(options.term);

        var sync = $.trim(options.term) && !_cache.isSearched(options.term);

        if ( sync ) {

            if ( ajaxTimeout ) {
                window.clearTimeout(ajaxTimeout);
            }

            ajaxTimeout = window.setTimeout(function() {
                _cache.addKeyword(options.term);
                syncData(options.term);
            }, 300);
        }

        if ( (!sync || state.length) && !syncing ) {
            options.callback({
                results: state
            });
        }
    };

    return {
        init: function( selector, settings, options, data ) {

            if ( $.isPlainObject(data) ) {
                _cache.setState(data);
            }

            _settings = settings;

            node = $(selector);

            node.esel2($.extend(options, {
                "query": getData,
                "formatResult": formatResult,
                "formatSelection": formatSelection,
                "formatNoMatches": function( term ) {
                    return PEEP.getLanguageText('searchsys', 'selector_no_matches', {
                        "term": term
                    });
                },
                "formatSearching": function() {
                    return PEEP.getLanguageText('searchsys', 'selector_searching');
                },
                formatInputTooShort: function (input, min) {
                    return PEEP.getLanguageText('searchsys', 'input_too_short', {chars : (min - input.length) });
                },
                "postRender": function(term) {
                    var items = esel2.results.find(".esel2-result").length;
                    if ( items > 0 )
                    {
                        esel2.results.append("<li class='esel2-more-results'><a href='" + _settings.viewAllUrl + "?term=" + encodeURIComponent(term) + "'>" + PEEP.getLanguageText('searchsys', 'view_all_results') + "</a></li>");
                    }
                    var $resultsCont = esel2.results.parent();
                    if ($resultsCont.css('background-color') == 'transparent')
                    {
                        $resultsCont.css('background-color', $('#floatbox_prototype .floatbox_container .peep_bg_color').css('background-color'));
                    }
                }
            }));

            node.next(".us-field-fake").hide();

            esel2 = node.data().esel2;

            node.get(0).focus = function() {
                esel2.focusSearch();
            };

            _cache.observer.bind('change', function()
            {
                esel2.updateResults();
            });

            return esel2;
        }
    }
})();

SEARCHSYS.UserSelectorFormElement = function( id, name ) {
    var formElement = new PeepFormElement( id, name );

    formElement.init = function( selector, settings, options, data ) {
        formElement.esel2 = SEARCHSYS.userSelector.init(selector, settings, options, data);
    };

    formElement.resetValue = function() {
        formElement.esel2.data([]);
    };

    formElement.getValue = function() {
        return formElement.esel2.val();
    };

    formElement.setValue = function( val ) {
        formElement.esel2.data(val);
    };

    return formElement;
};