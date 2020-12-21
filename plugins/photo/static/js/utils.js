window.photoUtils = Object.freeze({
    extend: function( ctor, superCtor )
    {
        ctor._super = superCtor;
        ctor.prototype = Object.create(superCtor.prototype, {
            constructor: {
                value: ctor,
                enumerable: false,
                writable: true,
                configurable: true
            }
        });
    },

    bind: function( context )
    {
        return function( f )
        {
            return f.bind(context);
        };
    },

    fluent: function( f )
    {
        return function()
        {
            f.apply(this, arguments);

            return this;
        };
    },

    truncate: function( value, limit, ended )
    {
        if ( !value ) return '';

        value = String(value);
        limit = +limit || 50;
        ended = ended === undefined ? '...' : ended;

        var parts;

        if ( (parts = value.split('\n')).length >= 3 )
        {
            value = parts.slice(0, 3).join('\n') + ended;
        }
        else if ( value.length > limit )
        {
            value = value.substring(0, limit) + ended;
        }

        return value;
    },

    hashtagPattern: /#(?:\w|[^\u0000-\u007F])+/g,

    getHashtags: function( text )
    {
        var result = {};

        text.replace(this.hashtagPattern, function( str, offest )
        {
            result[offest] = str;
        });

        return result;
    },

    descToHashtag: function( description, hashtags, url )
    {
        var url = '<a href="' + url + '">{$tagLabel}</a>';

        return description.replace(this.hashtagPattern, function( str, offest )
        {
            return (url.replace('-tag-', encodeURIComponent(hashtags[offest]))).replace('{$tagLabel}', str);
        }).replace(/\n/g, '<br>');
    },

    includeScriptAndStyle: function( markup )
    {
        if ( !markup ) return;

        if (markup.styleSheets)
        {
            $.each(markup.styleSheets, function(i, o)
            {
                PEEP.addCssFile(o);
            });
        }

        if (markup.styleDeclarations)
        {
            PEEP.addCss(markup.styleDeclarations);
        }

        if (markup.beforeIncludes)
        {
            PEEP.addScript(markup.beforeIncludes);
        }

        if (markup.scriptFiles)
        {
            PEEP.addScriptFiles(markup.scriptFiles, function()
            {
                if (markup.onloadScript)
                {
                    PEEP.addScript(markup.onloadScript);
                }
            });
        }
        else
        {
            if (markup.onloadScript)
            {
                PEEP.addScript(markup.onloadScript);
            }
        }
    },

    isEmptyArray: function( arr )
    {
        return !Array.isArray(arr) || arr.length === 0;
    },

    getObjectType: function( o )
    {
        return Object.prototype.toString.call(o).slice(8, -1);
    },

    getObjectValue: function( keys, object )
    {
        if ( !Array.isArray(keys) || this.getObjectType(object) !== 'Object' )
        {
            return {};
        }

        return keys.reduce(function( result, key )
        {
            var value = object[key];

            if ( value !== undefined )
            {
                result[key] = value;
            }

            return result;
        }, {});
    }
});